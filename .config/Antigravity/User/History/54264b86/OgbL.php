
<x-modals.creation-and-update-modal 
    id="import-modal"
    title="Import Student Data"
    action=""
    formId="student-import-form"
    submitButtonName="Import"
>

{{-- FILE --}}
<div class="col-sm-12 form-control-validation">
    <x-input.file-field
        id="student-file"
        label="Import File"
        name="file"
        accept=".csv,.xlsx,.xls"
        helptext="Upload CSV or Excel files for student import"
    />
</div>

</x-modals.creation-and-update-modal>
