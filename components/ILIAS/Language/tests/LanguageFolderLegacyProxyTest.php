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

use ILIAS\Language\LanguageFolderLegacyProxy;

class LanguageFolderLegacyProxyTest extends ilLanguageBaseTestCase
{
    public function testDatabaseIsOnlyReadWhenTheRefIdIsRequested(): void
    {
        $queried = false;
        $db = $this->createStub(ilDBInterface::class);
        $db->method('quote')->willReturn("'lngf'");
        $db->method('query')->willReturnCallback(function () use (&$queried): ilDBStatement {
            $queried = true;
            return $this->createStub(ilDBStatement::class);
        });
        $db->method('fetchAssoc')->willReturn(['ref_id' => '42']);
        $GLOBALS['DIC']['ilDB'] = $db;

        $proxy = new LanguageFolderLegacyProxy();
        self::assertFalse($queried);

        self::assertSame(42, $proxy->getRefId());
        self::assertTrue($queried);
    }
}
