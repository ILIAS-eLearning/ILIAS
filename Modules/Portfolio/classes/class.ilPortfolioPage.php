<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/COPage/classes/class.ilPageObject.php");
include_once("./Modules/Portfolio/classes/class.ilObjPortfolio.php");

/**
 * Page for user portfolio
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id$
 *
 * @ingroup ModulesPortfolio
 */
class ilPortfolioPage extends ilPageObject
{
	protected $portfolio_id;
	protected $type = 1;
	protected $title;
	protected $order_nr;
	
	const TYPE_PAGE = 1;
	const TYPE_BLOG = 2;
	
	/**
	 * Get parent type
	 *
	 * @return string parent type
	 */
	function getParentType()
	{
		return "prtf";
	}
	
	/**
	 * Set portfolio id
	 *
	 * @param int $a_val portfolio id	
	 */
	function setPortfolioId($a_val)
	{
		$this->portfolio_id = $a_val;
	}
	
	/**
	 * Get portfolio id
	 *
	 * @return int portfolio id
	 */
	function getPortfolioId()
	{
		return $this->portfolio_id;
	}
	
	/**
	 * Set type
	 *
	 * @param	int	type
	 */
	function setType($a_val)
	{
		$this->type = $a_val;
	}

	/**
	 * Get type
	 *
	 * @return	int	type
	 */
	function getType()
	{
		return $this->type;
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
		global $lng;
		
		// because of migration of extended user profiles
		if($this->title == "###-")
		{
			return $lng->txt("profile");
		}
		
		return $this->title;
	}

	/**
	 * Set order nr
	 *
	 * @param	int	order nr
	 */
	function setOrderNr($a_val)
	{
		$this->order_nr = (int)$a_val;
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
	 * Lookup max order nr for portfolio
	 *
	 * @param int $a_portfolio_id
	 * @return int
	 */
	static function lookupMaxOrderNr($a_portfolio_id)
	{
		global $ilDB;

		$set = $ilDB->query("SELECT MAX(order_nr) m FROM usr_portfolio_page".
			" WHERE portfolio_id = ".$ilDB->quote($a_portfolio_id, "integer"));
		$rec = $ilDB->fetchAssoc($set);
		return (int) $rec["m"];
	}

	/**
	 * Get properties for insert/update statements
	 *
	 * @return array
	 */
	protected function getPropertiesForDB()
	{
		$fields = array("portfolio_id" => array("integer", $this->portfolio_id),
			"type" => array("integer", $this->getType()),
			"title" => array("text", $this->getTitle()),
			"order_nr" => array("integer", $this->getOrderNr()));

		return $fields;
	}

	/**
	 * Create new portfolio page
	 */
	function create($a_import = false)
	{
		global $ilDB;

		if(!$a_import)
		{
			$this->setOrderNr(self::lookupMaxOrderNr($this->portfolio_id) + 10);
		}

		$id = $ilDB->nextId("usr_portfolio_page");
		$this->setId($id);

		$fields = $this->getPropertiesForDB();
		$fields["id"] = array("integer", $id);

		$ilDB->insert("usr_portfolio_page", $fields);

		if(!$a_import)
		{
			parent::create();
			// $this->saveInternalLinks($this->getDomDoc());
		}
	}

	/**
	 * Update page
	 *
	 * @return	boolean
	 */
	function update($a_validate = true, $a_no_history = false)
	{
		global $ilDB;
		
		$id = $this->getId();
		if($id)
		{
			$fields = $this->getPropertiesForDB();
			$ilDB->update("usr_portfolio_page", $fields,
				array("id"=>array("integer", $id)));

			parent::update($a_validate, $a_no_history);
			return true;
		}
		return false;
	}
	
	/**
	 * Read page data
	 */
	function read()
	{
		global $ilDB;
		
		$query = "SELECT * FROM usr_portfolio_page".
			" WHERE id = ".$ilDB->quote($this->getId(), "integer");
		$set = $ilDB->query($query);
		$rec = $ilDB->fetchAssoc($set);

		$this->setPortfolioId($rec["portfolio_id"]);
		$this->setType($rec["type"]);
		$this->setTitle($rec["title"]);
		$this->setOrderNr($rec["order_nr"]);
		
		// get co page
		parent::read();
	}

	/**
	 * delete portfolio page and all related data
	 */
	function delete()
	{
		global $ilDB;

		$id = $this->getId();
		if($id)
		{
			// delete internal links information to this page
			include_once("./Services/Link/classes/class.ilInternalLink.php");
			ilInternalLink::_deleteAllLinksToTarget("user", $this->getId());

			// delete record of table usr_portfolio_page
			$query = "DELETE FROM usr_portfolio_page".
				" WHERE id = ".$ilDB->quote($this->getId(), "integer");
			$ilDB->manipulate($query);
		
			// delete co page
			parent::delete();
		}
	}

	/**
	 * Lookup portfolio page property
	 *
	 * @param int $a_id
	 * @param string $a_prop
	 * @return mixed
	 */
	protected static function lookupProperty($a_id, $a_prop)
	{
		global $ilDB;

		$set = $ilDB->query("SELECT ".$a_prop.
			" FROM usr_portfolio_page".
			" WHERE id = ".$ilDB->quote($a_id, "integer"));
		$rec = $ilDB->fetchAssoc($set);
		return $rec[$a_prop];
	}

	/**
	 * Lookup title
	 *
	 * @param int $a_page_id
	 */
	static function lookupTitle($a_page_id)
	{
		return self::lookupProperty($a_page_id, "title");
	}

	/**
	 * Get pages of portfolio
	 *
	 * @param int $a_portfolio_id
	 * @return array
	 */
	static function getAllPages($a_portfolio_id)
	{
		global $ilDB, $lng;

		$set = $ilDB->query("SELECT * FROM usr_portfolio_page".
			" WHERE portfolio_id = ".$ilDB->quote($a_portfolio_id, "integer").
			" ORDER BY order_nr");
		$pages = array();
		while ($rec = $ilDB->fetchAssoc($set))
		{
			// because of migration of extended user profiles
			if($rec["title"] == "###-")
			{
				$rec["title"] = $lng->txt("profile");
			}
			
			$pages[] = $rec;			
		}
		return $pages;
	}

	/**
	 * Fix ordering
	 *
	 * @param int $a_portfolio_id
	 */
	public static function fixOrdering($a_portfolio_id)
	{
		global $ilDB;

		$pages = self::getAllPages($a_portfolio_id);
		$cnt = 10;
		foreach ($pages as $p)
		{
			$ilDB->manipulate("UPDATE usr_portfolio_page SET ".
				" order_nr = ".$ilDB->quote($cnt, "integer").
				" WHERE id = ".$ilDB->quote($p["id"], "integer")
			);
			$cnt+= 10;
		}
	}
	
	/**
	 * Get portfolio id of page id 
	 * 
	 * @param int $a_page_id
	 * @return int
	 */
	public static function findPortfolioForPage($a_page_id)
	{
		return self::lookupProperty($a_page_id, "portfolio_id");
	}
}
?>
