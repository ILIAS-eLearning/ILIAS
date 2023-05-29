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

namespace ILIAS\COPage\Test\PC\FileList;

use PHPUnit\Framework\TestCase;
use ILIAS\COPage\PC\FileList\FileListManager;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class FileListManagerTest extends \COPageTestBase
{
    public function testGetAllFileObjIds(): void
    {
        $page = $this->getEmptyPageWithDom();
        $fm = new FileListManager();

        $page = $this->getEmptyPageWithDom();
        $pc = new \ilPCFileList($page);
        $pc->create($page, "pg");
        $pc->appendItem(10, "file_loc", "image/jpeg");
        $pc->appendItem(12, "file_loc2", "image/jpeg");
        $pc->appendItem(14, "file_loc3", "image/jpeg");
        $page->insertPCIds();

        $this->assertEquals(
            ["il__file_10", "il__file_12", "il__file_14"],
            $fm->getAllFileObjIds($page->getDomDoc())
        );
    }
}
