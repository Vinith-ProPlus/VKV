<?php

namespace App\Http\Controllers\web\Settings;

use App\enums\activeMenuNames;
use App\Http\Controllers\Controller;
use App\Http\Controllers\web\masters\general\Exception;
use App\enums\cruds;
use Illuminate\Support\Facades\DB;
use App\Models\DocNum;
use App\enums\docTypes;
use App\Models\general;
use App\helper\helper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use logs;
use SSP;
use App\Rules\ValidUnique;

class ChatSuggestionsController extends Controller
{
    private $general;
    private $UserID;
    private $ActiveMenuName;
    private $PageTitle;
    private $CRUD;
    private $Settings;
    private $Menus;
    private $generalDB;

    public function __construct()
    {
        $this->ActiveMenuName = activeMenuNames::ChatSuggestions->value;
        $this->PageTitle = "Chat Suggestion";
        $this->middleware('auth');
        $this->generalDB = Helper::getGeneralDB();
        $this->middleware(function ($request, $next) {
            $this->UserID = auth()->user()->UserID;
            $this->general = new general($this->UserID, $this->ActiveMenuName);
            $this->Menus = $this->general->loadMenu();
            $this->CRUD = $this->general->getCrudOperations($this->ActiveMenuName);
            $this->Settings = $this->general->getSettings();
            return $next($request);
        });
    }

    public function view(Request $req)
    {
        if ($this->general->isCrudAllow($this->CRUD, "view")) {
            $FormData = $this->general->UserInfo;
            $FormData['ActiveMenuName'] = $this->ActiveMenuName;
            $FormData['PageTitle'] = $this->PageTitle;
            $FormData['menus'] = $this->Menus;
            $FormData['crud'] = $this->CRUD;
            return view('app.settings.chat-suggestions.view', $FormData);
        } elseif ($this->general->isCrudAllow($this->CRUD, "Add")) {
            return Redirect::to('/admin/settings/chat-suggestions/create');
        } else {
            return view('errors.403');
        }
    }

    public function TrashView(Request $req)
    {
        if ($this->general->isCrudAllow($this->CRUD, "restore")) {
            $FormData = $this->general->UserInfo;
            $FormData['menus'] = $this->Menus;
            $FormData['crud'] = $this->CRUD;
            $FormData['ActiveMenuName'] = $this->ActiveMenuName;
            $FormData['PageTitle'] = $this->PageTitle;
            return view('app.settings.chat-suggestions.trash', $FormData);
        } elseif ($this->general->isCrudAllow($this->CRUD, "view")) {
            return Redirect::to('/admin/settings/chat-suggestions/');
        } else {
            return view('errors.403');
        }
    }

    public function create(Request $req)
    {
        if ($this->general->isCrudAllow($this->CRUD, "add")) {
            $FormData = $this->general->UserInfo;
            $FormData['menus'] = $this->Menus;
            $FormData['crud'] = $this->CRUD;
            $FormData['ActiveMenuName'] = $this->ActiveMenuName;
            $FormData['PageTitle'] = $this->PageTitle;
            $FormData['isEdit'] = false;
            return view('app.settings.chat-suggestions.create', $FormData);
        } elseif ($this->general->isCrudAllow($this->CRUD, "view")) {
            return Redirect::to('/admin/settings/chat-suggestions/');
        } else {
            return view('errors.403');
        }
    }

    public function edit(Request $req, $CSID)
    {
        if ($this->general->isCrudAllow($this->CRUD, "edit")) {
            $FormData = $this->general->UserInfo;
            $FormData['menus'] = $this->Menus;
            $FormData['crud'] = $this->CRUD;
            $FormData['ActiveMenuName'] = $this->ActiveMenuName;
            $FormData['PageTitle'] = $this->PageTitle;
            $FormData['isEdit'] = true;
            $FormData['CSID'] = $CSID;
            $FormData['EditData'] = DB::Table('tbl_chat_suggestions')->where('DFlag', 0)->Where('CSID', $CSID)->get();
            if (count($FormData['EditData']) > 0) {
                return view('app.settings.chat-suggestions.create', $FormData);
            } else {
                return view('errors.403');
            }
        } elseif ($this->general->isCrudAllow($this->CRUD, "view")) {
            return Redirect::to('/admin/settings/chat-suggestions/');
        } else {
            return view('errors.403');
        }
    }

