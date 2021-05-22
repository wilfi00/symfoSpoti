// Consult - listen later
global.listenLaterConsult = function(config) {
	init();

	function addEvents()
	{

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

		$('table').dataTable(datatableOptions);
	}
};
