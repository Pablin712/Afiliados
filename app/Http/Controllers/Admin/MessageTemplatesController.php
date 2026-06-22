<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MessageTemplate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MessageTemplatesController extends Controller
{
    public function index(): View
    {
        $templates = MessageTemplate::orderBy('name')->get();

        return view('admin.message-templates.index', compact('templates'));
    }

    public function update(Request $request, MessageTemplate $messageTemplate): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'body' => ['required', 'string', 'max:10000'],
        ]);

        $messageTemplate->update($validated);

        return redirect()
            ->route('admin.message-templates.index')
            ->with('status', __('messages.admin.message_templates.messages.updated'));
    }
}
