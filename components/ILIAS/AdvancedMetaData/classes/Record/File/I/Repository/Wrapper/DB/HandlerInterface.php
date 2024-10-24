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

namespace ILIAS\AdvancedMetaData\Record\File\I\Repository\Wrapper\DB;

use ILIAS\AdvancedMetaData\Record\File\I\Repository\Key\HandlerInterface as FileRepositoryKeyInterface;
use ILIAS\AdvancedMetaData\Record\File\I\Repository\Element\CollectionInterface as FileRepositoryElementCollectionInterface;

interface HandlerInterface
{
    public const TABLE_NAME = "adv_md_record_files";

    public function insert(
        FileRepositoryKeyInterface $key
    ): void;

    public function delete(
        FileRepositoryKeyInterface $key
    ): void;

    public function select(
        FileRepositoryKeyInterface $key
    ): FileRepositoryElementCollectionInterface;

    public function buildSelectQuery(
        FileRepositoryKeyInterface $key
    ): string;

    public function buildDeleteQuery(
        FileRepositoryKeyInterface $key
    ): string;

    public function buildInsertQuery(
        FileRepositoryKeyInterface $key
    ): string;
}
