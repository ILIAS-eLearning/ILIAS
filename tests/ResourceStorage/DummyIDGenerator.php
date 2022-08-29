<?php

namespace ILIAS\ResourceStorage;

use ILIAS\ResourceStorage\Identification\IdentificationGenerator;
use ILIAS\ResourceStorage\Identification\ResourceIdentification;

/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/
class DummyIDGenerator implements IdentificationGenerator
{
    private string $id = 'dummy';

    /**
     * DummyIDGenerator constructor.
     */
    public function __construct(string $id = 'dummy')
    {
        $this->id = $id;
    }

    /**
     * @inheritDoc
     */
    public function getUniqueResourceIdentification(): ResourceIdentification
    {
        return new ResourceIdentification($this->id);
    }
}
