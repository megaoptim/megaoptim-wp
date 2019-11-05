/**
 * MegaOptim globals
 */
(function ($) {
    $.megaoptim = {};
    /**
     * MegaOptim Loader
     * @param params
     */
    $.megaoptim.loader = function (params) {
        this.start = function () {
            self.modal = $(self.element);
            self.instance = self.modal.remodal(self.modal_opts);
            self.modal.find('.megaoptim-panel-title').text(self.params.title);
            self.modal.find('.megaoptim-panel-body-inner').html('<p>' + self.params.description + '</p>');
            self.instance.open();
        };
        this.stop = function () {
            setTimeout(function () {
                self.modal = $(self.element);
                self.instance = self.modal.remodal(self.modal_opts);
                self.modal.find('.megaoptim-panel-title').text('');
                self.modal.find('.megaoptim-panel-body-inner').text('');
                //while(self.instance.getState() !== 'closed') {
                self.instance.close();
                //}
                //self.instance.destroy();
            }, 1000);

        };
        this.element = '#megaoptim-loader';
        this.modal = null;
        this.instance = null;
        this.modal_opts = {
            hashTracking: false,
            closeOnOutsideClick: false,
            closeOnEscape: false
        };
        this.params = params;
        var self = this;
    };
    // .. other
})(jQuery);

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
})(jQuery);

/**
 * Register for API key
 */
(function ($) {
    $.megaoptim.register = {};
    $.megaoptim.register.process_step = function (step, form_data, success) {
        $.ajax({
            url: MegaOptim.ajax_url + '?action=megaoptim_api_register&step='+step+'&nonce=' + MegaOptim.nonce_default,
            type: "POST",
            data: form_data,
            cache: false,
            beforeSend: function () {
                $('.megaoptim-panel-body').LoadingOverlay('show');
            },
            success: success,
            complete: function () {
                $('.megaoptim-panel-body').LoadingOverlay('hide');
            }
        });
    };
    $("form#megaoptim-register-form").submit(function (e) {
        var $self = $(this);
        var form_data = $self.serialize();
        var form_step = $self.data('step');
        $.megaoptim.register.process_step(form_step, form_data, function (response) {
            switch (form_step) {
                case 1:
                    if (!response.success) {
                        $('.mgo-form-group').each(function () {
                            $(this).find('.mgo-field-error').detach().remove();
                            $(this).removeClass('mgo-error');
                        });
                        for (var i in  response.data.errors) {
                            var $item = $('#mgo-' + i);
                            $item.addClass('mgo-error');
                            $item.find('.mgo-field-error').detach().remove();
                            $item.append('<span class="mgo-field-error">' +  response.data.errors[i][0] + '</span>');
                        }
                    } else {
                        $('.mgo-form-group').each(function () {
                            $(this).find('.mgo-field-error').detach().remove();
                            $(this).removeClass('mgo-error');
                        });
                        $self.data('step', parseInt(form_step) + 1);
                        $('#megaoptim-register-form-step1').hide();
                        $('#megaoptim-register-form-step2').show();
                    }
                    break;
                case 2:
                    if(response.success) {
                        $('#megaoptim-register-form-step2').hide();
                        $('#megaoptim-register-form-step3').show();
                        $self.closest('.remodal').find('.megaoptim-ok').detach().remove();
                    } else {
                        var $field = $('#mgo_api_key');
                        $field.addClass('mgo-error');
                        $field.find('.mgo-field-error').detach().remove();
                        $field.append('<span class="mgo-field-error">' +  response.data + '</span>');
                    }
                    break;
            }
        });
        return false;
    });
})(jQuery);

/**
 * MegaOptim
 * API Key form used on the bulk optimizer page.
 */
