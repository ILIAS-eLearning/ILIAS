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

namespace ILIAS\AdvancedMetaData\Services\SubObjectModes;

use ILIAS\DI\Container;
use ILIAS\AdvancedMetaData\Services\SubObjectIDInterface;
use ILIAS\Data\Factory as DataFactory;

class SubObjectModes implements SubObjectModesInterface
{
    protected Container $dic;

    protected string $type;
    protected int $ref_id;

    /**
     * @var SubObjectIDInterface[]
     */
    protected array $sub_object_ids;

    public function __construct(
        Container $dic,
        string $type,
        int $ref_id,
        SubObjectIDInterface ...$sub_object_ids
    ) {
        $this->dic = $dic;
        $this->type = $type;
        $this->ref_id = $ref_id;
        $this->sub_object_ids = $sub_object_ids;
    }
    public function inDataTable(): DataTableInterface
    {
        return new DataTable(
            $this->dic->user(),
            $this->dic->ui()->factory(),
            new DataFactory(),
            $this->dic['static_url'],
            $this->type,
            $this->ref_id,
            ...$this->sub_object_ids
        );
    }
}
