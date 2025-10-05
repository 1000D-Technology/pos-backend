<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Cache;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class);
    }

    public function hasPermissionTo(string $permissionSlug): bool
    {
        $permissions = Cache::remember(
            key: "user.{$this->id}.permissions", // A unique cache key for this user
            // ttl: now()->addHour(), // How long to cache (e.g., 1 hour)
            ttl: now()->addSeconds(10), //for dev stage cache (e.g., 1 hour)
            callback: fn() => $this->permissions()->pluck('slug')->toArray() // The data to cache
        );

        return in_array($permissionSlug, $permissions);
    }

    /**
     * Clear the user's permission cache.
     */
    public function clearPermissionCache(): void
    {
        Cache::forget("user.{$this->id}.permissions");
    }

    public function salaries(): HasMany
    {
        return $this->hasMany(Salary::class);
    }
}
