// Consult - listen later
global.listenLaterConsult = function(config) {
	init();

	function addEventDatatable(table, tableObject)
	{
		table.find('.delete').off('click').on('click', function() {
			deleteSong($(this), tableObject);
		});
	}

	function init()
	{
		let datatableOptions = {
			columnDefs: [
				{orderable: false, targets: [0, 2, 4]}
			],
			order: [[3, "desc"]],
			lengthChange: false,
			dom: "<'row'<'col-sm-12' f>>" +
				"<'row'<'col-sm-12'tr>>" +
				"<'row'<'col-sm-4'li><'col-sm-8'p>>"
		};
		if (config.isFrench) {
			datatableOptions.language = { url: "https://cdn.datatables.net/plug-ins/1.10.24/i18n/French.json" }
		}

		$('table').each(function() {
			let table = $(this).DataTable(datatableOptions);
			addEventDatatable($(this), table);
		});
	}

	function deleteSong(song, table)
	{
		showLoader();
		$.ajax({
			url : config.urlDeleteSong,
			type: 'POST',
			data : {
				id:   song.data('id'),
				type: song.data('type')
			}
		}).done(function(response) {
			feedbackSuccess(config.text.deleteSuccess);
			table.rows(song.parents('tr')).remove().draw();
		}).fail(function() {
			feedbackError(config.text.feedbackError);
		}).always(function(){
			hideLoader();
		});
	}
};
