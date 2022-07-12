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
        $repo = $this->getMockBuilder(ilMailTemplateRepository::class)->disableOriginalConstructor()->getMock();

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
        $repo = $this->getMockBuilder(ilMailTemplateRepository::class)->disableOriginalConstructor()->getMock();

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
