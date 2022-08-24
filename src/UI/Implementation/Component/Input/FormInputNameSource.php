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

    /**
     * @inheritDoc
     */
    public function getNewName(): string
    {
        return 'form_input_' . $this->count++;
    }
}
