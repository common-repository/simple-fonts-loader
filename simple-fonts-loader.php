<?php
/**
 * Plugin Name: Simple Fonts Loader
 * Plugin URI: https://simpleplugins.fr/sfl/
 * Description: Just enable some fonts from Google Fonts on Website
 * Version: 1.8.2
 * Author: Tom Baumgarten
 * Author URI: https://www.tombgtn.fr/
 * Text Domain: simple-fonts-loader
 * License: GPL2
 */

if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! defined( 'SIMPLE_FONTS_LOADER_VERSION' ) ) define( 'SIMPLE_FONTS_LOADER_VERSION', '1.8.2' );
if ( ! defined( 'SIMPLE_FONTS_LOADER_FILE' ) ) define( 'SIMPLE_FONTS_LOADER_FILE', __FILE__ );

require( dirname(SIMPLE_FONTS_LOADER_FILE) . '/includes/fonts.php' );
require( dirname(SIMPLE_FONTS_LOADER_FILE) . '/includes/helper.php' );

if (is_admin()) {
	require( dirname(SIMPLE_FONTS_LOADER_FILE) . '/includes/admin/admin.php' );
}

require( dirname(SIMPLE_FONTS_LOADER_FILE) . '/includes/front/front.php' );


/* Chargement des fonts */
foreach ( scandir( dirname(SIMPLE_FONTS_LOADER_FILE) . '/fonts' ) as $dirname ) {

	$dirpath = dirname(SIMPLE_FONTS_LOADER_FILE) . '/fonts/' . $dirname;
	
	if ($dirname!=='.' && $dirname!=='..' && !is_file($dirpath)) {
		$slug = strtolower($dirname);
		$label = ucfirst(strtolower($dirname));
		$category = 'sans-serif';
		$variable = false;
		if (file_exists($dirpath . '/config.json')) {
			$config = file_get_contents($dirpath . '/config.json');
			if ($config) {
				$config_json = json_decode($config);
				if (is_object($config_json)) {
					if (isset($config_json->label)) $label = strval($config_json->label);
					if (isset($config_json->category)) $category = strval($config_json->category);
					if (isset($config_json->fonts) && is_array($config_json->fonts)) $variable = true;

					register_font_family($slug, $label, $category, $variable);

					/* Variable fonts */
					if (isset($config_json->fonts) && is_array($config_json->fonts)) {
						foreach ($config_json->fonts as $variable_font) {
							if (isset($variable_font->file) && file_exists($dirpath . '/'.$variable_font->file)) {
								$url = plugin_dir_url(SIMPLE_FONTS_LOADER_FILE) . 'fonts/' . $dirname . '/' . $variable_font->file;
								$filename_parts = explode('.', $variable_font->file);
								$format = strtolower(end($filename_parts));
								$variant = (isset($variable_font->variant)) ? strval($variable_font->variant) : 'normal' ;
								$stretch = (isset($variable_font->stretch)) ? strval($variable_font->stretch) : 'normal' ;
								$url_v2 = add_query_arg(array(
									'family'	=> $dirname,
									'file'		=> $variable_font->file,
									'format'	=> $format
								), plugin_dir_url(SIMPLE_FONTS_LOADER_FILE) . 'fonts/load.php');
								if (in_array($format,  array('woff2', 'woff', 'svg', 'ttf', 'eot'))) {
									if (isset($variable_font->weights) && is_array($variable_font->weights) && count($variable_font->weights)>0) {
										foreach ($variable_font->weights as $weight) {
											register_font(strtolower($dirname), $url, intval($weight), $variant, $stretch, $format);
										}
									} else {
										register_font(strtolower($dirname), $url, 400, $variant, $stretch, $format);
									}
								}
							}
						}
					}
				}
			}
		}

		register_font_family($slug, $label, $category);

		foreach ( scandir( $dirpath ) as $filename ) {

			$path = $dirpath . '/' . $filename;

			if ($filename!=='.' && $filename!=='..' && $filename!=='config.json' && is_file($path)) {
				$filename_parts = explode('.', $filename);
				$format = strtolower(end($filename_parts));
				$fonts_parts = explode('-', $filename_parts[0]);
				if (is_array($fonts_parts) && count($fonts_parts)>1 && in_array($format,  array('woff2', 'woff', 'svg', 'ttf', 'eot'))) {
					$url = plugin_dir_url(SIMPLE_FONTS_LOADER_FILE) . 'fonts/' . $dirname . '/' . $filename;
					$url_v2 = add_query_arg(array(
						'family'	=> $dirname,
						'file'		=> $filename,
						'format'	=> $format
					), plugin_dir_url(SIMPLE_FONTS_LOADER_FILE) . 'fonts/load.php');
					$weight = (isset($fonts_parts[0])) ? intval($fonts_parts[0]) : 400 ;
					$variant = (isset($fonts_parts[1])) ? strval($fonts_parts[1]) : 'normal' ;
					$stretch = (isset($fonts_parts[2])) ? strval($fonts_parts[2]) : 'normal' ;
					register_font(strtolower($dirname), $url, $weight, $variant, $stretch, $format);
				}
			}
		}
	}
}
