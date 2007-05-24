<?php
/**
 * ILIAS Open Source
 * --------------------------------
 * Implementation of ADL SCORM 2004
 * 
 * This program is free software. The use and distribution terms for this software
 * are covered by the GNU General Public License Version 2
 * 	<http://opensource.org/licenses/gpl-license.php>.
 * By using this software in any fashion, you are agreeing to be bound by the terms 
 * of this license.
 * 
 * Note: This code derives from other work by the original author that has been
 * published under Common Public License (CPL 1.0). Please send mail for more
 * information to <alfred.kohnert@bigfoot.com>.
 * 
 * You must not remove this notice, or any other, from this software.
 *  
 * PRELIMINARY EDITION 
 * This is work in progress and therefore incomplete and buggy ... 
 *  
 * Content-Type: application/x-httpd-php; charset=ISO-8859-1 
 * 
 * @author Alfred Kohnert <alfred.kohnert@bigfoot.com>
 * @version $Id$
 * @copyright: (c) 2005-2007 Alfred Kohnert
 * 
 * Business class for demonstration of current state of ILIAS SCORM 2004 
 * 
 * For security reasons this is not connected to ILIAS database
 * but uses a small fake database in slite2 format.
 * Waits on finishing other sub tasks before being connected to ILIAS.
 * 
 * "Playing" a SCORM Package to the end user
 * showing navigation and resources
 * tracking CMI API data     
 */ 



	
class ilSCORM13Player
{

	// zum client
	// vom client
	// prüfmuster
	// default wert
	const NONE = 0;
	const READONLY = 1;
	const WRITEONLY = 2;
	const READWRITE = 3;
	
		
		
