<?php


namespace App\Services;

use App\Models\Payment;
use App\Models\User;
use Illuminate\Support\Carbon;

class PaymentService {

    public function getUser(){
        $user = User::get();
        return $user;
    }


    public function current_date(){
        $date = now()->format('Y-m-d');
        return $date;
    }

    public function getPayments($user = null)
    {
        $payments = Payment::select('payments.*', 'customers.id', 'plans.price')
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
        return $now->eq($due_date);
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

                     Payment::where('customer_id', $payment->customer_id)
                                    ->update([
                                        'balance'=>$new_balance,
                                        'start_date'=>$current_date,
                                        'due_date'=>$next_due_date
                                    ]);

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
        return ($plan_price+$balance);
    }


}
