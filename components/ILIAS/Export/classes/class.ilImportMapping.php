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
 * Import mapping
 * @author Alexander Killing <killing@leifos.de>
 */
class ilImportMapping
{
    public array $mappings;
    public string $install_id;
    public string $install_url;
    public ilLogger $log;

    protected int $target_id = 0;

    public function __construct()
    {
        $this->mappings = array();
        $this->log = ilLoggerFactory::getLogger("exp");
        $this->log->debug("ilImportMapping Construct this->mappings = array()");
    }

    final public function setInstallId(string $a_val): void
    {
        $this->install_id = $a_val;
    }

    final public function getInstallId(): string
    {
        return $this->install_id;
    }

    final public function setInstallUrl(string $a_val): void
    {
        $this->install_url = $a_val;
    }

    final public function getInstallUrl(): string
    {
        return $this->install_url;
    }

    final public function setTargetId(int $a_target_id): void
    {
        $this->target_id = $a_target_id;
        $this->log->debug("a_target_id=" . $a_target_id);
    }

    final public function getTargetId(): int
    {
        return $this->target_id;
    }

    public function addMapping(
        string $a_comp,
        string $a_entity,
        string $a_old_id,
        string $a_new_id
    ): void {
        $this->mappings[$a_comp][$a_entity][$a_old_id] = $a_new_id;
        //$this->log->debug("ADD MAPPING this->mappings = ", $this->mappings);
    }

    public function getMapping(
        string $a_comp,
        string $a_entity,
        string $a_old_id
    ): ?string {
        $this->log->debug("a_comp = $a_comp, a_entity = $a_entity , a_old_id = $a_old_id");

        if (!isset($this->mappings[$a_comp]) or !isset($this->mappings[$a_comp][$a_entity])) {
            return null;
        }
        if (isset($this->mappings[$a_comp][$a_entity][$a_old_id])) {
            return $this->mappings[$a_comp][$a_entity][$a_old_id];
        }

        return null;
    }

    public function getAllMappings(): array
    {
        return $this->mappings;
    }

    public function getMappingsOfEntity(
        string $a_comp,
        string $a_entity
    ): array {
        if (isset($this->mappings[$a_comp][$a_entity])) {
            return $this->mappings[$a_comp][$a_entity];
        }
        return array();
    }
}
