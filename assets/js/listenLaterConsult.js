// Consult - listen later
global.listenLaterConsult = function(config) {
	init();

	function addEvents()
	{

	}

	function init()
	{
		$('table').dataTable({
			columnDefs: [
				{ orderable: false, targets: [0, 2, 4] }
			],
			order: [[ 3, "desc" ]]
		})
	}

};
