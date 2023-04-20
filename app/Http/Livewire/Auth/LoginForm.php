<?php

namespace App\Http\Livewire\Auth;

use App\Actions\Auth\SendLoginLink;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class LoginForm extends Component
{
    public string $email = '';

    protected $rules = [
        'email' => 'required|email|string|exists:users',
    ];

    public function submit(SendLoginLink $action): void
    {
        $this->validate();

        $action->handle($this->email);
    }

    public function render(): View
    {
        return view('livewire.auth.login-form');
    }
}
