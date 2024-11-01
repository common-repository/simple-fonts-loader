(function($) {

	$(document).ready(function() {

		var $cards = $('#simple-fonts-loader-cards').isotope({
			itemSelector: ".simple-fonts-loader-card",
			layoutMode: "masonry",
			masonry: {
				gutter: 20
			}
		});
		
		var sfl_card_filter = '*';

		$(document).on('change', 'input[name="fonts_activated[]"]', function() {

			let ajax_action = ($(this).is(':checked')) ? 'simple_fonts_loader_activate_font' : 'simple_fonts_loader_deactivate_font';

			if (ajax_action=='simple_fonts_loader_activate_font') {
				$(this).closest('.simple-fonts-loader-font-item').find('input[name="fonts_favorites[]"]').attr('style', 'display:inline-block');
				$(this).closest('.simple-fonts-loader-card').addClass('simple-fonts-loader-card-active');
			} else {
				$(this).closest('.simple-fonts-loader-font-item').find('input[name="fonts_favorites[]"]').attr('style', 'display:none');
				all_disabled = true;
				$(this).closest('.simple-fonts-loader-card').find('input[name="fonts_activated[]"]').each(function() {
					if ($(this).is(':checked')) all_disabled = false;
				});
				if (all_disabled) {
					$(this).closest('.simple-fonts-loader-card').removeClass('simple-fonts-loader-card-active');
					if (sfl_card_filter!='*') {
						$(this).closest('.simple-fonts-loader-card').addClass('loading');
						that = this;
						setTimeout(function() {
							$cards.isotope({ filter: sfl_card_filter });
							setTimeout(function() {
								$(that).closest('.simple-fonts-loader-card').removeClass('loading');
							}, 500);
						}, 1000);
					};
					
				}
			}

			$.ajax({
				url : adminAjax, // à adapter selon la ressource
				method : 'POST', // GET par défaut
				data : {
					action : ajax_action,
					font : $(this).val()
				},
				error : function( data ) {
					if (ajax_action=='simple_fonts_loader_activate_font') {
						$(this).prop('checked', false);
					} else {
						$(this).prop('checked', true);
					}
				}
			});
		});

		$(document).on('change', 'input[name="fonts_favorites[]"]', function() {

			let ajax_action = ($(this).is(':checked')) ? 'simple_fonts_loader_favorite_font' : 'simple_fonts_loader_unfavorite_font';

			$.ajax({
				url : adminAjax, // à adapter selon la ressource
				method : 'POST', // GET par défaut
				data : {
					action : ajax_action,
					font : $(this).val()
				},
				error : function( data ) {
					if (ajax_action=='simple_fonts_loader_favorite_font') {
						$(this).prop('checked', false);
					} else {
						$(this).prop('checked', true);
					}
				}
			});
		});

		$(document).on('click', '#simple-fonts-loader-filter-all', function(e) {
			e.preventDefault();
			sfl_card_filter = '*';
			$cards.isotope({ filter: sfl_card_filter });
			$(this).parent().find('.nav-tab-active').removeClass('nav-tab-active');
			$(this).addClass('nav-tab-active');
			return false;
		});

		$(document).on('click', '#simple-fonts-loader-filter-actives', function(e) {
			e.preventDefault();
			sfl_card_filter = '.simple-fonts-loader-card-active';
			$cards.isotope({ filter: sfl_card_filter });
			$(this).parent().find('.nav-tab-active').removeClass('nav-tab-active');
			$(this).addClass('nav-tab-active');
			return false;
		});
	});

})(jQuery);