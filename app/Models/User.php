<?php

namespace App\Models;

use App\Enums\UserRole;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'role', 'phone', 'country', 'avatar', 'status'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, SoftDeletes;

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
        ];
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    /** Admins and managers both work in the panel. */
    public function isStaff(): bool
    {
        return $this->role->canAccessAdmin();
    }

    /**
     * Admins only. Guards the three things a manager must not do: touch staff
     * accounts, rewrite the homepage, or delete anything.
     */
    public function isAdmin(): bool
    {
        return $this->role === UserRole::Admin;
    }

    /** Staff who can still log in — the people worth notifying about desk work. */
    public function scopeStaff(Builder $query): Builder
    {
        return $query->where('role', '!=', UserRole::Customer)->where('status', 'active');
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        return $query->when($term, fn (Builder $q) => $q->where(function (Builder $q) use ($term) {
            $q->where('name', 'like', "%{$term}%")
                ->orWhere('email', 'like', "%{$term}%")
                ->orWhere('country', 'like', "%{$term}%");
        }));
    }

    public function getAvatarUrlAttribute(): string
    {
        return $this->avatar
            ? asset($this->avatar)
            : asset('assets/images/teams/user-0'.(($this->id % 8) + 1).'.jpg');
    }

    public function getInitialsAttribute(): string
    {
        return collect(explode(' ', $this->name))
            ->take(2)
            ->map(fn (string $part) => mb_strtoupper(mb_substr($part, 0, 1)))
            ->implode('');
    }
}
