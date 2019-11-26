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

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access is not allowed.' );
}
$settings = MGO_Settings::instance()->get();

$api_key       = MGO_Settings::instance()->get( MGO_Settings::API_KEY, '' );
$compression   = MGO_Settings::instance()->get( MGO_Settings::COMPRESSION, 'intelligent' );
$preserve_exif = MGO_Settings::instance()->get( MGO_Settings::PRESERVE_EXIF, 0 );
$cmyktorgb     = MGO_Settings::instance()->get( MGO_Settings::CMYKTORGB, 1 );
$auto_optimize = MGO_Settings::instance()->get( MGO_Settings::AUTO_OPTIMIZE, 1 );
$resize_images = MGO_Settings::instance()->get( MGO_Settings::RESIZE_LARGE_IMAGES, 0 );
$max_width     = MGO_Settings::instance()->get( MGO_Settings::MAX_WIDTH, '' );
$max_height    = MGO_Settings::instance()->get( MGO_Settings::MAX_HEIGHT, '' );
$http_user     = MGO_Settings::instance()->get(MGO_Settings::HTTP_USER );
$http_pass     = MGO_Settings::instance()->get(MGO_Settings::HTTP_PASS );
?>
<div class="megaoptim-postbox">
    <form class="content-wrapper" method="POST" id="megaoptim_save_form" data-action="megaoptim_save_settings">
        <div class="megaoptim-field-group" id="save_status"></div>
        <div class="megaoptim-field-group">
            <div class="megaoptim-field-group-inner">
                <div class="megaoptim-label-wrap">
                    <label class="megaoptim-option-label" for="<?php echo MGO_Settings::API_KEY; ?>"><?php _e( 'MegaOptim API Key', 'megaoptim-image-optimizer' ); ?></label>
                </div>
                <div class="megaoptim-field-wrap">
                    <input type="text" name="<?php echo MGO_Settings::API_KEY; ?>" value="<?php echo $api_key; ?>" class="option-control form-control" placeholder="<?php _e( 'Enter API Key', 'megaoptim-image-optimizer' ); ?>"/> <?php echo MGO_Profile::_is_connected() ? sprintf( '%s', '<strong>' . __( 'Your API Key is valid!', 'megaoptim-image-optimizer' ) . '</strong>' ) : ''; ?>
                    <p class="megaoptim-field-desc">
						<?php echo sprintf( __( 'Enter the api key. Do not have it yet? Register %s here for %s', 'megaoptim-image-optimizer' ), '<a target="_blank" href="https://megaoptim.com/register">' . __( 'here', 'megaoptim-image-optimizer' ) . '</a>', '<strong>' . __( 'free!', 'megaoptim-image-optimizer' ) . '</strong>' ); ?>
                    </p>
                </div>
            </div>
        </div>
        <div class="megaoptim-field-group">
            <div class="megaoptim-field-group-inner">
                <div class="megaoptim-label-wrap">
                    <label class="megaoptim-option-label" for="<?php echo MGO_Settings::COMPRESSION; ?>"><?php _e( 'Compression', 'megaoptim-image-optimizer' ); ?></label>
                </div>
                <div class="megaoptim-field-wrap">
                    <div class="megaoptim-radios">
                        <div class="megaoptim-radio">
                            <input type="radio" <?php checked( $compression, 'ultra' ); ?> name="<?php echo MGO_Settings::COMPRESSION; ?>" value="ultra"/>
                            <label><?php _e( 'Ultra', 'megaoptim-image-optimizer' ); ?></label>
                            <p class="megaoptim-field-desc">
								<?php _e( 'This compression level uses our advanced algorithms to optimize the image as much as possible with some quality loss but not very noticeable and acceptable for web usage. The resulting image will be much smaller in terms of size and will load much faster.', 'megaoptim-image-optimizer' ); ?>
                            </p>
                        </div>
                        <div class="megaoptim-radio">
                            <input type="radio" <?php checked( $compression, 'intelligent' ); ?> name="<?php echo MGO_Settings::COMPRESSION; ?>" value="intelligent"/>
                            <label><?php _e( 'Intelligent', 'megaoptim-image-optimizer' ); ?></label>
                            <p class="megaoptim-field-desc">
								<?php _e( 'This compression level uses our advanced algorithms to find good compromise between file size and image quality. The optimized image will be almost identical in terms of quality as the original image but there will be significant reduction of its size, some images are even reduced by 80% while keeping the quality almost identical.', 'megaoptim-image-optimizer' ); ?>
                            </p>
                        </div>
                        <div class="megaoptim-radio">
                            <input type="radio" <?php checked( $compression, 'lossless' ); ?>name="<?php echo MGO_Settings::COMPRESSION; ?>" value="lossless"/>
                            <label><?php _e( 'Lossless', 'megaoptim-image-optimizer' ); ?></label>
                            <p class="megaoptim-field-desc">
								<?php _e( 'This compression level keeps the resulting image identical to the original version and the size reduction will be smaller than lossy compression because it only attempts to remove EXIF data and does not touch the quality. It\'t not recommended if you are looking for speed or to satisfy the pagespeed needs', 'megaoptim-image-optimizer' ); ?>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="megaoptim-field-group">
            <div class="megaoptim-field-group-inner">
                <div class="megaoptim-label-wrap">
                    <label class="megaoptim-option-label" for="<?php echo MGO_Settings::PRESERVE_EXIF; ?>"><?php _e( 'Keep EXIF Metadata', 'megaoptim-image-optimizer' ); ?></label>
                </div>
                <div class="megaoptim-field-wrap">
                    <div class="megaoptim-checkbox">
                        <input type="checkbox" <?php checked( $preserve_exif, 1 ); ?> id="<?php echo MGO_Settings::PRESERVE_EXIF; ?>" name="<?php echo MGO_Settings::PRESERVE_EXIF; ?>" value="1"/>
                        <label><?php _e( 'Yes, please!', 'megaoptim-image-optimizer' ); ?></label>
                    </div>
                    <p class="megaoptim-field-desc">
						<?php _e( 'Most of the images have EXIF metadata which contains information about gps location ( if the device had GPS ) where the image is taken, time, camera model and much more and this makes the image take more space. Removing the EXIF metadata will reduce the image size while keeping the image quality intact.', 'megaoptim-image-optimizer' ); ?>
                    </p>
                </div>
            </div>
        </div>

        <div class="megaoptim-field-group">
            <div class="megaoptim-field-group-inner">
                <div class="megaoptim-label-wrap">
                    <label class="megaoptim-option-label" for="<?php echo MGO_Settings::CMYKTORGB; ?>"><?php _e( 'Convert CMYK to RGB', 'megaoptim-image-optimizer' ); ?></label>
                </div>
                <div class="megaoptim-field-wrap">
                    <div class="megaoptim-checkbox">
                        <input type="checkbox" <?php checked( $cmyktorgb, 1 ); ?> id="<?php echo MGO_Settings::CMYKTORGB; ?>" name="<?php echo MGO_Settings::CMYKTORGB; ?>" value="1"/>
                        <label><?php _e( 'Yes, please!', 'megaoptim-image-optimizer' ); ?></label>
                    </div>
                    <p class="megaoptim-field-desc">
						<?php _e( 'The RGB color profile is better for web and it could also help with reducing the image size.', 'megaoptim-image-optimizer' ); ?>
                    </p>
                </div>
            </div>
        </div>

        <div class="megaoptim-field-group">
            <div class="megaoptim-field-group-inner">
                <div class="megaoptim-label-wrap">
                    <label class="megaoptim-option-label" for="<?php echo MGO_Settings::AUTO_OPTIMIZE; ?>"><?php _e( 'Auto Optimize Uploads', 'megaoptim-image-optimizer' ); ?></label>
                </div>
                <div class="megaoptim-field-wrap">
                    <div class="megaoptim-checkbox">
                        <input type="checkbox" <?php checked( $auto_optimize, 1 ); ?> id="<?php echo MGO_Settings::AUTO_OPTIMIZE; ?>" name="<?php echo MGO_Settings::AUTO_OPTIMIZE; ?>" value="1"/>
                        <label><?php _e( 'Yes, please!', 'megaoptim-image-optimizer' ); ?></label>
                    </div>
                    <p class="megaoptim-field-desc">
						<?php _e( 'If enabled, the images uploaded through Media Library or NextGen galleries will be auto-optimized after uploading so you don\'t have to run bulk optimization.', 'megaoptim-image-optimizer' ); ?>
                    </p>
                </div>
            </div>
        </div>
        <div class="megaoptim-field-group">
            <div class="megaoptim-field-group-inner">
                <div class="megaoptim-label-wrap">
                    <label class="megaoptim-option-label"><?php _e( 'Resize large images', 'megaoptim-image-optimizer' ); ?></label>
                </div>
                <div class="megaoptim-field-wrap">
                    <div class="checkbox_confirm">
                        <label class="checkbox" for="cb_resize_large_images">
                            <input  <?php checked( $resize_images, 1 ); ?>
                                    type="checkbox"
                                    name="<?php echo MGO_Settings::RESIZE_LARGE_IMAGES; ?>"
                                    value="1"
                                    class="megaoptim-checkbox-conditional"
                                    data-target="#<?php echo MGO_Settings::MAX_WIDTH; ?>, #<?php echo MGO_Settings::MAX_HEIGHT; ?>"
                                    data-targetclearvalues="1"
                                    data-targetstate="disabled"
                            >
                            Yes please! <br/> To maximum
                            <input <?php disabled( $resize_images, 0 ); ?> value="<?php echo $max_width; ?>" type="number" name="<?php echo MGO_Settings::MAX_WIDTH; ?>" id="<?php echo MGO_Settings::MAX_WIDTH; ?>"> px wide and
                            <input <?php disabled( $resize_images, 0 ); ?> value="<?php echo $max_height; ?>" type="number" name="<?php echo MGO_Settings::MAX_HEIGHT; ?>" id="<?php echo MGO_Settings::MAX_HEIGHT; ?>"> px tall
                            <br/>
                        </label>
                        <p class="megaoptim-field-desc">(<?php _e( 'Original aspect ratio is preserved and image is not cropped', 'megaoptim-image-optimizer' ); ?>)</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="megaoptim-field-group">
            <div class="megaoptim-field-group-inner">
                <div class="megaoptim-label-wrap">
                    <label class="megaoptim-option-label"><?php _e( 'HTTP AUTH credentials', 'megaoptim-image-optimizer' ); ?></label>
                </div>
                <div class="megaoptim-field-wrap">
                    <input type="text" name="<?php echo MGO_Settings::HTTP_USER; ?>" id="<?php echo MGO_Settings::HTTP_USER; ?>" value="<?php echo $http_user; ?>" class="option-control form-control" placeholder="User">
                    <br/>
                    <input type="password" name="<?php echo MGO_Settings::HTTP_PASS; ?>" id="<?php echo MGO_Settings::HTTP_PASS; ?>" value="<?php echo $http_pass; ?>" class="option-control form-control" placeholder="Password">
                    <p class="megaoptim-field-desc">
						<?php _e( 'If your site is behind HTTP Basic Authentication please enter the User & Password credentials. If you don\'t know what is this then just leave the fields empty', 'megaoptim-image-optimizer' ); ?>
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