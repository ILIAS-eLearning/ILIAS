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

namespace ILIAS\Tracking\View\DataRetrieval\Info\Iterator;

use ILIAS\Tracking\View\DataRetrieval\Info\CombinedInterface as CombinedInfoInterface;
use ILIAS\Tracking\View\DataRetrieval\Info\Iterator\CombinedInterface as CombinedIteratorInterface;
use ILIAS\Tracking\View\DataRetrieval\Info\Iterator\Combined as CombinedIterator;
use ILIAS\Tracking\View\DataRetrieval\Info\Iterator\FactoryInterface as IteratorFactoryInterface;
use ILIAS\Tracking\View\DataRetrieval\Info\Iterator\LPInterface as LPIteratorInterface;
use ILIAS\Tracking\View\DataRetrieval\Info\Iterator\LP as LPIterator;
use ILIAS\Tracking\View\DataRetrieval\Info\Iterator\ObjectDataInterface as ObjectDataIteratorInterface;
use ILIAS\Tracking\View\DataRetrieval\Info\Iterator\ObjectData as ObjectDataIterator;
use ILIAS\Tracking\View\DataRetrieval\Info\LPInterface as LPInfoInterface;
use ILIAS\Tracking\View\DataRetrieval\Info\ObjectDataInterface as ObjectDataInfoInterface;

class Factory implements IteratorFactoryInterface
{
    public function combined(
        CombinedInfoInterface ...$infos
    ): CombinedIteratorInterface {
        return new CombinedIterator(
            ...$infos
        );
    }

    public function lp(
        LPInfoInterface ...$infos
    ): LPIteratorInterface {
        return new LPIterator(
            ...$infos
        );
    }

    public function objectData(
        ObjectDataInfoInterface ...$infos
    ): ObjectDataIteratorInterface {
        return new ObjectDataIterator(
            ...$infos
        );
    }
}
