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

namespace ILIAS\AdvancedMetaData\Services\ObjectModes;

use ILIAS\AdvancedMetaData\Services\ObjectModes\Custom\CustomInterface;
use ILIAS\AdvancedMetaData\Services\ObjectModes\Custom\Custom;
use ILIAS\DI\Container;

class ObjectModes implements ObjectModesInterface
{
    protected Container $dic;

    protected string $type;
    protected int $ref_id;
    protected string $sub_type;
    protected int $sub_id;

    public function __construct(
        Container $dic,
        string $type,
        int $ref_id,
        string $sub_type = '',
        int $sub_id = 0
    ) {
        $this->dic = $dic;
        $this->type = $type;
        $this->ref_id = $ref_id;
        $this->sub_type = $sub_type;
        $this->sub_id = $sub_id;
    }

    public function custom(): CustomInterface
    {
        return new Custom(
            $this->dic->user(),
            $this->type,
            $this->ref_id,
            $this->sub_type,
            $this->sub_id
        );
    }
}
