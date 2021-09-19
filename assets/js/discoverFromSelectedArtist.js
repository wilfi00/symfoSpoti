// Selection artists
global.artistManager = function(config) {
	let sidebarSelection = $('.artistSelection');
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
		$('.search-result .artistBlocClick').each(function() {
			$(this).off('click').on('click', function() {
				addArtistToSelection($(this).parent());
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
		artist.children('.artistBlocClick').css('pointer-events', 'none');
		$.post(config.addArtistToSelectionUrl, JSON.stringify(artist.data().information));
		
		$('.saveAction').prop('disabled', false);
		updateNbArtists();
	}

	function removeArtistToSelection(artist)
	{
		let idArtist = artist.data('information').id
		$('.search-result .artistBloc').each(function() {
			if ($(this).data('information').id === idArtist) {
				$(this).css('pointer-events', 'auto');
			}
		});
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

	let searchForm = $('#search-form');
	ajaxInput(searchForm, $('#search-form input'));
	formAjaxSubmit(searchForm, $('.search-result'), addEvents);

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
