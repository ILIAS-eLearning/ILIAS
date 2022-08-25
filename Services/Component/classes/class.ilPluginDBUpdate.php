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
 *********************************************************************/

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

    private \ilPluginInfo $plugin;

    private string $db_update_file;
    private ?int $current_version;
    private ?int $file_version = null;

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

        $this->db_update_file = $this->PATH . $this->getDBUpdateScriptName();

        $this->current_version = $plugin->getCurrentDBVersion() ?? 0;

        $this->readDBUpdateFile();
        $this->readFileVersion();

        $class_map = require ILIAS_ABSOLUTE_PATH . '/libs/composer/vendor/composer/autoload_classmap.php';
        $this->ctrl_structure_iterator = new ilCtrlArrayIterator($class_map);
    }

    private function readDBUpdateFile(): void
    {
        if (!file_exists($this->db_update_file)) {
            $this->error = 'no_db_update_file';
            $this->filecontent = [];
            return;
        }

        $this->filecontent = @file($this->db_update_file);
    }

    private function readFileVersion(): void
    {
        //go through filecontent and search for last occurence of <#x>
        reset($this->filecontent);
        $regs = [];
        $version = 0;
        foreach ($this->filecontent as $row) {
            if (preg_match('/^\<\#([0-9]+)>/', $row, $regs)) {
                $version = $regs[1];
            }
        }

        $this->file_version = (int) $version;
    }

    public function getCurrentVersion(): int
    {
        return $this->current_version;
    }

    protected function checkQuery(string $q): bool
    {
        if ((is_int(stripos($q, 'create table')) || is_int(stripos($q, 'alter table')) ||
                is_int(stripos($q, 'drop table')))
            && !is_int(stripos($q, $this->getTablePrefix()))) {
            return false;
        }

        return true;
    }

    private function getTablePrefix(): string
    {
        $component = $this->plugin->getComponent();
        $slot = $this->plugin->getPluginSlot();
        return $component->getId() . '_' . $slot->getId() . '_' . $this->plugin->getId();
    }

    private function getDBUpdateScriptName(): string
    {
        return $this->plugin->getPath() . self::PLUGIN_UPDATE_FILE;
    }

    /**
     * Apply update
     * @return false|void
     */
    public function applyUpdate()
    {
        $ilCtrlStructureReader = null;
        $ilDB = null;
        $this->initGlobalsRequiredForUpdateSteps($ilCtrlStructureReader, $ilDB);

        $file_version = $this->file_version;
        $current_version = $this->current_version;

        $this->updateMsg = 'no_changes';
        if ($current_version < $file_version) {
            $msg = [];
            for ($i = ($current_version + 1); $i <= $file_version; $i++) {
                if ($this->applyUpdateNr($i) === false) {
                    $msg[] = 'msg: update_error - ' . $this->error . '; nr: ' . $i . ';';
                    $this->updateMsg = implode("\n", $msg);

                    return false;
                }

                $msg[] = 'msg: update_applied; nr: ' . $i . ';';
            }

            $this->updateMsg = implode('\n', $msg);
        }
    }
}
