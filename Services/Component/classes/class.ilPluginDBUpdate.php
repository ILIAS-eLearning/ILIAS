<?php
/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 ********************************************************************
 */

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
    protected \ilPluginInfo $plugin;

    /**
     * constructor
     * @noinspection MagicMethodsValidityInspection
     */
    public function __construct(
        \ilDBInterface $db,
        \ilPluginInfo $plugin
    ) {
        $this->client_ini = null;
        $this->db = $db;
        $this->plugin = $plugin;

        $this->currentVersion = $plugin->getCurrentDBVersion() ?? 0;

        $this->current_file = $this->getFileForStep(0 /* doesn't matter */);
        $this->DB_UPDATE_FILE = $this->PATH . $this->getDBUpdateScriptName();
        $this->LAST_UPDATE_FILE = $this->DB_UPDATE_FILE;

        $this->readDBUpdateFile();
        $this->readLastUpdateFile();
        $this->readFileVersion();

        $class_map = require ILIAS_ABSOLUTE_PATH . '/libs/composer/vendor/composer/autoload_classmap.php';
        $this->ctrl_structure_iterator = new ilCtrlArrayIterator($class_map);
    }

    /**
     * FROM ilDBUpdate
     */
    public function getFileForStep(int $a_version /* doesn't matter */): string
    {
        return "dbupdate.php";
    }

    /**
     * Get current DB version
     */
    public function getCurrentVersion(): int
    {
        return $this->currentVersion;
    }

    /**
     * Set current DB version
     */
    public function setCurrentVersion(int $a_version): void
    {
        $this->currentVersion = $a_version;
    }

    public function loadXMLInfo(): bool
    {
        return true;
    }

    /**
     * This is a very simple check. Could be done better.
     */
    public function checkQuery(string $q): bool
    {
        if ((is_int(stripos($q, "create table")) || is_int(stripos($q, "alter table")) ||
                is_int(stripos($q, "drop table")))
            && !is_int(stripos($q, $this->getTablePrefix()))) {
            return false;
        }

        return true;
    }

    public function getDBUpdateScriptName(): string
    {
        return $this->plugin->getPath() . self::PLUGIN_UPDATE_FILE;
    }

    protected function getTablePrefix(): string
    {
        $component = $this->plugin->getComponent();
        $slot = $this->plugin->getPluginSlot();
        return $component->getId() . "_" . $slot->getId() . "_" . $this->plugin->getId();
    }
}
