<?php

namespace App\Listeners;

// Required class imports for event handling and logging
use Illuminate\Auth\Events\Login;
use Illuminate\Http\Request;
use App\Models\LoginLog;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Exception;
use Illuminate\Database\QueryException;

class LogSuccessfulLogin
{
    /**
     * The request instance.
     *
     * @var \Illuminate\Http\Request
     */
    public $request;

    /**
     * Create the event listener.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return void
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * Handle the login event.
     *
     * @param  \Illuminate\Auth\Events\Login  $event
     * @return void
     */
    public function handle(Login $event)
    {
        try {
            // Get the authenticated user from the login event
            $user = $event->user;

            // Create login log entry using the same logic as in AuthController
            LoginLog::create([
                'user_id' => $user->id,
                'login_time' => \Carbon\Carbon::now(),
                'login_data' => [
                    'ip_address' => $this->request->ip(),
                    'user_agent' => $this->request->header('User-Agent'),
                ],
            ]);
        } catch (QueryException $e) {
            Log::error('Database error logging successful login', [
                'user_id' => $user->id ?? null,
                'exception' => $e->getMessage()
            ]);
        } catch (Exception $e) {
            Log::error('Unexpected error logging successful login', [
                'user_id' => $user->id ?? null,
                'exception' => $e->getMessage()
            ]);
        }
    }
}
