/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// Recommendations
global.recommendations = function() {
	let seedForm = $('#seed-search-form');
	let recommendationForm = $('#recommendations');
	let input = $('#seed-search');
	addEvents();
	ajaxInput(seedForm, input);
	formAjaxSubmit(seedForm, $('.seeds-result'), seedEvent);
	formAjaxSubmit(recommendationForm, $('.recommendation-result'));


	function addEvents()
	{
		input.on('focusout', function() {
			$('.seeds-result').css('height', '0');
			// $('.selection').show();
		});
		input.on('click keyup', function () {
			$('.seeds-result').css('height', '300px');
		});
		
		seedEvent();
		addValidateEvents();
		addSeedAddedDelEvent();
		addSaveEvents();
	}

	function seedEvent()
	{
		$('.recommendations .block-element').off('click').on('click', function() {
			let imgTag = $('img', $(this));
			let img = '';
			if (imgTag.length) {
				img = imgTag[0];
			}
			let svgTag = $('svg', $(this));
			let svg = '';
			if (svgTag.length) {
				svg = svgTag[0];
			}
			let name = $('.name', $(this)).html();
			let type = $(this).data('type');
			let id = $(this).data('id');

			let seed = createSeedHtml(id, type, name, img, svg);
			$('.seeds-added').append(seed);
			emptySeedInputSearch();
		});
	}

	function createSeedHtml(id, type, name, img, svg)
	{
		if (typeof img === 'object' && img !== null) {
			img = img.outerHTML;
		}
		if (typeof svg === 'object' && svg !== null) {
			svg = svg.outerHTML;
		}

		return $('#hidden-seed-template').html()
			.replace('{id}', id)
			.replace('{type}', type)
			.replace('{name}', name)
			.replace('{img}', img)
			.replace('{svg}', svg);
	}

	function addSeedAddedDelEvent()
	{
		$('.seeds-added').off('click').on('click', '.btn-close', function() {
			$(this).closest('.seed-added').remove();

			if ($('.seeds-added').children().length !== 0) {
				$('#recommendations').submit();
			} else {
				emptyResults();
			}
		});
	}

	function createInputSeedHtml(id, type, number)
	{
		return $('#hidden-inputseed-template').html()
			.replace('{id}', id)
			.replace('{type}', type)
			.replaceAll('{index}', number);
	}

	function addValidateEvents()
	{
		$('#recommendations').off('submit.validate').on('submit.validate', function(event) {
			$('.seeds').empty();
			$('.seeds-added .seed-added').each(function(index) {
				$('.seeds').append(createInputSeedHtml($(this).data('id'), $(this).data('type'), index));

			});
		});

		$('.seeds-result').on('click', '.block-element', function() {
			$('#recommendations').submit();
		})

		$('#recommendations input').on('change', function() {
			if ($('.seeds-added').children().length !== 0) {
				$('#recommendations').submit();
			}
		});

		$('button[type="reset"]').click(function() {
			if ($('.seeds-added').children().length !== 0) {
				$('#recommendations').submit();
			}
		});
	}

	function addSaveEvents()
	{
		$('#saveAction').off('submit.save').on('submit.save', function(event) {
			let tracks = [];
			$('.recommendation-result .songBlock').each(function() {
				tracks.push($(this).data('spotifyid'));
			});
			$('#saveAction').append('<input type="hidden" name="tracks" value=\'' + JSON.stringify(tracks) + '\'>');
		});
	}

	function emptyResults()
	{
		$('.recommendation-result').empty();
		emptySeedInputSearch();
	}
	function emptySeedInputSearch()
	{
		$('#seed-search').val('');
	}
};

