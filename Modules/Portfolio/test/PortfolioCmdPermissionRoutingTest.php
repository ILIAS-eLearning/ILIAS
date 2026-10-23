<?php

declare(strict_types=1);

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

use ILIAS\Portfolio\StandardGUIRequest;
use Portfolio\Permission\PortfolioCmdPermission;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../../Services/Repository/Service/Permission/CmdPermissionInterface.php';
require_once __DIR__ . '/../../../Services/Repository/Service/Permission/CmdPermission.php';
require_once __DIR__ . '/../Permission/PortfolioCmdPermission.php';

class PortfolioCmdPermissionRoutingTest extends TestCase
{
    public function testPortfolioUsesLegacyStyleSettingsGUI(): void
    {
        $portfolio_access = $this->createMock(ilPortfolioAccessHandler::class);
        $portfolio_access->expects($this->once())
            ->method('checkAccess')
            ->with('write', '', 123)
            ->willReturn(true);

        $permission = $this->createPermission($portfolio_access, null);

        $this->assertTrue($permission->isForwardPermitted(
            ilObjPortfolioGUI::class,
            ilObjectContentStyleSettingsGUI::class
        ));
    }

    public function testPortfolioDoesNotUseUnavailableSettingsGUI(): void
    {
        $portfolio_access = $this->createMock(ilPortfolioAccessHandler::class);
        $portfolio_access->expects($this->never())->method('checkAccess');

        $permission = $this->createPermission($portfolio_access, null);

        $this->assertFalse($permission->isForwardPermitted(
            ilObjPortfolioGUI::class,
            'ILIAS\\Portfolio\\Settings\\SettingsGUI'
        ));
    }

    public function testPortfolioExerciseForwardRequiresAssignmentInPortfolio(): void
    {
        $portfolio_access = $this->createMock(ilPortfolioAccessHandler::class);
        $portfolio_access->expects($this->never())->method('checkAccess');
        $request = $this->createMock(StandardGUIRequest::class);
        $request->expects($this->once())
            ->method('getExcAssId')
            ->willReturn(456);

        $permission = new class (
            $this->createMock(ilLanguage::class),
            $portfolio_access,
            null,
            null,
            null,
            $request,
            123
        ) extends PortfolioCmdPermission {
            protected function portfolioAssignmentBelongsTo(int $node_id, int $assignment_id): bool
            {
                return false;
            }
        };

        $this->assertFalse($permission->isForwardPermitted(
            ilObjPortfolioGUI::class,
            ilPortfolioExerciseGUI::class
        ));
    }

    public function testPortfolioExerciseForwardStillRequiresWritePermission(): void
    {
        $portfolio_access = $this->createMock(ilPortfolioAccessHandler::class);
        $portfolio_access->expects($this->once())
            ->method('checkAccess')
            ->with('write', '', 123)
            ->willReturn(true);
        $request = $this->createMock(StandardGUIRequest::class);
        $request->expects($this->once())
            ->method('getExcAssId')
            ->willReturn(456);

        $permission = new class (
            $this->createMock(ilLanguage::class),
            $portfolio_access,
            null,
            null,
            null,
            $request,
            123
        ) extends PortfolioCmdPermission {
            protected function portfolioAssignmentBelongsTo(int $node_id, int $assignment_id): bool
            {
                return true;
            }
        };

        $this->assertTrue($permission->isForwardPermitted(
            ilObjPortfolioGUI::class,
            ilPortfolioExerciseGUI::class
        ));
    }

    public function testPortfolioTemplateUsesLegacyStyleSettingsGUI(): void
    {
        $repo_access = $this->createMock(ilAccessHandler::class);
        $repo_access->expects($this->once())
            ->method('checkAccess')
            ->with('write', '', 123)
            ->willReturn(true);

        $permission = $this->createPermission(null, $repo_access);

        $this->assertTrue($permission->isForwardPermitted(
            ilObjPortfolioTemplateGUI::class,
            ilObjectContentStyleSettingsGUI::class
        ));
    }

    public function testPortfolioTemplateDoesNotUseUnavailableSettingsGUI(): void
    {
        $repo_access = $this->createMock(ilAccessHandler::class);
        $repo_access->expects($this->never())->method('checkAccess');

        $permission = $this->createPermission(null, $repo_access);

        $this->assertFalse($permission->isForwardPermitted(
            ilObjPortfolioTemplateGUI::class,
            'ILIAS\\Portfolio\\Settings\\SettingsGUI'
        ));
    }

    private function createPermission(
        ?ilPortfolioAccessHandler $portfolio_access,
        ?ilAccessHandler $repo_access
    ): PortfolioCmdPermission {
        return new PortfolioCmdPermission(
            $this->createMock(ilLanguage::class),
            $portfolio_access,
            $repo_access,
            null,
            null,
            null,
            123
        );
    }
}
