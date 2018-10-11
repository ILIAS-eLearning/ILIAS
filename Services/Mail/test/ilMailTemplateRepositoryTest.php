<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilMailTemplateRepository
 */
class ilMailTemplateRepositoryTest extends \ilMailBaseTest
{
	/**
	 *
	 */
	protected function setUp()
	{
		parent::setUp();
	}

	/**
	 * @return \ilMailTemplate
	 */
	public function testEntityCanBeSaved(): \ilMailTemplate
	{
		$db = $this->getMockbuilder(\ilDBInterface::class)->getMock();

		$repository = new \ilMailTemplateRepository($db);

		$templateId = 666;

		$template = new \ilMailTemplate();
		$template->setTitle('phpunit');
		$template->setSubject('FooBar');
		$template->setMessage('FooBar');
		$template->setLang('de');
		$template->setContext('4711');
		$template->setAsDefault(true);

		$db->expects($this->once())->method('nextId')->willReturn($templateId);
		$db->expects($this->once())->method('insert');

		$repository->store($template);

		$this->assertEquals($templateId, $template->getTplId());

		return $template;
	}

	/**
	 * @depends testEntityCanBeSaved
	 * @param \ilMailTemplate $template
	 * @return \ilMailTemplate
	 */
	public function testEntityCanBeModified(\ilMailTemplate $template): \ilMailTemplate
	{
		$db = $this->getMockbuilder(\ilDBInterface::class)->getMock();

		$repository = new \ilMailTemplateRepository($db);

		$db->expects($this->once())->method('update');

		$repository->store($template);

		return $template;
	}

	/**
	 * @depends testEntityCanBeModified
	 * @param \ilMailTemplate $template
	 */
	public function testEntityCanBeDeleted(\ilMailTemplate $template)
	{
		$db = $this->getMockbuilder(\ilDBInterface::class)->getMock();

		$repository = new \ilMailTemplateRepository($db);

		$repository->deleteByIds([$template->getTplId()]);

		$db->expects($this->once())->method('manipulate');
	}
}