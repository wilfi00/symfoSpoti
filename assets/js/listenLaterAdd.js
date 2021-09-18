// Ajout - listen later
global.listenLaterAdd = function(config) {
	init();

	function addEvents()
	{
		// Ajout listen after
		$('.search-result .songBlock').each(function() {
			$(this).off('click').on('click', function() {
				addListenAfter($(this));
			});
		});
	}

	function init()
	{
		let input = $('#searchAction');
		ajaxInput(input, $('#addListenLater'));
		formAjaxSubmit(input, $('.search-result'), addEvents);
	}

	function addListenAfter(song)
	{
		showLoader();
		$.ajax({
			url : config.urlAddSong,
			type: 'POST',
			data : {
				name:        song.data('name'),
				type:        song.data('type'),
				spotifyid:   song.data('spotifyid'),
				spotifyuri:  song.data('spotifyuri'),
				popularity:  song.data('popularity'),
				image:       song.data('image')
			}
		}).done(function(response) {
			feedbackSuccess(config.text.addSuccess);
		}).fail(function() {
			feedbackError(config.text.feedbackError);
		}).always(function(){
			hideLoader();
		});
	}
};
