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

use ILIAS\MetaData\Paths\PathInterface;
use ILIAS\MetaData\Manipulator\ManipulatorInterface as InternalManipulator;
use ILIAS\MetaData\Elements\SetInterface;
use ILIAS\MetaData\Repository\RepositoryInterface;

class Manipulator implements ManipulatorInterface
{
    protected InternalManipulator $internal_manipulator;
    protected RepositoryInterface $repository;
    protected SetInterface $set;

    public function __construct(
        InternalManipulator $internal_manipulator,
        RepositoryInterface $repository,
        SetInterface $set
    ) {
        $this->internal_manipulator = $internal_manipulator;
        $this->repository = $repository;
        $this->set = $set;
    }

    public function prepareCreateOrUpdate(
        PathInterface $path,
        string ...$values
    ): ManipulatorInterface {
        try {
            $set = $this->internal_manipulator->prepareCreateOrUpdate(
                $this->set,
                $path,
                ...$values
            );
        } catch (\ilMDPathException $e) {
            throw new \ilMDServicesException(
                'Failed to prepare create or update values ' . implode(', ', $values) .
                ' at "' . $path->toString() . '": ' . $e->getMessage()
            );
        }

        return $this->getCloneWithNewSet($set);
    }

    public function prepareForceCreate(
        PathInterface $path,
        string ...$values
    ): ManipulatorInterface {
        try {
            $set = $this->internal_manipulator->prepareForceCreate(
                $this->set,
                $path,
                ...$values
            );
        } catch (\ilMDPathException $e) {
            throw new \ilMDServicesException(
                'Failed to force-create values ' . implode(', ', $values) .
                ' at "' . $path->toString() . '": ' . $e->getMessage()
            );
        }

        return $this->getCloneWithNewSet($set);
    }

    public function prepareDelete(PathInterface $path): ManipulatorInterface
    {
        $set = $this->internal_manipulator->prepareDelete(
            $this->set,
            $path
        );
        return $this->getCloneWithNewSet($set);
    }

    public function execute(): void
    {
        try {
            $this->repository->manipulateMD($this->set);
        } catch (\ilMDRepositoryException $e) {
            throw new \ilMDServicesException(
                'Failed to execute manipulations: ' . $e->getMessage()
            );
        }

    }

    protected function getCloneWithNewSet(SetInterface $set): ManipulatorInterface
    {
        $clone = clone $this;
        $clone->set = $set;
        return $clone;
    }
}
