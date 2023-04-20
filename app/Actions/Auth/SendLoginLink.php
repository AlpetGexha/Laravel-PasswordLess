<?php

namespace App\Actions\Auth;

use App\Mail\Auth\LoginLink;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;

class SendLoginLink
{
    public function handle(string $email)
    {
        $this->handleWithRateLimit($email);
    }

    /**
 * Send a login URL to the user.
 *
 * @param  string  $email
 * @return void
 */

 private function sendURL(string $email): void
    {
        $loginLink = URL::signedRoute(
            'login:store',
            ['email' => $email],
            900 // link expiration after 15minute (900)
        );
        Mail::to($email)->send(new LoginLink($loginLink));
    }

    /**
     * The rate limit ensures that the email is only sent once every 2 minutes to prevent spamming.
     *
     * @var string Email
     *
     */

    private function handleWithRateLimit(string $email): void
    {
        $key = 'send-to' . $email;
        $decayRate = 120; // 2 minute
        $maxAttempts = 1;

        $executed = RateLimiter::attempt(
            $key,
            $maxAttempts,
            function () use ($email) {
                $this->sendURL($email);
            },
            $decayRate,
        );

        if (!$executed) {
            $this->handleWithRateLimitError($key);
        } else {
            $this->handleWithRateLimitSuccess($email);
        }
    }

    private function handleWithRateLimitError(string $key): void
    {
        $seconds = RateLimiter::availableIn($key);
 
        session()->flash('error', "Plz try again after {$seconds} seconds");
    }

    private function handleWithRateLimitSuccess(string $email): void
    {
        session()->flash('success', "Login link sent to {$email}");
    }
}
