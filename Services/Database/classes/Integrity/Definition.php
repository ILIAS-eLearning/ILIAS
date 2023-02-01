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

namespace ILIAS\Services\Database\Integrity;

use InvalidArgumentException;

class Definition
{
    private Ignore $ignore;

    /**
     * @param \ILIAS\Services\Database\Integrity\Association[] $associations
     */
    public function __construct(private array $associations, ?Ignore $ignore = null)
    {
        $this->ignore = $ignore ?? new Ignore();
        $this->validate();
    }

    private function validate(): void
    {
        if ($this->associations === []) {
            throw new InvalidArgumentException('Associations must not be empty.');
        }

        array_walk($this->associations, static function ($association): void {
            if (!$association instanceof Association) {
                throw new InvalidArgumentException(
                    'Associations must be of type ' . Association::class . '.'
                );
            }
        });

        $first = $this->associations[0];

        foreach ($this->associations as $association) {
            if ($association->field()->tableName() !== $first->field()->tableName() ||
                $association->referenceField()->tableName() !== $first->referenceField()->tableName()
            ) {
                throw new InvalidArgumentException('All fields must have the same table');
            }
        }
    }

    public function tableName(): string
    {
        return $this->associations[0]->field()->tableName();
    }

    /**
     * @return Association[]
     */
    public function associations(): array
    {
        return $this->associations;
    }

    /**
     * @return string[]
     */
    public function ignoreValues(): array
    {
        return $this->ignore->values();
    }

    public function referenceTableName(): string
    {
        return $this->associations[0]->referenceField()->tableName();
    }
}
