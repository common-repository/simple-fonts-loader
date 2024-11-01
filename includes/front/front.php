<?php

declare(strict_types=1);

namespace SFL\Front;

if ( ! defined( 'ABSPATH' ) ) exit;



if (!class_exists('\SFL\Front\SFLFront')) {

	/**
	* 
	*/
	class SFLFront {
		
		/**
		 * @var Singleton
		 * @access private
		 * @static
		 */
		private static $_instance = null;

		/**
		* Méthode qui crée l'unique instance de la classe
		* si elle n'existe pas encore puis la retourne.
		*
		* @param void
		* @return Singleton
		*/
		public static function getInstance() {
			if(is_null(self::$_instance)) self::$_instance = new SFLFront();
			return self::$_instance;
		}
		
		/**
		* Constructeur de la classe
		*
		* @param void
		* @return void
		*/
		private function __construct() {
			$this->addHooks();
		}

		public function addHooks() {
			add_action('wp_head', array($this, 'addFontFace'), 1);

			if (apply_filters('sfl_fonts_admin', false)) add_action('admin_head', array($this, 'addFontFace'), 1);

			add_action('send_headers', array($this, 'addHeaders'), 1);
		}

		/**
		* Méthode qui ajoute les font-face au style
		*
		* @param void
		* @return void
		*/
		public function addFontFace() {
			$all_fonts = \SFL\Fonts\SFLFonts::getFonts();
			$activated = \SFL\Fonts\SFLFonts::getFontsActivated();
			$favorites = \SFL\Fonts\SFLFonts::getFontsFavorites();

			$preloaded = array();

			foreach ($all_fonts as $slug => $fonts) {
				foreach ($fonts['fonts'] as $font_key => $font) {
					if (in_array($slug.':'.$font_key, array_keys($activated)) && in_array($slug.':'.$font_key, array_keys($favorites))) {

						$formats = array();

						/* Order formats from lightest to heaviest */
						$formats_order = array('eot', 'woff2', 'woff', 'svg', 'ttf');
						foreach ($font['formats'] as $format => $url) {
							$key = array_search(strtolower($format), $formats_order, true);
							if (is_int($key) && $key >= 0 && $key < count($formats_order)) $formats[$key] = array('format' => $format, 'url' => $url);
						}
						ksort($formats);

						/* Preload only best format */
						$format = array_shift($formats);
						$mime_format = array(
							'eot'	=> 'application/vnd.ms-fontobject',
							'woff2'	=> 'font/woff2',
							'woff'	=> 'font/woff',
							'svg'	=> 'image/svg+xml',
							'ttf'	=> 'font/ttf'
						);
						if (in_array($format['format'], array_keys($mime_format)) && !in_array(esc_url_raw($format['url']), $preloaded)) {
							echo '<link rel="preload" type="'.esc_attr($mime_format[$format['format']]).'" href="'.esc_url_raw($format['url']).($format['format']=='eot' ? '?#iefix' : ($format['format']!='woff2' ? '?v='.SIMPLE_FONTS_LOADER_VERSION : '')).'" as="font" crossorigin>';
							$preloaded[] = esc_url_raw($format['url']);
						}
					}
				}
			}

			echo '<style>';

			foreach ($all_fonts as $slug => $fonts) {
				foreach ($fonts['fonts'] as $font_key => $font) {
					if (in_array($slug.':'.$font_key, array_keys($activated))) {

						$formats = array();

						/* Order formats from lightest to heaviest */
						$formats_order = array('eot', 'woff2', 'woff', 'svg', 'ttf');
						foreach ($font['formats'] as $format => $url) {
							$key = array_search(strtolower($format), $formats_order, true);
							if (is_int($key) && $key >= 0 && $key < count($formats_order)) $formats[$key] = array('format' => $format, 'url' => $url);
						}
						ksort($formats);

						/* Pretty name of the format in the declaration */
						$pretty_format = array(
							'eot'	=> 'embedded-opentype',
							'woff2'	=> 'woff2',
							'woff'	=> 'woff',
							'svg'	=> 'svg',
							'ttf'	=> 'truetype'
						);

						$font_formats = array();
						$font_format_eot = '';
						foreach ($formats as $format) {
							if (in_array($format['format'], array_keys($pretty_format))) $font_formats[] = 'url('.esc_url_raw($format['url']).($format['format']=='eot' ? '?#iefix' : ($format['format']!='woff2' ? '?v='.SIMPLE_FONTS_LOADER_VERSION : '')).') format(\''.esc_attr($pretty_format[$format['format']]).'\')';

							if ($format['format']=='eot') $font_format_eot = '
							src: url('.esc_url_raw($format['url']).');';
						}

						echo '
						@font-face {
							font-family: \''.((isset($fonts['label'])) ? esc_html($fonts['label']) : esc_html(esc_attr($slug))).'\';
							font-style: '.esc_attr($font['variant']).';
							font-weight: '.esc_attr($font['weight']).';
							font-stretch: '.esc_attr($font['stretch']).';
							font-display: swap;'.
							(!empty($font_format_eot) ? $font_format_eot : '').'
							src: '.implode(', ', $font_formats).';
						}
						';
					}
				}
			}

			echo '</style>';
		}

		/**
		* Méthode qui ajoute les link preload aux headers http
		*
		* @param void
		* @return void
		*/
		public function addHeaders() {
			$all_fonts = \SFL\Fonts\SFLFonts::getFonts();
			$activated = \SFL\Fonts\SFLFonts::getFontsActivated();
			$favorites = \SFL\Fonts\SFLFonts::getFontsFavorites();

			$preloaded = array();

			foreach ($all_fonts as $slug => $fonts) {
				foreach ($fonts['fonts'] as $font_key => $font) {
					if (in_array($slug.':'.$font_key, array_keys($activated)) && in_array($slug.':'.$font_key, array_keys($favorites))) {

						$formats = array();

						/* Order formats from lightest to heaviest */
						$formats_order = array('eot', 'woff2', 'woff', 'svg', 'ttf');
						foreach ($font['formats'] as $format => $url) {
							$key = array_search(strtolower($format), $formats_order, true);
							if (is_int($key) && $key >= 0 && $key < count($formats_order)) $formats[$key] = array('format' => $format, 'url' => $url);
						}
						ksort($formats);

						/* Preload only best format */
						$format = array_shift($formats);
						$mime_format = array(
							'eot'	=> 'application/vnd.ms-fontobject',
							'woff2'	=> 'font/woff2',
							'woff'	=> 'font/woff',
							'svg'	=> 'image/svg+xml',
							'ttf'	=> 'font/ttf'
						);
						if (in_array($format['format'], array_keys($mime_format)) && !in_array(esc_url_raw($format['url']), $preloaded)) {
							header('Link: <'.esc_url_raw($format['url']).($format['format']=='eot' ? '?#iefix' : ($format['format']!='woff2' ? '?v='.SIMPLE_FONTS_LOADER_VERSION : '')).'>; rel="preload"; as="font"; type="'.esc_attr($mime_format[$format['format']]).'", crossorigin=""');
							$preloaded[] = esc_url_raw($format['url']);
						}
					}
				}
			}
		}
	}

	SFLFront::getInstance();
}