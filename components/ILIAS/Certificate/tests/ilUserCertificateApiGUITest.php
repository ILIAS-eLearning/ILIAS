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

use Psr\Http\Message\ServerRequestInterface;
use PHPUnit\Framework\MockObject\Stub;

class ilUserCertificateApiGUITest extends ilCertificateBaseTestCase
{
    public function testCreationOfGuiClass(): void
    {
        /** @var ilLanguage&Stub $language */
        $language = $this->createStub(ilLanguage::class);

        /** @var ServerRequestInterface&Stub $request */
        $request = $this->createStub(ServerRequestInterface::class);

        /** @var ilLogger&Stub $logger */
        $logger = $this->createStub(ilLogger::class);

        /** @var ilCtrlInterface&Stub $controller */
        $controller = $this->createStub(ilCtrlInterface::class);

        $gui = new ilUserCertificateApiGUI($language, $request, $logger, $controller);
        $this->assertInstanceOf(ilUserCertificateApiGUI::class, $gui);
    }
}
