<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Consignee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ConsigneeController extends Controller
{
    public function index(){
        return view('admin.consignee.list');
    }

    public function editparty($id){
        $consignee = Consignee::find($id);
        return response()->json($consignee);
    }

    public function addorupdateparty(Request $request){
        $messages = [
            'party_name.required' =>'Please enter a Party Name',
            'mobile_no.numeric' =>'Please enter a valid Mobile No',
            'mobile_no.digits' =>'Please enter upto 10 digit',
            'gst_no.required' =>'Please enter a GST Number',
            'address.required' =>'Please enter a Address',
            'state.required' =>'Please enter a State',
            'state_code.required' =>'Please enter a State Code',
            'state_code.digits' =>'Please enter only Numeric Values',
        ];

        $validator = Validator::make($request->all(), [
            'party_name' => 'required',
            'mobile_no' => 'nullable|numeric|digits:10',
            'gst_no' => 'required',
            'address' => 'required',
            'state' => 'required',
            'state_code' => 'required|numeric',
        ], $messages);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors(),'status'=>'failed']);
        }

        if( isset($request->action) && $request->action == 'update' ){
            $action = "update";
            $consignee = Consignee::find($request->consignee_id);

            if(!$consignee){
                return response()->json(['status' => '400']);
            }
            
        } else {

            $action = "add";
            $consignee = new Consignee();
        }

        $consignee->partyname = $request->party_name;
        $consignee->mobile_no = $request->mobile_no;
        $consignee->gst_no = $request->gst_no;
        $consignee->address = $request->address;
        $consignee->state = $request->state;
        $consignee->state_code = $request->state_code;
        $consignee->save();

        return response()->json(['status' => '200', 'action' => $action]);
    }

    public function allpartylist(Request $request){
        if ($request->ajax()) {

            $columns = array(
                0 =>'id',
                1 =>'partyname',
                2=> 'address',
                3=> 'state',
                4=> 'action'
            );

            $totalData = Consignee::count();
            // if (isset($role)){
            //     $totalData = Consignee::where('role',$role)->count();
            // }

            $totalFiltered = $totalData;

            $limit = $request->input('length');
            $start = $request->input('start');
            $order = $columns[$request->input('order.0.column')];
            $dir = $request->input('order.0.dir');

            if($order == "id"){
                $order == "partyname";
                $dir = 'ASC';
            }

            if(empty($request->input('search.value'))) {
                $consignees = Consignee::offset($start)
                    ->limit($limit)
                    ->orderBy($order,$dir)
                    ->get();
            } else {
                $search = $request->input('search.value');
                $consignees =  Consignee::Query();

                $consignees = $consignees->where(function($query) use($search){
                      $query->where('partyname','LIKE',"%{$search}%")
                            ->orWhere('mobile_no', 'LIKE',"%{$search}%")
                            ->orWhere('address', 'LIKE',"%{$search}%")
                            ->orWhere('state', 'LIKE',"%{$search}%")
                            ->orWhere('state_code', 'LIKE',"%{$search}%");
                      })
                      ->offset($start)
                      ->limit($limit)
                      ->orderBy($order,$dir)
                      ->get();

                $totalFiltered = count($consignees->toArray());
            }

            $data = array();

            if(!empty($consignees))
            {
                foreach ($consignees as $consignee)
                {

                    $party = '';
                    if (isset($consignee->partyname)){
                        $party = '<span>' .$consignee->partyname .'</span>';
                    }
                    if (isset($consignee->mobile_no)){
                        $party .= '<span><i class="fa fa-phone" aria-hidden="true"></i> ' .$consignee->mobile_no .'</span>';
                    }

                    $state = '['.$consignee->state_code.'] '. $consignee->state;

                    $action='';
                    $action .= '<button id="editPartyBtn" class="btn btn-gray text-blue btn-sm" data-toggle="modal" data-target="#PartyModal" onclick="" data-id="' .$consignee->id. '"><i class="fa fa-pencil" aria-hidden="true"></i></button>';
                    $action .= '<button id="deletePartyBtn" class="btn btn-gray text-danger btn-sm" data-toggle="modal" data-target="#DeletePartyModal" onclick="" data-id="' .$consignee->id. '"><i class="fa fa-trash-o" aria-hidden="true"></i></button>';

                    $nestedData['party'] = $party;
                    $nestedData['address'] = $consignee->address;
                    $nestedData['state'] = $state;
                    $nestedData['action'] = $action;
                    $data[] = $nestedData;
                }
            }

            $json_data = array(
                "draw"            => intval($request->input('draw')),
                "recordsTotal"    => intval($totalData),
                "recordsFiltered" => intval($totalFiltered),
                "data" => $data,
            );

            echo json_encode($json_data);
        }
    }

    public function deleteparty($id){
        $consignee = Consignee::find($id);
        if ($consignee){
            $consignee->estatus = 3;
            $consignee->save();

            $consignee->delete();
            return response()->json(['status' => '200']);
        }
        return response()->json(['status' => '400']);
    }
}
