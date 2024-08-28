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

namespace ILIAS\Export\ExportHandler\Target;

use ILIAS\Export\ExportHandler\I\Target\HandlerInterface as ilExportHandlerTargetInterface;

class Handler implements ilExportHandlerTargetInterface
{
    protected string $type;
    protected array $object_ids;
    protected string $target_release;
    protected string $class_name;
    protected string $component;

    public function __construct()
    {
        $this->type = "";
        $this->target_release = "";
        $this->class_name = "";
        $this->component = "";
        $this->object_ids = [];
    }

    public function withType(string $type): ilExportHandlerTargetInterface
    {
        $clone = clone $this;
        $clone->type = $type;
        return $clone;
    }

    public function withObjectIds(array $object_ids): ilExportHandlerTargetInterface
    {
        $clone = clone $this;
        $clone->object_ids = $object_ids;
        return $clone;
    }

    public function withTargetRelease(string $target_release): ilExportHandlerTargetInterface
    {
        $clone = clone $this;
        $clone->target_release = $target_release;
        return $clone;
    }

    public function withClassname(string $classname): ilExportHandlerTargetInterface
    {
        $clone = clone $this;
        $clone->class_name = $classname;
        return $clone;
    }

    public function withComponent(string $component): ilExportHandlerTargetInterface
    {
        $clone = clone $this;
        $clone->component = $component;
        return $clone;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getObjectIds(): array
    {
        return $this->object_ids;
    }

    public function getTargetRelease(): string
    {
        return $this->target_release;
    }

    public function getClassname(): string
    {
        return $this->class_name;
    }

    public function getComponent(): string
    {
        return $this->component;
    }
}
