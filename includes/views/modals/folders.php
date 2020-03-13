<div class="remodal megaoptim-panel megaoptim-modal" id="megaoptim-dir-select" style="display: none;" data-remodal-id="megaoptim-dir-select" data-remodal-options="hashTracking: false;">
    <div class="megaoptim-panel-inner">
        <div class="megaoptim-panel-header">
            <div class="megaoptim-panel-header-inner">
                <h3 class="megaoptim-panel-title"><?php _e( 'Select Folder', 'megaoptim-image-optimizer' ); ?></h3>
                <p class="megaoptim-panel-desc">
					<?php _e( 'Click on the check icon to select a folder you want to optimize! The icon will be green if you selected a folder correctly.', 'megaoptim-image-optimizer' ); ?>
                </p>
                <p class="megaoptim-panel-desc" style="margin-top: 15px;">
                    <input type="checkbox" id="recursive" name="recursive" value="1" checked/> <?php _e('Scan recursively', 'megaoptim-image-optimizer'); ?>
                </p>
            </div>
        </div>
        <div class="megaoptim-panel-body">
            <div class="megaoptim-panel-body-inner">
                <div class="megaoptimdirtree"></div>
            </div>
        </div>
        <div class="megaoptim-panel-footer">
            <div class="megaoptim-panel-footer-inner">
                <button data-remodal-action="cancel" class="megaoptim-btn megaoptim-cancel"><?php _e( 'Cancel', 'megaoptim-image-optimizer' ); ?></button>
                <button id="megaoptim-dir-select-action" class="megaoptim-btn megaoptim-ok"><?php _e( 'OK', 'megaoptim-image-optimizer' ); ?></button>
            </div>
        </div>
    </div>
</div>