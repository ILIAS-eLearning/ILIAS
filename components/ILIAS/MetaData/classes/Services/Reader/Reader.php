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

use ILIAS\MetaData\Paths\PathInterface;
use ILIAS\MetaData\Elements\Data\DataInterface;
use ILIAS\MetaData\Elements\SetInterface;
use ILIAS\MetaData\Paths\Navigator\NavigatorFactoryInterface;
use ILIAS\MetaData\Elements\Data\NullData;

class Reader implements ReaderInterface
{
    protected NavigatorFactoryInterface $navigator_factory;
    protected SetInterface $set;

    public function __construct(
        NavigatorFactoryInterface $navigator_factory,
        SetInterface $set
    ) {
        $this->navigator_factory = $navigator_factory;
        $this->set = $set;
    }

    /**
     * @return DataInterface[]
     */
    public function allData(PathInterface $path): \Generator
    {
        $navigator = $this->navigator_factory->navigator($path, $this->set->getRoot());
        foreach ($navigator->elementsAtFinalStep() as $element) {
            yield $element->getData();
        }
    }

    public function firstData(PathInterface $path): DataInterface
    {
        $navigator = $this->navigator_factory->navigator($path, $this->set->getRoot());
        if ($first = $navigator->elementsAtFinalStep()->current()) {
            return $first->getData();
        }
        return new NullData();
    }
}
