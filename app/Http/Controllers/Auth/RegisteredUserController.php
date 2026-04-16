<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Membership;
use App\Models\MembershipType;
use App\Models\User;
use App\Services\RegistrationWhatsappService;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\View\View;
use Throwable;

class RegisteredUserController extends Controller
{
    private const REFERRAL_SPONSOR_SESSION_KEY = 'registration_referral_sponsor_id';

    public function __construct(private readonly RegistrationWhatsappService $registrationWhatsappService)
    {
    }

    /**
     * Display the registration view.
     */
    public function create(Request $request): View
    {
        $rawRefCode = trim($request->string('ref')->toString());
        $refCode = $this->sanitizeReferralCode($rawRefCode);

        try {
            $sponsor = null;

            if ($refCode !== '') {
                $sponsor = User::resolveAffiliateCode($refCode);

                if ($sponsor !== null) {
                    $request->session()->put(self::REFERRAL_SPONSOR_SESSION_KEY, $sponsor->id);
                }
            }

            if ($sponsor === null) {
                $sessionSponsorId = (int) $request->session()->get(self::REFERRAL_SPONSOR_SESSION_KEY, 0);
                $sponsor = $sessionSponsorId > 0 ? User::query()->find($sessionSponsorId) : null;
            }

            if ($sponsor === null) {
                $sponsor = $this->resolveDefaultSponsor();
            }

            abort_if($sponsor === null, 404);

            return view('auth.register', [
                'sponsor' => $sponsor,
            ]);
        } catch (Throwable $exception) {
            $incidentId = (string) Str::uuid();

            Log::error('Register referral lookup failed', [
                'incident_id' => $incidentId,
                'ref_raw' => $rawRefCode,
                'ref_sanitized' => $refCode,
                'exception_class' => $exception::class,
                'exception_message' => $exception->getMessage(),
            ]);

            $sessionSponsorId = (int) $request->session()->get(self::REFERRAL_SPONSOR_SESSION_KEY, 0);

            $sponsor = $sessionSponsorId > 0
                ? User::query()->find($sessionSponsorId)
                : $this->resolveDefaultSponsor();

            abort_if($sponsor === null, 404);

            return view('auth.register', [
                'sponsor' => $sponsor,
            ])->with('error', __('messages.auth.registration_error_referral_lookup', ['id' => $incidentId]));
        }
    }

