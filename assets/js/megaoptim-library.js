(function ($) {

    /**
     * The initial attachments list
     * @type {*[]}
     */
    window.megaoptim_attachment_list = [];
    window.megaoptim_total_optimized_mixed = 0;
    window.megaoptim_total_fully_optimized_attachments = 0;
    window.megaoptim_total_thumbnails_optimized = 0;
    window.megaoptim_total_saved_bytes = 0;
    window.megaoptim_total_remaining = 0;
    window.megaoptim_total_pages = 1;
    window.megaoptim_current_page = 1;

    /**
     * Format bytes
     * @param bytes
     * @returns {string}
     */
    $.format_bytes = function (bytes) {
        var marker = 1024;
        var decimal = 0;
        var megaBytes = marker * marker;
        return (bytes / megaBytes).toFixed(decimal);
    };

    /**
     * Merge arrays
     * @param arr1
     * @param arr2
     * @returns {[]}
     */
    $.merge_arrays = function (arr1, arr2) {

        var final = [];
        if (Array.isArray(arr1) && arr1.length > 0) {
            for (var i in arr1) {
                final.push(arr1[i]);
            }
        }
        if (Array.isArray(arr2) && arr2.length > 0) {
            for (var j in arr2) {
                final.push(arr2[j]);
            }
        }
        return final;
    };

    /**
     * Queries data recursively.
     * @param params
     * @param success
     */
    $.query_stats = function (params, success) {
        $.ajax({
            url: MGOLibrary.ajax_url + '?action=megaoptim_library_data&nonce=' + MGOLibrary.nonce_default,
            data: params,
            type: 'POST',
            success: function (response) {
                if (response.hasOwnProperty('data')) {

                    var total_pages = response.data.hasOwnProperty('total_pages') ? response.data.total_pages : 1;
                    var current_page = parseInt(params['page']);
                    var next_page = current_page + 1;

                    if (response.data.hasOwnProperty('remaining')) {
                        window.megaoptim_attachment_list = $.merge_arrays(window.megaoptim_attachment_list, response.data.remaining);
                    }
                    if (response.data.hasOwnProperty('total_optimized_mixed')) {
                        window.megaoptim_total_optimized_mixed += parseInt(response.data.total_optimized_mixed);
                    }
                    if (response.data.hasOwnProperty('total_fully_optimized_attachments')) {
                        window.megaoptim_total_fully_optimized_attachments += parseInt(response.data.total_fully_optimized_attachments);
                    }
                    if (response.data.hasOwnProperty('total_thumbnails_optimized')) {
                        window.megaoptim_total_thumbnails_optimized += parseInt(response.data.total_thumbnails_optimized);
                    }
                    if (response.data.hasOwnProperty('total_saved_bytes')) {
                        window.megaoptim_total_saved_bytes += parseFloat(response.data.total_saved_bytes);
                    }
                    if (response.data.hasOwnProperty('total_remaining')) {
                        window.megaoptim_total_remaining += parseFloat(response.data.total_remaining);
                    }
                    if (response.data.hasOwnProperty('total_pages')) {
                        window.megaoptim_total_pages = response.data.total_pages;
                    }
                    if (current_page < total_pages) {
                        params['page'] = next_page;
                        $.query_stats(params, success);
                    } else {
                        success(1);
                    }
                } else {
                    success(0);
                }
            },
            error: function () {
                success(0);
            }
        });
    };

    /**
     * Return the library stats
     * @param params
     * @param success
     */
    $.get_library_stats = function (params, success) {

        params['page'] = 1;
        params['per_page'] = MGOLibrary.max_chunk_size;

        $.query_stats(params, success);
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

        var loader = new $.megaoptim.loader({
            'title': MGOLibrary.strings.loading_title,
            'description': MGOLibrary.strings.loading_description,
        });

        var $wrapper = $('#megaoptim-optimizer-scan');
        var $processor_btn = $('#megaoptim-toggle-optimizer');
        loader.start();
        $.get_library_stats(params, function (isSuccess) {
            if (isSuccess) {
                var $container = $('#megaoptim-optimizer-wrapper');

                var $total_optimized_counter = $container.find('#total_optimized');
                var $total_remaining_counter = $container.find('#total_remaining');
                var $total_saved_bytes_counter = $container.find("#total_saved_bytes");

                var $progress_percentage = $container.find('#progress_percentage');
                var $progress_percentage_bar = $container.find('#progress_percentage_bar');

                var total_optimized_percentage = 0;
                var total_saved_bytes = $.format_bytes(window.megaoptim_total_saved_bytes);
                var total_optimized = window.megaoptim_total_optimized_mixed;
                var total_remaining = window.megaoptim_total_remaining;
                var total_attachments = total_optimized + total_remaining;
                if (total_attachments > 0 && total_optimized > 0) {
                    total_optimized_percentage = ((total_optimized*100)/total_attachments).toFixed(2);
                }

                $total_optimized_counter.text(total_optimized);
                $total_remaining_counter.text(total_remaining);
                $total_saved_bytes_counter.text(total_saved_bytes);
                $progress_percentage.text(total_optimized_percentage + '%');
                $progress_percentage_bar.css('width', total_optimized_percentage + '%');

                if (total_remaining > 0) {
                    $processor_btn.prop('disabled', false);
                } else {
                    $processor_btn.prop('disabled', true);
                }
                $container.show();
                $wrapper.hide();
            } else {
                alert("Internal server error. Please contact support.");
            }
            loader.stop();
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