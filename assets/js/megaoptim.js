/**
 * MegaOptim Options
 */
(function ($) {
    $(document).on('submit', "#megaoptim_save_form", function () {
        var data = $(this).serialize();
        var dismiss = '\t<button type="button" class="notice-dismiss megaoptim-notice-dismiss">\n' +
            '\t\t<span class="screen-reader-text">Dismiss this notice.</span>\n' +
            '\t</button>';
        var action = $(this).data('action');
        $.ajax({
            url: MegaOptim.ajax_url + '?action=' + action,
            type: "POST",
            data: data + '&nonce=' + MegaOptim.nonce_settings,
            dataType: 'json',
            beforeSend: function () {
                $('.megaoptim-postbox').LoadingOverlay('show', {'size': 20});
            },
            success: function (sdata) {
                var $save_status = $("#save_status");
                if (sdata.success && sdata.success === true) {
                    $save_status.html('<div class="notice notice-success is-dismissible"><p>' + sdata.message + '</p>' + dismiss + '</div>');
                } else {
                    var status = '';
                    if (sdata.errors && sdata.errors.length > 0) {
                        var errors = '';
                        errors += '<ul>';
                        for (var error in sdata.errors) {
                            errors += '<li>' + sdata.errors[error] + '</li>';
                        }
                        errors += '</ul>';
                        status += '<div class="notice notice-error is-dismissible"><p>' + sdata.message + '</p>' + errors + dismiss + '</div>';
                        $save_status.html(status);
                    }
                }
            },
            complete: function () {
                $('.megaoptim-postbox').LoadingOverlay('hide');
            }
        });
        return false;
    });
    $(document).on('click', '.megaoptim-notice-dismiss', function () {
        $(this).closest('.notice').detach().remove();
    })
})(jQuery);


/**
 * MegaOptim
 * DISMISS Nonce
 */
(function ($) {
    $(document).on('click', '.dismiss-megaoptim-notice', function (e) {
        e.preventDefault();
        var $instructions = $(this).closest('.instructions');
        if (!$instructions.is(":hidden")) {
            //noinspection JSUnresolvedVariable
            $.ajax({
                url: MegaOptim.ajax_url + '?action=megaoptim_instructions_dismiss',
                type: "POST",
                data: {
                    dismiss_instructions: 1,
                    nonce: MegaOptim.nonce_default
                },
                dataType: 'json',
                success: function (response) {
                    if (response.success) {
                        $instructions.slideUp();
                    }
                }
            });
        }
    });
    $("form#megaoptim-register-modal").submit(function (e) {
        var data = $(this).serialize();
        //TODO
        return false;
    });
})(jQuery);

/**
 * MegaOptim
 * API Key
 */
(function ($) {
    $(document).on('submit', '.megaoptim-apikey-form', function (e) {
        var $self = $(this);
        var $modal = $(this).closest('.remodal');
        var data = $self.serialize();
        var $messages = $self.find('.megaoptim-modal-status');
        var $fields = $self.find('.form-wrapper');
        var $actions = $self.find('.form-actions');
        $.ajax({
            url: MegaOptim.endpoints.setapikey,
            type: "POST",
            data: data + '&nonce=' + MegaOptim.nonce_settings,
            dataType: 'json',
            beforeSend: function () {
                $modal.LoadingOverlay('show');
            },
            success: function (response) {
                if (response.success) {
                    var $title = $('.megaoptim-title');
                    var $subtitle = $('.megaoptim-subtitle');
                    $title.text('Done!');
                    $subtitle.hide();
                    $messages
                        .html(response.data.message)
                        .show()
                        .removeClass('error')
                        .addClass('success');
                    $fields.hide();
                    $actions.hide();
                    setTimeout(function () {
                        window.location.href = MegaOptim.urls.settings
                    }, 4000);
                } else {
                    $messages.html('<p>' + response.data.error + '</p>').show().removeClass('success').addClass('error');
                }
            },
            complete: function () {
                $modal.LoadingOverlay('hide');
            }
        });
        return false;
    });

})(jQuery);


/**
 * Export debug info in the settings screen
 */
(function ($) {
    $(document).on('click', '.megaoptim-export-table', function (e) {
        e.preventDefault();
        var action = 'megaoptim_export_report';
        var url = MegaOptim.ajax_url + '?action=' + action + '&nonce=' + MegaOptim.nonce_settings;
        document.location = url;
    })
})(jQuery);


/**
 * Remove batckup advanced settings buttons
 */
