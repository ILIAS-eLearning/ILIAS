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

namespace ILIAS\MetaData\OERHarvester\ControlCenter\Http;

interface RequestParserInterface
{
    public const string REF_ID_PARAM = 'publish_ref_id';
    public const string OBJ_ID_PARAM = 'publish_obj_id';
    public const string TYPE_PARAM = 'publish_type';

    public function fetchRefID(): int;

    public function fetchObjID(): int;

    public function fetchType(): string;
}
