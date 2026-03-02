<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditEvent extends Model
{
    public $timestamps = false; // only has created_at

    protected $fillable = [
        'actor_user_id', 'entity_type', 'entity_id',
        'action', 'before_json', 'after_json', 'created_at',
    ];

    protected $casts = [
        'before_json' => 'array',
        'after_json'  => 'array',
        'created_at'  => 'datetime',
    ];

    public function actor()
    {
        return $this->belongsTo(User::class, 'actor_user_id');
    }
}
