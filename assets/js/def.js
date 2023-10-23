function autoRequestFormLibrary() {
    $('#libraryForm').on('change', 'input, select', function () {
        var $form = $(this).closest('form');

        $form.request();
    })
}
document.addEventListener('DOMContentLoaded', function () {
    autoRequestFormLibrary();
});
