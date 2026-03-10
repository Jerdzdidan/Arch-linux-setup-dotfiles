
<x-modals.creation-and-update-modal 
    id="student-import-update-modal"
    title="Update Student Import"
    action=""
    submitButtonName="Submit"
    formId="student-import-update-form"
>

{{-- Name --}}
<div class="col-12 form-control-validation">
    <x-input.input-field
        id="filename" 
        name="filename" 
        label="Filename"
        type="text"
        icon="fa-solid fa-file"
        placeholder="Filename" 
        help=""
    />
</div>

</x-modals.creation-and-update-modal>
