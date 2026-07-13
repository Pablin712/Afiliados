<?php

namespace App\Http\Requests\Channels;

use App\Models\Channel;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateChannelRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('edit channels') ?? false;
    }

    public function rules(): array
    {
        $channel = $this->route('channel');

        return [
            'type' => ['required', Rule::in(array_keys(Channel::types()))],
            'name' => ['required', 'string', 'max:120', Rule::unique('channels', 'name')->ignore($channel)],
            'purpose' => ['required', Rule::in(array_keys(Channel::purposes()))],
            'is_active' => ['nullable', 'boolean'],
            'is_exclusive' => ['nullable', 'boolean'],
            'chat_id' => ['required', 'string', 'max:100'],
            'bot_token' => ['nullable', 'string', 'max:255'],
            'instance_name' => ['nullable', 'string', 'max:100', 'required_if:type,'.Channel::TYPE_WHATSAPP],
            'server_url' => ['nullable', 'url', 'max:255', 'required_if:type,'.Channel::TYPE_WHATSAPP],
            'api_key' => ['nullable', 'string', 'max:255', 'required_if:type,'.Channel::TYPE_WHATSAPP],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
