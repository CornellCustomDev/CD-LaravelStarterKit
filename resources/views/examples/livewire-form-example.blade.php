<x-cd.layout.app title=":project_name" subtitle="Livewire Form Example">
    <h2>Form</h2>
    <x-cd.form legend="Simple form" wire:submit="submit">
        <x-cd.form.text label="Name" wire:model.live="name"/>
        <div>
            <x-cd.form.submit-button/>
            <x-cd.form.reset-button/>
        </div>

        <h3>Text</h3>
        <x-cd.form.text label="Text Input" wire:model.live="name"/>

        <x-cd.form.text label="Text Input" wire:model.live="name" placeholder="use-size-attr"
                        size="15" class="use-size-attr" :required="true" description="With placeholder"/>

        <h3>Select</h3>
        @php
            $roleoptions = [
                [ 'value'=>'', 'option'=>'Select a Role', 'disabled'=>true],
                [ 'value'=>'administrator', 'option'=>'Administrator', 'disabled'=>false],
                [ 'value'=>'editor', 'option'=>'Editor', 'disabled'=>false],
                [ 'value'=>'subscriber', 'option'=>'Subscriber', 'disabled'=>false],
            ];
        @endphp
        <x-cd.form.select :options="$roleoptions" required="1" label="Select" wire:model.live="role"/>

        <x-cd.form.select :options="$roleoptions" required="1" label="Select" wire:model.live="role" multiple>
            <x-slot name="description">With "multiple" attribute</x-slot>
        </x-cd.form.select>

        <h3>Checkbox</h3>
        <x-cd.form.checkbox label="Subscription" text="Subscribe" wire:model.live="subscribe" value="1"/>

        <x-cd.form.checkbox label="Subscription" wire:model.live="subscribe" value="1">
            Subscribe
        </x-cd.form.checkbox>

        <x-cd.form.checkbox-inline name="inline"  label="Select me" description="description" wire:model.live="subscribe" value="1"/>

        <h3>Checkboxes</h3>
        @php
            $checkboxoptions = [
                [ 'value' => "tomato", "label" => "Tomato"],
                [ 'value' => "lettuce", "label" => "Lettuce"],
                [ 'value' => "pickle", "label" => "Pickle"],
                [ 'value' => "onion", "label" => "Onion"],
            ];
        @endphp
        <x-cd.form.checkboxes :checkboxes="$checkboxoptions" wire:model.live="toppings" label="Topping Choices"/>

        <h3>Special text inputs</h3>
        <x-cd.form.text type="search" label="Search" wire:model.live="search"/>
        <x-cd.form.text type="telephone" label="Telephone" wire:model.live="telephone"/>
        <x-cd.form.text type="url" label="URL" wire:model.live="url"/>
        <x-cd.form.text type="email" label="Email" wire:model.live="email"/>
        <x-cd.form.text type="password" label="Password" wire:model.live="password"/>
        <x-cd.form.text type="number" label="Number" wire:model.live="number"/>
        <x-cd.form.text type="datetime" label="Datetime" wire:model.live="datetime"/>
        <x-cd.form.text type="datetimelocal" label="Datetime Local" wire:model.live="datetimelocal"/>
        <x-cd.form.text type="date" label="Date" wire:model.live="date"/>
        <x-cd.form.text type="month" label="Month" wire:model.live="month"/>
        <x-cd.form.text type="week" label="Week" wire:model.live="week"/>
        <x-cd.form.text type="time" label="Time" wire:model.live="time"/>

        <h3>Range</h3>
        <x-cd.form.text type="range" required="1" label="Range" min="1" max="10" wire:model.live="range"/>

        <h3>File</h3>
        <x-cd.form.text type="file" required="1" label="File" wire:model.live="file"/>

        <h3>Color</h3>
        <x-cd.form.text type="color" label="Color" wire:model.live="color"
                        value="#ff0000"/>

        <h3>Radio Buttons</h3>
        @php
            $radiooptions = [
                [ 'value' => "tomato", "label" => "Tomato"],
                [ 'value' => "lettuce", "label" => "Lettuce"],
                [ 'value' => "pickle", "label" => "Pickle"],
                [ 'value' => "onion", "label" => "Onion"],
            ];
        @endphp
        <x-cd.form.radios label="Radios" wire:model.live="radios" :radiobuttons="$radiooptions" />

        <h3>Submit, Reset, and Cancel Buttons</h3>
        <x-cd.form.submit-button />
        <x-cd.form.reset-button />
        <x-cd.form.cancel-button wire:click="closemodal" />

    </x-cd.form>
</x-cd.layout.app>
