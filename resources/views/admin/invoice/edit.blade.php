<div id="page-wrap" class="table-textarea">
    <form method="post" id="invoiceForm">
        {{ csrf_field() }}
        <input type="hidden" name="invoice_id" value="{{ $invoice->id }}">
        <div class="row">
            <div class="col-sm-2">
                <div class="form-group row mb-0">
                    <label class="col-lg-12 col-form-label" for="">Party <span class="text-danger">*</span></label>
                    <div class="col-lg-12">
                        <select name="customer_name" id="customer_name">
                            <option></option>
                            @foreach($consignees as $consignee)
                                <option value="{{ $consignee->id }}" @if( $invoice->consignee_id == $consignee->id) selected @endif> {{ $consignee->partyname }} [{{ $consignee->id }}]</option>
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
                                <option value="{{ $transporter->id }}" @if( $invoice->transporter_id == $transporter->id) selected @endif> {{ $transporter->transporter_name }}</option>
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
                        <input class="form-control" type="text" id="transport_mode" name="transport_mode" placeholder="Transport Mode" value="{{ $invoice->transport_mode }}">
                        <label id="transport_mode-error" class="error invalid-feedback animated fadeInDown" for="transport_mode"></label>
                    </div>
                </div>
            </div>
            <div class="col-sm-2 ml-auto">
                <div class="form-group row mb-0">
                    <label class="col-lg-12 col-form-label" for="invoice_no">Invoice No <span class="text-danger">*</span></label>
                    <div class="col-lg-12">
                        <input type="text" name="invoice_no" id="invoice_no" value="{{ $invoice->invoice_no }}" class="form-control input-flat">
                        <label id="invoice_no-error" class="error invalid-feedback animated fadeInDown" for="invoice_no"></label>
                    </div>
                </div>
            </div>
            <div class="col-sm-2">
                <div class="form-group row">
                    <label class="col-lg-12 col-form-label text-left" for="invoice_date">Date <span class="text-danger">*</span></label>
                    <div class="col-lg-12">
                        <input class="form-control custom_date_picker" type="text" id="invoice_date" name="invoice_date" placeholder="dd-mm-yyyy" data-date-format="dd-mm-yyyy" value="{{ date('d-m-Y', strtotime($invoice->invoice_date)) }}">
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
                    <th width="100px">Packing Qty</th>
                    <th width="100px">Packing</th>
                    <th width="100px">Weight</th>
                    <th width="100px">Quantity (Kg)</th>
                    <th width="100px">Unit Cost</th>
                    <th class="text-right">Price</th>
                </tr>
            </thead>
            <tbody id="itemstbody">
                <?php $i = 1; ?>
                @foreach($invoice->invoice_item as $invoice_item)
                    <tr class="item-row" id="table-row-{{ $i }}">
                        <td class="item-name">
                            <div class="delete-wpr">
                                <select name="item_name" id="item_name{{ $i }}" class="item_name">
                                    <option></option>
                                    @foreach($products as $product)
                                        <option value="{{ $product->id }}" @if($invoice_item->product_id==$product->id) selected @endif>{{ $product->title }}</option>
                                    @endforeach
                                </select>
                                <label id="item_name{{ $i }}-error" class="error invalid-feedback animated fadeInDown" for="item_name{{ $i }}"></label>
                                @if($i != 1)
                                    <a class="delete" onclick="removeRow('table-row-{{ $i }}',0)" href="javascript:;" title="Remove row">X</a>
                                @endif
                            </div>
                        </td>
                        <td width="100px">
                            <input class="form-control packing_qty" id="packing_qty{{ $i }}" name="packing_qty" type="number" min="1" value="{{ $invoice_item->packing_qty }}">
                            <label id="packing_qty{{ $i }}-error" class="error invalid-feedback animated fadeInDown" for="packing_qty{{ $i }}"></label>
                        </td>
                        <td width="100px">
                            <select name="packing_name" id="packing_name{{ $i }}" class="packing_name">
                                <option value="Bag" @if( $invoice_item->packingType == 'Bag' ) selected @endif>Bag</option>
                                <option value="Carbo" @if( $invoice_item->packingType == 'Carbo' ) selected @endif>Carbo</option>
                                <option value="Drum" @if( $invoice_item->packingType == 'Drum' ) selected @endif>Drum</option>
                            </select>
                        </td>
                        <td width="100px">
                            <input class="form-control weight_of_packing" id="weight_of_packing{{ $i }}" name="weight_of_packing" type="number" min="1" value="{{ $invoice_item->packing_weight }}">
                            <label id="weight_of_packing{{ $i }}-error" class="error invalid-feedback animated fadeInDown" for="weight_of_packing{{ $i }}"></label>
                        </td>
                        <td width="100px">
                            <div class="prse text-center"><span id="quantity{{ $i }}" class="quantity">{{ $invoice_item->quantity }}</span></div>
                            <label id="quantity{{ $i }}-error" class="error invalid-feedback animated fadeInDown" for="quantity{{ $i }}"></label>
                        </td>
                        <td width="200px">
                            <input class="form-control unitcost cost" id="price{{ $i }}" placeholder="0.00" type="number" name="price" value="{{ $invoice_item->price }}">
                            <label id="price{{ $i }}-error" class="error invalid-feedback animated fadeInDown" for="price{{ $i }}"></label>
                        </td>
                        <td class="subt_price"><div class="prse text-right"><i class="fa fa-inr" aria-hidden="true"></i><span class="price proprice sub_price">{{ $invoice_item->final_price }}</span></div></td>
                    </tr>
                    <?php $i++; ?>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="7">
                        <button type="button" class="btn btn-light" id="addrow" style="margin-bottom: 5px;">New Item</button>
                    </td>
                </tr>
                <tr class="fullrow" style="border-top: 1px solid #e4e4e4;">
                    <td class="total-line" colspan="6" style="border-right: 1px solid #e4e4e4;">Sub Total</td>
                    <td class="total-value"><div id="subtotal_html">{{ IND_money_format($invoice->sub_total) }}</div><i class="fa fa-inr" aria-hidden="true"></i></td>
                </tr>
                @if($invConsignee->state_code != $settings->company_statecode)
                    <?php 
                        $styleForScgst = '';
                        $styleForIgst = 'display: none;';
                        $gstAmount = $invoice->sgst_amount;
                        $gstType = 'csgst';
                    ?>
                @endif

                @if($invConsignee->state_code == $settings->company_statecode)
                    <?php 
                        $styleForIgst = '';
                        $styleForScgst = 'display: none;'; 
                        $gstAmount = $invoice->igst_amount;
                        $gstType = 'igst';
                    ?>
                @endif
                <tr class="fullrow sgst-row" style="{{ $styleForScgst }} border-top: 1px solid #e4e4e4;">
                    <td class="total-line" colspan="6" style="border-right: 1px solid #e4e4e4;">SGST(<span id="sgst_apply_percent">{{ $invoice->gst_percentage }}</span>%)</td>
                    <td class="total-value"><div id="sgst_html">{{ IND_money_format($invoice->sgst_amount) }}</div><i class="fa fa-inr" aria-hidden="true"></i></td>
                </tr>
                <tr class="fullrow cgst-row" style="{{ $styleForScgst }} border-top: 1px solid #e4e4e4;">
                    <td class="total-line" colspan="6" style="border-right: 1px solid #e4e4e4;">CGST(<span id="cgst_apply_percent">{{ $invoice->gst_percentage }}</span>%)</td>
                    <td class="total-value"><div id="cgst_html">{{ IND_money_format($invoice->cgst_amount) }}</div><i class="fa fa-inr" aria-hidden="true"></i></td>
                </tr>
                <tr class="fullrow igst-row" style="{{ $styleForIgst }} border-top: 1px solid #e4e4e4;">
                    <td class="total-line" colspan="6" style="border-right: 1px solid #e4e4e4;">IGST(<span id="igst_apply_percent">{{ $invoice->gst_percentage }}</span>%)</td>
                    <td class="total-value"><div id="igst_html">{{ IND_money_format($invoice->igst_amount) }}</div><i class="fa fa-inr" aria-hidden="true"></i></td>
                </tr>
                
                <tr class="fullrow" style="border-top: 1px solid #e4e4e4; border-bottom: 1px solid #e4e4e4;">
                    <td class="total-line" colspan="6" style="border-right: 1px solid #e4e4e4;">Total</td>
                    <td class="total-value"><div id="grandTotal_html">{{ IND_money_format($invoice->final_amount) }}</div><i class="fa fa-inr" aria-hidden="true"></i></td>
                </tr>
            </tfoot>
        </table>
    </div>
    <input type="hidden" id="subtotal" name="subtotal" value="{{ $invoice->sub_total }}">
    <input type="hidden" id="gstType" name="gstType" value="{{ $gstType }}">
    <input type="hidden" id="gstPercentage" name="gstPercentage" value="{{ $invoice->gst_percentage }}">
    <input type="hidden" id="gstAmount" name="gstAmount" value="{{ $gstAmount }}">
    <input type="hidden" id="grandTotal" name="grandTotal" value="{{ $invoice->final_amount }}">
    <input type="hidden" id="addednum" name="addednum" value="{{ count($invoice->invoice_item) }}">
    
    <div class="row mt-5">
        <div class="col-sm-2 ml-auto">
            <button type="button" class="btn btn-primary btn-lg w-100 mt-3" id="invoice_submit" name="invoice_submit" action="update">Save <i class="fa fa-circle-o-notch fa-spin loadericonfa" style="display:none;"></i></button>
        </div>
    </div>
</div>
