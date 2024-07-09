$(document).ready(function() {
    function toggleClearButtonVisibility() {
        if ($('.form-control.search').val()) {
            $('#clearBtn').show(); // Show the clear button if the search input is not empty
        } else {
            $('#clearBtn').hide(); // Otherwise, hide the clear button
        }
    }

    // Initial check to decide whether to show or hide the clear button
    toggleClearButtonVisibility();

    $('.form-control.search').on('input', function() {
        toggleClearButtonVisibility();
    });

    // Bind click events to <a> tags for type selection.
    $('#mylibraryForm a').on('click', function(e) {
        e.preventDefault();

        var type = $(this).data('type');
        $('input[name="type"]').val(type);

        // Adjust sort value based on the type.
        if(type == '1') {
            // If type is 1, set sort to 'title asc'.
            $('#sortSelect').val('title asc');
        } else {
            // For other types, set sort to 'year desc'.
            $('#sortSelect').val('year desc');
        }

        // Update hidden input for sort to reflect the change.
        $('input[name="Filter[sort]"]').val($('#sortSelect').val());

        // Submit the form.
        $('#typeForm').submit();
    });

    // Ensure sort selection changes are recognized and form is submitted.
    $('#sortSelect').on('change', function() {
        $('input[name="Filter[sort]"]').val($(this).val());
        $('#typeForm').submit();
    });

    // Handle the clear search action.
    $('#clearBtn').on('click', function() {
        $('.form-control.search').val(''); // Clear the search field.
        toggleClearButtonVisibility();
        $('#typeForm').submit(); // Submit the form, reflecting the cleared search.
    });
});