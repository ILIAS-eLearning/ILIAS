<?php declare(strict_types=1);

/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilMailTemplateServiceTest
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilMailTemplateServiceTest extends ilMailBaseTest
{
    /**
     * @throws ReflectionException
     */
    public function testDefaultTemplateCanBeSetByContext() : void
    {
        $repo = $this->getMockbuilder(ilMailTemplateRepository::class)->disableOriginalConstructor()->getMock();

        $template = new ilMailTemplate();
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
        $service = new ilMailTemplateService($repo);

        $service->setAsContextDefault($template);

        $this->assertTrue($template->isDefault());
        $this->assertFalse($otherTemplate->isDefault());
        $this->assertFalse($yetAnotherTemplate->isDefault());
    }

    /**
     * @throws ReflectionException
     */
    public function testDefaultTemplateForContextCanBeUnset() : void
    {
        $repo = $this->getMockbuilder(ilMailTemplateRepository::class)->disableOriginalConstructor()->getMock();

        $template = new ilMailTemplate();
        $template->setTplId(1);
        $template->setAsDefault(true);
        $template->setContext('phpunit');

        $repo->expects($this->once())->method('store')->with($template);
        $service = new ilMailTemplateService($repo);

        $service->unsetAsContextDefault($template);

        $this->assertFalse($template->isDefault());
    }
}