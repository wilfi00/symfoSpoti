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
$('select:not(.searchSelect)').niceSelect();


$('#modalePlaylists .btn-primary').on('click', function() {
	$('form[name="playlist_selection"]').submit();
});

global.showLoader = function()
{
	$('#loader').show();
};
global.hideLoader = function()
{
	$('#loader').hide();
};
global.feedbackSuccess = function(msg = '')
{
	showFeedback(msg, 'alert-success');
};
global.feedbackError = function(msg)
{
	showFeedback(msg, 'alert-danger');
};

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
global.searchGenres = function(genres, callbackAddGenre = null, callbackDeleteGenre = null) {
	addInputSearchEvent(genres);
	
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
				var genres0 = genres.filter(genre => genre.name == input.val());
				
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
				app.activeVueGenres = genres0.concat(genres1).concat(genres2).concat(genres3).unique();
				addEventToGenre(callbackAddGenre, callbackDeleteGenre);
		   }, doneTypingInterval);
	    });
	    input.off('keydown').on('keydown', function () {
			clearTimeout(typingTimer);
	    });
	}
	
	function addEventToGenre()
	{
		$('.genreResult .genre').off('click').on('click', function() {
			addGenreToSelection($(this));
		});
	}
	
	function addGenreToSelection(genre)
	{
		$('.selection').show();

		// A l'ajout d'un genre on nettoie la barre de recherche et on active le bouton de génération de playlist
		$('.inputSearchGenre').val("");
	}
}

// Changement de langue
global.changeLanguage = function(defaultLanguage)
{
	var select = $('#changeLanguage select');
	
	$('#changeLanguage').find('.nice-select li').removeClass('selected focus');

	// Langage courant
	if (defaultLanguage === 'en') {
		// Html select
		var option = select.find('option[data-lang="en"]');
		option.attr('selected', true);
		// Nice select
		$('#changeLanguage').find('.nice-select .current').html(option.html());
	} else {
		// Html select
		var option = select.find('option[data-lang="fr"]');
		option.attr('selected', true);
		// Nice select
		$('#changeLanguage').find('.nice-select .current').html(option.html());
	}

	// Si on sélectionne une langue, on recharge la page avec la langue choisie
	select.change(function(eventData) {
		window.location.replace(eventData.currentTarget.value);
	});
}

//fonction scroll top
$('#link').click(function(e){
	window.scrollTo({
	  top: 0,
	  behavior: 'smooth'
	});
});