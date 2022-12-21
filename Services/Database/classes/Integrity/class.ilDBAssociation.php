<?php

declare(strict_types=1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *********************************************************************/

namespace ILIAS\Services\Database\Integrity;

class ilDBAssociation
{
    private ilDBField $field;
    private ilDBField $reference_field;

    public function __construct(ilDBField $field, ilDBField $reference_field)
    {
        $this->field = $field;
        $this->reference_field = $reference_field;
    }

    public function field(): ilDBField
    {
        return $this->field;
    }

    public function referenceField(): ilDBField
    {
        return $this->reference_field;
    }
}
