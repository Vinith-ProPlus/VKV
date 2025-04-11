<?php

namespace App\Http\Controllers\Admin\Users;

use App\Http\Controllers\Controller;
use App\Http\Requests\SupportTicketMessageRequest;
use App\Models\Document;
use App\Models\SupportTicket;
use App\Models\SupportTicketMessage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Random\RandomException;

class SupportTicketMessageController extends Controller
{
    /**
     * @param SupportTicketMessageRequest $request
     * @param SupportTicket $supportTicket
     * @return JsonResponse
     * @throws RandomException
     */
    public function storeMessage(SupportTicketMessageRequest $request, SupportTicket $supportTicket): JsonResponse
    {
        $messageData = [
            'support_ticket_id' => $supportTicket->id,
            'user_id' => Auth::id(),
            'message' => $request->message,
        ];

        $message = SupportTicketMessage::create($messageData);
        $documents = [];

        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $filename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME) .
                    '_' . now()->timestamp . '_' . random_int(1000, 9999) .
                    '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('documents', $filename, 'public');

                $documents[] = Document::create([
                    'title' => 'Support Ticket Attachment',
                    'description' => '',
                    'module_name' => 'Support-Message',
                    'module_id' => $message->id,
                    'file_path' => $path,
                    'file_name' => $filename,
                    'uploaded_by' => auth()->id(),
                ]);
            }
        }

        return response()->json([
            'id' => $message->id,
            'support_ticket_id' => $message->support_ticket_id,
            'user_id' => $message->user_id,
            'message' => $message->message,
            'documents' => collect($documents)->map(static fn($document) => [
                'id' => $document->id,
                'file_name' => $document->file_name,
                'file_path' => generate_file_url($document->file_path),
            ]),
            'created_at' => $message->created_at->toDateTimeString(),
        ]);
    }

    /**
     * @param SupportTicket $supportTicket
     * @param Request $request
     * @return JsonResponse
     */
    public function loadMessages(SupportTicket $supportTicket, Request $request): JsonResponse
    {
        $offset = $request->get('offset', 0);
        $messages = SupportTicketMessage::with('documents')->where('support_ticket_id', $supportTicket->id)
            ->orderBy('created_at', 'desc') // Latest messages first
            ->skip($offset)
            ->take(10)
            ->get()
            ->each(fn($message) => $message->documents->each(static function ($document) {
                $document->file_path = generate_file_url($document->file_path);
            }));

        return response()->json($messages);
    }
}
