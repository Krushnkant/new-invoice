@extends('admin.layout')

@section('content')
    <div class="row page-titles mx-0">
        <div class="col p-md-0">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('admin/dashboard') }}">Home</a></li>
                @if(isset($action) && $action=='create')
                    <li class="breadcrumb-item"><a href="{{ url('admin/invoice') }}">Invoice</a></li>
                    <li class="breadcrumb-item active"><a href="javascript:void(0)">Create Invoice</a></li>
                @elseif(isset($action) && $action=='edit')
                    <li class="breadcrumb-item"><a href="{{ url('admin/invoice') }}">Invoice</a></li>
                    <li class="breadcrumb-item active"><a href="javascript:void(0)">Edit Invoice</a></li>
                @else
                <li class="breadcrumb-item active"><a href="javascript:void(0)">Invoice</a></li>
                @endif
            </ol>
        </div>
    </div>

    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">
                            @if(isset($action) && $action=='create')
                                Create Invoice
                            @elseif(isset($action) && $action=='edit')
                                Edit Invoice
                            @else
                                Invoice List
                            @endif
                        </h4>

                        @if(isset($action) && $action=='list')
                        <div class="action-section">
                            <button type="button" class="btn btn-primary" id="AddInvoiceBtn"><i class="fa fa-plus" aria-hidden="true"></i></button>
                            {{-- <button class="btn btn-danger" onclick="deleteMultipleAttributes()"><i class="fa fa-trash" aria-hidden="true"></i></button>--}}
                        </div>

                        <!-- <div class="row">
                            <div class="col-md-3">
                                <select class="form-control" id="user_id_filter">
                                    <option></option>
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}">{{ $user->full_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3 input-group">
                                <input type="text" class="form-control custom_date_picker" id="start_date" name="start_date" placeholder="Start Date" data-date-format="yyyy-mm-dd"> <span class="input-group-append"><span class="input-group-text"><i class="mdi mdi-calendar-check"></i></span></span>
                            </div>
                            <div class="col-md-3 input-group">
                                <input type="text" class="form-control custom_date_picker" id="end_date" name="end_date" placeholder="End Date" data-date-format="yyyy-mm-dd"> <span class="input-group-append"><span class="input-group-text"><i class="mdi mdi-calendar-check"></i></span></span>
                            </div>
                            <div class="col-md-3">
                                <select class="form-control" id="product_id_filter">
                                    <option></option>
                                    @foreach($products as $product)
                                        <option value="{{ $product->id }}">{{ $product->title_english }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 mt-3">
                                <button type="button" class="btn btn-outline-primary" id="export_excel_btn" >Export to Excel <i class="fa fa-circle-o-notch fa-spin loadericonfa" style="display:none;"></i></button>
                                <button type="button" class="btn btn-outline-primary" id="export_pdf_btn" >Export to PDF <i class="fa fa-circle-o-notch fa-spin loadericonfa" style="display:none;"></i></button>
                                <button type="button" class="btn btn-outline-primary" id="item_export_pdf_btn" >Item Export PDF<i class="fa fa-circle-o-notch fa-spin loadericonfa" style="display:none;"></i></button>
                            </div>
                        </div> -->
                        @endif

                        @if(isset($action) && $action=='list')
                            <div class="table-responsive">
                                <table id="invoice_table" class="table zero-configuration customNewtable" style="width:100%">
                                    <thead>
                                    <tr>
                                        <th></th>
                                        <th>No.</th>
                                        <th>Invoice No</th>
                                        <th>Party</th>
                                        <!-- <th>Qty</th> -->
                                        <th>Amount</th>
                                        <th>Date</th>
                                        <th>Action</th>
                                        <!-- <th>Quantity</th> -->
                                        <!-- <th>Amount</th> -->
                                        <!-- <th>Amount transfer</th>
                                        <th>Payment type</th>
                                        <th>Outstanding amount</th>
                                        <th>Total payable amount</th> -->
                                    </tr>
                                    </thead>
                                    <tfoot>
                                    <tr>
                                        <th></th>
                                        <th>No.</th>
                                        <th>Invoice No</th>
                                        <th>Party</th>
                                        <!-- <th>Qty</th> -->
                                        <th>Amount</th>
                                        <th>Date</th>
                                        <th>Action</th>
                                        <!-- <th>Quantity</th> -->
                                        <!-- <th>Amount</th> -->
                                        <!-- <th>Amount transfer</th>
                                        <th>Payment type</th>
                                        <th>Outstanding amount</th>
                                        <th>Total payable amount</th> -->
                                    </tr>
                                    </tfoot>
                                </table>
                            </div>
                        @endif

                        @if(isset($action) && $action=='create')
                            @include('admin.invoice.create')
                        @endif

                        @if(isset($action) && $action=='edit')
                            @include('admin.invoice.edit')
                        @endif

                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="DeleteInvoiceModal">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Remove Invoice</h5>
                </div>
                <div class="modal-body">
                    Are you sure you wish to remove this Invoice?
                </div>
                <div class="modal-footer">
                    <button class="btn btn-default" data-dismiss="modal" type="button">Cancel</button>
                    <button class="btn btn-danger" id="RemoveInvoiceSubmit" type="submit">Remove <i class="fa fa-circle-o-notch fa-spin removeloadericonfa" style="display:none;"></i></button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')
<!-- Invoice JS start -->
<script type="text/javascript">
var table;

$('body').on('click', '#AddInvoiceBtn', function () {
    location.href = "{{ route('admin.invoice.add') }}";
});

$('body').on('click', '#editInvoiceBtn', function () {
    var invoice_id = $(this).attr('data-id');
    var url = "{{ url('admin/invoice/edit') }}" + "/" + invoice_id;
    window.open(url,"_blank");
});

$(document).ready(function() {
    invoice_table(true);

    $('#invoice_table tbody').on('click', 'td.details-control', function () {
        var tr = $(this).closest('tr');
        var row = table.row( tr );

        if ( row.child.isShown() ) {
            // This row is already open - close it
            row.child.hide();
            tr.removeClass('shown');
        } else {
            // Open this row
            row.child( format(row.data()) ).show();
            tr.addClass('shown');
        }
    });

    $('#customer_name').select2({
        width: '100%',
        placeholder: "Select...",
        allowClear: false
    });

    $('#transporter_name').select2({
        width: '100%',
        placeholder: "Select...",
        allowClear: false
    });

    $(".item_name").each(function() {
        var id = $(this).attr('id');
        $('#'+id).select2({
            width: '100%',
            placeholder: "Select...",
            allowClear: false
        });
    });

    $(".packing_name").each(function() {
        var id = $(this).attr('id');
        $('#'+id).select2({
            width: '100%',
            placeholder: "Select...",
            allowClear: false
        });
    })

    $('#user_id_filter').select2({
        width: '100%',
        placeholder: "Select User",
        allowClear: true
    });

    $('#product_id_filter').select2({
        width: '100%',
        placeholder: "Select Product",
        allowClear: true
    });

    $("#addrow").click(function(){
        $("#addrow").prop('disabled', true);
        var addednum = $("#addednum").val();
        $.ajax({
            type: 'POST',
            url: "{{ route('admin.invoice.add_row_item') }}",
            data: {_token: '{{ csrf_token() }}', total_item: addednum},
            success: function (res) {
                $("#itemstbody").append(res['html']);
                $("#addednum").val(res['next_item']);
                $('#item_name'+res['next_item']).select2({
                    width: '100%',
                    placeholder: "Select...",
                    allowClear: false
                });
                $('#packing_name_'+res['next_item']).select2({
                    width: '100%',
                    allowClear: false
                });
                $("#addrow").prop('disabled', false);
            },
            error: function (data) {

            }
        });
    });
});

function format ( d ) {
    // `d` is the original data object for the row
    return d.table1;
}

function invoice_table(is_clearState=false){
    if(is_clearState){
        $('#invoice_table').DataTable().state.clear();
    }
    var user_id_filter = $("#user_id_filter").val();
    var start_date = $("#start_date").val();
    var end_date = $("#end_date").val();
    var hideFromExport = [6];

    table = $('#invoice_table').DataTable({
        "destroy": true,
        "processing": true,
        "serverSide": true,
        "pageLength": 100,
        'stateSave': function(){
            if(is_clearState){
                return false;
            }
            else{
                return true;
            }
        },
        buttons: [
            {
                extend: 'excel',
                // text: 'Export to Excel',
                exportOptions: {
                    /*columns: function ( idx, data, node ) {
                        var isVisible = table.column( idx ).visible();
                        var isNotForExport = $.inArray( idx, hideFromExport ) !== -1;
                        return ((isVisible && !isNotForExport) || !isVisible) ? true : false;
                    },*/
                    columns: [0,1,6,2,3,function ( idx, data, node ) {
                        var isVisible = table.column( idx ).visible();
                        return (!isVisible) ? true : false;
                    }],
                    modifier: {
                        page: 'current'
                    }
                }
            }
        ],
        "ajax":{
            "url": "{{ url('admin/allInvoicelist') }}",
            "dataType": "json",
            "type": "POST",
            "data":{ _token: '{{ csrf_token() }}', user_id_filter: user_id_filter, start_date: start_date, end_date: end_date},
            // "dataSrc": ""
        },
        'columnDefs': [
            { "width": "20px", "targets": 0 },
            { "width": "50px", "targets": 1 },
            { "width": "100px", "targets": 2 },
            { "width": "230px", "targets": 3 },
            { "width": "100px", "targets": 4 },
            { "width": "180px", "targets": 5 },
            // { "width": "200px", "targets": 6 },
            // { "width": "200px", "targets": 7 },
            // { "width": "5px", "visible": false ,"targets": 8 },
            // { "width": "5px", "visible": false ,"targets": 9 },
            // { "width": "5px", "visible": false ,"targets": 10 },
            // { "width": "5px", "visible": false ,"targets": 11 },
            // { "width": "5px", "visible": false ,"targets": 12 },
            // { "width": "5px", "visible": false ,"targets": 13 },
        ],
        "columns": [
            {"className": 'details-control', "orderable": false, "data": null, "defaultContent": ''},
            {data: 'id', name: 'id', class: "text-center", orderable: false ,
                render: function (data, type, row, meta) {
                    return meta.row + meta.settings._iDisplayStart + 1;
                }
            },
            {data: 'invoice_no', name: 'invoice_no', orderable: false, class: "text-left"},
            {data: 'customer_info', name: 'customer_info', orderable: false, class: "text-left multirow"},
            // {data: 'total_qty', name: 'total_qty', orderable: false, class: "text-left "},
            {data: 'amount', name: 'amount', orderable: false, class: "text-left multirow"},
            {data: 'invoice_date', name: 'invoice_date', orderable: false, class: "text-left"},
            {data: 'action', name: 'action', orderable: false, searchable: false, class: "text-center"},
            // {data: 'quantity', name: 'quantity', orderable: false, searchable: false},
            // {data: 'final_amount', name: 'final_amount', orderable: false, searchable: false},
            // {data: 'amount_transfer', name: 'amount_transfer', orderable: false, searchable: false},
            // {data: 'payment_type', name: 'payment_type', orderable: false, searchable: false},
            // {data: 'outstanding_amount', name: 'outstanding_amount', orderable: false, searchable: false},
            // {data: 'total_payable_amount', name: 'total_payable_amount', orderable: false, searchable: false},
        ]
    });
}

function removeRow(rowid,clickfrom){
    $("#"+rowid).closest("tr").remove();
    update_total();
}

// $("#language").change(function() {
//     var language = $("#language").val();
//     $.ajax({
//         type: 'POST',
//         url: "{{ route('admin.invoice.change_products') }}",
//         data: {_token: '{{ csrf_token() }}', language: language},
//         success: function (res) {
//             $("#item_name_1").empty();
//             $("#item_name_1").append(res['html']);
//         },
//         error: function (data) {

//         }
//     });
// });

$('body').on('change', '#customer_name', function () {
    var consignee_id = $(this).val();
    $.ajax({
        type: 'POST',
        url: "{{ route('admin.invoice.change_inv_consignee') }}",
        data: {_token: '{{ csrf_token() }}', consignee_id: consignee_id},
        success: function (res) {
            // console.log(res);
            if(res.gst_type === 'csgst'){
                $('#sgst_apply_percent').html(res.gst_percent);
                $('#cgst_apply_percent').html(res.gst_percent);
                $('#igst_apply_percent').html('');
                $('.sgst-row').fadeIn();
                $('.cgst-row').fadeIn();
                $('.igst-row').fadeOut();
            } else {
                $('#sgst_apply_percent').html('');
                $('#cgst_apply_percent').html('');
                $('#igst_apply_percent').html(res.gst_percent);
                $('.sgst-row').fadeOut();
                $('.cgst-row').fadeOut();
                $('.igst-row').fadeIn();
            }
            $("#gstType").val(res.gst_type);
            $("#gstPercentage").val(res.gst_percent);
            update_total();
        },
        error: function (data) {

        }
    });
});

$('body').on('change', '.item_name', function () {

    var product_id = $(this).val();
    var thi = $(this);

    $.ajax({
        type: 'POST',
        url: "{{ route('admin.invoice.change_product_price') }}",
        data: {_token: '{{ csrf_token() }}', product_id: product_id},
        success: function (res) {
            $(thi).parents('.item-row').find('.unitcost').val(res);
            $(thi).parents('.item-row').find('.unitcost').trigger('change');
        },
        error: function (data) {

        }
    });
});

$('body').on('change', '.unitcost', function () {

    var price = $(this).val();
    var qty = $(this).parents('.item-row').find('.quantity').html();

    var final_price_item = (price * qty);

    if(final_price_item > 0) {
        $(this).parents('.item-row').find('.proprice').html(final_price_item);
    } else {
        $(this).parents('.item-row').find('.proprice').html("0.00");
    }

    update_total();
});

$('body').on('change', '.packing_qty', function () {
    
    var weight = $(this).parents('.item-row').find('.weight_of_packing').val();
    var price = $(this).parents('.item-row').find('.unitcost').val();
    var package_qty = $(this).val();

    var newqty = weight * package_qty;
    var final_price_item = (price * newqty);

    $(this).parents('.item-row').find('.quantity').html(newqty);
    $(this).parents('.item-row').find('.unitcost').trigger('change');

    update_total();
});

$('body').on('change', '.weight_of_packing', function () {
    
    var packing_qty = $(this).parents('.item-row').find('.packing_qty').val();
    var price = $(this).parents('.item-row').find('.unitcost').val();
    var weight_of_packing = $(this).val();

    var newqty = packing_qty * weight_of_packing;
    var final_price_item = (price * newqty);

    $(this).parents('.item-row').find('.quantity').html(newqty);
    $(this).parents('.item-row').find('.unitcost').trigger('change');

    update_total();
});

// $('body').on('change', '.discount', function () {
//     var price = $(this).parents('.item-row').find('.unitcost').val();
//     var qty = $(this).parents('.item-row').find('.quantity').val();
//     var discount = $(this).val();

//     var final_price_item = (price * qty) - discount;

//     if(final_price_item > 0) {
//         $(this).parents('.item-row').find('.proprice').html(final_price_item);
//     } else {
//         $(this).parents('.item-row').find('.proprice').html("0.00");
//     }

//     update_total();
// });

function numberformat(amount){
    return (Math.round(amount * 100) / 100).toFixed(2);
}

function update_total() {
    var final_amount = 0;
    var gstAmount = 0;
    var sub_total = 0;
    var gstAmt = 0;
    var gstType = $("#gstType").val();
    var gstPercentage = $("#gstPercentage").val();

    $(".price").each(function() {
        if( $(this).html() > 0 ) {
            sub_total = parseFloat(sub_total) + parseFloat($(this).html());
        }
    });
    gstAmount = (sub_total * gstPercentage) / 100;

    if(gstType == 'igst'){
        gstAmt = gstAmount;
        $("#igst_html").html( numberformat(gstAmount) );
    } else {
        gstAmt = gstAmount * 2;
        $("#sgst_html").html( numberformat(gstAmount) );
        $("#cgst_html").html( numberformat(gstAmount) );
    }

    grandTotal = sub_total + gstAmt;

    $("#subtotal_html").html( numberformat(sub_total) );
    $("#subtotal").val(sub_total);
    $("#gstAmount").val(gstAmount);
    $('#grandTotal_html').html( numberformat(grandTotal) );
    $('#grandTotal').val(grandTotal);
}

// $('body').on('change', '#outstanding_amount', function () {
//     update_total();
// });

$('body').on('click', '#invoice_submit', function () {

    $(this).prop('disabled',true);
    $(this).find('.loadericonfa').show();
    var btn = $(this);

    var validate_invoice = validateInvoice();
    var validate_invoice_items = validateInvoiceItems($(btn).attr('action'));

    if(validate_invoice == true && validate_invoice_items == true) {
        var formData = new FormData($('#invoiceForm')[0]);
        var cnt = 1;
        var product_ids = [];
        $('.item-row').each(function () {
            var thi = $(this);
            var InvoiceItemForm = {
                "item_name":$(thi).find('.item_name').val(),
                "packing_qty":$(thi).find('.packing_qty').val(),
                "packing_name":$(thi).find('.packing_name').val(),
                "weight_of_packing":$(thi).find('.weight_of_packing').val(),
                "quantity":$(thi).find('.quantity').html(),
                "price":$(thi).find('.unitcost').val(),
                "final_price":$(thi).find('.sub_price').html()
            };

            formData.append("InvoiceItemForm" + cnt, JSON.stringify(InvoiceItemForm));
            product_ids.push($(thi).find('.item_name').val());
            cnt++;
        });
        formData.append("subtotal", $("#subtotal").val());
        formData.append("gstType", $("#gstType").val());
        formData.append("gstPercentage", $("#gstPercentage").val());
        formData.append("gstAmount", $("#gstAmount").val());
        formData.append("grandTotal", $("#grandTotal").val());
        formData.append("action", $(btn).attr('action'));
        formData.append("total_items", $('.item-row').length);
        formData.append("product_ids", product_ids);

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $.ajax({
            type: 'POST',
            url: "{{ route('admin.invoice.save') }}",
            data: formData,
            dataType: 'json',
            cache: false,
            processData: false,
            contentType: false,
            // contentType: 'json',
            success: function (res) {
                if(res['status'] == 200){
                    
                    if(res['action'] == "add"){
                        toastr.success("Invoice has been created Successfully", 'Success', {timeOut: 5000});
                    }
                    else if(res['action'] == "update"){
                        toastr.success("Invoice has been Updated Successfully", 'Success', {timeOut: 5000});
                    }
                    location.href = "{{ route('admin.invoice.list') }}";
                }
            },
            error: function (data) {
                $(btn).prop('disabled',false);
                $(btn).find('.loadericonfa').hide();
                toastr.error("Please try again",'Error',{timeOut: 5000});
            }
        });
    } else {
        $(btn).prop('disabled',false);
        $(btn).find('.loadericonfa').hide();
    }
});

function validateInvoice() {

    var isValidParty = false;
    var isValidTransporter = false;
    var isValidTransportMode = false;
    var isValidInvoice = false;
    var isValidInvoiceDate = false;

    var partyVal = $("#customer_name").val();
    var transporterVal = $("#transporter_name").val();
    var transportModeVal = $("#transport_mode").val();
    var invoiceVal = $("#invoice_no").val();
    var invoiceDateVal = $("#invoice_date").val();

    if( partyVal === '' ){
        $("#customer_name-error").html("Please Select a Party").show();
    } else {
        $("#customer_name-error").html("").hide();
        isValidParty = true;
    }

    if( transporterVal === '' ){
        $("#transporter_name-error").html("Please Select a Transporter").show();
    } else {
        $("#transporter_name-error").html("").hide();
        isValidTransporter = true;
    }

    if( transportModeVal === '' ){
        $("#transport_mode-error").html("Please Provide Transport Mode").show();
    } else {
        $("#transport_mode-error").html("").hide();
        isValidTransportMode = true;
    }

    if( invoiceVal === '' ){
        $("#invoice_no-error").html("Please Provide an Invoice No").show();
    } else {
        $("#invoice_no-error").html("").hide();
        isValidInvoice = true;
    }

    if( invoiceDateVal === '' ){
        $("#invoice_date-error").html("Please Provide a Date").show();
    } else {
        $("#invoice_date-error").html("").hide();
        isValidInvoiceDate = true;
    }

    if( isValidParty === true && isValidTransporter === true && isValidTransportMode === true && isValidInvoice === true && isValidInvoiceDate === true ){
        return true;
    }

    return false;
}

function validateInvoiceItems(action) {
    
    var isValidItemName = false;
    var isValidUnitCost = false;
    var isValidPackingQty = false;
    var isValidPackingWeight = false;

    var itemNameArray = [];
    var unitCostArray = [];
    var packingQtyArray = [];
    var weightArray = [];

    $(".item_name").each(function() {
        var itemFieldVal = $(this).val();
        var itemFieldId = $(this).attr('id');
        var rowNo = itemFieldId.replace('item_name','');;
        if( itemFieldVal === '' ){
            $("#"+itemFieldId+"-error").html("Please Select Item").show();
            itemNameArray.push(0);
        } else {
            $("#"+itemFieldId+"-error").html("").hide();
            var check_stock = $.ajax({
                                type: 'POST',
                                url: "{{ route('admin.check_stock') }}",
                                data: { 
                                    _token: '{{ csrf_token() }}', 
                                    product_id: itemFieldVal, 
                                    quantity: $('#quantity'+rowNo).html(), 
                                    action: action 
                                },
                                async:false,
                                success: function (res) {

                                },
                                error: function (data) {

                                }
                            }).responseText;
            if(check_stock != 1){
                $('#quantity'+rowNo+'-error').show().html("Item is not available in stock");
                itemNameArray.push(0);
            } else {
                $('#quantity'+rowNo+'-error').hide().html("");
            }
        }
    });
    if(itemNameArray.length === 0) {
        isValidItemName = true;
    }

    $(".packing_qty").each(function() {
        var packQtyFieldVal = $(this).val();
        var packQtyFieldId = $(this).attr('id');
        if( packQtyFieldVal === '' ){
            $("#"+packQtyFieldId+"-error").html("Please Provide Packing Quantity").show();
            packingQtyArray.push(0);
        } else {
            $("#"+packQtyFieldId+"-error").html("").hide();
        }
    });
    if(packingQtyArray.length === 0) {
        isValidPackingQty = true;
    }

    $(".weight_of_packing").each(function() {
        var weightFieldVal = $(this).val();
        var weightFieldId = $(this).attr('id');
        if( weightFieldVal === '' ){
            $("#"+weightFieldId+"-error").html("Please Provide Weight").show();
            weightArray.push(0);
        } else {
            $("#"+weightFieldId+"-error").html("").hide();
        }
    });
    if(weightArray.length === 0) {
        isValidPackingWeight = true;
    }

    $(".unitcost").each(function() {
        var unitcostFieldVal = $(this).val();
        var unitcostFieldId = $(this).attr('id');
        if( unitcostFieldVal === '' ){
            $("#"+unitcostFieldId+"-error").html("Please Provide Price").show();
            unitCostArray.push(0);
        } else {
            $("#"+unitcostFieldId+"-error").html("").hide();
        }
    });
    if(unitCostArray.length === 0) {
        isValidUnitCost = true;
    }

    if( isValidItemName === true && isValidUnitCost === true && isValidPackingQty === true && isValidPackingWeight === true ){
        return true;
    }

    return false;
}

$('body').on('click', '#deleteInvoiceBtn', function (e) {
    // e.preventDefault();
    var invoice_id = $(this).attr('data-id');
    $("#DeleteInvoiceModal").find('#RemoveInvoiceSubmit').attr('data-id',invoice_id);
});

$('#DeleteInvoiceModal').on('hidden.bs.modal', function () {
    $(this).find("#RemoveInvoiceSubmit").removeAttr('data-id');
});

$('body').on('click', '#RemoveInvoiceSubmit', function (e) {
    $('#RemoveInvoiceSubmit').prop('disabled',true);
    $(this).find('.removeloadericonfa').show();
    e.preventDefault();
    var invoice_id = $(this).attr('data-id');
    $.ajax({
        type: 'GET',
        url: "{{ url('admin/invoice') }}" +'/' + invoice_id +'/delete',
        success: function (res) {
            if(res.status == 200){
                $("#DeleteInvoiceModal").modal('hide');
                $('#RemoveInvoiceSubmit').prop('disabled',false);
                $("#RemoveInvoiceSubmit").find('.removeloadericonfa').hide();
                invoice_table();
                toastr.success("Invoice Deleted",'Success',{timeOut: 5000});
            }

            if(res.status == 400){
                $("#DeleteInvoiceModal").modal('hide');
                $('#RemoveInvoiceSubmit').prop('disabled',false);
                $("#RemoveInvoiceSubmit").find('.removeloadericonfa').hide();
                invoice_table();
                toastr.error("Please try again",'Error',{timeOut: 5000});
            }
        },
        error: function (data) {
            $("#DeleteInvoiceModal").modal('hide');
            $('#RemoveInvoiceSubmit').prop('disabled',false);
            $("#RemoveInvoiceSubmit").find('.removeloadericonfa').hide();
            invoice_table();
            toastr.error("Please try again",'Error',{timeOut: 5000});
        }
    });
});

$('body').on('change', '#user_id_filter', function (e) {
    // e.preventDefault();
    invoice_table(true);
});

$('body').on('change', '#start_date', function (e) {
    // e.preventDefault();
    invoice_table(true);
});

$('body').on('change', '#end_date', function (e) {
    // e.preventDefault();
    invoice_table(true);
});

$("#export_excel_btn").on("click", function() {
    table.button( '.buttons-excel' ).trigger();
});

$('body').on('click', '#export_pdf_btn', function (e) {
    e.preventDefault();
    var user_id_filter = $("#user_id_filter").val();
    var start_date = $("#start_date").val();
    var end_date = $("#end_date").val();
    if(user_id_filter == ""){
        user_id_filter = null;
    }
    if(start_date == ""){
        start_date = null;
    }
    if(end_date == ""){
        end_date = null;
    }
    var url = "{{ url('admin/invoice/report') }}" + "/" + user_id_filter + "/" + start_date + "/" + end_date;
    window.open(url, "_blank");
});

$('body').on('click', '#item_export_pdf_btn', function (e) {
    e.preventDefault();
    var user_id_filter = $("#user_id_filter").val();
    var product_id_filter = $("#product_id_filter").val();
    var start_date = $("#start_date").val();
    var end_date = $("#end_date").val();
    if(user_id_filter == ""){
        user_id_filter = null;
    }
    if(product_id_filter == ""){
        product_id_filter = null;
    }
    if(start_date == ""){
        start_date = null;
    }
    if(end_date == ""){
        end_date = null;
    }
    var url = "{{ url('admin/invoice/itemreport') }}" + "/" + user_id_filter + "/" + start_date + "/" + end_date+ "/" + product_id_filter;
    window.open(url, "_blank");
});

$('body').on('click', '#printBtn', function (e) {
    e.preventDefault();
    var invoice_id = $(this).attr('data-id');
    var url = "{{ url('admin/invoice/pdf') }}" + "/" + invoice_id;
    window.open(url, "_blank");
});

</script>
<!-- Invoice JS end -->
@endsection
