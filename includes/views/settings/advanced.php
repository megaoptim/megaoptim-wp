<?php
/********************************************************************
 * Copyright (C) 2017 Darko Gjorgjijoski (http://darkog.com)
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 **********************************************************************/

/* @var integer $medialibrary_backup_dir_size */
/* @var integer $nextgen_backup_dir_size */
/* @var integer $localfiles_backup_dir_size */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access is not allowed.' );
}
$settings = MGO_Settings::instance()->get();
?>
<div class="megaoptim-postbox">
    <form class="content-wrapper" method="POST" id="megaoptim_save_form" data-action="megaoptim_save_advanced_settings">
        <div class="megaoptim-form-header">
            <div class="megaoptim-full">
                <h3 class="content-header"><?php _e( 'Advanced Settings', 'megaoptim' ); ?></h3>
            </div>
        </div>
        <div class="megaoptim-field-group" id="save_status"></div>

        <div class="megaoptim-field-group">
            <div class="megaoptim-field-group-inner">
                <div class="megaoptim-label-wrap">
                    <label class="megaoptim-option-label"><?php _e( 'WebP Management', 'megaoptim' ); ?></label>
                    <p class="megaoptim-small-paragraph">Configure next-gen image formats settings</p>
                </div>
                <div class="megaoptim-field-wrap">
                    <div class="megaoptim-checkbox">
                        <input type="checkbox"
							<?php checked( $settings[ MGO_Settings::WEBP_CREATE_IMAGES ], 1 ); ?>
                               id="<?php echo MGO_Settings::WEBP_CREATE_IMAGES; ?>" name="<?php echo MGO_Settings::WEBP_CREATE_IMAGES; ?>" value="1"/>
                        <label for="<?php echo MGO_Settings::WEBP_CREATE_IMAGES; ?>">
                            Create optimized WebP images <strong>for free</strong>
                        </label>
                        <p class="megaoptim-field-desc">
		                    <?php _e( 'We can create WebP versions of your images while optimizing only if resulting WebP version is smaller than the original image. Below you have option to deliver the created WebP on the front-end instead of the jpg/png formats if available.', 'megaoptim' ); ?>
                        </p>
                    </div>
                    <div class="megaoptim-checkbox">
                        <div class="megaoptim-full-wrap megaoptim-mt-5">
                            <label class="megaoptim-option-label" for="<?php echo MGO_Settings::WEBP_DELIVERY_METHOD; ?>">WebP Front-end Delivery Method</label>
                        </div>
                        <div class="megaoptim-full-wrap">
                            <select name="<?php echo MGO_Settings::WEBP_DELIVERY_METHOD; ?>" id="<?php echo MGO_Settings::WEBP_DELIVERY_METHOD; ?>">
                                <option value="none">No front-end delivery</option>
                                <option value="picture">Using &lt;PICTURE&gt; TAG</option>
                                <option value="rewritting">Using server-side rewritting.</option>
                            </select>
                        </div>
                        <div class="megaoptim-full-wrap">
                            <div class="megaoptim-checkbox-explanation">
                                <p id="megaoptim-<?php echo MGO_Settings::WEBP_DELIVERY_METHOD; ?>-none" class="megaoptim-option-explanation">
			                        <?php _e( 'Skip delivering WebP on front end for now.', 'megaoptim' ); ?>
                                </p>
                            </div>
                            <div class="megaoptim-checkbox-explanation">
                                <p id="megaoptim-<?php echo MGO_Settings::WEBP_DELIVERY_METHOD; ?>-picture" class="megaoptim-option-explanation">
		                            <?php _e( 'Using the &lt;PICTURE&gt; method replaces &lt;img&gt; tags with &lt;PICTURE&gt; tags. While this is recommended method of delivery. Please note that there can be display inconssitency after switching if your site relies on styling the &lt;img&gt; tags. You can switch to other option anytime.', 'megaoptim' ); ?>
                                </p>
                                <div class="megaoptim-full-wrap">
                                    <input type="radio"
		                                <?php checked( $settings[ MGO_Settings::WEBP_TARGET_TO_REPLACE ], 1 ); ?>
                                           id="<?php echo MGO_Settings::WEBP_TARGET_TO_REPLACE; ?>" name="<?php echo MGO_Settings::WEBP_TARGET_TO_REPLACE; ?>" value="1"/>
                                    <label for="<?php echo MGO_Settings::WEBP_TARGET_TO_REPLACE; ?>">
                                        Create optimized WebP images <strong>for free</strong>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>


        <div class="megaoptim-field-group">
            <div class="megaoptim-field-group-inner">
                <div class="megaoptim-label-wrap">
                    <label class="megaoptim-option-label"><?php _e( 'Backup Management', 'megaoptim' ); ?></label>
                    <p class="megaoptim-small-paragraph">Select which libaries to backup</p>
                </div>
                <div class="megaoptim-field-wrap">
                    <p class="megaoptim-field-desc">
						<?php _e( 'Backups are useful if you want to restore the original version of the optimized image or reoptimize it using other method such as Lossless or Lossy and we recommend to keep this option On.', 'megaoptim' ); ?>
                    </p>
                    <div class="megaoptim-checkbox">
                        <input type="checkbox"
							<?php checked( $settings[ MGO_Settings::BACKUP_MEDIA_LIBRARY_ATTACHMENTS ], 1 ); ?>
                               id="<?php echo MGO_Settings::BACKUP_MEDIA_LIBRARY_ATTACHMENTS; ?>" name="<?php echo MGO_Settings::BACKUP_MEDIA_LIBRARY_ATTACHMENTS; ?>" value="1"/>
                        <label for="<?php echo MGO_Settings::BACKUP_MEDIA_LIBRARY_ATTACHMENTS; ?>">Media Library Backup</label>
                    </div>
                    <div class="megaoptim-checkbox">
                        <input type="checkbox" <?php checked( $settings[ MGO_Settings::BACKUP_NEXTGEN_ATTACHMENTS ], 1 ); ?> id="<?php echo MGO_Settings::BACKUP_NEXTGEN_ATTACHMENTS; ?>" name="<?php echo MGO_Settings::BACKUP_NEXTGEN_ATTACHMENTS; ?>" value="1"/> Local Folders Backup
                    </div>
                    <div class="megaoptim-checkbox">
                        <input type="checkbox" <?php checked( $settings[ MGO_Settings::BACKUP_FOLDER_FILES ], 1 ); ?> id="<?php echo MGO_Settings::BACKUP_FOLDER_FILES; ?>" name="<?php echo MGO_Settings::BACKUP_FOLDER_FILES; ?>" value="1"/> NextGen Backup
                    </div>
                    <p style="margin-top: 10px; margin-bottom: 0;">
                        <strong><?php _e( 'Existing Backups', 'megaoptim' ); ?></strong>
                    </p>
                    <p class="megaoptim-field-desc"><?php _e( sprintf( 'Here you can clean up the backup folders, please note that if you clean up the folders you will not be able to restore the images or reoptimize them using different technique. %s', '<strong>' . __( 'Only do this if you know what you are doing.', 'megaoptim' ) . '</strong>' ), 'megaoptim' ); ?></p>
                    <div class="megaoptim-row">

                        <div class="megaoptim-col-4">
                            <div class="megaoptim-subrow">
                                <p><strong><?php _e( 'Media Library Backups', 'megaoptim' ); ?></strong></p>
                                <p>
                                    <button <?php disabled( true, $medialibrary_backup_dir_size <= 0 ); ?> type="button" data-context="<?php echo MGO_MediaAttachment::TYPE; ?>" class="button megaoptim-button-small megaoptim-remove-backups"><?php _e( 'Clean', 'megaoptim' ); ?>&nbsp;<?php echo $medialibrary_backup_dir_size > 0 ? megaoptim_human_file_size( $medialibrary_backup_dir_size ) : ''; ?></button>
                                </p>
                            </div>
                        </div>
						<?php if ( class_exists( 'MGO_NextGenAttachment' ) ): ?>
                            <div class="megaoptim-col-4">
                                <div class="megaoptim-subrow">
                                    <p><strong>Nextgen Libraries Backups</strong></p>
                                    <p>
                                        <button <?php disabled( true, $nextgen_backup_dir_size <= 0 ); ?> type="button" data-context="<?php echo MGO_NextGenAttachment::TYPE; ?>" class="button megaoptim-button-small megaoptim-remove-backups"><?php _e( 'Clean', 'megaoptim' ); ?>&nbsp;<?php echo $nextgen_backup_dir_size > 0 ? megaoptim_human_file_size( $nextgen_backup_dir_size ) : ''; ?></button>
                                    </p>
                                </div>
                            </div>
						<?php endif; ?>
                        <div class="megaoptim-col-4">
                            <div class="megaoptim-subrow">
                                <p><strong>Custom Files Backups</strong></p>
                                <p>
                                    <button <?php disabled( true, $localfiles_backup_dir_size <= 0 ); ?> type="button" data-context="<?php echo MGO_LocalFileAttachment::TYPE; ?>" class="button megaoptim-button-small megaoptim-remove-backups"><?php _e( 'Clean', 'megaoptim' ); ?>&nbsp;<?php echo $localfiles_backup_dir_size > 0 ? megaoptim_human_file_size( $localfiles_backup_dir_size ) : ''; ?></button>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="megaoptim-field-group">
            <div class="megaoptim-field-group-inner">
                <div class="megaoptim-label-wrap">
                    <label class="megaoptim-option-label"><?php _e( 'Image thumbnails/sizes to be optimized', 'megaoptim' ); ?></label>
                </div>
                <div class="megaoptim-field-wrap">
                    <p class="megaoptim-field-desc">
						<?php _e( 'Depending of the theme and plugin you are using WordPress generates multiple thumbnails for the images you upload through the WordPress Media Library. Those thumbnails are used in different places in the theme/pages. For example if you print multiple posts in one page and the post image column is small it might not be optimal to use the full version but use the custom defined thumbnail size for it.', 'megaoptim' ); ?>

                    </p>
                    <p class="megaoptim-field-desc">
						<?php _e( 'MegaOptim gives you option to choose which sizes to be optimized by the optimizer from normal to retina ones if available.', 'megaoptim' ); ?>
                    </p>
                    <div class="megaoptim-row">
                        <div class="megaoptim-col-2">
                            <div class="checkboxes">
                                <p class="megaoptim-margin-top-0"><strong>Normal thumbnails</strong></p>
								<?php
								$image_sizes = MGO_MediaLibrary::get_image_sizes();
								foreach ( $image_sizes as $key => $image_size ) { ?>
                                    <p class="megaoptim-checkbox-row">
										<?php $tooltip = $image_size['width'] . 'x' . $image_size['height']; ?>
                                        <label title="<?Php echo $tooltip; ?>" class="checkbox" for="cb_<?php echo $key; ?>">
                                            <input
												<?php echo is_array( $settings[ MGO_Settings::IMAGE_SIZES ] )
												           && in_array( $key, $settings[ MGO_Settings::IMAGE_SIZES ] ) ? 'checked' : ''; ?>
                                                    type="checkbox" name="<?php echo MGO_Settings::IMAGE_SIZES; ?>[]" value="<?php echo $key; ?>" id="cb_<?php echo $key; ?>">
											<?php echo $key . ' (' . $tooltip . ')'; ?>
                                        </label>
                                    </p>
								<?php } ?>
                            </div>
                        </div>
                        <div class="megaoptim-col-2">
                            <div class="checkboxes">
                                <p class="megaoptim-margin-top-0"><strong>Retina thumbnails (When available)</strong>
                                </p>
								<?php
								$image_sizes = MGO_MediaLibrary::get_image_sizes();
								foreach ( $image_sizes as $key => $image_size ) { ?>
                                    <p class="megaoptim-checkbox-row">
										<?php
										$tooltip = ( $image_size['width'] * 2 ) . 'x' . ( $image_size['height'] * 2 );
										?>
                                        <label title="<?Php echo $tooltip; ?>" class="checkbox" for="cb_retina_<?php echo $key; ?>">
                                            <input
												<?php echo is_array( $settings[ MGO_Settings::RETINA_IMAGE_SIZES ] )
												           && in_array( $key, $settings[ MGO_Settings::RETINA_IMAGE_SIZES ] ) ? 'checked' : ''; ?>
                                                    type="checkbox" name="<?php echo MGO_Settings::RETINA_IMAGE_SIZES; ?>[]" value="<?php echo $key; ?>" id="cb_retina_<?php echo $key; ?>">
											<?php echo $key . ' (' . $tooltip . ')'; ?>
                                        </label>
                                    </p>
								<?php } ?>
                            </div>
                        </div>
                    </div>
                    <p class="warning">
                        <strong><?php _e( 'Remember:', 'megaoptim' ); ?></strong> <?php _e( 'Each image size of those will cost you one optimization token meaning this option will affect your optimization token usage.', 'megaoptim' ); ?>
                    </p>
                </div>
            </div>
        </div>


        <div class="megaoptim-field-group">
            <div class="megaoptim-field-group-inner">
                <div class="megaoptim-label-wrap">
                    <label class="megaoptim-option-label"><?php _e( 'CloudFlare Credentials', 'megaoptim' ); ?></label>
                </div>
                <div class="megaoptim-field-wrap">
                    <input type="text" name="<?php echo MGO_Settings::CLOUDFLARE_EMAIL; ?>" id="<?php echo MGO_Settings::CLOUDFLARE_EMAIL; ?>" value="<?php echo $settings[ MGO_Settings::CLOUDFLARE_EMAIL ]; ?>" class="option-control form-control" placeholder="Email">
                    <br/>
                    <input type="text" name="<?php echo MGO_Settings::CLOUDFLARE_API_KEY; ?>" id="<?php echo MGO_Settings::CLOUDFLARE_API_KEY; ?>" value="<?php echo $settings[ MGO_Settings::CLOUDFLARE_API_KEY ]; ?>" class="option-control form-control" placeholder="API Key">
                    <br/>
                    <input type="text" name="<?php echo MGO_Settings::CLOUDFLARE_ZONE; ?>" id="<?php echo MGO_Settings::CLOUDFLARE_ZONE; ?>" value="<?php echo $settings[ MGO_Settings::CLOUDFLARE_ZONE ]; ?>" class="option-control form-control" placeholder="Zone ID">
                    <p class="megaoptim-field-desc">
						<?php _e( 'If you are running using CloudFlare APIs and or any WordPress CloudFlare plugins enter your details to be able to purge the cached urls of the files that MegaOptim optimizes.', 'megaoptim' ); ?>
                    </p>
                </div>
            </div>
        </div>

        <div class="megaoptim-form-actions">
            <div class="options-save">
                <button class="button button-primary button-large" type="submit" id="options-save">
					<?php _e( 'Save Settings', 'megaoptim' ); ?>
                </button>
            </div>
        </div>
    </form>
</div>