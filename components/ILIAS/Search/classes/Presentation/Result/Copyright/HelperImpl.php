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

namespace ILIAS\Search\Presentation\Result\Copyright;

use ILIAS\MetaData\Services\ServicesInterface as LOMServices;
use ILIAS\MetaData\Paths\PathInterface;

class HelperImpl implements Helper
{
    public function __construct(
        protected LOMServices $lom_services
    ) {
    }

    public function readPresentableCopyright(int $obj_id, int $sub_id, string $type): string
    {
        if (!$this->supportsLOM($obj_id, $sub_id, $type)) {
            return '';
        }

        $copyright_path = $this->lom_services->paths()->copyright();
        $reader = $this->lom_services->read($obj_id, $sub_id, $type, $copyright_path);
        if ($this->lom_services->copyrightHelper()->hasPresetCopyright($reader)) {
            return $this->lom_services->copyrightHelper()->readPresetCopyright($reader)->title();
        } else {
            return $this->lom_services->copyrightHelper()->readCustomCopyright($reader);
        }
    }

    /**
     * There really should be better infrastructure in place to check what
     * objects support LOM and which do not.
     * Until then, we check the existence of a required field.
     */
    protected function supportsLOM(int $obj_id, int $sub_id, string $type): bool
    {
        $title_path = $this->lom_services->paths()->title();
        $reader = $this->lom_services->read($obj_id, $sub_id, $type, $title_path);
        if ($reader->allData($title_path)->current() !== null) {
            return true;
        }
        return false;
    }
}
