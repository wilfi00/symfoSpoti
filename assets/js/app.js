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
require('popper.js');
require('../js/jquery.nice-select.min.js');
window.bootstrap = require('bootstrap');
require( 'datatables.net' )( window, $ );

require('../img/discovernewmusic.png');

require('@fortawesome/fontawesome-free/css/all.min.css');
require('@fortawesome/fontawesome-free/js/all.js');

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
global.feedbackError = function(msg = '')
{
	if (msg === '') {
		msg = traductions.default_error_msg;
	}
	showFeedback(msg, 'alert-danger');
};

function showFeedback(msg, classname, size = 220)
{
	let feedback      = $('.feedback');
	let feedbackAlert = feedback.find('.alert');

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

global.manageFeedback = function(success, msgSuccess, msgError)
{
	if (success === '1') {
		feedbackSuccess(msgSuccess);
		// Nettoyage de l'url
		window.history.replaceState({}, document.title, location.protocol + "//" + location.host + location.pathname);
	} else if (success === '0') {
		feedbackError(msgError);
		// Nettoyage de l'url
		window.history.replaceState({}, document.title, location.protocol + "//" + location.host + location.pathname);
	}
}

Array.prototype.unique = function() {
	let a = this.concat();
    for(let i=0; i<a.length; ++i) {
        for(let j=i+1; j<a.length; ++j) {
            if(a[i] === a[j])
                a.splice(j--, 1);
        }
    }

    return a;
};

Array.prototype.remove = function() {
	let what, a = arguments, L = a.length, ax;
    while (L && this.length) {
        what = a[--L];
        while ((ax = this.indexOf(what)) !== -1) {
            this.splice(ax, 1);
        }
    }
    return this;
};

// Gestion du champ de recherche de genres
global.searchGenres = function(genres, searchGenreUsingJs = false) {
	addInputSearchEvent(genres);
	
	function addInputSearchEvent(genres)
	{
		let typingTimer; // Timer
		let doneTypingInterval = 10;
		let input = $('.inputSearchGenre');
		input.off('focusout').on('focusout', function() {
			$('.genreResult').css('height', '0');
			$('.selection').show();
		});
	
	 	input.off('click keyup').on('click keyup', function () {
			$('.genreResult').css('height', '220px');
			
			// Code JS pour la recherche de genres en mode JS
			if (searchGenreUsingJs) {
				clearTimeout(typingTimer);
				typingTimer = setTimeout(function() {
					let textSearch = input.val().trim().toLowerCase();

					let genres0 = genres.filter(genre => genre.name == textSearch);
					
					// Recherche exact (uk metalcore matchera uk metalcore)
					let regex = '\\b(\\w*' +  textSearch + '\\w*)\\b';
					let genres1 = genres.filter(genre => genre.name.search(regex) >= 0);
		
					// Recherche inversée exact (exemple, uk metalcore matchera metalcore uk)
					regex = '\\b(\\w*' +  $.trim(textSearch.split(' ').reverse().join(' ')) + '\\w*)\\b';
					let genres2 = genres.filter(genre => genre.name.search(regex) >= 0);
		
					// Recherche très générale en mode OU (uk metalcore renverra tous les uk et tous les metalcore)
					regex = '';
					textSearch.split(' ').forEach(function(value) {
					   regex += '\\b(\\w*' + value + '\\w*)\\b|';
					});
					// Supression du dernier caractère de la chaine pour enlever le ou |
					regex = regex.substring(0, regex.length - 1);
					let genres3 = genres.filter(genre => genre.name.search(regex) >= 0);
	
					// On concatène tout et on enlève les genres dupliqués
					app.vueGenres = genres0.concat(genres1).concat(genres2).concat(genres3).unique();
			   }, doneTypingInterval);
			}
	    });
	    input.off('keydown').on('keydown', function () {
			clearTimeout(typingTimer);
	    });
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
		let option = select.find('option[data-lang="en"]');
		option.attr('selected', true);
		// Nice select
		$('#changeLanguage').find('.nice-select .current').html(option.html());
	} else {
		// Html select
		let option = select.find('option[data-lang="fr"]');
		option.attr('selected', true);
		// Nice select
		$('#changeLanguage').find('.nice-select .current').html(option.html());
	}

	// Si on sélectionne une langue, on recharge la page avec la langue choisie
	select.change(function(eventData) {
		window.location.replace(eventData.currentTarget.value);
	});
}

global.manageSaveChoice = function() {
	let playlistName   = $('input[name="playlistName"]');
	let playlistChoice = $('.nice-select.existingPlaylist');
	
	$('select[name="saveOption"]').change(function() {
		if ($(this).val() === 'createNewPlaylist'){
			playlistName.show();
			playlistChoice.hide();
			playlistName.prop('required', true);
		} else if ($(this).val() === 'existingPlaylist') {
			playlistName.hide();
			playlistChoice.show();
			playlistName.prop('required', false);
		} else {
			playlistName.hide();
			playlistChoice.hide();
			playlistName.prop('required', false);
		}
	});
};

$( document ).ready(function() {
	//fonction scroll top
	$('#link-top').click(function() {
		window.scrollTo({
			top: 0,
			behavior: 'smooth'
		});
	});

	//fonction apparition du bouton scroll top
	let heightScreen = screen.availHeight;
	$(window).scroll(function() {
		if (heightScreen < window.scrollY) {
			$("#link-top svg").css("display", "block");
		} else {
			$("#link-top svg").css("display", "none");
		}
	});

	global.addPopover = function(selector) {
		$(selector).popover({
			trigger: 'manual',
			html: true,
			content: function() {
				return $(this).parent().find('.popover-content').html();
			},
		}).on("mouseenter", function() {
			let _this = this;
			$(this).popover("show");
			$(".popover").on("mouseleave", function() {
				$(_this).popover('hide');
			});
		}).on("mouseleave", function() {
			let _this = this;
			setTimeout(function() {
				if (!$(".popover:hover").length) {
					$(_this).popover("hide");
				}
			}, 300);
		});
	};
});

global.array_column = function(array, columnName) 
{
    return array.map(function(value,index) {
        return value[columnName];
    })
}

global.ajaxInput = function(formElementToSubmit, inputElement)
{
	let typingTimer; // Timer
	let doneTypingInterval = 300;  // On laisse une seconde
	inputElement.on('keyup', function () {
		if (inputElement.val() === '') {
			return;
		}
		clearTimeout(typingTimer);
		typingTimer = setTimeout(function() {
			formElementToSubmit.submit();
		}, doneTypingInterval);
	});
	inputElement.on('keydown', function () {
		clearTimeout(typingTimer);
	});
}

global.formAjaxSubmit = function(formElement, resultDomElement, callback = null)
{
	formElement.submit(function(event) {
		resultDomElement.hide();
		showLoader();
		event.preventDefault(); //prevent default action
		let url           = $(this).attr("action"); //get form action url
		let requestMethod = $(this).attr("method"); //get form GET/POST method
		let data          = $(this).serialize(); //Encode form elements for submission

		$.ajax({
			url : url,
			type: requestMethod,
			data : data
		}).done(function(response) {
			resultDomElement.html(response);
			resultDomElement.show();
			if (callback !== null) {
				callback();
			}
		}).fail(function() {
			feedbackError();
		}).always(function() {
			hideLoader();
		});
	});
}