@php
    $is_required = $required ?? true;
@endphp
<div class="form-item @if($is_required) required @endif {{ $classes ?? '' }}">
    <div class="form-field">
        <label
            for="{{ $field }}"
            @if($is_required) aria-required="true" @endif
        >
            {{ $field_title }}:
        </label>

        {{ $slot }}
    </div>

    @error($field)
    <div class="error" role="alert">
        {!! $message !!}
    </div>
    @enderror
</div>
