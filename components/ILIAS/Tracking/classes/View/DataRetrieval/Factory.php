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

declare(strict_types=0);

namespace ILIAS\Tracking\View\DataRetrieval;

use ilDBInterface;
use ILIAS\Tracking\View\DataRetrieval\DataRetrievalInterface as DRInterface;
use ILIAS\Tracking\View\DataRetrieval\DataRetrieval;
use ILIAS\Tracking\View\DataRetrieval\FactoryInterface as DRFactoryInterface;
use ILIAS\Tracking\View\DataRetrieval\FilterInterface as DRFilterInterface;
use ILIAS\Tracking\View\DataRetrieval\Filter;
use ILIAS\Tracking\View\DataRetrieval\Info\FactoryInterface as InfoFactoryInterface;
use ILIAS\Tracking\View\DataRetrieval\Info\Factory as InfoFactory;

class Factory implements DRFactoryInterface
{
    public function __construct(
        protected ilDBInterface $db
    ) {
    }

    public function info(): InfoFactoryInterface
    {
        return new InfoFactory();
    }

    public function service(): DRInterface
    {
        return new DataRetrieval(
            $this->db,
            $this->info()
        );
    }

    public function filter(): DRFilterInterface
    {
        return new Filter();
    }
}
