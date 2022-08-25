<?php

declare(strict_types=1);

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

/**
 * Database Update class
 * @author  Peter Gabriel <pgabriel@databay.de>
 * @author  Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup ServicesDatabase
 */
class ilDBUpdate
{
    protected string $updateMsg;
    protected ilDBInterface $db;
    protected ?ilIniFile $client_ini = null;
    protected Iterator $ctrl_structure_iterator;

    protected string $error;
    protected string $PATH = './';

    protected array $filecontent;

    private ?int $custom_updates_current_version = 0;
    private ?int $custom_updates_file_version = null;
    private ?bool $custom_updates_info_read = null;
    private ilSetting $custom_updates_setting;
    private array $custom_updates_content;

    public function __construct(ilDBInterface $a_db_handler, ilIniFile $client_ini = null)
    {
        $this->client_ini = $client_ini;
        $this->db = $a_db_handler;
        $this->PATH = "./";

        $class_map = require ILIAS_ABSOLUTE_PATH . '/libs/composer/vendor/composer/autoload_classmap.php';
        $this->ctrl_structure_iterator = new ilCtrlArrayIterator($class_map);
    }

    private function execQuery(ilDBInterface $db, string $str): bool
    {
        $q = "";
        $sql = explode("\n", trim($str));
        foreach ($sql as $i => $statement) {
            $sql[$i] = trim($statement);
            if ($statement !== "" && $statement[0] !== "#") {
                //take line per line, until last char is ";"
                if (substr($statement, -1) === ";") {
                    //query is complete
                    /** @noinspection PhpUndefinedVariableInspection */
                    $q .= " " . substr($statement, 0, -1);
                    $check = $this->checkQuery($q);
                    if ($check === true) {
                        $db->query($q);
                    } else {
                        $this->error = (string) $check;
                        return false;
                    }
                    unset($q);
                } else {
                    /** @noinspection PhpUndefinedVariableInspection */
                    $q .= " " . $statement;
                }
            }
        }
        if (isset($q) && $q !== "") {
            echo "incomplete_statement: " . $q . "<br>";

            return false;
        }

        return true;
    }

    protected function checkQuery(string $q): bool
    {
        return true;
    }

    protected function initGlobalsRequiredForUpdateSteps(
        ?ilCtrlStructureReader &$ilCtrlStructureReader,
        ?ilDBInterface &$ilDB
    ): void {
        global $DIC;

        // TODO: There is currently a huge mixup of globals, $DIC and dependencies, esprecially in setup and during DB-Updates. This leads to many problems. The following core tries to provide the needed dependencies for the dbupdate-script. The code hopefully will change in the future.

        if (isset($GLOBALS['ilCtrlStructureReader'])) {
            $ilCtrlStructureReader = $GLOBALS['ilCtrlStructureReader'];
        } elseif ($DIC->offsetExists('ilCtrlStructureReader')) {
            $ilCtrlStructureReader = $DIC['ilCtrlStructureReader'];
        } else {
            $ilCtrlStructureReader = new ilCtrlStructureReader(
                $this->ctrl_structure_iterator,
                new ilCtrlStructureCidGenerator()
            );
            $DIC->offsetSet('ilCtrlStructureReader', $ilCtrlStructureReader);
        }

        $GLOBALS['ilCtrlStructureReader'] = $ilCtrlStructureReader;

        if ($this->client_ini) {
            $ilCtrlStructureReader->setIniFile($this->client_ini);
        }
        $ilDB = $DIC->database();
    }

