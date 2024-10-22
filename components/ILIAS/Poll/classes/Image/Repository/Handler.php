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

namespace ILIAS\Poll\Image\Repository;

use ILIAS\Poll\Image\I\Repository\Element\HandlerInterface as ilPollImageRepositoryElementInterface;
use ILIAS\Poll\Image\I\Repository\HandlerInterface as ilPollImageRepositoryInterface;
use ILIAS\Poll\Image\I\Repository\Key\HandlerInterface as ilPollImageRepositoryKeyInterface;
use ILIAS\Poll\Image\I\Repository\Values\HandlerInterface as ilPollImageRepositoryValuesInterface;
use ILIAS\Poll\Image\I\Repository\Wrapper\DB\HandlerInterface as ilPollImageRepositoryDBWrapperInterface;

class Handler implements ilPollImageRepositoryInterface
{
    protected ilPollImageRepositoryDBWrapperInterface $db_wrapper;

    public function __construct(
        ilPollImageRepositoryDBWrapperInterface $db_wrapper
    ) {
        $this->db_wrapper = $db_wrapper;
    }

    public function store(
        ilPollImageRepositoryKeyInterface $key,
        ilPollImageRepositoryValuesInterface $values
    ) {
        $this->db_wrapper->insert($key, $values);
    }

    public function getElement(
        ilPollImageRepositoryKeyInterface $key
    ): null|ilPollImageRepositoryElementInterface {
        return $this->db_wrapper->select($key);
    }

    public function deleteElement(
        ilPollImageRepositoryKeyInterface $key
    ): void {
        $this->db_wrapper->delete($key);
    }
}
