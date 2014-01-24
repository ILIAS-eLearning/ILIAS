<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* Export options
*
* @author Stefan Meyer <meyer@leifos.com>
*
* @version $Id$
*
* @ingroup ServicesExport
*/
class ilExportOptions
{
	private static $instance = null;
	
	const EXPORT_EXISTING = 1;
	const EXPORT_BUILD = 2;
	const EXPORT_OMIT = 3;
	
	const KEY_INIT = 1;
	const KEY_ITEM_MODE = 2;
	const KEY_ROOT = 3;
	
	private $export_id = 0;
	private $ref_options = array();
	private $obj_options = array();
	private $options = array();
	
	/**
	 * Singleton constructor
	 * @return 
	 */
	private function __construct($a_export_id)
	{
		$this->export_id = $a_export_id;
		$this->read();
	}
	
	/**
	 * Get singelton instance
	 * @return object ilExportOptions
	 */
	public static function getInstance()
	{
		if(self::$instance)
		{
			return self::$instance;
		}
	}
	
	/**
	 * Create new instance
	 * @param object $a_export_id
	 * @return object ilExportOptions
	 */
	public static function newInstance($a_export_id)
	{
		return self::$instance = new ilExportOptions($a_export_id);
	}
	
	/**
	 * Allocate a new export id
	 * @return 
	 */
	public static function allocateExportId()
	{
		global $ilDB;
		
		// get last export id
		$query = 'SELECT MAX(export_id) exp FROM export_options '.
			'GROUP BY export_id ';
		$res = $ilDB->query($query);
		$exp_id = 1;
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$exp_id = $row->exp + 1;
		}
		$query = 'INSERT INTO export_options (export_id,keyword,ref_id,obj_id,value) '.
			'VALUES( '.
			$ilDB->quote($exp_id,'integer').', '.
			$ilDB->quote(self::KEY_INIT,'integer').', '.
			$ilDB->quote(0,'integer').', '.
			$ilDB->quote(0,'integer').', '.
			$ilDB->quote(0,'integer').' '.
			')';
		$ilDB->manipulate($query);

		return $exp_id; 
	}
	
	/**
	 * Get all subitems with mode <code>ilExportOptions::EXPORT_BUILD</code>
	 * @param int ref_id of source 
	 * @return 
	 */
	public function getSubitemsForCreation($a_source_id)
	{
		$refs = array();

		foreach((array) $this->ref_options[self::KEY_ITEM_MODE] as $ref_id => $mode)
		{
			if($mode == self::EXPORT_BUILD)
			{
				$refs[] = $ref_id;
			}
		}
		return $refs;
	}
	
	/**
	 * Get all subitems with mode != self::EXPORT_OMIT
	 * @return array ref ids
	 */
	public function getSubitemsForExport()
	{
		$refs = array();
		foreach((array) $this->ref_options[self::KEY_ITEM_MODE] as $ref_id => $mode)
		{
			if($mode != self::EXPORT_OMIT)
			{
				$refs[] = $ref_id;
			}
		}
		return $refs;
	}
	
	/**
	 * Get export id
	 * @return 
	 */
	public function getExportId()
	{
		return $this->export_id;
	}
	
	public function addOption($a_keyword, $a_ref_id, $a_obj_id, $a_value)
	{
		global $ilDB;
		
		$query = "SELECT MAX(pos) position FROM export_options";
		$res = $ilDB->query($query);
		
		$pos = 0;
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$pos = $row->position;
		}
		$pos++;
		
		$query = 'INSERT INTO export_options (export_id,keyword,ref_id,obj_id,value,pos) '.
			'VALUES( '.
			$ilDB->quote($this->getExportId(),'integer').', '.
			$ilDB->quote($a_keyword,'integer').', '.
			$ilDB->quote($a_ref_id,'integer').', '.
			$ilDB->quote($a_obj_id,'integer').', '.
			$ilDB->quote($a_value,'integer').', '.
			$ilDB->quote($pos,'integer').' '.
			')';
		$ilDB->manipulate($query);
	}

	/**
	 * Get option
	 * @param object $a_keyword
	 * @return 
	 */
	public function getOption($a_keyword)
	{
		return isset($this->options[$a_keyword]) ? $this->options[$a_keyword] : null;
	}
	
	/**
	 * Get option by 
	 * @param object $a_obj_id
	 * @param object $a_keyword
	 * @return 
	 */
	public function getOptionByObjId($a_obj_id,$a_keyword)
	{
		return isset($this->obj_options[$a_keyword][$a_obj_id]) ? $this->obj_options[$a_keyword][$a_obj_id] : null;
	}
	
	/**
	 * Get option by 
	 * @param object $a_obj_id
	 * @param object $a_keyword
	 * @return 
	 */
	public function getOptionByRefId($a_ref_id,$a_keyword)
	{
		return isset($this->ref_options[$a_keyword][$a_ref_id]) ? $this->ref_options[$a_keyword][$a_ref_id] : null;
	}
	
	/**
	 * Delete by export id
	 * @return 
	 */
	public function delete()
	{
		global $ilDB;
		
		$query = "DELETE FROM export_options ".
			"WHERE export_id = ".$ilDB->quote($this->getExportId(),'integer');
		$ilDB->manipulate($query);
		return true;
	}

	/**
	 * Read entries
	 * @return 
	 */
	public function read()
	{
		global $ilDB;
		
		$this->options = array();
		$this->obj_options = array();
		$this->ref_options = array(); 
		
		$query = "SELECT * FROM export_options ".
			"WHERE export_id = ".$ilDB->quote($this->getExportId(),'integer').' '.
			"ORDER BY pos";
		$res = $ilDB->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			if($row->ref_id)
			{
				$this->ref_options[$row->keyword][$row->ref_id] = $row->value;
			}
			if($row->obj_id)
			{
				$this->obj_options[$row->keyword][$row->obj_id] = $row->value;
			}
			if(!$row->ref_id and !$row->obj_id)
			{
				$this->options[$row->keyword] = $row->value;
			}
			
		}
		return true;
	}
}
?>