(function ($) {
    $(document).on('click', '.megaoptim-remove-backups', function (e) {
        if (confirm(MegaOptim.words.backup_delete_confirm)) {
            var status = false;
            var $self = $(this);
            var spinner = '<span class="megaoptim-spinner"></span>';
            if (!$self.is(':disabled') || !$self.hasClass('disabled')) {
                var action = 'megaoptim_empty_backup_dir';
                var context = $(this).data('context');
                var url = MegaOptim.ajax_url + '?action=' + action + '&nonce=' + MegaOptim.nonce_default;
                $.ajax({
                    url: url,
                    type: "POST",
                    beforeSend: function () {
                        var text = $self.text();
                        var newtext = spinner + text;
                        $self.html(newtext);
                    },
                    data: {context: context},
                    success: function (response) {
                        if (response.success) {
                            status = true;
                            $self.html(MegaOptim.words.clean);
                            $self.prop('disabled', true);
                        } else {
                            alert(response.data);
                        }
                    },
                    complete: function () {
                        if ($self.text() !== MegaOptim.words.clean) {
                            var html = $self.html();
                            html.replace(spinner, '');
                        }
                    }
                });
            }
        }
    });
})(jQuery);


/**
 * Media library
 */
(function ($) {
    function setIntervalImmediately(func, interval) {
        func();
        return setInterval(func, interval);
    }

    function getJObjectByID(source, id) {
        for (var i = 0; i < source.length; i++) {
            if (parseInt(source[i].id) === id) {
                return source[i];
            }
        }
        return false;
    }

    var action = "megaoptim_ticker_upload";
    var url = MegaOptim.ajax_url + '?action=' + action + '&nonce=' + MegaOptim.nonce_default;

    if (MegaOptim.ticker.enabled) {

        if(MegaOptim.ticker.context === 'upload' || MegaOptim.ticker.context === 'attachment') {
            $.megaoptim_upload_ticker = setIntervalImmediately(function () {
                var $attachments = $('.megaoptim_media_attachment');
                var processing_items = [];
                $attachments.each(function (i, self) {
                    var $optimizing = $(self).find('.megaoptim-optimize');
                    if ($optimizing.length > 0) {
                        $optimizing.each(function (i, att) {
                            processing_items.push($(att).data('attachmentid'));
                        });
                    }
                });
                if (processing_items.length > 0) {
                    $.ajax({
                        url: url,
                        type: "POST",
                        data: {processing: processing_items, context: MegaOptim.context.medialibrary},
                        success: function (response) {
                            if (response.success) {
                                for (var i in processing_items) {
                                    var id = processing_items[i];
                                    var current = getJObjectByID(response.data, id);
                                    if (false !== current) {
                                        if (!current.is_locked && !current.is_optimized) {
                                            // Do nothing
                                        } else {
                                            if(MegaOptim.ticker.context === 'attachment') {
                                                var selector = 'div.megaoptim_media_attachment';
                                            } else {
                                                var selector = '#post-' + current.id + ' td.megaoptim_media_attachment';
                                            }
                                            
                                            $(selector).html(current.html);
                                        }
                                    }
                                }
                            }
                        }
                    });
                }
            }, MegaOptim.ticker.interval);
        } else if( MegaOptim.ticker.context === 'nggallery-manage-images' ) {
            $.megaoptim_upload_ticker = setIntervalImmediately(function () {
                var $attachments = $('#the-list tr');
                var processing_items = [];
                $attachments.each(function (i, self) {
                    var $optimizing = $(self).find('.megaoptim-optimize');
                    if ($optimizing.length > 0) {
                        processing_items.push($optimizing.data('attachmentid'));
                    }
                });
                if (processing_items.length > 0) {
                    $.ajax({
                        url: url,
                        type: "POST",
                        data: {processing: processing_items, context: MegaOptim.context.nextgen},
                        success: function (response) {
                            if (response.success) {
                                for (var i in processing_items) {
                                    var id = processing_items[i];
                                    var current = getJObjectByID(response.data, id);
                                    if (false !== current) {
                                        if (!current.is_locked && !current.is_optimized) {
                                            // Do nothing
                                        } else {
                                            var selector = '#megaoptim-galleryimage-id-' + current.id;
                                            $(selector).html(current.html);
                                        }
                                    }
                                }
                            }
                        }
                    });
                }
            }, MegaOptim.ticker.interval);
        }
    }
})(jQuery);


/**
 * Optimize buttons
 */
