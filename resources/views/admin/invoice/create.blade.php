<div id="page-wrap" class="table-textarea invoice-box">
    <form method="post" id="invoiceForm">
        {{ csrf_field() }}
        <div class="row">
            <div class="col-sm-2">
                <div class="form-group row mb-0">
                    <label class="col-lg-12 col-form-label" for="">Party <span class="text-danger">*</span></label>
                    <div class="col-lg-12">
                        <select name="customer_name" id="customer_name">
                            <option></option>
                            @foreach($consignees as $consignee)
                                <option value="{{ $consignee->id }}">{{ $consignee->partyname }} [{{ $consignee->id }}]</option>
                            @endforeach
                        </select>
                        <label id="customer_name-error" class="error invalid-feedback animated fadeInDown" for="customer_name"></label>
                    </div>
                </div>
            </div>
            <div class="col-sm-2">
                <div class="form-group row mb-0">
                    <label class="col-lg-12 col-form-label" for="">Transporter <span class="text-danger">*</span></label>
                    <div class="col-lg-12">
                        <select name="transporter_name" id="transporter_name">
                            <option></option>
                            @foreach($transporters as $transporter)
                                <option value="{{ $transporter->id }}">{{ $transporter->transporter_name }}</option>
                            @endforeach
                        </select>
                        <label id="transporter_name-error" class="error invalid-feedback animated fadeInDown" for="transporter_name"></label>
                    </div>
                </div>
            </div>
            <div class="col-sm-2">
                <div class="form-group row">
                    <label class="col-lg-12 col-form-label text-left" for="transport_mode">Transport Mode <span class="text-danger">*</span></label>
                    <div class="col-lg-12">
                        <input class="form-control" type="text" id="transport_mode" name="transport_mode" placeholder="Transport Mode" value="">
                        <label id="transport_mode-error" class="error invalid-feedback animated fadeInDown" for="transport_mode"></label>
                    </div>
                </div>
            </div>
            <div class="col-sm-2 ml-auto">
                <div class="form-group row mb-0">
                    <label class="col-lg-12 col-form-label" for="invoice_no">Invoice No <span class="text-danger">*</span></label>
                    <div class="col-lg-12">
                        <input type="text" name="invoice_no" id="invoice_no" value="{{ $invoice_no }}" class="form-control input-flat">
                        <label id="invoice_no-error" class="error invalid-feedback animated fadeInDown" for="invoice_no"></label>
                    </div>
                </div>
            </div>
            <div class="col-sm-2">
                <div class="form-group row">
                    <label class="col-lg-12 col-form-label text-left" for="invoice_date">Date <span class="text-danger">*</span></label>
                    <div class="col-lg-12">
                        <input class="form-control custom_date_picker" type="text" id="invoice_date" name="invoice_date" placeholder="dd-mm-yyyy" data-date-format="dd-mm-yyyy" value="{{ date("d-m-Y") }}">
                        <label id="invoice_date-error" class="error invalid-feedback animated fadeInDown" for="invoice_date"></label>
                    </div>
                </div>
            </div>
        </div>
    </form>
    <div class="table-responsive">
        <table id="items">
            <thead>
                <tr>
                    <th>Item</th>
                    <th width="100px">HSN</th>
                    <th width="100px">Packing Qty</th>
                    <th width="100px">Packing</th>
                    <th width="100px">Weight</th>
                    <th width="100px">Quantity (Kg)</th>
                    <th width="100px">Unit Cost</th>
                    <th class="text-right">Price</th>
                </tr>
            </thead>
            <tbody id="itemstbody">
                <tr class="item-row addnew">
                    <td class="item-name">
                        <div class="delete-wpr">
                            <select name="item_name" id="item_name1" class="item_name">
                                <option></option>
                                @foreach($products as $product)
                                    <option value="{{ $product->id }}">{{ $product->title }}</option>
                                @endforeach
                            </select>
                            <!-- <input class="form-control item_name" id="item_name1" name="item_name1" type="text"> -->
                            <label id="item_name1-error" class="error invalid-feedback animated fadeInDown" for="item_name1"></label>
                        </div>
                    </td>
                    <td width="100px">
                        <input class="form-control hsn" id="hsn1" name="hsn" type="text">
                        <label id="hsn1-error" class="error invalid-feedback animated fadeInDown" for="hsn1"></label>
                    </td>
                    <td width="100px">
                        <input class="form-control packing_qty" id="packing_qty1" name="packing_qty" type="number" min="1">
                        <label id="packing_qty1-error" class="error invalid-feedback animated fadeInDown" for="packing_qty1"></label>
                    </td>
                    <td width="100px">
                        <select name="packing_name" id="packing_name1" class="packing_name">
                            <option value="Bag">Bag</option>
                            <option value="Carbo">Carbo</option>
                            <option value="Drum">Drum</option>
                        </select>
                    </td>
                    <td width="100px">
                        <input class="form-control weight_of_packing" id="weight_of_packing1" name="weight_of_packing" type="number" min="1">
                        <label id="weight_of_packing1-error" class="error invalid-feedback animated fadeInDown" for="weight_of_packing1"></label>
                    </td>
                    <td width="100px">
                        <div class="prse text-center"><span id="quantity1" class="quantity">0</span></div>
                        <!-- <input class="form-control quantity qty" name="quantity" type="number" min="1"> -->
                        <!-- <label id="quantity-error" class="error invalid-feedback animated fadeInDown" for="quantity"></label> -->
                        <label id="quantity1-error" class="error invalid-feedback animated fadeInDown" for="quantity1"></label>
                    </td>
                    <td width="100px">
                        <input class="form-control unitcost cost" id="price1" placeholder="0.00" type="number" name="price" value="">
                        <label id="price1-error" class="error invalid-feedback animated fadeInDown" for="price1"></label>
                    </td>
                    <td class="subt_price"><div class="prse text-right"><i class="fa fa-inr" aria-hidden="true"></i><span class="price proprice sub_price">0.00</span></div></td>
                </tr>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="8">
                        <button type="button" class="btn btn-light" id="addrow" style="margin-bottom: 5px;">New Item</button>
                    </td>
                </tr>
                <tr class="fullrow" style="border-top: 1px solid #e4e4e4;">
                    <td class="total-line" colspan="7" style="border-right: 1px solid #e4e4e4;">Sub Total</td>
                    <td class="total-value"><div id="subtotal_html">0.00</div><i class="fa fa-inr" aria-hidden="true"></i></td>
                </tr>
                <tr class="fullrow sgst-row" style="display: none; border-top: 1px solid #e4e4e4;">
                    <td class="total-line" colspan="7" style="border-right: 1px solid #e4e4e4;">SGST(<span id="sgst_apply_percent"></span>%)</td>
                    <td class="total-value"><div id="sgst_html">0.00</div><i class="fa fa-inr" aria-hidden="true"></i></td>
                </tr>
                <tr class="fullrow cgst-row" style="display: none; border-top: 1px solid #e4e4e4;">
                    <td class="total-line" colspan="7" style="border-right: 1px solid #e4e4e4;">CGST(<span id="cgst_apply_percent"></span>%)</td>
                    <td class="total-value"><div id="cgst_html">0.00</div><i class="fa fa-inr" aria-hidden="true"></i></td>
                </tr>
                <tr class="fullrow igst-row" style="display: none; border-top: 1px solid #e4e4e4;">
                    <td class="total-line" colspan="7" style="border-right: 1px solid #e4e4e4;">IGST(<span id="igst_apply_percent"></span>%)</td>
                    <td class="total-value"><div id="igst_html">0.00</div><i class="fa fa-inr" aria-hidden="true"></i></td>
                </tr>
                <tr class="fullrow" style="border-top: 1px solid #e4e4e4; border-bottom: 1px solid #e4e4e4;">
                    <td class="total-line" colspan="7" style="border-right: 1px solid #e4e4e4;">Total</td>
                    <td class="total-value"><div id="grandTotal_html">0.00</div><i class="fa fa-inr" aria-hidden="true"></i></td>
                </tr>
                <!-- <tr class="fullrow">
                    <td colspan="3">Outstanding Amount</td>
                    <td><input type="number" name="outstanding_amount" id="outstanding_amount" class="form-control"></td>
                </tr> -->
                <!-- <tr class="fullrow">
                    <td colspan="3">Total Payable Amount</td>
                    <td class="total-value"><div id="total_payable_amount">0.00</div><i class="fa fa-inr" aria-hidden="true"></i></td>
                </tr> -->
            </tfoot>
        </table>
    </div>
    <input type="hidden" id="subtotal" name="subtotal" value="">
    <input type="hidden" id="gstType" name="gstType" value="">
    <input type="hidden" id="gstPercentage" name="gstPercentage" value="">
    <input type="hidden" id="gstAmount" name="gstAmount" value="">
    <input type="hidden" id="grandTotal" name="grandTotal" value="">
    <input type="hidden" id="addednum" name="addednum" value="1">
    <div class="row mt-5">
        <div class="col-sm-2 ml-auto">
            <button type="button" class="btn btn-primary btn-lg w-100 mt-3" id="invoice_submit" name="invoice_submit" action="add">Save <i class="fa fa-circle-o-notch fa-spin loadericonfa" style="display:none;"></i></button>
        </div>
    </div>
</div>
