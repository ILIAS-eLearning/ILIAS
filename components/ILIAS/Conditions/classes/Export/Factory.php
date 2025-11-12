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

namespace ILIAS\Conditions\Export;

use ilDBInterface;

class Factory
{
    public function __construct(
        protected ilDBInterface $db
    ) {
    }

    public function repository(): Repository
    {
        return new Repository($this->db);
    }

    public function info(): Info
    {
        return new Info();
    }

    public function infoCollection(): InfoCollection
    {
        return new InfoCollection();
    }

    public function xmlReader(): XMLReader
    {
        return new XMLReader($this);
    }

    public function xmlWriter(): XMLWriter
    {
        return new XMLWriter();
    }
}
