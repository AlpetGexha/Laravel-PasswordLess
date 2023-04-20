<form class="space-y-6" wire:submit.prevent="submit">
    @csrf

    <x-status class="mb-4" />

    <div>
        <x-form.label for="name">
            Name
        </x-form.label>
        <div class="mt-2">
            <x-form.input
                wire:model.defer="name"
                id="name"
                name="name"
                type="text"
                required
                autofocus
            />
            <x-form.error :messages="$errors->get('name')" />
        </div>
    </div>

    <div>
        <x-form.label for="email">
            Email Address
        </x-form.label>
        <div class="mt-2">
            <x-form.input
                id="email"
                name="email"
                type="email"
                wire:model.defer="email"
                required
            />
            <x-form.error :messages="$errors->get('email')" />
        </div>
    </div>

    <div>
        <x-form.submit type="submit">
            Register
            <div wire:loading>
                <x-spinning  />
            </div>
        </x-form.submit>
    </div>
</form>
