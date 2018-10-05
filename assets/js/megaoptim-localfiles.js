(function ($) {

    /**
     * Init tabs
     */
    $.init_tabs = function () {
        var $tabs = $('.megaoptim-tabs');
        $('.megaoptim-tabcontent').hide();
        var $active = $tabs.find('.megaoptim-tablinks.active');
        var $active_tab_content = $tabs.find($active.data('tab'));
        $active_tab_content.show();
    };


    /**
     * Returns the dir stats
     * @param dir
     * @param data
     * @param success
     */
    $.get_folder_stats = function (dir, data, success) {
        var params = {};
        params.dir = dir;
        if (data && data.hasOwnProperty('recursive')) {
            params.recursive = data.recursive;
        }
        $.ajax({
            url: MGOLocalFiles.ajax_url + '?action=megaoptim_directory_data&nonce=' + MGOLocalFiles.nonce_default,
            data: params,
            beforeSend: function () {
                $('.megaoptim-postbox').LoadingOverlay('show', {
                    //size:40,
                    maxSize: 30,
                    background: "rgba(16, 50, 128, 0.8)"
                });
            },
            type: 'POST',
            success: success,
            complete: function () {
                $('.megaoptim-postbox').LoadingOverlay('hide');
            }
        });
    };

    /**
     * Prepare the MegaOptim processor for processing the files
     * - Get data
     * - Load with data
     * - Enable/disable start button
     * @param path
     * @param data
     */
    $.prepare_processor = function (path, data) {
        if (path) {
            var $processor_btn = $('#megaoptim-toggle-optimizer');
            $.get_folder_stats(path, data, function (response) {
                if (response.success) {
                    var $optimizer_container = $('#megaoptim-file-optimizer');
                    var $total_optimized_counter = $optimizer_container.find('#total_optimized_mixed');
                    var $total_remaining_counter = $optimizer_container.find('#total_remaining');
                    var $total_saved_bytes_counter = $optimizer_container.find("#total_saved_bytes");
                    var $progress_percentage = $optimizer_container.find('#progress_percentage');
                    var $progress_percentage_bar = $optimizer_container.find('#progress_percentage_bar');
                    var $info = $optimizer_container.find('.megaoptim-info');
                    $total_optimized_counter.text(response.data.total_optimized_mixed);
                    $total_remaining_counter.text(response.data.total_remaining);
                    $total_saved_bytes_counter.text(response.data.total_saved_bytes_human);
                    $progress_percentage.text(response.data.total_optimized_mixed_percentage + '%');
                    $progress_percentage_bar.css('width', response.data.total_optimized_mixed_percentage + '%');
                    window.megaoptim_attachment_list = response.data.remaining;
                    if (response.data.total_remaining > 0) {
                        $processor_btn.prop('disabled', false);
                        $info.html(MGOLocalFiles.info_not_optimized);
                    } else {
                        $processor_btn.prop('disabled', true);
                        $info.html(MGOLocalFiles.info_optimized)
                    }
                    $('#megaoptim-selected-folder').html('<p><strong>'+MGOLocalFiles.selected_folder+'</strong>: ' + path + '</p>').show();
                    $optimizer_container.show();
                } else {
                    alert("Internal server error. Please contact support.");
                }
            });
        }
    };

    // Make folder selected
    $(document).on('click', '.megaoptim-select-directory', function () {
        $('.directory').removeClass('megaoptim-directory-selected');
        $(this).closest('.directory').addClass('megaoptim-directory-selected');
    });

    // Select folder action
    $(document).on('click', '#sos-dir-select-action', function (e) {
        var $selected = $('.megaoptim-directory-selected');
        if ($selected.length <= 0) {
            alert(MGOLocalFiles.alert_select_files);
        } else {
            var instance = $(this).closest('.remodal').remodal();
            var path = jQuery("UL.jqueryFileTree LI.directory.megaoptim-directory-selected A").attr("rel");
            $.prepare_processor(path);
            instance.close();
        }
    });

    $(document).on('click', '#megaoptim-select-current-theme-folder', function (e) {
        var path = $(this).data('themedir');
        $.prepare_processor(path, {recursive: 1});
    });

    // handle tabs
    $(document).on('click', '.megaoptim-tablinks', function (e) {
        e.preventDefault();
        var $self = $(this);
        if (!$self.hasClass('disabled')) {
            var $tabs = $self.closest('.megaoptim-tabs');
            var target = $self.data('tab');
            var $alltablinks = $('.megaoptim-tablinks');
            var $allcontent = $('.megaoptim-tabcontent');
            if ($tabs.length > 0) {
                var $active_tab_content = $tabs.find(target);
                if ($active_tab_content.length > 0) {
                    $alltablinks.removeClass('active');
                    $allcontent.hide();
                    $self.addClass('active');
                    $active_tab_content.show();
                }
            }
        }
    });

    function main() {
        // Init file tree
        if ($.fn.fileTree) {
            $('.megaoptimdirtree').fileTree({
                root: '/',
                script: MGOLocalFiles.ajax_url + '?action=megaoptim_directory_tree&nonce=' + MGOLocalFiles.nonce_default,
                expandSpeed: 500,
                collapseSpeed: 500,
                onlyFolders: true,
                multiFolder: false,
                multiSelect: false
            });
        }
        // Init tabs
        $.init_tabs('.megaoptim-tabs');
    }

    main();

})(jQuery);