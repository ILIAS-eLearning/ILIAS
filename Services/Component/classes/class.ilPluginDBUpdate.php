<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\Data\Version;

/**
* Database Update class
*
* @author Peter Gabriel <pgabriel@databay.de>
* @author Richard Klees <richard.klees@concepts-and-training.de>
* @version $Id: class.ilDBUpdate.php 15875 2008-02-03 13:56:32Z akill $
*/
class ilPluginDBUpdate extends ilDBUpdate
{
    protected const PLUGIN_UPDATE_FILE = "/sql/dbupdate.php";

    protected \ilDBInterface $db;
    protected \ilComponentDataDBWrite $component_data_db;
    protected string $plugin_id;
    protected ?Version $target_version;

    /**
     * constructor
     * @noinspection MagicMethodsValidityInspection
     */
    public function __construct(
        \ilComponentDataDBWrite $component_data_db,
        \ilDBInterface $db,
        string $plugin_id,
        Version $target_version = null
    ) {
        $this->component_data_db = $component_data_db;
        $this->db = $db;
        $this->plugin_id = $plugin_id;
        $this->target_version = $target_version;

        $this->current_file = $this->getFileForStep(0 /* doesn't matter */);
        ;
        $this->DB_UPDATE_FILE = $this->PATH . $this->getDBUpdateScriptName();
        $this->LAST_UPDATE_FILE = $this->DB_UPDATE_FILE;

        $this->readDBUpdateFile();
        $this->readLastUpdateFile();
        $this->readFileVersion();
    }

    protected function getPluginInfo() : \ilPluginInfo
    {
        return $this->component_data_db->getPluginById($this->plugin_id);
    }

    /**
     * FROM ilDBUpdate
     */
    public function getFileForStep(int $_ /* doesn't matter */) : string
    {
        return "dbupdate.php";
    }

    /**
     * Get current DB version
     */
    public function getCurrentVersion() : int
    {
        $this->currentVersion = $this->getPluginInfo()->getCurrentDBVersion() ?? null;
        return $this->currentVersion;
    }

    /**
     * Set current DB version
     */
    public function setCurrentVersion(int $a_version) : void
    {
        if (!is_numeric($a_version)) {
            throw new \InvalidArgumentException(
                "Expected numeric version, got '$a_version' instead."
            );
        }
        $this->component_data_db->setCurrentPluginVersion(
            $this->plugin_id,
            $this->target_version,
            (int) $a_version
        );
    }

    public function loadXMLInfo()
    {
        return true;
    }
    
    /**
     * This is a very simple check. Could be done better.
     */
    public function checkQuery(string $q) : bool
    {
        if ((is_int(stripos($q, "create table")) || is_int(stripos($q, "alter table")) ||
                is_int(stripos($q, "drop table")))
            && !is_int(stripos($q, $this->getTablePrefix()))) {
            return "Plugin may only create or alter tables that use prefix " .
                $this->getTablePrefix();
        } else {
            return true;
        }

        return true;
    }

    public function getDBUpdateScriptName() : string
    {
        $plugin = $this->getPluginInfo();
        $component = $plugin->getComponent();
        $slot = $plugin->getPluginSlot();
        return "Customizing/global/plugins/" . $component->getType() . "/" . $component->getName() . "/" .
            $slot->getName() . "/" . $plugin->getName() . self::PLUGIN_UPDATE_FILE;
    }

    protected function getTablePrefix() : string
    {
        $plugin = $this->getPluginInfo();
        $component = $plugin->getComponent();
        $slot = $plugin->getPluginSlot();

        return $component->getId() . "_" . $slot->getId() . "_" . $plugin->getId();
    }
}
