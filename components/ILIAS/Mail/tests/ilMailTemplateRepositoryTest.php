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

use PHPUnit\Framework\Attributes\Depends;

class ilMailTemplateRepositoryTest extends ilMailBaseTestCase
{
    public function testEntityCanBeSaved(): ilMailTemplate
    {
        $db = $this->getMockBuilder(ilDBInterface::class)->getMock();

        $repository = new ilMailTemplateRepository($db);

        $template_id = 666;

        $template = new ilMailTemplate();
        $template->setTitle('phpunit');
        $template->setSubject('FooBar');
        $template->setMessage('FooBar');
        $template->setLang('de');
        $template->setContext('4711');
        $template->setAsDefault(true);

        $db->expects($this->once())->method('nextId')->willReturn($template_id);
        $db->expects($this->once())->method('insert');

        $repository->store($template);

        $this->assertSame($template_id, $template->getTplId());

        return $template;
    }

    #[Depends('testEntityCanBeSaved')]
    public function testEntityCanBeModified(ilMailTemplate $template): ilMailTemplate
    {
        $db = $this->getMockBuilder(ilDBInterface::class)->getMock();

        $repository = new ilMailTemplateRepository($db);

        $db->expects($this->once())->method('update');

        $repository->store($template);

        return $template;
    }

    #[Depends('testEntityCanBeModified')]
    public function testEntityCanBeDeleted(ilMailTemplate $template): void
    {
        $db = $this->getMockBuilder(ilDBInterface::class)->getMock();

        $repository = new ilMailTemplateRepository($db);

        $db->expects($this->once())->method('manipulate');

        $repository->deleteByIds([$template->getTplId()]);
    }

    public function testTemplateCanBeFoundById(): void
    {
        $db = $this->getMockBuilder(ilDBInterface::class)->getMock();
        $statement = $this->getMockBuilder(ilDBStatement::class)->getMock();

        $template_id = 666;

        $empty_template = new ilMailTemplate();
        $empty_template->setTplId($template_id);

        $db->expects($this->once())->method('queryF')->willReturn($statement);
        $db->expects($this->once())->method('numRows')->willReturn(1);
        $db->expects($this->once())->method('fetchAssoc')->willReturn($empty_template->toArray());

        $repository = new ilMailTemplateRepository($db);
        $template = $repository->findById(4711);

        $this->assertSame($template_id, $template->getTplId());
    }

    public function testExceptionIsRaisedIfNoTemplateCanBeFoundById(): void
    {
        $this->expectException(OutOfBoundsException::class);

        $db = $this->getMockBuilder(ilDBInterface::class)->getMock();
        $statement = $this->getMockBuilder(ilDBStatement::class)->getMock();

        $db->expects($this->once())->method('queryF')->willReturn($statement);
        $db->expects($this->once())->method('numRows')->willReturn(0);
        $db->expects($this->never())->method('fetchAssoc');

        $repository = new ilMailTemplateRepository($db);
        $repository->findById(4711);
    }
}
