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

namespace ILIAS\LegalDocuments\test\Provide;

use ILIAS\LegalDocuments\test\ContainerMock;
use PHPUnit\Framework\TestCase;
use ILIAS\LegalDocuments\Provide\ProvidePublicPage;
use ilCtrl;
use ilStartUpGUI;

require_once __DIR__ . '/../ContainerMock.php';

class ProvidePublicPageTest extends TestCase
{
    use ContainerMock;

    public function testConstruct(): void
    {
        $this->assertInstanceOf(ProvidePublicPage::class, new ProvidePublicPage('foo', $this->mock(ilCtrl::class)));
    }

    public function testUrl(): void
    {
        $ctrl = $this->mock(ilCtrl::class);
        $consecutive = ['foo', ''];
        $ctrl->expects(self::exactly(2))->method('setParameterByClass')->with(
            $this->identicalTo(ilStartUpGUI::class),
            $this->identicalTo('id'),
            $this->callback(function ($value) use (&$consecutive) {
                $this->assertSame(array_shift($consecutive), $value);
                return true;
            })
        );
        $ctrl->expects(self::once())->method('getLinkTargetByClass')->with(ilStartUpGUI::class, 'showLegalDocuments')->willReturn('url');

        $instance = new ProvidePublicPage('foo', $ctrl);
        $this->assertSame('url', $instance->url());
    }
}