(function ($) {
    $(document).on('click', '.megaoptim-optimize-run', function (e) {
        e.preventDefault();
        var $self = $(this);
        if ($self.is(':disabled') || $self.hasClass('disabled')) {
            return;
        }

        var spinner = '<span class="megaoptim-spinner"></span>';

        var optimize_single_attachment = function($self, url) {
            var context = $self.closest('.megaoptim-optimize').data('context');
            var attachment_id = $self.closest('.megaoptim-optimize').data('attachmentid');
            var compression = $self.data('compression');
            $.ajax({
                url: url,
                type: "POST",
                data: {attachmentid: attachment_id, context: context, compression: compression},
                success: function (response) {
                    if (MegaOptim.ticker.enabled) {
                        // No need anything, ticker handles it all.
                    } else {
                        // TODO: Implement some ideas for this for later.
                    }
                }
            });
        }
        var attachment_id = $self.closest('.megaoptim-optimize').data('attachmentid');
        if (attachment_id) {
            var url_optimize = MegaOptim.ajax_url + '?action=megaoptim_optimize_single_attachment&nonce=' + MegaOptim.nonce_default;
            var url_tokens = MegaOptim.ajax_url + '?action=megaoptim_get_profile&nonce=' + MegaOptim.nonce_default;
            var $dropdown = $self.closest('.megaoptim-dropdown');
            var $button = $dropdown.find('label');
            // Check tokens
            $.ajax({
                url: url_tokens,
                type: "POST",
                beforeSend: function () {
                    $button.click();
                    $self.addClass('megaoptim-optimizing');
                    $button.removeClass('button-primary').addClass('button disabled').html(spinner + MegaOptim.words.optimizing);
                },
                success: function(response) {
                    // If all good, proceed.
                    if(response.success) {
                        if(response.data.tokens > 0) {
                            optimize_single_attachment($self, url_optimize);
                        } else {
                            alert(MegaOptim.words.no_tokens);
                            $button.addClass('button-primary').removeClass('button').removeClass('disabled').html(MegaOptim.words.optimize);
                            $self.removeClass('megaoptim-optimizing');
                        }
                    } else {
                        $button.addClass('button-primary').removeClass('button').removeClass('disabled').html(MegaOptim.words.optimize);
                        $self.removeClass('megaoptim-optimizing');
                        alert(MegaOptim.words.profile_error);
                    }
                }
            })
        }
    })
})(jQuery);


(function ($) {
    $(document).on('click', '.megaoptim-optimize-restore', function () {
        var context = $(this).data('context');
        var attachment_id = $(this).data('attachmentid');
        var $self = $(this);
        if ($self.is(':disabled') || $self.hasClass('disabled')) {
            return;
        }
        var $wrapper;
        var is_ngg = context === MegaOptim.context.nextgen;
        if (is_ngg) {
            $wrapper = $('#megaoptim-galleryimage-id-' + attachment_id);
        } else {
            $wrapper = $self.closest('.megaoptim_media_attachment');
        }
        if (attachment_id) {
            var action = 'megaoptim_restore_single_attachment';
            var url = MegaOptim.ajax_url + '?action=' + action + '&nonce=' + MegaOptim.nonce_default;
            $.ajax({
                url: url,
                type: "POST",
                beforeSend: function () {
                    $self.addClass('disabled').html(MegaOptim.spinner + MegaOptim.words.working);
                },
                data: {attachmentid: attachment_id, context: context},
                success: function (response) {
                    if (response.success) {
                        $wrapper.html(response.data);
                    } else {
                        $wrapper.html('Error restoring attachment!');
                    }
                }
            });
        }
    })
})(jQuery);


(function ($) {
    $(document).on('click', '#setapikey', function (e) {
        e.preventDefault();
        var $wrapper = $(this).closest($(this).data('wrapper'));
        var key = $('#apikey').val();

        console.log(key);

        if (key && key !== '') {
            $.ajax({
                url: MegaOptim.ajax_url + '?action=megaoptim_set_apikey&nonce=' + MegaOptim.nonce_default,
                tyoe: 'POST',
                beforeSend: function () {
                    $wrapper.LoadingOverlay('show', {'size': 20});
                },
                data: {apikey: key},
                success: function (response) {
                    if (!response.success) {
                        alert(response.data.error);
                    } else {
                        window.location.reload();
                    }
                },
                complete: function () {
                    $wrapper.LoadingOverlay('hide');
                }
            });
        } else {
            alert("Please enter valid api key.");
        }
    });
})(jQuery);