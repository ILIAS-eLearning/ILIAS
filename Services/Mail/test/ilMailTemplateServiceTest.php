<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilMailTemplateServiceTest
 */
class ilMailTemplateServiceTest extends \ilMailBaseTest
{
    /**
     *
     */
    protected function setUp()
    {
        parent::setUp();
    }

    /**
     *
     */
    public function testDefaultTemplateCanBeSetByContext()
    {
        $repo = $this->getMockbuilder(\ilMailTemplateRepository::class)->disableOriginalConstructor()->getMock();

        $template = new \ilMailTemplate();
        $template->setTplId(1);
        $template->setAsDefault(false);
        $template->setContext('phpunit');

        $otherTemplate = clone $template;
        $otherTemplate->setTplId(2);
        $otherTemplate->setAsDefault(false);

        $yetAnotherTemplate = clone $template;
        $yetAnotherTemplate->setTplId(3);
        $yetAnotherTemplate->setAsDefault(true);

        $all = [
            $template,
            $otherTemplate,
            $yetAnotherTemplate,
        ];

        $repo->expects($this->once())->method('findByContextId')->with($template->getContext())->willReturn($all);
        $repo->expects($this->exactly(count($all)))->method('store');
        $service = new \ilMailTemplateService($repo);

        $service->setAsContextDefault($template);

        $this->assertTrue($template->isDefault());
        $this->assertFalse($otherTemplate->isDefault());
        $this->assertFalse($yetAnotherTemplate->isDefault());
    }

    /**
     *
     */
    public function testDefaultTemplateForContextCanBeUnset()
    {
        $repo = $this->getMockbuilder(\ilMailTemplateRepository::class)->disableOriginalConstructor()->getMock();

        $template = new \ilMailTemplate();
        $template->setTplId(1);
        $template->setAsDefault(true);
        $template->setContext('phpunit');

        $repo->expects($this->once())->method('store')->with($template);
        $service = new \ilMailTemplateService($repo);

        $service->unsetAsContextDefault($template);

        $this->assertFalse($template->isDefault());
    }
}
