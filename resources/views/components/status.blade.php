@props(['status' => null])

@if ($status)
    <div {{ $attributes->merge(['class' => 'font-medium text-sm text-green-600 dark:text-green-400']) }}>
        {{ $status }}
    </div>
@endif


@if (session()->has('error'))
    <div {{ $attributes->merge(['class' => 'font-medium text-sm text-red-600 dark:text-red-400']) }}>
        {{ session('error') }}
    </div>
@endif

@if (session()->has('warning'))
    <div {{ $attributes->merge(['class' => 'font-medium text-sm text-yellow-600 dark:text-yellow-400']) }}>
        {{ session('warning') }}
    </div>
@endif

@if (session()->has('info'))
    <div {{ $attributes->merge(['class' => 'font-medium text-sm text-blue-600 dark:text-blue-400']) }}>
        {{ session('info') }}
    </div>
@endif

@if (session()->has('success'))
    <div {{ $attributes->merge(['class' => 'font-medium text-sm text-green-600 dark:text-green-400']) }}>
        {{ session('success') }}
    </div>
@endif
