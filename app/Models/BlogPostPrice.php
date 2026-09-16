<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BlogPostPrice extends Model
{
    protected $fillable = [
        'blog_post_id',
        'region',
        'item',
        'price',
        'currency',
        'note',
        'sort_order',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'sort_order' => 'integer',
    ];

    public function post(): BelongsTo
    {
        return $this->belongsTo(BlogPost::class, 'blog_post_id');
    }

    public function formattedPrice(): string
    {
        $symbols = ['USD' => '$', 'EUR' => '€'];
        $amount = number_format((float) $this->price, 2);

        return isset($symbols[$this->currency])
            ? $symbols[$this->currency].$amount
            : $this->currency.' '.$amount;
    }
}
