<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once "Services/Object/classes/class.ilObject2.php";

/**
 * Portfolio 
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id$
 *
 * @ingroup ServicesPortfolio
 */
class ilObjPortfolio extends ilObject2
{
	protected $online; // [bool]
	protected $default; // [bool]

	function initType()
	{
		$this->type = "prtf";
	}

	/**
	 * Set online
	 *
	 * @param bool $a_value
	 */
	function setOnline($a_value)
	{
		$this->online = (bool)$a_value;
	}

	/**
	 * Is online?
	 *
	 * @return bool
	 */
	function isOnline()
	{
		return $this->online;
	}

	/**
	 * Set default
	 *
	 * @param bool $a_value
	 */
	function setDefault($a_value)
	{
		$this->default = (bool)$a_value;
	}

	/**
	 * Is default?
	 *
	 * @return bool
	 */
	function isDefault()
	{
		return $this->default;
	}
	
	protected function doRead()
	{
		global $ilDB;

		$set = $ilDB->query("SELECT * FROM usr_portfolio".
				" WHERE id = ".$ilDB->quote($this->id, "integer"));
		$row = $ilDB->fetchAssoc($set);
		$this->setOnline((bool)$row["is_online"]);
		$this->setDefault((bool)$row["is_default"]);		
	}

	protected function doCreate()
	{
		global $ilDB;
		
		$ilDB->manipulate("INSERT INTO usr_portfolio (id,is_online,is_default)".
			" VALUES (".$ilDB->quote($this->id, "integer").",".
			$ilDB->quote($this->isOnline(), "integer").",".
			$ilDB->quote($this->isDefault(), "integer").")");
	}
	
	protected function doUpdate()
	{
		global $ilDB;
		
		// must be online to be default
		if(!$this->isOnline() && $this->isDefault())
		{
			$this->setDefault(false);
		}
		
		$ilDB->manipulate("UPDATE usr_portfolio SET".
			" is_online = ".$ilDB->quote($this->isOnline(), "integer").
			", is_default = ".$ilDB->quote($this->isDefault(), "integer").
			" WHERE id = ".$ilDB->quote($this->id, "integer"));
	}

	protected function doDelete()
	{
		global $ilDB;

		$ilDB->manipulate("DELETE FROM usr_portfolio".
			" WHERE id = ".$ilDB->quote($this->id, "integer"));
	}
	
	/**
	 * Set the user default portfolio
	 *
	 * @param int $a_user_id
	 * @param int $a_portfolio_id
	 */
	public static function setUserDefault($a_user_id, $a_portfolio_id)
	{
		global $ilDB;
		
		$all = array();
		foreach(self::getPortfoliosOfUser($a_user_id) as $item)
		{
			$all[] = $item["id"];
		}
		if($all)
		{
			$ilDB->manipulate("UPDATE usr_portfolio".
				" SET is_default = ".$ilDB->quote(false, "integer").
				" WHERE ".$ilDB->in("id", $all, "", "integer"));
		}

		$ilDB->manipulate("UPDATE usr_portfolio".
			" SET is_default = ".$ilDB->quote(true, "integer").
			" WHERE id = ".$ilDB->quote($a_portfolio_id, "integer"));
	}

	/**
	 * Get views of user
	 *
	 * @param int $a_user_id
	 * @return array
	 */
	static function getPortfoliosOfUser($a_user_id)
	{
		global $ilDB;

		$set = $ilDB->query("SELECT up.*,od.title,od.description".
			" FROM usr_portfolio up".
			" JOIN object_data od ON (up.id = od.obj_id)".
			" WHERE od.owner = ".$ilDB->quote($a_user_id, "integer").
			" ORDER BY od.title");
		$res = array();
		while ($rec = $ilDB->fetchAssoc($set))
		{
			$res[] = $rec;
		}
		return $res;
	}
	
	/**
	 * Get default portfolio of user
	 * 
	 * @param type $a_user_id
	 * @return int
	 */
	static function getDefaultPortfolio($a_user_id)
	{
		global $ilDB, $ilSetting;
		
		if(!$ilSetting->get('user_portfolios'))
		{
			return;
		}
			
		$set = $ilDB->query("SELECT up.id FROM usr_portfolio up".
			" JOIN object_data od ON (up.id = od.obj_id)".
			" WHERE od.owner = ".$ilDB->quote($a_user_id, "integer").
			" AND up.is_default = ".$ilDB->quote(1, "integer"));
		$res = $ilDB->fetchAssoc($set);
		if($res["id"])
		{
			return $res["id"];
		}		
	}
}

?>