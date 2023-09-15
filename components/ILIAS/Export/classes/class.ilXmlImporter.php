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
 * Xml importer class
 * @author Alexander Killing <killing@leifos.de>
 */
abstract class ilXmlImporter
{
    protected array $skip_entities = array();
    protected ilImport $imp;
    protected string $install_id;
    protected string $install_url;
    protected string $schema_version;
    protected string $import_directory;

    public function __construct()
    {
    }

    public function setImport(ilImport $a_val): void
    {
        $this->imp = $a_val;
    }

    public function getImport(): ilImport
    {
        return $this->imp;
    }

    public function init(): void
    {
    }

    public function setInstallId(string $a_val): void
    {
        $this->install_id = $a_val;
    }

    public function getInstallId(): string
    {
        return $this->install_id;
    }

    public function setInstallUrl(string $a_val): void
    {
        $this->install_url = $a_val;
    }

    public function getInstallUrl(): string
    {
        return $this->install_url;
    }

    public function setSchemaVersion(string $a_val): void
    {
        $this->schema_version = $a_val;
    }

    public function getSchemaVersion(): string
    {
        return $this->schema_version;
    }

    public function setImportDirectory(string $a_val): void
    {
        $this->import_directory = $a_val;
    }

    public function getImportDirectory(): string
    {
        return $this->import_directory;
    }

    public function setSkipEntities(array $a_val): void
    {
        $this->skip_entities = $a_val;
    }

    public function getSkipEntities(): array
    {
        return $this->skip_entities;
    }

    // Is exporting and importing installation identical?
    public function exportedFromSameInstallation(): bool
    {
        if ($this->getInstallId() > 0 && ($this->getInstallId() == IL_INST_ID)) {
            return true;
        }
        return false;
    }

    abstract public function importXmlRepresentation(
        string $a_entity,
        string $a_id,
        string $a_xml,
        ilImportMapping $a_mapping
    ): void;

    public function finalProcessing(ilImportMapping $a_mapping): void
    {
    }

    // Called after all container objects have been imported.
    public function afterContainerImportProcessing(ilImportMapping $mapping): void
    {
    }
}
