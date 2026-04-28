@extends('layouts.admin')

@section('content')
    @php
        $PageTitle = 'Lead Followups';
        $ActiveMenuName = 'Followups';
    @endphp
    <div class="container-fluid">
        <div class="page-header">
            <div class="row">
                <div class="col-sm-12">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ url('/') }}" title=""><i
                                    class="f-16 fa fa-home"></i></a></li>
                        <li class="breadcrumb-item">CRM</li>
                        <li class="breadcrumb-item"><a href="{{ route('leads.index') }}">Leads</a></li>
                        <li class="breadcrumb-item">{{ $PageTitle }}</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
    <div class="container-fluid">
        <div class="row d-flex justify-content-center">
            <div class="col-12 col-sm-12 col-lg-10">
                <div class="card">
                    <div class="card-header text-center">
                        <div class="row">
                            <div class="col-sm-4"></div>
                            <div class="col-sm-4 my-2">
                                <h5>{{ $PageTitle }} - {{ $lead->name }}</h5>
                                <small class="text-muted">{{ $lead->mobile_number }}</small>
                            </div>
                            <div class="col-sm-4 my-2 text-right text-md-right">
                                <a href="{{ route('leads.index') }}" class="btn btn-sm btn-secondary">
                                    <i class="fa fa-arrow-left"></i> Back to Leads
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        @if($followups->count() > 0)
                            <div class="followup-list-container">
                                @foreach($followups as $index => $followup)
                                    @php
                                        $statusClass = match($followup->status) {
                                            'new' => 'primary',
                                            'under followup' => 'info',
                                            'visited' => 'secondary',
                                            'booked' => 'success',
                                            'sold' => 'success',
                                            'closed' => 'dark',
                                            default => 'secondary'
                                        };
                                        $statusText = ucfirst($followup->status);
                                        $isLatest = $index === 0 ? 'latest' : '';
                                    @endphp
                                    <div class="followup-list-item status-{{ $statusClass }} {{ $isLatest }}">
                                        <div class="followup-list-header">
                                            <div class="followup-list-info">
                                                <span class="followup-counter">{{ $followups->count() - $index }}</span>
                                                <div class="followup-list-title">
                                                    <h6 class="mb-1">{{ $followup->project->name ?? 'N/A' }}</h6>
                                                    <small class="text-muted">{{ $followup->created_at->format('d M Y, h:i A') }}</small>
                                                </div>
                                            </div>
                                            <div class="followup-list-badge">
                                                <span class="followup-status-badge badge-{{ $statusClass }}">{{ $statusText }}</span>
                                            </div>
                                        </div>
                                        <div class="followup-list-body">
                                            <div class="row">
                                                <div class="col-md-3">
                                                    <label class="followup-info-label">Project - Site</label>
                                                    <p class="followup-info-value">{{ $followup->project->name ?? 'N/A' }} - {{ $followup->site->site_no ?? 'Not Specified' }}</p>
                                                </div>
                                                <div class="col-md-9">
                                                    <label class="followup-info-label">Remarks</label>
                                                    <p class="followup-info-value remarks-text">{{ $followup->remarks ?? 'No remarks' }}</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center" role="alert">
                                <i class="fa fa-info-circle"></i> No followups found for this lead.
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('style')
    <style>
        .followup-list-container {
            display: flex;
            flex-direction: column;
            gap: 15px;
            padding: 10px 0;
        }

        .followup-list-item {
            background: white;
            border-left: 4px solid #007bff;
            border-radius: 6px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            padding: 0;
        }

        .followup-list-item.status-info {
            border-left-color: #17a2b8;
        }

        .followup-list-item.status-success {
            border-left-color: #28a745;
        }

        .followup-list-item.status-dark {
            border-left-color: #343a40;
        }

        .followup-list-item.status-secondary {
            border-left-color: #6c757d;
        }

        .followup-list-item.latest {
            border-left: 5px solid #28a745;
            box-shadow: 0 4px 16px rgba(40, 167, 69, 0.2);
            background: #f8fff9;
        }

        .followup-list-item:hover {
            box-shadow: 0 6px 16px rgba(0, 0, 0, 0.12);
            transform: translateX(4px);
        }

        .followup-list-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 16px;
            border-bottom: 1px solid #f0f0f0;
        }

        .followup-list-info {
            display: flex;
            align-items: flex-start;
            gap: 15px;
            flex: 1;
        }

        .followup-counter {
            background: linear-gradient(135deg, #007bff, #0056b3);
            color: white;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 16px;
            box-shadow: 0 2px 8px rgba(0, 123, 255, 0.3);
            flex-shrink: 0;
        }

        .followup-list-item.latest .followup-counter {
            background: linear-gradient(135deg, #28a745, #1e7e34);
            box-shadow: 0 2px 8px rgba(40, 167, 69, 0.4);
        }

        .followup-list-title {
            flex: 1;
        }

        .followup-list-title h6 {
            font-size: 16px;
            font-weight: 600;
            color: #343a40;
            margin: 0;
        }

        .followup-list-badge {
            margin-left: 10px;
        }

        .followup-status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            white-space: nowrap;
            color: white;
        }

        .badge-primary {
            background-color: #007bff;
        }

        .badge-info {
            background-color: #17a2b8;
        }

        .badge-secondary {
            background-color: #6c757d;
        }

        .badge-success {
            background-color: #28a745;
        }

        .badge-dark {
            background-color: #343a40;
        }

        .followup-list-body {
            padding: 16px;
        }

        .followup-info-label {
            font-size: 11px;
            font-weight: 700;
            color: #6c757d;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 4px;
            display: block;
        }

        .followup-info-value {
            font-size: 14px;
            color: #343a40;
            word-break: break-word;
            margin: 0;
        }

        .remarks-text {
            color: #495057;
            line-height: 1.5;
            font-style: italic;
            max-height: 120px;
            overflow-y: auto;
        }

        @media (max-width: 768px) {
            .followup-list-header {
                flex-direction: column;
                align-items: flex-start;
                padding: 12px;
            }

            .followup-list-info {
                width: 100%;
                margin-bottom: 10px;
            }

            .followup-list-badge {
                margin-left: 0;
            }

            .followup-list-body {
                padding: 12px;
            }
        }
    </style>
@endsection
