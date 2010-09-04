<!DOCTYPE html>
<html>
<head>
<title>MunkeeSoft Git Admin</title>

<link rel="stylesheet" href="css/main.css" type="text/css" media="screen" />
<link rel="stylesheet" href="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.4/themes/smoothness/jquery-ui.css" type="text/css" media="screen" />

<script type="text/javascript" src="http://www.google.com/jsapi"></script>

<script type="text/javascript">
google.load('jquery','1.4.2');
google.load('jqueryui','1.8.4');
</script>

<script type="text/javascript" src="js/main.js"></script>
</head>

<body>

<div id="tabs">
	<ul>
		<li><a href="#tab-groups">Groups</a></li>
		<li><a href="#tab-repos">Repositories</a></li>
		<li><a href="#tab-keys">Keys (Users)</a></li>
	</ul>

	<div id="tab-groups" class="tab"></div>

	<div id="tab-repos" class="tab"></div>

	<div id="tab-keys" class="tab"></div>
</div>


<div id="dialog_groups" class="dialog">
	<form id="form_groups">
	<p>
		<label for="name">Name</label>
		<input type="text" id="name" name="name" />
	</p>

	<p>
		<label for="members">Keys (Users)</label>
		<span class="label_hint">Use ctrl+click</span>
		<select id="members" name="members[]" size="5" multiple="multiple"></select>
	</p>

	<p>
		<label for="writable">Repos</label>
		<span class="label_hint">Use ctrl+click</span>
		<select id="writable" name="writable[]" size="5" multiple="multiple"></select>
	</p>

	<p class="delete_wrapper">
		<label>Delete</label>
		<input type="checkbox" id="delete" name="delete" value="Y" />
	</p>

	<p>
		<button type="reset" onclick="$('#dialog_groups').dialog('close');">Cancel</button>
		<button type="submit">Save</button>
	</p>

	<input type="hidden" name="orig" id="orig" value="" />
	<input type="hidden" name="tab" id="tab" value="groups" />
	<input type="hidden" name="action" id="action" value="" />

	</form>
</div>


<div id="dialog_repos" class="dialog">
	<form id="form_repos">
	<p>
		<label for="name">Name</label>
		<input type="text" id="name" name="name" />
	</p>

	<p>
		<label for="description">Description</label>
		<input type="text" id="description" name="description" />
	</p>

	<p class="delete_wrapper">
		<label>Delete</label>
		<input type="checkbox" id="delete" name="delete" value="Y" />
	</p>

	<p>
		<button type="reset" onclick="$('#dialog_repos').dialog('close');">Cancel</button>
		<button type="submit">Save</button>
	</p>

	<input type="hidden" name="orig" id="orig" value="" />
	<input type="hidden" name="tab" id="tab" value="repos" />
	<input type="hidden" name="action" id="action" value="" />

	</form>
</div>


<div id="dialog_keys" class="dialog">
	<form id="form_keys">
	<p>
		<label for="name">Name</label>
		<input type="text" id="name" name="name" />
	</p>

	<p>
		<label for="key">Description</label>
		<textarea id="key" name="key" rows="5"></textarea>
	</p>

	<p class="delete_wrapper">
		<label>Delete</label>
		<input type="checkbox" id="delete" name="delete" value="Y" />
	</p>

	<p>
		<button type="reset" onclick="$('#dialog_keys').dialog('close');">Cancel</button>
		<button type="submit">Save</button>
	</p>

	<input type="hidden" name="orig" id="orig" value="" />
	<input type="hidden" name="tab" id="tab" value="keys" />
	<input type="hidden" name="action" id="action" value="" />

	</form>
</div>

</body>
</html>