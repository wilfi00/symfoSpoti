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
require('../js/jquery.nice-select.min.js');
require('bootstrap');

// Nice select
$('select').niceSelect();

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
	var genres                   = config.genres;
	const generateButton         = $('.generate');
	const saveIntoPlaylistButton = $('.saveIntoPlaylist');

	init();
	addEvents();

	function init()
	{
		// Désactivation par défaut des boutons :)
		saveIntoPlaylistButton.prop('disabled', true);
		generateButton.prop('disabled', true);

		if (config.success === '1') {
			feedbackSuccess(config.text.playlistSaveSucessFeedback);
			// Nettoyage de l'url
			window.history.replaceState({}, document.title, location.protocol + "//" + location.host + location.pathname);
		} else if (config.success === '0') {
			feedbackError(config.text.feedbackError);
			// Nettoyage de l'url
			window.history.replaceState({}, document.title, location.protocol + "//" + location.host + location.pathname);
		}
	}

	function addEvents()
	{
		// Champ de recherche
		addInputSearchEvent();

		// Sélection d'un genre
		$('.genreResult .genre').each(function() {
			$(this).off('click').on('click', function() {
				$('.selection').show();
				addGenreToSelection($(this));
			});
		});

		// Bouton de génération de la playlist
		generateButton.off('click').on('click', function() {
			generatePlaylist();
		});

		$('#saveIntoPlaylist').off('submit').on('submit', function(event) {
			saveIntoPlaylist(event);
		});

		$('.inputSearchGenre').off('focusin').on('focusin', function() {
			// $('.genreResult').css('height', '220px');
		});
		$('.inputSearchGenre').off('focusout').on('focusout', function() {
			$('.genreResult').css('height', '0');
			$('.selection').show();
		});

		// Popover sur le bouton de génération de playlist
		generateButton.hover(
			function() {
				if (generateButton.is(':disabled')) {
					$(this).popover('show');
					$('.inputSearchGenre').addClass('hover');
					setTimeout(function() {
						$('.inputSearchGenre').removeClass('hover');
					}, 650);

				}
			}, function() {
				if (generateButton.is(':disabled')) {
					$(this).popover('hide');
				}
			}
		);
		// Popover sur le bouton d'enregistrement de la playlist dans spotfiy
		saveIntoPlaylistButton.hover(
			function() {
				if (saveIntoPlaylistButton.is(':disabled')) {
					$(this).popover('show');
				}
			}, function() {
				if (saveIntoPlaylistButton.is(':disabled')) {
					$(this).popover('hide');
				}
			}
		);
	}

	function addInputSearchEvent()
	{
		var typingTimer; // Timer
		var doneTypingInterval = 10;
		var input = $('.inputSearchGenre');

	 	input.off('click keyup').on('click keyup', function () {
			$('.genreResult').css('height', '220px');
	        clearTimeout(typingTimer);
			typingTimer = setTimeout(function() {
				// Recherche exact (uk metalcore matchera uk metalcore)
				var regex = '';
				regex +=  '\\b(\\w*' +  $.trim(input.val()) + '\\w*)\\b';
				var genres1 = genres.filter(genre => genre.name.search(regex) >= 0);

				// Recherche inversée exact (exemple, uk metalcore matchera metalcore uk)
				var regex = '';
				regex +=  '\\b(\\w*' +  $.trim(input.val().split(' ').reverse().join(' ')) + '\\w*)\\b';
				var genres2 = genres.filter(genre => genre.name.search(regex) >= 0);

				// Recherche très générale en mode OU (uk metalcore renverra tous les uk et tous les metalcore)
				var regex = '';
				input.val().split(' ').forEach(function(value) {
				   regex += '\\b(\\w*' + value + '\\w*)\\b|';
				});
				// Supression du dernier caractère de la chaine pour enlever le ou |
				regex = regex.substring(0, regex.length - 1);
				var genres3 = genres.filter(genre => genre.name.search(regex) >= 0);

				// On concatène tout et on enlève les genres dupliqués
				displayResultGenres(genres1.concat(genres2).concat(genres3).unique());
		   }, doneTypingInterval);
	    });
	    input.off('keydown').on('keydown', function () {
			clearTimeout(typingTimer);
	    });
	}

	function displayResultGenres(genres)
	{
		if (genres.length === 0) {
			return;
		}
		cleanResults();
		var ids = '';
		genres.forEach(function(genre) {
			ids += '#genre' + genre.id + ', ';
		});
		ids = ids.substring(0, ids.length - 2);
		var jsElements = document.querySelectorAll(ids);
		jsElements.forEach(function(element) {
			element.style.display = '';
		});
	}

	function cleanResults()
	{
		document.querySelectorAll('.genreResult .genre').forEach(function(element) {
			element.style.display = 'none';
		});
	}

	function addGenreToSelection(genre)
	{
		var test = '<button type="button" class="close" aria-label="Close"><span aria-hidden="true">&times;</span></button>';
		genre.clone().append(test).appendTo($('.selection'));
		genre.remove();
		// Ajout de l'event pour supprimer un genre
		$('.selection .genre').each(function() {
			var genre = $(this);
			$(this).find('.close').off('click').on('click', function() {
				genre.remove();

				// Si c'était le dernier genre alors on désactive le bouton de génération de playlist
				if ($('.selection').html() == '') {
					generateButton.prop('disabled', true);
				}
			});
		});

		// A l'ajout d'un genre on nettoie la barre de recherche et on active le bouton de génération de playlist
		$('.inputSearchGenre').val("");
		generateButton.prop('disabled', false);
		const index = genres.indexOf(5);
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

	function saveIntoPlaylist(event)
	{
		var tracks = [];
		$('.playlistResult .trackBlock').each(function() {
			tracks.push($(this).data('id'));
		});

		$('#saveIntoPlaylist').append('<input type="hidden" name="tracks" value=\'' + JSON.stringify(tracks) + '\'>');
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

function feedbackSuccess(msg = '')
{
	showFeedback(msg, 'alert-success');
}
function feedbackError(msg)
{
	showFeedback(msg, 'alert-danger');
}

function showFeedback(msg, classname, size = 220)
{
	var feedback      = $('.feedback');
	var feedbackAlert = feedback.find('.alert');

	feedbackAlert.attr('class', 'alert ' + classname);
	feedbackAlert.html(msg);

	// Permet d'appliquer le width que prendra le message
	feedbackAlert.css('position', 'absolute');
	feedback.width(feedbackAlert.outerWidth());
	feedbackAlert.css('position', 'relative');

	window.setTimeout(function() {
		feedback.width('0');
	}, 3000);
}

Array.prototype.unique = function() {
    var a = this.concat();
    for(var i=0; i<a.length; ++i) {
        for(var j=i+1; j<a.length; ++j) {
            if(a[i] === a[j])
                a.splice(j--, 1);
        }
    }

    return a;
};

// Changement de langue
global.changeLanguage = function(defaultLanguage)
{
	var select = $('#changeLanguage select');

	// Langage courant
	if (defaultLanguage === 'en') {
		// Html select
		var option = select.find('option[value="en"]');
		option.attr('selected', true);
		// Nice select
		$('#changeLanguage').find('.nice-select .current').html(option.html());
		$('#changeLanguage').find('.nice-select li[data-value="en"]').addClass('selected focus');
		$('#changeLanguage').find('.nice-select li[data-value="fr"]').removeClass('selected focus');
	} else {
		// Html select
		var option = select.find('option[value="fr"]');
		option.attr('selected', true);
		// Nice select
		$('#changeLanguage').find('.nice-select .current').html(option.html());
		$('#changeLanguage').find('.nice-select li[data-value="en"]').removeClass('selected focus');
		$('#changeLanguage').find('.nice-select li[data-value="fr"]').addClass('selected focus');
	}

	// Si on sélectionne une langue, on recharge la page avec la langue choisie
	select.change(function(eventData) {
		$('#changeLanguage').submit();
	});
}
