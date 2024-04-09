<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Product;
use App\Models\ProductPrice;
use App\Models\ProductStock;
use App\Models\Setting;
use App\Models\User;
use App\Models\Consignee;
use App\Models\Transporter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Mpdf\Mpdf;
use Spipu\Html2Pdf\Exception\ExceptionFormatter;
use Spipu\Html2Pdf\Exception\Html2PdfException;
use Spipu\Html2Pdf\Html2Pdf;
use Carbon\Carbon;

class InvoiceController extends Controller
{
    public function index(){
        $action = "list";
        $users = User::where('role',2)->get();
        $products = Product::where('estatus',1)->get();
        
        return view('admin.invoice.list',compact('action','users','products'));
    }

    public function create(){
        $action = "create";
        $consignees = Consignee::where('estatus',1)->orderBy('partyname', 'ASC')->get();
        $products = Product::where('estatus',1)->orderBy('title', 'ASC')->get();
        $transporters = Transporter::where('estatus', 1)->get();

        $current_month = date('m');
        $firstThreeMonthsArr = ['01', '02', '03'];
        if(in_array($current_month, $firstThreeMonthsArr)){
            $fromYear = date('Y') - 1;
            $toYear = date('Y');
        } else {
            $fromYear = date('Y'); // Get the current year
            $toYear = date('Y') + 1;
        }

        $start_date = Carbon::create($fromYear, 4, 1, 0, 0, 0); // April 1, 2024, 00:00:00
        $end_date = Carbon::create($toYear, 3, 31, 23, 59, 59); // March 31, 2025, 23:59:59

        $lastInvoiceNo = Invoice::where('estatus', 1)->whereBetween('created_at', [$start_date, $end_date])->latest('created_at')->pluck('invoice_no')->first();
        $invoice_no = $lastInvoiceNo + 1;
        if($lastInvoiceNo == ''){
            $invoice_no = 1;
        }

        return view('admin.invoice.list',compact('action', 'consignees', 'invoice_no', 'products', 'transporters'));
    }

    public function add_row_item(Request $request){

        $next_item = $request->total_item + 1;

        $products = Product::where('estatus',1)->get();
        $html_product = '<option></option>';
        foreach ($products as $product){
            $html_product .= '<option value="'.$product->id.'">'. $product->title .'</option>';
        }


        $html = '<tr class="item-row addnew" id="table-row-'.$next_item.'">
                <td class="item-name">
                    <div class="delete-wpr">
                        <select name="item_name" id="item_name'.$next_item.'" class="item_name">
                            '.$html_product.'
                        </select>
                        <label id="item_name'.$next_item.'-error" class="error invalid-feedback animated fadeInDown" for="item_name'.$next_item.'"></label>
                        <a class="delete" onclick="removeRow(\'table-row-'.$next_item.'\',0)" href="javascript:;" title="Remove row">X</a>
                    </div>
                </td>
                <td width="100px">
                    <input class="form-control hsn" id="hsn'.$next_item.'" name="hsn" type="text">
                    <label id="hsn'.$next_item.'-error" class="error invalid-feedback animated fadeInDown" for="hsn'.$next_item.'"></label>
                </td>
                <td width="100px">
                    <input class="form-control packing_qty" id="packing_qty'.$next_item.'" name="packing_qty" type="number" min="1">
                    <label id="packing_qty'.$next_item.'-error" class="error invalid-feedback animated fadeInDown" for="packing_qty'.$next_item.'"></label>
                </td>
                <td width="100px">
                    <select name="packing_name" id="packing_name_'.$next_item.'" class="packing_name">
                        <option value="Bag">Bag</option>
                        <option value="Carbo">Carbo</option>
                        <option value="Drum">Drum</option>
                    </select>
                </td>
                <td width="100px">
                    <input class="form-control weight_of_packing" id="weight_of_packing'.$next_item.'" name="weight_of_packing" type="number" min="1">
                    <label id="weight_of_packing'.$next_item.'-error" class="error invalid-feedback animated fadeInDown" for="weight_of_packing'.$next_item.'"></label>
                </td>
                <td width="100px">
                    <div class="prse text-center"><span id="quantity'.$next_item.'" class="quantity">0</span></div>
                    <label id="quantity'.$next_item.'-error" class="error invalid-feedback animated fadeInDown" for="quantity'.$next_item.'"></label>
                </td>
                <td width="100px">
                    <input class="form-control unitcost cost" placeholder="0.00" type="number" id="price'.$next_item.'" name="price" value="">
                    <label id="price'.$next_item.'-error" class="error invalid-feedback animated fadeInDown" for="price'.$next_item.'"></label>
                </td>
                <td class="subt_price"><div class="prse text-right"><i class="fa fa-inr" aria-hidden="true"></i><span class="price proprice sub_price">0.00</span></div></td>
           </tr>';

        return ['html' => $html, 'next_item' => $next_item];
    }

    public function change_products(Request $request){
        $language = $request->language;
        $products = Product::where('estatus',1)->get();
        $html_product = '<option></option>';
        foreach ($products as $product){
            $title = $product->title;
            $html_product .= '<option value="'.$product->id.'">'.$title.'</option>';
        }

        return ['html' => $html_product];
    }

    public function change_inv_consignee(Request $request){
        
        $consignee_statecode = Consignee::where('id', $request->consignee_id)->pluck('state_code')->first();
        $setting = Setting::where('id', 1)->first();

        if( $setting->company_statecode === (int) $consignee_statecode ){
            $gst_type = 'csgst';
            $gst_percentage = ($setting->gst_percentage / 2);
        } else {
            $gst_type = 'igst';
            $gst_percentage = $setting->gst_percentage;
        }
        
        return [ 'gst_type' => $gst_type, 'gst_percent' => $gst_percentage ];
    }

    public function change_product_price(Request $request){
        // $price = ProductPrice::where('user_id',$request->user_id)->where('product_id',$request->product_id)->pluck('price')->first();
        // $price = Product::where('id',$request->product_id)->pluck('price')->first();
        $price = Product::where('id',$request->product_id)->first();
        return $price;
    }

