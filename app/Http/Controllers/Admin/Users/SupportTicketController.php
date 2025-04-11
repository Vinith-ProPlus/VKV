<?php

namespace App\Http\Controllers\Admin\Users;

use App\Http\Controllers\Controller;
use App\Http\Requests\SupportTicketRequest;
use App\Models\Document;
use App\Models\SupportTicket;
use App\Models\SupportTicketMessage;
use App\Models\SupportType;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;
use function Laravel\Prompts\warning;

class SupportTicketController extends Controller{
    use AuthorizesRequests;

    /**
     * @param Request $request
     * @return Factory|Application|View|JsonResponse
     * @throws AuthorizationException
     */
    public function index(Request $request): Factory|Application|View|JsonResponse
    {
        $this->authorize('View Support Tickets');

        if ($request->ajax()) {
            $query = SupportTicket::with('user', 'support_type')->withTrashed()->orderByDesc('created_at')
                ->when($request->get('support_type_id'), static fn($q) => $q->where('support_type_id', $request->support_type_id))
                ->when($request->get('user_id'), static fn($q) => $q->where('user_id', $request->user_id))
                ->when($request->get('status'), static fn($q) => $q->where('status', $request->status))
                ->when($request->get('from_date'), static fn($q) => $q->whereDate('created_at', '>=', $request->from_date))
                ->when($request->get('to_date'), static fn($q) => $q->whereDate('created_at', '<=', $request->to_date))
                ->get();

            return DataTables::of($query)
                ->addIndexColumn()
                ->editColumn('support_type', static fn($data) => $data->support_type?->name)
                ->editColumn('user_name', static fn($data) => $data->user?->name)
                ->editColumn('created_on', static fn($data) => Carbon::parse($data->created_at)->format('d-m-Y'))
                ->addColumn('action', static function ($data) {
                    $button = '<div class="d-flex justify-content-center">';
                    if ($data->deleted_at) {
                        $button .= '<a onclick="commonRestore(\'' . route('support_tickets.restore', $data->id) . '\')" class="btn btn-outline-warning"><i class="fa fa-undo"></i></a>';
                    } else {
                        $button .= '<a href="' . route('support_tickets.show', $data->id) . '" class="btn btn-outline-success btn-sm m-1"><i class="fa fa-eye" aria-hidden="true"></i></a>';
                        $button .= '<a onclick="commonDelete(\'' . route('support_tickets.destroy', $data->id) . '\')"  class="btn btn-outline-danger btn-sm m-1"><i class="fa fa-trash" style="color: red"></i></a>';
                    }
                    $button .= '</div>';
                    return $button;
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        return view('support_tickets.index');
    }

    /**
     * @return View|Factory|Application
     * @throws AuthorizationException
     */
    public function create(): View|Factory|Application
    {
        $this->authorize('Create Support Tickets');
        return view('support_tickets.data', ['support_ticket' => '']);
    }

    /**
     * @param SupportTicketRequest $request
     * @return RedirectResponse
     * @throws AuthorizationException
     */
    public function store(SupportTicketRequest $request): RedirectResponse
    {
        $this->authorize('Create Support Tickets');
        DB::beginTransaction();
        try {
            $ticket = SupportTicket::create([
                'user_id'       => $request->user_id,
                'subject'       => $request->subject,
                'support_type_id'  => $request->support_type,
                'status'        => $request->status,
            ]);
            $message = SupportTicketMessage::create([
                'support_ticket_id' => $ticket->id,
                'user_id'           => auth()->id(),
                'message'           => $request->message,
            ]);
            Document::where('module_name', 'User-Support-Message')->where('module_id', Auth::id())
                ->update(['module_name' => 'Support-Message', 'module_id' => $message->id]);
            DB::commit();
            return redirect()->route('support_tickets.index')->with('success', 'Support Ticket created successfully.');
        } catch (Exception $exception) {
            DB::rollBack();
            $ErrMsg = $exception->getMessage();
            warning('Error::Place@SupportTicketController@store - ' . $ErrMsg);
            return redirect()->back()->withInput()->with("warning", "Something went wrong" . $ErrMsg);
        }
    }

    /**
     * @param SupportTicket $supportTicket
     * @return View|Factory|Application
     */
    public function show(SupportTicket $supportTicket): View|Factory|Application
    {
        $user_name = User::find($supportTicket->user_id)->first()?->name;
        $support_type = SupportType::find($supportTicket->support_type_id)->first()?->name;
        return view('support_tickets.show', compact('supportTicket', 'user_name', 'support_type'));
    }

    public function updateStatus(SupportTicket $supportTicket, Request $request): RedirectResponse
    {
        $supportTicket->update(['status' => $request->status]);
        return back()->with('success', 'Ticket Status updated successfully.');
    }

    /**
     * @param $id
     * @return Application|Response|RedirectResponse|ResponseFactory
     * @throws AuthorizationException
     */
    public function destroy($id): Application|Response|RedirectResponse|ResponseFactory
    {
        $this->authorize('Delete Support Tickets');
        try {
            $ticket = SupportTicket::findOrFail($id);
            $ticket->delete();
            return response(['status' => 'warning', 'message' => 'Support Ticket deleted Successfully!']);
        } catch (Exception $exception) {
            info('Error::Place@SupportTicketController@destroy - ' . $exception->getMessage());
            return redirect()->back()->with("warning", "Something went wrong" . $exception->getMessage());
        }
    }

    /**
     * @param $id
     * @return Application|Response|RedirectResponse|ResponseFactory
     * @throws AuthorizationException
     */
    public function restore($id): Application|Response|RedirectResponse|ResponseFactory
    {
        $this->authorize('Restore Support Tickets');
        try {
            SupportTicket::withTrashed()->findOrFail($id)?->restore();
            return response(['status' => 'success', 'message' => 'Support Ticket restored Successfully!']);
        } catch (Exception $exception) {
            info('Error::Place@SupportTicketController@restore - ' . $exception->getMessage());
            return redirect()->back()->with("warning", "Something went wrong" . $exception->getMessage());
        }
    }

    /**
     * @param SupportTicket $supportTicket
     * @return JsonResponse
     */
    public function close(SupportTicket $supportTicket): JsonResponse
    {
        $supportTicket->update(['status' => CLOSED]);

        return response()->json(['success' => true, 'message' => 'Ticket closed successfully.']);
    }
}
