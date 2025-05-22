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

namespace ILIAS\AdvancedMetaData\Record\File\Repository;

use ILIAS\AdvancedMetaData\Record\File\I\Repository\Element\HandlerInterface as FileRepositoryElementInterface;
use ILIAS\AdvancedMetaData\Record\File\I\Repository\HandlerInterface as FileRepositoryInterface;
use ILIAS\AdvancedMetaData\Record\File\I\Repository\Key\HandlerInterface as FileRepositoryKeyInterface;
use ILIAS\AdvancedMetaData\Record\File\I\Repository\Element\CollectionInterface as FileRepositoryElementCollectionInterface;
use ILIAS\AdvancedMetaData\Record\File\I\Repository\Wrapper\DB\HandlerInterface as FileRepositoryDBWrapperInterface;

class Handler implements FileRepositoryInterface
{
    protected FileRepositoryDBWrapperInterface $db_wrapper;

    public function __construct(
        FileRepositoryDBWrapperInterface $db_wrapper
    ) {
        $this->db_wrapper = $db_wrapper;
    }

    public function store(
        FileRepositoryKeyInterface $key
    ): void {
        if (!$key->isCompositKeyOfAll()) {
            return;
        }
        $this->db_wrapper->insert($key);
    }

    public function getElements(
        FileRepositoryKeyInterface $key
    ): FileRepositoryElementCollectionInterface|null {
        if (!$key->isValid()) {
            return null;
        }
        return $this->db_wrapper->select($key);
    }

    public function delete(
        FileRepositoryKeyInterface $key
    ): void {
        if (!$key->isValid()) {
            return;
        }
        $this->db_wrapper->delete($key);
    }
}
