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

/**
 * Interface ilBiblFieldFilterInterface
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface ilBiblFieldFilterInterface
{
    public const FILTER_TYPE_MULTI_SELECT_INPUT = 3;
    public const FILTER_TYPE_SELECT_INPUT = 2;
    public const FILTER_TYPE_TEXT_INPUT = 1;

    public function getId(): ?int;

    public function setId(int $id): void;

    public function getFieldId(): int;

    public function setFieldId(int $field_id): void;

    public function getObjectId(): int;

    public function setObjectId(int $object_id): void;

    public function getFilterType(): int;

    public function setFilterType(int $filter_type): void;

    public function create();

    public function update();

    public function delete();
}
