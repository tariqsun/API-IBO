<?php

namespace App\Console;

use App\Http\Controllers\CronController;
use App\Mail\RecoveryAlert;
use App\Models\Payment;
use App\Models\User;
use App\Services\PaymentService;
use Carbon\Carbon;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
         $schedule->call(function(){
           try {
                $now_date = now()->format('Y-m-d');
                $users = User::get();
                foreach($users as $user){
                    $payments = Payment::select('payments.*', 'customers.name as customer_name', DB::raw("DATEDIFF(payments.due_date, '$now_date') as days_left"))
                    ->join('customers', 'payments.customer_id', '=', 'customers.id')
                    ->where('payments.user_id', $user->id)
                    ->where('payments.balance', '>', 0)
                    ->whereRaw("DATEDIFF(payments.due_date, '$now_date') <= 5")
                    ->orderBy('days_left', 'asc')
                    ->where('payments.status', 0)->get();

                    if(count($payments) > 0){
                        Mail::to($user->email)
                        ->cc('aneeq@sunlink.net.pk')
                        ->cc('ehsan@sunlink.net.pk')
                        ->cc('zohaib.dev2@gmail.com')
                        ->send(new RecoveryAlert($payments));
                    }
                }
                Log::info("email sent successfully");
           } catch (\Exception $e) {
             Log::error("Pending Payment Email notification error ".$e->getMessage());
           }
        })->dailyAt('08:00');

        $schedule->call(function(){
            Log::alert("Schudle runing");
            $paymentService = new CronController();
            $paymentService->addBalance();
        })->daily();

    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
