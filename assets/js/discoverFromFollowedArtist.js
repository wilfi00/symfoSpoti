/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// Artistes follow
global.artistFollowManager = function(genres) {
	setTimeout(function() {
		// Search genres
		searchGenres(
			genres, 
			null,
			function(genre) {
				app.deleteSelectedGenre(genre.data('name'));
			}
		);
		
		var displayLink = $('.displayAll');
		var hideLink    = $('.hideAll');
		var artists     = $('.artistsFollowed');
		// Liens afficher/cacher tous les artistes
		displayLink.click(function() {
		  artists.addClass('displayAll');
		  $(this).addClass('d-none');
		  hideLink.removeClass('d-none');
		});
		hideLink.click(function() {
		  artists.removeClass('displayAll');
		  $(this).addClass('d-none');
		  displayLink.removeClass('d-none');
		});
		
		// Popover artistes genres
		$('.artistBloc .picto-info').popover({ 
			trigger: 'hover', 
			html: true,
			content: function() {
				return $(this).parent().find('.popover-content').html();
			}
		});
	}, 100);
};
