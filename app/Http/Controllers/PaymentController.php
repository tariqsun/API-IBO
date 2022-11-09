<?php

namespace App\Http\Controllers;

use App\Models\Customers;
use App\Models\Payment;
use App\Models\PaymentHistory;
use App\Models\Plan;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{

    public function index(){
        $auth = Auth::user();
        date_default_timezone_set("Asia/Karachi");
        $now_date = now()->format('Y-m-d');

        $payments = Payment::select('payments.balance', 'payments.id', 'payments.start_date', 'payments.due_date', 'customers.phone_number', 'customers.name as customer_name', DB::raw('DATEDIFF(payments.due_date, payments.start_date) as duration'), DB::raw("DATEDIFF(payments.due_date, '$now_date') as days_left"))
            ->join('customers', 'payments.customer_id', '=', 'customers.id')
            ->where('customers.status', 1)
            ->where('payments.status', 0)
            ->where('payments.user_id', $auth->id)
            ->orderBy('payments.due_date', 'asc')
            ->get();

        return $payments;
    }

    public function getDonePayment(){
         $auth = Auth::user();
        date_default_timezone_set("Asia/Karachi");
        $now_date = now()->format('Y-m-d');
        $payments = Payment::select('payments.balance', 'payments.id', 'payments.start_date', 'payments.due_date', 'customers.name as customer_name', DB::raw('DATEDIFF(payments.due_date, payments.start_date) as duration'), 'payments.due_date')
            ->join('customers', 'payments.customer_id', '=', 'customers.id')
            ->where('customers.status', 1)
            ->where('payments.status', 1)
            ->where('payments.user_id', $auth->id)
            ->orderBy('payments.due_date', 'asc')
            ->get();

        foreach($payments as $key => $payment){
            $date2 = Carbon::parse($payment->due_date)->addMonth();
            $date =  Carbon::parse($payment->due_date);
            $days = $date->diffInDays($date2);

            $payments[$key]->days_left = $days;
        }

        return $payments;
    }

    public function payment_recived(Request $request){
        $auth = Auth::user();
        date_default_timezone_set("Asia/Karachi");
        $payment = Payment::where('id', $request->payment_id)->first();
        $customer = Customers::where('id', $payment->customer_id)->first();
        $plan = Plan::where('id', $customer->plan_id)->first();
        $date =Carbon::parse($payment->due_date);

        $payment_update = Payment::where('id', $payment->id)->update([
            'balance'=>$plan->price,
            'start_date'=>$date->format('Y-m-d'),
            'due_date'=>$date->addMonth()->format('Y-m-d'),
        ]);

        if($payment_update){

            $payment_history = PaymentHistory::create([
                'payment'=>$plan->price,
                'payment_id'=>$payment->id,
                'user_id'=>$customer->user_id
            ]);

            return $payment_history;
        }else{
            return false;
        }
    }

    public function get_recovery_payment(){
        $auth = Auth::user();
        $payment = 0;
        date_default_timezone_set("Asia/Karachi");
        $payments = Payment::where('status', 0)
                            ->where('user_id', $auth->id)
                            ->get();

        foreach($payments as $row){
            $payment += (int)$row->balance;
        }

        return $payment;

    }

    public function totalPayment(){
        $auth = Auth::user();
        $payment = 0;
        date_default_timezone_set("Asia/Karachi");
        $payments = Payment::where('status', 1)
                            ->where('user_id', $auth->id)
                            ->get();

        foreach($payments as $row){
            $payment += (int)$row->balance;
        }

        return $payment;

    }

    public function getMonthPendingIncome(){
        $auth = Auth::user();
        $payment = 0;
        date_default_timezone_set("Asia/Karachi");
        $payments = Payment::where('status', 0)
                            ->where('user_id', $auth->id)
                            ->whereBetween('due_date', [
                                now()->startOfMonth()->format('Y-m-d'),
                                now()->endOfMonth()->format('Y-m-d')
                            ])
                            ->get();

        foreach($payments as $row){
            $payment += (int)$row->balance;
        }

        return $payment;

    }

    public function pending_payment(){
        $auth = Auth::user();
        $payment = 0;
        date_default_timezone_set("Asia/Karachi");
        $payments = Payment::where('status', 0)
                            ->where('user_id', $auth->id)
                            ->get();

        foreach($payments as $row){
            $payment += (int)$row->balance;
        }

        return $payment;

    }

    public function addBalance()
    {
        $paymentService = new PaymentService();
        $paymentService->addBalance();
    }

    public function payment(Request $request)
    {
        try {
           $payment = (int)$request->payment;
           $customers = Customers::where('id', $request->customer_id)->first();
           $getPayment = Payment::where('customer_id', $request->customer_id)->where('status', false)->first();
           if($getPayment){


                $customer_due = (int)$getPayment->balance;
                $remain_balance = $customer_due-$payment;

                $date = Carbon::parse($getPayment->due_date);
                $date2 = Carbon::parse($getPayment->start_date);

                    Payment::where('id', $getPayment->id)->update([
                        'status'=>1,
                        'balance'=>$payment,
                    ]);

                    Payment::create([
                        'customer_id'=>$customers->id,
                        'balance'=>$remain_balance,
                        'status'=>0,
                        'start_date'=>$date2->format('Y-m-d'),
                        'due_date'=>$date->format('Y-m-d'),
                        'user_id'=>auth()->user()->id
                    ]);
           }else{
              return ['errors'=>true, 'message'=>"There is no payment to this customer"];
           }


        } catch (\Exception $th) {
            return ['errors'=>true,'message'=>$th->getMessage()];
        }

    }
}
