<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CronController extends Controller
{

    public function __construct()
    {
        date_default_timezone_set("Asia/Karachi");
    }

    public function current_date(){
        $date = now()->format('Y-m-d');
        return $date;
    }

    public function getPayments($user = null)
    {
         $payments = Payment::select('payments.*', 'customers.id', 'plans.price', 'customers.discount')
                         ->join('customers', 'payments.customer_id', '=', 'customers.id')
                         ->join('plans', 'customers.plan_id', '=', 'plans.id')
                         ->where('payments.user_id', $user->id)
                         ->where('payments.status', 0)
                         ->get();
        return $payments;
    }

    public function isCurentDate($payment)
    {
        $due_date = Carbon::parse($payment->due_date);
        $now = now();
        return $now->format('Y-m-d') == $due_date->format('Y-m-d');
    }


    public function addBalance()
    {
        date_default_timezone_set("Asia/Karachi");
        $users = $this->getUser();
        foreach($users as $user){
            $payments = $this->getPayments($user);
            foreach($payments as $payment){
                if($this->isCurentDate($payment)){
                    $next_due_date = $this->nextMonth($payment);
                    $current_date = $this->dateInFormate($payment->due_date);
                    $new_balance = $this->getBalance($payment);
                    // Log::info("New Ballance >".$new_balance);

                    Payment::where('customer_id', $payment->customer_id)
                            ->update([
                                'balance'=>$new_balance,
                                'start_date'=>$current_date,
                                'due_date'=>$next_due_date
                        ]);
                        $payment = Payment::where('customer_id', $payment->customer_id)->first();
                        Log::info("New Ballance >".$payment->balance);
                 }else{
                    Log::alert("No curedate".$payment->customer_id);
                 }

                }
            }
        }





        public function dateInFormate($date)
        {
            return Carbon::parse($date)->format('Y-m-d');
        }

        public function nextMonth($payment){
            $due_date = Carbon::parse($payment->due_date);
            return $due_date->addMonth()->format('Y-m-d');
        }

        public function getBalance($payment){
            $plan_price = (int)$payment->price;
            $balance = (int)$payment->balance;
            $discount = (int)$payment->discount;
            return (($plan_price-$discount)+$balance);
        }


}
