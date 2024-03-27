<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Transporter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TransporterController extends Controller
{
    public function index(){
        return view('admin.transporter.list');
    }

    public function edittransporter($id){
        $transporter = Transporter::find($id);
        return response()->json($transporter);
    }

    public function addorupdatetransporter(Request $request){
        $messages = [
            'transporter_name.required' =>'Please enter a Transporter Name',
            'mobile_no.numeric' =>'Please enter a valid Mobile No',
            'mobile_no.digits' =>'Please enter upto 10 digit'
        ];

        $validator = Validator::make($request->all(), [
            'transporter_name' => 'required',
            'mobile_no' => 'nullable|numeric|digits:10'
        ], $messages);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors(),'status'=>'failed']);
        }

        if( isset($request->action) && $request->action == 'update' ){
            $action = "update";
            $transporter = Transporter::find($request->transporter_id);

            if(!$transporter){
                return response()->json(['status' => '400']);
            }
            
        } else {

            $action = "add";
            $transporter = new Transporter();
        }

        $transporter->transporter_name = $request->transporter_name;
        $transporter->mobile_no = $request->mobile_no;
        $transporter->save();

        return response()->json(['status' => '200', 'action' => $action]);
    }

    public function alltransporterlist(Request $request){
        if ($request->ajax()) {

            $columns = array(
                0 =>'id',
                1 =>'transporter_name',
                2=> 'action'
            );

            $totalData = Transporter::count();

            $totalFiltered = $totalData;

            $limit = $request->input('length');
            $start = $request->input('start');
            $order = $columns[$request->input('order.0.column')];
            $dir = $request->input('order.0.dir');

            if($order == "id"){
                $order == "transporter_name";
                $dir = 'ASC';
            }

            if(empty($request->input('search.value'))) {
                $transporters = Transporter::offset($start)
                    ->limit($limit)
                    ->orderBy($order,$dir)
                    ->get();
            } else {
                $search = $request->input('search.value');
                $transporters =  Transporter::Query();

                $transporters = $transporters->where(function($query) use($search){
                      $query->where('transporter_name','LIKE',"%{$search}%")
                            ->orWhere('mobile_no', 'LIKE',"%{$search}%");
                      })
                      ->offset($start)
                      ->limit($limit)
                      ->orderBy($order,$dir)
                      ->get();

                $totalFiltered = count($transporters->toArray());
            }

            $data = array();

            if(!empty($transporters))
            {
                foreach ($transporters as $transporter)
                {

                    $transporterInfo = '';
                    if (isset($transporter->transporter_name)){
                        $transporterInfo = '<span>' .$transporter->transporter_name .'</span>';
                    }
                    if (isset($transporter->mobile_no)){
                        $transporterInfo .= '<span><i class="fa fa-phone" aria-hidden="true"></i> ' .$transporter->mobile_no .'</span>';
                    }

                    $action='';
                    $action .= '<button id="editTransporterBtn" class="btn btn-gray text-blue btn-sm" data-toggle="modal" data-target="#TransporterModal" onclick="" data-id="' .$transporter->id. '"><i class="fa fa-pencil" aria-hidden="true"></i></button>';
                    $action .= '<button id="deleteTransporterBtn" class="btn btn-gray text-danger btn-sm" data-toggle="modal" data-target="#DeleteTransporterModal" onclick="" data-id="' .$transporter->id. '"><i class="fa fa-trash-o" aria-hidden="true"></i></button>';

                    $nestedData['transporter'] = $transporterInfo;
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

    public function deletetransporter($id){
        $transporter = Transporter::find($id);
        if ($transporter){
            $transporter->estatus = 3;
            $transporter->save();

            $transporter->delete();
            return response()->json(['status' => '200']);
        }
        return response()->json(['status' => '400']);
    }
}
