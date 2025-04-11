@extends('layouts.admin')

@section('content')
    @php
        $PageTitle = "Support Ticket";
        $ActiveMenuName = 'Support-Tickets';
    @endphp

    <style>
        .chat-box {
            height: 400px;
            overflow-y: auto;
            padding: 15px;
            background: #f8f9fa;
            display: flex;
            flex-direction: column;
        }
        .chat-message {
            max-width: 75%;
            padding: 10px;
            border-radius: 10px;
            margin-bottom: 10px;
            word-wrap: break-word;
        }
        .message-right {
            background: #007bff;
            color: white;
            align-self: flex-end;
        }
        .message-left {
            background: #e9ecef;
            color: black;
            align-self: flex-start;
        }
        .attachment-icon {
            position: relative;
            cursor: pointer;
            font-size: 20px;
            padding: 8px;
        }
        .attachment-count {
            position: absolute;
            top: -5px;
            right: 5px;
            background: red;
            color: white;
            font-size: 12px;
            border-radius: 50%;
            width: 18px;
            height: 18px;
            text-align: center;
            line-height: 18px;
            display: none;
        }
        #fileInput {
            display: none;
        }
        .btn-green {
            background-color: #51bb2596 !important;
            border-color: #51bb2596 !important;
            color: white;
        }
        .btn-green:hover {
            background-color: #45a220 !important;
            border-color: #45a220 !important;
            color: white;
            font-weight: 600;
        }
        .btn-blue {
            background-color: #655af3 !important;
            border-color: #655af3 !important;
            color: white;
        }

        .btn-blue:hover {
            background-color: #3947f3 !important;
            border-color: #3947f3 !important;
            color: white;
            font-weight: 600;
        }

    </style>

    <div class="container-fluid">
        <div class="row">
            <!-- Support Ticket Details Card -->
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header bg-primary text-white" style="font-weight: 700; background-color: #655af394 !important;">Ticket Details</div>
                    <div class="card-body">
                        <p><strong>Ticket #:</strong> {{ $supportTicket->ticket_number }}</p>
                        <p><strong>Support Type :</strong> {{ $support_type }}</p>
                        <p><strong>Name:</strong> {{ $user_name }}</p>
                        <p><strong>Subject:</strong> {{ $supportTicket->subject }}</p>
                        <p><strong>Created On:</strong> {{ \Carbon\Carbon::parse($supportTicket->created_at)->format('d-m-Y') }}</p>
                        <p><strong>Status:</strong> {{ ucfirst($supportTicket->status) }}</p>
                        @if($supportTicket->status !== CLOSED)
                            <p><button class="btn btn-danger" id="closeTicketBtn">Close Ticket</button></p>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Conversation View Card -->
            <div class="col-md-8">
                <div class="card mb-1">
                    <div class="card-header bg-success text-white" style="background-color: #51bb2596 !important; font-weight: 700;">Conversation</div>
                    <div class="card-body chat-box" id="chatBox"></div>
                </div>
                @if($supportTicket->status !== CLOSED)
                    <div class="card mt-3">
                        <div class="card-body" id="MessageDiv">
                            <form id="messageForm">
                                <div class="input-group">
                                    <input type="text" id="messageInput" class="form-control" placeholder="Type a message...">

                                    <!-- File Input (Hidden) -->
                                    <input type="file" id="fileInput" multiple>

                                    <!-- Attachment Icon -->
                                    <span class="attachment-icon" id="attachmentIcon">
                                        📎
                                        <span class="attachment-count" id="attachmentCount">0</span>
                                    </span>

                                    <button class="btn btn-blue" type="submit">Send</button>
                                </div>
                            </form>
                        </div>
                    </div>
                @else
                    <div class="card mt-3">
                        <div class="card-body">
                            <p class="text-center">This ticket is Closed.</p>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Close Ticket Confirmation Modal -->
    <div class="modal fade" id="closeTicketModal" tabindex="-1" aria-labelledby="closeTicketModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="closeTicketModalLabel">Confirm Ticket Closure</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Are you sure you want to close this support ticket? This action cannot be undone.
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" id="confirmCloseTicket">Yes, Close Ticket</button>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('script')
    <script>
        $(document).ready(function() {
            let offset = 0;
            let chatBox = $('#chatBox');
            let loading = false;
            let selectedFiles = [];
            let ticketId = "{{ $supportTicket->id }}";

            // Open Confirmation Modal
            $('#closeTicketBtn').on('click', function() {
                $('#closeTicketModal').modal('show');
            });

            // Handle Close Ticket Confirmation
            $('#confirmCloseTicket').on('click', function() {
                $.ajax({
                    url: `/admin/support_tickets/${ticketId}/close`,
                    method: 'POST',
                    headers: { 'X-CSRF-Token': $('meta[name="csrf-token"]').attr('content') },
                    success: function(response) {
                        if (response.success) {
                            $('#closeTicketModal').modal('hide');
                            $('#closeTicketBtn').remove(); // Remove close button
                            $('#messageForm').remove(); // Remove message input section
                            $('#MessageDiv').html(`<p class="text-center">This ticket is Closed.</p>`);
                        }
                    },
                    error: function() {
                        alert("Failed to close the ticket. Please try again.");
                    }
                });
            });

            function renderMessages(messages, append = true) {
                let html = '';
                messages.forEach(msg => {
                    let isCurrentUser = msg.user_id === {{ auth()->id() }};
                    let alignment = isCurrentUser ? 'align-items-end' : 'align-items-start';
                    let bubbleClass = isCurrentUser ? 'message-right' : 'message-left';
                    let docAlignment = isCurrentUser ? 'justify-content-end' : 'justify-content-start';

                    html = `
                        <div class="d-flex flex-column ${alignment}">
                            <div class="chat-message ${bubbleClass}">${msg.message}</div>
                    `;

                    if (msg.documents && msg.documents.length > 0) {
                        html += `<div class="mt-2 d-flex flex-wrap ${docAlignment}">`;

                        msg.documents.forEach(doc => {
                            html += `
                                <div class="document-box border p-2 m-1" style="min-width: 100px; text-align: center;">
                                    <p class="text-truncate" style="max-width: 150px; margin-top: 5px; margin-bottom: 5px;">${doc.file_name}</p>
                                    <a href="${doc.file_path}" target="_blank" class="btn btn-sm btn-blue">View</a>
                                    <a href="${doc.file_path}" download class="btn btn-sm btn-green">Download</a>
                                </div>
                            `;
                        });

                        html += `</div>`; // Close document container
                    }
                    html += `</div>`;

                    if (append) {
                        chatBox.append(html);
                    } else {
                        chatBox.prepend(html);
                    }
                });
            }

            function loadMessages(initialLoad = false, scrollBottom = false) {
                if (loading) return;
                loading = true;

                $.get(`/admin/support_tickets/${ticketId}/messages?offset=` + offset, function(data) {
                    if (data.length > 0) {
                        renderMessages(data, !initialLoad);
                        offset += data.length;
                    }
                    if (scrollBottom) {
                        chatBox.scrollTop(chatBox[0].scrollHeight);
                    }
                    loading = false;
                });
            }

            // Load initial 10 messages
            loadMessages(true, true);

            // Load older messages on scroll up
            chatBox.on('scroll', function() {
                if (chatBox.scrollTop() === 0) {
                    loadMessages(true);
                }
            });

            // File input trigger on attachment icon click
            $('#attachmentIcon').on('click', function() {
                $('#fileInput').click();
            });

            // Handle file selection
            $('#fileInput').on('change', function() {
                selectedFiles = Array.from(this.files);
                let count = selectedFiles.length;
                $('#attachmentCount').text(count).toggle(count > 0);
            });

            // Handle message submission
            $('#messageForm').on('submit', function(e) {
                e.preventDefault();
                let message = $('#messageInput').val();
                let formData = new FormData();

                if (message) {
                    formData.append('message', message);
                }

                selectedFiles.forEach((file, index) => {
                    formData.append(`attachments[${index}]`, file);
                });

                if (!message){
                    alert('Message is required');
                    return;
                }

                $.ajax({
                    url: `/admin/support_tickets/${ticketId}/messages`,
                    method: 'POST',
                    headers: {'X-CSRF-Token': $('meta[name=_token]').attr('content')},
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function (response) {
                        renderMessages([response], true);
                        $('#messageInput').val('');
                        $('#fileInput').val('');
                        selectedFiles = [];
                        $('#attachmentCount').hide();
                        chatBox.scrollTop(chatBox[0].scrollHeight);
                    }
                });
            });
        });
    </script>
@endsection
