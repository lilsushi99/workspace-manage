@extends('layouts.app')

@section('title', 'Review Withdrawal #' . $withdrawal->reference)

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Withdrawal Request Details</h1>
        <a href="{{ route('admin.withdrawals.index') }}" class="btn btn-outline-secondary">Back to List</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="row g-4">
        <div class="col-md-6">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-light fw-bold">Withdrawal Request Information</div>
                <div class="card-body">
                    <table class="table table-borderless mb-0">
                        <tr>
                            <th>Reference:</th>
                            <td><code>{{ $withdrawal->reference }}</code></td>
                        </tr>
                        <tr>
                            <th>Tenant:</th>
                            <td>{{ $withdrawal->tenant->name ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>Requested Amount:</th>
                            <td>${{ number_format($withdrawal->amount, 2) }}</td>
                        </tr>
                        <tr>
                            <th>Processing Fee:</th>
                            <td>${{ number_format($withdrawal->fee, 2) }}</td>
                        </tr>
                        <tr>
                            <th>Net Payout Amount:</th>
                            <td><strong class="text-success h5">${{ number_format($withdrawal->net_amount, 2) }}</strong></td>
                        </tr>
                        <tr>
                            <th>Status:</th>
                            <td>
                                <span class="badge bg-{{ $withdrawal->status === 'paid' ? 'success' : ($withdrawal->status === 'rejected' ? 'danger' : 'warning') }}">
                                    {{ ucfirst($withdrawal->status) }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <th>Requested At:</th>
                            <td>{{ $withdrawal->requested_at ? $withdrawal->requested_at->format('Y-m-d H:i:s') : $withdrawal->created_at->format('Y-m-d H:i:s') }}</td>
                        </tr>
                        @if($withdrawal->processed_at)
                            <tr>
                                <th>Processed At:</th>
                                <td>{{ $withdrawal->processed_at->format('Y-m-d H:i:s') }}</td>
                            </tr>
                            <tr>
                                <th>Processed By:</th>
                                <td>{{ $withdrawal->processedBy->email ?? 'System' }}</td>
                            </tr>
                        @endif
                        @if($withdrawal->rejection_reason)
                            <tr>
                                <th>Rejection Reason:</th>
                                <td class="text-danger">{{ $withdrawal->rejection_reason }}</td>
                            </tr>
                        @endif
                    </table>
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-header bg-light fw-bold">Destination Bank Details</div>
                <div class="card-body">
                    <table class="table table-borderless mb-0">
                        <tr>
                            <th>Bank Name:</th>
                            <td>{{ $withdrawal->bankAccount->bank_name ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>Account Name:</th>
                            <td>{{ $withdrawal->account_name }}</td>
                        </tr>
                        <tr>
                            <th>Account Number:</th>
                            <td><code>{{ $withdrawal->account_number }}</code></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            @if($withdrawal->status === 'pending' || $withdrawal->status === 'processing')
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-light fw-bold text-success">Approve Payout</div>
                    <div class="card-body">
                        <p class="text-muted">Approving will mark this withdrawal as paid and finalize ledger deductions.</p>
                        <form method="POST" action="{{ route('admin.withdrawals.approve', $withdrawal) }}">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label">Notes / Transaction Reference (Optional)</label>
                                <textarea name="notes" class="form-control" rows="2" placeholder="e.g., Bank Ref #123456"></textarea>
                            </div>
                            <button type="submit" class="btn btn-success w-100" onclick="return confirm('Are you sure you want to approve this withdrawal?')">Approve & Mark Paid</button>
                        </form>
                    </div>
                </div>

                <div class="card shadow-sm">
                    <div class="card-header bg-light fw-bold text-danger">Reject Withdrawal</div>
                    <div class="card-body">
                        <p class="text-muted">Rejecting will release the reserved pending balance back into the reseller's available wallet balance.</p>
                        <form method="POST" action="{{ route('admin.withdrawals.reject', $withdrawal) }}">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label">Rejection Reason (Required)</label>
                                <textarea name="rejection_reason" class="form-control" rows="3" required placeholder="Specify why the withdrawal request was rejected..."></textarea>
                            </div>
                            <button type="submit" class="btn btn-danger w-100" onclick="return confirm('Are you sure you want to reject this withdrawal and release reserved funds?')">Reject & Release Funds</button>
                        </form>
                    </div>
                </div>
            @else
                <div class="alert alert-info">
                    This withdrawal request has already been finalized with status: <strong>{{ ucfirst($withdrawal->status) }}</strong>.
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
