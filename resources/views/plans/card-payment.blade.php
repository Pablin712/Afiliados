<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-graphite-100 leading-tight">
            {{ __('messages.plans.card_payment_title') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Plan summary --}}
            <div class="rounded-3xl border border-gray-200 bg-white p-6 shadow-sm dark:border-graphite-800 dark:bg-graphite-950/40">
                <p class="text-xs font-semibold uppercase tracking-[0.3em] text-brand-600 dark:text-brand-400">
                    {{ __('messages.plans.program_badge') }}
                </p>
                <h3 class="mt-1 text-xl font-bold text-gray-900 dark:text-graphite-100">
                    {{ $payment->program?->name ?? __('messages.plans.program_badge') }}
                </h3>
                <div class="mt-3 flex items-center gap-3">
                    <span class="text-sm text-gray-600 dark:text-graphite-400">{{ __('messages.plans.amount_label') }}:</span>
                    <span class="text-2xl font-bold text-brand-600 dark:text-brand-400">${{ number_format((float) $payment->amount, 2) }}</span>
                </div>
            </div>

            {{-- Datafast widget --}}
            <div class="rounded-3xl border border-gray-200 bg-white p-6 shadow-sm dark:border-graphite-800 dark:bg-graphite-950/40">
                <h4 class="mb-4 text-base font-semibold text-gray-900 dark:text-graphite-100">
                    {{ __('messages.plans.card_widget_title') }}
                </h4>

                <p class="mb-6 text-sm text-gray-600 dark:text-graphite-400">
                    {{ __('messages.plans.card_widget_description') }}
                </p>

                {{-- The widget form. Datafast populates this with the card fields. --}}
                <form action="{{ route('plans.card-return') }}" class="paymentWidgets" data-brands="{{ $brands }}"></form>

                <p class="mt-4 text-xs text-gray-400 dark:text-graphite-500">
                    {{ __('messages.plans.card_widget_secure_note') }}
                </p>
            </div>

            {{-- Back link --}}
            <div class="text-center">
                <a href="{{ route('plans.index') }}" class="text-sm text-gray-500 hover:text-gray-700 dark:text-graphite-400 dark:hover:text-graphite-200 underline underline-offset-2">
                    {{ __('messages.plans.card_cancel_link') }}
                </a>
            </div>

        </div>
    </div>

    @push('styles')
    <style>
        /* ── Datafast / oppwa widget – dark mode overrides ── */
        .dark .wpwl-container { background: transparent; }
        .dark .wpwl-form-card { background: transparent; }

        .dark .wpwl-group { margin-bottom: .75rem; }

        .dark .wpwl-label {
            color: #94a3b8;          /* slate-400 */
            font-size: .8125rem;
        }

        .dark .wpwl-control {
            background-color: #0f172a;   /* slate-900 */
            color: #f1f5f9;              /* slate-100 */
            border: 1px solid #334155;   /* slate-700 */
            border-radius: .375rem;
            padding: .5rem .75rem;
        }
        .dark .wpwl-control:focus {
            border-color: #6366f1;
            outline: none;
            box-shadow: 0 0 0 2px rgba(99,102,241,.25);
        }

        .dark .wpwl-has-error .wpwl-control { border-color: #f87171; }

        .wpwl-hint,
        .wpwl-hint-cardHolderError {
            color: #dc2626;              /* red-600 */
            font-size: .75rem;
            margin-top: .25rem;
        }
        .dark .wpwl-hint,
        .dark .wpwl-hint-cardHolderError {
            color: #f87171;              /* red-400 */
            font-size: .75rem;
        }

        .dark .wpwl-button-pay {
            background-color: #4f46e5;   /* indigo-600 */
            border: none;
            color: #fff;
            border-radius: .5rem;
            padding: .625rem 1.5rem;
            font-weight: 600;
            cursor: pointer;
        }
        .dark .wpwl-button-pay:hover { background-color: #4338ca; }

        .dark .wpwl-brand-card,
        .dark .wpwl-wrapper-brand {
            filter: brightness(1.15);
        }

        .dark .wpwl-wrapper-cardNumber,
        .dark .wpwl-wrapper-expiry,
        .dark .wpwl-wrapper-cvv,
        .dark .wpwl-wrapper-cardHolder {
            background: transparent;
        }

        /* Iframe card number input (oppwa renders inside iframe) */
        .dark .wpwl-sup-wrapper { background: transparent; }
    </style>
    @endpush

    @push('scripts')
        <script>
            var wpwlOptions = {
                style: "card",
                locale: "es",
                labels: {
                    cvv: "CVV",
                    cardHolder: "Nombre (igual que en la tarjeta)"
                },
                onReady: function () {
                    var dfImg = '<br><img src="https://www.datafast.com.ec/images/verified.png" '
                        + 'style="display:block;margin:0 auto;width:100%;" alt="Datafast">';
                    var btn = document.querySelector('form.wpwl-form-card .wpwl-button');
                    if (btn) { btn.insertAdjacentHTML('beforebegin', dfImg); }
                },
                onBeforeSubmitCard: function () {
                    var cardHolderInput = document.querySelector('.wpwl-control-cardHolder');
                    if (cardHolderInput && cardHolderInput.value.trim() === '') {
                        cardHolderInput.classList.add('wpwl-has-error');
                        if (!document.querySelector('.wpwl-hint-cardHolderError')) {
                            cardHolderInput.insertAdjacentHTML(
                                'afterend',
                                '<div class="wpwl-hint-cardHolderError">Campo requerido</div>'
                            );
                        }
                        var payButton = document.querySelector('.wpwl-button-pay');
                        if (payButton) {
                            payButton.classList.add('wpwl-button-error');
                            payButton.setAttribute('disabled', 'disabled');
                        }
                        return false;
                    }
                    return true;
                }
            };
        </script>
        <script src="{{ $widgetUrl }}"></script>
    @endpush
</x-app-layout>
