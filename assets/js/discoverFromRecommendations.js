/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// Recommendations
global.recommendations = function() {
	const MAX_SEEDS = 5;
	let seedForm = $('#seed-search-form');
	let recommendationForm = $('#recommendations');
	let input = $('#seed-search');
	addEvents();
	ajaxInput(seedForm, input);
	formAjaxSubmit(seedForm, $('.seeds-result'), seedEvent);
	formAjaxSubmit(recommendationForm, $('.recommendation-result'));
	initValuesFromStorage();
	$('#recommendations .picto-info').each(function() {
		addPopover($(this));
	})

	function addEvents()
	{
		input.on('focusout', function() {
			$('.seeds-result').css('height', '0');
		});
		input.on('click keyup', function () {
			$('.seeds-result').css('height', '300px');
		});
		$('.seeds-result').off('DOMSubtreeModified.disableseed').on('DOMSubtreeModified.disableseed', function() {
			$(this).find('.block-element').each(function() {
				setTimeout(() => {
					if (isAlreadyAdded($(this))) {
						$(this).css('pointer-events', 'none');
					} else {
						$(this).css('pointer-events', 'auto');
					}
				}, 100);
			})

		});
		$('.recommendation-result').off('DOMSubtreeModified.storage').on('DOMSubtreeModified.storage', function() {
			sessionStorage.setItem('recommendationsTracks', $(this).html());
			manageActivationSaveAction($(this).children('.songBlock').length);
		});
		$('.seeds-added').off('DOMSubtreeModified.storage').on('DOMSubtreeModified.storage', function() {
			setTimeout(() => {
				sessionStorage.setItem('recommendationsSeeds', $(this).html());

				if ($(this).children('.seed-added').length >= MAX_SEEDS) {
					input.prop('disabled', true);
					$('#seed-search').attr('placeholder', input.data('disabledplaceholder'));
				} else {
					input.prop('disabled', false);
					$('#seed-search').attr('placeholder', input.data('defaultplaceholder'));
				}
			}, 100);
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
			// Submit du form si on a au moins un seed
			if ($('.seeds-added').children().length !== 0) {
				$('#recommendations').submit();
			}
			// Sauvegarde des inputs
			let inputValues = $('#recommendations input[type="range"]').map(function(idx, input) {
				return $(input).val();
			}).get();
			sessionStorage.setItem('recommendationsInputs', JSON.stringify(inputValues));
		});

		$('button[type="reset"]').click(function() {
			if ($('.seeds-added').children().length !== 0) {
				$('#recommendations').submit();
				sessionStorage.removeItem('recommendationsInputs');
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

	function initValuesFromStorage()
	{
		// Tracks
		let tracks = sessionStorage.getItem('recommendationsTracks');
		if (tracks !== null) {
			$('.recommendation-result').html(tracks);
		}
		// Seeds
		let seeds = sessionStorage.getItem('recommendationsSeeds');
		if (seeds !== null) {
			$('.seeds-added').html(seeds);
		}
		// Inputs
		let inputsValues = JSON.parse(sessionStorage.getItem('recommendationsInputs'));
		if (inputsValues !== null) {
			$('#recommendations input').each(function(index) {
				$(this).val(inputsValues[index]);
			});
		}
	}

	function manageActivationSaveAction(length)
	{
		let btnSave = $('#saveAction .saveAction:submit');
		if (length > 0) {
			btnSave.prop('disabled', false);
		} else {
			btnSave.prop('disabled', true);
		}
	}

	function isAlreadyAdded(seed)
	{
		let seedsAdded = new Map();
		$('.seeds-added .seed-added').each(function() {
			seedsAdded.set($(this).data('id'), $(this).data('type'));
		})
		return seedsAdded.has(seed.data('id')) && seedsAdded.get(seed.data('id')) === seed.data('type');
	}
};

