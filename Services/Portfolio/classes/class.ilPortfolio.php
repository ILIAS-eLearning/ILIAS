<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Portfolio view
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id$
 *
 * @ingroup ServicesPortfolio
 */
class ilPortfolio
{
	protected $id; // [int]
	protected $user_id; // [int]
	protected $title; // [string]
	protected $description; // [string]
	protected $online; // [bool]
	protected $default; // [bool]

	/**
	 * Constructor
	 *
	 * @param int $a_id
	 * @param int $a_user_id
	 * @return object
	 */
	function __construct($a_id = null, $a_user_id = null)
	{
		global $ilUser;

		if(!$a_user_id)
		{
			$a_user_id = $ilUser->getId();
		}
		$this->setUserId($a_user_id);

		if($a_id)
		{
			$this->read($a_id);
		}
	}

	/**
	 * Set id
	 *
	 * @param int id
	 */
	function setId($a_val)
	{
		$this->id = (int)$a_val;
	}

	/**
	 * Get id
	 *
	 * @return int
	 */
	function getId()
	{
		return $this->id;
	}

	/**
	 * Set user id
	 *
	 * @param int user id
	 */
	function setUserId($a_val)
	{
		$this->user_id = (int)$a_val;
	}

	/**
	 * Get user id
	 *
	 * @return int
	 */
	function getUserId()
	{
		return $this->user_id;
	}

	/**
	 * Set title
	 *
	 * @param string $a_title Title
	 */
	function setTitle($a_title)
	{
		$this->title = (string)$a_title;
	}

	/**
	 * Get title
	 *
	 * @return string
	 */
	function getTitle()
	{
		return $this->title;
	}

	/**
	 * Set description
	 *
	 * @param string $a_desc Description
	 */
	function setDescription($a_desc)
	{
		$this->description = (string)$a_desc;
	}

	/**
	 * Get description
	 *
	 * @return string
	 */
	function getDescription()
	{
		return $this->description;
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

	/**
	 * Get properties for insert/update statements
	 *
	 * @return array
	 */
	protected function getPropertiesForDB()
	{
		$fields = array("user_id" => array("integer", $this->getUserId()),
			"title" => array("text", $this->getTitle()),
			"description" => array("text", $this->getDescription()),
			"is_online" => array("integer", $this->isOnline()),
			// "is_default" => array("integer", $this->isDefault())
			);

		return $fields;
	}

	/**
	 * Create new portfolio view
	 */
	function create()
	{
		global $ilDB;

		$id = $ilDB->nextId("usr_portfolio");
		$this->setId($id);

		$properties = $this->getPropertiesForDB();
		$properties["id"] = array("integer", $id);

		$ilDB->insert("usr_portfolio", $properties);
	}

	/**
	 * Update page
	 *
	 * @return	boolean
	 */
	function update()
	{
		global $ilDB;

		$id = $this->getId();
		if($id)
		{
			$properties = $this->getPropertiesForDB();

			$ilDB->update("usr_portfolio", $properties,
				array("id"=>array("integer", $id)));

			return true;
		}
		return false;
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

		$ilDB->manipulate("UPDATE usr_portfolio".
			" SET is_default = ".$ilDB->quote(false, "integer").
			" WHERE user_id = ".$ilDB->quote($a_user_id, "integer"));

		$ilDB->manipulate("UPDATE usr_portfolio".
			" SET is_default = ".$ilDB->quote(true, "integer").
			" WHERE user_id = ".$ilDB->quote($a_user_id, "integer").
			" AND id = ".$ilDB->quote($a_portfolio_id, "integer"));
	}
	
	/**
	 * Read page data
	 *
	 * @param int $a_id
	 * @return bool
	 */
	function read($a_id)
	{
		global $ilDB;
		
		$query = "SELECT * FROM usr_portfolio".
			" WHERE id = ".$ilDB->quote($a_id, "integer");
		$set = $ilDB->query($query);
		$rec = $ilDB->fetchAssoc($set);
		if($rec["id"])
		{
			$this->setId($rec["id"]);
			$this->setUserId($rec["user_id"]);
			$this->setTitle($rec["title"]);
			$this->setDescription($rec["description"]);
			$this->setOnline($rec["is_online"]);
			$this->setDefault($rec["is_default"]);
			return true;
		}
		return false;
	}

	/**
	 * Delete portfolio view
	 *
	 * @return bool
	 */
	function delete()
	{
		global $ilDB;

		$id = $this->getId();
		if($id)
		{
			// delete pages first
			include_once "Services/Portfolio/classes/class.ilPortfolioPage.php";
			$pages = ilPortfolioPage::getAllPages($id);
			if($pages)
			{
				foreach($pages as $page)
				{
					$obj = new ilPortfolioPage($id, $page["id"]);
					$obj->delete();
				}
			}

			$query = "DELETE FROM usr_portfolio".
				" WHERE id = ".$ilDB->quote($id, "integer");
			$ilDB->manipulate($query);
			return true;
		}
		return false;
	}

	/**
	 * Lookup property
	 *
	 * @param int id
	 * @param string $a_prop
	 * @return mixed
	 */
	protected static function lookupProperty($a_id, $a_prop)
	{
		global $ilDB;

		$set = $ilDB->query("SELECT ".(string)$a_prop.
			" FROM usr_portfolio".
			" WHERE id = ".$ilDB->quote($a_id, "integer"));
		$rec = $ilDB->fetchAssoc($set);
		return $rec[$a_prop];
	}

	/**
	 * Lookup user
	 *
	 * @param int $a_portfolio_id
	 * @return int
	 */
	static function lookupUserId($a_portfolio_id)
	{
		return self::lookupProperty($a_portfolio_id, "user_id");
	}

	/**
	 * Lookup title
	 *
	 * @param int $a_portfolio_id
	 * @return string
	 */
	static function lookupTitle($a_portfolio_id)
	{
		return self::lookupProperty($a_portfolio_id, "title");
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

		$set = $ilDB->query("SELECT * FROM usr_portfolio".
			" WHERE user_id = ".$ilDB->quote($a_user_id, "integer").
			" ORDER BY title");
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
			
		$set = $ilDB->query("SELECT id FROM usr_portfolio".
			" WHERE user_id = ".$ilDB->quote($a_user_id, "integer").
			" AND is_default = ".$ilDB->quote(1, "integer"));
		$res = $ilDB->fetchAssoc($set);
		if($res["id"])
		{
			return $res["id"];
		}		
	}
}

?>