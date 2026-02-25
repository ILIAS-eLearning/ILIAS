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

use ILIAS\Mail\TemplateEngine\TemplateEngineFactoryInterface;

class ilMailTemplateServiceTest extends ilMailBaseTestCase
{
    public function testDefaultTemplateCanBeSetByContext(): void
    {
        $repo = $this->getMockBuilder(ilMailTemplateRepository::class)->disableOriginalConstructor()->getMock();

        $template = new ilMailTemplate();
        $template->setTplId(1);
        $template->setAsDefault(false);
        $template->setContext('phpunit');

        $other_template = clone $template;
        $other_template->setTplId(2);
        $other_template->setAsDefault(false);

        $yet_another_template = clone $template;
        $yet_another_template->setTplId(3);
        $yet_another_template->setAsDefault(true);

        $all = [
            $template,
            $other_template,
            $yet_another_template,
        ];

        $repo->expects($this->once())->method('findByContextId')->with($template->getContext())->willReturn($all);
        $repo->expects($this->exactly(count($all)))->method('store');
        $template_engine_factory = $this->getMockBuilder(TemplateEngineFactoryInterface::class)->getMock();
        $service = new ilMailTemplateService($repo, $template_engine_factory);

        $service->setAsContextDefault($template);

        $this->assertTrue($template->isDefault());
        $this->assertFalse($other_template->isDefault());
        $this->assertFalse($yet_another_template->isDefault());
    }

    public function testDefaultTemplateForContextCanBeUnset(): void
    {
        $repo = $this->getMockBuilder(ilMailTemplateRepository::class)->disableOriginalConstructor()->getMock();

        $template = new ilMailTemplate();
        $template->setTplId(1);
        $template->setAsDefault(true);
        $template->setContext('phpunit');

        $repo->expects($this->once())->method('store')->with($template);
        $template_engine_factory = $this->getMockBuilder(TemplateEngineFactoryInterface::class)->getMock();
        $service = new ilMailTemplateService($repo, $template_engine_factory);

        $service->unsetAsContextDefault($template);

        $this->assertFalse($template->isDefault());
    }
}
