@extends('admin.layout')

@section('content')
    <div class="row page-titles mx-0">
        <div class="col p-md-0">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('admin/dashboard') }}">Home</a></li>
                <li class="breadcrumb-item active"><a href="javascript:void(0)">Transporter List</a></li>
            </ol>
        </div>
    </div>
    <!-- row -->

    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">Transporter List</h4>
                        <div class="action-section">
                            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#TransporterModal" id="addTransporterBtn"><i class="fa fa-plus" aria-hidden="true"></i></button>
                        </div>
                        <table id="transporter_table" class="table zero-configuration customNewtable" style="width:100%">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Transporter</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tfoot>
                                <tr>
                                    <th>No</th>
                                    <th>Transporter</th>
                                    <th>Action</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="TransporterModal">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <form class="form-valide" action="" id="transporterform" method="post" enctype="multipart/form-data">
                    <div class="modal-header">
                        <h5 class="modal-title" id="formtitle">Add Transporter</h5>
                        <button type="button" class="close" data-dismiss="modal"><span>×</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div id="attr-cover-spin" class="cover-spin"></div>
                        {{ csrf_field() }}
                        <div class="form-group ">
                            <label class="col-form-label" for="transporter_name">Transporter Name <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control input-flat" id="transporter_name" name="transporter_name" placeholder="">
                            <div id="transporter_name-error" class="invalid-feedback animated fadeInDown" style="display: none;"></div>
                        </div>
                        <div class="form-group ">
                            <label class="col-form-label" for="mobile_no">Mobile No</label>
                            <input type="text" class="form-control input-flat" id="mobile_no" name="mobile_no" placeholder="">
                            <div id="mobile_no-error" class="invalid-feedback animated fadeInDown" style="display: none;"></div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <input type="hidden" name="transporter_id" id="transporter_id" value="">
                        <button type="button" class="btn btn-outline-primary" id="save_newTransporterBtn">Save & New <i class="fa fa-circle-o-notch fa-spin loadericonfa" style="display:none;"></i></button>
                        <button type="button" class="btn btn-primary" id="save_closeTransporterBtn">Save & Close <i class="fa fa-circle-o-notch fa-spin loadericonfa" style="display:none;"></i></button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="DeleteTransporterModal">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Remove Transporter</h5>
                </div>
                <div class="modal-body">
                    Are you sure you wish to remove this Transporter?
                </div>
                <div class="modal-footer">
                    <button class="btn btn-default" data-dismiss="modal" type="button">Cancel</button>
                    <button class="btn btn-danger" id="RemoveTransporterSubmit" type="submit">Remove <i class="fa fa-circle-o-notch fa-spin removeloadericonfa" style="display:none;"></i></button>
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

    function save_transporter(btn,btn_type){
        $(btn).prop('disabled',true);
        $(btn).find('.loadericonfa').show();
        var regexGst = /^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$/;
        var action = $(btn).attr('data-action');
        var formData = new FormData($("#transporterform")[0]);
        formData.append('action', action);

        $.ajax({
            type: 'POST',
            url: "{{ url('admin/addorupdatetransporter') }}",
            data: formData,
            processData: false,
            contentType: false,
            success: function (res) {
                if(res.status == 'failed'){
                    $(btn).prop('disabled',false);
                    $(btn).find('.loadericonfa').hide();

                    if (res.errors.transporter_name) {
                        $('#transporter_name-error').show().text(res.errors.transporter_name);
                    } else {
                        $('#transporter_name-error').hide();
                    }

                    if (res.errors.mobile_no) {
                        $('#mobile_no-error').show().text(res.errors.mobile_no);
                    } else {
                        $('#mobile_no-error').hide();
                    }
                }

                if(res.status == 200){
                    if(btn_type == 'save_close'){
                        $("#TransporterModal").modal('hide');
                        $(btn).prop('disabled',false);
                        $(btn).find('.loadericonfa').hide();
                        if(res.action == 'add'){
                            set_table_view(true);
                            toastr.success("Transporter has been Added Successfully",'Success',{timeOut: 5000});
                        }
                        if(res.action == 'update'){
                            set_table_view();
                            toastr.success("Transporter has been Updated Successfully",'Success',{timeOut: 5000});
                        }
                    }

                    if(btn_type == 'save_new'){
                        $(btn).prop('disabled',false);
                        $(btn).find('.loadericonfa').hide();
                        $("#TransporterModal").find('form').trigger('reset');
                        $("#TransporterModal").find("#save_newTransporterBtn").removeAttr('data-action');
                        $("#TransporterModal").find("#save_closeTransporterBtn").removeAttr('data-action');
                        $("#TransporterModal").find("#save_newTransporterBtn").removeAttr('data-id');
                        $("#TransporterModal").find("#save_closeTransporterBtn").removeAttr('data-id');
                        $('#transporter_id').val("");
                        $('#transporter_name-error').html("");
                        $('#mobile_no-error').html("");
                        $("#transporter_name").focus();
                        if(res.action == 'add'){
                            set_table_view(true);
                            toastr.success("Transporter has been Added Successfully",'Success',{timeOut: 5000});
                        }
                        if(res.action == 'update'){
                            set_table_view();
                            toastr.success("Transporter has been Updated Successfully",'Success',{timeOut: 5000});
                        }
                    }
                }

                if(res.status == 400){
                    // $("#TransporterModal").modal('hide');
                    $(btn).prop('disabled',false);
                    $(btn).find('.loadericonfa').hide();
                    set_table_view();
                    toastr.error("Please try again",'Error',{timeOut: 5000});
                }
            },
            error: function (data) {
                // $("#TransporterModal").modal('hide');
                $(btn).prop('disabled',false);
                $(btn).find('.loadericonfa').hide();
                set_table_view();
                toastr.error("Please try again",'Error',{timeOut: 5000});
            }
        });
    }

    $('body').on('click', '#save_newTransporterBtn', function () {
        save_transporter($(this),'save_new');
    });

    $('body').on('click', '#save_closeTransporterBtn', function () {
        save_transporter($(this),'save_close');
    });

    $('#TransporterModal').on('shown.bs.modal', function (e) {
        $("#transporter_name").focus();
    });

    $('#TransporterModal').on('hidden.bs.modal', function () {
        $(this).find('form').trigger('reset');
        $(this).find("#save_newTransporterBtn").removeAttr('data-action');
        $(this).find("#save_closeTransporterBtn").removeAttr('data-action');
        $(this).find("#save_newTransporterBtn").removeAttr('data-id');
        $(this).find("#save_closeTransporterBtn").removeAttr('data-id');
        $('#transporter_id').val("");
        $('#transporter_name-error').html("");
        $('#mobile_no-error').html("");
    });

    $('#DeleteTransporterModal').on('hidden.bs.modal', function () {
        $(this).find("#RemoveTransporterSubmit").removeAttr('data-id');
    });

    function set_table_view(is_clearState=false){

        if(is_clearState){
            $('#transporter_table').DataTable().state.clear();
        }

        $('#transporter_table').DataTable({
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
                "url": "{{ url('admin/alltransporterlist') }}",
                "dataType": "json",
                "type": "POST",
                "data":{ _token: '{{ csrf_token() }}'},
                // "dataSrc": ""
            },
            "order": [[ 1, "ASC" ]],
            'columnDefs': [
                { "width": "10%", "targets": 0 },
                { "width": "80%", "targets": 1 },
                { "width": "10%", "targets": 2 }
            ],
            "columns": [
                {data: 'id', name: 'id', class: "text-center", orderable: false,
                    render: function (data, type, row, meta) {
                        return meta.row + meta.settings._iDisplayStart + 1;
                    }
                },
                {data: 'transporter', name: 'transporter', class: "text-left multirow", orderable: false},
                {data: 'action', name: 'action', orderable: false, searchable: false, class: "text-center"},
            ]
        });
    }

    $('body').on('click', '#addTransporterBtn', function (e) {
        $("#TransporterModal").find('.modal-title').html("Add Party");
    });

    $('body').on('click', '#editTransporterBtn', function () {
        var transporter_id = $(this).attr('data-id');
        $.get("{{ url('admin/transporter') }}" +'/' + transporter_id +'/edit', function (data) {
            $('#TransporterModal').find('.modal-title').html("Edit Transporter");
            $('#TransporterModal').find('#save_closeTransporterBtn').attr("data-action", "update");
            $('#TransporterModal').find('#save_newTransporterBtn').attr("data-action", "update");
            $('#TransporterModal').find('#save_closeTransporterBtn').attr("data-id", transporter_id);
            $('#TransporterModal').find('#save_newTransporterBtn').attr("data-id", transporter_id);
            $('#transporter_id').val(data.id);
            $('#transporter_name').val(data.partyname);
            $('#mobile_no').val(data.mobile_no);
        })
    });

    $('body').on('click', '#deleteTransporterBtn', function (e) {
        // e.preventDefault();
        var delete_transporter_id = $(this).attr('data-id');
        $("#DeleteTransporterModal").find('#RemoveTransporterSubmit').attr('data-id',delete_transporter_id);
    });

    $('body').on('click', '#RemoveTransporterSubmit', function (e) {
        $('#RemoveTransporterSubmit').prop('disabled',true);
        $(this).find('.removeloadericonfa').show();
        e.preventDefault();
        var remove_transporter_id = $(this).attr('data-id');

        $.ajax({
            type: 'GET',
            url: "{{ url('admin/transporter') }}" +'/' + remove_transporter_id +'/delete',
            success: function (res) {
                if(res.status == 200){
                    $("#DeleteTransporterModal").modal('hide');
                    $('#RemoveTransporterSubmit').prop('disabled',false);
                    $("#RemoveTransporterSubmit").find('.removeloadericonfa').hide();
                    set_table_view();
                    toastr.success("Transporter has been Deleted Successfully",'Success',{timeOut: 5000});
                }

                if(res.status == 400){
                    $("#DeleteTransporterModal").modal('hide');
                    $('#RemoveTransporterSubmit').prop('disabled',false);
                    $("#RemoveTransporterSubmit").find('.removeloadericonfa').hide();
                    set_table_view();
                    toastr.error("Please try again",'Error',{timeOut: 5000});
                }
            },
            error: function (data) {
                $("#DeleteTransporterModal").modal('hide');
                $('#RemoveTransporterSubmit').prop('disabled',false);
                $("#RemoveTransporterSubmit").find('.removeloadericonfa').hide();
                set_table_view();
                toastr.error("Please try again",'Error',{timeOut: 5000});
            }
        });
    });
</script>
<!-- party list JS end -->
@endsection

