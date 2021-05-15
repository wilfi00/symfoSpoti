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
		$('#searchAction').submit(function(event) {
			let result = $('.search-result');
			result.hide();
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
				result.html(response);
				hideLoader();
				result.show();
				addEvents();
			});
		});

		var typingTimer; // Timer
		var doneTypingInterval = 100;  // On laisse une seconde
		let input = $('#addListenLater');
		input.on('keyup', function () {
			if (input.val() === '') {
				return;
			}
			clearTimeout(typingTimer);
			typingTimer = setTimeout(function() {
				$('#searchAction').submit();
			}, doneTypingInterval);
		});
		input.on('keydown', function () {
			clearTimeout(typingTimer);
		});
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
