@extends('layouts.app')

@section('title', 'Withdrawal Requests')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Withdrawal Requests (Finance)</h1>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if($errors->has('withdrawal'))
        <div class="alert alert-danger">{{ $errors->first('withdrawal') }}</div>
    @endif

    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.withdrawals.index') }}" class="row g-3">
                <div class="col-md-4">
                    <input type="text" name="search" class="form-control" placeholder="Search reference..." value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <select name="status" class="form-select">
                        <option value="">All Statuses</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="processing" {{ request('status') === 'processing' ? 'selected' : '' }}>Processing</option>
                        <option value="paid" {{ request('status') === 'paid' ? 'selected' : '' }}>Paid</option>
                        <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-secondary w-100">Filter</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Ref</th>
                            <th>Tenant</th>
                            <th>Amount</th>
                            <th>Fee</th>
                            <th>Net Amount</th>
                            <th>Bank</th>
                            <th>Status</th>
                            <th>Requested At</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($withdrawals as $withdrawal)
                            <tr>
                                <td><code>{{ $withdrawal->reference }}</code></td>
                                <td>{{ $withdrawal->tenant->name ?? 'N/A' }}</td>
                                <td>${{ number_format($withdrawal->amount, 2) }}</td>
                                <td>${{ number_format($withdrawal->fee, 2) }}</td>
                                <td><strong>${{ number_format($withdrawal->net_amount, 2) }}</strong></td>
                                <td>
                                    {{ $withdrawal->account_name }}<br>
                                    <small class="text-muted">{{ $withdrawal->account_number }}</small>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $withdrawal->status === 'paid' ? 'success' : ($withdrawal->status === 'rejected' ? 'danger' : 'warning') }}">
                                        {{ ucfirst($withdrawal->status) }}
                                    </span>
                                </td>
                                <td>{{ $withdrawal->requested_at ? $withdrawal->requested_at->format('Y-m-d H:i') : $withdrawal->created_at->format('Y-m-d H:i') }}</td>
                                <td>
                                    <a href="{{ route('admin.withdrawals.show', $withdrawal) }}" class="btn btn-sm btn-outline-primary">Review</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-4 text-muted">No withdrawal requests found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="p-3">
                {{ $withdrawals->withQueryString()->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
