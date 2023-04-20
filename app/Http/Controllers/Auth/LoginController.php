<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class LoginController
{
    public function __invoke(Request $request, string $email): RedirectResponse
    {
        if (!$request->hasValidSignature() || $this->isValidTimestamp($request)) {
            abort(Response::HTTP_UNAUTHORIZED);
        }

        $user = User::query()
            ->where('email', $email)
            ->firstOrFail();

        Auth::login($user);

        return new RedirectResponse(
            url: route('dashboard:show'),
        );
    }

    private function isValidTimestamp(Request $request)
    {
        return now()->timestamp > $request->input('timestamp') + config('passwordless.expired_time');
    }
}
