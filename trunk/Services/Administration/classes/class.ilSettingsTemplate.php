<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Settings template application class
 *
 * @author Alex Killing <alex.killing>
 * @version $Id$
 * @ingroup ServicesAdministration
 */
class ilSettingsTemplate
{
	private $id;
	private $type;
	private $title;
	private $description;
	private $setting = array();
	private $hidden_tab = array();

        /**
         *
         * @var ilSettingsTemplateConfig
         */
        private $config;

	/**
	 * Constructor
	 *
	 * @param
	 */
	function __construct($a_id = 0, $config = null)
	{
		if ($a_id > 0)
		{
                        if ($config)
                            $this->setConfig($config);
			$this->setId($a_id);
			$this->read();
		}
	}

	/**
	 * Set id
	 *
	 * @param	integer	id
	 */
	function setId($a_val)
	{
		$this->id = $a_val;
	}

	/**
	 * Get id
	 *
	 * @return	integer	id
	 */
	function getId()
	{
		return $this->id;
	}

	/**
	 * Set title
	 *
	 * @param	string	title
	 */
	function setTitle($a_val)
	{
		$this->title = $a_val;
	}

	/**
	 * Get title
	 *
	 * @return	string	title
	 */
	function getTitle()
	{
		return $this->title;
	}

	/**
	 * Set type
	 *
	 * @param	string	$a_val	type
	 */
	public function setType($a_val)
	{
		$this->type = $a_val;
	}

	/**
	 * Get type
	 *
	 * @return	string	type
	 */
	public function getType()
	{
		return $this->type;
	}

	/**
	 * Set description
	 *
	 * @param	string	$a_val	description
	 */
	public function setDescription($a_val)
	{
		$this->description = $a_val;
	}

	/**
	 * Get description
	 *
	 * @return	string	description
	 */
	public function getDescription()
	{
		return $this->description;
	}

	/**
	 * Set setting
	 *
	 * @param string setting
	 * @param mixed value
	 * @param boolean hide the setting?
	 */
	function setSetting($a_setting, $a_value, $a_hide = false)
	{
                if ($this->getConfig()) {
                    $settings = $this->getConfig()->getSettings();

                    if ($settings[$a_setting]['type'] == ilSettingsTemplateConfig::CHECKBOX) {
                        if (is_array($a_value))
                            $a_value = serialize($a_value);
                        else
                            $a_value = unserialize($a_value);
                    }
                }

		$this->setting[$a_setting] = array(
			"value" => $a_value,
			"hide" => $a_hide
		);
	}

	/**
	 * Remove setting
	 *
	 * @param string setting
	 */
	function removeSetting($a_setting)
	{
		unset($this->setting[$a_setting]);
	}

	/**
	 * Remove all settings
	 */
	function removeAllSettings()
	{
		$this->setting = array();
	}

	/**
	 * Get settings
	 */
	function getSettings()
	{
		return $this->setting;
	}

	/**
	 * Add hidden tab
	 *
	 * @param string tab id
	 * @return
	 */
	function addHiddenTab($a_tab_id)
	{
		$this->hidden_tab[$a_tab_id] = $a_tab_id;
	}

	/**
	 * Remove all hidden tabs
	 */
	function removeAllHiddenTabs()
	{
		$this->hidden_tab = array();
	}

	/**
	 * Get hidden tabs
	 */
	function getHiddenTabs()
	{
		return $this->hidden_tab;
	}
	
        /**
         * Returns the template config associated with this template or NULL if
         * none is given.
         * 
         * @return ilSettingsTemplateConfig
         */
        public function getConfig() {
            return $this->config;
        }

        /**
         * Sets the template config for this template
         * 
         * @param ilSettingsTemplateConfig $config
         */
        public function setConfig(ilSettingsTemplateConfig $config) {
            $this->config = $config;
        }


	/**
	 * Read
	 *
	 * @param
	 * @return
	 */
	function read()
	{
		global $ilDB;

		// read template
		$set = $ilDB->query("SELECT * FROM adm_settings_template WHERE ".
			" id = ".$ilDB->quote($this->getId(), "integer")
			);
		$rec = $ilDB->fetchAssoc($set);
		$this->setTitle($rec["title"]);
		$this->setType($rec["type"]);
		$this->setDescription($rec["description"]);

		// read template setttings
		$set = $ilDB->query("SELECT * FROM adm_set_templ_value WHERE ".
			" template_id = ".$ilDB->quote($this->getId(), "integer")
			);
		while ($rec = $ilDB->fetchAssoc($set))
		{
			$this->setSetting($rec["setting"],
				$rec["value"], $rec["hide"]);
		}

		// read hidden tabs
		$set = $ilDB->query("SELECT * FROM adm_set_templ_hide_tab WHERE ".
			" template_id = ".$ilDB->quote($this->getId(), "integer")
			);
		while ($rec = $ilDB->fetchAssoc($set))
		{
			$this->addHiddenTab($rec["tab_id"]);
		}
	}

