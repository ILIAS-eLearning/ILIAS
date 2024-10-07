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

namespace ILIAS\MetaData\Services\Manipulator;

use ILIAS\MetaData\Manipulator\ManipulatorInterface as InternalManipulator;
use ILIAS\MetaData\Elements\SetInterface;
use ILIAS\MetaData\Repository\RepositoryInterface;

class Factory implements FactoryInterface
{
    protected InternalManipulator $internal_manipulator;
    protected RepositoryInterface $repository;

    /**
     * Note that the general Manipulator used in MetaData does not have an
     * mutable internal state (prepared changes are tracked in the set), but
     * the Manipulator exposed through the API does.
     */
    public function __construct(
        InternalManipulator $internal_manipulator,
        RepositoryInterface $repository,
    ) {
        $this->internal_manipulator = $internal_manipulator;
        $this->repository = $repository;
    }

    public function get(
        SetInterface $set
    ): ManipulatorInterface {
        return new Manipulator(
            $this->internal_manipulator,
            $this->repository,
            $set
        );
    }
}
