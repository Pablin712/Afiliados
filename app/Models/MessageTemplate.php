<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MessageTemplate extends Model
{
    protected $fillable = ['key', 'name', 'body', 'description'];

    public static function bodyFor(string $key, string $fallback = ''): string
    {
        $template = self::where('key', $key)->first();

        return $template ? $template->body : $fallback;
    }

    public function render(array $variables = []): string
    {
        $body = $this->body;

        foreach ($variables as $var => $value) {
            $body = str_replace('{'.$var.'}', (string) $value, $body);
        }

        return $body;
    }
}
