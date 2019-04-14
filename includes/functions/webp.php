<?php
/********************************************************************
 * Copyright (C) 2019 MegaOptim (https://megaoptim.com)
 *
 * This file is part of MegaOptim Image Optimizer
 *
 * MegaOptim Image Optimizer is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * (at your option) any later version.
 *
 * MegaOptim Image Optimizer is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with MegaOptim Image Optimizer. If not, see <https://www.gnu.org/licenses/>.
 **********************************************************************/

/**
 * The actual filter attached to the_content, the_excerpt and post_thumbnail_html
 * @param $content
 * @return string|string[]|null
 */
function megaoptim_webp_filter_content( $content ) {
	if ( function_exists( 'is_amp_endpoint' ) && is_amp_endpoint() ) {
		return $content;
	}
	if ( is_feed() || is_admin() ) {
		return $content;
	}
	return megaoptim_webp_convert_text($content);
}

/**
 * Convert content images to webp images. Replaces .png,.jpg to .web if they exist.
 * This function can be used as standalone
 * eg. megaoptim_webp_convert_text($your_content)
 *
 * @param $content
 *
 * @return string
 */
function megaoptim_webp_convert_text( $content ) {
	return preg_replace_callback( '/<img[^>]*>/', 'megaoptim_replace_img_with_webp', $content );
}

/**
 * Replaces img jpg or png src to webp src if exists.
 *
 * @param $match string eg. <img src...>
 *
 * @return string
 */
function megaoptim_replace_img_with_webp( $match ) {
	// Skip images with this class
	$ignore_webp_by_class = apply_filters( 'megaoptim_webp_ignored_class', 'mgo-skip-webp' );

	if ( megaoptim_contains( $match[0], $ignore_webp_by_class ) ) {
		return $match[0];
	}

	$img = megaoptim_get_dom_element_attributes( $match[0], 'img' );

	$src_data      = megaoptim_get_image_attributes( $img, 'src' );
	$src           = $src_data['value'];
	$src_prefix    = $src_data['prefix'];
	$srcset_data   = megaoptim_get_image_attributes( $img, 'srcset' );
	$srcset        = $srcset_data['value'];
	$srcset_prefix = $srcset ? $srcset_data['prefix'] : $src_data['prefix'];
	$sizes_data    = megaoptim_get_image_attributes( $img, 'sizes' );
	$sizes         = $sizes_data['value'];
	$sizes_prefix  = $sizes_data['prefix'];
	$alt_attr      = isset( $img['alt'] ) && strlen( $img['alt'] ) ? ' alt="' . $img['alt'] . '"' : '';

	$uploads_path_base = megaoptim_webp_get_image_dir($src);
	if($uploads_path_base === false) {
		return $match[0];
	}

	// Remove all previous attributes.
	unset( $img['src'] );
	unset( $img['data-src'] );
	unset( $img['data-lazy-src'] );
	unset( $img['srcset'] );
	unset( $img['sizes'] );
	unset( $img['alt'] );

	// Try to assemble the picture
	$srcset_webp = '';
	if ( $srcset ) {
		$defs = explode( ",", $srcset );
		foreach ( $defs as $item ) {
			$parts            = preg_split( '/\s+/', trim( $item ) );
			$file_webp_compat = $uploads_path_base . wp_basename( $parts[0], '.' . pathinfo( $parts[0], PATHINFO_EXTENSION ) ) . '.webp';
			$file_webp        = $uploads_path_base . wp_basename( $parts[0] ) . '.webp';
			if ( file_exists( $file_webp ) ) {
				$srcset_webp .= strlen( $srcset_webp ) > 0 ? ',' : '';
				$srcset_webp .= $parts[0] . '.webp';
				$srcset_webp .= isset( $parts[1] ) ? ' ' . $parts[1] : '';
			}
			if ( file_exists( $file_webp_compat ) ) {
				$srcset_webp .= strlen( $srcset_webp ) > 0 ? ',' : '';
				$srcset_webp .= preg_replace( '/\.[a-zA-Z0-9]+$/', '.webp', $parts[0] );
				$srcset_webp .= isset( $parts[1] ) ? ' ' . $parts[1] : '';
			}
		}
	} else {
		$srcset           = trim( $src );
		$file_webp_compat = $uploads_path_base . wp_basename( $srcset, '.' . pathinfo( $srcset, PATHINFO_EXTENSION ) ) . '.webp';
		$file_webp        = $uploads_path_base . wp_basename( $srcset ) . '.webp';
		if ( file_exists( $file_webp ) ) {
			$srcset_webp = $srcset . ".webp";
		} else {
			if ( file_exists( $file_webp_compat ) ) {
				$srcset_webp = preg_replace( '/\.[a-zA-Z0-9]+$/', '.webp', $srcset );
			}
		}
	}

	if ( ! strlen( $srcset_webp ) ) {
		return $match[0];
	}
	$img['class'] = ( isset( $img['class'] ) ? $img['class'] . " " : "" ) . $ignore_webp_by_class;

	return '<picture ' . megaoptim_create_dom_element_attributes( $img ) . '>'
	       . '<source ' . $srcset_prefix . 'srcset="' . $srcset_webp . '"' . ( $sizes ? ' ' . $sizes_prefix . 'sizes="' . $sizes . '"' : '' ) . ' type="image/webp">'
	       . '<source ' . $srcset_prefix . 'srcset="' . $srcset . '"' . ( $sizes ? ' ' . $sizes_prefix . 'sizes="' . $sizes . '"' : '' ) . '>'
	       . '<img ' . $src_prefix . 'src="' . $src . '" ' . megaoptim_create_dom_element_attributes( $img ) . $alt_attr
	       . ( strlen( $srcset ) ? ' srcset="' . $srcset . '"' : '' ) . ( strlen( $sizes ) ? ' sizes="' . $sizes . '"' : '' ) . '>'
	       . '</picture>';
}