    public function save(Request $req)
    {
        if ($this->general->isCrudAllow($this->CRUD, "add")) {
            $OldData = [];
            $NewData = [];
            $CSID = "";
            $rules = [
                'Question' => ['required', 'min:3', 'max:100', new ValidUnique(["TABLE" => 'tbl_chat_suggestions', "WHERE" => " Question='" . $req->Question . "' "], "This Question is already taken.")],
                'Answer' => ['required', 'min:3'],
            ];
            $message = [];
            $validator = Validator::make($req->all(), $rules, $message);

            if ($validator->fails()) {
                return ['status' => false, 'message' => "Chat suggestion Create Failed", 'errors' => $validator->errors()];
            }
            DB::beginTransaction();
            $status = false;
            try {
                $CSID = DocNum::getDocNum(docTypes::ChatSuggestions->value);
                $data = [
                    "CSID" => $CSID,
                    "Question" => $req->Question,
                    "Answer" => $req->Answer,
                    "ActiveStatus" => $req->ActiveStatus,
                    "CreatedBy" => $this->UserID,
                    "CreatedOn" => date("Y-m-d H:i:s")
                ];
                $status = DB::Table('tbl_chat_suggestions')->insert($data);
            } catch (Exception $e) {
                logger("Error in ChatSuggestionsController@save: " . $e->getMessage());
                $status = false;
            }

            if ($status == true) {
                DocNum::updateDocNum(docTypes::ChatSuggestions->value);
                $NewData = DB::table('tbl_chat_suggestions')->where('CSID', $CSID)->get();
                $logData = ["Description" => "New Chat Suggestion Created", "ModuleName" => $this->ActiveMenuName, "Action" => cruds::ADD->value, "ReferID" => $CSID, "OldData" => $OldData, "NewData" => $NewData, "UserID" => $this->UserID, "IP" => $req->ip()];
                logs::Store($logData);
                DB::commit();
                return ['status' => true, 'message' => "Chat Suggestion Created Successfully"];
            } else {
                DB::rollback();
                return ['status' => false, 'message' => "Chat Suggestion Create Failed"];
            }
        } else {
            return ['status' => false, 'message' => 'Access denied'];
        }
    }

    public function update(Request $req, $CSID)
    {
        if ($this->general->isCrudAllow($this->CRUD, "edit")) {
            $OldData = [];
            $NewData = [];

            $rules = [
                'Question' => ['required', 'min:3', 'max:100', new ValidUnique(["TABLE" => 'tbl_chat_suggestions', "WHERE" => " Question='" . $req->Question . "' and CSID<>'" . $CSID . "'  "], "This Chat Suggestion is already taken.")],
                'Answer' => ['required', 'min:3'],
            ];
            $message = [];
            $validator = Validator::make($req->all(), $rules, $message);

            if ($validator->fails()) {
                return ['status' => false, 'message' => "Chat Suggestion Update Failed", 'errors' => $validator->errors()];
            }
            DB::beginTransaction();
            $status = false;
            try {
                $OldData = DB::table('tbl_chat_suggestions')->where('CSID', $CSID)->get();
                $data = [
                    "Question" => $req->Question,
                    "Answer" => $req->Answer,
                    "ActiveStatus" => $req->ActiveStatus,
                    "UpdatedBy" => $this->UserID,
                    "UpdatedOn" => date("Y-m-d H:i:s")
                ];
                $status = DB::Table('tbl_chat_suggestions')->where('CSID', $CSID)->update($data);
            } catch (Exception $e) {
                logger("Error in ChatSuggestionsController@update: " . $e->getMessage());
                $status = false;
            }

            if ($status == true) {
                $NewData = DB::table('tbl_chat_suggestions')->where('CSID', $CSID)->get();
                $logData = ["Description" => "Chat Suggestion Updated ", "ModuleName" => $this->ActiveMenuName, "Action" => cruds::UPDATE->value, "ReferID" => $CSID, "OldData" => $OldData, "NewData" => $NewData, "UserID" => $this->UserID, "IP" => $req->ip()];
                logs::Store($logData);
                DB::commit();
                return ['status' => true, 'message' => "Chat Suggestion Updated Successfully"];
            } else {
                DB::rollback();
                return ['status' => false, 'message' => "Chat Suggestion Update Failed"];
            }
        } else {
            return ['status' => false, 'message' => 'Access denied'];
        }
    }

    public function Delete(Request $req, $CSID)
    {
        $OldData = $NewData = [];
        if ($this->general->isCrudAllow($this->CRUD, "delete")) {
            DB::beginTransaction();
            $status = false;
            try {
                $OldData = DB::table('tbl_chat_suggestions')->where('CSID', $CSID)->get();
                $status = DB::table('tbl_chat_suggestions')->where('CSID', $CSID)->update(["DFlag" => 1, "DeletedBy" => $this->UserID, "DeletedOn" => date("Y-m-d H:i:s")]);
            } catch (Exception $e) {
                logger("Error in ChatSuggestionsController@Delete: " . $e->getMessage());
            }
            if ($status) {
                DB::commit();
                $logData = ["Description" => "Chat Suggestion has been Deleted ", "ModuleName" => $this->ActiveMenuName, "Action" => cruds::DELETE->value, "ReferID" => $CSID, "OldData" => $OldData, "NewData" => $NewData, "UserID" => $this->UserID, "IP" => $req->ip()];
                logs::Store($logData);
                return ['status' => true, 'message' => "Chat Suggestion Deleted Successfully"];
            } else {
                DB::rollback();
                return ['status' => false, 'message' => "Chat Suggestion Delete Failed"];
            }
        } else {
            return response(['status' => false, 'message' => "Access Denied"], 403);
        }
    }

