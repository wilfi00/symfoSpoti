/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// any CSS you require will output into a single css file (app.css in this case)
require('../css/app.scss');
require('../css/nice-select.scss');

// Need jQuery? Install it with "yarn add jquery", then uncomment to require it.
jQuery = $ = require('jquery');

require('bootstrap');
require('../js/select.js');
require('../js/jquery.nice-select.min.js');

$(document).ready(function() {
	$('select').niceSelect();
});
global.artistManager = function(config) {

};
global.genreManager = function(config) {

};
// global.artistManager = function(config) {
// 	var sidebarSelection = $('.sidebar-left');
// 	addEvents();

// 	function addEvents()
// 	{
// 		// Ajout d'un artiste à la sélection
// 		$('.search-result .artistBloc').each(function() {
// 			$(this).off('click').on('click', function() {
// 				addArtistToSelection($(this));
// 			});
// 		});

// 		// Suppression de l'artiste de la sélection
// 		$('.sidebar-left .removeArtist').each(function() {
// 			$(this).off('click').on('click', function() {
// 				removeArtistToSelection($(this).parent());
// 			});
// 		});

// 		// Supprime toute la sélection
// 		$('.sidebar-left .removeAll').off('click').on('click', function() {
// 			removeAllSelection();
// 		});
// 	}

// 	function addArtistToSelection(artist)
// 	{
// 		artist.clone().appendTo(sidebarSelection);
// 		addEvents();
// 		artist.css('pointer-events', 'none');
// 		$.post(config.addArtistToSelectionUrl, JSON.stringify(artist.data().information));
// 	}

// 	function removeArtistToSelection(artist)
// 	{
// 		artist.remove();
// 		$.post(config.removeArtistToSelectionUrl, artist.data().information.id);
// 	}

// 	function removeAllSelection()
// 	{
// 		$.get(config.removeAllSelectionUrl);
// 		$('.sidebar-left .artistBloc').each(function() {
// 			$(this).remove();
// 		});
// 	}

// 	$('#search-form').submit(function(event) {
// 		var result = $('.search-result');
// 		result.hide();
// 		showLoader();
// 		event.preventDefault(); //prevent default action
// 		var url           = $(this).attr("action"); //get form action url
// 		var requestMethod = $(this).attr("method"); //get form GET/POST method
// 		var data          = $(this).serialize(); //Encode form elements for submission

// 		$.ajax({
// 			url : url,
// 			type: requestMethod,
// 			data : data
// 		}).done(function(response) {
// 			result.html(response);
// 			hideLoader();
// 			result.show();
// 			addEvents();
// 		});
// 	});

// 	var typingTimer; // Timer
// 	var doneTypingInterval = 100;  // On laisse une seconde
// 	$('#search-form :input').each(function() {
// 	    var input = $("#" + this.id);
// 	    input.on('keyup', function () {
// 	        clearTimeout(typingTimer);
// 	        typingTimer = setTimeout(function() {
// 				$('#search-form').submit();
// 			}, doneTypingInterval);
// 	    });
// 	    input.on('keydown', function () {
// 			clearTimeout(typingTimer);
// 	    });
// 	});
// };

// global.genreManager = function(config) {
// 	addEvents();

// 	function addEvents()
// 	{
// 		console.log('addevents');
// 		// Champ de recherche
// 		addInputSearchEvent();

// 		// Sélection d'un genre
// 		$('.genreResult .genre').each(function() {
// 			$(this).off('click').on('click', function() {
// 				$('.selection').show();
// 				addGenreToSelection($(this));
// 			});
// 		});

// 		// Bouton de génération de la playlist
// 		$('.generate').off('click').on('click', function() {
// 			generatePlaylist();
// 		});

// 		// Bouton pour sauvegarder les tracks dans une playlistResult
// 		$('.saveIntoPlaylist').off('click').on('click', function() {
// 			saveIntoPlaylist();
// 		});

// 		$('.inputSearchGenre').off('focusin').on('focusin', function() {
// 			$('.genreResult').css('height', '220px');
// 			// $('.selection').hide();
// 		});
// 		$('.inputSearchGenre').off('focusout').on('focusout', function() {
// 			$('.genreResult').css('height', '0');
// 			$('.selection').show();
// 		});
// 	}

// 	function addInputSearchEvent()
// 	{
// 		var typingTimer; // Timer
// 		var doneTypingInterval = 100;  // On laisse une seconde
// 		var input = $('.inputSearchGenre');

// 	    input.off('click').on('click').off('keyup').on('keyup', function () {
// 	        clearTimeout(typingTimer);
// 	        typingTimer = setTimeout(function() {
// 				$.post(config.searchGenreUrl, JSON.stringify(input.val().split(' ')), function(jsonGenres) {
// 					displayResultGenres(jsonGenres);
// 				});
// 			}, doneTypingInterval);
// 	    });
// 	    input.off('keydown').on('keydown', function () {
// 			clearTimeout(typingTimer);
// 	    });
// 	}

// 	function displayResultGenres(genres)
// 	{
// 		cleanResults();
// 		var htmlResult = $('.genreResult');
// 		genres.forEach(function(genre) {
// 			$('<li class="genre" data-name="' + genre.name + '">' + genre.name + '</li>').appendTo(htmlResult)
// 		});
// 		addEvents();
// 	}

// 	function cleanResults()
// 	{
// 		$('.genreResult .genre').each(function() {
// 			$(this).remove();
// 		});
// 	}

// 	function addGenreToSelection(genre)
// 	{
// 		console.log('addGenreToSelection');
// 		var test = '<button type="button" class="close" aria-label="Close"><span aria-hidden="true">&times;</span></button>';
// 		genre.clone().append(test).appendTo($('.selection'));

// 		$('.selection .genre').each(function() {
// 			var genre = $(this);
// 			$(this).find('.close').off('click').on('click', function() {
// 				genre.remove();
// 			});
// 		});
// 	}

// 	function generatePlaylist()
// 	{
// 		var result = $('.playlistResult');
// 		result.hide();
// 		showLoader();
// 		$.ajax({
// 			url : config.generatePlaylistUrl,
// 			type: 'POST',
// 			data : JSON.stringify(getSelectedGenres())
// 		}).done(function(response) {
// 			result.html(response);
// 			hideLoader();
// 			result.show();
// 			// addEvents();
// 		});
// 	}

// 	function getSelectedGenres()
// 	{
// 		var genres = [];
// 		$('.selection .genre').each(function() {
// 		  genres.push($(this).data('name'));
// 		});

// 		return genres;
// 	}

// 	function saveIntoPlaylist()
// 	{
// 		var playlistName = $('.playlistName').val();
// 		var tracks = [];
// 		$('.playlistResult .trackBlock').each(function() {
// 			tracks.push($(this).data('id'));
// 		});

// 		$.post(config.saveIntoPlaylistUrl, {'name': playlistName, 'tracks': tracks}, function() {
// 			// displayResultGenres(jsonGenres);
// 		});
// 	}
// };

// $('#modalePlaylists .btn-primary').on('click', function() {
// 	$('form[name="playlist_selection"]').submit();
// });

// function showLoader()
// {
// 	$('#loader').show();
// }
// function hideLoader()
// {
// 	$('#loader').hide();
// }
