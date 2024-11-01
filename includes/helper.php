<?php

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) exit;


function register_font_family(string $slug, string $label = '', string $category = 'sans-serif', bool $variable = false) {
	SFL\Fonts\SFLFonts::registerFontFamily($slug, $label, $category, $variable);
}

function register_font(string $family, string $url, int $weight = 400, string $variant = 'normal', string $stretch = 'normal', string $format = 'woff2') {
	SFL\Fonts\SFLFonts::registerFont($family, $url, $weight, $variant, $stretch, $format);
}

function activate_font(string $family, int $weight = 400, string $variant = 'normal') {
	SFL\Fonts\SFLFonts::activateFont($family.':'.$weight.$variant);
}