    public function Restore(Request $req, $CSID)
    {
        $OldData = $NewData = [];
        if ($this->general->isCrudAllow($this->CRUD, "restore")) {
            DB::beginTransaction();
            $status = false;
            try {
                $OldData = DB::table('tbl_chat_suggestions')->where('CSID', $CSID)->get();
                $status = DB::table('tbl_chat_suggestions')->where('CSID', $CSID)->update(["DFlag" => 0, "UpdatedBy" => $this->UserID, "UpdatedOn" => date("Y-m-d H:i:s")]);
            } catch (Exception $e) {
                logger("Error in ChatSuggestionsController@Restore: " . $e->getMessage());
            }
            if ($status) {
                DB::commit();
                $NewData = DB::table('tbl_chat_suggestions')->where('CSID', $CSID)->get();
                $logData = ["Description" => "Chat Suggestion has been Restored", "ModuleName" => $this->ActiveMenuName, "Action" => cruds::RESTORE->value, "ReferID" => $CSID, "OldData" => $OldData, "NewData" => $NewData, "UserID" => $this->UserID, "IP" => $req->ip()];
                logs::Store($logData);
                return ['status' => true, 'message' => "Chat Suggestion Restored Successfully"];
            } else {
                DB::rollback();
                return ['status' => false, 'message' => "Chat Suggestion Restore Failed"];
            }
        } else {
            return response(['status' => false, 'message' => "Access Denied"], 403);
        }
    }

    public function TableView(Request $req)
    {
        if ($this->general->isCrudAllow($this->CRUD, "view")) {
            $columns = [
                ['db' => 'CSID', 'dt' => '0'],
                ['db' => 'Question', 'dt' => '1'],
                ['db' => 'ActiveStatus', 'dt' => '2',
                    'formatter' => function ($d, $row) {
                        if ($d == "Active") {
                            return "<span class='badge badge-success m-1'>Active</span>";
                        } else {
                            return "<span class='badge badge-danger m-1'>Inactive</span>";
                        }
                    }
                ],
                ['db' => 'CSID', 'dt' => '3',
                    'formatter' => function ($d, $row) {
                        $html = '';
                        if ($this->general->isCrudAllow($this->CRUD, "edit") && ($row['Question'] !== 'Others')) {
                            $html .= '<button type="button" data-id="' . $d . '" class="btn  btn-outline-success ' . $this->general->UserInfo['Theme']['button-size'] . ' m-5 mr-10 btnEdit" data-original-title="Edit"><i class="fa fa-pencil"></i></button>';
                        }
                        if ($this->general->isCrudAllow($this->CRUD, "delete") && ($row['Question'] !== 'Others')) {
                            $html .= '<button type="button" data-id="' . $d . '" class="btn  btn-outline-danger ' . $this->general->UserInfo['Theme']['button-size'] . ' m-5 btnDelete" data-original-title="Delete"><i class="fa fa-trash" aria-hidden="true"></i></button>';
                        }
                        return $html;
                    }
                ]
            ];
            $Where = " DFlag=0 ";
            $data = [];
            $data['POSTDATA'] = $req;
            $data['TABLE'] = 'tbl_chat_suggestions';
            $data['PRIMARYKEY'] = 'CSID';
            $data['COLUMNS'] = $columns;
            $data['COLUMNS1'] = $columns;
            $data['GROUPBY'] = null;
            $data['WHERERESULT'] = null;
            $data['WHEREALL'] = $Where;
            return SSP::SSP($data);
        } else {
            return response(['status' => false, 'message' => "Access Denied"], 403);
        }
    }

    public function TrashTableView(Request $req)
    {
        if ($this->general->isCrudAllow($this->CRUD, "restore")) {
            $columns = [
                ['db' => 'CSID', 'dt' => '0'],
                ['db' => 'Question', 'dt' => '1'],
                ['db' => 'ActiveStatus', 'dt' => '2',
                    'formatter' => function ($d, $row) {
                        if ($d == "Active") {
                            return "<span class='badge badge-success m-1'>Active</span>";
                        } else {
                            return "<span class='badge badge-danger m-1'>Inactive</span>";
                        }
                    }
                ],
                ['db' => 'CSID', 'dt' => '3',
                    'formatter' => function ($d, $row) {
                        $html = '<button type="button" data-id="' . $d . '" class="btn btn-outline-success ' . $this->general->UserInfo['Theme']['button-size'] . '  m-2 btnRestore"> <i class="fa fa-repeat" aria-hidden="true"></i> </button>';
                        return $html;
                    }
                ]
            ];
            $data = [];
            $data['POSTDATA'] = $req;
            $data['TABLE'] = 'tbl_chat_suggestions';
            $data['PRIMARYKEY'] = 'CSID';
            $data['COLUMNS'] = $columns;
            $data['COLUMNS1'] = $columns;
            $data['GROUPBY'] = null;
            $data['WHERERESULT'] = null;
            $data['WHEREALL'] = " DFlag=1 ";
            return SSP::SSP($data);
        } else {
            return response(['status' => false, 'message' => "Access Denied"], 403);
        }
    }

    public function GetChatSuggestions(request $req)
    {
        return DB::Table('tbl_chat_suggestions')->where('ActiveStatus', 'Active')->where('DFlag', 0)->get();
    }
}
