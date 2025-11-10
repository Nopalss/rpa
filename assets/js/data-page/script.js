$(document).ready(function () {
    // ---------------------------------
    // FUNGSI FORM DATA (DROPDOWN)
    // ---------------------------------
    $(document).on('change', '.line2', function () {
        const lineId = $(this).val();
        const $application = $(`.application2`);
        $application.prop('disabled', true).html('<option value="">Loading...</option>');

        if (lineId) {
            $.ajax({
                url: `${HOST_URL}api/get_applications.php`,
                type: 'POST',
                data: {
                    line_id: lineId
                },
                dataType: 'json',
                success: function (response) {
                    $application.prop('disabled', false).html('<option value="">Select</option>');
                    $.each(response, function (i, item) {
                        $application.append(`<option value="${item.id}">${item.name}</option>`);
                    });
                },
                error: function () {
                    $application.html('<option value="">Error loading</option>');
                }
            });
        } else {
            $application.html('<option value="">Select</option>');
        }
    });

})