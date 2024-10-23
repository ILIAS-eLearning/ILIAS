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

namespace ILIAS\Export\ExportHandler\I\Target;

interface HandlerInterface
{
    public function withType(string $type): HandlerInterface;

    /**
     * @param int[] $object_ids
     */
    public function withObjectIds(array $object_ids): HandlerInterface;

    public function withTargetRelease(string $target_release): HandlerInterface;

    public function withClassname(string $classname): HandlerInterface;

    public function withComponent(string $component): HandlerInterface;

    public function getType(): string;

    /**
     * @return int[]
     */
    public function getObjectIds(): array;

    public function getTargetRelease(): string;

    public function getClassname(): string;

    public function getComponent(): string;
}
