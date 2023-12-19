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

namespace ILIAS\AdvancedMetaData\Services\SubObjectModes\DataTable;

use ILIAS\AdvancedMetaData\Services\SubObjectIDInterface;

class Data implements DataInterface
{
    protected array $data = [];

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function dataForSubObject(SubObjectIDInterface $sub_object_id): array
    {
        return $this->data[$sub_object_id->subtype()][$sub_object_id->objID()][$sub_object_id->subID()] ?? [];
    }
}
