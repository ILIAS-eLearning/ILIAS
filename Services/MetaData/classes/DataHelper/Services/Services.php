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

namespace ILIAS\MetaData\DataHelper\Services;

use ILIAS\MetaData\DataHelper\DataHelperInterface;
use ILIAS\MetaData\DataHelper\DataHelper;

class Services
{
    protected DataHelperInterface $data_helper;

    public function dataHelper(): DataHelperInterface
    {
        if (isset($this->data_helper)) {
            return $this->data_helper;
        }
        return $this->data_helper = new DataHelper();
    }
}
