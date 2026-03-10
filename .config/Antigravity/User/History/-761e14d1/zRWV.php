<x-modals.creation-and-update-modal 
    id="student-import-create-modal"
    title="New Student Import"
    action=""
    submitButtonName="Submit"
    formId="student-import-create-form"
    enctype="multipart/form-data"
>

<div class="mb-3">
    <x-input.file-field
        id="student-file"
        label="Import File"
        name="file"
        accept=".csv,.xlsx,.xls"
        helptext="Upload CSV or Excel files for student import"
    />
</div>

</x-modals.creation-and-update-modal>
