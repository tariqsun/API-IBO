<?php

namespace App\Jobs;

use App\Mail\RecoveryAlert;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class RecoveryElertJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $user;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
       try {
         $now_date = now()->format('Y-m-d');
         $user = $this->user;
         $payments = Payment::select('payments.*', 'customers.name as customer_name', DB::raw("DATEDIFF(payments.due_date, '$now_date') as days_left"))
                                ->join('customers', 'payments.customer_id', '=', 'customers.id')
                                ->where('payments.user_id', $user->id)
                                ->where('payments.balance', '>', 0)
                                ->whereRaw("DATEDIFF(payments.due_date, '$now_date') <= 5")
                                ->orderBy('days_left', 'asc')
                                ->where('payments.status', 0)->get();

            Mail::to('zohaib.dev2@gmail.com')->send(new RecoveryAlert($payments));
            Log::info("Payment email sent to ".$user->name);
       } catch (\Exception $e) {
            Log::info("Payment email sent Error ".$e->getMessage());
       }
    }
}
