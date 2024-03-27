@extends('admin.layout')

@section('content')
    <div class="row page-titles mx-0">
        <div class="col p-md-0">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('admin/dashboard') }}">Home</a></li>
                <li class="breadcrumb-item active"><a href="javascript:void(0)">Party List</a></li>
            </ol>
        </div>
    </div>
    <!-- row -->

    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">Party List</h4>
                        <div class="action-section">
                            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#PartyModal" id="addPartyBtn"><i class="fa fa-plus" aria-hidden="true"></i></button>
                            {{-- <button class="btn btn-danger" onclick="deleteMultipleAttributes()"><i class="fa fa-trash" aria-hidden="true"></i></button>--}}
                        </div>
                        <table id="party_table" class="table zero-configuration customNewtable" style="width:100%">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Party</th>
                                    <th>Address</th>
                                    <th>State</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tfoot>
                                <tr>
                                    <th>No</th>
                                    <th>Party</th>
                                    <th>Address</th>
                                    <th>State</th>
                                    <th>Action</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="PartyModal">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <form class="form-valide" action="" id="partyform" method="post" enctype="multipart/form-data">
                    <div class="modal-header">
                        <h5 class="modal-title" id="formtitle">Add Party</h5>
                        <button type="button" class="close" data-dismiss="modal"><span>×</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div id="attr-cover-spin" class="cover-spin"></div>
                        {{ csrf_field() }}
                        <div class="form-group ">
                            <label class="col-form-label" for="party_name">Party Name <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control input-flat" id="party_name" name="party_name" placeholder="">
                            <div id="party_name-error" class="invalid-feedback animated fadeInDown" style="display: none;"></div>
                        </div>
                        <div class="form-group ">
                            <label class="col-form-label" for="mobile_no">Mobile No</label>
                            <input type="text" class="form-control input-flat" id="mobile_no" name="mobile_no" placeholder="">
                            <div id="mobile_no-error" class="invalid-feedback animated fadeInDown" style="display: none;"></div>
                        </div>
                        <div class="form-group ">
                            <label class="col-form-label" for="gst_no">GSTIN <span class="text-danger">*</span></label>
                            <input type="text" class="form-control input-flat" id="gst_no" name="gst_no" placeholder="">
                            <div id="gst_no-error" class="invalid-feedback animated fadeInDown" style="display: none;"></div>
                        </div>
                        <div class="form-group" id="address_div">
                            <label class="col-form-label" for="address">Address <span class="text-danger">*</span>
                            </label>
                            <textarea class="form-control input-flat" id="address" name="address" placeholder="" rows="5"></textarea>
                            <div id="address-error" class="invalid-feedback animated fadeInDown" style="display: none;"></div>
                        </div>
                        <div class="form-group" id="state_div">
                            <label class="col-form-label" for="state">State <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control input-flat" id="state" name="state" placeholder="" value="">
                            <div id="state-error" class="invalid-feedback animated fadeInDown" style="display: none;"></div>
                        </div>
                        <div class="form-group" id="state_code_div">
                            <label class="col-form-label" for="state_code">State Code <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control input-flat" id="state_code" name="state_code" placeholder="" value="">
                            <div id="state_code-error" class="invalid-feedback animated fadeInDown" style="display: none;"></div>
                        </div>
                        
                    </div>
                    <div class="modal-footer">
                        <input type="hidden" name="consignee_id" id="consignee_id" value="">
                        {{-- <button type="button" class="btn btn-outline-primary" data-dismiss="modal">Close</button>--}}
                        <button type="button" class="btn btn-outline-primary" id="save_newPartyBtn">Save & New <i class="fa fa-circle-o-notch fa-spin loadericonfa" style="display:none;"></i></button>
                        <button type="button" class="btn btn-primary" id="save_closePartyBtn">Save & Close <i class="fa fa-circle-o-notch fa-spin loadericonfa" style="display:none;"></i></button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="DeletePartyModal">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Remove Party</h5>
                </div>
                <div class="modal-body">
                    Are you sure you wish to remove this Party?
                </div>
                <div class="modal-footer">
                    <button class="btn btn-default" data-dismiss="modal" type="button">Cancel</button>
                    <button class="btn btn-danger" id="RemovePartySubmit" type="submit">Remove <i class="fa fa-circle-o-notch fa-spin removeloadericonfa" style="display:none;"></i></button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')
