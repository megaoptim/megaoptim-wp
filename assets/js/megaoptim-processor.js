(function ($) {

    /*global $, MGOProcessorData */
    /*jslint browser:true */

    /**
     * Class used for processing the images
     * @constructor
     */
    var MGOProcessor = function (action, context) {

        this.action = action;
        this.context = context;

        /**
         * Mark the optimizer as running
         */
        this.set_optimizer_running = function () {
            $.is_megaoptim_optimizer_running = true;
        };

        /**
         * Mark optimizer as stopped/paused
         */
        this.set_optimizer_off = function () {
            $.is_megaoptim_optimizer_running = false;
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
            $('#megaoptim-select-current-theme-folder').addClass('disabled').prop('disabled', true);
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
            $('#megaoptim-select-current-theme-folder').removeClass('disabled').prop('disabled', false);
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
            self.unlock_optimizer();
            self.set_optimizer_running();
            var attachments = self.get_attachments();
            if (!self.is_table_populated()) {
                self.populate_results_table(attachments);
            }
            setTimeout(function () {
                var index = 0;
                if ($.megaoptim_current_index) {
                    index = $.megaoptim_current_index;
                }
                self.run(index, attachments.length, attachments);
            }, 1000);
        };

        /**
         * Turn off the optimizer
         */
        this.stop_optimizer = function () {
            var $btn = $('#megaoptim-toggle-optimizer');
            self.set_optimizer_off();
            $btn.text(MGOProcessorData.strings.cancelling + '...');
            $btn.prop('disabled', true);
            setTimeout(function () {
                $btn.text($btn.data('start-text'));
                $btn.prop('disabled', false);
                self.set_optimizer_off();
            }, 4000);
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
                            if (response.success) {
                                self.update_table_row(response.data['attachment']);
                                self.update_other_data(response.data['general']);
                                self.log('Response ***success*** received for image with id: ' + data[index]['ID'], 'info');
                                if (response.data['general']['total_remaining'] === 0) {
                                    self.stop_optimizer(); // is_running = false
                                } else if (response.data['tokens'] === 0) {
                                    window.location.href = window.location.href;
                                }
                            } else {
                                self.log('Response ***error*** received for image with id: ' + data[index]['ID'], 'warn');
                                self.log(response.data, 'log');
                                self.stop_optimizer(); // is_running = false
                                self.update_row_error(data[index]['ID'], response.data.error);
                                // Maybe show popup?
                            }
                        }
                        else {
                            self.stop_optimizer(); // is_running = false
                            self.log('Optimizer finished.', 'info');
                            return;
                        }
                        index++;
                        setTimeout(function () {
                            self.unlock_optimizer();
                            self.run(index, len, data);
                        }, 500);
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
                self.bind_spinner('.megaoptim-postbox');
                for (i = 0; i < attachments.length; i++) {
                    var html = '<tr id="attachment_' + attachments[i]["ID"] + '">' +
                        '<td class="thumbnail atttachment_name"><img src="' + attachments[i]['thumbnail'] + '" width="25"></td>' +
                        '<td class="column-primary atttachment_name">' + attachments[i]["title"] + '</td>' +
                        '<td class="column-author attachment_original_size">-</td>' +
                        '<td class="column-author attachment_optimized_size">-</td>' +
                        '<td class="column-author attachment_saved_bytes">-</td>' +
                        '<td class="column-author attachment_saved_percent">-</td>' +
                        '<td class="column-author attachment_optimized_thumbs">-</td>' +
                        '<td class="column-status attachment_status">' + MGOProcessorData.strings.waiting + '</td>' +
                        '</tr>';
                    $body.append(html);
                }
                self.unbind_spinner('.megaoptim-postbox');
            }
        };

        /**
         * Returns the results table
         * @returns {*|HTMLElement}
         */
        this.get_results_table = function () {
            return $('#megaoptim-results-table');
        };

        /**
         * Bind spinner
         * @param selector
         */
        this.bind_spinner = function (selector) {
            $(selector).LoadingOverlay('show');
        };

        /**
         * Unbind spinner
         * @param selector
         */
        this.unbind_spinner = function (selector) {
            $(selector).LoadingOverlay('hide');
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
                var txt_optimized_thumbs = attachment.optimized_thumbs;
                if (attachment.hasOwnProperty('optimized_thumbs_retina') && attachment.optimized_thumbs_retina > 0 && attachment.hasOwnProperty('saved_thumbs_retina') ) {
                    txt_optimized_thumbs += ' regular (total saved: ' + attachment.saved_thumbs + ') and ' + attachment.optimized_thumbs_retina + ' retina (total saved: ' + attachment.saved_thumbs_retina + ')';
                }
                $row.find('.attachment_optimized_thumbs').text(txt_optimized_thumbs);
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
        this.update_other_data = function (data) {
            if (data.hasOwnProperty('total_optimized_mixed')) {
                $('#total_optimized_mixed').text(data['total_optimized_mixed']);
            }
            if (data.hasOwnProperty('total_remaining')) {
                $('#total_remaining').text(data['total_remaining']);
                if (data['total_remaining'] === 0) {
                    $('#megaoptim-toggle-optimizer').addClass('disabled').prop('disabled', true);
                }
            }
            if (data.hasOwnProperty('total_saved_bytes_human')) {
                $('#total_saved_bytes').text(data['total_saved_bytes_human']);
            }
            if (data.hasOwnProperty('total_optimized_mixed_percentage')) {
                if (data['total_optimized_mixed_percentage'] <= 100) {
                    $('.megaoptim-progress-bar-content').text(data['total_optimized_mixed_percentage'] + '%');
                    $('.megaoptim-progress-bar-fill').css({width: data['total_optimized_mixed_percentage'] + '%'});
                } else {
                    alert('Progress: Unknown error.');
                }
            }
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