    /**
     * Apply a custom database update or a plugin update
     * @param int $nr number what patch to apply (Reference: Patch for https://mantis.ilias.de/view.php?id=28550)
     * @access private
     */
    protected function applyUpdateNr(int $nr, bool $custom_update = false): bool
    {
        $ilCtrlStructureReader = null;
        $ilDB = null;
        $this->initGlobalsRequiredForUpdateSteps($ilCtrlStructureReader, $ilDB);

        reset($this->filecontent);

        //init
        $i = 0;

        //go through filecontent
        while (!preg_match("/^\<\#" . $nr . ">/", $this->filecontent[$i]) && $i < count($this->filecontent)) {
            $i++;
        }

        //update not found
        if ($i === count($this->filecontent)) {
            $this->error = 'update_not_found';

            return false;
        }

        $i++;

        //update found, now extract this update to a new array
        $update = [];
        while ($i < count($this->filecontent) && !preg_match("/^<#" . ($nr + 1) . ">/", $this->filecontent[$i])) {
            $update[] = trim($this->filecontent[$i]);
            $i++;
        }

        //now you have the update, now process it
        $sql = [];
        $php = [];
        $mode = 'sql';

        foreach ($update as $row) {
            if (preg_match("/<\?php/", $row)) {
                if (count($sql) > 0) {
                    if ($this->execQuery($this->db, implode("\n", $sql)) === false) {
                        return false;
                    }
                    $sql = [];
                }
                $mode = 'php';
            } elseif (preg_match("/\?>/", $row)) {
                if (count($php) > 0) {
                    $code = implode("\n", $php);
                    if (eval($code) === false) {
                        $this->error = 'Parse error: ' . $code;

                        return false;
                    }
                    $php = [];
                }
                $mode = 'sql';
            } else {
                if ($mode === 'sql') {
                    $sql[] = $row;
                }

                if ($mode === 'php') {
                    $php[] = $row;
                }
            }
        }

        if ($mode === 'sql' && count($sql) > 0 && $this->execQuery($this->db, implode("\n", $sql)) === false) {
            $this->error = "dump_error: " . $this->error;

            return false;
        }

        if ($custom_update) {
            $this->setCustomUpdatesCurrentVersion($nr);
        }

        return true;
    }

    public function getCustomUpdatesCurrentVersion(): ?int
    {
        $this->readCustomUpdatesInfo();

        return $this->custom_updates_current_version;
    }

    private function setCustomUpdatesCurrentVersion(?int $a_version): void
    {
        $this->readCustomUpdatesInfo();
        $this->custom_updates_setting->set('db_version_custom', (string) $a_version);
        $this->custom_updates_current_version = $a_version;
    }

    public function getCustomUpdatesFileVersion(): ?int
    {
        $this->readCustomUpdatesInfo();

        return $this->custom_updates_file_version;
    }

    private function readCustomUpdatesFileVersion(array $a_file_content): int
    {
        //go through file content and search for last occurrence of <#x>
        reset($a_file_content);
        $regs = [];
        $version = 0;
        foreach ($a_file_content as $row) {
            if (preg_match("/^<#([0-9]+)>/", $row, $regs)) {
                $version = $regs[1];
            }
        }

        return (int) $version;
    }

    private function readCustomUpdatesInfo(bool $a_force = false): void
    {
        if ($this->custom_updates_info_read && !$a_force) {
            return;
        }

        $this->custom_updates_setting = new ilSetting();
        $custom_updates_file = $this->PATH . 'setup/sql/dbupdate_custom.php';
        if (is_file($custom_updates_file)) {
            $this->custom_updates_content = @file($custom_updates_file);
            $this->custom_updates_current_version = (int) $this->custom_updates_setting->get('db_version_custom', '0');
            $this->custom_updates_file_version = $this->readCustomUpdatesFileVersion($this->custom_updates_content);
        }
        $this->custom_updates_info_read = true;
    }

    public function applyCustomUpdates(): bool
    {
        $ilCtrlStructureReader = null;
        $ilDB = null;
        $this->initGlobalsRequiredForUpdateSteps($ilCtrlStructureReader, $ilDB);
        $this->readCustomUpdatesInfo(true);

        $file_version = $this->getCustomUpdatesFileVersion();
        $current_version = $this->getCustomUpdatesCurrentVersion();
        $this->filecontent = $this->custom_updates_content;

        $this->updateMsg = 'no_changes';
        if ($current_version < $file_version) {
            $msg = [];
            for ($i = ($current_version + 1); $i <= $file_version; $i++) {
                if ($this->applyUpdateNr($i, true) === false) {
                    $msg[] = [
                        'msg' => 'update_error: ' . $this->error,
                        'nr' => $i,
                    ];
                    $this->updateMsg = implode("\n", $msg);

                    return false;
                }

                $msg[] = [
                    'msg' => 'custom_update_applied',
                    'nr' => $i,
                ];
            }

            $this->updateMsg = implode("\n", $msg);
        }

        return true;
    }
}
