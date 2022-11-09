<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CronReversController extends Controller
{

    public function __construct()
    {
        date_default_timezone_set("Asia/Karachi");
    }

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
         $payments = Payment::select('payments.*', 'customers.id', 'plans.price', 'customers.discount')
                         ->join('customers', 'payments.customer_id', '=', 'customers.id')
                         ->join('plans', 'customers.plan_id', '=', 'plans.id')
                         ->where('payments.user_id', $user->id)
                         ->where('payments.status', 0)
                         ->get();
        return $payments;
    }

    public function getNextPayments($user = null)
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

    public function isDate($payment, $month=1, $days=0)
    {
        $due_date = Carbon::parse($payment->due_date);
        $now = now()->addMonth($month);

        if($days > 0){
               $now->addDays($days);
        }

        Log::alert("{$now->format('Y-m-d')} == {$due_date->format('Y-m-d')}");
        return $now->format('Y-m-d') >= $due_date->format('Y-m-d') && $now->format('Y-m-d') <= $due_date->format('Y-m-d');
    }

    public function customBalance($month, $day=0){
        date_default_timezone_set("Asia/Karachi");
        $users = $this->getUser();
        foreach($users as $user){
            $payments = $this->getNextPayments($user);
            foreach($payments as $payment){
                if($this->isDate($payment, $month, $day)){
                    $next_due_date = $this->prevMonth($payment, 1);
                    $current_date = $this->dateInFormateWithPreve($payment->due_date, 2);
                    $new_balance = $this->removeBalance($payment);
                    $plan_price = $this->getPlanPrice($payment);
                    $current_ballance = $new_balance-$plan_price;
                    $preve_1x_ballance = $current_ballance-$plan_price;
                    // Log::info("New Ballance >".$new_balance);

                    Log::info("New Ballance >> {$new_balance} curent_date >> {$current_date} >> next_ballance_date {$next_due_date} >> {$plan_price} >> Current Ballance >> {$current_ballance} >> prev Ballance >> $preve_1x_ballance");

                    Payment::where('customer_id', $payment->customer_id)
                            ->update([
                                'balance'=>$preve_1x_ballance,
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

    public function addBalance()
    {
        $this->customBalance(1, 25);
        $this->customBalance(1, 23);
        $this->customBalance(1, 22);
        $this->customBalance(1, 19);
        $this->customBalance(1, 17);
        $this->customBalance(1, 15);
        $this->customBalance(1, 13);
        $this->customBalance(1, 14);
        $this->customBalance(1, 6);
        $this->customBalance(1, 5);
        $this->customBalance(1, 4);
        $this->customBalance(1, 3);
    }

    public function addBalance_final()
    {
        date_default_timezone_set("Asia/Karachi");
        $users = $this->getUser();
        foreach($users as $user){
            $payments = $this->getNextPayments($user);
            foreach($payments as $payment){
                if($this->isDate($payment, 5, 23)){
                    $next_due_date = $this->prevMonth($payment, 1);
                    $current_date = $this->dateInFormateWithPreve($payment->due_date, 2);
                    $new_balance = $this->removeBalance($payment);
                    $plan_price = $this->getPlanPrice($payment);
                    $current_ballance = $new_balance-$plan_price;
                    $preve_1x_ballance = $current_ballance-$plan_price;
                    // Log::info("New Ballance >".$new_balance);

                    Log::info("New Ballance >> {$new_balance} curent_date >> {$current_date} >> next_ballance_date {$next_due_date} >> {$plan_price} >> Current Ballance >> {$current_ballance} >> prev Ballance >> $preve_1x_ballance");

                    Payment::where('customer_id', $payment->customer_id)
                            ->update([
                                'balance'=>$preve_1x_ballance,
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

        public function dateInFormateWithPreve($date, $sub_month=1)
        {
            return Carbon::parse($date)->subMonth($sub_month)->format('Y-m-d');
        }

        public function nextMonth($payment, $month=1){
            $due_date = Carbon::parse($payment->due_date);
            return $due_date->addMonth($month)->format('Y-m-d');
        }

        public function prevMonth($payment, $month=1){
            $due_date = Carbon::parse($payment->due_date);
            return $due_date->subMonth($month)->format('Y-m-d');
        }

        public function getBalance($payment){
            $plan_price = (int)$payment->price;
            $balance = (int)$payment->balance;
            $discount = (int)$payment->discount;
            return (($plan_price-$discount)+$balance);
        }

        public function removeBalance($payment){
            $plan_price = (int)$payment->price;
            $balance = (int)$payment->balance;
            $discount = (int)$payment->discount;
            return (($plan_price-$discount)+$balance);
        }

        public function getPlanPrice($payment){
            $plan_price = (int)$payment->price;
            $discount = (int)$payment->discount;
            return (($plan_price-$discount));
        }

}
