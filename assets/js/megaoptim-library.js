(function ($) {

    /**
     * Return the library stats
     * @param params
     * @param success
     */
    $.get_library_stats = function (params, success) {

        var loader = new $.megaoptim.loader({
            'title': MGOLibrary.strings.loading_title,
            'description': MGOLibrary.strings.loading_description,
        });

        $.ajax({
            url: MGOLibrary.ajax_url + '?action=megaoptim_library_data&nonce=' + MGOLibrary.nonce_default,
            data: params,
            beforeSend: function () {
                loader.start()
            },
            type: 'POST',
            success: success,
            complete: function () {
                loader.stop()
            }
        });
    };

    /**
     * Prepare the MegaOptim processor for processing the files
     * - Get data
     * - Load with data
     * - Enable/disable start button
     *
     * @param params
     */
    $.prepare_processor = function (params) {
        var $wrapper = $('#megaoptim-optimizer-scan');
        var $processor_btn = $('#megaoptim-toggle-optimizer');
        $.get_library_stats(params, function (response) {
            if (response.success) {
                var $optimizer_container = $('#megaoptim-optimizer-wrapper');
                var $total_optimized_counter = $optimizer_container.find('#total_optimized');
                var $total_remaining_counter = $optimizer_container.find('#total_remaining');
                var $total_saved_bytes_counter = $optimizer_container.find("#total_saved_bytes");
                var $progress_percentage = $optimizer_container.find('#progress_percentage');
                var $progress_percentage_bar = $optimizer_container.find('#progress_percentage_bar');
                $total_optimized_counter.text(response.data.total_optimized_mixed);
                $total_remaining_counter.text(response.data.total_remaining);
                $total_saved_bytes_counter.text(response.data.total_saved_bytes_human);
                $progress_percentage.text(response.data.total_optimized_mixed_percentage + '%');
                $progress_percentage_bar.css('width', response.data.total_optimized_mixed_percentage + '%');
                window.megaoptim_attachment_list = response.data.remaining;
                if (response.data.total_remaining > 0) {
                    $processor_btn.prop('disabled', false);
                } else {
                    $processor_btn.prop('disabled', true);
                }
                $optimizer_container.show();
                $wrapper.hide();
            } else {
                alert("Internal server error. Please contact support.");
            }
        });
    };

    $(document).on('click', '#megaoptim-scan-library', function (e) {
        var context = $(this).data('context');
        e.preventDefault();
        $(this).prop('disabled', true);
        $.prepare_processor({context:context});
        $(this).prop('disabled', false);
    });

})(jQuery);