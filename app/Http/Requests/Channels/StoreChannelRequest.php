<?php

namespace App\Http\Requests\Channels;

use App\Models\Channel;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreChannelRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create channels') ?? false;
    }

    public function rules(): array
    {
        return [
            'type' => ['required', Rule::in(array_keys(Channel::types()))],
            'name' => ['required', 'string', 'max:120', Rule::unique('channels', 'name')],
            'purpose' => ['required', Rule::in(array_keys(Channel::purposes()))],
            'is_active' => ['nullable', 'boolean'],
            'chat_id' => ['required', 'string', 'max:100'],
            'bot_token' => ['nullable', 'string', 'max:255'],
            'instance_name' => ['nullable', 'string', 'max:100', 'required_if:type,'.Channel::TYPE_WHATSAPP],
            'server_url' => ['nullable', 'url', 'max:255', 'required_if:type,'.Channel::TYPE_WHATSAPP],
            'api_key' => ['nullable', 'string', 'max:255', 'required_if:type,'.Channel::TYPE_WHATSAPP],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
