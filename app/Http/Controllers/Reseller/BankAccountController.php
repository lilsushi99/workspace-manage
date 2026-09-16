<?php

namespace App\Http\Controllers\Reseller;

use App\Http\Controllers\Controller;
use App\Models\BankAccount;
use App\Services\Bank\Contracts\BankVerificationInterface;
use Illuminate\Http\Request;

class BankAccountController extends Controller
{
    protected BankVerificationInterface $bankVerifier;

    public function __construct(BankVerificationInterface $bankVerifier)
    {
        $this->bankVerifier = $bankVerifier;
    }

    public function index(Request $request)
    {
        $tenant = $request->user()->tenant;
        if (!$tenant) {
            return redirect()->route('onboarding.index');
        }

        $bankAccounts = BankAccount::where('tenant_id', $tenant->id)->get();
        return view('reseller.banks.index', compact('bankAccounts'));
    }

    public function create()
    {
        return view('reseller.banks.create');
    }

    public function store(Request $request)
    {
        $tenant = $request->user()->tenant;
        if (!$tenant) {
            return redirect()->route('onboarding.index');
        }

        $request->validate([
            'bank_name' => ['required', 'string', 'max:255'],
            'bank_code' => ['required', 'string', 'max:50'],
            'account_number' => ['required', 'string', 'max:50'],
        ]);

        $resolved = $this->bankVerifier->resolveAccountName($request->bank_code, $request->account_number);

        $isFirst = !BankAccount::where('tenant_id', $tenant->id)->exists();

        BankAccount::create([
            'tenant_id' => $tenant->id,
            'bank_code' => $request->bank_code,
            'bank_name' => $request->bank_name,
            'account_number' => $request->account_number,
            'account_name' => $resolved['account_name'],
            'verification_status' => $resolved['status'],
            'verified_at' => now(),
            'is_default' => $isFirst,
        ]);

        return redirect()->route('reseller.banks.index')->with('success', 'Bank account verified and added successfully.');
    }

    public function setDefault(Request $request, BankAccount $bankAccount)
    {
        $tenant = $request->user()->tenant;
        if (!$tenant || $bankAccount->tenant_id !== $tenant->id) {
            abort(403);
        }

        BankAccount::where('tenant_id', $tenant->id)->update(['is_default' => false]);
        $bankAccount->update(['is_default' => true]);

        return back()->with('success', 'Default bank account updated.');
    }
}