	/**
	 * Create settings template
	 */
	function create()
	{
		global $ilDB;

		$this->setId($ilDB->nextId("adm_settings_template"));

		// write template
		$ilDB->insert("adm_settings_template", array(
			"id" => array("integer", $this->getId()),
			"title" => array("text", $this->getTitle()),
			"type" => array("text", $this->getType()),
			"description" => array("clob", $this->getDescription())
			));

		// write settings
		$this->insertSettings();

		// write hidden tabs
		$this->insertHiddenTabs();
	}

	/**
	 * Update settings template
	 */
	public function update()
	{
		global $ilDB;

		// update template
		$ilDB->update("adm_settings_template", array(
			"title" => array("text", $this->getTitle()),
			"type" => array("text", $this->getType()),
			"description" => array("clob", $this->getDescription())
			), array(
			"id" => array("integer", $this->getId()),
			));

		// delete settings and hidden tabs
		$ilDB->manipulate("DELETE FROM adm_set_templ_value WHERE "
			." template_id = ".$ilDB->quote($this->getId(), "integer")
			);
		$ilDB->manipulate("DELETE FROM adm_set_templ_hide_tab WHERE "
			." template_id = ".$ilDB->quote($this->getId(), "integer")
			);

		// insert settings and hidden tabs
		$this->insertSettings();
		$this->insertHiddenTabs();

	}

	/**
	 * Insert settings to db
	 */
	private function insertSettings()
	{
		global $ilDB;

		foreach ($this->getSettings() as $s => $set)
		{
			$ilDB->manipulate("INSERT INTO adm_set_templ_value ".
				"(template_id, setting, value, hide) VALUES (".
				$ilDB->quote($this->getId(), "integer").",".
				$ilDB->quote($s, "text").",".
				$ilDB->quote($set["value"], "text").",".
				$ilDB->quote($set["hide"], "integer").
				")");
		}
	}

	/**
	 * Insert hidden tabs
	 */
	function insertHiddenTabs()
	{
		global $ilDB;

		foreach ($this->getHiddenTabs() as $tab_id)
		{
			$ilDB->manipulate("INSERT INTO adm_set_templ_hide_tab ".
				"(template_id, tab_id) VALUES (".
				$ilDB->quote($this->getId(), "integer").",".
				$ilDB->quote($tab_id, "text").
				")");
		}
	}

	/**
	 * Delete settings template
	 */
	function delete()
	{
		global $ilDB;

		$ilDB->manipulate("DELETE FROM adm_settings_template WHERE "
			." id = ".$ilDB->quote($this->getId(), "integer")
			);
		$ilDB->manipulate("DELETE FROM adm_set_templ_value WHERE "
			." template_id = ".$ilDB->quote($this->getId(), "integer")
			);
		$ilDB->manipulate("DELETE FROM adm_set_templ_hide_tab WHERE "
			." template_id = ".$ilDB->quote($this->getId(), "integer")
			);
	}

	/**
	 * Get all settings templates of type
	 *
	 * @param string $a_type object type
	 */
	static function getAllSettingsTemplates($a_type)
	{
		global $ilDB;

		$set = $ilDB->query("SELECT * FROM adm_settings_template ".
			" WHERE type = ".$ilDB->quote($a_type, "text").
			" ORDER BY title");

		$settings_template = array();
		while ($rec = $ilDB->fetchAssoc($set))
		{
			$settings_template[] = $rec;
		}

		return $settings_template;
	}

	/**
	 * Lookup property
	 *
	 * @param	id		level id
	 * @return	mixed	property value
	 */
	protected static function lookupProperty($a_id, $a_prop)
	{
		global $ilDB;

		$set = $ilDB->query("SELECT $a_prop FROM adm_settings_template WHERE ".
			" id = ".$ilDB->quote($a_id, "integer")
			);
		$rec = $ilDB->fetchAssoc($set);
		return $rec[$a_prop];
	}

	/**
	 * Lookup title
	 *
	 * @param
	 * @return
	 */
	static function lookupTitle($a_id)
	{
		return ilSettingsTemplate::lookupProperty($a_id, "title");
	}

}

?>
