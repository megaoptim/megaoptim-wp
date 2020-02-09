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
        var params = {context: context};

        $('.mgo-filter').each(function (i, self) {
            var key = $(self).data('key');
            var value = $(self).val();
            if (!value) {
                return; // continue;
            }
            params[key] = value;
        });

        e.preventDefault();
        $(this).prop('disabled', true);
        $.prepare_processor(params);
        $(this).prop('disabled', false);
    });

})(jQuery);


// Optimizer Filters
(function ($) {

    if (jQuery().datepicker) {
        $('.mgo-datepicker').each(function () {
            var format = $(this).data('format');
            if (!format) {
                format = 'yy-mm-dd';
            }
            $(this).datepicker({
                'dateFormat': format,
                changeMonth: true,
                changeYear: true
            })
        });
    }

    $(document).on('click', '.megoaptim-current-filters-clear a', function (e) {
        e.preventDefault();
        var filtersForm = document.getElementById("megaoptim-filters-form");
        if (filtersForm) {
            filtersForm.reset();
            $('.megaoptim-current-filters').hide();
            $('.megoaptim-current-filters-wrap ul').html('');
        }
    });

    $(document).on('change', '.mgo-filter', function (e) {
        var filters = [];
        $('.mgo-filter').each(function (i, self) {
            var value = $(self).val();
            if (!value) {
                return; // continue;
            }
            var label = $(self).data('label');
            if (!label) {
                var labelEl = $(self).closest('.mgo-form-group').find('label');
                if (labelEl.length) {
                    label = labelEl.text();
                }
            }
            var formatted = '<li>' + label + '(' + value + ')</li>';
            filters.push(formatted)
        });

        if (filters.length > 0) {
            var formatted;
            if (filters.length > 1) {
                formatted = filters.join('');
            } else {
                formatted = filters[0];
            }
            var html = '<div class="megaoptim-current-filters">\n' +
                '    <div class="megoaptim-current-filters-wrap">\n' +
                '        <div class="megoaptim-current-filters-label">'+MegaOptim.strings.current_filters+':</div>\n' +
                '        <ul class="megoaptim-current-filters-list">'+formatted+'</ul>\n' +
                '    </div>\n' +
                '    <div class="megoaptim-current-filters-clear"><a href="#">'+MegaOptim.strings.clear+'</a>\n' +
                '    </div>\n' +
                '</div>';
            $('.megaoptim-filters-wrap').each(function(i, fself){
                $(fself).html(html);
            });
        } else {
            $('.megaoptim-filters-wrap').each(function(i, fself){
                $(fself).html('');
            });
        }
    });

})(jQuery);