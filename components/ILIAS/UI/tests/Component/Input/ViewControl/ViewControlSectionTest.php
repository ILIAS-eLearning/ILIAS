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

use ILIAS\UI\Implementation\Component\Input\ViewControl as Control;

require_once('ViewControlTestBase.php');

class ViewControlSectionTest extends ViewControlTestBase
{
    public function testSectionAcceptsDropdown(): void
    {
        $previous = $this->getUIFactory()->button()->standard('previous', '#');
        $dropdown = (new \ILIAS\UI\Implementation\Component\Dropdown\Standard([]))
            ->withLabel('section');
        $next = $this->getUIFactory()->button()->standard('next', '#');

        $section = $this->buildVCFactory()->section($previous, $dropdown, $next);

        $this->assertInstanceOf(Control\Section::class, $section);
        $this->assertSame($previous, $section->getPreviousActions());
        $this->assertSame($dropdown, $section->getSelectorButton());
        $this->assertSame($next, $section->getNextActions());
    }

    public function testSectionDropdownRendering(): void
    {
        $previous = $this->getUIFactory()->button()->standard('previous', '#');
        $dropdown = (new \ILIAS\UI\Implementation\Component\Dropdown\Standard([]))
            ->withLabel('section');
        $next = $this->getUIFactory()->button()->standard('next', '#');
        $section = $this->buildVCFactory()->section($previous, $dropdown, $next);

        $html = $this->getDefaultRenderer(null, [$dropdown])->render($section);

        $this->assertStringContainsString($dropdown->getCanonicalName(), $html);
    }
}
