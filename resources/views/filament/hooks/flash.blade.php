@foreach (['success' => 'lucide-circle-check', 'error' => 'lucide-triangle-alert'] as $type => $icone)
    @if (session($type))
        <div class="cd-flash cd-flash-{{ $type }}" role="alert">
            <x-filament::icon :icon="$icone" class="h-5 w-5" />
            <span>{{ session($type) }}</span>
        </div>
    @endif
@endforeach
@if ($errors->any())
    <div class="cd-flash cd-flash-error" role="alert">
        <x-filament::icon icon="lucide-triangle-alert" class="h-5 w-5" />
        <span>{{ $errors->first() }}</span>
    </div>
@endif
