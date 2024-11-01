<?php

declare(strict_types=1);

namespace SFL\Admin;

if ( ! defined( 'ABSPATH' ) ) exit;



if (!class_exists('\SFL\Admin\SFLAdmin')) {

	/**
	* 
	*/
	class SFLAdmin {
		
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
			if(is_null(self::$_instance)) self::$_instance = new SFLAdmin();
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
		
		/**
		* Crochets Wordpress
		*
		* @param void
		* @return void
		*/
		public function addHooks() {
			add_action( 'admin_menu', array($this, 'addAdminPage') );
			add_action( 'admin_enqueue_scripts', array($this, 'addAdminScripts') );
			add_action( 'wp_ajax_simple_fonts_loader_activate_font', array($this, 'activateFont') );
			add_action( 'wp_ajax_simple_fonts_loader_deactivate_font', array($this, 'deactivateFont') );
			add_action( 'wp_ajax_simple_fonts_loader_favorite_font', array($this, 'favoriteFont') );
			add_action( 'wp_ajax_simple_fonts_loader_unfavorite_font', array($this, 'unfavoriteFont') );
		}

		public function addAdminPage() {
			add_submenu_page('themes.php', __( 'Polices d\'écritures', 'simple-fonts-loader' ), __( 'Polices d\'écritures', 'simple-fonts-loader' ), 'manage_options', 'simple-fonts-loader-settings', array($this, 'adminPageCallback'));
		}

		public function adminPageCallback() {

			if ( ! current_user_can( 'manage_options' ) ) { wp_die('Access denied'); }

			?><div class="wrap" id="simple-fonts-loader-settings">
				<h1><?php echo get_admin_page_title(); ?></h1>
				<p><?php _e('Paramétrez ici les police d\'écritures que vous souhaitez utiliser sur le site.', 'simple-fonts-loader'); ?></p>
				<h2 class="nav-tab-wrapper">
					<a class="nav-tab <?php if (!isset($_GET['actives'])) { echo 'nav-tab-active'; } ?>" id="simple-fonts-loader-filter-all" href="<?php echo esc_url( add_query_arg( array('page'=>'simple-fonts-loader-settings'), get_admin_url().'themes.php' ) ); ?>"><?php _e('Toutes', 'simple-fonts-loader'); ?></a>
					<a class="nav-tab <?php if (isset($_GET['actives'])) { echo 'nav-tab-active'; } ?>" id="simple-fonts-loader-filter-actives" href="<?php echo esc_url( add_query_arg( array('page'=>'simple-fonts-loader-settings', 'actives'=>1), get_admin_url().'themes.php' ) ); ?>"><?php _e('Actives', 'simple-fonts-loader'); ?></a>
				</h2>
				<div id="simple-fonts-loader-cards"><?php
					$typefaces = \SFL\Fonts\SFLFonts::getFonts();
					$activated = \SFL\Fonts\SFLFonts::getFontsActivated();
					$favorites = \SFL\Fonts\SFLFonts::getFontsFavorites();
					$weight_pretty = array(
						'100' => __( 'Thin', 'simple-fonts-loader' ),
						'200' => __( 'Extra-light', 'simple-fonts-loader' ),
						'300' => __( 'Light', 'simple-fonts-loader' ),
						'400' => __( 'Regular', 'simple-fonts-loader' ),
						'500' => __( 'Medium', 'simple-fonts-loader' ),
						'600' => __( 'Semi-Bold', 'simple-fonts-loader' ),
						'700' => __( 'Bold', 'simple-fonts-loader' ),
						'800' => __( 'Extra-Bold', 'simple-fonts-loader' ),
						'900' => __( 'Black', 'simple-fonts-loader' ),
						'1000' => __( 'Extra-Black', 'simple-fonts-loader' ),
						'1100' => __( 'Ultra-Black', 'simple-fonts-loader' ),
						'1200' => __( 'Vanta-Black', 'simple-fonts-loader' )
					);
					$convert_weight_to_pretty = apply_filters( 'sfl-admin-prety-weight', true );

					foreach ($typefaces as $slug => $fonts) {
						$property_copy = 'font-family: "' . ((isset($fonts['label'])) ? esc_html($fonts['label']) : esc_html(esc_attr($slug))) . '", ' . esc_html($fonts['category']) . ';';
						
						$family_active = false;
						foreach ($fonts['fonts'] as $_font_key => $_font) {
							if (in_array($slug.':'.$_font_key, array_keys($activated))) {
								$family_active = true;
								break;
							}
						}
						
						?><div class="card simple-fonts-loader-card <?php if ($family_active) { echo 'simple-fonts-loader-card-active'; } ?>" id="<?php echo esc_attr($slug); ?>">
							<h2 class="title">
								<?php if (isset($fonts['variable']) && $fonts['variable']) {
									?><svg viewBox="0 0 1000 1000" class="icon-variable">
										<title><?php _e('Police variable', 'simple-fonts-loader'); ?></title>
										<path d="M260.982,775.997c9.594,7.656,30.018,12.625,62.018,14.906V809H114v-18.097c28-4.875,45.756-11.21,53.912-19.038c16.594-15.641,35.052-47.398,55.24-95.305L456.495,131h16.453l141.46,338.467l-70.046,69.971L437.933,284.374L323.199,552h208.667l-36.021,36H307.824l-44.391,103.764c-11.063,25.734-16.719,44.905-16.719,57.608C246.714,759.466,251.389,768.341,260.982,775.997z M705.346,687.01L583.134,809H817v-18.097c-27-1.625-48.01-9.007-63.135-22.194C739.246,755.977,723.139,728.756,705.346,687.01z M730.88,353l57.746,57.794L426.145,772.898L368,715.179V869h154.904l-56.881-56.869l361.959-361.937L885,507.203V353H730.88z"/>
									</svg><?php
								}
								echo (isset($fonts['label'])) ? esc_html($fonts['label']) : esc_html(esc_attr($slug)) ; ?>
								<span onclick="var that=this;navigator.clipboard.writeText('<?php echo esc_attr($property_copy); ?>').then(function() { that.nextElementSibling.classList.add('show'); setTimeout(function() { that.nextElementSibling.classList.remove('show'); }, 3000); }, function() { that.nextElementSibling.nextElementSibling.classList.add('show'); setTimeout(function() { that.nextElementSibling.nextElementSibling.classList.remove('show'); }, 3000); });">
									<svg version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:a="http://ns.adobe.com/AdobeSVGViewerExtensions/3.0/" x="0px" y="0px" width="22px" height="22px" viewBox="-149.717 -154.5 1000 1000" enable-background="new -149.717 -154.5 1000 1000" xml:space="preserve">
										<path fill="#8B8F94" d="M689.283,212.778c0-33.738-26.294-60.278-60.033-60.278H212.779c-33.739,0-61.496,26.54-61.496,60.278v416.471c0,33.74,27.757,62.251,61.496,62.251H629.25c33.739,0,60.033-28.511,60.033-62.251V212.778z M625.283,627.5h-410v-410h410V627.5z"/>
										<path fill="#8B8F94" d="M479.529,0H61.09C27.351,0,0,27.351,0,61.09v418.751c0,17.853,14.472,32.323,32.324,32.323c17.853,0,32.325-14.471,32.325-32.323V64.648h414.88c17.852,0,32.324-14.472,32.324-32.324S497.38,0,479.529,0z"/>
									</svg>
								</span>
								<span class="success_copy"><?php _e('Copiée !', 'simple-fonts-loader'); ?></span>
								<span class="error_copy"><?php _e('Erreur.', 'simple-fonts-loader'); ?></span>
							</h2>
							<br class="clear">
							<div class="inside"><?php
								
								$fonts = $fonts['fonts'];
								ksort($fonts);

								foreach ($fonts as $font_key => $font) {

									?><div class="simple-fonts-loader-font-item">
										<label>
											<h4>
												<input type="checkbox" name="fonts_activated[]" value="<?php echo esc_attr($slug.':'.$font_key); ?>" <?php checked(in_array($slug.':'.$font_key, array_keys($activated))); ?>/> <?php
												echo ( ($convert_weight_to_pretty && isset($weight_pretty[strval(esc_attr($font['weight']))])) ? $weight_pretty[strval(esc_attr($font['weight']))] : esc_html($font['weight']) ).' '.ucfirst(strtolower(esc_html($font['variant']))).((isset($font['stretch']) && strtolower(esc_html($font['stretch']))!='normal') ? ' '.ucfirst(strtolower(esc_html($font['stretch']))) : '' );
											?></h4>
										</label><!--
										--><code><?php
											printf('font-weight: %s;', esc_html($font['weight']));
											echo (strtolower(esc_html($font['variant']))!='normal') ? '<br/>' . sprintf('font-style: %s;', strtolower(esc_html($font['variant']))) : '';
											echo (isset($font['stretch'])&&strtolower(esc_html($font['stretch']))!='normal') ? '<br/>' . sprintf('font-stretch: %s;', strtolower(esc_html($font['stretch']))) : '';
											$styles_copy = 'font-weight: ' . esc_html($font['weight']) . ';';
											$styles_copy .= (strtolower(esc_html($font['variant']))!='normal') ? '\r\n\tfont-style: ' . strtolower(esc_html($font['variant'])) . ';' : '';
											$styles_copy .= (isset($font['stretch']) && strtolower(esc_html($font['stretch']))!='normal') ? '\r\n\tfont-stretch: ' . strtolower(esc_html($font['stretch'])) . ';' : '';
											?><span onclick="var that=this;navigator.clipboard.writeText('<?php echo esc_attr($styles_copy); ?>').then(function() { that.nextElementSibling.classList.add('show'); setTimeout(function() { that.nextElementSibling.classList.remove('show'); }, 3000); }, function() { that.nextElementSibling.nextElementSibling.classList.add('show'); setTimeout(function() { that.nextElementSibling.nextElementSibling.classList.remove('show'); }, 3000); });">
												<svg version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:a="http://ns.adobe.com/AdobeSVGViewerExtensions/3.0/" x="0px" y="0px" width="22px" height="22px" viewBox="-149.717 -154.5 1000 1000" enable-background="new -149.717 -154.5 1000 1000" xml:space="preserve">
													<path fill="#8B8F94" d="M689.283,212.778c0-33.738-26.294-60.278-60.033-60.278H212.779c-33.739,0-61.496,26.54-61.496,60.278v416.471c0,33.74,27.757,62.251,61.496,62.251H629.25c33.739,0,60.033-28.511,60.033-62.251V212.778z M625.283,627.5h-410v-410h410V627.5z"/>
													<path fill="#8B8F94" d="M479.529,0H61.09C27.351,0,0,27.351,0,61.09v418.751c0,17.853,14.472,32.323,32.324,32.323c17.853,0,32.325-14.471,32.325-32.323V64.648h414.88c17.852,0,32.324-14.472,32.324-32.324S497.38,0,479.529,0z"/>
												</svg>
											</span>
											<span class="success_copy"><?php _e('Copiée !', 'simple-fonts-loader'); ?></span>
											<span class="error_copy"><?php _e('Erreur.', 'simple-fonts-loader'); ?></span>
										</code>
										<input type="checkbox" name="fonts_favorites[]" value="<?php echo esc_attr($slug.':'.$font_key); ?>" <?php checked(in_array($slug.':'.$font_key, array_keys($favorites))); ?> <?php if (in_array($slug.':'.$font_key, array_keys($activated))) { echo 'style="display:inline-block"'; } ?>/>
									</div><?php
								}
							?></div>
						</div><?php
					}
				?></div>
			</div><?php

		}

		public function addAdminScripts() {
			wp_enqueue_style( 'simple-fonts-loader-style', plugins_url( 'assets/css/admin.css', SIMPLE_FONTS_LOADER_FILE ) );
			wp_enqueue_script( 'simple-fonts-loader-isotope', plugins_url( 'assets/js/isotope.min.js', SIMPLE_FONTS_LOADER_FILE ), array( 'jquery' ) );
			wp_enqueue_script( 'simple-fonts-loader-script', plugins_url( 'assets/js/admin.js', SIMPLE_FONTS_LOADER_FILE ), array( 'jquery', 'simple-fonts-loader-isotope' ) );
			wp_add_inline_script( 'simple-fonts-loader-script', 'const adminAjax = '.json_encode(admin_url( 'admin-ajax.php' )) );
		}

		public function getPostFont() {
			$post_font = sanitize_text_field($_POST['font']);
			$typefaces = \SFL\Fonts\SFLFonts::getFonts();

			foreach ($typefaces as $slug => $fonts) {
				foreach ($fonts['fonts'] as $font_key => $font) {
					if (esc_attr($slug.':'.$font_key)===esc_attr($post_font)) return esc_attr($slug.':'.$font_key);
				}
			}
			return false;
		}

		public function activateFont() {
			$font = $this->getPostFont();
			if ($font) \SFL\Fonts\SFLFonts::activateFont($font);
			wp_send_json_success();
			wp_die();
			die;
		}

		public function deactivateFont() {
			$font = $this->getPostFont();
			if ($font) \SFL\Fonts\SFLFonts::deactivateFont($font);
			wp_send_json_success();
			wp_die();
			die;
		}

		public function favoriteFont() {
			$font = $this->getPostFont();
			if ($font) \SFL\Fonts\SFLFonts::favoriteFont($font);
			wp_send_json_success();
			wp_die();
			die;
		}

		public function unfavoriteFont() {
			$font = $this->getPostFont();
			if ($font) \SFL\Fonts\SFLFonts::unfavoriteFont($font);
			wp_send_json_success();
			wp_die();
			die;
		}
	}

	SFLAdmin::getInstance();
}