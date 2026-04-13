<?php

/**
 * Log Successful Login Listener
 * -----------------------------------------
 * Event listener that logs successful user authentication attempts, capturing login details
 * including IP address, user agent, and timestamp for security auditing.
 *
 * @package App\Listeners
 * @author Achal Sharma
 * @version 1.0.0
 * @since 2026-04-09
 */

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
use Throwable;

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
            Log::error('Database error writing login', [
                'user_id' => $user->id ?? null,
                'exception_type' => get_class($e),
                'exception_code' => $e->getCode()
            ]);
        } catch (Throwable $e) {
            Log::error('Unexpected error writing login', [
                'user_id' => $user->id ?? null,
                'exception_type' => get_class($e),
                'exception_code' => $e->getCode()
            ]);
        }
    }
}
