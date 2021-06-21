(function ($) {
    /*global $, MGOProcessorData */
    /*jslint browser:true */

    /**
     * Class used for processing the images
     * @constructor
     */
    var MGOProcessor = function (action, context) {

        this.fatalErrors = 0;
        this.maxFatalErrors = 30;

        this.action = action;
        this.context = context;
        this.loader_preparing = new $.megaoptim.loader({
            'title': MGOProcessorData.strings.loader_working_title,
            'description': MGOProcessorData.strings.loader_working_description,
        });

        /**
         * Mark the optimizer as running
         */
        this.set_optimizer_running = function () {
            $.is_megaoptim_optimizer_running = true;
            $('body').data('megaoptim', 'running');
        };

        /**
         * Mark optimizer as stopped/paused
         */
        this.set_optimizer_off = function () {
            $.is_megaoptim_optimizer_running = false;
            $('body').data('megaoptim', 'stopped');
        };

        /**
         * Lock! when the optimizer is busy processing attachment
         */
        this.lock_optimizer = function () {
            $.is_megaoptim_optimizer_locked = true;
            $('button[name=switch]').prop('disabled', true);
            $('select[name=module]').prop('disabled', true);
            $('#megaoptim-selected-dir').prop('disabled', true);
            $('#megaoptim-select-folder').addClass('disabled').prop('disabled', true);
            $('.megaoptim-optimize-theme-folder').addClass('disabled').prop('disabled', true);
        };

        /**
         * Unlock the optimizer once the attachment is processed.
         */
        this.unlock_optimizer = function () {
            $.is_megaoptim_optimizer_locked = false;
            $('button[name=switch]').prop('disabled', false);
            $('select[name=module]').prop('disabled', false);
            $('#megaoptim-selected-dir').prop('disabled', false);
            $('#megaoptim-select-folder').removeClass('disabled').prop('disabled', false);
            $('.megaoptim-optimize-theme-folder').removeClass('disabled').prop('disabled', false);
        };

        /**
         * Is the optimizer started/running?
         * @returns {boolean}
         */
        this.is_running = function () {
            return $.is_megaoptim_optimizer_running;
        };

        /**
         * Is the optimizer busy/locked?
         * @returns {boolean}
         */
        this.is_locked = function () {
            return $.is_megaoptim_optimizer_locked;
        };

        /**
         * Is the Media Library optimizer?
         * @returns {boolean}
         */
        this.is_media_library = function () {
            return this.context === MGOProcessorData.context.media_library;
        };

        /**
         * Is the NGG Optimizer ?
         * @returns {boolean}
         */
        this.is_ngg = function () {
            return this.context === MGOProcessorData.context.ngg;
        };

        /**
         * Is local folders?
         * @returns {boolean}
         */
        this.is_local_folders = function () {
            return this.context === MGOProcessorData.context.local_folders;
        };

        /**
         * Init?
         */
        this.init = function () {
            if (!self.is_table_populated()) {
                self.populate_results_table(self.get_attachments());
            }
        };

        /**
         * Get the attachments
         */
        this.get_attachments = function () {
            return window.megaoptim_attachment_list;
        };

        /**
         * Turn on the optimizer
         */
        this.start_optimizer = function () {
            var $spin = $('#megaoptim-running-spinner');
            self.fatalErrors = 0;
            self.loader_preparing.start();
            self.unlock_optimizer();
            self.set_optimizer_running();
            var attachments = self.get_attachments();
            if (self.is_local_folders()) {
                if (!self.is_table_populated()) {
                    self.populate_results_table(attachments);
                }
            }
            setTimeout(function () {
                var index = 0;
                if ($.megaoptim_current_index) {
                    index = $.megaoptim_current_index;
                }
                self.loader_preparing.stop();
                $spin.show();
                self.run(index, attachments.length, attachments);
            }, 500);

        };

        /**
         * Turn off the optimizer
         */
        this.stop_optimizer = function () {
            var $spin = $('#megaoptim-running-spinner');
            var $btn = $('#megaoptim-toggle-optimizer');
            self.set_optimizer_off();
            $btn.text(MGOProcessorData.strings.cancelling + '...');
            $btn.prop('disabled', true);
            setTimeout(function () {
                $spin.hide();
                $btn.text($btn.data('start-text'));
                $btn.prop('disabled', false);
                self.set_optimizer_off();
            }, 5000);
            self.fatalErrors = 0;
        };

        /**
         * Run the optimizer
         * @param index
         * @param len
         * @param data
         */
        this.run = function (index, len, data) {
            $.megaoptim_current_index = index;
            var action = self.action;
            if (!len || !data) {
                self.stop_optimizer();
                return;
            }
            if (self.is_running() && !self.is_locked()) {
                if (undefined === data[index] || !data[index] || !data[index].hasOwnProperty('ID')) {
                    self.stop_optimizer();
                    return;
                }
                self.log('Start optimizing attachment with id:' + data[index]['ID'], 'info');
                self.add_table_row(data[index]);
                self.update_row_status(data[index]['ID'], self.get_small_spinner(MGOProcessorData.strings.optimizing));
                self.lock_optimizer();
                $.ajax({
                    type: "POST",
                    dataType: "json",
                    url: MGOProcessorData.ajax_url,
                    data: {action: action, nonce: MGOProcessorData.nonce_optimizer, attachment: data[index]},
                    async: true,
                    success: function (response) {
                        self.log('Received response. Optimization maybe done?!', 'info');
                        self.log(response, 'log');
                        if (index < len) {
                            if (response.hasOwnProperty('success')) {
                                if (response.success) {
                                    // Success handling
                                    self.update_table_row(response.data['attachment']);
                                    self.update_couters(response.data);
                                    if (parseInt(response.data['tokens']) === 0) {
                                        $(window).off('beforeunload');
                                        window.location.href = window.location.href;
                                    }
                                } else {
                                    // Failed optimization handling.
                                    self.update_row_error(data[index]['ID'], response.data.error);
                                    self.log(response.data, 'log');
                                    if (response.data.hasOwnProperty('can_continue')) {
                                        if( !(response.data['can_continue'] === 1 || response.data['can_continue'] === '1') ) {
                                            self.stop_optimizer();
                                        }
                                    }
                                }
                                self.fatalErrors = 0; // for consecutiveness.
                            } else {
                                // Unreadable response.
                                self.update_row_error(data[index]['ID'], MGOProcessorData.strings.parse_error);
                                self.fatalErrors++;
                                if (self.fatalErrors >= self.maxFatalErrors) {
                                    alert(MGOProcessorData.strings.consecutive_errors.replace('_number_', self.fatalErrors));
                                    self.stop_optimizer();
                                }
                            }

                        } else {
                            // Finished?
                            self.stop_optimizer();
                            self.log('Optimizer finished.', 'info');
                            return;
                        }
                        // Next attachment.
                        index++;
                        setTimeout(function () {
                            self.unlock_optimizer();
                            self.run(index, len, data);
                        }, 120);
                    },
                    error: function () {
                        // Error handling.
                        self.update_row_error(data[index]['ID'],  MGOProcessorData.strings.unprocessable);
                        self.fatalErrors++;
                        if (self.fatalErrors >= self.maxFatalErrors) {
                            alert(MGOProcessorData.strings.consecutive_errors.replace('_number_', self.fatalErrors));
                            self.stop_optimizer();
                        } else {
                            self.unlock_optimizer();
                            self.run(index, len, data);
                        }
                    }
                });
            } else {
                self.log('Optimizer already waiting for file to be optimized...', 'info');
            }
        };

        /**
         * Populate the results table
         * @param attachments
         */
        this.populate_results_table = function (attachments) {
            if (attachments.length > 0) {
                var $table = self.get_results_table();
                var $body = $table.find('tbody');
                self.loader_preparing.start();
                for (var i = 0; i < attachments.length; i++) {
                    $body.append(self.generate_table_row(attachments[i]));
                }
                self.loader_preparing.stop();
            }
        };

        /**
         * Returns the row html
         * @param attachment
         * @returns {string}
         */
        this.generate_table_row = function (attachment) {
            return '<tr id="attachment_' + attachment["ID"] + '">' +
                '<td class="thumbnail atttachment_name"><img src="' + attachment['thumbnail'] + '" width="25"></td>' +
                '<td class="column-primary atttachment_name">' + attachment["title"] + '</td>' +
                '<td class="column-author attachment_original_size">-</td>' +
                '<td class="column-author attachment_optimized_size">-</td>' +
                '<td class="column-author attachment_saved_bytes">-</td>' +
                '<td class="column-author attachment_saved_percent">-</td>' +
                '<td class="column-author attachment_optimized_thumbs">-</td>' +
                '<td class="column-status attachment_status">' + MGOProcessorData.strings.waiting + '</td>' +
                '</tr>';
        };

        /**
         * Add row to the table
         * @param attachment
         */
        this.add_table_row = function (attachment) {

            var $table = self.get_results_table();
            var $body = $table.find('tbody');

            // Clean up older entries. Allow only N rows to be displayed.
            var max_rows = 30;
            var $rows = $body.find('tr');
            if ($rows.length > max_rows) {
                $body.find('tr:last').remove();
            }

            // Insert new one
            $body.prepend(self.generate_table_row(attachment));
        };

        /**
         * Returns the results table
         * @returns {*|HTMLElement}
         */
        this.get_results_table = function () {
            return $('#megaoptim-results-table');
        };

        /**
         * Returns true if the table is already populated.
         * @returns {boolean}
         */
        this.is_table_populated = function () {
            var $table = self.get_results_table();
            var $body = $table.find('tbody');
            var $rows = $body.find('tr');
            return $rows.length > 0;
        };

        /**
         * Prints in console
         * @param message
         * @param type
         */
        this.log = function (message, type) {
            if (type === 'info') {
                console.info(message);
            } else if (type === 'warn') {
                console.warn(message);
            } else if (type === 'log') {
                console.log(message);
            }
        };
        /**
         * Used to update row with newly received details
         * @param attachment
         */
        this.update_table_row = function (attachment) {
            var attachment_id = attachment['ID'];
            var $row = $('#attachment_' + attachment_id);
            if ($row.length > 0) {
                $row.find('.attachment_original_size').text(attachment.original_size);
                $row.find('.attachment_optimized_size').text(attachment.optimized_size);
                $row.find('.attachment_saved_bytes').text(attachment.saved_bytes);
                $row.find('.attachment_saved_percent').text(attachment.saved_percent + '%');
                var txt_processed_thumbs = attachment.processed_thumbs;
                if (attachment.hasOwnProperty('processed_thumbs_retina') && attachment.processed_thumbs_retina > 0 && attachment.hasOwnProperty('saved_thumbs_retina')) {
                    txt_processed_thumbs += ' regular (total saved: ' + attachment.saved_thumbs + ') and ' + attachment.processed_thumbs_retina + ' retina (total saved: ' + attachment.saved_thumbs_retina + ') thumbs';
                } else if (attachment.hasOwnProperty('saved_thumbs') && attachment.saved_thumbs > 0) {
                    txt_processed_thumbs += ' regular thumbs (total saved: ' + attachment.saved_thumbs + ')';
                }
                $row.find('.attachment_optimized_thumbs').text(txt_processed_thumbs);
                var status;
                if (attachment.saved_percent < 5) {
                    status = MGOProcessorData.strings.already_optimized;
                } else {
                    status = MGOProcessorData.strings.finished;
                }
                $row.find('.attachment_status').html(self.get_small_icon(status, 'megaoptim-check'));
            }
        };
        /**
         * Used to update single row status
         * @param attachment_id
         * @param status
         */
        this.update_row_status = function (attachment_id, status) {
            var $row = $('#attachment_' + attachment_id);
            if ($row.length > 0) {
                $row.find('.attachment_status').html(status);
            }
        };

        /**
         * Used to update other data in the dashboard
         * @param data
         */
        this.update_couters = function (data) {
            var percent = 0;
            // elements
            var $el_total_optimized_mixed = $('#total_optimized');
            var $el_total_remaining = $('#total_remaining');
            var $el_total_saved_bytes = $('#total_saved_bytes');
            var $el_percent_number = $('#progress_percentage');
            var $el_percent_bar = $('#progress_percentage_bar');

            // calculations
            var total_sizes_processed = data.attachment.processed_total;
            var total_saved_megabytes = data.attachment.raw.saved_total_mb;

            var total_optimized = parseInt($el_total_optimized_mixed.text()) + total_sizes_processed;
            var total_remaining = parseInt($el_total_remaining.text()) - total_sizes_processed;
            var total = (total_optimized + total_remaining);
            var total_saved_bytes = parseFloat($el_total_saved_bytes.text()) + parseFloat(total_saved_megabytes);

            // aassignments
            $el_total_optimized_mixed.text(total_optimized);
            $el_total_remaining.text(total_remaining);
            $el_total_saved_bytes.text(total_saved_bytes.toFixed(2));

            // progress bar
            if (total <= 0) {
                percent = 100;
            } else if (total_optimized <= 0) {
                percent = 0;
            } else {
                percent = (total_optimized / total) * 100;
            }
            $el_percent_bar.css({width: percent.toFixed(2) + '%'});
            $el_percent_number.text(percent.toFixed(2) + '%');
        };

        /**
         * Set row error
         * @param id
         * @param error
         */
        this.update_row_error = function (id, error) {
            self.update_row_status(id, '<span class="megaoptim-error"><strong>' + MGOProcessorData.strings.error + ':</strong> ' + error + '</span>');
        };

        /**
         * Generate small spinner
         * @param text
         * @returns {string}
         */
        this.get_small_spinner = function (text) {
            return '<div class="megaoptim-spinner-wrapper"><span class="megaoptim-spinner"></span><span>' + text + '</span></div>';
        };

        /**
         * Generate small spinner
         * @param text
         * @returns {string}
         */
        this.get_small_icon = function (text, icon) {
            return '<div class="megaoptim-icon-wrapper"><span class="' + icon + '"></span><span>' + text + '</span></div>';
        };

        /**
         * Self instance, accessible in methods.
         * @type {MGOProcessor}
         */
        var self = this;
    };


    $(document).on('click', '#megaoptim-toggle-optimizer', function (e) {
        e.preventDefault();
        var $self = $(this);
        if ($self.hasClass('disabled') || $self.is(':disabled')) {
            return;
        }
        var current_state = $(this).data('next-state');
        var action = $(this).data('action');
        var context = $(this).data('context');
        var next_state = current_state === 'start' ? 'stop' : 'start';
        var next_label = current_state === 'start' ? $self.data('stop-text') : $self.data('start-text');
        $self.data('next-state', next_state);
        $self.text(next_label);
        // Init the processor
        var processor = new MGOProcessor(action, context);
        if (current_state === 'start') {
            processor.start_optimizer();
        } else {
            processor.stop_optimizer();
        }
    });


})(jQuery);