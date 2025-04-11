<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\SupportTicketMessageRequest;
use App\Http\Requests\SupportTicketRequest;
use App\Models\Document;
use App\Models\SupportTicket;
use App\Models\SupportTicketMessage;
use App\Models\SupportType;
use App\Traits\ApiResponse;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Random\RandomException;
use Throwable;

class SupportTicketController extends Controller
{
    use ApiResponse;
    /**
     * Record User Check-in/Check-out
     */

    public function createTicket(Request $request, SupportTicket $supportTicket): JsonResponse
    {
        DB::beginTransaction();
        try {
            $request->validate([
                'subject'      => 'required|string|max:255',
                'support_type' => 'required|exists:support_types,id',
                'message' => 'required|string|max:255'
            ]);
            $ticket = SupportTicket::create([
                'user_id'        => auth()->id(),
                'subject'        => $request->subject,
                'support_type_id'=> $request->support_type,
                'status'         => OPEN,
            ]);
            $message = SupportTicketMessage::create([
                'support_ticket_id' => $ticket->id,
                'user_id'           => auth()->id(),
                'message'           => $request->message,
            ]);
            $documents = [];

            if ($request->hasFile('attachments')) {
                $files = is_array($request->file('attachments'))
                    ? $request->file('attachments')
                    : [$request->file('attachments')];

                foreach ($files as $file) {
                    $filename = $this->generateUniqueFileName($file);
                    $path = $file->storeAs('documents', $filename, 'public');
                    $documents[] = Document::create([
                        'title'        => 'Support Ticket Attachment',
                        'description'  => '',
                        'module_name'  => 'Support-Message',
                        'module_id'    => $message->id,
                        'file_path'    => $path,
                        'file_name'    => $filename,
                        'uploaded_by'  => auth()->id(),
                    ]);
                }
            }

            $response = [
                'id'                => $message->id,
                'support_ticket_id' => $message->support_ticket_id,
                'user_id'           => $message->user_id,
                'message'           => $message->message,
                'documents'         => collect($documents)->map(static fn($document) => [
                    'id'        => $document->id,
                    'file_name' => $document->file_name,
                    'file_path' => generate_file_url($document->file_path),
                ]),
                'created_at'        => $message->created_at->toDateTimeString(),
            ];

            DB::commit();
            return $this->successResponse($response, "Support Ticket created successfully!");
        } catch (Exception $exception) {
            DB::rollBack();
            Log::error("Error::SupportTicketController@createTicket - " . $exception->getMessage());
            return $this->errorResponse($exception->getMessage(), "Failed to create support ticket!", 500);
        }
    }

    /**
     * @param SupportTicketMessageRequest $request
     * @param SupportTicket $supportTicket
     * @return JsonResponse
     */
    public function storeMessage(Request $request, SupportTicket $supportTicket): JsonResponse
    {
        DB::beginTransaction();
        try {
            $request->validate([
                'message' => 'required|string|max:5000',
            ]);

            $message = SupportTicketMessage::create([
                'support_ticket_id' => $supportTicket->id,
                'user_id'           => auth()->id(),
                'message'           => $request->message,
            ]);
            $documents = [];
            if ($request->hasFile('attachments')) {
                $files = is_array($request->file('attachments')) ? $request->file('attachments') : [$request->file('attachments')];
                foreach ($files as $file) {
                    $filename = $this->generateUniqueFileName($file);
                    $path = $file->storeAs('documents', $filename, 'public');
                    $documents[] = Document::create([
                        'title'       => 'Support Ticket Attachment',
                        'description' => '',
                        'module_name' => 'Support-Message',
                        'module_id'   => $message->id,
                        'file_path'   => $path,
                        'file_name'   => $filename,
                        'uploaded_by' => auth()->id(),
                    ]);
                }
            }

            $response = [
                'id'                => $message->id,
                'support_ticket_id' => $message->support_ticket_id,
                'user_id'           => auth()->id(),
                'message'           => $message->message,
                'documents'         => collect($documents)->map(fn ($document) => [
                    'id'        => $document->id,
                    'file_name' => $document->file_name,
                    'file_path' => generate_file_url($document->file_path),
                ]),
                'created_at'        => $message->created_at->toDateTimeString(),
            ];
            DB::commit();
            return $this->successResponse($response, "Support Ticket Message created successfully!");
        } catch (Exception $exception) {
            DB::rollBack();
            Log::error("Error::SupportTicketController@storeMessage - " . $exception->getMessage(), [
                'support_ticket_id' => $supportTicket->id, 'user_id' => auth()->id(), 'request' => $request->all(),
            ]);
            return $this->errorResponse($exception->getMessage(), "Failed to create support ticket message!", 500);
        }
    }

    /**
     * @param $file
     * @return string
     * @throws RandomException
     */
    private function generateUniqueFileName($file): string
    {
        return pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME) .
            '_' . now()->timestamp . '_' . random_int(1000, 9999) .
            '.' . $file->getClientOriginalExtension();
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getSupportTicketsHistory(Request $request): JsonResponse
    {
        try {
            $user = auth()->user();
            $query = SupportTicket::where('user_id', $user->id)->with(['user:id,name', 'support_type:id,name'])->orderByDesc('created_at');
            $query = dataFilter($query, $request);
            return $this->successResponse(dataFormatter($query), "Support Ticket fetched successfully!");
        } catch (Throwable $exception) {
            Log::error('Error::SupportTicketController@getSupportTicketsHistory - ' . $exception->getMessage());
            return $this->errorResponse($exception->getMessage(), "Failed to fetch support ticket history!", 500);
        }
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getSupportTypes(Request $request): JsonResponse
    {
        $query = SupportType::where('is_active', 1);
        $query = dataFilter($query, $request, ['name']);
        return $this->successResponse(dataFormatter($query), "Support Type fetched successfully!");
    }
    /**
     * Get User's Attendance History
     */
    public function getSupportTicketMessages(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'support_ticket_id' => ['required', Rule::exists('support_tickets', 'id')]
            ]);
            $query = SupportTicketMessage::with(['user:name', 'ticket', 'documents'])
                ->where('support_ticket_id', $request->support_ticket_id)
                ->latest();
            $query = dataFilter($query, $request);
            $query->getCollection()->transform(static function ($message) {
                $message->documents->transform(static function ($document) {
                    $document->file_path = generate_file_url($document->file_path);
                    return $document;
                });
                return $message;
            });
            return $this->successResponse(dataFormatter($query), "Support Ticket Messages fetched successfully!");
        } catch (Throwable $exception) {
            Log::error('Error::SupportTicketController@getSupportTicketMessages - ' . $exception->getMessage());
            return $this->errorResponse($exception->getMessage(), "Failed to fetch support ticket messages!", 500);
        }
    }
}
