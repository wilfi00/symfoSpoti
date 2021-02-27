// Selection artists
global.artistManager = function(config) {
	var sidebarSelection = $('.artistSelection');
	init();

	function init()
	{
		addEvents();
		manageSaveChoice();
		updateNbArtists();
		manageFeedback(config.success, config.text.playlistSaveSucessFeedback, config.text.feedbackError);
	}

	function addEvents()
	{
		// Ajout d'un artiste à la sélection
		$('.search-result .artistBloc').each(function() {
			$(this).off('click').on('click', function() {
				addArtistToSelection($(this));
			});
		});

		// Suppression de l'artiste de la sélection
		$('.artistSelection .removeArtist').each(function() {
			$(this).off('click').on('click', function() {
				removeArtistToSelection($(this).parent());
			});
		});

		// Supprime toute la sélection
		$('.removeAll').off('click').on('click', function() {
			removeAllSelection();
		});
		
		// Popover artistes genres
		addPopover('.artistBloc .picto-info');
		
		$('#saveAction').off('submit').on('submit', function(event) {
			saveAction();
		});
	}

	function addArtistToSelection(artist)
	{
		artist.clone().prependTo(sidebarSelection);
		addEvents();
		artist.css('pointer-events', 'none');
		$.post(config.addArtistToSelectionUrl, JSON.stringify(artist.data().information));
		
		$('.saveAction').prop('disabled', false);
		updateNbArtists();
	}

	function removeArtistToSelection(artist)
	{
	
		artist.remove();
		$.post(config.removeArtistToSelectionUrl, artist.data().information.id);
		
		if (getNbArtists() === 0) {
			$('.saveAction').prop('disabled', true);
		}
		updateNbArtists();
	}

	function removeAllSelection()
	{
		$.get(config.removeAllSelectionUrl);
		$('.artistSelection .artistBloc').each(function() {
			$(this).remove();
		});
		
		$('.saveAction').prop('disabled', true);
		updateNbArtists();
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
	    	if (input.val() == '') {
	    		return;
	    	}
	        clearTimeout(typingTimer);
	        typingTimer = setTimeout(function() {
				$('#search-form').submit();
			}, doneTypingInterval);
	    });
	    input.on('keydown', function () {
			clearTimeout(typingTimer);
	    });
	});
	
	function saveAction()
	{
		$('#saveAction').append('<input type="hidden" name="nbTracks" value="' + $('#nbTracks').val() + '">');
	}
	
	function getNbArtists()
	{
		return $('.artistSelection .artistBloc').length;
	}
	
	function updateNbArtists()
	{
		$('.nb-artists-container .important').html(getNbArtists());
	}
};
