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
	
	static private $schema = array(
		'package' => array(
			'user_id' =>  array('pattern'=>null, 'permission' => self::NONE, 'default'=>null),
			'learner_name' =>  array('pattern'=>null, 'permission' => self::NONE, 'default'=>null),
			'slm_id' =>  array('pattern'=>null, 'permission' => self::NONE, 'default'=>null),
			'mode' =>  array('pattern'=>null, 'permission' => self::NONE, 'default'=>null),
			'credit' =>  array('pattern'=>null, 'permission' => self::NONE, 'default'=>null),
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
		'node' => array(
			'accesscount' =>  array('pattern'=>null, 'permission' => self::READWRITE, 'default'=>null),
			'accessduration' =>  array('pattern'=>null, 'permission' => self::READWRITE, 'default'=>null),
			'accessed' =>  array('pattern'=>null, 'permission' => self::READWRITE, 'default'=>null),
			'activityAbsoluteDuration' =>  array('pattern'=>null, 'permission' => self::READWRITE, 'default'=>null),
			'activityAttemptCount' =>  array('pattern'=>null, 'permission' => self::READWRITE, 'default'=>null),	
			'activityExperiencedDuration' =>  array('pattern'=>null, 'permission' => self::READWRITE, 'default'=>null),
			'activityProgressState' =>  array('pattern'=>null, 'permission' => self::READWRITE, 'default'=>null),
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
		),
	);
	
	private $userId;
	private $packageId;
	private $jsMode;
	
	function __construct() 
	{
		$this->userId = IL_OP_USER_ID;
		$this->packageId = IL_OP_PACKAGE_ID;
		$this->jsMode = strpos($_SERVER['HTTP_ACCEPT'], 'text/javascript')!==false;
		ilSCORM13DB::addQueries('ilSCORM13Player');
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
		$config = array(
			'cp' => $_SERVER['SCRIPT_NAME'] . '?call=cp&packageId=' . $this->packageId,
			'cmi' => $_SERVER['SCRIPT_NAME'] .'?call=cmi&packageId=' . $this->packageId,
			'learner_id' => (string) IL_OP_USER_ID,
			'learner_name' => IL_OP_USER_NAME,
			'mode' => 'normal',
			'credit' => 'credit',
		);
		$gui = array(
			'base' =>  str_replace('{packageId}', $this->packageId, IL_OP_PACKAGE_BASE),		);
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
		$tpl->setParam('LOADING', '');
		$tpl->setParam('CSS_NEEDED', '');
		$tpl->setParam('JS_NEEDED', '');
		$tpl->setParam('JSON_CONFIG', json_encode($config));
		$tpl->setParam('JSON_GUI', json_encode($gui));
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
			header('Content-Type: text/html; charset=UTF-8');
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
			$result['data'][$k] = ilSCORM13DB::query('view_cmi_' . $k, array($userId, $packageId), null, null, PDO::FETCH_NUM);
		}
		return $result;
	}
	

	private function removeCMIData($userId, $packageId, $cp_node_id=null) 
	{
		$delorder = array('correct_response', 'objective', 'interaction', 'comment', 'node');
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
		$addorder = array('node', 'comment', 'interaction', 'objective', 'correct_response');
		foreach ($addorder as $k)
		{
			$v = & self::$schema[$k];
			if (!is_array($data->$k)) continue;
			$i=0;
			foreach ($v as &$vv) 
			{
				$vv['no'] = $i++;
			}
			foreach ($data->$k as &$row)
			{
				switch ($k)
				{
					case 'correct_response':
						$n = $v['cmi_interaction_id']['no'];
						$row[$n] = $map['interaction'][$row[$n]];
					case 'comment':
					case 'interaction':
						$n = $v['cmi_node_id']['no'];
						$row[$n] = $map['node'][$row[$n]];
						break;
					case 'objective':
						$n = $v['cmi_interaction_id']['no'];
						$row[$n] = $map['interaction'][$row[$n]];
						$n = $v['cmi_node_id']['no'];
						$row[$n] = $map['node'][$row[$n]];
						break;
					case 'node':
						$n = $v['user_id']['no'];
						$row[$n] = $userId;
						break;
				}
				
				$n = $v['cp_' . $k . '_id']['no'];						 
				$key = $v['cmi_' . $k . '_id']['no'];
				$value = $row[$key];
				$row[$key] = null;
				// TODO validate values
				$sql = 'REPLACE INTO cmi_' . $k . ' (' . implode(', ', array_keys($v)) . ') VALUES (' . implode(', ', array_fill(0, count($v), '?')) . ')';
				if ($k==='node') 
				{
					$this->removeCMIData($userId, $packageId, $row[$n]);
				}
				if (!ilSCORM13DB::exec($sql, $row))
				{
					$return = false;
					break;
				}
				$row[$key] = is_numeric($row[$key]) ? $row[$key] : ilSCORM13DB::getLastId();
				if ($k==='node') 
				{
					$result[(string)$row[$n]] = $row[$key];
				}
				$map[$k][$value] = $row[$key];
			}
		}
		//$return===false ? ilSCORM13DB::rollback() : ilSCORM13DB::commit();
		return $result;
	}
	
}	

?>