/**
 * Returns the image dir if it's local. Otherwise it returns false.
 * @param $src
 *
 * @return bool|mixed|string
 */
function megaoptim_webp_get_image_dir($src) {
	$updir = wp_upload_dir();

	$content_dir = WP_CONTENT_DIR;
	$content_url = content_url();

	$base_dir = $updir['basedir'];
	$base_url = $updir['baseurl'];

	// Make the src and the current protocol same protocol.
	$protocol = explode( "://", $src );
	if ( count( $protocol ) > 1 ) {
		$protocol = $protocol[0];
		if ( strpos( $base_url, $protocol . "://" ) === false ) {
			$url_parts = explode( "://", $base_url );
			if ( count( $url_parts ) > 1 ) {
				$base_url = $protocol . "://" . $url_parts[1];
			}
		}
	}
	// Handle non-upload paths
	$base_img_src = str_replace( $base_url, $base_dir, $src );
	if ( $base_img_src == $src ) {
		$base_img_src = str_replace( $content_url, $content_dir, $src );
	}
	// Handle CDN, subdomain or relative url cases.
	if ( $base_img_src == $src ) {
		$url  = parse_url( $src );
		$base = parse_url( $base_url );
		$src_host     = array_reverse( explode( '.', $url['host'] ) );
		$base_url_host = array_reverse( explode( '.', $base['host'] ) );
		if ( $src_host[0] === $base_url_host[0] && $src_host[1] === $base_url_host[1] && ( strlen( $src_host[1] ) > 3 || isset( $src_host[2] ) && isset( $src_host[2] ) && $src_host[2] == $base_url_host[2] ) ) {
			$baseurl   = str_replace( $base['scheme'] . '://' . $base['host'], $url['scheme'] . '://' . $url['host'], $base_url );
			$base_img_src = str_replace( $baseurl, $base_dir, $src );
		}
		// Bail if external url
		if ( $base_img_src == $src ) {
			return false;
		}
	}
	$base_img_src = trailingslashit(dirname($base_img_src));
	return $base_img_src;
}


/**
 * Returns the parameter needed out of array of parameters for specific html img tag.
 *
 * @param array $attribute_array
 * @param $type
 *
 * @return array
 */
function megaoptim_get_image_attributes( $attribute_array, $type ) {
	return array(
		'value'  =>
			( isset( $attribute_array[ 'data-lazy-' . $type ] ) && strlen( $attribute_array[ 'data-lazy-' . $type ] ) ) ?
				$attribute_array[ 'data-lazy-' . $type ]
				: ( isset( $attribute_array[ 'data-' . $type ] ) && strlen( $attribute_array[ 'data-' . $type ] ) ?
				$attribute_array[ 'data-' . $type ]
				: ( isset( $attribute_array[ $type ] ) && strlen( $attribute_array[ $type ] ) ? $attribute_array[ $type ] : false ) ),
		'prefix' =>
			( isset( $attribute_array[ 'data-lazy-' . $type ] ) && strlen( $attribute_array[ 'data-lazy-' . $type ] ) ) ? 'data-lazy-'
				: ( isset( $attribute_array[ 'data-' . $type ] ) && strlen( $attribute_array[ 'data-' . $type ] ) ? 'data-'
				: ( isset( $attribute_array[ $type ] ) && strlen( $attribute_array[ $type ] ) ? '' : false ) )
	);
}

