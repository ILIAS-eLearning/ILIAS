<?php

/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Multi-language properties 
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup 
 */
class ilPageMultiLang
{
	protected $db;
	protected $parent_type;
	protected $parent_id;
	protected $master_lang;
	protected $languages = array();
	
	/**
	 * Constructor
	 *
	 * @param
	 * @return
	 */
	function __construct($a_parent_type, $a_parent_id)
	{
		global $ilDB;
		
		$this->db = $ilDB;
		
		$this->setParentType($a_parent_type);
		$this->setParentId($a_parent_id);

		if ($this->getParentType() == "")
		{
			include_once("./Services/COPage/exceptions/class.ilCOPageException.php");
			throw new ilCOPageException("ilPageMultiLang: No parent type passed.");
		}
		
		if ($this->getParentId() <= 0)
		{
			include_once("./Services/COPage/exceptions/class.ilCOPageException.php");
			throw new ilCOPageException("ilPageMultiLang: No parent ID passed.");
		}
		
		$this->read();
	}
	
	/**
	 * Set parent type
	 *
	 * @param string $a_val parent type	
	 */
	function setParentType($a_val)
	{
		$this->parent_type = $a_val;
	}
	
	/**
	 * Get parent type
	 *
	 * @return string parent type
	 */
	function getParentType()
	{
		return $this->parent_type;
	}
	
	/**
	 * Set parent id
	 *
	 * @param int $a_val parent id	
	 */
	function setParentId($a_val)
	{
		$this->parent_id = $a_val;
	}
	
	/**
	 * Get parent id
	 *
	 * @return int parent id
	 */
	function getParentId()
	{
		return $this->parent_id;
	}
	
	/**
	 * Set master language
	 *
	 * @param string $a_val master language	
	 */
	function setMasterLanguage($a_val)
	{
		$this->master_lang = $a_val;
	}
	
	/**
	 * Get master language
	 *
	 * @return string master language
	 */
	function getMasterLanguage()
	{
		return $this->master_lang;
	}

	/**
	 * Set languages
	 *
	 * @param array $a_val array of language codes	
	 */
	function setLanguages(array $a_val)
	{
		$this->languages = $a_val;
	}
	
	/**
	 * Get languages
	 *
	 * @return array array of language codes
	 */
	function getLanguages()
	{
		return $this->languages;
	}
	
	/**
	 * Read
	 *
	 * @param
	 * @return
	 */
	function read()
	{
		$set = $this->db->query("SELECT * FROM copg_multilang ".
			" WHERE parent_type = ".$this->db->quote($this->getParentType(), "text").
			" AND parent_id = ".$this->db->quote($this->getParentId(), "integer")
			);
		$rec = $this->db->fetchAssoc($set);
		$this->setMasterLanguage($rec["master_lang"]);

		$this->setLanguages(array());
		$set = $this->db->query("SELECT * FROM copg_multilang_lang ".
			" WHERE parent_type = ".$this->db->quote($this->getParentType(), "text").
			" AND parent_id = ".$this->db->quote($this->getParentId(), "integer")
			);
		while ($rec = $this->db->fetchAssoc($set))
		{
			$this->addLanguage($rec["lang"]);
		}
	}
	
	/**
	 * Save
	 *
	 * @param
	 * @return
	 */
	function save()
	{
		$this->db->manipulate("DELETE FROM copg_multilang ".
			" WHERE parent_type = ".$this->db->quote($this->getParentType(), "text").
			" AND parent_id = ".$this->db->quote($this->getParentId(), "integer")
			);
		$this->db->manipulate("DELETE FROM copg_multilang_lang ".
			" WHERE parent_type = ".$this->db->quote($this->getParentType(), "text").
			" AND parent_id = ".$this->db->quote($this->getParentId(), "integer")
			);
		
		$this->db->manipulate("INSERT INTO copg_multilang ".
			"(parent_type, parent_id, master_lang) VALUES (".
			$this->db->quote($this->getParentType(), "text").",".
			$this->db->quote($this->getParentId(), "integer").",".
			$this->db->quote($this->getMasterLanguage(), "text").
			")");
		
		foreach ($this->getLanguages() as $lang)
		{
			$this->db->manipulate("INSERT INTO copg_multilang ".
				"(parent_type, parent_id, lang) VALUES (".
				$this->db->quote($this->getParentType(), "text").",".
				$this->db->quote($this->getParentId(), "integer").",".
				$this->db->quote($lang, "text").
				")");
		}
	}

}

?>
