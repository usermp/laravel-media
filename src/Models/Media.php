<?php

namespace  Usermp\LaravelMedia\Models;

use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Usermp\LaravelFilter\Traits\Filterable;

class Media extends Model
{
    use HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'title',
        'alt',
        'user_id',
        'description',
        'path',
        'option'
    ];

    /**
     * The dates that should be mutated to instances of Carbon.
     *
     * @var array
     */
    protected $dates =[
        "created_at",
        "updated_at",
        "deleted_at"
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($media) {
            $media->user_id = Auth::id() ?? 1;
        });
    }

    // Define relationships
    public function user()
    {
        return $this->belongsTo(App\Models\User::class, 'user_id');
    }
}
