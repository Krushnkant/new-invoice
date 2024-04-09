<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(){

        $current_month = date('m');
        $firstThreeMonthsArr = ['01', '02', '03'];
        if(in_array($current_month, $firstThreeMonthsArr)){
            $fromYear = date('Y') - 1;
            $toYear = date('Y');
        } else {
            $fromYear = date('Y'); // Get the current year
            $toYear = date('Y') + 1;
        }
        $fromDate = $fromYear.'-04-01';
        $toDate = $toYear.'-03-31';

        $monthlyInvoice = Invoice::whereYear("created_at", Carbon::now()->year)->whereMonth('created_at', Carbon::now()->month)->count();
        $yearlyInvoice = Invoice::whereBetween('created_at', [$fromDate, $toDate])->count();
        $monthlySales = Invoice::whereYear("created_at", Carbon::now()->year)->whereMonth('created_at', Carbon::now()->month)->sum('final_amount');
        $yearlySales = Invoice::whereBetween('created_at', [$fromDate, $toDate])->sum('final_amount');

        $record = Invoice::select(DB::raw("COUNT(*) as count"), DB::raw("final_amount as amount"), \DB::raw("DATE(created_at) as date"))
            ->where('created_at', '>', Carbon::today()->subDay(6))
            ->groupBy('amount','date')
            ->orderBy('date')
            ->get();
        $chart_data = [];
        foreach($record as $row) {
            $chart_data['label'][] = $row->date;
            $chart_data['data'][] = $row->amount;
        }
        $final_chart_data = json_encode($chart_data);
//        $labels = $chart_data['label'];
//        $data = $chart_data['data'];
//        dd($chart_data,$labels,$data);

        return view('admin.dashboard',compact('yearlyInvoice','monthlyInvoice','monthlySales','yearlySales','final_chart_data'));
    }
}