    public function save(Request $request){
        if ($request->action == "add"){
            $invoice = new Invoice();
        } elseif ($request->action == "update"){
            $invoice = Invoice::find($request->invoice_id);
        }
        $igst = 0;
        $cgst = 0;
        $sgst = 0;
        $gst_percentage = $request->gstPercentage;
        if($request->gstType == "csgst"){
            $sgst = $cgst = $request->gstAmount;
        } else {
            $igst = $request->gstAmount;
        }

        // add Transporter If not exist
        $transporterId = Transporter::where('id', $request->transporter_name)->pluck('id')->first();
        if(!$transporterId){

            $transporterId = Transporter::where('transporter_name', $request->transporter_name)->pluck('id')->first();
            if(!$transporterId){

                $Transporter = new Transporter();
                $Transporter->transporter_name = $request->transporter_name;
                $Transporter->created_at = new \DateTime(null, new \DateTimeZone('Asia/Kolkata'));

                $Transporter->save();
                $transporterId = $Transporter->id;
            }
        }

        $invoice->invoice_no = $request->invoice_no;
        $invoice->consignee_id = $request->customer_name;
        $invoice->transporter_id = $transporterId;
        $invoice->invoice_date = date("Y-m-d", strtotime($request->invoice_date));
        $invoice->sub_total = $request->subtotal;
        $invoice->gst_percentage = $request->gstPercentage;
        $invoice->sgst_amount = $sgst;
        $invoice->cgst_amount = $cgst;
        $invoice->igst_amount = $igst;
        $invoice->final_amount = $request->grandTotal;
        $invoice->transport_mode = $request->transport_mode;
        $invoice->save();

        $deleted_product_ids = array();
        $deleted_product_item_ids = array();
        if ($request->action == "update"){
            $invoice_items = InvoiceItem::where('invoice_id',$request->invoice_id)->get();
            foreach ($invoice_items as $invoice_item){
                $invoice_item->estatus = 3;

                $temp['product_id'] = $invoice_item->product_id;
                $temp['qty'] = $invoice_item->quantity;
                array_push($deleted_product_ids, $temp);
                array_push($deleted_product_item_ids, $invoice_item->product_id);
                //update stock
                
                // if (!in_array($invoice_item->product_id, explode(",",$request->product_ids))){
                //     $product = Product::find($invoice_item->product_id);
                //     $product->stock = $product->stock + $invoice_item->quantity;
                //     $product->save();
                // }

                $invoice_item->save();
                $invoice_item->delete();
            }
        }

        for ($i = 1; $i <= $request->total_items; $i++){
            $form = 'InvoiceItemForm'.$i;
            $item = json_decode($request[$form],true);

            $productId = Product::where('id', $item['item_name'])->pluck('id')->first();
            if($productId){
                $productId = $item['item_name'];
            } else {
                
                $productId = Product::where('title', $item['item_name'])->pluck('id')->first();

                if(!$productId){

                    $Product = new Product();
                    $Product->title = $item['item_name'];
                    $Product->hsn_code = $item['hsn'];
                    $Product->price = $item['price'];
                    $Product->created_at = new \DateTime(null, new \DateTimeZone('Asia/Kolkata'));

                    $Product->save();
                    $productId = $Product->id;
                }
            }
           
            $invoice_item = new InvoiceItem();
            $invoice_item->invoice_id = $invoice->id;
            $invoice_item->product_id = $productId;
            $invoice_item->price = $item['price'];
            $invoice_item->packing_qty = $item['packing_qty'];
            $invoice_item->packingType = $item['packing_name'];
            $invoice_item->packing_weight = $item['weight_of_packing'];
            $invoice_item->quantity = $item['quantity'];
            $invoice_item->final_price = $item['final_price'];
            $invoice_item->save();

            $itemQty = (float) $invoice_item->quantity;
            //update stock
            // if ($request->action == "add") {
            //     $product = Product::find($invoice_item->product_id);
            //     $product->stock = $product->stock - $itemQty;
            //     $product->save();
            // } elseif ($request->action == "update"){
            //     //dd($deleted_product_item_ids);
            //     $no = 1;
            //     foreach ($deleted_product_ids as $deleted_product_id) {
            //         if ($deleted_product_id['product_id'] == $invoice_item->product_id && $deleted_product_id['qty'] != $itemQty){
            //             if ($itemQty > $deleted_product_id['qty']){
            //                 $qty = $itemQty - $deleted_product_id['qty'];
            //                 $product = Product::find($invoice_item->product_id);
            //                 $product->stock = $product->stock - $qty;
            //                 $product->save();
            //             }
            //             elseif ($itemQty < $deleted_product_id['qty']){
            //                 $qty = $deleted_product_id['qty'] - $itemQty;
            //                 $product = Product::find($invoice_item->product_id);
            //                 $product->stock = $product->stock + $qty;
            //                 $product->save();
            //             }
            //         }
                    
            //         // if($no == 1){
            //         //     if ($deleted_product_id['product_id'] != $item['item_name'] ){
            //         //             $qty = $item['quantity'];
            //         //             $product = Product::find($item['item_name']);
            //         //             $product->stock = $product->stock - $qty;
            //         //             $product->save();
            //         //     }
            //         // }
            //         // $no++;
            //     }

            //     if (!in_array($item['item_name'], $deleted_product_item_ids)){
            //         $qty = $item['quantity'];
            //         $product = Product::find($item['item_name']);
            //         $product->stock = $product->stock - $qty;
            //         $product->save();
            //     }
            // }
        }

        // if ($request->action == "add") {
        //     $settings = Setting::find(1);
        //     $settings->invoice_no = $settings->invoice_no + 1;
        //     $settings->save();
        // }

        return ['status' => 200, 'action' => $request->action];
    }

