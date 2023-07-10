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

/**
 * @author Klees
 */
class ilEventHandlingDefinitionProcessor implements ilComponentDefinitionProcessor
{
    protected array $data = [];
    protected ?string $component;

    public function getData(): array
    {
        return $this->data;
    }

    public function purge(): void
    {
        foreach ($this->data as $data_entry_key => $data_entry_values) {
            $pattern = "^(plugins/).*";
            $subject = $data_entry_values["component"];
            preg_match($pattern, $subject, $component_is_plugin);

            if (!$component_is_plugin) {
                unset($this->data[$data_entry_key]);
            }
        }
    }

    public function beginComponent(string $component, string $type): void
    {
        $this->component = $type . "/" . $component;
    }

    public function endComponent(string $component, string $type): void
    {
        $this->component = null;
    }

    public function beginTag(string $name, array $attributes): void
    {
        if ($name !== "event") {
            return;
        }

        $component = $attributes["component"] ?? null;
        if (!$component) {
            $component = $this->component;
        }

        $event = [
            "component"             => $component,
            "type"                  => $attributes["type"],
            "type_specification"    => $attributes["id"]
        ];

        //only add event to data if no such entry exists
        if (!$this->hasDataEntryForEvent($event)) {
            $this->data[] = $event;
        }
    }

    public function endTag(string $name): void
    {
    }

    public function hasDataEntryForEvent($event): bool
    {
        return in_array($event, $this->data, true);
    }
}
