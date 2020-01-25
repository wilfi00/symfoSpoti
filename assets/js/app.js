/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// any CSS you require will output into a single css file (app.css in this case)
require('../css/app.scss');

// Need jQuery? Install it with "yarn add jquery", then uncomment to require it.
const $ = require('jquery');
require('bootstrap');

global.artistManager = function(config) {
	var sidebarSelection = $('.sidebar-left');
	addEvents();
	console.log(config.addArtistToSelectionUrl);
	console.log(config.prout);

	function addEvents()
	{
		$('.search-result .artistBloc').each(function() {
			$(this).off('click').on('click', function() {
				addArtistToSelection($(this));
			});
		})
	}

	function addArtistToSelection(artist)
	{
		artist.clone().appendTo(sidebarSelection);
		$.post(config.addArtistToSelectionUrl, JSON.stringify(artist.data().information));
	}
}
$('#modalePlaylists .btn-primary').on('click', function() {
	$('form[name="playlist_selection"]').submit();
});