	static private $schema = array // order of entries matters!
	(
		'package' => array(
			'user_id' =>  array('pattern'=>null, 'permission' => self::NONE, 'default'=>null),
			'learner_name' =>  array('pattern'=>null, 'permission' => self::NONE, 'default'=>null),
			'slm_id' =>  array('pattern'=>null, 'permission' => self::NONE, 'default'=>null),
			'mode' =>  array('pattern'=>null, 'permission' => self::NONE, 'default'=>null),
			'credit' =>  array('pattern'=>null, 'permission' => self::NONE, 'default'=>null),
		),
		'node' => array(
			'accesscount' =>  array('pattern'=>null, 'permission' => self::READWRITE, 'default'=>null),
			'accessduration' =>  array('pattern'=>null, 'permission' => self::READWRITE, 'default'=>null),
			'accessed' =>  array('pattern'=>null, 'permission' => self::READWRITE, 'default'=>null),
			'activityAbsoluteDuration' =>  array('pattern'=>null, 'permission' => self::READWRITE, 'default'=>null),
			'activityAttemptCount' =>  array('pattern'=>null, 'permission' => self::READWRITE, 'default'=>null),	
			'activityExperiencedDuration' =>  array('pattern'=>null, 'permission' => self::READWRITE, 'default'=>null),
			'activityProgressStatus' =>  array('pattern'=>null, 'permission' => self::READWRITE, 'default'=>null),
			'attemptAbsoluteDuration' =>  array('pattern'=>null, 'permission' => self::READWRITE, 'default'=>null),
			'attemptCompletionAmount' =>  array('pattern'=>null, 'permission' => self::READWRITE, 'default'=>null),
			'attemptCompletionStatus' =>  array('pattern'=>null, 'permission' => self::READWRITE, 'default'=>null),
			'attemptExperiencedDuration' =>  array('pattern'=>null, 'permission' => self::READWRITE, 'default'=>null),
			'attemptProgressStatus' =>  array('pattern'=>null, 'permission' => self::READWRITE, 'default'=>null),
			'audio_captioning' =>  array('pattern'=>null, 'permission' => self::READWRITE, 'default'=>null),
			'audio_level' =>  array('pattern'=>null, 'permission' => self::READWRITE, 'default'=>null),
			'availableChildren' =>  array('pattern'=>null, 'permission' => self::READWRITE, 'default'=>null),
			'cmi_node_id' =>  array('pattern'=>null, 'permission' => self::NONE, 'default'=>null),
			'completion' =>  array('pattern'=>null, 'permission' => self::READWRITE, 'default'=>null),
			'completion_status' =>  array('pattern'=>null, 'permission' => self::READWRITE, 'default'=>null),
			'completion_threshold' =>  array('pattern'=>null, 'permission' => self::READWRITE, 'default'=>null),
			'cp_node_id' =>  array('pattern'=>null, 'permission' => self::NONE, 'default'=>null),
			'created' =>  array('pattern'=>null, 'permission' => self::READWRITE, 'default'=>null),
			'credit' =>  array('pattern'=>null, 'permission' => self::READWRITE, 'default'=>null),
			'delivery_speed' =>  array('pattern'=>null, 'permission' => self::READWRITE, 'default'=>null),
			'exit' =>  array('pattern'=>null, 'permission' => self::READWRITE, 'default'=>null),
			'language' =>  array('pattern'=>null, 'permission' => self::READWRITE, 'default'=>null),
			'launch_data' =>  array('pattern'=>null, 'permission' => self::READWRITE, 'default'=>null),
			'learner_name' =>  array('pattern'=>null, 'permission' => self::READWRITE, 'default'=>null),
			'location' =>  array('pattern'=>null, 'permission' => self::READWRITE, 'default'=>null),
			'max' =>  array('pattern'=>null, 'permission' => self::READWRITE, 'default'=>null),
			'min' =>  array('pattern'=>null, 'permission' => self::READWRITE, 'default'=>null),
			'mode' =>  array('pattern'=>null, 'permission' => self::READWRITE, 'default'=>null),
			'modified' =>  array('pattern'=>null, 'permission' => self::READWRITE, 'default'=>null),
			'progress_measure' =>  array('pattern'=>null, 'permission' => self::READWRITE, 'default'=>null),
			'raw' =>  array('pattern'=>null, 'permission' => self::READWRITE, 'default'=>null),
			'scaled' =>  array('pattern'=>null, 'permission' => self::READWRITE, 'default'=>null),
			'scaled_passing_score' =>  array('pattern'=>null, 'permission' => self::READWRITE, 'default'=>null),	
			'session_time' =>  array('pattern'=>null, 'permission' => self::READWRITE, 'default'=>null),
			'success_status' =>  array('pattern'=>null, 'permission' => self::READWRITE, 'default'=>null),
			'suspend_data' =>  array('pattern'=>null, 'permission' => self::READWRITE, 'default'=>null),
			'total_time' =>  array('pattern'=>null, 'permission' => self::READWRITE, 'default'=>null),
			'user_id' =>  array('pattern'=>null, 'permission' => self::NONE, 'default'=>null),
		),
		'comment' => array (
			'cmi_comment_id' =>  array('pattern'=>null, 'permission' => self::NONE, 'default'=>null),	
			'cmi_node_id' =>  array('pattern'=>null, 'permission' => self::NONE, 'default'=>null),
			'comment' =>  array('pattern'=>null, 'permission' => self::READWRITE, 'default'=>null),	
			'timestamp' =>  array('pattern'=>null, 'permission' => self::READWRITE, 'default'=>null),	
			'location' =>  array('pattern'=>null, 'permission' => self::READWRITE, 'default'=>null),	
			'sourceIsLMS' =>  array('pattern'=>null, 'permission' => self::READWRITE, 'default'=>null),
		),
		'correct_response' => array(
			'cmi_correct_response_id' =>  array('pattern'=>null, 'permission' => self::NONE, 'default'=>null),	
			'cmi_interaction_id' =>  array('pattern'=>null, 'permission' => self::NONE, 'default'=>null),
			'pattern' =>  array('pattern'=>null, 'permission' => self::READWRITE, 'default'=>null),
		),
		'interaction' => array(
			'cmi_interaction_id' =>  array('pattern'=>null, 'permission' => self::NONE, 'default'=>null),	
			'cmi_node_id' =>  array('pattern'=>null, 'permission' => self::NONE, 'default'=>null),
			'description' =>  array('pattern'=>null, 'permission' => self::READWRITE, 'default'=>null),
			'id' =>  array('pattern'=>null, 'permission' => self::READWRITE, 'default'=>null),
			'latency' =>  array('pattern'=>null, 'permission' => self::READWRITE, 'default'=>null),
			'learner_response' =>  array('pattern'=>null, 'permission' => self::READWRITE, 'default'=>null),
			'result' =>  array('pattern'=>null, 'permission' => self::READWRITE, 'default'=>null),
			'timestamp' =>  array('pattern'=>null, 'permission' => self::READWRITE, 'default'=>null),
			'type' =>  array('pattern'=>null, 'permission' => self::READWRITE, 'default'=>null),
			'weighting' =>  array('pattern'=>null, 'permission' => self::READWRITE, 'default'=>null),
		),
		'objective' => array(
			'cmi_interaction_id' =>  array('pattern'=>null, 'permission' => self::NONE, 'default'=>null),	
			'cmi_node_id' =>  array('pattern'=>null, 'permission' => self::NONE, 'default'=>null),
			'cmi_objective_id' =>  array('pattern'=>null, 'permission' => self::NONE, 'default'=>null),
			'completion_status' =>  array('pattern'=>null, 'permission' => self::READWRITE, 'default'=>null),
			'description' =>  array('pattern'=>null, 'permission' => self::READWRITE, 'default'=>null),
			'id' =>  array('pattern'=>null, 'permission' => self::READWRITE, 'default'=>null),
			'max' =>  array('pattern'=>null, 'permission' => self::READWRITE, 'default'=>null),
			'min' =>  array('pattern'=>null, 'permission' => self::READWRITE, 'default'=>null),
			'raw' =>  array('pattern'=>null, 'permission' => self::READWRITE, 'default'=>null),
			'scaled' =>  array('pattern'=>null, 'permission' => self::READWRITE, 'default'=>null),
			'success_status' =>  array('pattern'=>null, 'permission' => self::READWRITE, 'default'=>null),
			'progress_measure' =>  array('pattern'=>null, 'permission' => self::READWRITE, 'default'=>null),
			'scope' =>  array('pattern'=>null, 'permission' => self::READWRITE, 'default'=>null),			
		),
	);
	
