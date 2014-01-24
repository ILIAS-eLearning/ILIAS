<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/DidacticTemplate/classes/class.ilDidacticTemplateSetting.php';

/**
 * Didactical template settings
 *
 * @author Stefan Meyer <meyer@leifos.com>
 * @defgroup ServicesDidacticTemplate
 */
class ilDidacticTemplateSettings
{
	private static $instance = null;
	private static $instances = null;


	private $templates = array();
	private $obj_type = '';

	/**
	 * Constructor
	 * @param int $a_id
	 */
	private function __construct($a_obj_type = '')
	{
		$this->obj_type = $a_obj_type;
		$this->read();
	}

	/**
	 * Get singelton instance
	 * @return ilDidacticTemplateSetting
	 */
	public static function getInstance()
	{
		if(self::$instance)
		{
			return self::$instance;
		}
		return self::$instance = new ilDidacticTemplateSettings();
	}

	/**
	 * Get instance by obj type
	 * @param string $a_obj_type
	 * @return ilDidacticTemplateSettings
	 */
	public static function getInstanceByObjectType($a_obj_type)
	{
		if(self::$instances[$a_obj_type])
		{
			return self::$instances[$a_obj_type];
		}
		return self::$instances[$a_obj_type] = new ilDidacticTemplateSettings($a_obj_type);
	}

	/**
	 * Get templates
	 * @return array ilDidacticTemplateSetting
	 */
	public function getTemplates()
	{
		return (array) $this->templates;
	}

	/**
	 * Get object type
	 * @return string
	 */
	public function getObjectType()
	{
		return $this->obj_type;
	}

	/**
	 * Read disabled templates
	 */
	public function readInactive()
	{

		global $ilDB;

		$query = 'SELECT dtpl.id FROM didactic_tpl_settings dtpl ';

		if($this->getObjectType())
		{
			$query .= 'JOIN didactic_tpl_sa tplsa ON dtpl.id = tplsa.id ';
		}
		$query .= 'WHERE enabled = '.$ilDB->quote(0,'integer').' ';

		if($this->getObjectType())
		{
			$query .= 'AND obj_type = '.$ilDB->quote($this->getObjectType(),'text');
		}

		$res = $ilDB->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$this->templates[$row->id] = new ilDidacticTemplateSetting($row->id);
		}
		return true;
	}

	/**
	 * Read active didactic templates
	 * @global ilDB $ilDB
	 * @return bool
	 */
	protected function read()
	{
		global $ilDB;

		$query = 'SELECT dtpl.id FROM didactic_tpl_settings dtpl ';

		if($this->getObjectType())
		{
			$query .= 'JOIN didactic_tpl_sa tplsa ON dtpl.id = tplsa.id ';
		}
		$query .= 'WHERE enabled = '.$ilDB->quote(1,'integer').' ';

		if($this->getObjectType())
		{
			$query .= 'AND obj_type = '.$ilDB->quote($this->getObjectType(),'text');
		}

		$res = $ilDB->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$this->templates[$row->id] = new ilDidacticTemplateSetting($row->id);
		}
		return true;
	}
}

?>