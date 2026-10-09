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

use ILIAS\ResourceStorage\Manager\Manager;
use ILIAS\ResourceStorage\Services as IRSS;
use ILIAS\ResourceStorage\Revision\Revision;
use ILIAS\ResourceStorage\Information\FileInformation;
use ILIAS\ResourceStorage\Identification\ResourceIdentification;

class ilFileDataMailFileNameTest extends ilMailBaseTestCase
{
    public function testApplyResourceFileNameSetsTitleAndSuffix(): void
    {
        $rid = new ResourceIdentification('ics-rid');
        $information = new FileInformation();
        $information->setTitle('abcdef');

        $revision = $this->createMock(Revision::class);
        $revision->expects($this->once())->method('getInformation')->willReturn($information);
        $revision->expects($this->once())->method('setInformation')->with($information);

        $manage = $this->createMock(Manager::class);
        $manage->expects($this->once())->method('getCurrentRevision')->with($rid)->willReturn($revision);
        $manage->expects($this->once())->method('updateRevision')->with($revision);

        $irss = $this->createStub(IRSS::class);
        $irss->method('manage')->willReturn($manage);

        $reflection = new ReflectionClass(ilFileDataMail::class);
        $sut = $reflection->newInstanceWithoutConstructor();
        $reflection->getProperty('irss')->setValue($sut, $irss);

        $reflection->getMethod('applyResourceFileName')->invoke($sut, $rid, 'appointment_20261008.ics');

        $this->assertSame('appointment_20261008.ics', $information->getTitle());
        $this->assertSame('ics', $information->getSuffix());
    }

    public function testApplyResourceFileNameIgnoresEmptyName(): void
    {
        $manage = $this->createMock(Manager::class);
        $manage->expects($this->never())->method('getCurrentRevision');

        $irss = $this->createStub(IRSS::class);
        $irss->method('manage')->willReturn($manage);

        $reflection = new ReflectionClass(ilFileDataMail::class);
        $sut = $reflection->newInstanceWithoutConstructor();
        $reflection->getProperty('irss')->setValue($sut, $irss);

        $reflection->getMethod('applyResourceFileName')->invoke($sut, new ResourceIdentification('ics-rid'), '');
    }
}