<!-- party list JS start -->
<script type="text/javascript">
    $(document).ready(function() {
        set_table_view(true);
    });

    function save_party(btn,btn_type){
        $(btn).prop('disabled',true);
        $(btn).find('.loadericonfa').show();
        var regexGst = /^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$/;
        var action = $(btn).attr('data-action');
        var formData = new FormData($("#partyform")[0]);
        formData.append('action', action);

        $.ajax({
            type: 'POST',
            url: "{{ url('admin/addorupdateparty') }}",
            data: formData,
            processData: false,
            contentType: false,
            success: function (res) {
                if(res.status == 'failed'){
                    $(btn).prop('disabled',false);
                    $(btn).find('.loadericonfa').hide();

                    if (res.errors.party_name) {
                        $('#party_name-error').show().text(res.errors.party_name);
                    } else {
                        $('#party_name-error').hide();
                    }

                    if (res.errors.mobile_no) {
                        $('#mobile_no-error').show().text(res.errors.mobile_no);
                    } else {
                        $('#mobile_no-error').hide();
                    }

                    if (res.errors.gst_no) {
                        $('#gst_no-error').show().text(res.errors.gst_no);
                    } else {
                        if (regexGst.test($("#gst_no").val())) {
                            $('#gst_no-error').hide();
                        } else {
                            $('#gst_no-error').show().text('Please enter a valid GST No');
                        }
                    }

                    if (res.errors.state) {
                        $('#state-error').show().text(res.errors.state);
                    } else {
                        $('#state-error').hide();
                    }

                    if (res.errors.address) {
                        $('#address-error').show().text(res.errors.address);
                    } else {
                        $('#address-error').hide();
                    }

                    if (res.errors.state_code) {
                        $('#state_code-error').show().text(res.errors.state_code);
                    } else {
                        $('#state_code-error').hide();
                    }
                }

                if(res.status == 200){
                    if(btn_type == 'save_close'){
                        $("#PartyModal").modal('hide');
                        $(btn).prop('disabled',false);
                        $(btn).find('.loadericonfa').hide();
                        if(res.action == 'add'){
                            set_table_view(true);
                            toastr.success("Party has been Added Successfully",'Success',{timeOut: 5000});
                        }
                        if(res.action == 'update'){
                            set_table_view();
                            toastr.success("Party has been Updated Successfully",'Success',{timeOut: 5000});
                        }
                    }

                    if(btn_type == 'save_new'){
                        $(btn).prop('disabled',false);
                        $(btn).find('.loadericonfa').hide();
                        $("#PartyModal").find('form').trigger('reset');
                        $("#PartyModal").find("#save_newPartyBtn").removeAttr('data-action');
                        $("#PartyModal").find("#save_closePartyBtn").removeAttr('data-action');
                        $("#PartyModal").find("#save_newPartyBtn").removeAttr('data-id');
                        $("#PartyModal").find("#save_closePartyBtn").removeAttr('data-id');
                        $('#consignee_id').val("");
                        $('#gst_no-error').html("");
                        $('#party_name-error').html("");
                        $('#mobile_no-error').html("");
                        $('#state_code-error').html("");
                        $('#state-error').html("");
                        $('#address-error').html("");
                        $("#party_name").focus();
                        if(res.action == 'add'){
                            set_table_view(true);
                            toastr.success("Party has been Added Successfully",'Success',{timeOut: 5000});
                        }
                        if(res.action == 'update'){
                            set_table_view();
                            toastr.success("Party has been Updated Successfully",'Success',{timeOut: 5000});
                        }
                        $('#party_table').DataTable().ajax.reload();
                    }
                }

                if(res.status == 400){
                    // $("#PartyModal").modal('hide');
                    $(btn).prop('disabled',false);
                    $(btn).find('.loadericonfa').hide();
                    set_table_view();
                    toastr.error("Please try again",'Error',{timeOut: 5000});
                }
            },
            error: function (data) {
                // $("#PartyModal").modal('hide');
                $(btn).prop('disabled',false);
                $(btn).find('.loadericonfa').hide();
                set_table_view();
                toastr.error("Please try again",'Error',{timeOut: 5000});
            }
        });
    }

    $('body').on('click', '#save_newPartyBtn', function () {
        save_party($(this),'save_new');
    });

    $('body').on('click', '#save_closePartyBtn', function () {
        save_party($(this),'save_close');
    });

    $('#PartyModal').on('shown.bs.modal', function (e) {
        $("#party_name").focus();
    });

    $('#PartyModal').on('hidden.bs.modal', function () {
        $(this).find('form').trigger('reset');
        $(this).find("#save_newPartyBtn").removeAttr('data-action');
        $(this).find("#save_closePartyBtn").removeAttr('data-action');
        $(this).find("#save_newPartyBtn").removeAttr('data-id');
        $(this).find("#save_closePartyBtn").removeAttr('data-id');
        $('#consignee_id').val("");
        $('#gst_no-error').html("");
        $('#party_name-error').html("");
        $('#mobile_no-error').html("");
        $('#state_code-error').html("");
        $('#state-error').html("");
        $('#address-error').html("");
    });

    $('#DeletePartyModal').on('hidden.bs.modal', function () {
        $(this).find("#RemovePartySubmit").removeAttr('data-id');
    });

    function set_table_view(is_clearState=false){

        if(is_clearState){
            $('#party_table').DataTable().state.clear();
        }

        $('#party_table').DataTable({
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
            "ajax":{
                "url": "{{ url('admin/allpartylist') }}",
                "dataType": "json",
                "type": "POST",
                "data":{ _token: '{{ csrf_token() }}'},
                // "dataSrc": ""
            },
            "order": [[ 1, "ASC" ]],
            'columnDefs': [
                { "width": "10%", "targets": 0 },
                { "width": "20%", "targets": 1 },
                { "width": "50%", "targets": 2 },
                { "width": "10%", "targets": 3 },
                { "width": "10%", "targets": 4 }
            ],
            "columns": [
                {data: 'id', name: 'id', class: "text-center", orderable: false,
                    render: function (data, type, row, meta) {
                        return meta.row + meta.settings._iDisplayStart + 1;
                    }
                },
                {data: 'party', name: 'party', class: "text-left multirow", orderable: false},
                {data: 'address', name: 'address', class: "text-left"},
                {data: 'state', name: 'state', class: "text-left"},
                {data: 'action', name: 'action', orderable: false, searchable: false, class: "text-center"},
            ]
        });
    }

    $('body').on('click', '#addPartyBtn', function (e) {
        $("#PartyModal").find('.modal-title').html("Add Party");
    });

    $('body').on('click', '#editPartyBtn', function () {
        var consignee_id = $(this).attr('data-id');
        $.get("{{ url('admin/consignee') }}" +'/' + consignee_id +'/edit', function (data) {
            $('#PartyModal').find('.modal-title').html("Edit Party");
            $('#PartyModal').find('#save_closePartyBtn').attr("data-action", "update");
            $('#PartyModal').find('#save_newPartyBtn').attr("data-action", "update");
            $('#PartyModal').find('#save_closePartyBtn').attr("data-id", consignee_id);
            $('#PartyModal').find('#save_newPartyBtn').attr("data-id", consignee_id);
            $('#consignee_id').val(data.id);
            $('#party_name').val(data.partyname);
            $('#mobile_no').val(data.mobile_no);
            $('#gst_no').val(data.gst_no);
            $('#address').val(data.address);
            $('#state').val(data.state);
            $('#state_code').val(data.state_code);
        })
    });

    $('body').on('click', '#deletePartyBtn', function (e) {
        // e.preventDefault();
        var delete_consignee_id = $(this).attr('data-id');
        $("#DeletePartyModal").find('#RemovePartySubmit').attr('data-id',delete_consignee_id);
    });

    $('body').on('click', '#RemovePartySubmit', function (e) {
        $('#RemovePartySubmit').prop('disabled',true);
        $(this).find('.removeloadericonfa').show();
        e.preventDefault();
        var remove_consignee_id = $(this).attr('data-id');

        $.ajax({
            type: 'GET',
            url: "{{ url('admin/consignee') }}" +'/' + remove_consignee_id +'/delete',
            success: function (res) {
                if(res.status == 200){
                    $("#DeletePartyModal").modal('hide');
                    $('#RemovePartySubmit').prop('disabled',false);
                    $("#RemovePartySubmit").find('.removeloadericonfa').hide();
                    set_table_view();
                    toastr.success("Party has been Deleted Successfully",'Success',{timeOut: 5000});
                }

                if(res.status == 400){
                    $("#DeletePartyModal").modal('hide');
                    $('#RemovePartySubmit').prop('disabled',false);
                    $("#RemovePartySubmit").find('.removeloadericonfa').hide();
                    set_table_view();
                    toastr.error("Please try again",'Error',{timeOut: 5000});
                }
            },
            error: function (data) {
                $("#DeletePartyModal").modal('hide');
                $('#RemovePartySubmit').prop('disabled',false);
                $("#RemovePartySubmit").find('.removeloadericonfa').hide();
                set_table_view();
                toastr.error("Please try again",'Error',{timeOut: 5000});
            }
        });
    });
</script>
<!-- party list JS end -->
@endsection

