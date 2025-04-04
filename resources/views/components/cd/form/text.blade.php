<x-cd.form.form-item field="{{ $field }}" classes="{{ $classes ?? '' }}" required="{{ $required ?? 'true' }}">
    <x-slot name="field_title">{{ $title }}</x-slot>
    <input type="{{$type??'text'}}" id="{{ $id ?? $field }}" name="{{ $field }}"
        {{-- type: can be text, number, date, datetime, datetime-local, month, week, time, range, color --}}
        {{-- value="{{ $fieldValue }}" --}}
        {{-- size="{{ $size ?? '12' }}" --}}
        class="{{$classes??""}}"
        autocomplete="{{ $autocomplete ?? 'off' }}"
        wire:model="{{ $field }}"
        @if ($disabled??false)
            disabled
        @endif
        {{$attributes->only('maxlength','min','max','value','step','accept')}}

    />
</x-cd.form.form-item>