	private $userId;
	public $packageId;
	public $jsMode;
		
	function __construct() 
	{
		if ($_REQUEST['learnerId']) {
			$this->userId = $_REQUEST['learnerId'];
		} else {
			$this->userId = $GLOBALS['USER']['usr_id'];
		}
		$this->packageId = (int) $_REQUEST['packageId'];
		$this->jsMode = strpos($_SERVER['HTTP_ACCEPT'], 'text/javascript')!==false;
		
		//ilSCORM13DB::addQueries('ilSCORM13Player');
	}
	
	public function getLangStrings()
	{
		$return = array();
		foreach (ilSCORM13DB::query('SELECT identifier, value FROM lng_data 
			WHERE module=? AND lang_key=?', 
			array('scorm13', 'en')) as $row) 
		{
			$return[$row['identifier']] = $row['value']; 
		}
		return $return;
	}	

	public function getPlayer()
	{
		$config = array
		(
			'cp_url' => $_SERVER['SCRIPT_NAME'] . '?call=cp&packageId=' . $this->packageId,
			'cmi_url' => $_SERVER['SCRIPT_NAME'] .'?call=cmi&packageId=' . $this->packageId,
			'learner_id' => (string) $GLOBALS["USER"]["id_usr"],
			'learner_name' => $GLOBALS["USER"]["login"],
			'mode' => 'normal',
			'credit' => 'credit',
			'package_url' =>  str_replace('{packageId}', $this->packageId, IL_OP_PACKAGE_BASE),
		);
		
		$langstrings = $this->getLangStrings();
		
		$langstrings['btnStart'] = 'Start'; 
		$langstrings['btnResumeAll'] = 'Resume All';  
		$langstrings['btnBackward'] = 'backward';
		$langstrings['btnForward'] = 'Forward';
		$langstrings['btnExit'] = 'Exit';
		$langstrings['btnExitAll'] = 'Exit All';
		$langstrings['btnAbandon'] = 'Abandon';
		$langstrings['btnAbandonAll'] = 'Abandon All';
		$langstrings['btnSuspendAll'] = 'Suspend All';
		$langstrings['btnPrevious'] = 'Previous';
		$langstrings['btnContinue'] = 'Next';
		$langstrings['lblChoice'] = 'Select a choice from the tree.';
		
		$config['langstrings'] = $langstrings;
		
 		header('Content-Type: text/html; charset=UTF-8');
		$tpl = new SimpleTemplate();
		$tpl->setParam('DEBUG', (int) $_REQUEST['debug']);
		if ($_REQUEST['debug']) 
		{
			$tpl->load('templates/tpl/tpl.scorm2004.player_debug.html');
			$tpl->setParam('INCLUDE_DEBUG', $tpl->save(null));
		}
		else
		{
			$tpl->setParam('INCLUDE_DEBUG', '');
		}
		$tpl->load('templates/tpl/tpl.scorm2004.player.html');
		$tpl->setParam('JSON_LANGSTRINGS', json_encode($langstrings));
		$tpl->setParams($langstrings);
		$tpl->setParam('DOC_TITLE', 'ILIAS SCORM 2004 Player');
		$tpl->setParam('THEME_CSS', 'templates/css/delos.css');
		$tpl->setParam('CSS_NEEDED', '');
		$tpl->setParam('JS_NEEDED', '');
		$tpl->setParam('JS_DATA', json_encode($config));
		$tpl->setParam('BASE_DIR', '');
		list($tsfrac, $tsint) = explode(' ', microtime()); 
		$tpl->setParam('TIMESTAMP', sprintf('%d%03d', $tsint, 1000*(float)$tsfrac));
		$tpl->save();
	}
	
