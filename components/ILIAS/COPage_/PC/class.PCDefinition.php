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

declare(strict_types=1);

namespace ILIAS\COPage\PC;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class PCDefinition
{
    protected \ilDBInterface $db;
    protected array $pc_def = [];
    protected array $pc_def_by_name = [];
    protected array $pc_gui_classes = array();
    protected array $pc_gui_classes_lc = array();
    protected array $pc_def_by_gui_class_cl = array();

    public function __construct()
    {
        global $DIC;

        $this->db = $DIC->database();
    }

    public function getRecords(): array
    {
        $db = $this->db;
        $set = $db->query("SELECT * FROM copg_pc_def ORDER BY order_nr");
        return $db->fetchAll($set);
    }

    protected function init(): void
    {
        $db = $this->db;
        if ($this->pc_def == null) {
            foreach ($this->getRecords() as $rec) {
                $rec["pc_class"] = "ilPC" . $rec["name"];
                $rec["pc_gui_class"] = "ilPC" . $rec["name"] . "GUI";
                $this->pc_gui_classes[] = $rec["pc_gui_class"];
                $this->pc_gui_classes_lc[] = strtolower($rec["pc_gui_class"]);
                $this->pc_def[$rec["pc_type"]] = $rec;
                $this->pc_def_by_name[$rec["name"]] = $rec;
                $this->pc_def_by_gui_class_cl[strtolower($rec["pc_gui_class"])] = $rec;
            }
        }
    }

    public function getPCDefinitions(): array
    {
        $this->init();
        return $this->pc_def;
    }

    /**
     * Get PC definition by type
     */
    public function getPCDefinitionByType(string $a_pc_type): ?array
    {
        $this->init();
        return ($this->pc_def[$a_pc_type] ?? null);
    }

    /**
     * Get PC definition by name
     */
    public function getPCDefinitionByName(
        string $a_pc_name
    ): array {
        $this->init();
        return $this->pc_def_by_name[$a_pc_name];
    }

    /**
     * Get PC definition by name
     */
    public function getPCDefinitionByGUIClassName(
        string $a_gui_class_name
    ): array {
        $this->init();
        $a_gui_class_name = strtolower($a_gui_class_name);
        return $this->pc_def_by_gui_class_cl[$a_gui_class_name];
    }

    public function isPCGUIClassName(
        string $a_class_name,
        bool $a_lower_case = false
    ): bool {
        $this->init();
        if ($a_lower_case) {
            return in_array($a_class_name, $this->pc_gui_classes_lc);
        } else {
            return in_array($a_class_name, $this->pc_gui_classes);
        }
    }

    /**
     * Get instance
     */
    public function getPCEditorInstanceByName(
        string $a_name
    ): ?\ILIAS\COPage\Editor\Components\PageComponentEditor {
        $this->init();
        $pc_def = $this->getPCDefinitionByName($a_name);
        $pc_class = "ilPC" . $pc_def["name"] . "EditorGUI";
        if (class_exists($pc_class)) {
            return new $pc_class();
        }
        return null;
    }
}
