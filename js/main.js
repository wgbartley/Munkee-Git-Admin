var html = new String();

$(function() {
	/* Set up tabs */
	$('#tabs').tabs();

	get_tabs('groups');
	get_tabs('repos');
	get_tabs('keys');


	/* Set up dialogs */
	var dialog_options = new Object({
		autoOpen: false,
		draggable: false,
		modal: true,
		resizable: false,
		minHeight: 400,
		minWidth: 400
	});

	$('#dialog_groups').dialog(dialog_options);
	$('#dialog_repos').dialog(dialog_options);
	$('#dialog_keys').dialog(dialog_options);


	$('form').submit(function() {
		/* Check for a name */
		if($(this).find('#name').val().trim()=='') {
			alert('Please enter a name.');
			return false;
		}


		/* Check for a key if it's a key */
		if($(this).find('#action').val()=='keys_add')
			if($(this).find('#key').val().trim()=='') {
				alert('Please enter a key.');
				return false;
			}


		/* If delete is checked, make sure we really want to delete */
		if($(this).find('#delete').attr('checked')==true) {
			var del_confirm = confirm('Are you sure you want to delete?');

			if(del_confirm==false) {
				$(this).find('#delete').attr('checked',false);
				return false;
			}
		}

		var tab = $(this).find('#tab').val();

		$.ajax({
			type: 'post',
			url: 'ajax.php',
			data: $(this).serialize(),
			success: function(data) {
				if(data!=false && data.error==undefined) {
					window.location.href=window.location.protocol+
						'//'+window.location.host+window.location.pathname+
						'#tab-'+tab;
					window.location.reload();
					return false;
				} else {
					alert('There was an error:'+"\n"+data.error);
					console.log(data);
				}},
			error: function(r,s,e) {
				console.log(r,s,e);
			}
		});
		return false;
	});
});


function get_tabs(tab) {
	$.getJSON('ajax.php?action=get_'+tab, function(data) {
		html = '<ul class="tab_items">';

		$.each(data, function(i) {
			html += '<li><a href="#" id="'+tab+'_'+data[i].id+'" onclick="get_item(\''+tab+'\',\''+data[i].id+'\');return false">'+data[i].name+'</a></li>';
		});

		html += '</ul>';

		$('#tab-'+tab).html(html);

		$('#tab-'+tab).append('<button type="button" onclick="get_item(\''+tab+'\',\'__add__\')">Add</button>');
	})
}


function get_item(type,id) {
	if(id=='__add__') {
		dialog_clear($('#dialog_'+type));

		$('#dialog_'+type+' #action').val(type+'_add');
		$('#dialog_'+type).dialog('option','title','Add New');
		$('#dialog_'+type+' .delete_wrapper').hide();

		$('#dialog_'+type).dialog('open');

		return true;
	}


	$.getJSON('ajax.php?action=get_'+type+'_single&id='+id, function(data) {
		/* Set general dialog stuff */
		$('#dialog_'+type).dialog('option','title',data.name);
		$('#dialog_'+type+' .delete_wrapper').show();


		/* Custom by type */
		$.each(data, function(i) {
			/* If it's an array, we'll treat for a <select> */
			if($.isArray(data[i])) {
				$('#dialog #'+i).html(''); // Clear the <select>

				/* Members = keys */
				if(i=='members') {
					/* Populate the <select> */
					get_select_options(type,'keys',i,$.makeArray(data[i]));
				}


				/* Writable = repos */
				if(i=='writable') {
					/* Populate the <select> */
					get_select_options(type,'repos',i,$.makeArray(data[i]));
				}

			/* Otherwise, treat as input */
			} else
				$('#dialog_'+type+' #'+i).val(data[i]);
		});


		/* Make a copy of the original name */
		$('#dialog_'+type+' #orig').val($('#dialog_'+type+' #name').val());


		$('#dialog_'+type+' #action').val(type+'_edit');


		/* Show the dialog box */
		$('#dialog_'+type).dialog('open');
	});
}


function get_select_options(type,alias,e,values) {
	/* Populate the <select> */
	$.getJSON('ajax.php?action=get_'+alias, function(keys) {
		$.each(keys, function(j) {
			$('#dialog_'+type+' #'+e).
				append($('<option></option>').
				text(keys[j].name));
			});

		$('#dialog_'+type+' #'+e).val($.makeArray(values)); // Set the selected values
	});
}


function dialog_clear(node) {
	/* Clear input text boxes */
	$(node).find('input[type=text]').each(function() {
		$(this).val('');
	});

	/* Clear input checkboxes */
	$(node).find('input[type=checkbox]').each(function() {
		$(this).attr('checked',false);
	});

	/* Clear selected <select> values */
	$(node).find('select').each(function() {
		$(this).html('');
	});

	/* Clear textareas */
	$(node).find('textarea').each(function() {
		$(this).val('');
	})

	/* Re-populate <selects> */
	get_select_options('groups','keys','members',new Array());
	get_select_options('groups','repos','writable',new Array());
}