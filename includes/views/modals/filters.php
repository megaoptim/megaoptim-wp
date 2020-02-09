<div class="remodal megaoptim-panel megaoptim-modal" id="megaoptim-media-filters" style="display: none;" data-remodal-id="megaoptim-media-filters" data-remodal-options="hashTracking: false; closeOnOutsideClick: false;">
    <div class="megaoptim-panel-inner">
        <div class="megaoptim-panel-header">
            <div class="megaoptim-panel-header-inner">
                <h1 class="megaoptim-panel-title"><?php _e( 'Scan Filters', 'megaoptim-image-optimizer' ); ?></h1>
                <p class="megaoptim-panel-desc">
					<?php _e( 'Scan for unoptimized images by specifying custom filters', 'megaoptim-image-optimizer' ); ?>
                </p>
            </div>
        </div>
        <div class="megaoptim-panel-body">
            <div class="megaoptim-panel-body-inner">
                <form id="megaoptim-filters-form">
                    <div class="mgo-form-group">
                        <label for="mgo-date-from"><?php _e( 'Upload date from', 'megaoptim-image-optimizer' ); ?></label>
                        <input type="text" id="mgo-date-from" placeholder="eg: 2019-07-20" class="mgo-filter mgo-datepicker" data-format="yy-mm-dd" data-key='date_from' data-label="<?php _e('Date from', 'megaoptim-image-optimizer'); ?>">
                    </div>
                    <div class="mgo-form-group">
                        <label for="mgo-date-to"><?php _e( 'Upload date to (can be blank)', 'megaoptim-image-optimizer' ); ?></label>
                        <input type="text" id="mgo-date-to" placeholder="eg: 2019-07-30" class="mgo-filter mgo-datepicker" data-format="yy-mm-dd"  data-key='date_to' data-label="<?php _e('Date to', 'megaoptim-image-optimizer'); ?>">
                    </div>
                </form>
            </div>
        </div>
        <div class="megaoptim-panel-footer">
            <div class="megaoptim-panel-footer-inner">
                <button data-remodal-action="cancel" class="megaoptim-btn megaoptim-cancel"><?php _e( 'Cancel', 'megaoptim-image-optimizer' ); ?></button>
                <button data-remodal-action="confirm" class="megaoptim-btn megaoptim-ok"><?php _e( 'OK', 'megaoptim-image-optimizer' ); ?></button>
            </div>
        </div>
    </div>
</div>
