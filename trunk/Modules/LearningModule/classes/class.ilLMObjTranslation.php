<?php

/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Translation information on lm object 
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup ModulesLearningModule
 */
class ilLMObjTranslation
{
	protected $lang;
	protected $title;
	protected $create_date;
	protected $last_update;

	/**
	 * Constructor
	 *
	 * @param int $a_id object id (page, chapter)
	 * @param string $a_lang language code
	 */
	function __construct($a_id = 0, $a_lang = "")
	{
		if ($a_id > 0 && $a_lang != "")
		{
			$this->setId($a_id);
			$this->setLang($a_lang);
			$this->read();
		}
	}
	
	/**
	 * Set Id
	 *
	 * @param int $a_val id	
	 */
	function setId($a_val)
	{
		$this->id = $a_val;
	}
	
	/**
	 * Get Id
	 *
	 * @return int id
	 */
	function getId()
	{
		return $this->id;
	}
	
	/**
	 * Set lang
	 *
	 * @param string $a_val language	
	 */
	function setLang($a_val)
	{
		$this->lang = $a_val;
	}
	
	/**
	 * Get lang
	 *
	 * @return string language
	 */
	function getLang()
	{
		return $this->lang;
	}
	
	/**
	 * Set title
	 *
	 * @param string $a_val title	
	 */
	function setTitle($a_val)
	{
		$this->title = $a_val;
	}
	
	/**
	 * Get title
	 *
	 * @return string title
	 */
	function getTitle()
	{
		return $this->title;
	}
	
	/**
	 * Get create date
	 *
	 * @return string create date
	 */
	function getCreateDate()
	{
		return $this->create_date;
	}

	/**
	 * Get update date
	 *
	 * @return string update date
	 */
	function getLastUpdate()
	{
		return $this->last_update;
	}
	
	/**
	 * Read
	 */
	function read()
	{
		global $ilDB;
		
		$set = $ilDB->query("SELECT * FROM lm_data_transl ".
			" WHERE id = ".$ilDB->quote($this->getId(), "integer").
			" AND lang = ".$ilDB->quote($this->getLang(), "text")
			);
		$rec  = $ilDB->fetchAssoc($set);
		$this->setTitle($rec["title"]);
		$this->create_date = $rec["create_date"];
		$this->last_update = $rec["last_update"];
	}
	
	/**
	 * Save (inserts if not existing, otherwise updates)
	 */
	function save()
	{
		global $ilDB;
		
		if (!self::exists($this->getId(), $this->getLang()))
		{
			$ilDB->manipulate("INSERT INTO lm_data_transl ".
				"(id, lang, title, create_date, last_update) VALUES (".
				$ilDB->quote($this->getId(), "integer").",".
				$ilDB->quote($this->getLang(), "text").",".
				$ilDB->quote($this->getTitle(), "text").",".
				$ilDB->now().",".
				$ilDB->now().
				")");
		}
		else
		{
			$ilDB->manipulate("UPDATE lm_data_transl SET ".
				" title = ".$ilDB->quote($this->getTitle(), "text").",".
				" last_update = ".$ilDB->now().
				" WHERE id = ".$ilDB->quote($this->getId(), "integer").
				" AND lang = ".$ilDB->quote($this->getLang(), "text")
				);
		}
	}

	/**
	 * Check for existence
	 *
	 * @param int $a_id object id (page, chapter)
	 * @param string $a_lang language code
	 * @return bool true/false
	 */
	static function exists($a_id, $a_lang)
	{
		global $ilDB;
		
		$set = $ilDB->query("SELECT * FROM lm_data_transl ".
			" WHERE id = ".$ilDB->quote($a_id, "integer").
			" AND lang = ".$ilDB->quote($a_lang, "text")
			);
		if($rec  = $ilDB->fetchAssoc($set))
		{
			return true;
		}
		return false;
	}

	/**
	 * Copy all translations of an object
	 *
	 * @param int $a_source_id source id
	 * @param int $a_target_id target
	 */
	static function copy($a_source_id, $a_target_id)
	{
		global $ilDB;

		$set = $ilDB->query("SELECT * FROM lm_data_transl ".
			" WHERE id = ".$ilDB->quote($a_source_id, "integer")
			);
		while ($rec = $ilDB->fetchAssoc($set))
		{
			$lmobjtrans = new ilLMObjTranslation($a_target_id, $rec["lang"]);
			$lmobjtrans->setTitle($rec["title"]);
			$lmobjtrans->save();
		}
	}

}

?>
