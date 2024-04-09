@extends('admin.layout')

@section('content')
    <?php 
    $current_month = date('m');
    $firstThreeMonthsArr = ['01', '02', '03'];
    if(in_array($current_month, $firstThreeMonthsArr)){
        $fromYear = date('Y') - 1;
        $toYear = date('Y');
    } else {
        $fromYear = date('Y'); // Get the current year
        $toYear = date('Y') + 1;
    }
    ?>
    <div class="row page-titles mx-0">
        <div class="col p-md-0">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="javascript:void(0)">Dashboard</a></li>
                <li class="breadcrumb-item active"><a href="javascript:void(0)">Home</a></li>
            </ol>
        </div>
    </div>
    <!-- row -->

    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-3 col-sm-6">
                <div class="card gradient-1">
                    <div class="card-body">
                        <h3 class="card-title text-white">Invoice</h3>
                        <div class="d-inline-block">
                            <h2 class="text-white">{{ $monthlyInvoice }}</h2>
                            <p class="text-white mb-0">{{ date("M, Y") }}</p>
                        </div>
                        <span class="float-right display-5 opacity-5"><i class="fa fa-file-text-o" aria-hidden="true"></i></span>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-sm-6">
                <div class="card gradient-2">
                    <div class="card-body">
                        <h3 class="card-title text-white">Sales</h3>
                        <div class="d-inline-block">
                            <h2 class="text-white">{{ IND_money_format($monthlySales) }}</h2>
                            <p class="text-white mb-0">{{ date("M, Y") }}</p>
                        </div>
                        <span class="float-right display-5 opacity-5"><i class="fa fa-inr"></i></span>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-sm-6">
                <div class="card gradient-3">
                    <div class="card-body">
                        <h3 class="card-title text-white">Invoice</h3>
                        <div class="d-inline-block">
                            <h2 class="text-white">{{ $yearlyInvoice }}</h2>
                            <p class="text-white mb-0">Apr, {{ $fromYear }} - Mar, {{ $toYear }}</p>
                        </div>
                        <span class="float-right display-5 opacity-5"><i class="fa fa-file-text-o" aria-hidden="true"></i></span>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-sm-6">
                <div class="card gradient-4">
                    <div class="card-body">
                        <h3 class="card-title text-white">Sales</h3>
                        <div class="d-inline-block">
                            <h2 class="text-white">{{ IND_money_format($yearlySales) }}</h2>
                            <p class="text-white mb-0">Apr, {{ $fromYear }} - Mar, {{ $toYear }}</p>
                        </div>
                        <span class="float-right display-5 opacity-5"><i class="fa fa-inr"></i></span>
                    </div>
                </div>
            </div>
        </div>

        <canvas id="invoice_line_chart" height="100px"></canvas>
    </div>
@endsection

@section('js')
<script type="text/javascript">
    var cData = JSON.parse('<?php echo $final_chart_data; ?>');
    // console.log("cData:",cData);

    const data = {
        labels: cData.label,
        datasets: [{
            label: 'Invoice',
            backgroundColor: 'rgb(255, 99, 132)',
            borderColor: 'rgb(255, 99, 132)',
            data: cData.data,
        }]
    };

    const config = {
        type: 'line',
        data: data,
        options: {}
    };

    const myChart = new Chart(
        document.getElementById('invoice_line_chart'),
        config
    );
</script>
@endsection
