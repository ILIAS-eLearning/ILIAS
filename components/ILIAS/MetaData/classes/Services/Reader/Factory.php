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

namespace ILIAS\MetaData\Services\Reader;

use ILIAS\MetaData\Paths\Navigator\NavigatorFactoryInterface;
use ILIAS\MetaData\Elements\SetInterface;

class Factory implements FactoryInterface
{
    protected NavigatorFactoryInterface $navigator_factory;

    public function __construct(NavigatorFactoryInterface $navigator_factory)
    {
        $this->navigator_factory = $navigator_factory;
    }

    public function get(SetInterface $set): ReaderInterface
    {
        return new Reader(
            $this->navigator_factory,
            $set
        );
    }
}
