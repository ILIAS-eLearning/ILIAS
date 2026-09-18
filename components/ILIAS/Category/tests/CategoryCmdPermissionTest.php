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

use ILIAS\Category\Permission\CategoryCmdPermission;
use ILIAS\ILIASObject\Properties\Translations\TranslationGUI;
use PHPUnit\Framework\TestCase;

class CategoryCmdPermissionTest extends TestCase
{
    public function testTranslationGuiForwardingIsPermittedWithWritePermission(): void
    {
        $language = $this->createMock(ilLanguage::class);
        $access = $this->createMock(ilAccessHandler::class);
        $request = $this->createMock(\ILIAS\Category\StandardGUIRequest::class);

        $request
            ->expects($this->once())
            ->method('getRefId')
            ->willReturn(42);
        $access
            ->expects($this->once())
            ->method('checkAccess')
            ->with('write', '', 42)
            ->willReturn(true);

        $permission = new CategoryCmdPermission($language, $access, null, null, $request);

        $this->assertTrue(
            $permission->isForwardPermitted(ilObjCategoryGUI::class, TranslationGUI::class)
        );
    }
}
