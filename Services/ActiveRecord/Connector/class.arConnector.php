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

/**
 * Class arConnector
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @internal
 */
abstract class arConnector
{
    /**
     * @return mixed
     */
    abstract public function nextID(ActiveRecord $activeRecord);

    abstract public function checkConnection(ActiveRecord $activeRecord): bool;

    abstract public function installDatabase(ActiveRecord $activeRecord, array $fields): bool;

    abstract public function updateDatabase(ActiveRecord $activeRecord): bool;

    abstract public function resetDatabase(ActiveRecord $activeRecord): bool;

    abstract public function truncateDatabase(ActiveRecord $activeRecord): bool;

    abstract public function checkTableExists(ActiveRecord $activeRecord): bool;

    abstract public function checkFieldExists(ActiveRecord $activeRecord, string $field_name): bool;

    abstract public function removeField(ActiveRecord $activeRecord, string $field_name): bool;

    abstract public function renameField(ActiveRecord $activeRecord, string $old_name, string $new_name): bool;

    abstract public function create(ActiveRecord $activeRecord): void;

    /**
     * @return mixed[]
     */
    abstract public function read(ActiveRecord $activeRecord): array;

    abstract public function update(ActiveRecord $activeRecord): void;

    abstract public function delete(ActiveRecord $activeRecord): void;

    /**
     * @return mixed[]
     */
    abstract public function readSet(ActiveRecordList $activeRecordList): array;

    abstract public function affectedRows(ActiveRecordList $activeRecordList): int;

    abstract public function quote(mixed $value, string $type): string;

    abstract public function updateIndices(ActiveRecord $activeRecord): void;

    public function fixDate(string $value): string
    {
        return $value;
    }
}
