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

	function addEvents()
	{
		// Ajout d'un artiste à la sélection
		$('.search-result .artistBloc').each(function() {
			$(this).off('click').on('click', function() {
				addArtistToSelection($(this));
			});
		});

		// Suppression de l'artiste de la sélection
		$('.sidebar-left .removeArtist').each(function() {
			$(this).off('click').on('click', function() {
				removeArtistToSelection($(this).parent());
			});
		});

		// Supprime toute la sélection
		$('.sidebar-left .removeAll').off('click').on('click', function() {
			removeAllSelection();
		});
	}

	function addArtistToSelection(artist)
	{
		artist.clone().appendTo(sidebarSelection);
		addEvents();
		artist.css('pointer-events', 'none');
		$.post(config.addArtistToSelectionUrl, JSON.stringify(artist.data().information));
	}

	function removeArtistToSelection(artist)
	{
		artist.remove();
		$.post(config.removeArtistToSelectionUrl, artist.data().information.id);
	}

	function removeAllSelection()
	{
		$.get(config.removeAllSelectionUrl);
		$('.sidebar-left .artistBloc').each(function() {
			$(this).remove();
		});
	}

	$('#search-form').submit(function(event) {
		var result = $('.search-result');
		result.hide();
		showLoader();
		event.preventDefault(); //prevent default action
		var url           = $(this).attr("action"); //get form action url
		var requestMethod = $(this).attr("method"); //get form GET/POST method
		var data          = $(this).serialize(); //Encode form elements for submission

		$.ajax({
			url : url,
			type: requestMethod,
			data : data
		}).done(function(response) {
			result.html(response);
			hideLoader();
			result.show();
			addEvents();
		});
	});

	var typingTimer; // Timer
	var doneTypingInterval = 100;  // On laisse une seconde
	$('#search-form :input').each(function() {
	    var input = $("#" + this.id);
	    input.on('keyup', function () {
	        clearTimeout(typingTimer);
	        typingTimer = setTimeout(function() {
				$('#search-form').submit();
			}, doneTypingInterval);
	    });
	    input.on('keydown', function () {
			clearTimeout(typingTimer);
	    });
	});
};

global.genreManager = function(config) {
	console.log(config.searchGenreUrl);
	addInputSearchEvent();



	function addInputSearchEvent()
	{
		var typingTimer; // Timer
		var doneTypingInterval = 100;  // On laisse une seconde
		var input = $('.inputSearchGenre');

	    input.on('keyup', function () {
	        clearTimeout(typingTimer);
	        typingTimer = setTimeout(function() {
				$.post(config.searchGenreUrl, JSON.stringify(input.val().split(' ')), function(jsonGenres) {
					console.log(jsonGenres);
					displayResult(jsonGenres);
				});
			}, doneTypingInterval);
	    });
	    input.on('keydown', function () {
			clearTimeout(typingTimer);
	    });
	}

	function displayResult(genres)
	{
		cleanResults();
		var htmlResult = $('.result');
		genres.forEach(function(genre) {
			htmlResult.append('<span class="genre">' + genre.name + '</span>');
		});
			// htmlResult.append('<span class="genre">' + genre.name + '</span>');
		// }
	}

	function cleanResults()
	{
		$('.result .genre').each(function() {
			$(this).remove();
		});
	}
};

$('#modalePlaylists .btn-primary').on('click', function() {
	$('form[name="playlist_selection"]').submit();
});

function showLoader()
{
	$('#loader').show();
}
function hideLoader()
{
	$('#loader').hide();
}
