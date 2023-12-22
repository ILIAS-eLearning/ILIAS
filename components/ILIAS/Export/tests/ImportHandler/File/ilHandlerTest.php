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

namespace Test\ImportHandler\File;

use PHPUnit\Framework\TestCase;
use ILIAS\Export\ImportHandler\File\ilHandler as ilFileHandler;
use ILIAS\Export\ImportHandler\File\Namespace\ilFactory as ilFileNamespaceFactory;
use ILIAS\Export\ImportHandler\File\Namespace\ilCollection as ilFileNamespaceCollection;
use SplFileInfo;

class ilHandlerTest extends TestCase
{
    public function testFileHandler(): void
    {
        $file_name = 'TestFile.test';
        $file_dir = 'A' . DIRECTORY_SEPARATOR
            . 'B' . DIRECTORY_SEPARATOR
            . 'C';
        $file_path = $file_dir . DIRECTORY_SEPARATOR . $file_name;
        $namespaces = $this->createMock(ilFileNamespaceCollection::class);

        $namespace = $this->createMock(ilFileNamespaceFactory::class);
        $namespace->expects($this->any())->method('collection')->willReturn($namespaces);

        $file_info = $this->createMock(SplFileInfo::class);
        $file_info->expects($this->any())->method('getFilename')->willReturn($file_name);
        $file_info->expects($this->any())->method('getRealPath')->willReturn(false);
        $file_info->expects($this->any())->method('getPath')->willReturn($file_dir);

        $file_handler = new ilFileHandler($namespace);
        $file_handler = $file_handler->withFileInfo($file_info);

        $this->assertEquals($file_path, $file_handler->getFilePath());
        $this->assertFalse($file_handler->fileExists());
        $this->assertEquals($file_name, $file_handler->getFileName());
        $this->assertEquals($namespaces, $file_handler->getNamespaces());
        $this->assertTrue($file_handler->pathContainsFolderName('A'));
        $this->assertTrue($file_handler->pathContainsFolderName('B'));
        $this->assertTrue($file_handler->pathContainsFolderName('C'));
        $this->assertFalse($file_handler->pathContainsFolderName('D'));
        $this->assertFalse($file_handler->pathContainsFolderName('E'));
        $this->assertEquals('B/C/TestFile.test', $file_handler->getSubPathToDirBeginningAtPathEnd('B')->getFilePath());
        $this->assertEquals('A/B', $file_handler->getSubPathToDirBeginningAtPathStart('B')->getFilePath());
        $this->assertEquals($file_dir, $file_handler->getPathToFileLocation());
        $this->assertEquals('C', $file_handler->getPathPart("/C/"));
    }
}
