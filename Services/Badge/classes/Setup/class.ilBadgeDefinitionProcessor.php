<?php declare(strict_types=1);

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

class ilBadgeDefinitionProcessor implements ilComponentDefinitionProcessor
{
    protected \ilDBInterface $db;
    protected ?string $component_id;
    protected bool $has_badges = false;

    public function __construct(\ilDBInterface $db)
    {
        $this->db = $db;
    }

    public function purge() : void
    {
        $bh = ilBadgeHandler::getInstance();
        $bh->setComponents(null);
    }

    public function beginComponent(string $component, string $type) : void
    {
        $this->has_badges = false;
        $this->component_id = null;
    }

    public function endComponent(string $component, string $type) : void
    {
        $this->has_badges = false;
        $this->component_id = null;
    }

    public function beginTag(string $name, array $attributes) : void
    {
        if ($name === "module" || $name === "service") {
            $this->component_id = $attributes["id"] ?? null;
            return;
        }

        if ($name !== 'badges') {
            return;
        }

        if ($this->component_id === null) {
            throw new \RuntimeException(
                "Found $name-tag outside of module or service."
            );
        }

        ilBadgeHandler::updateFromXML($this->component_id);
        $this->has_badges = true;
    }

    public function endTag(string $name) : void
    {
        if ($name === "module" || $name === "service") {
            $this->component_id = null;
        }
    }
}
