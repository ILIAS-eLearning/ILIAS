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

use ILIAS\MetaData\Elements\SetInterface;
use ILIAS\MetaData\Paths\PathInterface;

interface ManipulatorInterface
{
    /**
     * Follows the path, adding scaffolds where necessary to perform the step.
     * At the last step, adds as many scaffolds as necessary to accomodate
     * all passed values. Note that added scaffolds will not necessarily fulfill
     * index or id filters of the path.
     */
    public function prepareCreateOrUpdate(
        SetInterface $set,
        PathInterface $path,
        string ...$values
    ): SetInterface;

    public function prepareForceCreate(
        SetInterface $set,
        PathInterface $path,
        string ...$values
    ): SetInterface;

    public function prepareDelete(
        SetInterface $set,
        PathInterface $path
    ): SetInterface;

    public function execute(SetInterface $set): void;
}
