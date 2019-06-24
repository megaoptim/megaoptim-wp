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
$settings = MGO_Settings::instance();

// Webp
$webp_create_images     = $settings->get( MGO_Settings::WEBP_CREATE_IMAGES, 0 );
$webp_delivery_method   = $settings->get( MGO_Settings::WEBP_DELIVERY_METHOD, 'none' );
$webp_target_to_replace = $settings->get( MGO_Settings::WEBP_TARGET_TO_REPLACE, 'default' );
$webp_picturefill       = $settings->get( MGO_Settings::WEBP_PICTUREFILL, 1 );

// Backups
$backup_media_library_attachments = $settings->get( MGO_Settings::BACKUP_MEDIA_LIBRARY_ATTACHMENTS, 1 );
$backup_nextgen_attachments       = $settings->get( MGO_Settings::BACKUP_NEXTGEN_ATTACHMENTS, 1 );
$backup_file_attachments          = $settings->get( MGO_Settings::BACKUP_FOLDER_FILES );

// Cloudflare
$cf_zone    = $settings->get( MGO_Settings::CLOUDFLARE_ZONE );
$cf_api_key = $settings->get( MGO_Settings::CLOUDFLARE_API_KEY );
$cf_email   = $settings->get( MGO_Settings::CLOUDFLARE_EMAIL );
?>
<div class="megaoptim-postbox">
    <form class="content-wrapper" method="POST" id="megaoptim_save_form" data-action="megaoptim_save_advanced_settings">
        <div class="megaoptim-form-header">
            <div class="megaoptim-full">
                <h3 class="content-header"><?php _e( 'Advanced Settings', 'megaoptim-image-optimizer' ); ?></h3>
            </div>
        </div>
        <div class="megaoptim-field-group" id="save_status"></div>

        <div class="megaoptim-field-group">
            <div class="megaoptim-field-group-inner">
                <div class="megaoptim-label-wrap">
                    <label class="megaoptim-option-label"><?php _e( 'WebP Management', 'megaoptim-image-optimizer' ); ?></label>
                    <p class="megaoptim-small-paragraph"><?php _e( 'Configure next-gen image formats settings', 'megaoptim-image-optimizer' ); ?></p>
                </div>
                <div class="megaoptim-field-wrap">
                    <div class="megaoptim-checkbox">
                        <input type="checkbox" <?php checked( $webp_create_images, 1 ); ?>
                               id="<?php echo MGO_Settings::WEBP_CREATE_IMAGES; ?>"
                               name="<?php echo MGO_Settings::WEBP_CREATE_IMAGES; ?>" value="1"/>
                        <label for="<?php echo MGO_Settings::WEBP_CREATE_IMAGES; ?>"><?php echo sprintf( __( 'Create WebP Images upon optimization for %s', 'megaoptim-image-optimizer' ), '<strong>' . __( 'free', 'megaoptim-image-optimizer' ) . '</strong>' ); ?></label>
                        <p class="megaoptim-field-desc">
							<?php _e( 'If enbaled, the plugin creates WebP versions of your images upon optimizing, so, each optimized image will have optimized webp version which will be used once your page is rendered.', 'megaoptim-image-optimizer' ); ?>
                            <br/>
                        </p>
                    </div>
                    <div id="<?php echo MGO_Settings::WEBP_CREATE_IMAGES; ?>_additional" class="megaoptim-checkbox"
                         style="<?php echo $webp_create_images == 1 ? '' : 'display: none;'; ?>">
                        <div class="megaoptim-full-wrap megaoptim-mt-5">
                            <label class="megaoptim-option-label"
                                   for="<?php echo MGO_Settings::WEBP_DELIVERY_METHOD; ?>"><?php _e( 'WebP Front-end Delivery Method', 'megaoptim-image-optimizer' ); ?></label>
                        </div>
                        <div class="megaoptim-full-wrap">
                            <select name="<?php echo MGO_Settings::WEBP_DELIVERY_METHOD; ?>"
                                    id="<?php echo MGO_Settings::WEBP_DELIVERY_METHOD; ?>">
                                <option <?php selected( $webp_delivery_method, 'none' ); ?>
                                        value="none"><?php _e( 'No front-end delivery', 'megaoptim-image-optimizer' ); ?></option>
                                <option <?php selected( $webp_delivery_method, 'picture' ); ?>
                                        value="picture"><?php _e( 'Using &lt;PICTURE&gt; TAG', 'megaoptim-image-optimizer' ); ?></option>
                                <option <?php selected( $webp_delivery_method, 'rewrite' ); ?>
                                        value="rewrite"><?php _e( 'Using server-side rewritting', 'megaoptim-image-optimizer' ); ?></option>
                            </select>
                        </div>
                        <div class="megaoptim-full-wrap">
                            <!-- PICTURE Method settings-->
                            <div id="megaoptim-<?php echo MGO_Settings::WEBP_DELIVERY_METHOD; ?>-picture"
                                 class="megaoptim-explanation-wrapper"
                                 style="<?php echo $webp_delivery_method === 'picture' ? '' : 'display: none;'; ?>">
                                <div class="megaoptim-full-wrap megaoptim-mb-10">
                                    <p class="megaoptim-field-desc" style="margin-bottom: 0;">
										<?php _e( 'Using the &lt;PICTURE&gt; method replaces &lt;img&gt; tags with &lt;PICTURE&gt; tags. While this is recommended method of delivery. Please note that there can be display inconssitency after switching if your site relies on styling the &lt;img&gt; tags. You can switch to other option anytime.', 'megaoptim-image-optimizer' ); ?>
                                    </p>
                                </div>
                                <div class="megaoptim-full-wrap megaoptim-mb-10">
                                    <label class="megaoptim-option-label"
                                           for="<?php echo MGO_Settings::WEBP_DELIVERY_METHOD; ?>"><?php _e( 'Target to replace WebP', 'megaoptim-image-optimizer' ); ?></label>
                                </div>
                                <div class="megaoptim-full-wrap">
                                    <input type="radio" <?php checked( $webp_target_to_replace, 'default' ); ?>
                                           name="<?php echo MGO_Settings::WEBP_TARGET_TO_REPLACE; ?>" value="default"/>
                                    <label for="<?php echo MGO_Settings::WEBP_TARGET_TO_REPLACE; ?>">Default
                                        (<?php echo implode( ', ', megaoptim_webp_target_filters() ); ?>) filters.
                                        <strong>(Recommended)</strong></label>
                                </div>
                                <div class="megaoptim-full-wrap megaoptim-mb-10">
                                    <input type="radio" <?php checked( $webp_target_to_replace, 'global' ); ?>
                                           name="<?php echo MGO_Settings::WEBP_TARGET_TO_REPLACE; ?>" value="global"/>
                                    <label for="<?php echo MGO_Settings::WEBP_TARGET_TO_REPLACE; ?>"><?php _e( 'Global (Captures the output using output buffer and replaces the images. May not work sometimes.)', 'megaoptim-image-optimizer' ); ?></label>
                                </div>
                                <div class="megaoptim-full-wrap megaoptim-mb-10">
                                    <label class="megaoptim-option-label"
                                           for="<?php echo MGO_Settings::WEBP_DELIVERY_METHOD; ?>"><?php _e( 'Greater &lt;PICTURE&gt; tag compatibility', 'megaoptim-image-optimizer' ); ?></label>
                                </div>
                                <div class="megaoptim-full-wrap megaoptim-mb-10">
                                    <p class="megaoptim-field-desc">
										<?php _e( 'Include Picturefill.js polyfill for better &lt;PICTURE&gt; compatibility. Please note that &lt;PICTURE&gt; tag is already supported by all modern browsers and you may not need this. Only enable this if you want a support older browsers e.g Internet Explorer 11.', 'megaoptim-image-optimizer' ); ?>
                                    </p>
                                </div>
                                <div class="megaoptim-full-wrap">
                                    <select name="<?php echo MGO_Settings::WEBP_PICTUREFILL; ?>"
                                            id="<?php echo MGO_Settings::WEBP_PICTUREFILL; ?>">
                                        <option <?php selected( $webp_picturefill, '1' ); ?>
                                                value="1"><?php _e( 'Yes, Include Picturefill' ); ?></option>
                                        <option <?php selected( $webp_picturefill, '0' ); ?>
                                                value="0"><?php _e( "No, i don't need Picturefill." ); ?></option>
                                    </select>
                                </div>
                            </div>
                            <!-- REWRITE Method settings-->
                            <div id="megaoptim-<?php echo MGO_Settings::WEBP_DELIVERY_METHOD; ?>-rewrite"
                                 class="megaoptim-explanation-wrapper"
                                 style="<?php echo $settings->get( MGO_Settings::WEBP_DELIVERY_METHOD ) === 'rewrite' ? '' : 'display: none;'; ?>">
                                <p class="megaoptim-option-explanation">
									<?php if ( megaoptim_contains( strtolower( $_SERVER['SERVER_SOFTWARE'] ), 'apache' ) || megaoptim_contains( strtolower( $_SERVER['SERVER_SOFTWARE'] ), 'litespeed' ) ): ?>
										<?php
										$htaccess_path = megaoptim_get_htaccess_path();
										if ( ! file_exists( $htaccess_path ) && is_writable( dirname( $htaccess_path ) ) ) { // if .htaccess doesn't exist and the root dir is writable, we can create it probably.
											$writable = 1;
										} else if ( file_exists( $htaccess_path ) && is_writable( $htaccess_path ) ) { // if .htaccess exists and is writable, we can alter it probably.
											$writable = 1;
										} else { // If none of those, htaccess is not writable.
											$writable = 0;
										}
										?>
										<?php if ( ! $writable ): ?>
                                            <span style="color: red;"><?php _e( 'Permission denied. We tried to alter your .htaccess file but it looks like we don\'t have enough permissions to do it. We kindly ask you to contact your administrator and ask to grant you with permissions to write to .htaccess and then come back on this page and re-save the advanced settings tab. If everything is alright the .htaccess webp snippet will be added automatically upon save.', 'megaoptim-image-optimizer' ); ?></span>
										<?php else: ?>
											<?php echo sprintf( __( 'You are using %s which supports .htaccess', 'megaoptim-image-optimizer' ), '<strong>' . $_SERVER['SERVER_SOFTWARE'] . '</strong>' ); ?>
											<?php _e( 'We will try to automatically alter your .htaccess file to add support for webp rewriting. Once you hit "Save" button below, you can check your .htaccess file to see if there is block of code that starts with "# BEGIN MegaOptimIO". If the code is there, no other action required.', 'megaoptim-image-optimizer' ); ?>
										<?php endif; ?>
                                        ?>
									<?php elseif ( megaoptim_contains( strtolower( $_SERVER['SERVER_SOFTWARE'] ), 'nginx' ) ): ?>
                                        <span style="color:red"><?php echo sprintf( __( 'You are using %s which doesn\'t support .htaccess. To enable WebP for nginx you need to edit your nginx config using administrative permissions and restart the web server. Note: Please be careful and do this only if you know what you are doing.', 'megaoptim-image-optimizer' ), '<strong>' . $_SERVER['SERVER_SOFTWARE'] . '</strong>' ); ?></span>
                                        <br/>
                                        <a style="margin-top: 10px;" target="_blank"
                                           href="https://megaoptim.com/blog/how-to-serve-webp-images-in-wordpress-with-nginx"><?php _e( 'Follow the Guide', 'megaoptim-image-optimizer' ); ?></a>
									<?php else: ?>
										<?php _e( 'Looks like you are using unsupported web server. This feature will not be supported. Please choose the picture method instead.', 'megaoptim-image-optimizer' ); ?>
									<?php endif; ?>
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
                    <label class="megaoptim-option-label"><?php _e( 'Backup Management', 'megaoptim-image-optimizer' ); ?></label>
                    <p class="megaoptim-small-paragraph">Select which libaries to backup</p>
                </div>
                <div class="megaoptim-field-wrap">
                    <p class="megaoptim-field-desc">
						<?php _e( 'Backups are useful if you want to restore the original version of the optimized image or reoptimize it using other method such as Lossless or Lossy and we recommend to keep this option On.', 'megaoptim-image-optimizer' ); ?>
                    </p>
                    <div class="megaoptim-checkbox">
                        <input type="checkbox" <?php checked( $backup_media_library_attachments, 1 ); ?> id="<?php echo MGO_Settings::BACKUP_MEDIA_LIBRARY_ATTACHMENTS; ?>"  name="<?php echo MGO_Settings::BACKUP_MEDIA_LIBRARY_ATTACHMENTS; ?>" value="1"/>
                        <label for="<?php echo MGO_Settings::BACKUP_MEDIA_LIBRARY_ATTACHMENTS; ?>">Backup Media Library</label>
                    </div>
                    <div class="megaoptim-checkbox">
                        <input type="checkbox" <?php checked( $backup_nextgen_attachments, 1 ); ?> id="<?php echo MGO_Settings::BACKUP_NEXTGEN_ATTACHMENTS; ?>"  name="<?php echo MGO_Settings::BACKUP_NEXTGEN_ATTACHMENTS; ?>" value="1"/>
                        <label for="<?php echo MGO_Settings::BACKUP_NEXTGEN_ATTACHMENTS; ?>">Backup NextGen Galleries</label>
                    </div>
                    <div class="megaoptim-checkbox">
                        <input type="checkbox" <?php checked( $backup_file_attachments, 1 ); ?> id="<?php echo MGO_Settings::BACKUP_FOLDER_FILES; ?>"  name="<?php echo MGO_Settings::BACKUP_FOLDER_FILES; ?>" value="1"/>
                        <label for="<?php echo MGO_Settings::BACKUP_FOLDER_FILES; ?>">Backup Local Files</label>
                    </div>
                    <p style="margin-top: 10px; margin-bottom: 0;">
                        <strong><?php _e( 'Existing Backups', 'megaoptim-image-optimizer' ); ?></strong>
                    </p>
                    <p class="megaoptim-field-desc"><?php _e( sprintf( 'Here you can clean up the backup folders, please note that if you clean up the folders you will not be able to restore the images or reoptimize them using different technique. %s', '<strong>' . __( 'Only do this if you know what you are doing.', 'megaoptim-image-optimizer' ) . '</strong>' ), 'megaoptim-image-optimizer' ); ?></p>
                    <div class="megaoptim-row">

                        <div class="megaoptim-col-4">
                            <div class="megaoptim-subrow">
                                <p><strong><?php _e( 'Media Library Backups', 'megaoptim-image-optimizer' ); ?></strong></p>
                                <p>
                                    <button <?php disabled( true, $medialibrary_backup_dir_size <= 0 ); ?> type="button" data-context="<?php echo MGO_MediaAttachment::TYPE; ?>" class="button megaoptim-button-small megaoptim-remove-backups"><?php _e( 'Clean', 'megaoptim-image-optimizer' ); ?> <?php echo $medialibrary_backup_dir_size > 0 ? megaoptim_human_file_size( $medialibrary_backup_dir_size ) : ''; ?></button>
                                </p>
                            </div>
                        </div>
						<?php if ( class_exists( 'MGO_NextGenAttachment' ) ): ?>
                            <div class="megaoptim-col-4">
                                <div class="megaoptim-subrow">
                                    <p><strong>Nextgen Galleries Backups</strong></p>
                                    <p>
                                        <button <?php disabled( true, $nextgen_backup_dir_size <= 0 ); ?> type="button" data-context="<?php echo MGO_NextGenAttachment::TYPE; ?>" class="button megaoptim-button-small megaoptim-remove-backups"><?php _e( 'Clean', 'megaoptim-image-optimizer' ); ?> <?php echo $nextgen_backup_dir_size > 0 ? megaoptim_human_file_size( $nextgen_backup_dir_size ) : ''; ?></button>
                                    </p>
                                </div>
                            </div>
						<?php endif; ?>
                        <div class="megaoptim-col-4">
                            <div class="megaoptim-subrow">
                                <p><strong>Custom Files Backups</strong></p>
                                <p>
                                    <button <?php disabled( true, $localfiles_backup_dir_size <= 0 ); ?> type="button" data-context="<?php echo MGO_LocalFileAttachment::TYPE; ?>" class="button megaoptim-button-small megaoptim-remove-backups"><?php _e( 'Clean', 'megaoptim-image-optimizer' ); ?> <?php echo $localfiles_backup_dir_size > 0 ? megaoptim_human_file_size( $localfiles_backup_dir_size ) : ''; ?></button>
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
                    <label class="megaoptim-option-label"><?php _e( 'Image thumbnails/sizes to be optimized', 'megaoptim-image-optimizer' ); ?></label>
                </div>
                <div class="megaoptim-field-wrap">
                    <p class="megaoptim-field-desc">
						<?php _e( 'Depending of the theme and plugin you are using WordPress generates multiple thumbnails for the images you upload through the WordPress Media Library. Those thumbnails are used in different places in the theme/pages. For example if you print multiple posts in one page and the post image column is small it might not be optimal to use the full version but use the custom defined thumbnail size for it.', 'megaoptim-image-optimizer' ); ?>

                    </p>
                    <p class="megaoptim-field-desc">
						<?php _e( 'MegaOptim gives you option to choose which sizes to be optimized by the optimizer from normal to retina ones if available.', 'megaoptim-image-optimizer' ); ?>
                    </p>
                    <div class="megaoptim-row">
                        <div class="megaoptim-col-2">
                            <div class="checkboxes">
                                <p class="megaoptim-margin-top-0"><strong>Normal thumbnails</strong></p>
								<?php
								$image_sizes = MGO_MediaLibrary::get_image_sizes();
								$selected_image_sizes = $settings->get( MGO_Settings::IMAGE_SIZES, array() );
								foreach ( $image_sizes as $key => $image_size ) {
									$is_checked = in_array( $key, $selected_image_sizes );
								    ?>
                                    <p class="megaoptim-checkbox-row">
										<?php $tooltip = $image_size['width'] . 'x' . $image_size['height']; ?>
                                        <label title="<?php echo $tooltip; ?>" class="checkbox" for="cb_<?php echo $key; ?>">
                                            <input <?php echo $is_checked ? 'checked' : ''; ?> type="checkbox" name="<?php echo MGO_Settings::IMAGE_SIZES; ?>[]" value="<?php echo $key; ?>" id="cb_<?php echo $key; ?>"> <?php echo $key . ' (' . $tooltip . ')'; ?>
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
								$reina_image_sizes = $settings->get( MGO_Settings::RETINA_IMAGE_SIZES, array() );
								foreach ( $image_sizes as $key => $image_size ) {
								    $is_selected = in_array( $key, $reina_image_sizes );
								    ?>
                                    <p class="megaoptim-checkbox-row">
										<?php
										$tooltip = ( $image_size['width'] * 2 ) . 'x' . ( $image_size['height'] * 2 );
										?>
                                        <label title="<?Php echo $tooltip; ?>" class="checkbox" for="cb_retina_<?php echo $key; ?>">
                                            <input <?php echo $is_selected ? 'checked' : ''; ?> type="checkbox" name="<?php echo MGO_Settings::RETINA_IMAGE_SIZES; ?>[]" value="<?php echo $key; ?>" id="cb_retina_<?php echo $key; ?>"> <?php echo $key . ' (' . $tooltip . ')'; ?>
                                        </label>
                                    </p>
								<?php } ?>
                            </div>
                        </div>
                    </div>
                    <p class="warning">
                        <strong><?php _e( 'Remember:', 'megaoptim-image-optimizer' ); ?></strong> <?php _e( 'Each image size of those will cost you one optimization token meaning this option will affect your optimization token usage.', 'megaoptim-image-optimizer' ); ?>
                    </p>
                </div>
            </div>
        </div>


        <div class="megaoptim-field-group">
            <div class="megaoptim-field-group-inner">
                <div class="megaoptim-label-wrap">
                    <label class="megaoptim-option-label"><?php _e( 'CloudFlare Credentials', 'megaoptim-image-optimizer' ); ?></label>
                </div>
                <div class="megaoptim-field-wrap">
                    <input type="text" name="<?php echo MGO_Settings::CLOUDFLARE_EMAIL; ?>" id="<?php echo MGO_Settings::CLOUDFLARE_EMAIL; ?>" value="<?php echo $cf_email; ?>" class="option-control form-control" placeholder="Email">
                    <br/>
                    <input type="text" name="<?php echo MGO_Settings::CLOUDFLARE_API_KEY; ?>" id="<?php echo MGO_Settings::CLOUDFLARE_API_KEY; ?>" value="<?php echo $cf_api_key; ?>" class="option-control form-control" placeholder="API Key">
                    <br/>
                    <input type="text" name="<?php echo MGO_Settings::CLOUDFLARE_ZONE; ?>" id="<?php echo MGO_Settings::CLOUDFLARE_ZONE; ?>" value="<?php echo $cf_zone; ?>" class="option-control form-control" placeholder="Zone ID">
                    <p class="megaoptim-field-desc">
						<?php _e( 'If you are running using CloudFlare APIs and or any WordPress CloudFlare plugins enter your details to be able to purge the cached urls of the files that MegaOptim optimizes.', 'megaoptim-image-optimizer' ); ?>
                    </p>
                </div>
            </div>
        </div>

        <div class="megaoptim-form-actions">
            <div class="options-save">
                <button class="button button-primary button-large" type="submit" id="options-save">
					<?php _e( 'Save Settings', 'megaoptim-image-optimizer' ); ?>
                </button>
            </div>
        </div>
    </form>
</div>