<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/COPage/classes/class.ilPageObject.php");

/**
 * Page for extended public profile
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 *
 * @ingroup ServicesUser
 */
class ilExtPublicProfilePage extends ilPageObject
{
	/**
	 * Constructor
	 *
	 * @access	public
	 * @param	wiki page id
	 */
	function __construct($a_id = 0, $a_old_nr = 0)
	{
		parent::__construct("user", $a_id, $a_old_nr);
	}

	/**
	 * Set Title
	 *
	 * @param	string	$a_title	Title
	 */
	function setTitle($a_title)
	{
		$this->title = $a_title;
	}

	/**
	 * Get Title.
	 *
	 * @return	string	Title
	 */
	function getTitle()
	{
		return $this->title;
	}

	/**
	 * Set user id
	 *
	 * @param	int	user id
	 */
	function setUserId($a_val)
	{
		$this->setParentId($a_val);
	}

	/**
	 * Get user id
	 *
	 * @return	int	user id
	 */
	function getUserId()
	{
		return $this->getParentId();
	}

	/**
	 * Set order nr
	 *
	 * @param	int	order nr
	 */
	function setOrderNr($a_val)
	{
		$this->order_nr = $a_val;
	}

	/**
	 * Get order nr
	 *
	 * @return	int	order nr
	 */
	function getOrderNr()
	{
		return $this->order_nr;
	}

	/**
	 * Lookup max order nr for user
	 *
	 * @param
	 * @return
	 */
	static function lookupMaxOrderNr($a_user_id)
	{
		global $ilDB;

		$set = $ilDB->query("SELECT MAX(order_nr) m FROM usr_ext_profile_page WHERE ".
			" user_id = ".$ilDB->quote($a_user_id, "integer"));
		$rec = $ilDB->fetchAssoc($set);

		return (int) $rec["m"];
	}

	/**
	 * Create new extended public profile page
	 */
	function create()
	{
		global $ilDB;

		$this->setOrderNr(ilExtPublicProfilePage::lookupMaxOrderNr($this->getUserId()) + 10);

		$id = $ilDB->nextId("usr_ext_profile_page");
		$this->setId($id);
		$query = "INSERT INTO usr_ext_profile_page (".
			"id".
			", title".
			", user_id".
			", order_nr".
			" ) VALUES (".
			$ilDB->quote($this->getId(), "integer")
			.",".$ilDB->quote($this->getTitle(), "text")
			.",".$ilDB->quote($this->getUserId(), "integer")
			.",".$ilDB->quote($this->getOrderNr(), "integer")
			.")";
		$ilDB->manipulate($query);

		parent::create();
		$this->saveInternalLinks($this->getXMLContent());
	}

	/**
	 * Update page
	 *
	 * @return	boolean
	 */
	function update($a_validate = true, $a_no_history = false)
	{
		global $ilDB;
		
		// update wiki page data
		$query = "UPDATE usr_ext_profile_page SET ".
			" title = ".$ilDB->quote($this->getTitle(), "text").
			",user_id = ".$ilDB->quote($this->getUserId(), "integer").
			",order_nr = ".$ilDB->quote($this->getOrderNr(), "integer").
			" WHERE id = ".$ilDB->quote($this->getId(), "integer");
		$ilDB->manipulate($query);
		parent::update($a_validate, $a_no_history);

		return true;
	}
	
	/**
	 * Read page data
	 */
	function read()
	{
		global $ilDB;
		
		$query = "SELECT * FROM usr_ext_profile_page WHERE id = ".
			$ilDB->quote($this->getId(), "integer");
		$set = $ilDB->query($query);
		$rec = $ilDB->fetchAssoc($set);

		$this->setTitle($rec["title"]);
		$this->setUserId($rec["user_id"]);
		$this->setOrderNr($rec["order_nr"]);
		
		// get co page
		parent::read();
	}


	/**
	* delete wiki page and al related data	
	*
	* @access	public
	*/
	function delete()
	{
		global $ilDB;
		
		// delete internal links information to this page
		include_once("./Services/COPage/classes/class.ilInternalLink.php");
		ilInternalLink::_deleteAllLinksToTarget("user", $this->getId());
		
		// delete record of table usr_ext_profile_page
		$query = "DELETE FROM usr_ext_profile_page".
			" WHERE id = ".$ilDB->quote($this->getId(), "integer");
		$ilDB->manipulate($query);
		
		// delete co page
		parent::delete();

		return true;
	}


	/**
	 * Lookup user
	 */
	static function lookupUserId($a_page_id)
	{
		global $ilDB;

		return ilExtPublicProfilePage::lookupProperty($a_page_id, "user_id");
	}

	/**
	 * Lookup user
	 */
	static function lookupTitle($a_page_id)
	{
		global $ilDB;

		return ilExtPublicProfilePage::lookupProperty($a_page_id, "title");
	}

	/**
	 * Lookup profile page property
	 *
	 * @param	id		page id
	 * @return	mixed	property value
	 */
	protected static function lookupProperty($a_id, $a_prop)
	{
		global $ilDB;

		$set = $ilDB->query($q = "SELECT $a_prop FROM usr_ext_profile_page WHERE ".
			" id = ".$ilDB->quote($a_id, "integer")
			);
		$rec = $ilDB->fetchAssoc($set);
		return $rec[$a_prop];
	}

	/**
	 * Get tabs of user
	 *
	 * @param	int		user id
	 * @return
	 */
	static function getPagesOfUser($a_user_id)
	{
		global $ilDB;

		$set = $ilDB->query("SELECT * FROM usr_ext_profile_page WHERE ".
			" user_id = ".$ilDB->quote($a_user_id, "integer").
			" ORDER BY order_nr");
		$tabs = array();
		while ($rec = $ilDB->fetchAssoc($set))
		{
			$tabs[] = $rec;
		}
		return $tabs;
	}

	/**
	 * Fix ordering
	 *
	 * @param int $a_user_id
	 * @return
	 */
	public static function fixOrdering($a_user_id)
	{
		global $ilDB;

		$pages = self::getPagesOfUser($a_user_id);
		$cnt = 10;
		foreach ($pages as $p)
		{
			$ilDB->manipulate("UPDATE usr_ext_profile_page SET ".
				" order_nr = ".$ilDB->quote($cnt, "integer").
				" WHERE id = ".$ilDB->quote($p["id"], "integer")
			);
			$cnt+= 10;
		}
	}
}
?>
