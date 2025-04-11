@extends('layouts.admin')

@section('content')
    @php
        $PageTitle = "Support Ticket";
        $ActiveMenuName = 'Support-Tickets';
    @endphp

    <div class="container-fluid">
        <div class="page-header">
            <div class="row">
                <div class="col-sm-12">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ url('/') }}"><i class="f-16 fa fa-home"></i></a></li>
                        <li class="breadcrumb-item">User</li>
                        <li class="breadcrumb-item">{{$PageTitle}}</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="container-fluid">
        <div class="row d-flex justify-content-center">
            <div class="col-12 col-lg-12">
                <div class="card">
                    <div class="card-header text-center">
                        <h5>{{ $support_ticket ? 'Edit' : 'Create' }} {{$PageTitle}}</h5>
                    </div>
                    <div class="card-body">
                        <form action="{{ $support_ticket ? route('support_tickets.update', $support_ticket->id) : route('support_tickets.store') }}" method="POST">
                            @csrf
                            @if($support_ticket)
                                @method('PUT')
                            @endif

                            <div class="row mt-10">
                                <div class="col-6">
                                    <div class="form-group">
                                        <label>Support Type</label>
                                        <select name="support_type" id="support_type" class="form-control select2 @error('support_type') is-invalid @enderror"
                                                data-selected='{{ $support_ticket ? old('support_type', $support_ticket->support_type) : old('support_type') }}' required>
                                            <option value="">Select a Support type</option>
                                        </select>
                                        @error('support_type')
                                        <div class="text-danger mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="form-group">
                                        <label>Support Ticket For</label>
                                        <select name="user_id" id="user_id" class="form-control select2 @error('user_id') is-invalid @enderror"
                                                data-selected='{{ $support_ticket ? old('user_id', $support_ticket->user_id) : old('user_id') }}' required>
                                            <option value="">Select a User</option>
                                        </select>
                                        @error('user_id')
                                        <div class="text-danger mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row mt-10">
                                <div class="col-12">
                                    <div class="form-group">
                                        <label>Subject</label>
                                        <input type="text" name="subject" class="form-control"
                                               value="{{ old('subject', $support_ticket->subject ?? '') }}" required>
                                        @error('subject')
                                        <div class="text-danger mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="row mt-10">
                                <div class="col-12">
                                    <div class="form-group">
                                        <label>Description</label>
                                        <textarea name="message" class="form-control">{{ old('message', $support_ticket->message ?? '') }}</textarea>
                                        @error('message')
                                        <div class="text-danger mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-12 mt-10">
                                    <div class="form-group">
                                        <label>Status</label>
                                        <select name="status" id="status" class="form-control select2 @error('status') is-invalid @enderror" required>
                                            <option value="">Select a Status</option>
                                            @foreach(SUPPORT_TICKET_STATUSES as $status)
                                                <option value="{{ $status }}" {{ $status === ($support_ticket ? old('status', $support_ticket->status) : old('status')) ? 'selected' : '' }}>{{ $status }}</option>
                                            @endforeach
                                        </select>
                                        @error('status')
                                        <div class="text-danger mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row mt-15 text-end">
                                <div>
                                    <a href="javascript:void(0)" onclick="window.history.back()" class="btn btn-warning">Back</a>
                                    <button type="submit" class="btn btn-primary">{{ $support_ticket ? 'Update' : 'Save' }}</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        $(document).ready(function () {
            $('.select2').select2();

            function fetchOptions(url, selectElement) {
                let selectedValue = selectElement.attr('data-selected');

                $.ajax({
                    url: url,
                    type: 'GET',
                    dataType: 'json',
                    success: function (response) {
                        updateSelectBox(selectElement, response, selectedValue);
                    },
                    error: function () {
                        console.error(`Error fetching data from ${url}`);
                    }
                });
            }

            function updateSelectBox(selectElement, data, selectedValue) {
                let options = '<option value="">Select an Option</option>';
                data.forEach(item => {
                    options += `<option value="${item.id}" ${item.id == selectedValue ? 'selected' : ''}>${item.name}</option>`;
                });

                selectElement.html(options).select2();
            }

            fetchOptions("{{ route('getSupportTypes') }}", $('#support_type'));
            fetchOptions("{{ route('getUsers') }}", $('#user_id'));
        });

    </script>
@endsection
