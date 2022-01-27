<?php declare(strict_types=1);

/* Copyright (c) 2021 Thibeau Fuhrer <thf@studer-raimann.ch> Extended GPL, see docs/LICENSE */

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
    public function getNewName() : string
    {
        return 'form_input_' . $this->count++;
    }
}