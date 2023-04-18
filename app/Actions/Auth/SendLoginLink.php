<?php

namespace App\Actions\Auth;

use App\Mail\Auth\LoginLink;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;

class SendLoginLink
{
    public function handle(string $email): void
    {
        Mail::to($email)
            ->send(
                new LoginLink(
                    URL::signedRoute(
                        'login:store',
                        ['email' => $email],
                        3600 // expiration
                    ),
                )
            );
    }
}
