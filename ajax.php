<?
require 'config.php';

$git = new MunkeeGit($gitosis_dir);

$retval = array();

/* Trim the $_GET values */
if(isset($_GET))
	foreach($_GET as $g_k => $g_v)
		if(is_string($g_v))
			$_GET[$g_k] = trim($g_v);

/* Trim the $_POST values */
if(isset($_POST))
	foreach($_POST as $p_k => $p_v)
		if(is_string($p_v))
			$_POST[$p_k] = trim($p_v);


/* Do $_GET stuff */
if(isset($_GET['action']))
	switch($_GET['action']) {
		case 'get_groups':
			$groups = $git->show_groups();
			ksort($groups);

			foreach($groups as $g_k => $g_v)
				$retval[] = array(
					'id' => $g_k,
					'name' => $g_k
				);

			break;
		case 'get_repos':
			$repos = $git->show_repos();
			ksort($repos);

			foreach($repos as $r_k => $r_v)
				$retval[] = array(
					'id' => $r_k,
					'name' => $r_k
				);

			break;
		case 'get_keys':
			$keys = $git->show_keys();
			ksort($keys);

			foreach($keys as $k_k => $k_v)
				$retval[] = array(
					'id' => $k_k,
					'name' => $k_k
				);

			break;
		case 'get_groups_single':
			$retval = $git->show_groups($_GET['id']);
			break;
		case 'get_repos_single':
			$retval = $git->show_repos($_GET['id']);
			break;
		case 'get_keys_single':
			$retval = $git->show_keys($_GET['id']);
			break;
		default:
			$retval = array();
	}


/* Do $_POST stuff */
if(isset($_POST['action']))
	switch($_POST['action']) {
		case 'groups_edit':
			/* Check for deletion */
			if(isset($_POST['delete']) && $_POST['delete']=='Y')
				$retval = $git->del_group($_POST['orig']);
			else {
				/* Rename the group */
				if($_POST['orig']!=$_POST['name'])
					$retval = $git->rename_group($_POST['orig'],$_POST['name']);

				/* Loop through all repos to add/remove */
				foreach($git->show_repos() as $repo) {
					if(in_array($repo['name'],$_POST['writable']))
						$git->add_repo_to_group($repo['name'],$_POST['name']);
					else
						$git->del_repo_from_group($repo['name'],$_POST['name']);
				}


				/* Loop through all keys to add/remove */
				foreach($git->show_keys() as $key) {
					if(in_array($key['name'],$_POST['members']))
						$git->add_key_to_group($key['name'],$_POST['name']);
					else
						$git->del_key_from_group($key['name'],$_POST['name']);
				}

				$retval = $git->show_groups($_POST['name']);
			}

			break;
		case 'groups_add':
			/* Add the group */
			$retval = $git->add_group($_POST['name']);

			if($retval && !isset($retval['error'])) {
				/* Add keys to group */
				if(isset($_POST['members']))
					foreach($_POST['members'] as $m)
						$git->add_key_to_group($m,$retval['name']);

				/* Add repos to group */
				if(isset($_POST['writable']))
					foreach($_POST['writable'] as $w)
						$git->add_repo_to_group($w,$retval['name']);

				$retval = $git->show_groups($retval['name']);
			}
			break;
		case 'repos_edit':
			/* Check for deletion */
			if(isset($_POST['delete']) && $_POST['delete']=='Y')
				$retval = $git->del_repo($_POST['orig']);
			else {
				/* Rename the repo */
				if($_POST['orig']!=$_POST['name'])
					$retval = $git->rename_repo($_POST['orig'],$_POST['name']);

				/* Update the repo */
				$retval = $git->edit_repo($_POST['name'],$_POST['description']);
			}
			break;
		case 'repos_add':
			$retval = $git->add_repo($_POST['name'],$_POST['description']);
			break;
		case 'keys_edit':
			/* Check for deletion */
			if(isset($_POST['delete']) && $_POST['delete']=='Y')
				$retval = $git->del_key($_POST['orig']);
			else {
				/* Rename the key */
				if($_POST['orig']!=$_POST['name'])
					$retval = $git->rename_key($_POST['orig'],$_POST['name']);

				/* Update the key */
				$retval = $git->edit_key($_POST['name'],$_POST['key']);
			}
			break;
		case 'keys_add':
			$retval = $git->add_key($_POST['name'],$_POST['key']);
			break;
		default:
			$retval = array();
	}


header('Content-type: application/json');

echo json_encode($retval);
?>
