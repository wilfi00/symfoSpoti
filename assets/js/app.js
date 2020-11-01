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

$('#modalePlaylists .btn-primary').on('click', function() {
	$('form[name="playlist_selection"]').submit();
});

global.showLoader = function()
{
	$('#loader').show();
}
global.hideLoader = function()
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

Array.prototype.remove = function() {
    var what, a = arguments, L = a.length, ax;
    while (L && this.length) {
        what = a[--L];
        while ((ax = this.indexOf(what)) !== -1) {
            this.splice(ax, 1);
        }
    }
    return this;
};

// Gestion du champ de recherche de genres
global.searchGenres = function(genres, callbackAddGenre = null, callbackLastGenre = null) {
	addSearchGenresEvents(genres);
	
	function addSearchGenresEvents(genres)
	{
		// Champ de recherche
		addInputSearchEvent(genres);
		// Sélection d'un genre
		$('.genreResult .genre').each(function() {
			$(this).off('click').on('click', function() {
				$('.selection').show();
				addGenreToSelection($(this), callbackAddGenre, callbackLastGenre);
			});
		});
	}
	
	function addInputSearchEvent(genres)
	{
		var typingTimer; // Timer
		var doneTypingInterval = 10;
		var input = $('.inputSearchGenre');
		input.off('focusout').on('focusout', function() {
			$('.genreResult').css('height', '0');
			$('.selection').show();
		});
	
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

	function addGenreToSelection(genre, callbackAddGenre = null, callbackLastGenre = null)
	{
		var test = '<button type="button" class="close" aria-label="Close"><span aria-hidden="true">&times;</span></button>';
		genre.clone().append(test).appendTo($('.selection'));
		genre.remove();
		// Ajout de l'event pour supprimer un genre
		$('.selection .genre').each(function() {
			var genre = $(this);
			$(this).find('.close').off('click').on('click', function() {
				genre.remove();
				if (callbackLastGenre instanceof Function) {
					callbackLastGenre();
				}
			});
		});

		// A l'ajout d'un genre on nettoie la barre de recherche et on active le bouton de génération de playlist
		$('.inputSearchGenre').val("");
		if (callbackAddGenre !== null) {
			callbackAddGenre();
		}
		const index = genres.indexOf(5);
	}
}