(function ($) {
    $(document).on('click', '#setapikey', function (e) {
        e.preventDefault();
        var $wrapper = $(this).closest($(this).data('wrapper'));
        var key = $('#apikey').val();

        console.log(key);

        if (key && key !== '') {
            $.ajax({
                url: MegaOptim.endpoints.setapikey,
                tyoe: 'POST',
                beforeSend: function () {
                    $wrapper.LoadingOverlay('show', {'size': 20});
                },
                data: {apikey: key, nonce : MegaOptim.nonce_settings},
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

/**
 * MegaOptim
 * API Key Modal used on the instructions page.
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
                        .addClass('notice success');
                    $fields.hide();
                    $actions.hide();
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
        if (confirm(MegaOptim.strings.backup_delete_confirm)) {
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
                            $self.html(MegaOptim.strings.clean);
                            $self.prop('disabled', true);
                        } else {
                            alert(response.data);
                        }
                    },
                    complete: function () {
                        if ($self.text() !== MegaOptim.strings.clean) {
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

        if (MegaOptim.ticker.context === 'upload' || MegaOptim.ticker.context === 'attachment') {
            var tries = 0;
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
                                    //console.log(current);
                                    if (false !== current) {
                                        if (!current.is_locked && !current.is_optimized) {
                                            // Do nothing
                                        } else {
                                            if (MegaOptim.ticker.context === 'attachment') {
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
        } else if (MegaOptim.ticker.context === 'nggallery-manage-images') {
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

        var optimize_single_attachment = function ($self) {
            var url = MegaOptim.ajax_url + '?action=megaoptim_optimize_single_attachment&nonce=' + MegaOptim.nonce_default;
            var context = $self.closest('.megaoptim-optimize').data('context');
            var attachment_id = $self.closest('.megaoptim-optimize').data('attachmentid');
            var compression = $self.data('compression');
            var $dropdown = $self.closest('.megaoptim-dropdown');
            var $button = $dropdown.find('label');
            var $main = $self.closest('.megaoptim-attachment-buttons');
            $main.data('compression', compression);
            $.ajax({
                url: url,
                type: "POST",
                data: {attachmentid: attachment_id, context: context, compression: compression},
                beforeSend: function () {
                    $button.click();
                    $self.addClass('megaoptim-optimizing');
                    $button.removeClass('button-primary').addClass('button disabled').html(spinner + ' ' + MegaOptim.strings.optimizing);
                },
                success: function (response) {
                    if(!response.success) {
                        $button.addClass('button-primary').removeClass('button').removeClass('disabled').html(MegaOptim.strings.optimize);
                        $self.removeClass('megaoptim-optimizing');
                        alert(response.data.message);
                    }
                },
                error:function () {
                    $button.addClass('button-primary').removeClass('button').removeClass('disabled').html(MegaOptim.strings.optimize);
                    $self.removeClass('megaoptim-optimizing');
                    alert('HTTP Server Error. Please check error logs and contact your host or MegaOptim support.')
                }
            });
        };

        optimize_single_attachment($self);
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
                    $self.addClass('disabled').html(MegaOptim.spinner + ' ' + MegaOptim.strings.working);
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


// Admin settings conditional checkbox
(function ($) {
    $('.megaoptim-checkbox-conditional').on('change', function () {
        var target= $(this).data('target');
        var clearinputs = $(this).data('targetclearvalues') ? 1 : 0;
        var state = $(this).data('targetstate');
        if(!state) {
            state = 'disabled';
        }
        if(target) {
            var $self = $(this);
            var $target = $(target);
            if($target.length > 0) {
                if($self.is(':checked')) {
                    if(state === 'hide') {
                        $target.hide();
                    } else {
                        $target.prop(state, false);
                    }
                    if(clearinputs) {
                        $target.val('');
                        $target.find('input').val('');
                    }
                } else {
                    if(state === 'hide') {
                        $target.show();
                    } else {
                        $target.prop(state, true);
                    }
                }
            }
        }
    });
})(jQuery);

// Show/Hide Detailed Stats
(function($){
    $(document).on('click', '.megaoptim-see-stats', function(e){
        e.preventDefault();
        var $self = $(this);
        var $wrap = $self.closest('.megaoptim-attachment-buttons');
        var $tbl  = $wrap.find('.megaoptim-attachment-stats');

        if($tbl.is(':hidden')) {
            $tbl.show();
            $self.text(MegaOptim.strings.hide_thumbnail_info);
        } else {
            $tbl.hide();
            $self.text(MegaOptim.strings.show_thumbnail_info);
        }
    })
})(jQuery);

// WebP management
(function($){
    $('#webp_create').on('change', function(){
        var $additional = $('#webp_create_additional');
        if($(this).is(':checked')) {
            $additional.show();
        } else {
            $additional.hide();
        }
    });
    $('#webp_delivery_method').on('change', function(){
        var value = $(this).val();
        var $explanationWrap = $('#megaoptim-webp_delivery_method-'+value);
        $('.megaoptim-explanation-wrapper').hide();
        if($explanationWrap.length > 0) {
            $explanationWrap.show();
        }
    })
})(jQuery);