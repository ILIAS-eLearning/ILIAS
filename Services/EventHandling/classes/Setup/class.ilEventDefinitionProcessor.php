<?php declare(strict_types=1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 */

/**
 * @author Klees
 */
class ilEventDefinitionProcessor implements ilComponentDefinitionProcessor
{
    protected ilDBInterface $db;
    protected ?string $component;

    public function __construct(ilDBInterface $db)
    {
        $this->db = $db;
    }

    public function purge() : void
    {
        $this->db->manipulate("DELETE FROM il_event_handling WHERE component NOT LIKE 'Plugins/%'");
    }

    public function beginComponent(string $component, string $type) : void
    {
        $this->component = $type . "/" . $component;
    }

    public function endComponent(string $component, string $type) : void
    {
        $this->component = null;
    }

    public function beginTag(string $name, array $attributes) : void
    {
        if ($name !== "event") {
            return;
        }

        $component = $attributes["component"] ?? null;
        if (!$component) {
            $component = $this->component;
        }
        $q = "INSERT INTO il_event_handling (component, type, id) VALUES (" .
            $this->db->quote($component, "text") . "," .
            $this->db->quote($attributes["type"], "text") . "," .
            $this->db->quote($attributes["id"], "text") . ")";
        $this->db->manipulate($q);
    }

    public function endTag(string $name) : void
    {
    }
}
