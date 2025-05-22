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

namespace ILIAS\Tracking\View\DataRetrieval\Info;

use ILIAS\Tracking\View\DataRetrieval\Info\Iterator\CombinedInterface as CombinedIteratorInterface;
use ILIAS\Tracking\View\DataRetrieval\Info\Iterator\LPInterface as LPIteratorInterface;
use ILIAS\Tracking\View\DataRetrieval\Info\Iterator\ObjectDataInterface as ObjectDataIteratorInterface;
use ILIAS\Tracking\View\DataRetrieval\Info\ViewInterface;

class View implements ViewInterface
{
    public function __construct(
        protected ObjectDataIteratorInterface $object_data_iterator,
        protected LPIteratorInterface $lp_iterator,
        protected CombinedIteratorInterface $combined_iterator
    ) {
    }

    public function objectIterator(): ObjectDataIteratorInterface
    {
        return $this->object_data_iterator;
    }

    public function lpInfoIterator(): LPIteratorInterface
    {
        return $this->lp_iterator;
    }

    public function combinedInfoIterator(): CombinedIteratorInterface
    {
        return $this->combined_iterator;
    }
}
