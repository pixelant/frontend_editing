(function($, w){

	var editorConfig = {
		entities_latin: false,
		htmlEncodeOutput: false,
		allowedContent: true,
		toolbarGroups: [
			{ name: 'clipboard', groups: [ 'clipboard', 'undo' ] },
			{ name: 'editing', groups: [ 'find', 'selection' ] },
			{ name: 'links' },
			{ name: 'insert' },
			{ name: 'tools' },
			{ name: 'document', groups: [ 'mode', 'document', 'doctools' ] },
			{ name: 'others' },
			'/',
			{ name: 'basicstyles', groups: [ 'basicstyles', 'cleanup' ] },
			{ name: 'paragraph', groups: [ 'list', 'indent', 'blocks', 'align', 'bidi' ] },
			{ name: 'styles' }
		]
	};

	var pageUrl = window.location.protocol + '//' + window.location.host;
	var functionRoutes = {
		'crud': '?type=1470741815'
	};

	// Add additional configuration to all 'contenteditable' instances
	$('body').find('div[contenteditable=\'true\']').each(function() {
		$(this).ckeditor(editorConfig);
	});

	CKEDITOR.on('instanceReady', function(event) {
		var editor = event.editor;
		editor.on('change', function(changeEvent) {
			if (typeof editor.element !== 'undefined') {
				var dataSet = editor.element.$.dataset;
				var data = {
					'action': 'save',
					'table': dataSet.table,
					'uid': dataSet.uid,
					'field': dataSet.field,
					'content': editor.getData()
				};

				$.ajax({
					type: 'POST',
					url: pageUrl + functionRoutes.crud,
					dataType: 'JSON',
					data: data
				}).done(function(data, textStatus, jqXHR) {
					toastr.success('Content (uid: "' + data.message +'") have been saved!', 'Content saved');
				}).fail(function(jqXHR, textStatus, errorThrown) {
					toastr.error(errorThrown, 'Something went wrong');
				});
			}
		});
	});

	/*$('#sidebar').simplerSidebar({
		opener: '#toggle-sidebar',
		sidebar: {
			align: 'right', //or 'right' - This option can be ignored, the sidebar will automatically align to right.
			width: 300, //You can ignore this option, the sidebar will automatically size itself to 300px.
			closingLinks: '.close-sidebar' // If you ignore this option, the plugin will look for all links and this can be buggy. Choose a class for every object inside the sidebar that once clicked will close the sidebar.
		}
	});*/

})(jQuery, window);