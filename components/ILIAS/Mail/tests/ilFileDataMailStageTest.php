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
use PHPUnit\Framework\MockObject\MockObject;
use ILIAS\ResourceStorage\Collection\Collections;
use ILIAS\ResourceStorage\Collection\ResourceCollection;
use ILIAS\ResourceStorage\Identification\ResourceIdentification;
use ILIAS\ResourceStorage\Identification\ResourceCollectionIdentification;

class ilFileDataMailStageTest extends ilMailBaseTestCase
{
    private ResourceCollection $stage;
    private Manager&MockObject $manage;
    private Collections&MockObject $collections;

    public function testRestrictingTheStageDeletesFilesMissingInTheForm(): void
    {
        $sut = $this->createSut(has_stage: true);
        $this->collections->expects($this->once())->method('store')->with($this->stage);
        $this->manage->expects($this->once())->method('remove')->with(
            $this->callback(static fn(ResourceIdentification $rid): bool => $rid->serialize() === 'rid-b')
        );

        $rcid = $sut->restrictStageTo(['rid-a']);

        $this->assertSame('33333333-3333-4333-8333-333333333333', $rcid?->serialize());
        $this->assertSame(['rid-a'], $this->stageRids());
    }

    public function testRestrictingTheStageToAllFilesKeepsIt(): void
    {
        $sut = $this->createSut(has_stage: true);
        $this->collections->expects($this->never())->method('store');
        $this->manage->expects($this->never())->method('remove');

        $this->assertSame('33333333-3333-4333-8333-333333333333', $sut->restrictStageTo(['rid-a', 'rid-b'])?->serialize());
    }

    public function testWithoutStageNothingIsRestricted(): void
    {
        $sut = $this->createSut(has_stage: false);
        $this->collections->expects($this->never())->method('store');
        $this->manage->expects($this->never())->method('remove');

        $this->assertNull($sut->restrictStageTo(['rid-a']));
    }

    public function testOnlyFilesOfTheStageCanBeUsedInTheComposeForm(): void
    {
        $sut = $this->createSut(has_stage: true);
        $this->collections->expects($this->never())->method('store');
        $this->manage->expects($this->never())->method('remove');

        $this->assertTrue($sut->canUseResourceInCompose(new ResourceIdentification('rid-a')));
        $this->assertFalse($sut->canUseResourceInCompose(new ResourceIdentification('foreign-rid')));
    }

    public function testRemovingAFileOutsideTheStageDeletesNothing(): void
    {
        $sut = $this->createSut(has_stage: true);
        $this->collections->expects($this->never())->method('store');
        $this->manage->expects($this->never())->method('remove');

        $this->assertFalse($sut->removeFromStage(new ResourceIdentification('foreign-rid')));
    }

    public function testRemovingAStageFileDeletesIt(): void
    {
        $sut = $this->createSut(has_stage: true);
        $this->collections->expects($this->once())->method('store')->with($this->stage);
        $this->manage->expects($this->once())->method('remove');

        $this->assertTrue($sut->removeFromStage(new ResourceIdentification('rid-b')));
        $this->assertSame(['rid-a'], $this->stageRids());
    }

    private function createSut(bool $has_stage): ilFileDataMail
    {
        $this->stage = new ResourceCollection(new ResourceCollectionIdentification('33333333-3333-4333-8333-333333333333'), 6, '');
        $this->stage->add(new ResourceIdentification('rid-a'));
        $this->stage->add(new ResourceIdentification('rid-b'));

        $this->collections = $this->createMock(Collections::class);
        $this->collections->method('exists')->willReturn(true);
        $this->collections->method('get')->willReturn($this->stage);

        $this->manage = $this->createMock(Manager::class);

        $irss = $this->createStub(IRSS::class);
        $irss->method('collection')->willReturn($this->collections);
        $irss->method('manage')->willReturn($this->manage);

        $db = $this->createStub(ilDBInterface::class);
        $db->method('queryF')->willReturn($this->createStub(ilDBStatement::class));
        $db->method('fetchAssoc')->willReturn($has_stage ? ['attachments' => '33333333-3333-4333-8333-333333333333'] : null);

        $reflection = new ReflectionClass(ilFileDataMail::class);
        $sut = $reflection->newInstanceWithoutConstructor();
        $sut->user_id = 6;
        $reflection->getProperty('irss')->setValue($sut, $irss);
        $reflection->getProperty('db')->setValue($sut, $db);
        $reflection->getProperty('stakeholder')->setValue($sut, new ilMailAttachmentStakeholder(6));

        return $sut;
    }

    /**
     * @return list<string>
     */
    private function stageRids(): array
    {
        return array_values(array_map(
            static fn(ResourceIdentification $rid): string => $rid->serialize(),
            $this->stage->getResourceIdentifications()
        ));
    }
}
