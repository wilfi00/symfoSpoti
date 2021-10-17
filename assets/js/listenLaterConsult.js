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

		// Call datatables, and return the API to the variable for use in our code
		// Binds datatables to all elements with a class of datatable
		const dtable = $(".datatable").dataTable().api();
		// Grab the datatables input box and alter how it is bound to events
		$(".dataTables_filter input")
			.unbind() // Unbind previous default bindings
			.bind("input", function(e) { // Bind our desired behavior
				// If the length is 3 or more characters, or the user pressed ENTER, search
				if(this.value.length >= 1 || e.keyCode === 13) {
					// Call the API search function
					dtable.search(this.value).draw();
				}
				// Ensure we clear the search if they backspace far enough
				if(this.value === "") {
					dtable.search("").draw();
				}
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
