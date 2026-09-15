<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tenant extends Model
{
    use HasFactory;

    protected $fillable = [
        'owner_user_id',
        'name',
        'slug',
        'status',
        'currency',
        'timezone',
        'onboarding_step',
        'onboarding_completed_at',
    ];

    protected function casts(): array
    {
        return [
            'onboarding_completed_at' => 'datetime',
        ];
    }

    public function isOnboardingComplete(): bool
    {
        return $this->onboarding_completed_at !== null;
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_user_id');
    }

    public function stores()
    {
        return $this->hasMany(Store::class);
    }

    public function customDomains()
    {
        return $this->hasMany(CustomDomain::class);
    }

    public function settings()
    {
        return $this->hasMany(TenantSetting::class);
    }

    public function services()
    {
        return $this->hasMany(TenantService::class);
    }

    public function customers()
    {
        return $this->hasMany(Customer::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function wallet()
    {
        return $this->hasOne(Wallet::class);
    }

    public function walletTransactions()
    {
        return $this->hasMany(WalletTransaction::class);
    }

    public function bankAccounts()
    {
        return $this->hasMany(BankAccount::class);
    }

    public function withdrawals()
    {
        return $this->hasMany(Withdrawal::class);
    }

    public function supportTickets()
    {
        return $this->hasMany(SupportTicket::class);
    }

    public function auditLogs()
    {
        return $this->hasMany(AuditLog::class);
    }

    public function getSetting(string $key, $default = null)
    {
        $setting = $this->settings->firstWhere('key', $key);
        return $setting ? $setting->value : $default;
    }

    public function setSetting(string $key, $value): TenantSetting
    {
        return $this->settings()->updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );
    }
}
