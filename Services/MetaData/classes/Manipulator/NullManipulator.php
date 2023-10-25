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

namespace ILIAS\MetaData\Manipulator;

use ILIAS\MetaData\Manipulator\Path\PathConditionsCollectionInterface;
use ILIAS\MetaData\Manipulator\Path\PathUtilitiesFactoryInterface;
use ILIAS\MetaData\Paths\Filters\FilterType;
use ILIAS\MetaData\Paths\Steps\StepInterface;
use ILIAS\MetaData\Paths\Steps\StepToken;
use ILIAS\MetaData\Repository\RepositoryInterface;
use ILIAS\MetaData\Elements\Markers\MarkerFactoryInterface;
use ILIAS\MetaData\Paths\Navigator\NavigatorFactoryInterface;
use ILIAS\MetaData\Elements\SetInterface;
use ILIAS\MetaData\Paths\PathInterface;
use ILIAS\MetaData\Elements\ElementInterface;
use ILIAS\MetaData\Elements\Markers\MarkableInterface;
use ILIAS\MetaData\Elements\Markers\Action;
use ilMDPathException;
use ILIAS\MetaData\Elements\NullSet;

class NullManipulator implements ManipulatorInterface
{
    public function prepareDelete(
        SetInterface $set,
        PathInterface $path
    ): SetInterface {
        return new NullSet();
    }

    public function execute(SetInterface $set): void
    {
    }

    public function prepareCreateOrUpdate(
        SetInterface $set,
        PathInterface $path,
        string ...$values
    ): SetInterface {
        return new NullSet();
    }

    public function prepareForceCreate(
        SetInterface $set,
        PathInterface $path,
        string ...$values
    ): SetInterface {
        return new NullSet();
    }
}