    public function allInvoicelist(Request $request){
        if ($request->ajax()) {
            $columns = array(
                0 =>'id',
                1 =>'invoice_no',
                2 => 'customer_info',
                3 => 'amount',
                4 => 'invoice_date',
                5 => 'action',
            );

            $limit = $request->input('length');
            $start = $request->input('start');
            $order = $columns[$request->input('order.0.column')];
            $dir = $request->input('order.0.dir');

            if($order == "id"){
                $order = "created_at";
                $dir = 'DESC';
            }

            $totalData = Invoice::count();
            $totalFiltered = $totalData;

            if(empty($request->input('search.value')))
            {
                $Invoices = Invoice::with('invoice_item.product','consignee');
                if (isset($request->user_id_filter) && $request->user_id_filter!=""){
                    $Invoices = $Invoices->where('consignee_id',$request->user_id_filter);
                }
                if (isset($request->start_date) && $request->start_date!="" && isset($request->end_date) && $request->end_date!=""){
                    $Invoices = $Invoices->whereRaw("invoice_date between '".$request->start_date."' and '".$request->end_date."'");
                }
                $Invoices = $Invoices->offset($start)
                    ->limit($limit)
                    ->orderBy($order,$dir)
                    ->get();

                $totalFiltered = count($Invoices->toArray());
            } else {
                $search = $request->input('search.value');
                $Invoices = Invoice::with('invoice_item.product','consignee');
                if (isset($request->user_id_filter) && $request->user_id_filter!=""){
                    $Invoices = $Invoices->where('consignee_id',$request->user_id_filter);
                }
                if (isset($request->start_date) && $request->start_date!="" && isset($request->end_date) && $request->end_date!=""){
                    $Invoices = $Invoices->whereRaw("invoice_date between '".$request->start_date."' and '".$request->end_date."'");
                }
                $Invoices = $Invoices->where(function($query) use($search){
                    $query->where('invoice_no','LIKE',"%{$search}%")
                        ->orWhere('invoice_date', 'LIKE',"%{$search}%")
                        // ->orWhere('total_qty', 'LIKE',"%{$search}%")
                        ->orWhere('final_amount', 'LIKE',"%{$search}%")
                        ->orWhereHas('consignee_id',function ($Query) use($search) {
                            $Query->where('partyname', 'Like', '%' . $search . '%');
                        });
                    })
                    ->offset($start)
                    ->limit($limit)
                    ->orderBy($order,$dir)
                    ->get();

                $totalFiltered = count($Invoices->toArray());
            }

            $data = array();
            if(!empty($Invoices))
            {
                foreach ($Invoices as $Invoice)
                {
                    $amount = '';
                    // if (isset($Invoice->total_qty)){
                    //     $amount .= '<span>Total Quantity: '.$Invoice->total_qty;
                    // }
                    setlocale(LC_MONETARY, 'en_IN');
                    if (isset($Invoice->final_amount)){
                        $amount .= '<i class="fa fa-inr" aria-hidden="true"></i> '.IND_money_format($Invoice->final_amount);
                        // $amount .= '<span>Final Amount: <i class="fa fa-inr" aria-hidden="true"></i> '.$Invoice->final_amount;
                    }
                    // if (isset($Invoice->outstanding_amount)){
                    //     $amount .= '<span>Outstanding Amount: <i class="fa fa-inr" aria-hidden="true"></i> '.$Invoice->outstanding_amount;
                    // }
                    // if (isset($Invoice->total_payable_amount)){
                    //     $amount .= '<span>Total Payable Amount: <i class="fa fa-inr" aria-hidden="true"></i> '.$Invoice->total_payable_amount;
                    // }


                    $table = '<table cellpadding="5" cellspacing="0" border="1" width="100%" id="items_table">';
                    $table .= '<tbody>';
                    $table .='<tr style="width: 100%">';
                    $table .= '<th style="text-align: center">Item No.</th>';
                    $table .= '<th>Item Name</th>';
                    $table .= '<th style="text-align: center">Packing</th>';
                    $table .= '<th style="text-align: center">Quantity</th>';
                    $table .= '<th style="text-align: center">Rate</th>';
                    $table .= '<th style="text-align: right">Final Price</th>';
                    $table .= '</tr>';
                    $item = 1;
                    foreach ($Invoice->invoice_item as $invoice_item){
                        $product = Product::withTrashed()->find($invoice_item->product_id);
                        $product_title = $product->title;
                        // if ($Invoice->language == "English" && isset($product)){
                        //     $product_title = $product->title_english;
                        // }
                        // elseif ($Invoice->language == "Hindi" && isset($product)){
                        //     $product_title = $product->title_english." | ".$product->title_hindi;
                        // }
                        // elseif ($Invoice->language == "Gujarati" && isset($product)){
                        //     $product_title = $product->title_english." | ".$product->title_gujarati;
                        // }
                        $table .='<tr>';
                        $table .= '<td style="text-align: center">'.$item.'</td>';
                        $table .= '<td>'.$product_title.'</td>';
                        $table .= '<td style="text-align: center">'.$invoice_item->packing_qty.' '.$invoice_item->packingType.' X '.round($invoice_item->packing_weight, 0).' Kg</td>';
                        $table .= '<td style="text-align: center">'.round($invoice_item->quantity, 0).' Kg</td>';
                        $table .= '<td style="text-align: center"><i class="fa fa-inr" aria-hidden="true"></i> '.IND_money_format($invoice_item->price).'</td>';
                        $table .= '<td style="text-align: right"><i class="fa fa-inr" aria-hidden="true"></i> '.IND_money_format($invoice_item->final_price).'</td>';
                        $table .= '</tr>';
                        $item++;
                    }
                    $table .='</tbody>';
                    $table .='</table>';

                    $action = '';
                    $action .= '<button id="printBtn" class="btn btn-gray text-warning btn-sm" data-id="'.$Invoice->id.'"><i class="fa fa-print" aria-hidden="true"></i></button>';
                    $action .= '<button id="editInvoiceBtn" class="btn btn-gray text-blue btn-sm" data-id="'.$Invoice->id.'"><i class="fa fa-pencil" aria-hidden="true"></i></button>';
                    $action .= '<button id="deleteInvoiceBtn" class="btn btn-gray text-danger btn-sm" data-toggle="modal" data-target="#DeleteInvoiceModal" data-id="'.$Invoice->id.'"><i class="fa fa-trash-o" aria-hidden="true"></i></button>';

                    $nestedData['invoice_no'] = $Invoice->invoice_no;
                    $nestedData['customer_info'] = isset($Invoice->consignee->partyname)?$Invoice->consignee->partyname:'';
                    // $nestedData['total_qty'] = $Invoice->total_qty .' KG';
                    $nestedData['amount'] = $amount;
                    $nestedData['invoice_date'] = date("d-m-Y", strtotime($Invoice->invoice_date));
                    $nestedData['action'] = $action;
                    // $nestedData['quantity'] = $Invoice->total_qty;
                    // $nestedData['final_amount'] = $Invoice->final_amount;
                    //$nestedData['amount_transfer'] = '';
                    //$nestedData['payment_type'] = '';
                   // $nestedData['outstanding_amount'] = $Invoice->outstanding_amount;
                   // $nestedData['total_payable_amount'] = $Invoice->total_payable_amount;
                    $nestedData['table1'] = $table;
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

    public function edit($id){
        $action = "edit";
        $consignees = Consignee::where('estatus',1)->orderBy('partyname', 'ASC')->get();
        $products = Product::where('estatus',1)->get();
        $invoice = Invoice::with('invoice_item')->where('id',$id)->first();
        $invConsignee = Consignee::where('id', $invoice->consignee_id)->first();
        $transporters = Transporter::where('estatus', 1)->get();
        $settings = Setting::find(1);

        return view('admin.invoice.list',compact('action', 'consignees', 'products', 'transporters', 'invoice', 'invConsignee', 'settings'));
    }

    public function delete($id){
        $Invoice = Invoice::with('invoice_item')->where('id',$id)->first();
        if ($Invoice){
            $Invoice->estatus = 3;
            $Invoice->save();
            $Invoice->delete();

            foreach ($Invoice->invoice_item as $invoice_item){
                $invoice_item->estatus = 3;
                $invoice_item->save();

                $product = Product::find($invoice_item->product_id);
                $product->stock = $product->stock + $invoice_item->quantity;
                $product->save();

                $invoice_item->delete();
            }

            return response()->json(['status' => '200']);
        }
        return response()->json(['status' => '400']);
    }

    public function generate_pdf1($id){
        try{
            $invoice = Invoice::with('invoice_item.product', 'consignee')->where('id',$id)->first();
            $settings = Setting::find(1);
            $f = new \NumberFormatter( locale_get_default(), \NumberFormatter::SPELLOUT );

            $image = '';
            if (isset($settings->company_logo)){
                $image = '<img style="width: 100%;" src="'.url('public/images/company/'.$settings->company_logo).'" alt="Logo">';
            }

            $HTMLContent = '<style type="text/css">
                            <!--
                            table { vertical-align: top; }
                            tr    { vertical-align: top; }
                            td    { vertical-align: top; }
                            -->
                            </style>';
            $HTMLContent .= '<page backcolor="#FEFEFE" style="font-size: 12pt">
                        <bookmark title="Lettre" level="0" ></bookmark>
                        <p style="text-align: center; font-size: 7pt; margin-bottom: 0;">SHREE GANESHAY NAMAH</p>
                        <p style="text-align: right; font-size: 10pt; margin-bottom: 0;">Mo.: '.$settings->company_mobile_no.'</p>
                        <table cellspacing="0" style="width: 100%; border-bottom: dotted 1px black;">
                            <tr>
                                <td style="width: 15%;">
                                    '.$image.'
                                </td>
                                <td style="width: 20%"></td>
                                <td style="width: 65%;">
                                	<h3 style="text-align: left; font-size: 20pt; margin: 0;">'.$settings->company_name.'</h3>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="3"><p style="text-align: center;font-size: 10pt;">'.$settings->company_address.'</p></td>
                            </tr>
                        </table>
                        <br>
                       
                        <table cellspacing="0" style="width: 100%;">
                            <colgroup>
                                <col style="width: 12%;">
                                <col style="width: 60%;">
                                <col style="width: 12%;">
                                <col style="width: 16%;">
                            </colgroup>
                            <tbody>
                                <tr>
                                    <td style="font-size: 12pt; padding:2px 0;">
                                        Name
                                    </td>
                                    <td style="font-size: 12pt; padding:2px 0;">
                                        : <b>'.$invoice->consignee->partyname.'</b>
                                    </td>
                                    <td style="font-size: 10pt; padding:2px 0;">
                                        Invoice No
                                    </td>
                                    <td style="font-size: 10pt; padding:2px 0;">
                                        : '.$invoice->invoice_no.'
                                    </td>
                                </tr>
                                <tr>
                                    <td style="font-size: 10pt; padding:2px 0;">
                                        Mobile No
                                    </td>
                                    <td style="font-size: 10pt; padding:2px 0;">
                                        : '.$invoice->consignee->mobile_no.'
                                    </td>
                                    <td style="font-size: 10pt; padding:2px 0;">
                                        Date
                                    </td>
                                    <td style="font-size: 10pt; padding:2px 0;">
                                        : '.date('d M, Y', strtotime($invoice->invoice_date)).'
                                    </td>
                                </tr>
                                <tr>
                                    <td style="font-size: 10pt; padding:2px 0;">
                                        Address
                                    </td>
                                    <td style="font-size: 10pt; padding:2px 0;">
                                        : '.$invoice->consignee->address.'
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        
                        <table cellspacing="0" style="width: 100%; margin-top:10px;  font-size: 10pt; margin-bottom:0px;" align="center" border="1">
                            <colgroup>
                                <col style="width: 10%; text-align: center">
                                <col style="width: 50%; text-align: left">
                                <col style="width: 20%; text-align: center">
                                <col style="width: 10%; text-align: center">
                                <col style="width: 10%; text-align: center">
                            </colgroup>
                            <thead>
                                <tr style="background: #ffe6e6;">
                                    <th colspan="5" style="text-align: center; padding:8px 0;"> Item Details </th>
                                </tr>
                                <tr>
                                    <th style="padding:8px 0;">No.</th>
                                    <th style="padding:8px 0;">Item</th>
                                    <th style="padding:8px 0;">Qty</th>
                                    <th style="padding:8px 0;">Price</th>
                                    <th style="padding:8px 0;">Total</th>
                                </tr>
                            </thead>
                            <tbody>';

            $no = 1;
            foreach ($invoice->invoice_item as $invoice_item){
                $item = $invoice_item->product->title;

                $HTMLContent .= '<tr>
                                    <th style="font-weight : 10px; padding:8px 0;">'.$no.'</th>
                                    <th style="font-weight : 10px; padding:8px 0;"><b>'.$item.'</b></th>
                                    <th style="font-weight : 10px; padding:8px 0;">'.$invoice_item->quantity.' KG</th>
                                    <th style="font-weight : 10px; padding:8px 0;">'.number_format($invoice_item->price, 2, '.', ',').'</th>
                                    <th style="font-weight : 10px; padding:8px 0;">'.number_format($invoice_item->final_price, 2, '.', ',').'</th>
                                </tr>';
                $no++;
            }

            $HTMLContent .= '<tr>
                                    <th colspan="2" style="padding:10px 0;">Total</th>
                                    <th  style="padding:10px 0;">'.$invoice->total_qty.'</th>
                                    <th  style="padding:10px 0;"></th>
                                    <th  style="padding:10px 0;">'.number_format($invoice->final_amount, 2, '.', ',').'</th>
                             </tr>
                            </tbody>
                        </table>';

            $HTMLContent .= '<p style="font-size: 8pt;">AMOUNT IN WORDS: '.strtoupper($f->format($invoice->final_amount)).' RUPEES ONLY</p>';

            $HTMLContent .= '<table cellspacing="0" style="width: 100%; margin-top: 0px;">
                                <tr>
                                    <td  style="padding-top: 40px;padding-bottom: 10px; width :50%; border-bottom: solid 1px gray; text-align:left; color:gray;">Customer Signature</td>
                                    <td  style="padding-top: 40px;padding-bottom: 10px; width :50%; border-bottom: solid 1px gray; text-align:right; color:gray;"><b>For, '.$settings->company_name.'</b></td>
                                </tr>
                            </table>
                        </page>';

            $html2pdf = new Html2Pdf('P', 'A4', 'fr', true, "UTF-8");
            $html2pdf->setDefaultFont('freeserif');
            $html2pdf->pdf->SetDisplayMode('fullpage');
            $html2pdf->writeHTML($HTMLContent);
            $html2pdf->output($invoice->invoice_no.'.pdf');
        } catch (Html2PdfException $e) {
            $html2pdf->clean();

            $formatter = new ExceptionFormatter($e);
            echo $formatter->getHtmlMessage();
        }
    }

    public function generate_pdf($id){
        $invoice = Invoice::with('invoice_item', 'consignee', 'transporter')->where('id', $id)->first();
        $settings = Setting::find(1);

        $image = '';
        if (isset($settings->company_logo)){
            $image = '<img src="'.url('public/images/company/'.$settings->company_logo).'" alt="Logo" width="100px" height="100px">';
        }

        $HTMLContent = '<style type="text/css">
                            <!--
                            table { vertical-align: top; }
                            tr    { vertical-align: top; }
                            td    { vertical-align: top; }
                            .d-flex{ display: flex;}
                            -->
                            </style>';
        $HTMLContent .= '<page backcolor="#FEFEFE" style="font-size: 12pt">
                        <bookmark title="Lettre" level="0" ></bookmark>
                        <div>
                            <p style="font-size: 7pt; margin: 5px; text-align: center;">SHREE GANESHAY NAMAH</p>
                        </div>

                        <table cellspacing="5" cellpadding="0" style="width: 100%; border: 1px solid grey;">
                            <tr>
                                <td style="width: 18%; height: 15%; text-align: center; padding-top: 5px;" rowspan="4">
                                    '.$image.'
                                </td>
                                <td style="width: 50%; padding:5px 0 0;" colspan="3">
                                	<h3 style="text-align: left; font-size: 25pt; margin: 0;">'.$settings->company_name.'</h3>
                                </td>
                                <td style="background: lightgray; padding: 10px 0px 0px 0px; text-align: center;" colspan="2">
                                	<h5 style="font-size: 14pt;">Tax Invoice</h5>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="3">'.$settings->company_address.'</td>
                                <td colspan="" style="text-align: left;">[ ] Original</td>
                                <td colspan="" style="text-align: right;">[ ] Duplicate</td>
                            </tr>
                            <tr>
                                <td style="width: 45%;" >Mobile No: '.$settings->company_mobile_no.'</td>
                                <td colspan="4">MSME No:'.$settings->msme_no.'</td>
                            </tr>
                            <tr>
                                <td>GSTIN: '.$settings->company_gstno.'</td>
                                <td colspan="4">PAN No: '.$settings->company_panno.'</td>
                            </tr>
                        </table>
                        <table cellspacing="0" style="width: 100%; margin-top:3px; font-size: 8pt; border: 1px solid grey; margin-bottom:0px;" align="center" >
                            <tr>
                                <td style="width: 65%; padding: 5px;">
                                    <table cellspacing="0" cellpadding="0">
                                        <tr>
                                            <td style="width: 20%; padding: 2px 0; font-size: 10pt;">
                                                Name:
                                            </td>
                                            <td colspan="3" style="width: 80%; padding: 2px 0; font-size: 10pt;">
                                                <b>'.$invoice->consignee->partyname.'</b>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style="width: 20%; padding: 2px 0; font-size: 10pt;">
                                                Mobile No:
                                            </td>
                                            <td colspan="3" style="width: 80%; padding: 2px 0; font-size: 10pt;">
                                                '.$invoice->consignee->mobile_no.'
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style="width: 20%; padding: 2px 0; font-size: 10pt;">
                                                Address:
                                            </td>
                                            <td colspan="3" style="width: 80%; padding: 2px 0; font-size: 10pt;">
                                                '.$invoice->consignee->address.'
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style="width: 20%; padding: 2px 0; font-size: 10pt;">
                                                State:
                                            </td>
                                            <td style="width: 40%; padding: 2px 0; font-size: 10pt;">
                                                '.$invoice->consignee->state.'
                                            </td>
                                            <td style="width: 30%; padding: 2px 5px; font-size: 10pt; text-align: right;">
                                                State Code:
                                            </td>
                                            <td style="width: 10%; padding: 2px 0; font-size: 10pt;">
                                                '.$invoice->consignee->state_code.'
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style="padding: 2px 0; font-size: 10pt;">
                                                GSTIN:
                                            </td>
                                            <td colspan="3" style="padding: 2px 0; font-size: 10pt;">
                                                '.$invoice->consignee->gst_no.'
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                                <td style="width: 35%; border-left: 1px solid grey; padding: 5px;">
                                    <table cellspacing="0" cellpadding="0" style="width: 100%;">
                                        <tr>
                                            <td style="width: 50%; padding: 2px 0; font-size: 10pt;">
                                                Invoice No:
                                            </td>
                                            <td style=" padding: 2px 0; font-size: 10pt;">
                                                <b>'.$invoice->invoice_no.'</b>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style="width: 50%; padding: 2px 0; font-size: 10pt;">
                                                Invoice Date:
                                            </td>
                                            <td style="padding: 2px 0; font-size: 10pt;">
                                                '.date('d-m-Y', strtotime($invoice->invoice_date)).'
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style="width: 50%; padding: 2px 0; font-size: 10pt;">
                                                Transport Mode:
                                            </td>
                                            <td style="padding: 2px 0; font-size: 10pt;">
                                                '.$invoice->transport_mode.'
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style="width: 50%; padding: 2px 0; font-size: 10pt;">
                                                Transporter:
                                            </td>
                                            <td style=" padding: 2px 0; font-size: 10pt;">
                                                '.$invoice->transporter->transporter_name.'
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style="width: 50%; padding: 2px 0; font-size: 10pt;">
                                                Place of Supply:
                                            </td>
                                            <td style=" padding: 2px 0; font-size: 10pt;">
                                                '.$settings->place_of_supply.'
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        </table>

                        <table cellspacing="0" style="width: 100%; margin-top:5px; font-size: 10pt; margin-bottom:0px;border: 1px solid grey;" align="center" >
                            <colgroup>
                                <col style="width: 10%; text-align: center">
                                <col style="width: 50%; text-align: left">
                                <col style="width: 10%; text-align: center">
                                <col style="width: 15%; text-align: center">
                                <col style="width: 15%; text-align: right">
                            </colgroup>
                            <thead>
                                <tr style="background: lightgray;">
                                    <th colspan="7" style="font-size: 12pt; text-align: center; padding:8px 0;border: 1px solid grey;"> Item Details </th>
                                </tr>
                                <tr>
                                    <th style="font-size: 11pt; padding:8px 0;border: 1px solid grey;width: 10%;">No.</th>
                                    <th style="font-size: 11pt; padding:8px 0;text-align: left;padding-left: 5px;border: 1px solid grey;width: 50%;">Item</th>
                                    <th style="font-size: 11pt; padding:8px 0;border: 1px solid grey;width: 10%;">HSN No.</th>
                                    <th style="font-size: 11pt; padding:8px 0;border: 1px solid grey;width: 10%;">Packing</th>
                                    <th style="font-size: 11pt; padding:8px 0;border: 1px solid grey;width: 10%;">Qty (Kg)</th>
                                    <th style="font-size: 11pt; padding:8px 0;border: 1px solid grey;width: 15%;">Rate</th>
                                    <th style="font-size: 11pt; padding:8px 0;border: 1px solid grey;padding-right: 5px;text-align: right;width: 15%;">Amount</th>
                                </tr>
                            </thead>
                            <tbody>';

                                $no = 1;
                                foreach ($invoice->invoice_item as $invoice_item){
                                    $product = Product::withTrashed()->find($invoice_item->product_id);

                                    $HTMLContent .= '<tr>
                                                        <td style="font-size: 12pt; padding:8px 0; text-align: center; border: 1px solid grey; width: 5%;">'.$no.'</td>
                                                        <td style="font-size: 12pt; padding:8px 0; padding-left: 5px; border: 1px solid grey; width: 30%;text-align: left">'.$product->title.'</td>
                                                        <td style="font-size: 12pt; padding:8px 0; text-align: center; border: 1px solid grey; width: 17%;">'.$product->hsn_code.'</td>
                                                        <td style="font-size: 12pt; padding:8px 0; text-align: center; border: 1px solid grey; width: 17%;">'.$invoice_item->packing_qty.' '.$invoice_item->packingType.' X '.round($invoice_item->packing_weight, 0).' Kg</td>
                                                        <td style="font-size: 12pt; padding:8px 0; text-align: center; border: 1px solid grey; width: 12%;">'.round($invoice_item->quantity, 0).'</td>
                                                        <td style="font-size: 12pt; padding:8px 0; text-align: center; border: 1px solid grey; width: 9%;">'.IND_money_format($invoice_item->price).'</td>
                                                        <td style="font-size: 12pt; padding:8px 0; text-align: right; border: 1px solid grey; padding-right: 5px; width: 10%;">'.IND_money_format($invoice_item->final_price).'</td>
                                                    </tr>';
                                    $no++;
                                }

                                if($no < 15){
                                    $emptyRows = 15 - $no;
                                    for($r = 0; $r < $emptyRows; $r++){
                                        $HTMLContent .= '<tr>
                                                            <td style="padding:16px; border: 1px solid grey;"> </td>
                                                            <td style="padding:16px; border: 1px solid grey;"> </td>
                                                            <td style="padding:16px; border: 1px solid grey;"> </td>
                                                            <td style="padding:16px; border: 1px solid grey;"> </td>
                                                            <td style="padding:16px; border: 1px solid grey;"> </td>
                                                            <td style="padding:16px; border: 1px solid grey;"></td>
                                                            <td style="padding:16px; border: 1px solid grey;"> </td>
                                                        </tr>';
                                    }
                                }

                                $HTMLContent .= '<tr>
                                                    <th colspan="6" style="font-size: 12pt; padding:10px 0;border: 1px solid grey;">Sub Total</th>
                                                    <th style="font-size: 12pt; padding:10px 0;border: 1px solid grey;text-align: right;padding-right: 5px">'.IND_money_format($invoice->sub_total).'</th>
                                                </tr>';

                                $gstPercent = $invoice->gst_percentage;
                                if( $settings->company_statecode != $invoice->consignee->state_code ){
                                    
                                    $HTMLContent .= '<tr>
                                                        <th colspan="6" style="font-size: 12pt; padding:10px 0;border: 1px solid grey;">IGST('.$gstPercent.'%)</th>
                                                        <th style="font-size: 12pt; padding:10px 0; border: 1px solid grey; text-align: right; padding-right: 5px">'.IND_money_format($invoice->igst_amount).'</th>
                                                    </tr>';
                                    
                                } else {

                                    $HTMLContent .= '<tr>
                                                        <th colspan="6" style="font-size: 12pt; padding:10px 0;border: 1px solid grey;">SGST('.$gstPercent.'%)</th>
                                                        <th style="font-size: 12pt; padding:10px 0; border: 1px solid grey; text-align: right; padding-right: 5px">'.IND_money_format($invoice->sgst_amount).'</th>
                                                    </tr>';

                                    $HTMLContent .= '<tr>
                                                    <th colspan="6" style="font-size: 12pt; padding:10px 0;border: 1px solid grey;">CGST('.$gstPercent.'%)</th>
                                                    <th style="font-size: 12pt; padding:10px 0; border: 1px solid grey; text-align: right; padding-right: 5px">'.IND_money_format($invoice->cgst_amount).'</th>
                                                </tr>';
                                }

                                $HTMLContent .= '<tr>
                                                    <th colspan="6" style="font-size: 12pt; padding:10px 0;border: 1px solid grey;">Grand Total</th>
                                                    <th style="font-size: 12pt; padding:10px 0;border: 1px solid grey;text-align: right;padding-right: 5px">'.IND_money_format($invoice->final_amount).'</th>
                                                </tr>
                            </tbody>
                        </table>';

        $HTMLContent .= '<p style="font-size: 8pt; padding-right: 5px;">AMOUNT IN WORDS: '.strtoupper(numberTowords($invoice->final_amount)).'</p>';

        $HTMLContent .= '<table cellspacing="0" style="width: 100%; margin-top: 0px;">
                            <tr>
                                <td style=""><b>Terms & Conditions</b></td>
                            </tr>
                            <tr>
                                <td  style="padding-bottom: 10px; text-align:left; color:gray;">
                                    <ul>
                                        <li>The customer has to pay within 30 days of the invoice date.</li>
                                    </ul>
                                </td>
                            </tr>
                        </table>';

        // $HTMLContent .= '<table cellspacing="0" style="width: 100%; margin-top:10px; font-size: 10pt; margin-bottom:0px;border: 1px solid grey;" align="left">
        //                     <thead>
        //                         <tr>
        //                             <td colspan="5" style="text-align: left; padding:18px 0; padding-left: 5px; color:gray;border: 1px solid grey;"> Notes </td>
        //                         </tr>
        //                     </thead>
        //                 </table>';

        $HTMLContent .= '<htmlpagefooter name="footer">';
        // $HTMLContent .= '<table cellspacing="0" style="width: 100%; margin-top: 0px;padding-bottom: 40px">
        //                     <tr>
        //                         <td  style="padding-top: 50px;"><b>Terms & Conditions</b></td>
        //                     </tr>
        //                     <tr>
        //                         <td  style="padding-bottom: 10px; border-bottom: solid 1px gray; text-align:left; color:gray;">
        //                             <ul>
        //                                 <li>Condition 1</li>
        //                                 <li>Condition 2</li>
        //                                 <li>Condition 3</li>
        //                             </ul>
        //                         </td>
        //                     </tr>
        //                 </table>';

        $HTMLContent .= '<table cellspacing="0" style="width: 100%; margin-top: 0px; padding-bottom: 40px">
                            <tr>
                                <td  style="padding-top: 50px;padding-bottom: 10px; width :50%; border-bottom: solid 1px gray; text-align:left; color:gray;">Customer Signature</td>
                                <td  style="padding-top: 50px;padding-bottom: 10px; width :50%; border-bottom: solid 1px gray; text-align:right; color:gray;"><b>For, '.$settings->company_name.'</b></td>
                            </tr>
                        </table>';
        $HTMLContent .= '</htmlpagefooter>
                        <sethtmlpagefooter name="footer" />
                        </page>';

        $filename = "Invoice_".$invoice->invoice_no.".pdf";
        $mpdf = new Mpdf(["autoScriptToLang" => true, "autoLangToFont" => true, 'mode' => 'utf-8', 'format' => 'A4-P', 'margin_left' => 5, 'margin_right' => 5, 'margin_top' => 5, 'margin_bottom' => 5, 'margin_header' => 0, 'margin_footer' => 0]);
        $mpdf->WriteHTML($HTMLContent);
        $mpdf->Output($filename,"I");
    }

    public function report_pdf($user_id, $start_date, $end_date){
        $invoices = Invoice::with('invoice_item.product', 'user');
        if (isset($user_id) && $user_id!="null") {
            $invoices = $invoices->where('user_id', $user_id);
        }

        $date = '';
        if (isset($start_date) && $start_date!="null" && isset($end_date) && $end_date!="null"){
            $date = $start_date." - ".$end_date;
            $invoices = $invoices->whereRaw("invoice_date between '".$start_date."' and '".$end_date."'");
        }
        elseif (isset($start_date) && $start_date!="null"){
            $date = $start_date;
            $invoices = $invoices->where('invoice_date',$start_date);
        }
        elseif (isset($end_date) && $end_date!="null"){
            $date = $end_date;
            $invoices = $invoices->where('invoice_date',$end_date);
        }

        $invoices = $invoices->get();

        $HTMLContent = '<style type="text/css">
                            <!--
                            table { vertical-align: top; }
                            tr    { vertical-align: top; }
                            td    { vertical-align: top; }
                            -->
                            </style>';

        $HTMLContent .= '<page backcolor="#FEFEFE" style="font-size: 12pt">
                        <bookmark title="Lettre" level="0" ></bookmark>
                        <h2 style="text-align: center;margin: 0">Daily Report</h2>';

        foreach ($invoices as $invoice){
            $HTMLContent .= '<hr style="height: 1px">
                        <div>
                        <p style="font-size: 10pt;margin: 0;float: left; width: 50%; text-align: left;">Name: '.$invoice->user->full_name.'</p>
                        <p style="margin: 0;font-size: 10pt;float: left; width: 50%; text-align: right;">Invoice No: '.$invoice->invoice_no.'</p>
                        </div>
                        <div>
                        <p style="margin: 0;font-size: 10pt;float: left; text-align: right;">Date: '.date("d-m-Y", strtotime($invoice->invoice_date)).'</p>
                        </div>

                        <table cellspacing="0" style="width: 100%; margin-top:10px; font-size: 8pt; margin-bottom:0px;border: 1px solid grey;" align="center">
                            <colgroup>
                                <col style="width: 10%; text-align: center">
                                <col style="width: 50%; text-align: left">
                                <col style="width: 20%; text-align: center">
                                <col style="width: 10%; text-align: left">
                                <col style="width: 10%; text-align: right">
                            </colgroup>
                            <thead>
                                <tr>
                                    <th style="padding:8px 0;border: 1px solid grey;">No.</th>
                                    <th style="padding:8px 0;text-align: left;padding-left: 5px;border: 1px solid grey;">Item</th>
                                    <th style="padding:8px 0;border: 1px solid grey;">Qty (Kg)</th>
                                    <th style="padding:8px 0;text-align: left;padding-left: 5px;border: 1px solid grey;">Price</th>
                                    <th style="padding:8px 0;text-align: right;border: 1px solid grey;padding-right: 5px">Total</th>
                                </tr>
                            </thead>
                            <tbody>';

            $no = 1;
            foreach ($invoice->invoice_item as $invoice_item){
                $product = Product::withTrashed()->find($invoice_item->product_id);
                $product_title = '';
                if ($invoice->language == "English" && isset($product)){
                    $product_title = $product->title_english;
                }
                elseif ($invoice->language == "Hindi" && isset($product)){
                    $product_title = $product->title_english." | ".$product->title_hindi;
                }
                elseif ($invoice->language == "Gujarati" && isset($product)){
                    $product_title = $product->title_english." | ".$product->title_gujarati;
                }

                $HTMLContent .= '<tr>
                                    <td style="font-weight : 10px; padding:8px 0;text-align: center;border: 1px solid grey;">'.$no.'</td>
                                    <td style="font-weight : 10px; padding:8px 0;text-align: left;padding-left: 5px;border: 1px solid grey;">'.$product_title.'</td>
                                    <td style="font-weight : 10px; padding:8px 0;text-align: center;border: 1px solid grey;">'.$invoice_item->quantity.'</td>
                                    <td style="font-weight : 10px; padding:8px 0;text-align: left;padding-left: 5px;border: 1px solid grey;">'.number_format($invoice_item->price, 2, '.', ',').'</td>
                                    <td style="font-weight : 10px; padding:8px 0;text-align: right;padding-right: 5px;border: 1px solid grey;">'.number_format($invoice_item->final_price, 2, '.', ',').'</td>
                                </tr>';
                $no++;
            }
            $HTMLContent .= '<tr>
                                    <th colspan="2" style="padding:10px 0;border: 1px solid grey;">Total</th>
                                    <th  style="padding:10px 0;border: 1px solid grey;">'.$invoice->total_qty.'</th>
                                    <th  style="padding:10px 0;border: 1px solid grey;"></th>
                                    <th  style="padding:10px 0;text-align: right;padding-right: 5px;border: 1px solid grey;">'.number_format($invoice->final_amount, 2, '.', ',').'</th>
                             </tr>
                             <tr>
                                    <th colspan="2" style="padding:10px 0;border: 1px solid grey;">Outstanding Amount</th>
                                    <th  colspan="2" style="padding:10px 0;border: 1px solid grey;"></th>
                                    <th  style="padding:10px 0;text-align: right;padding-right: 5px;border: 1px solid grey;">'.number_format($invoice->outstanding_amount, 2, '.', ',').'</th>
                             </tr>
                             <tr>
                                    <th colspan="2" style="padding:10px 0;border: 1px solid grey;">Total Payable Amount</th>
                                    <th  colspan="2" style="padding:10px 0;border: 1px solid grey;"></th>
                                    <th  style="padding:10px 0;text-align: right;padding-right: 5px;border: 1px solid grey;">'.number_format($invoice->total_payable_amount, 2, '.', ',').'</th>
                             </tr>
                             
                            </tbody>
                        </table>';
        }

        $product_stocks = ProductStock::with('product');
        if (isset($start_date) && $start_date!="null" && isset($end_date) && $end_date!="null"){
            $product_stocks = $product_stocks->whereRaw("stock_date between '".$start_date."' and '".$end_date."'");
        }
        elseif (isset($start_date) && $start_date!="null"){
            $product_stocks = $product_stocks->where('stock_date',$start_date);
        }
        elseif (isset($end_date) && $end_date!="null"){
            $product_stocks = $product_stocks->where('stock_date',$end_date);
        }
        $product_stocks = $product_stocks->get();

        if(isset($product_stocks) && count($product_stocks)>0) {
            $HTMLContent .= '<hr style="height: 1px"><h3 style="text-align: center;margin: 0">Stock</h3>';

            $HTMLContent .= '<table cellspacing="0" style="width: 100%; margin-top:10px;  font-size: 8pt; margin-bottom:0px;border: 1px solid grey;" align="center">
                            <colgroup>
                                <col style="width: 10%; text-align: center">
                                <col style="width: 50%; text-align: left">
                                <col style="width: 20%; text-align: left">
                                <col style="width: 20%; text-align: center">
                            </colgroup>
                            <thead>
                                <tr>
                                    <th style="padding:8px 0;text-align: center;border: 1px solid grey;">No.</th>
                                    <th style="padding:8px 0;text-align: left;padding-left: 5px;border: 1px solid grey;">Product</th>
                                    <th style="padding:8px 0;text-align: left;padding-left: 5px;border: 1px solid grey;">Purchase From</th>
                                    <th style="padding:8px 0;text-align: center;border: 1px solid grey;">Qty (Kg)</th>
                                </tr>
                            </thead>
                            <tbody>';
            $no = 1;
            foreach ($product_stocks as $product_stock) {
                $HTMLContent .= '<tr>
                                <td style="padding:8px 0;text-align: center;border: 1px solid grey;">' . $no . '</td>
                                <td style="padding:8px 0;text-align: left;padding-left: 5px;border: 1px solid grey;">' . $product_stock->product->title_english . '</td>
                                <td style="padding:8px 0;text-align: left;padding-left: 5px;border: 1px solid grey;">' . $product_stock->purchase_from . '</td>
                                <td style="padding:8px 0;text-align: center;border: 1px solid grey;">' . $product_stock->stock . '</td>
                             </tr>';
                $no++;
            }

            $HTMLContent .= '</tbody>
                        </table>';
        }

        $HTMLContent .= '</page>';
        ini_set('pcre.backtrack_limit',100000000); 
        ini_set('pcre.recursion_limit',100000000);

        $filename = "report_".time().".pdf";
        $mpdf = new Mpdf(["autoScriptToLang" => true, "autoLangToFont" => true, 'mode' => 'utf-8', 'format' => 'A4-P', 'margin_top' => 3, 'margin_bottom' => 3]);
        $mpdf->WriteHTML($HTMLContent);
        $mpdf->Output($filename,"I");
    }


    public function itemreport_pdf($user_id, $start_date, $end_date,$product_id){

        $products = Product::join('invoice_items', 'products.id', '=', 'invoice_items.product_id')->join('invoices', 'invoices.id', '=', 'invoice_items.invoice_id')
        ->where('products.estatus',1)->where('invoice_items.estatus',1);
        if (isset($product_id) && $product_id!="null") {
            $products = $products->where('products.id', $product_id);
        }

        if (isset($user_id) && $user_id!="null") {
            $products = $products->where('invoices.user_id', $user_id);
        }

        // $products = $products->where('products.id','!=', 1);
        $products =  $products->groupBy('products.id')->get('products.*');

        //dd($products);
        
        $HTMLContent = '
            
        <style type="text/css">
                            <!--
                            table { vertical-align: top; }
                            tr    { vertical-align: top; }
                            td    { vertical-align: top; }
                            -->
                            </style>';

        $HTMLContent .= '<page backcolor="#FEFEFE" style="font-size: 12pt">
                        <bookmark title="Lettre" level="0" ></bookmark>
                        <h2 style="text-align: center;margin: 0">Item Daily Report</h2>';
        foreach ($products as $product)
        {
           
            $product_title = '';
            
            $product_title = $product->title_english." | ".$product->title_gujarati ." | ".$product->title_hindi;
            

            $invoiceitems = InvoiceItem::with('invoice.user','product');
            if (isset($user_id) && $user_id!="null") {
                $invoiceitems = $invoiceitems->whereHas('invoice.user', function($q) use ($user_id){
                             $q->where('user_id',$user_id);
                         });
            }

            // if (isset($product_id) && $product_id!="null") {
            //     $invoices = $invoices->whereHas('invoice_item', function($q) use ($product_id){
            //         $q->where('product_id',$product_id);
            //     });
            // }

            if (isset($product->id) && $product->id !="null") {
                $invoiceitems = $invoiceitems->where('product_id', $product->id);
            }

            

            $date = '';
            if (isset($start_date) && $start_date!="null" && isset($end_date) && $end_date!="null"){
                $date = $start_date." - ".$end_date;
                $invoiceitems = $invoiceitems->whereRaw("created_at between '".$start_date." 00:00:00' and '".$end_date." 23:59:59'");
            }
            elseif (isset($start_date) && $start_date!="null"){
                $date = $start_date;
                $invoiceitems = $invoiceitems->where('created_at',$start_date);
            }
            elseif (isset($end_date) && $end_date!="null"){
                $date = $end_date;
                $invoiceitems = $invoiceitems->where('created_at',$end_date);
            }

            $invoiceitems = $invoiceitems->get();
              if(count($invoiceitems) > 0){
                $HTMLContent .= '<hr style="height: 1px">
                            <div>
                            <p style="font-size: 10pt;margin: 0;float: left; width: 100%; text-align: left;">Product : '.$product_title.'</p><br>

                            <table cellspacing="0" style="width: 100%; margin-top:10px; font-size: 8pt; margin-bottom:0px;border: 1px solid grey;" align="center">
                            
                                <thead>
                                    <tr>
                                        <th style="width: 5%; text-align: center; padding:8px 0;border: 1px solid grey;">No.</th>
                                        <th style="width: 20%; text-align: center; padding:8px 0;text-align: left;padding-left: 5px;border: 1px solid grey;">Name</th>
                                        <th style="width: 15%; text-align: center; padding:8px 0;text-align: left;padding-left: 5px;border: 1px solid grey;">Invoice No</th>
                                        <th style="width: 10%; text-align: center; padding:8px 0;border: 1px solid grey;">Qty (Kg)</th>
                                        <th style="width: 10%; text-align: center; padding:8px 0;text-align: left;padding-left: 5px;border: 1px solid grey;">Price</th>
                                        <th style="width: 10%; text-align: center; padding:8px 0;text-align: right;border: 1px solid grey;padding-right: 5px">Total</th>
                                        <th style="width: 15%; text-align: center; padding:8px 0;border: 1px solid grey;padding-right: 5px">Date</th>
                                    </tr>
                                </thead>
                                <tbody>';

                $no = 1;
                foreach ($invoiceitems as $invoice_item){
                
                    $HTMLContent .= '<tr>
                                        <td style="font-weight : 5px; padding:8px 0;text-align: center;border: 1px solid grey;">'.$no.'</td>
                                        <td style="font-weight : 20px; padding:8px 0;text-align: left;padding-left: 5px;border: 1px solid grey;">'.$invoice_item->invoice->user->full_name.'</td>
                                        <td style="font-weight : 15px; padding:8px 0;text-align: left;padding-left: 5px;border: 1px solid grey;">'.$invoice_item->invoice->invoice_no.'</td>
                                        <td style="font-weight : 10px; padding:8px 0;text-align: center;border: 1px solid grey;">'.$invoice_item->quantity.' Kg</td>
                                        <td style="font-weight : 10px; padding:8px 0;text-align: left;padding-left: 5px;border: 1px solid grey;">'.number_format($invoice_item->price, 2, '.', ',').'</td>
                                        <td style="font-weight : 10px; padding:8px 0;text-align: right;padding-right: 5px;border: 1px solid grey;">'.number_format($invoice_item->final_price, 2, '.', ',').'</td>
                                        <td style="font-weight : 15px; padding:8px 0;text-align: center;padding-right: 5px;border: 1px solid grey;">'.date("d-m-Y", strtotime($invoice_item->invoice->created_at)).'</td>
                                    </tr>';
                            $no++;
                }
                $HTMLContent .= ' </tbody>
            </table>';
              }

        }

        ini_set('pcre.backtrack_limit',100000000); 
        ini_set('pcre.recursion_limit',100000000);
         
             $HTMLContent .= '</div> </page>';

            $filename = "report_".time().".pdf";

            $mpdf = new Mpdf(["autoScriptToLang" => true, "autoLangToFont" => true, 'mode' => 'utf-8', 'format' => 'A4-P', 'margin_top' => 3, 'margin_bottom' => 3]);
            
            $mpdf->WriteHTML($HTMLContent);
            
            $mpdf->Output($filename,"I");
    }
}

