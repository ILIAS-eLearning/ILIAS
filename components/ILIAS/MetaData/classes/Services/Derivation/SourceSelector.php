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

namespace ILIAS\MetaData\Services\Derivation;

use ILIAS\MetaData\Repository\RepositoryInterface;
use ILIAS\MetaData\Elements\SetInterface;
use ILIAS\MetaData\Services\Derivation\Creation\CreatorInterface;

class SourceSelector implements SourceSelectorInterface
{
    protected RepositoryInterface $repository;
    protected CreatorInterface $creator;

    public function __construct(
        RepositoryInterface $repository,
        CreatorInterface $creator
    ) {
        $this->repository = $repository;
        $this->creator = $creator;
    }

    public function fromObject(int $obj_id, int $sub_id, string $type): DerivatorInterface
    {
        if ($sub_id === 0) {
            $sub_id = $obj_id;
        }

        return $this->getDerivator(
            $this->repository->getMD($obj_id, $sub_id, $type)
        );
    }

    /**
     * @throws \ilMDServicesException if title is empty string
     */
    public function fromBasicProperties(
        string $title,
        string $description = '',
        string $language = ''
    ): DerivatorInterface {
        return $this->getDerivator(
            $this->creator->createSet($title, $description, $language)
        );
    }

    protected function getDerivator(SetInterface $from_set): DerivatorInterface
    {
        return new Derivator(
            $from_set,
            $this->repository
        );
    }
}
