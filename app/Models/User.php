<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

#[Fillable([
    'name',
    'first_name',
    'last_name',
    'display_name',
    'email',
    'phone',
    'password',
    'avatar_path',
    'status',
    'last_login_at',
    'last_login_ip',
])]
#[Hidden([
    'password',
    'remember_token',
])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens;

    use HasFactory;
    use HasRoles;
    use Notifiable;

    /**
     * Get the customer profile associated with the user.
     */
    public function customerProfile(): HasOne
    {
        return $this->hasOne(CustomerProfile::class);
    }

    /**
     * Get the user's saved addresses.
     */
    public function addresses(): HasMany
    {
        return $this->hasMany(Address::class);
    }

    /**
     * Get the user's registered devices.
     */
    public function devices(): HasMany
    {
        return $this->hasMany(UserDevice::class);
    }

    /**
     * Get the user's application preferences.
     */
    public function preference(): HasOne
    {
        return $this->hasOne(UserPreference::class);
    }

    /**
     * Get the internal notes attached to this customer.
     */
    public function customerNotes(): HasMany
    {
        return $this->hasMany(
            CustomerNote::class,
            'user_id',
        );
    }

    /**
     * Get the notes created by this staff user.
     */
    public function createdCustomerNotes(): HasMany
    {
        return $this->hasMany(
            CustomerNote::class,
            'created_by',
        );
    }

    /**
     * Determine whether the account can access the admin dashboard.
     */
    public function canAccessDashboard(): bool
    {
        return $this->status === 'active'
            && $this->hasAnyRole([
                'super_admin',
                'admin',
                'product_manager',
                'order_manager',
                'customer_support',
            ]);
    }

    /**
     * Return the user's preferred display name.
     */
    public function getFullNameAttribute(): string
    {
        $fullName = trim(
            "{$this->first_name} {$this->last_name}",
        );

        return $this->display_name
            ?: ($fullName !== '' ? $fullName : $this->name);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'phone_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
