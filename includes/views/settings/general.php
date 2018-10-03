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
?>
<div class="megaoptim-postbox">
    <form class="content-wrapper" method="POST" id="megaoptim_save_form" data-action="megaoptim_save_settings">
        <div class="megaoptim-form-header">
            <div class="megaoptim-full">
                <h1 class="content-header"><?php _e( 'General Settings', 'megaoptim' ); ?></h1>
            </div>
        </div>
        <div class="megaoptim-field-group" id="save_status"></div>
        <div class="megaoptim-field-group">
            <div class="megaoptim-field-group-inner">
                <div class="megaoptim-label-wrap">
                    <label class="megaoptim-option-label" for="<?php echo MGO_Settings::API_KEY; ?>"><?php _e( 'MegaOptim API Key', 'megaoptim' ); ?></label>
                </div>
                <div class="megaoptim-field-wrap">
                    <input type="text" name="<?php echo MGO_Settings::API_KEY; ?>" value="<?php echo isset($settings[ MGO_Settings::API_KEY ]) ? $settings[ MGO_Settings::API_KEY ] : ''; ?>" class="option-control form-control" placeholder="<?php _e( 'Enter API Key', 'megaoptim' ); ?>"/> <?php echo megaoptim_is_connected() ? sprintf( '%s', '<strong>' . __( 'Your API Key is valid!', 'megaoptim' ) . '</strong>' ) : ''; ?>
                    <p class="megaoptim-field-desc">
						<?php echo sprintf( __( 'Enter the api key. Do not have it yet? Register %s', 'megaoptim' ), '<a target="_blank" href="https://megaoptim.com/register">' . __( 'here', 'megaoptim' ) . '</a>' ); ?>
                    </p>
                </div>
            </div>
        </div>
        <div class="megaoptim-field-group">
            <div class="megaoptim-field-group-inner">
                <div class="megaoptim-label-wrap">
                    <label class="megaoptim-option-label" for="<?php echo MGO_Settings::COMPRESSION; ?>"><?php _e( 'Compression', 'megaoptim' ); ?></label>
                </div>
                <div class="megaoptim-field-wrap">
                    <div class="megaoptim-radios">
                        <div class="megaoptim-radio">
                            <div class="megaoptim-radio">
                                <input type="radio" <?php checked( is_null( $settings[ MGO_Settings::COMPRESSION ] ) ? 'lossy' : $settings[ MGO_Settings::COMPRESSION ], 'ultra' ); ?> name="<?php echo MGO_Settings::COMPRESSION; ?>" value="ultra"/>
                                <label><?php _e( 'Ultra', 'megaoptim' ); ?></label>
                                <p class="megaoptim-field-desc">
									<?php _e( 'This type of optimization uses our advanced algorithms to optimize your image as much as possible with some quality loss but not very noticeable and ok for web usage. The optimized image will be much smaller in terms of size and will load much faster.', 'megaoptim' ); ?>
                                </p>
                            </div>
                            <input type="radio" <?php checked( is_null( $settings[ MGO_Settings::COMPRESSION ] ) ? 'lossy' : $settings[ MGO_Settings::COMPRESSION ], 'intelligent' ); ?> name="<?php echo MGO_Settings::COMPRESSION; ?>" value="intelligent"/>
                            <label><?php _e( 'Intelligent', 'megaoptim' ); ?></label>
                            <p class="megaoptim-field-desc">
								<?php _e( 'This type of optimization uses our advanced algorithms to find good compromise between file size and image quality. The optimized image will be almost identical in terms of quality as the original image but there will be significant reduction of its size, some images are even reduced by 90% while keeping the quality almost identical.', 'megaoptim' ); ?>
                            </p>
                        </div>
                        <div class="megaoptim-radio">
                            <input type="radio" <?php checked( $settings[ MGO_Settings::COMPRESSION ], 'lossless' ); ?>
                                   name="<?php echo MGO_Settings::COMPRESSION; ?>" value="lossless"/>
                            <label>Lossless</label>
                            <p class="megaoptim-field-desc">
								<?php _e( 'This type of compression will keep the resulting image identical to the original version and the size reduction will be smaller than lossy compression.', 'megaoptim' ); ?>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="megaoptim-field-group">
            <div class="megaoptim-field-group-inner">
                <div class="megaoptim-label-wrap">
                    <label class="megaoptim-option-label" for="<?php echo MGO_Settings::PRESERVE_EXIF; ?>"><?php _e( 'Keep EXIF Metadata', 'megaoptim' ); ?></label>
                </div>
                <div class="megaoptim-field-wrap">
                    <div class="megaoptim-checkbox">
                        <input type="checkbox"
							<?php checked( $settings[ MGO_Settings::PRESERVE_EXIF ], 1 ); ?> id="<?php echo MGO_Settings::PRESERVE_EXIF; ?>" name="<?php echo MGO_Settings::PRESERVE_EXIF; ?>" value="1"/>
                        <label><?php _e( 'Yes, please!', 'megaoptim' ); ?></label>
                    </div>
                    <p class="megaoptim-field-desc">
						<?php _e( 'Most of the photos have EXIF metadata which contains information about gps location ( if the device had GPS ) where the image is taken, time, camera model and much more and this makes the image bigger. Removing the EXIF metadata will reduce the image size while keeping the image quality intact.', 'megaoptim' ); ?>
                    </p>
                </div>
            </div>
        </div>

        <div class="megaoptim-field-group">
            <div class="megaoptim-field-group-inner">
                <div class="megaoptim-label-wrap">
                    <label class="megaoptim-option-label" for="<?php echo MGO_Settings::CMYKTORGB; ?>"><?php _e( 'Convert CMYK to RGB', 'megaoptim' ); ?></label>
                </div>
                <div class="megaoptim-field-wrap">
                    <div class="megaoptim-checkbox">
                        <input type="checkbox" <?php checked( $settings[ MGO_Settings::CMYKTORGB ], 1 ); ?> id="<?php echo MGO_Settings::CMYKTORGB; ?>" name="<?php echo MGO_Settings::CMYKTORGB; ?>" value="1"/>
                        <label><?php _e( 'Yes, please!', 'megaoptim' ); ?></label>
                    </div>
                    <p class="megaoptim-field-desc">
						<?php _e( 'Images for the web only need RGB format and converting them from CMYK to RGB makes them smaller.', 'megaoptim' ); ?>
                    </p>
                </div>
            </div>
        </div>

        <div class="megaoptim-field-group">
            <div class="megaoptim-field-group-inner">
                <div class="megaoptim-label-wrap">
                    <label class="megaoptim-option-label" for="<?php echo MGO_Settings::AUTO_OPTIMIZE; ?>"><?php _e( 'Auto Optimize Uploads', 'megaoptim' ); ?></label>
                </div>
                <div class="megaoptim-field-wrap">
                    <div class="megaoptim-checkbox">
                        <input type="checkbox"
							<?php checked( $settings[ MGO_Settings::AUTO_OPTIMIZE ], 1 ); ?>
                               id="<?php echo MGO_Settings::AUTO_OPTIMIZE; ?>" name="<?php echo MGO_Settings::AUTO_OPTIMIZE; ?>" value="1"/>
                        <label><?php _e( 'Yes, please!', 'megaoptim' ); ?></label>
                    </div>
                    <p class="megaoptim-field-desc">
						<?php _e( 'If enabled the uploaded images via the Media Library standard upload process will be optimized automatically.', 'megaoptim' ); ?>
                    </p>
                </div>
            </div>
        </div>
        <div class="megaoptim-field-group">
            <div class="megaoptim-field-group-inner">
                <div class="megaoptim-label-wrap">
                    <label class="megaoptim-option-label"><?php _e( 'Resize large images', 'megaoptim' ); ?></label>
                </div>
                <div class="megaoptim-field-wrap">
                    <div class="checkbox_confirm">
                        <label class="checkbox" for="cb_resize_large_images">
                            <input
								<?php checked( $settings[ MGO_Settings::RESIZE_LARGE_IMAGES ], 1 ); ?>
                                    type="checkbox" name="<?php echo MGO_Settings::RESIZE_LARGE_IMAGES; ?>" data-toggle="checkbox" value="1" id="cb_resize_large_images">
                            Yes please! <br/> To maximum
                            <input
								<?php disabled( $settings[ MGO_Settings::RESIZE_LARGE_IMAGES ], 0 ); ?>
                                    value="<?php echo $settings[ MGO_Settings::MAX_WIDTH ]; ?>" type="number" name="<?php echo MGO_Settings::MAX_WIDTH; ?>" id="<?php echo MGO_Settings::MAX_WIDTH; ?>"> px wide and
                            <input
								<?php disabled( $settings[ MGO_Settings::RESIZE_LARGE_IMAGES ], 0 ); ?>
                                    value="<?php echo $settings[ MGO_Settings::MAX_HEIGHT ]; ?>" type="number" name="<?php echo MGO_Settings::MAX_HEIGHT; ?>" id="<?php echo MGO_Settings::MAX_HEIGHT; ?>"> px tall
                            <br/>

                        </label>
                        <p class="megaoptim-field-desc">
                            (<?php _e( 'Original aspect ratio is preserved and image is not cropped', 'megaoptim' ); ?>) </p>
                    </div>
                </div>
            </div>
        </div>
        <div class="megaoptim-field-group">
            <div class="megaoptim-field-group-inner">
                <div class="megaoptim-label-wrap">
                    <label class="megaoptim-option-label"><?php _e( 'HTTP AUTH credentials', 'megaoptim' ); ?></label>
                </div>
                <div class="megaoptim-field-wrap">
                    <input type="text" name="<?php echo MGO_Settings::HTTP_USER; ?>" id="<?php echo MGO_Settings::HTTP_USER; ?>" value="<?php echo $settings[ MGO_Settings::HTTP_USER ]; ?>" class="option-control form-control" placeholder="User">
                    <br/>
                    <input type="password" name="<?php echo MGO_Settings::HTTP_PASS; ?>" id="<?php echo MGO_Settings::HTTP_PASS; ?>" value="<?php echo $settings[ MGO_Settings::HTTP_PASS ]; ?>" class="option-control form-control" placeholder="Password">
                    <p class="megaoptim-field-desc">
						<?php _e( 'If your site is behind HTTP Basic Authentication please enter the User & Password credentials. If you don\'t know what is this then just leave the fields empty', 'megaoptim' ); ?>
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

    <script>
        //Dependency
        (function ($) {
            $('#cb_resize_large_images').on('change', function () {
                if ($(this).is(':checked')) {
                    $("#maximum_size_w, #maximum_size_h").prop('disabled', false);
                } else {
                    $("#maximum_size_w, #maximum_size_h").prop('disabled', true);
                }
            });
        })(jQuery);
    </script>
</div>