    /**
     * Check if registration fields are available.
     */
    public function availability(Request $request): JsonResponse
    {
        $email = Str::lower(trim($request->string('email')->toString()));
        $identification = trim($request->string('identification')->toString());

        $emailExists = $email !== ''
            ? User::query()->whereRaw('LOWER(email) = ?', [$email])->exists()
            : false;

        $identificationExists = $identification !== ''
            ? User::query()->where('identification', $identification)->exists()
            : false;

        return response()->json([
            'email_exists' => $emailExists,
            'identification_exists' => $identificationExists,
        ]);
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $sponsorId = $this->resolveSponsorIdFromSessionOrPayload($request);
        $phoneColumnAvailable = Schema::hasColumn('users', 'phone');

        $request->merge([
            'sponsor_id' => $sponsorId,
            'phone' => $this->normalizePhone((string) $request->input('phone', '')),
        ]);

        $phoneRules = ['required', 'string', 'regex:/^\+5939\d{8}$/', 'max:32'];
        if ($phoneColumnAvailable) {
            $phoneRules[] = 'unique:'.User::class.',phone';
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'phone' => $phoneRules,
            'identification' => ['required', 'string', 'max:50', 'unique:'.User::class.',identification'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'sponsor_id' => ['required', 'integer', 'exists:users,id'],
        ]);

        try {
            $user = DB::transaction(function () use ($request, $validated, $phoneColumnAvailable): User {
                $payload = [
                    'name' => $request->name,
                    'email' => $request->email,
                    'identification' => $request->string('identification')->toString(),
                    'password' => Hash::make($request->password),
                    'sponsor_id' => (int) $validated['sponsor_id'],
                    'approved_at' => now(),
                ];

                if ($phoneColumnAvailable) {
                    $payload['phone'] = (string) $validated['phone'];
                }

                $user = User::create($payload);

                if (Schema::hasColumn('users', 'affiliate_code')) {
                    $user->affiliate_code = User::buildAffiliateCode($user->name, $user->id);
                    $user->save();
                }

                $user->assignRole('user');

                $freeMembershipType = MembershipType::query()->firstOrCreate(
                    ['name' => 'free'],
                    [
                        'affiliates_required' => 0,
                        'cost' => 0,
                        'profit' => 0,
                    ]
                );

                Membership::query()->updateOrCreate(
                    ['user_id' => $user->id],
                    [
                        'membership_type_id' => $freeMembershipType->id,
                        'status' => 'free',
                        'started_at' => now(),
                        'expires_at' => null,
                        'last_payment_id' => null,
                    ]
                );

                return $user;
            });

            $this->registrationWhatsappService->send($user);

            Auth::login($user);

            return redirect()->intended(route('dashboard'));
        } catch (Throwable $exception) {
            $incidentId = (string) Str::uuid();

            Log::error('Registration failed', [
                'incident_id' => $incidentId,
                'email' => (string) $request->input('email', ''),
                'identification' => (string) $request->input('identification', ''),
                'sponsor_id' => $request->input('sponsor_id'),
                'exception_class' => $exception::class,
                'exception_message' => $exception->getMessage(),
            ]);

            $errorMessage = $this->resolveRegistrationErrorMessage($exception, $incidentId);

            return back()
                ->withInput($request->except(['password', 'password_confirmation']))
                ->with('error', $errorMessage);
        }
    }

    private function resolveRegistrationErrorMessage(Throwable $exception, string $incidentId): string
    {
        if ($exception instanceof QueryException) {
            $sqlError = Str::lower($exception->getMessage());

            if (Str::contains($sqlError, ['unknown column', 'column not found']) && Str::contains($sqlError, 'phone')) {
                return __('messages.auth.registration_error_phone_column', ['id' => $incidentId]);
            }

            if (Str::contains($sqlError, ['foreign key constraint', 'constraint fails']) && Str::contains($sqlError, 'sponsor')) {
                return __('messages.auth.registration_error_sponsor', ['id' => $incidentId]);
            }
        }

        return __('messages.auth.registration_error_generic', ['id' => $incidentId]);
    }

    private function normalizePhone(string $phone): string
    {
        $phone = trim($phone);
        if ($phone === '') {
            return '';
        }

        $digits = preg_replace('/\D+/', '', $phone) ?? '';

        if ($digits === '') {
            return '';
        }

        if (str_starts_with($digits, '00')) {
            $digits = substr($digits, 2);
        }

        // Only accept values that already include Ecuador country code: 5939XXXXXXXX
        if (preg_match('/^5939\d{8}$/', $digits)) {
            return '+'.$digits;
        }

        return '';
    }

    private function resolveDefaultSponsor(): ?User
    {
        return User::query()->find(1)
            ?? User::role('admin')->orderBy('id')->first()
            ?? User::query()->orderBy('id')->first();
    }

    private function resolveSponsorIdFromSessionOrPayload(Request $request): int
    {
        $sessionSponsorId = (int) $request->session()->get(self::REFERRAL_SPONSOR_SESSION_KEY, 0);

        if ($sessionSponsorId > 0 && User::query()->whereKey($sessionSponsorId)->exists()) {
            return $sessionSponsorId;
        }

        if ($sessionSponsorId > 0) {
            $request->session()->forget(self::REFERRAL_SPONSOR_SESSION_KEY);
        }

        return (int) $request->input('sponsor_id', 0);
    }

    private function sanitizeReferralCode(string $refCode): string
    {
        $normalized = Str::upper(trim($refCode));

        if ($normalized === '') {
            return '';
        }

        if (! preg_match('/^[A-Z0-9]{3,80}$/', $normalized)) {
            return '';
        }

        return $normalized;
    }
}
