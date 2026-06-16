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

namespace ILIAS\User\Profile\Fields;

interface ConfigurationRepository
{
    public function hasMigrationBeenRun(): bool;

    /**
     * @return list<Field>
     */
    public function get(): array;
    public function getByIdentifier(string $identifier): ?Field;
    public function getByClass(string $class): ?Field;
    public function storeConfiguration(Field $field): void;

    /**
     * @return list<Custom\Type>
     */
    public function getCustomFieldTypes(): array;
    public function getUnspecifiedCustomField(): Field;
    public function deleteCustomField(Field $field): void;
}
