<x-cd.layout.app title=":project_name" subtitle="Form Example">
    <x-slot name="sidebarTop">
        <div id="page-toc" class="page-toc">
            <h2 class="h4">Table of Contents</h2>
        </div>
        @push('scripts')
            <script>
                autoTOC();
            </script>
        @endpush
    </x-slot>

    <h1>Form Examples</h1>

    <h2>Documentation Examples</h2>
    <x-cd.form legend="Simple form" wire:submit="submit">
        <x-cd.form.text label="Name" wire:model="name"/>
        <div>
            <x-cd.form.submit-button/>
            <x-cd.form.reset-button/>
        </div>
        <x-cd.form.text label="Text Input" wire:model="name"/>
        @php
            $roleoptions = [
                [ 'value'=>'', 'option'=>'Select a Role', 'disabled'=>true],
                [ 'value'=>'administrator', 'option'=>'Administrator', 'disabled'=>false],
                [ 'value'=>'editor', 'option'=>'Editor', 'disabled'=>false],
                [ 'value'=>'subscriber', 'option'=>'Subscriber', 'disabled'=>false],
            ];
        @endphp
        <x-cd.form.select :options="$roleoptions" required="1" label="Select" wire:model="role" />
        <x-cd.form.checkbox label="Subscribe" value="1" wire:model="subscribe" />
        @php
            $checkboxoptions = [
                [ 'value' => "tomato", "label" => "Tomato"],
                [ 'value' => "lettuce", "label" => "Lettuce"],
                [ 'value' => "pickle", "label" => "Pickle"],
                [ 'value' => "onion", "label" => "Onion"],
            ];
        @endphp
        <x-cd.form.checkboxes :checkboxes="$checkboxoptions" wire:model="toppings" label="Topping Choices"/>
    </x-cd.form>

    <h2>Form Inputs</h2>
    <h3>Basic Text</h3>
    <aside>
        <h4>CSSF Reference</h4>
        <form>
            <fieldset class="semantic">
                <legend class="sr-only">This legend is read by screen readers, but should not be visible.</legend>

                <label for="field1">Text Input</label>
                <input type="text" id="field1" name="field1" size="32" placeholder="default (full-width)">
                <label for="field1b">Text Input (with <code>.use-size-attr</code> class)</label>
                <input type="text" class="use-size-attr" id="field1b" name="field1b" size="32" placeholder="honors the 'size' attribute">
                <label for="field1c">Text Input with Description</label>
                <input type="text" id="field1c" name="field1c" size="32" placeholder="use 'aria-describedby' on the input to reference the description ID" aria-describedby="field1c_desc">
                <div class="description" id="field1c_desc">This description text provides additional instruction or formatting hints.</div>
            </fieldset>
        </form>
    </aside>
    <x-cd.form>
        <x-slot:legendtype>semantic</x-slot:legendtype>
        <x-slot:legend_sr_only>true</x-slot:legend_sr_only>
        <x-slot:legend>This legend is read by screen readers, but should not be visible.</x-slot:legend>

        <x-cd.form.text field="field1" size="32" label="Text Input" placeholder="default (full-width)" />
        <x-cd.form.text field="field1b" class="use-size-attr" label="Text Input (with <code>.use-size-attr</code> class)" size="32" placeholder="honors the 'size' attribute"/>
        <x-cd.form.text field="field1c" label="Text Input with Description" size="32" placeholder="use 'aria-describedby' on the input to reference the description ID" description="This description text provides additional instruction or formatting hints."/>
    </x-cd.form>

    <h3>Checkboxes</h3>
    <aside>
        <h4>CSSF Reference</h4>
        <form>
            <fieldset class="semantic">
                <legend class="sr-only">This legend is read by screen readers, but should not be visible.</legend>

                <h4 class="no-margin">Single Checkboxes</h4>
                <p class="smallprint">Two approaches to pairing a single checkbox with its labeling:</p>
                <div class="form-item">
                    <label for="field21">Overarching Label</label>
                    <input type="checkbox" id="field21" name="field21" aria-describedby="field21_desc" checked="">
                    <span class="option-label" id="field21_desc">This checkbox has an overarching label as well as a longer, inline description.</span>
                </div>

                <div class="form-item">
                    <input type="checkbox" id="field22" name="field22" aria-describedby="field22_desc" checked="">
                    <label class="option-label" for="field22">This checkbox uses an inline label (which is therefore clickable!).</label>
                    <div class="description" id="field22_desc">It can optionally include a longer description underneath.</div>
                </div>

                <hr>
                <h4 class="no-margin">A Plethora of Checkboxes</h4>
                <p class="smallprint">A grouped set of checkboxes sharing the same <code>name</code> attribute:</p>

                <div class="flex-grid compact-rows">
                    <div class="form-item">
                        <input type="checkbox" id="field23" name="checkbox-set1" checked="" value="1">
                        <label class="option-label" for="field23">Checkbox Option One</label>
                    </div>
                    <div class="form-item">
                        <input type="checkbox" id="field24" name="checkbox-set1" value="2">
                        <label class="option-label" for="field24">Second Checkbox Option</label>
                    </div>
                    <div class="form-item">
                        <input type="checkbox" id="field25" name="checkbox-set1" value="3">
                        <label class="option-label" for="field25">Checkbox Option Three</label>
                    </div>
                    <div class="form-item">
                        <input type="checkbox" id="field26" name="checkbox-set1" value="4">
                        <label class="option-label" for="field26">Fourth Checkbox Option</label>
                    </div>
                </div>

            </fieldset>
        </form>
    </aside>
    <x-cd.form>
        <x-slot:legendtype>semantic</x-slot:legendtype>
        <x-slot:legend_sr_only>true</x-slot:legend_sr_only>
        <x-slot:legend>This legend is read by screen readers, but should not be visible.</x-slot:legend>

        <h4 class="no-margin">Single Checkboxes</h4>
        <p class="smallprint">Two approaches to pairing a single checkbox with its labeling:</p>
        <x-cd.form.checkbox name="cd-field21f" value="field21" label="Overarching Label" checked="" text="This checkbox has an overarching label as well as a longer, inline description."/>
        <x-cd.form.checkbox-inline name="cd-field22" value="field22" label="This checkbox uses an inline label (which is therefore clickable!)." checked="" description="It can optionally include a longer description underneath."/>

        <hr>
        @php
            $checkboxoptions = [
                [ 'value' => "1", "label" => "Checkbox Option One", "checked" => true],
                [ 'value' => "2", "label" => "Second Checkbox Option"],
                [ 'value' => "3", "label" => "Checkbox Option Three"],
                [ 'value' => "4", "label" => "Fourth Checkbox Option"],
            ];
        @endphp
        <x-cd.form.checkboxes :checkboxes="$checkboxoptions"
                              name="cd-checkbox-set1" label="A Plethora of Checkboxes"
        >
            <p class="smallprint">A grouped set of checkboxes sharing the same <code>name</code> attribute:</p>
        </x-cd.form.checkboxes>
    </x-cd.form>

    <h3>Radio Buttons</h3>
    <aside>
        <form>
            <fieldset class="semantic">
                <legend class="sr-only">This legend is read by screen readers, but should not be visible.</legend>

                <h4 class="no-margin">Radio Button's Crew</h4>
                <p class="smallprint">A grouped set of radio buttons sharing the same <code>name</code> attribute (and a similar taste in movies):</p>

                <div class="flex-grid compact-rows no-margin">
                    <div class="form-item">
                        <input type="radio" id="field28" name="radio-set1" checked="" value="1">
                        <label class="option-label" for="field28">A Single Radio Button No More!</label>
                    </div>
                    <div class="form-item">
                        <input type="radio" id="field29" name="radio-set1" value="2">
                        <label class="option-label" for="field29">Radio Button's Friend</label>
                    </div>
                    <div class="form-item">
                        <input type="radio" id="field30" name="radio-set1" value="3">
                        <label class="option-label" for="field30">Radio Button's Friend</label>
                    </div>
                    <div class="form-item">
                        <input type="radio" id="field31" name="radio-set1" value="4">
                        <label class="option-label" for="field31">Radio Button's Friend Who Has a Car</label>
                    </div>
                </div>

            </fieldset>
        </form>
    </aside>
    <x-cd.form>
        <x-slot:legendtype>semantic</x-slot:legendtype>
        <x-slot:legend_sr_only>true</x-slot:legend_sr_only>
        <x-slot:legend>This legend is read by screen readers, but should not be visible.</x-slot:legend>
        @php
            $radiooptions = [
                [ 'value' => "1", "label" => "A Single Radio Button No More!", "checked" => true],
                [ 'value' => "2", "label" => "Radio Button's Friend"],
                [ 'value' => "3", "label" => "Radio Button's Friend"],
                [ 'value' => "4", "label" => "Radio Button's Friend Who Has a Car"],
            ];
        @endphp
        <x-cd.form.radios :radiobuttons="$radiooptions" field="radio-set1" label="Radio Button's Crew"
                          description="A grouped set of radio buttons sharing the same <code>name</code> attribute (and a similar taste in movies):"/>
    </x-cd.form>

    <h3>Select Lists</h3>
    <aside>
        <h4>CSSF Reference</h4>
        <form>
            <fieldset class="semantic">
                <legend class="sr-only">This legend is read by screen readers, but should not be visible.</legend>

                <div class="form-item">
                    <label for="select1">Standard Single-Select</label>
                    <select id="select1" name="select1">
                        <option value=""></option>
                        <option value="1">Tone is supercool!</option>
                        <option value="2">Tone is fabulous and always well-spoken.</option>
                        <option value="3">Tone is a pillar of professionalism.</option>
                        <option value="4" disabled="disabled">Or is he a little full of himself...</option>
                        <option value="5" disabled="disabled">A menace to campus who must be stopped!</option>
                        <option value="6" disabled="disabled">Ha! You can't select these!</option>
                    </select>
                </div>
            </fieldset>
        </form>
    </aside>
    <x-cd.form>
        <x-slot:legendtype>semantic</x-slot:legendtype>
        <x-slot:legend_sr_only>true</x-slot:legend_sr_only>
        <x-slot:legend>This legend is read by screen readers, but should not be visible.</x-slot:legend>
        @php
            $selectoptions = [
                [ 'value' => "", "option" => ""],
                [ 'value' => "1", "option" => "Tone is supercool!"],
                [ 'value' => "2", "option" => "Tone is fabulous and always well-spoken."],
                [ 'value' => "3", "option" => "Tone is a pillar of professionalism.", "selected" => true],
                [ 'value' => "4", "option" => "Or is he a little full of himself...", "disabled" => true],
                [ 'value' => "5", "option" => "A menace to campus who must be stopped!", "disabled" => true],
                [ 'value' => "6", "option" => "Ha! You can't select these!", "disabled" => true],
            ];
        @endphp
        <x-cd.form.select :options="$selectoptions" name="select1" label="Standard Single-Select"/>
    </x-cd.form>
</x-cd.layout.app>
