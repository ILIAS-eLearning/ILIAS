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

class Derivator implements DerivatorInterface
{
    protected SetInterface $from_set;
    protected RepositoryInterface $repository;

    public function __construct(
        SetInterface $from_set,
        RepositoryInterface $repository
    ) {
        $this->from_set = $from_set;
        $this->repository = $repository;
    }

    /**
     * @throws \ilMDServicesException
     */
    public function forObject(int $obj_id, int $sub_id, string $type): void
    {
        if ($sub_id === 0) {
            $sub_id = $obj_id;
        }

        try {
            $this->repository->transferMD(
                $this->from_set,
                $obj_id,
                $sub_id,
                $type,
                true
            );
        } catch (\ilMDRepositoryException $e) {
            throw new \ilMDServicesException(
                'Failed to derive LOM set: ' . $e->getMessage()
            );
        }
    }
}
