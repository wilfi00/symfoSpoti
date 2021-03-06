/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// Artistes follow
global.artistFollowManager = function(genres, success, text) {
	setTimeout(function() {
		// Search genres
		searchGenres(
			genres, 
			true,
		);
		manageSaveChoice();
		manageFeedback(success, text.playlistSaveSucessFeedback, text.feedbackError);
		
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
		addPopover('.artistBloc .picto-info');
		
		$('#saveAction').off('submit').on('submit', function(event) {
			saveAction();
		});
		
		function saveAction()
		{
			$('#saveAction').append('<input type="hidden" name="nbTracks" value="' + $('#nbTracks').val() + '">');
			$('#saveAction').append(
				'<input type="hidden" name="artists" value=\'' 
				+ JSON.stringify(app.getIdActiveArtists()) 
				+ '\'>'
			);
		}
	}, 100);
};

