<?php declare(strict_types=1);

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
 * Class ilMailTemplateRepository
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilMailTemplateRepositoryTest extends ilMailBaseTest
{
    /**
     * @throws ReflectionException
     */
    public function testEntityCanBeSaved() : ilMailTemplate
    {
        $db = $this->getMockBuilder(ilDBInterface::class)->getMock();

        $repository = new ilMailTemplateRepository($db);

        $templateId = 666;

        $template = new ilMailTemplate();
        $template->setTitle('phpunit');
        $template->setSubject('FooBar');
        $template->setMessage('FooBar');
        $template->setLang('de');
        $template->setContext('4711');
        $template->setAsDefault(true);

        $db->expects($this->once())->method('nextId')->willReturn($templateId);
        $db->expects($this->once())->method('insert');

        $repository->store($template);

        $this->assertSame($templateId, $template->getTplId());

        return $template;
    }

    /**
     * @depends testEntityCanBeSaved
     * @throws ReflectionException
     */
    public function testEntityCanBeModified(ilMailTemplate $template) : ilMailTemplate
    {
        $db = $this->getMockBuilder(ilDBInterface::class)->getMock();

        $repository = new ilMailTemplateRepository($db);

        $db->expects($this->once())->method('update');

        $repository->store($template);

        return $template;
    }

    /**
     * @depends testEntityCanBeModified
     * @throws ReflectionException
     */
    public function testEntityCanBeDeleted(ilMailTemplate $template) : void
    {
        $db = $this->getMockBuilder(ilDBInterface::class)->getMock();

        $repository = new ilMailTemplateRepository($db);

        $db->expects($this->once())->method('manipulate');

        $repository->deleteByIds([$template->getTplId()]);
    }

    /**
     * @throws ReflectionException
     */
    public function testTemplateCanBeFoundById() : void
    {
        $db = $this->getMockBuilder(ilDBInterface::class)->getMock();
        $statement = $this->getMockBuilder(ilDBStatement::class)->getMock();

        $templateId = 666;

        $emptyTemplate = new ilMailTemplate();
        $emptyTemplate->setTplId($templateId);

        $db->expects($this->once())->method('queryF')->willReturn($statement);
        $db->expects($this->once())->method('numRows')->willReturn(1);
        $db->expects($this->once())->method('fetchAssoc')->willReturn($emptyTemplate->toArray());

        $repository = new ilMailTemplateRepository($db);
        $template = $repository->findById(4711);

        $this->assertSame($templateId, $template->getTplId());
    }

    /**
     * @throws ReflectionException
     */
    public function testExceptionIsRaisedIfNoTemplateCanBeFoundById() : void
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
