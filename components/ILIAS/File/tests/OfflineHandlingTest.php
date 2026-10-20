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

use ILIAS\ILIASObject\Properties\CoreProperties\Online;
use ILIAS\ILIASObject\Properties\Properties;
use PHPUnit\Framework\TestCase;

/**
 * The online status of copied files relies on the central offline handling,
 * see ilObject::cloneObject(). New files are online by default, see ilObjFile::doCreate().
 * @see https://mantis.ilias.de/view.php?id=48551
 */
class OfflineHandlingTest extends TestCase
{
    public function testFileObjectSupportsOfflineHandling(): void
    {
        $module = simplexml_load_file(__DIR__ . '/../module.xml');
        $this->assertNotFalse($module);

        $file_definitions = array_filter(
            iterator_to_array($module->objects->object, false),
            static fn(SimpleXMLElement $object): bool => (string) $object['id'] === 'file'
        );
        $this->assertCount(1, $file_definitions);
        $this->assertSame('1', (string) reset($file_definitions)['offline_handling']);
    }

    public function testNewFileIsOnline(): void
    {
        $object_properties = $this->createMock(Properties::class);
        $object_properties
            ->expects($this->once())
            ->method('storePropertyIsOnline')
            ->with($this->callback(
                static fn(Online $property): bool => $property->getIsOnline()
            ));

        $file = $this->createPartialMock(
            ilObjFile::class,
            ['createProperties', 'updateCopyright', 'notifyCreation', 'getObjectProperties', 'getId', 'getDescription']
        );
        $file->expects($this->once())->method('getObjectProperties')->willReturn($object_properties);

        (new ReflectionMethod($file, 'doCreate'))->invoke($file);
    }
}
