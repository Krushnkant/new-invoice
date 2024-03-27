<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SettingsController extends Controller
{
    public function index(){
        $Settings = Setting::where('estatus',1)->first();
        return view('admin.settings.list',compact('Settings'));
    }

    public function editSettings(){
        $Settings = Setting::find(1);
        return response()->json($Settings);
    }

    public function updateInvoiceSetting(Request $request){
        $messages = [
            // 'invoice_no.required' =>'Please provide a Invoice No',
            'company_name.required' =>'Please provide a Company Name',
            'company_logo.image' =>'Please provide a Valid Extension Logo(e.g: .jpg .png)',
            'company_logo.mimes' =>'Please provide a Valid Extension Logo(e.g: .jpg .png)',
            'company_address.required' =>'Please provide a Company Address',
            'company_mobile_no.required' =>'Please provide a Company Mobile Number',
            'gst_percentage.numeric' =>'Please provide a Numeric value',
            'place_of_supply.required' =>'Please provide a Place of Supply',
            'gst_percentage.required' =>'Please provide a GST Percentage',
            'company_statecode.required' =>'Please provide a State Code',
            'company_statecode.numeric' =>'Please provide a Numeric Value',
            'gst_no.required' =>'Please provide a Company GST Number',
            'pan_no.required' =>'Please provide a Company PAN Number',
            'msme_no.required' =>'Please provide a Company MSME Number',
        ];

        $validator = Validator::make($request->all(), [
            // 'invoice_no' => 'required|numeric',
            'company_name' => 'required',
            'company_logo' => 'image|mimes:jpeg,png,jpg',
            'company_address' => 'required',
            'company_mobile_no' => 'required|numeric|digits:10',
            'gst_percentage' => 'required|numeric',
            'place_of_supply' => 'required',
            'company_statecode' => 'required|numeric',
            'gst_no' => 'required',
            'pan_no' => 'required',
            'msme_no' => 'required',
        ], $messages);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors(),'status'=>'failed']);
        }

        $Settings = Setting::find(1);
        if(!$Settings){
            return response()->json(['status' => '400']);
        }
        // $Settings->invoice_no = $request->invoice_no;
        $Settings->company_name = $request->company_name;
        $Settings->company_address = $request->company_address;
        $Settings->company_mobile_no = $request->company_mobile_no;
        $Settings->company_gstno = $request->gst_no;
        $Settings->company_panno = $request->pan_no;
        $Settings->gst_percentage = $request->gst_percentage;
        $Settings->place_of_supply = $request->place_of_supply;
        $Settings->company_statecode = $request->company_statecode;
        $Settings->msme_no = $request->msme_no;

        $old_image = $Settings->company_logo;
        if ($request->hasFile('company_logo')) {
            $image = $request->file('company_logo');
            $image_name = 'company_logo_' . rand(111111, 999999) . time() . '.' . $image->getClientOriginalExtension();
            $destinationPath = public_path('images/company');
            $image->move($destinationPath, $image_name);
            if(isset($old_image)) {
                $old_image = public_path('images/company/' . $old_image);
                if (file_exists($old_image)) {
                    unlink($old_image);
                }
            }
            $Settings->company_logo = $image_name;
        }

        $Settings->save();
        return response()->json(['status' => '200','Settings' => $Settings]);
    }
}