/**
 * Makes a string with all attributes.
 *
 * @param $attribute_array
 *
 * @return string
 */
function megaoptim_create_dom_element_attributes( $attribute_array ) {
	$attributes = '';
	foreach ( $attribute_array as $attribute => $value ) {
		$attributes .= $attribute . '="' . $value . '" ';
	}

	return substr( $attributes, 0, - 1 );
}

/**
 * Returns element attributes in assicative array by element name and domelement node
 *
 * @param $content
 * @param $element
 *
 * @return array
 */
function megaoptim_get_dom_element_attributes( $content, $element ) {
    if(empty($content)) {
        return array();
    }
	if ( function_exists( "mb_convert_encoding" ) ) {
		$content = mb_convert_encoding( $content, 'HTML-ENTITIES', 'UTF-8' );
	}
	$dom = new DOMDocument;
	$dom->loadHTML( $content );
	$attr = array();
	foreach ( $dom->getElementsByTagName( $element ) as $tag ) {
		foreach ( $tag->attributes as $attribName => $attribNodeVal ) {
			$attr[ $attribName ] = $tag->getAttribute( $attribName );
		}
	}

	return $attr;
}

/**
 * Returns target filters for webp analyzis and image replacement
 * @return mixed|array
 */
function megaoptim_webp_target_filters() {
    return apply_filters('megaoptim_webp_target_filters', array(
	    'the_content', 'the_excerpt', 'post_thumbnail_html', 'acf_the_content', 'widget_text'
    ));
}


/**
 * Creates htaccess file used for adding webp support through .htaccess
 * @return bool
 */
function megaoptim_add_webp_support_via_htaccess() {
	$htaccess_path = megaoptim_get_htaccess_path();

	if(!is_writable(dirname($htaccess_path))) {
	    megaoptim_log('.htaccess dir not writable.');
	    return false;
    }
	$htaccess_contents = '';
    if(file_exists($htaccess_path)) {
        ob_start();
        include($htaccess_path);
        $htaccess_contents = ob_get_clean();
	    $htaccess_contents = trim(megaoptim_remove_between('# BEGIN MegaOptimIO', '# END MegaOptimIO', $htaccess_contents));
    }

	ob_start();
	?>

# BEGIN MegaOptimIO
<IfModule mod_setenvif.c>
    # Vary: Accept for all the requests to jpeg and png
    SetEnvIf Request_URI "\.(jpe?g|png)$" REQUEST_image
</IfModule>
<IfModule mod_rewrite.c>
    RewriteEngine On
    # Check if browser supports WebP images
    RewriteCond %{HTTP_ACCEPT} image/webp
    # Check if WebP replacement image exists
    RewriteCond %{DOCUMENT_ROOT}/$1.webp -f
    # Serve WebP image instead
    RewriteRule (.+)\.(jpe?g|png)$ $1.webp [T=image/webp]
</IfModule>
<IfModule mod_headers.c>
    Header append Vary Accept env=REQUEST_image
</IfModule>
<IfModule mod_mime.c>
    AddType image/webp .webp
</IfModule>
# END MegaOptimIO

	<?php
	$htaccess_contents .= ob_get_clean();
	megaoptim_write($htaccess_path, $htaccess_contents, 'w');
	return true;
}

/**
 * Removes WebP support from the .htaccess file.
 * @return bool
 */
function megaoptim_remove_webp_support_via_htaccess() {
	$htaccess_path = megaoptim_get_htaccess_path();
	if(!is_writable(dirname($htaccess_path))) {
		megaoptim_log('.htaccess dir not writable.');
		return false;
	}
	if(!file_exists($htaccess_path)) {
	    return false;
	}

	ob_start();
	include($htaccess_path);
	$htaccess_contents = ob_get_clean();
	$htaccess_contents = trim(megaoptim_remove_between('# BEGIN MegaOptimIO', '# END MegaOptimIO', $htaccess_contents));
	megaoptim_write($htaccess_path, $htaccess_contents, 'w');

	return true;
}