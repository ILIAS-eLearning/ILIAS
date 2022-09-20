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

namespace ILIAS\ResourceStorage;

use ILIAS\ResourceStorage\Identification\CollectionIdentificationGenerator;
use ILIAS\ResourceStorage\Identification\IdentificationGenerator;
use ILIAS\ResourceStorage\Identification\ResourceIdentification;
use ILIAS\ResourceStorage\Identification\ResourceCollectionIdentification;

class DummyIDGenerator implements IdentificationGenerator, CollectionIdentificationGenerator
{
    private string $id = 'dummy';

    /**
     * DummyIDGenerator constructor.
     */
    public function __construct(string $id = 'dummy')
    {
        $this->id = $id;
    }

    public function getUniqueResourceIdentification(): ResourceIdentification
    {
        return new ResourceIdentification($this->id);
    }

    public function getUniqueResourceCollectionIdentification(): ResourceCollectionIdentification
    {
        return new ResourceCollectionIdentification($this->id);
    }

    public function validateScheme(string $existing): bool
    {
        return true;
    }
}
