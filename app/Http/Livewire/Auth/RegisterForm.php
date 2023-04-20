<?php

namespace App\Http\Livewire\Auth;

use App\Actions\Auth\CreateNewUser;
use App\Actions\Auth\SendLoginLink;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class RegisterForm extends Component
{
    public string $name = '';

    public string $email = '';

    public string $status = '';

    protected $rules = [
        'name' => 'required|string|min:2|max:55',
        'email' => 'required|email|string|unique:users',
    ];

    public function submit(CreateNewUser $user, SendLoginLink $action): void
    {
        $this->validate();

        $user = $user->handle(
            $this->name,
            $this->email,
        );

        if (! $user) {
            throw ValidationException::withMessages(
                [
                    'email' => 'Something went wrong, please try again later.',
                ],
            );
        }

        $action->handle($this->email);

        session()->flash('success', 'An email has been sent for you to log in.');

        $this->reset(['email', 'name']);
    }

    public function render(): View
    {
        return view('livewire.auth.register-form');
    }
}
