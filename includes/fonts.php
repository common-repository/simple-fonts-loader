<?php

declare(strict_types=1);

namespace SFL\Fonts;

if ( ! defined( 'ABSPATH' ) ) exit;



if (!class_exists('\SFL\Fonts\SFLFonts')) {

	/**
	* 
	*/
	class SFLFonts {
		
		/**
		 * @var Singleton
		 * @access private
		 * @static
		 */
		private static $_instance = null;
		
		/**
		 * @var Listes des fonts
		 * @access protected
		 * @static
		 */
		protected static $fonts = [];
		
		/**
		 * @var Listes des fonts actifs
		 * @access protected
		 * @static
		 */
		protected static $activated = [];
		
		/**
		 * @var Listes des fonts favorites
		 * @access protected
		 * @static
		 */
		protected static $favorites = [];

		/**
		* Méthode qui crée l'unique instance de la classe
		* si elle n'existe pas encore puis la retourne.
		*
		* @param void
		* @return Singleton
		*/
		public static function getInstance() {
			if(is_null(self::$_instance)) self::$_instance = new SFLFonts();
			return self::$_instance;
		}
		
		/**
		* Constructeur de la classe
		*
		* @param void
		* @return void
		*/
		private function __construct() {
			self::$activated = get_option('simple-fonts-loader-activated', array());
			self::$favorites = get_option('simple-fonts-loader-favorites', array());
			$this->addHooks();
		}

		public function addHooks() {
			add_action('init', array($this, 'upgradeVersion'), 1);
			add_action('template_redirect', array($this, 'getOptions'));
		}

		/* Changes due to upgrade version of plugin */
		public function upgradeVersion() {

			/* Add stretch parameter to fonts activated and favorites */
			$all_fonts = self::getFonts();
			$activated = self::getFontsActivated();
			$favorites = self::getFontsFavorites();

			foreach ($all_fonts as $slug => $fonts) {
				foreach ($fonts['fonts'] as $font_key => $font) {
					$old_font_key = $font['weight'].$font['variant'];

					if (in_array($slug.':'.$old_font_key, array_keys($activated))) {
						self::deactivateFont($slug.':'.$old_font_key);
						self::activateFont($slug.':'.$font_key);
					}

					if (in_array($slug.':'.$old_font_key, array_keys($favorites))) {
						self::unfavoriteFont($slug.':'.$old_font_key);
						self::favoriteFont($slug.':'.$font_key);
					}
				}
			}
		}

		public static function getOptions() {
			self::$activated = (array) apply_filters('sfl_fonts_activated', self::$activated);
			self::$favorites = (array) apply_filters('sfl_fonts_favorites', self::$favorites);
		}

		public static function getFonts() {
			return self::$fonts;
		}

		public static function getFontsActivated() {
			return self::$activated;
		}

		public static function getFontsFavorites() {
			return self::$favorites;
		}

		public static function registerFontFamily(string $slug, string $label = '', string $category = 'sans-serif', bool $variable = false) {
			$fonts = self::$fonts;
			if (!isset($fonts[$slug])) {
				$fonts[$slug] = array('label' => ((!empty($label)) ? $label : $slug), 'category' => ((in_array($category, array('serif', 'sans-serif', 'monospace', 'cursive', 'fantasy', 'system-ui', 'emoji', 'math', 'fangsong'))) ? $category : 'sans-serif'), 'variable' => $variable, 'fonts' => array());
				self::$fonts = $fonts;
			}
		}

		public static function registerFont(string $family, string $url, int $weight = 400, string $variant = 'normal', string $stretch = 'normal', string $format = 'woff2') {
			self::registerFontFamily($family);
			$fonts = self::$fonts;
			$variant = (in_array($variant, array('normal', 'italic', 'oblique'))) ? $variant : 'normal' ;
			if (!isset($fonts[$family]['fonts'][$weight.'_'.$variant.'_'.$stretch])) $fonts[$family]['fonts'][$weight.'_'.$variant.'_'.$stretch] = array('weight' => $weight, 'variant' => $variant, 'stretch' => $stretch, 'formats' => array());
			$fonts[$family]['fonts'][$weight.'_'.$variant.'_'.$stretch]['formats'][$format] = $url;
			self::$fonts = $fonts;
		}

		public static function activateFont($font) {
			$activated = self::$activated;
			$activated[$font] = true;
			self::$activated = $activated;
			self::saveActivatedFont();
		}

		public static function deactivateFont($font) {
			$activated = self::$activated;
			if (isset($activated[$font])) unset($activated[$font]);
			self::$activated = $activated;
			self::saveActivatedFont();
		}

		private static function saveActivatedFont() {
			$activated = self::$activated;
			update_option('simple-fonts-loader-activated', $activated, true);
		}

		public static function favoriteFont($font) {
			$favorites = self::$favorites;
			$favorites[$font] = true;
			self::$favorites = $favorites;
			self::saveFavoritesFont();
		}

		public static function unfavoriteFont($font) {
			$favorites = self::$favorites;
			if (isset($favorites[$font])) unset($favorites[$font]);
			self::$favorites = $favorites;
			self::saveFavoritesFont();
		}

		private static function saveFavoritesFont() {
			$favorites = self::$favorites;
			update_option('simple-fonts-loader-favorites', $favorites, true);
		}
	}

	SFLFonts::getInstance();
}