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

namespace ILIAS\Export\Test\ExportHandler\Target;

use Exception;
use ILIAS\Export\ExportHandler\Target\Handler as ilExportHandlerTarget;
use PHPUnit\Framework\TestCase;

class HandlerTest extends TestCase
{
    public function testExportHandlerTarget(): void
    {
        $object_ids = array_map(function (string $id) {return $id + rand(0, 20); }, [0, 0, 0]);
        $type = "myType";
        $component = "componentcomponent";
        $class_name = "classclass";
        $v = ['10', '0'];
        $target_release = "2000.20";
        try {
            $target_1 = (new ilExportHandlerTarget())
                ->withType($type)
                ->withComponent($component)
                ->withClassname($class_name)
                ->withTargetRelease($target_release)
                ->withObjectIds($object_ids);
            self::assertEquals($type, $target_1->getType());
            self::assertEquals($component, $target_1->getComponent());
            self::assertEquals($class_name, $target_1->getClassname());
            self::assertEquals($target_release, $target_1->getTargetRelease());
            self::assertEmpty(array_diff($object_ids, $target_1->getObjectIds()));
        } catch (Exception $exception) {
            self::fail($exception->getMessage());
        }
    }
}
