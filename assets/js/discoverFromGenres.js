/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// Discover
global.genreManager = function(config) {
	setTimeout(function() {
		var genres                   = config.genres;
		const generateButton         = $('.generate');
		const saveIntoPlaylistButton = $('.saveAction');
	
		init();
		addEvents();
		manageSaveChoice();
	
		function init()
		{
			// Désactivation par défaut des boutons :)
			saveIntoPlaylistButton.prop('disabled', true);
	
			if (config.success === '1') {
				feedbackSuccess(config.text.playlistSaveSucessFeedback);
				// Nettoyage de l'url
				window.history.replaceState({}, document.title, location.protocol + "//" + location.host + location.pathname);
			} else if (config.success === '0') {
				feedbackError(config.text.feedbackError);
				// Nettoyage de l'url
				window.history.replaceState({}, document.title, location.protocol + "//" + location.host + location.pathname);
			}
			
			// Initialisation de la recherche de genres
			searchGenres(genres);
		}
	
		function addEvents()
		{
			// Bouton de génération de la playlist
			generateButton.off('click').on('click', function() {
				generatePlaylist();
			});
			
			$('#saveAction').off('submit').on('submit', function(event) {
				saveAction(event);
			});
	
			// Popover sur le bouton de génération de playlist
			generateButton.mouseenter(function() {
				if (generateButton.is(':disabled')) {
					$(this).popover('show');
					$('.inputSearchGenre').addClass('hover');
					setTimeout(function() {
						$('.inputSearchGenre').removeClass('hover');
					}, 650);
				} else {
					$(this).popover('hide');
				}
			}).mouseleave(function() {
				$(this).popover('hide');
			});
			
	
			// Popover sur le bouton d'enregistrement de la playlist dans spotfiy
			saveIntoPlaylistButton.mouseenter(function() {
				if (saveIntoPlaylistButton.is(':disabled')) {
					$(this).popover('show');
				} else {
					$(this).popover('hide');
				}
			}).mouseleave(function() {
				$(this).popover('hide');
			});
		}
	
		function generatePlaylist()
		{
			var result = $('.playlistResult');
			result.hide();
			showLoader();
	
			var data = {};
			data['genres']  = getSelectedGenres();
			data['nbSongs'] = $('#nbTracks').val();
	
			$.ajax({
				url : config.generatePlaylistUrl,
				type: 'POST',
				data : JSON.stringify(data)
			}).done(function(response) {
				result.html(response);
				hideLoader();
				result.show();
				saveIntoPlaylistButton.prop('disabled', false);
			}).fail(function(response) {
				hideLoader();
				feedbackError(config.text.feedbackError);
			});
		}
	
		function getSelectedGenres()
		{
			var genres = [];
			$('.selection .genre').each(function() {
			  genres.push($(this).data('id'));
			});
	
			return genres;
		}
	
		function saveAction(event)
		{
			var tracks = [];
			$('.playlistResult .trackBlock').each(function() {
				tracks.push($(this).data('id'));
			});
	
			$('#saveAction').append('<input type="hidden" name="tracks" value=\'' + JSON.stringify(tracks) + '\'>');
		}
	}, 100);
};

$('#modalePlaylists .btn-primary').on('click', function() {
	$('form[name="playlist_selection"]').submit();
});