	public function getCPData()
	{
		$packageData = ilSCORM13DB::getRecord(
			'cp_package', 
			'obj_id', 
			$this->packageId
		);
		$jsdata = $packageData['jsdata'];
		if (!$jsdata) $jsdata = 'null';
		if ($this->jsMode) 
		{
			header('Content-Type: text/javascript; charset=UTF-8');
			print($jsdata);
		}
		else
		{
			header('Content-Type: text/plain; charset=UTF-8');
			$jsdata = json_decode($jsdata);
			print_r($jsdata);	
		}
	}
	
	public function fetchCMIData()
	{
		$data = $this->getCMIData($this->userId, $this->packageId);
		if ($this->jsMode) 
		{
			header('Content-Type: text/javascript; charset=UTF-8');
			print(json_encode($data));
		}
		else
		{
			header('Content-Type: text/plain; charset=UTF-8');
			print(var_export($data, true));
		}
	}
	
	public function persistCMIData($data = null)
	{
		$data = json_decode(is_string($data) ? $data : file_get_contents('php://input'));
		$return = $this->setCMIData($this->userId, $this->packageId, $data);
		if ($this->jsMode) 
		{
			header('Content-Type: text/javascript; charset=UTF-8');
			print(json_encode($return));
		}
		else
		{
			header('Content-Type: text/html; charset=UTF-8');
			print(var_export($return, true));
		}
	}
	
	/**
	 * maps API data structure type to internal datatype on a node	
	 * and accepts only valid values, dropping invalid ones from input	 
	 */
	private function normalizeFields($table, &$node) 
	{
		return;
		foreach (self::$schema[$table] as $k => $v) 
		{
			$value = $node->$k; 
			if (isset($value) && is_string($v) && !preg_match($v, $value)) 
			{
				unset($node->$k);
			}
		}
	}

	private function getCMIData($userId, $packageId) 
	{
		$result = array(
			'schema' => array(), 
			'data' => array()
		);
		foreach (self::$schema as $k=>&$v)
		{
			$result['schema'][$k] = array_keys($v);
			$result['data'][$k] = ilSCORM13DB::query('view_cmi_' . $k, array($userId, $packageId), 
				null, null, PDO::FETCH_NUM);
		}
		return $result;
	}
	

	private function removeCMIData($userId, $packageId, $cp_node_id=null) 
	{
		$delorder = array('correct_response', 'objective', 'interaction', 'comment', 'node');
		error_log("Delete, User:".$userId."Package".$packageId."Node: ".$cp_node_id);
		foreach ($delorder as $k) 
		{
			if (is_null($cp_node_id))
			{
				ilSCORM13DB::exec(
					'delete_cmi_' . $k . 's', 
					array($userId, $packageId)
				);
			}
			else
			{
				ilSCORM13DB::exec(
					'delete_cmi_' . $k , 
					array($cp_node_id)
				);
			}
		} 
	}
	
