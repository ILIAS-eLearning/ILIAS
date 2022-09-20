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
 * Settings template config class
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilSettingsTemplateConfig
{
    public const TEXT = "text";
    public const SELECT = "select";
    public const BOOL = "bool";
    public const CHECKBOX = "check";

    private string $type;
    private array $tabs = array();
    private array $setting = array();

    public function __construct(string $a_obj_type)
    {
        $this->setType($a_obj_type);
    }

    public function setType(string $a_val): void
    {
        $this->type = $a_val;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function addHidableTab(
        string $a_tab_id,
        string $a_text
    ): void {
        $this->tabs[$a_tab_id] = array(
            "id" => $a_tab_id,
            "text" => $a_text
        );
    }

    public function getHidableTabs(): array
    {
        return $this->tabs;
    }

    public function addSetting(
        string $a_id,
        string $a_type,
        string $a_text,
        bool $a_hidable,
        int $a_length = 0,
        array $a_options = array()
    ): void {
        $this->setting[$a_id] = array(
            "id" => $a_id,
            "type" => $a_type,
            "text" => $a_text,
            "hidable" => $a_hidable,
            "length" => $a_length,
            "options" => $a_options
        );
    }

    public function getSettings(): array
    {
        return $this->setting;
    }
}
