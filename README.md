# Passwordless Authentication Laravel

**Passwordless** - Passwordless refers to the concept of authenticating users without the need for a traditional password.

By eliminating the need for passwords, passwordless authentication can provide several benefits, including increased security, reduced risk of password-related attacks (such as phishing and credential stuffing), and a more user-friendly experience

![image](https://user-images.githubusercontent.com/50520333/233510580-ff958ac5-a5d1-46f5-b77f-d90d4a5f7a8d.png)


### Who to do this

We are going to use Laravel & Livewire

**Frontend**

-   Make a Register & Login
    -   Register have Email and Name filed
    -   Login have Email filed

**Backend**

-   First we need to make password on migration null or to remove

```php
Schema::create('users', function (Blueprint $table) {
    ...
    $table->string('password')->nullable();
    ...
});
```

We have 2 main Action `Create New User` and `Send Login Link`
for this we can use Controller but I like to use Action (for more easy access and clean code)

`Action\Auth\CreateNewUser.php`

```php
namespace App\Actions\Auth;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class CreateNewUser
{
    public function handle(string $name, string $email): Builder|Model
    {
        return User::query()->create([
            'name' => $name,
            'email' => $email,
        ]);
    }
}
```

`Action\Auth\SendLoginLink.php`

```php
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

    private function sendURL(string $email): void
    {
        $loginLink = URL::signedRoute(
            'login:store',
            ['email' => $email, 'timestamp' => now()->timestamp],
            config('passwordless.expired_time') // link expiration after 15minute (900)
        );
        Mail::to($email)->send(new LoginLink($loginLink));
    }

    private function handleWithRateLimit(string $email): void
    {
        $key = 'send-to' . $email;
        $decayRate = config('passwordless.rate_limit'); // 2 minute
        $maxAttempts = config('passwordless.max_attempts'); // 1

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
```

On this function we make an **URL** using **Signed Route** with user email, singled token and timestamp.

We use timestamp beacuse we want after user login that link need to expired (we see this bit later who it work) otherwise URL will expired after 15 minute

Using Ratelimited we say "_User can send only 2 request for 2 minute_" (1 To get e URL and 1 to reset if that URL dosent sent). This will eleminate _to many request_ on server

And for email

```bash
php artisan make:mail LoginLink
```

U can use queue by sending mail

```php
class LoginLink extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public readonly string|URL $url){}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your Magic Link is here!',
        );
    }

    public function content(): Content
    {
        return new Content(
            'emails.auth.login-link',[
                'url' => $this->url,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
```

```php
<x-mail::message>
# Login Link
Use the link below to log into the {{ config('app.name') }} application.

<x-mail::button :url="$url">
Login
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
```

<br />

Now we need to make a livewire component for Login and Register Logic

Lets start with Register

`RegisterForm.php`

```php
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
            throw ValidationException::withMessages([
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

```

We create a user and send the login link

`LoginFrom.php`

```php
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
```

We just check if user exist and send the email for login

`Controller`

```php
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

```

`Router`

```php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\LogoutController;
use Illuminate\Support\Facades\Route;

Route::group(['middleware' => 'guest'], static function (): void {
    Route::get('/', function () {
        return redirect()->route('login');
    });

    Route::view('login', 'app.auth.login')->name('login');

    Route::get('login/{email}', LoginController::class)->middleware('signed')->name('login:store');
    Route::view('register', 'app.auth.register')->name('register');
});

Route::group(['middleware' => 'auth'], static function (): void {
    Route::view('dashboard', 'app.dashboard.show')->name('dashboard:show');
    Route::post('logout', LogoutController::class)->name('logout');
});
```