	private function setCMIData($userId, $packageId, $data) 
	{
		$result = array();
		$map = array();
		if (!$data) return;
	
		// we don't want to have trouble with partially deleted or filled datasets
		// so we try transaction mode (hopefully your RDBS supports this)
		//ilSCORM13DB::begin();

		// we have to specify the exact order to add records
		// since there are dependencies
		$tables = array('node', 'comment', 'interaction', 'objective', 'correct_response');

		foreach ($tables as $table)
		{
			$schem = & self::$schema[$table];
			if (!is_array($data->$table)) continue;
			$i=0;
				
			// build up numerical index for schema fields
			foreach ($schem as &$field) 
			{
				$field['no'] = $i++;
			}
			// now iterate through data rows from input
			foreach ($data->$table as &$row)
			{
				
				// first fill some fields that could not be set from client side
				// namely the database id's depending on which table is processed  
				switch ($table)
				{
					case 'correct_response':
						$no = $schem['cmi_interaction_id']['no'];
						$row[$no] = $map['interaction'][$row[$no]];
					case 'comment':
					case 'interaction':
						$no = $schem['cmi_node_id']['no'];
						$row[$no] = $map['node'][$row[$no]];
						break;
					case 'objective':
						$no = $schem['cmi_interaction_id']['no'];
						$row[$no] = $map['interaction'][$row[$no]];
						$no = $schem['cmi_node_id']['no'];
						$row[$no] = $map['node'][$row[$no]];
						break;
					case 'node':
						$no = $schem['user_id']['no'];
						$row[$no] = $userId;
						break;
					
				}
				$cp_no = $schem['cp_' . $table . '_id']['no'];						 
				$cmi_no = $schem['cmi_' . $table . '_id']['no'];
				
				// get current id for later use
				// this is either a real db id or document unique string generated by client 
				$cmi_id = $row[$cmi_no]; 
				// set if field to null, so it will be filled up by autoincrement
				$row[$cmi_no] = null;
				// TODO validate values
				// create sql statement, RDBS should support "REPLACE" command
				$sql = 'REPLACE INTO cmi_' . $table . ' (' . implode(', ', array_keys($schem)) 
					. ') VALUES (' . implode(', ', array_fill(0, count($schem), '?')) . ')';
				// if we process a table we have to destroy all data on this activity 
				// and related sub elements in interactions etc.
				if ($table==='node') 
				{
					error_log("Lets remove old data");
					$this->removeCMIData($userId, $packageId, $row[$cp_no]);
				}
				// now insert the data record
				$ret=ilSCORM13DB::exec($sql, $row);
				
				if (!$ret)
				{
					$return = false;
					break;
				}
				// and get the new cmi_id
				$row[$cmi_no] = ilSCORM13DB::getLastId();
				// if we process a node save new id into result object that will be feedback for client
				if ($table==='node') 
				{
					$result[(string)$row[$cp_no]] = $row[$cmi_no];
				}
				
				// add new id to mapping table for later use on dependend elements 
				$map[$table][$cmi_id] = $row[$cmi_no];
			}
		}
		//$return===false ? ilSCORM13DB::rollback() : ilSCORM13DB::commit();
		return $result;
	}
	
	/**
	 * estimate content type for a filename by extension
	 * first do it for common static web files from external list
	 * if not found peek into file by slow php function mime_content_type()
	 * @param $filename required
	 * @return string mimetype name e.g. image/jpeg
	 */
	public function getMimetype($filename) 
	{
		$mimetypes = array();
		require_once('classes/mimemap.php');
		$info = pathinfo($filename);
		$ext = $mimetypes[$info['extension']];
		return $ext ? $ext : mime_content_type($filename);
	}
	
	/**
	 * getting and setting Scorm2004 cookie
	 * Cookie contains enrypted associative array of sahs_lm.id and permission value
	 * you may enforce stronger symmetrical encryption by adding RC4 via mcrypt()
	 **/
	public function getCookie() 
	{
		return unserialize(base64_decode($_COOKIE[IL_OP_COOKIE_NAME]));
	}
	
	public function setCookie($cook) 
	{
		setCookie(IL_OP_COOKIE_NAME, base64_encode(serialize($cook)));
	}
	
	/**
	 * Try to find file, identify content type, write it to buffer, and stop immediatly
	 * If no file given, read file from PATH_INFO, check permission by cookie, and write out and stop.	 
	 * @param $path filename
	 * @return void	 
	 */	 	
	public function readFile($path) 
	{

		if (headers_sent()) 
		{
			die('Error: Cookie could not be established');
		}
		
		$SAHS_LM_POSITION = 1; // index position of sahs_lm id in splitted path_info
	
		$comp = explode('/', (string) $path);
		$sahs = $comp[$SAHS_LM_POSITION];
		$cook = $this->getCookie();
		$perm = $cook[$sahs];
		
		if (!$perm) 
		{
			// check login an package access
			// TODO add rbac check function here
			$perm = 1;
			if (!$perm) 
			{
				header('HTTP/1.0 401 Unauthorized');
				die('/* Unauthorized */');
			}
			// write cookie
			$cook[$sahs] = $perm;
			$this->setCookie($cook);
		}
		
		$path = '.' . $path;
		if (!is_file($path))
		{
			header('HTTP/1.0 404 Not Found');
			die('/* Not Found ' . $path . '*/');
		} 
		
		// send mimetype to client
		header('Content-Type: ' . $this->getMimetype($path));
	
		// let page be cached in browser for session duration
		header('Expires: ' . gmdate('D, d M Y H:i:s', time() + session_cache_expire()*60) . ' GMT');
		header('Cache-Control: private');
	
		// now show it to the user and be fine
		readfile($path);
		die();
	
	} 
	
}	

?>
