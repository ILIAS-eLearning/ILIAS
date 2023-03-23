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

namespace ILIAS\UI\Implementation\Component\Input;

/**
 * FormInputNameSource is responsible for generating continuous
 * form input names.
 *
 * @author Thibeau Fuhrer <thf@studer-raimann.ch>
 */
class FormInputNameSource implements NameSource
{
    private int $count = 0;
    private array $used_names = [];

    /**
     * @inheritDoc
     */
    public function getNewName(): string
    {
        return 'input_' . $this->count++;
    }

    public function getNewDedicatedName(string $dedicated_name): string
    {
        if ($dedicated_name == 'input') {
            return $this->getNewName();
        }
        if (in_array($dedicated_name, $this->used_names)) {
            return $dedicated_name . '_' . $this->count++;
        } else {
            $this->used_names[] = $dedicated_name;
            return $dedicated_name;
        }
    }
}
