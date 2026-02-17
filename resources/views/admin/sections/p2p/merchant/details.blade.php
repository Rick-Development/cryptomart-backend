@extends('admin.layouts.master')

@section('page-title')
    @include('admin.components.page-title',['title' => __($page_title)])
@endsection

@section('breadcrumb')
    @include('admin.components.breadcrumb',['breadcrumbs' => [
        [
            'name'  => __("Dashboard"),
            'url'   => setRoute("admin.dashboard"),
        ],
        [
            'name'  => __("Merchant Applications"),
            'url'   => setRoute("admin.p2p.merchant.applications.index"),
        ]
    ], 'active' => __("Details")])
@endsection

@section('content')
    <div class="row mb-30-none">
        <div class="col-lg-6 mb-30">
            <div class="card">
                <div class="card-header bg--primary">
                    <h5 class="card-title text-white">{{ __("Applicant Details") }}</h5>
                </div>
                <div class="card-body">
                    <ul class="list-group">
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            {{ __("Full Name") }}
                            <span class="fw-bold">{{ $application->user->fullname }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            {{ __("Email") }}
                            <span class="fw-bold">{{ $application->email }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            {{ __("Phone") }}
                            <span class="fw-bold">{{ $application->phone }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            {{ __("WhatsApp") }}
                            <span class="fw-bold">{{ $application->whatsapp ?? 'N/A' }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            {{ __("Business Name") }}
                            <span class="fw-bold">{{ $application->business_name ?? 'N/A' }}</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="col-lg-6 mb-30">
            <div class="card">
                <div class="card-header bg--primary">
                    <h5 class="card-title text-white">{{ __("Balance Verification") }}</h5>
                </div>
                <div class="card-body">
                    <ul class="list-group">
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            {{ __("USDT Balance (Quidax)") }}
                            <span class="fw-bold">{{ get_amount($application->quidax_usdt_balance, 'USDT') }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            {{ __("Minimum Required") }}
                            <span class="fw-bold">{{ get_amount($application->min_usdt_required, 'USDT') }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            {{ __("Verified At") }}
                            <span class="fw-bold">{{ $application->balance_verified_at->format('d M Y, h:i A') }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            {{ __("Status") }}
                            @php
                                $statusClass = match($application->status) {
                                    'pending' => 'badge--warning',
                                    'approved' => 'badge--success',
                                    'rejected' => 'badge--danger',
                                    default => 'badge--primary'
                                };
                            @endphp
                            <span class="badge {{ $statusClass }}">{{ ucfirst($application->status) }}</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    @if($application->status === 'pending')
    <div class="row mb-30">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header bg--primary">
                    <h5 class="card-title text-white">{{ __("Action") }}</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-end gap-3">
                        <button type="button" class="btn btn--danger" data-bs-toggle="modal" data-bs-target="#rejectModal">{{ __("Reject") }}</button>
                        <button type="button" class="btn btn--success" data-bs-toggle="modal" data-bs-target="#approveModal">{{ __("Approve") }}</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Approve Modal --}}
    <div class="modal fade" id="approveModal" tabindex="-1" aria-labelledby="approveModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form action="{{ setRoute('admin.p2p.merchant.applications.approve', $application->id) }}" method="POST">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="approveModalLabel">{{ __("Approve Application") }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p>{{ __("Are you sure you want to approve this merchant application? The user will be granted merchant status.") }}</p>
                        <div class="form-group">
                            <label>{{ __("Admin Notes (Optional)") }}</label>
                            <textarea name="admin_notes" class="form-control" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn--secondary" data-bs-dismiss="modal">{{ __("Cancel") }}</button>
                        <button type="submit" class="btn btn--success">{{ __("Approve") }}</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Reject Modal --}}
    <div class="modal fade" id="rejectModal" tabindex="-1" aria-labelledby="rejectModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form action="{{ setRoute('admin.p2p.merchant.applications.reject', $application->id) }}" method="POST">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="rejectModalLabel">{{ __("Reject Application") }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p>{{ __("Are you sure you want to reject this application?") }}</p>
                        <div class="form-group">
                            <label>{{ __("Reason for Rejection") }} *</label>
                            <textarea name="reason" class="form-control" rows="3" required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn--secondary" data-bs-dismiss="modal">{{ __("Cancel") }}</button>
                        <button type="submit" class="btn btn--danger">{{ __("Reject") }}</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection
