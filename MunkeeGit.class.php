<?
class MunkeeGit {
	private $gitosis_dir, $conf, $keys, $groups, $repos;

	function __construct($gitosis_dir) {
		$this->gitosis_dir = realpath($gitosis_dir);

		$this->init();

		$this->conf = $this->check_conf();
	}


	function __destruct() {
		$this->save();
	}


	private function open_conf($file) {
		return parse_ini_file($file, TRUE);
	}


	private function init() {
		$this->conf = parse_ini_file($this->gitosis_dir.'/gitosis.conf', TRUE);
		$this->groups = $this->parse_groups($this->conf);
		$this->repos = $this->parse_repos($this->conf);
		$this->keys = $this->parse_keys($this->gitosis_dir.'/keydir/');
	}


	private function parse_groups($conf) {
		$retval = array();
		foreach($conf as $conf_k => $conf_v)
			if(strpos($conf_k,'group')===0) {
				$group = trim(substr($conf_k,6));
				$retval[$group] = $conf_v;
				$retval[$group]['name'] = $group;
				$retval[$group]['writable'] = explode(' ', $retval[$group]['writable']);
				$retval[$group]['members'] = explode(' ', $retval[$group]['members']);
			}

		return $retval;
	}


	private function parse_repos($conf) {
		$retval = array();
		foreach($conf as $conf_k => $conf_v)
			if(strpos($conf_k,'repo')===0) {
				$repo = trim(substr($conf_k,5));
				$retval[$repo] = $conf_v;
				$retval[$repo]['name'] = $repo;
			}

		return $retval;
	}


	private function parse_keys($keydir) {
		$retval = array();

		$files = glob($keydir.'/*.pub');
		foreach($files as $f) {
			$retval[basename($f,'.pub')]['key'] = trim(file_get_contents($f));
			$retval[basename($f,'.pub')]['name'] = basename($f,'.pub');
		}

		return $retval;
	}


	private function check_conf() {

		/* Loop through groups */
		foreach($this->groups as $group) {
			/* Check that all repos in groups exist */
			foreach($group['writable'] as $repo)
				if(!in_array($repo, array_keys($this->repos)))
					$this->del_repo_from_group($repo, $group['name']);


			/* Check that all members in groups exist */
			foreach($group['members'] as $key)
				if(!in_array($key, array_keys($this->keys)))
					$this->del_key_from_group($key, $group['name']);
		}


		/* Save it if something changed */
		if($this->gen_conf()!=$this->conf)
			$this->save();


		/* Re-initialize stuff */
		$this->init();
	}


	public function show_groups($group='') {
		$group = trim($group);

		if(strlen($group)>0)
			if(isset($this->groups[$group]))
				return $this->groups[$group];
			else
				return FALSE;
		else
			return $this->groups;
	}


	public function show_repos($repo='') {
		$repo = trim($repo);

		if(strlen($repo)>0)
			if(isset($this->repos[$repo]))
				return $this->repos[$repo];
			else
				return FALSE;
		else
			return $this->repos;
	}


	public function show_keys($user='') {
		$user = trim($user);

		if(strlen($user)>0)
			if(isset($this->keys[$user]))
				return $this->keys[$user];
			else
				return FALSE;
		else
			return $this->keys;
	}


	public function add_group($group) {
		$group = trim($group);

		if(strlen($group)>0 && !isset($this->groups[$group]))
			$this->groups[$group] = array(
				'name' => $group,
				'writable' => array(),
				'members' => array()
			);
		else
			$retval = array('error' => 'Group already exists');

		if(isset($this->groups[$group]) && !isset($retval['error'])) {
			return $this->show_groups($group);
		} else
			return $retval;
	}


	public function add_repo($repo, $descr) {
		$repo = trim($repo);
		$descr = trim($descr);

		if(strlen($repo) && !isset($this->repos[$repo]))
			$this->repos[$repo] = array(
				'description' => $descr
			);
		else
			$retval = array('error' => 'Repository already exists');

		if(isset($this->repos[$repo]) && !isest($retval['error']))
			return $this->repos[$repo];
		else
			return $retval;
	}


	public function add_key($user, $key) {
		$user = trim($user);
		$key = trim($key);

		if(strlen($user)>0 && strlen($key)>0 && !isset($this->keys[$user])) {
			$this->keys[$user] = $key;
			file_put_contents($this->gitosis_dir.'/keydir/'.$user.'.pub', $key);
		} else
			$retval = array('error' => 'Key already exists');

		if(isset($this->keys[$user]) && !isset($retval['error']))
			return $this->keys[$user];
		else
			return $retval;
	}


	public function del_group($group) {
		$group = trim($group);

		if($group=='gitosis-admin')
			return FALSE;

		if(isset($this->groups[$group])) {
			unset($this->groups[$group]);

			return TRUE;
		} else
			return FALSE;
	}


	public function del_repo($repo) {
		$repo = trim($repo);

		if(isset($this->repos[$repo])) {
			unset($this->repos[$repo]);

			foreach(array_keys($this->groups) as $group)
				$this->del_repo_from_group($repo, $group);

			return TRUE;
		} else
			return FALSE;
	}


	public function del_key($user) {
		$user = trim($user);

		if(isset($this->keys[$user])) {
			unset($this->keys[$user]);

			unlink($this->gitosis_dir.'/keydir/'.$user.'.pub');

			foreach(array_keys($this->groups) as $group)
				$this->del_key_from_group($user, $group);

			return TRUE;
		} else
			return FALSE;
	}


	public function del_repo_from_group($repo, $group) {
		$repo = trim($repo);
		$group = trim($group);

		if(isset($this->groups[$group])) {
			foreach(array_keys($this->groups[$group]['writable']) as $k)
				if($this->groups[$group]['writable'][$k]==$repo)
					unset($this->groups[$group]['writable'][$k]);

			return TRUE;
		} else
			return FALSE;
	}


	public function del_key_from_group($user, $group) {
		$user = trim($user);
		$group = trim($group);

		if(isset($this->groups[$group]))
			foreach(array_keys($this->groups[$group]['members']) as $k)
				if($this->groups[$group]['members'][$k]==$user)
					unset($this->groups[$group]['members'][$k]);

		return TRUE;
	}


	public function add_repo_to_group($repo, $group) {
		$repo = trim($repo);
		$group = trim($group);

		if(isset($this->groups[$group]) && isset($this->repos[$repo]) && !in_array($repo, $this->groups[$group]['writable']))
			$this->groups[$group]['writable'][] = $repo;

		if(isset($this->groups[$group]['writable'][count($this->groups[$group]['writable'])-1]))
			return $this->groups[$group]['writable'][count($this->groups[$group]['writable'])-1];
		else
			return FALSE;
	}


	public function add_key_to_group($user, $group) {
		$user = trim($user);
		$group = trim($group);

		if(isset($this->groups[$group]) && isset($this->keys[$user]) && !in_array($user, $this->groups[$group]['members']))
			$this->groups[$group]['members'][] = $user;

		if(isset($this->groups[$group]['members'][count($this->groups[$group]['members'])-1]))
			return $this->groups[$group]['members'][count($this->groups[$group]['members'])-1];
		else
			return FALSE;
	}


	public function rename_group($old, $new) {
		$old = trim($old);
		$new = trim($new);

		if(isset($this->groups[$old]) && strlen($new)>0 && !in_array($new,array_keys($this->groups))) {
			$this->groups[$new] =  $this->groups[$old];
			unset($this->groups[$old]);
		} else
			return FALSE;


		if(isset($this->groups[$new]))
			return $this->groups[$new];
		else
			return FALSE;
	}


	public function rename_repo($old, $new) {
		$old = trim($old);
		$new = trim($new);

		if(isset($this->repos[$old]) && strlen($new)>0 && !in_array($new,array_keys($this->repos))) {
			$this->repos[$new] = $this->repos[$old];
			unset($this->repos[$old]);

			foreach(array_keys($this->groups) as $group) {
				if(in_array($old,$this->groups[$group]['writable'])) {
					$this->groups[$group]['writable'][$new] = $this->groups[$group]['writable'][$old];
					unset($this->groups[$group]['writable'][$old]);
				}
			}
		} else
			return FALSE;


		if(isset($this->repos[$new]))
			return $this->repos[$new];
		else
			return FALSE;
	}


	public function rename_key($old, $new) {
		$old = trim($old);
		$new = trim($new);

		if(isset($this->keys[$old]) && strlen($new)>0 && !in_array($new,array_keys($this->keys))) {
			$this->keys[$new] = $this->keys[$old];
			unset($this->keys[$old]);

			foreach(array_keys($this->groups) as $group) {
				if(in_array($old,$this->groups[$group]['members'])) {
					$this->groups[$group]['members'][$new] = $this->groups[$group]['members'][$old];
					unset($this->groups[$group]['members'][$old]);
				}
			}
		} else
			return FALSE;


		if(isset($this->keys[$new]))
			return $this->keys[$new];
		else
			return FALSE;
	}


	public function edit_repo($repo, $descr) {
		$repo = trim($repo);
		$descr = trim($descr);

		if(isset($this->repos[$repo])) {
			$this->repos[$repo]['description'] = $descr;
			return $this->repos[$repo]['description'];
		} else
			return FALSE;
	}


	public function edit_key($user, $key) {
		$user = trim($user);
		$key = trim($key);

		if(isset($this->keys[$user]) && strlen($key)>0) {
			$this->keys[$user] = $key;
			
			return $this->keys[$user];
		} else
			return FALSE;
	}


	public function gen_conf() {
		$retval = <<<EOQ
[gitosis]


EOQ;

		foreach($this->groups as $g_k => $g_v) {
			$retval .= '[group '.$g_k.']'."\n";
			$retval .= 'writable = '.implode(' ',$g_v['writable'])."\n";
			$retval .= 'members = '.implode(' ',$g_v['members'])."\n";
			$retval .= "\n";
		}

		$retval .= "\n";

		foreach($this->repos as $r_k => $r_v) {
			$retval .= '[repo '.$r_k.']'."\n";
			$retval .= 'description = "'.$r_v['description'].'"'."\n";
			$retval .= "\n";
		}

		return $retval;
	}


	public function save() {
		$conf = $this->gen_conf();

		if($conf!=$this->conf) {
			/* Back up gitosis.conf */
			if(is_writable($this->gitosis_dir.'/gitosis.conf.bak'))
				file_put_contents($this->gitosis_dir.'/gitosis.conf.bak',file_get_contents($this->gitosis_dir.'/gitosis.conf'));

			/* Write gitosis.conf */
			if(is_writable($this->gitosis_dir.'/gitosis.conf') && @file_put_contents($this->gitosis_dir.'/gitosis.conf',$conf)) {
				$this->open_conf($this->gitosis_dir.'/gitosis.conf');

				return TRUE;
			} else
				return FALSE;
		} else
			return FALSE;
	}
}
?>
