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

use PHPUnit\Framework\TestCase;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class PCFileListTest extends COPageTestBase
{
    public function testConstruction(): void
    {
        $page = $this->getEmptyPageWithDom();
        $pc = new ilPCFileList($page);
        $this->assertEquals(
            ilPCFileList::class,
            get_class($pc)
        );
    }

    public function testCreate(): void
    {
        $page = $this->getEmptyPageWithDom();
        $pc = new ilPCFileList($page);
        $pc->create($page, "pg");
        $this->assertXmlEquals(
            '<PageObject HierId="pg"><PageContent><FileList></FileList></PageContent></PageObject>',
            $page->getXMLFromDom()
        );
    }

    public function testAppendItem(): void
    {
        $page = $this->getEmptyPageWithDom();
        $pc = new ilPCFileList($page);
        $pc->create($page, "pg");
        $pc->appendItem("10", "file_loc", "image/jpeg");
        $expected = <<<EOT
<PageObject HierId="pg"><PageContent><FileList><FileItem><Identifier Catalog="ILIAS" Entry="il__file_10"></Identifier><Location Type="LocalFile">file_loc</Location><Format>image/jpeg</Format></FileItem></FileList></PageContent></PageObject>
EOT;

        $this->assertXmlEquals(
            $expected,
            $page->getXMLFromDom()
        );
    }

    public function testListTitle(): void
    {
        $page = $this->getEmptyPageWithDom();
        $pc = new ilPCFileList($page);
        $pc->create($page, "pg");
        $pc->setListTitle("MyTitle", "en");
        $expected = <<<EOT
<PageObject HierId="pg"><PageContent><FileList><Title Language="en">MyTitle</Title></FileList></PageContent></PageObject>
EOT;

        $this->assertXmlEquals(
            $expected,
            $page->getXMLFromDom()
        );

        $this->assertEquals(
            "MyTitle",
            $pc->getListTitle()
        );

        $this->assertEquals(
            "en",
            $pc->getLanguage()
        );
    }

    public function testFileList(): void
    {
        $page = $this->getEmptyPageWithDom();
        $pc = new ilPCFileList($page);
        $pc->create($page, "pg");
        $pc->appendItem("10", "file_loc", "image/jpeg");
        $page->addHierIDs();
        $pc->setHierId("1");
        $this->assertEquals(
            [ 0 =>
                ["entry" => "il__file_10",
                "id" => "10",
                "pc_id" => "",
                "hier_id" => "1_1",
                "class" => ""]
            ],
            $pc->getFileList()
        );
    }

    public function testDeleteFileItem(): void
    {
        $page = $this->getEmptyPageWithDom();
        $pc = new ilPCFileList($page);
        $pc->create($page, "pg");
        $pc->appendItem("10", "file_loc", "image/jpeg");
        $page->addHierIDs();
        $pc->setHierId("1");
        $pc->deleteFileItems(["1_1:"]);

        $this->assertXmlEquals(
            '<PageObject HierId="pg"><PageContent HierId="1"><FileList></FileList></PageContent></PageObject>',
            $page->getXMLFromDom()
        );
    }

    public function testPositions(): void
    {
        $page = $this->getEmptyPageWithDom();
        $pc = new ilPCFileList($page);
        $pc->create($page, "pg");
        $pc->appendItem("10", "file_loc", "image/jpeg");
        $pc->appendItem("20", "file_loc2", "image/png");
        $page->addHierIDs();
        $pc->setHierId("1");

        $pc->savePositions([
            "1_1:" => 20,
            "1_2:" => 10,
        ]);
        $page->stripHierIDs();
        $expected = <<<EOT
<PageObject><PageContent><FileList><FileItem><Identifier Catalog="ILIAS" Entry="il__file_20"></Identifier><Location Type="LocalFile">file_loc2</Location><Format>image/png</Format></FileItem><FileItem><Identifier Catalog="ILIAS" Entry="il__file_10"></Identifier><Location Type="LocalFile">file_loc</Location><Format>image/jpeg</Format></FileItem></FileList></PageContent></PageObject>
EOT;
        $this->assertXmlEquals(
            $expected,
            $page->getXMLFromDom()
        );
    }

    public function testClasses(): void
    {
        $page = $this->getEmptyPageWithDom();
        $pc = new ilPCFileList($page);
        $pc->create($page, "pg");
        $pc->appendItem("10", "file_loc", "image/jpeg");
        $pc->appendItem("20", "file_loc2", "image/png");
        $page->addHierIDs();
        $pc->setHierId("1");
        $pc->saveStyleClasses([
            "1_1:" => "Class1",
            "1_2:" => "Class2",
        ]);
        $classes = $pc->getAllClasses();

        $this->assertEquals(
            [
                "1_1:" => "Class1",
                "1_2:" => "Class2",
            ],
            $pc->getAllClasses()
        );
    }

    //
    // Test file items
    //

    protected function getPageWithFileList(): ilPageObject
    {
        $page = $this->getEmptyPageWithDom();
        $pc = new ilPCFileList($page);
        $pc->create($page, "pg");
        $pc->appendItem("10", "file_loc", "image/jpeg");
        $pc->appendItem("20", "file_loc2", "image/png");
        $page->addHierIDs();
        $page->insertPCIds();
        $pc->setHierId("1");
        return $page;
    }

    protected function getItemForHierId(ilPageObject $page, string $hier_id): ilPCFileItem
    {
        $pc_id = $page->getPCIdForHierId($hier_id);
        $cont_node = $page->getContentDomNode($hier_id);
        $pc = new ilPCFileItem($page);
        $pc->setDomNode($cont_node);
        $pc->setHierId($hier_id);
        $pc->setPcId($pc_id);
        return $pc;
    }

    public function testNewItemAfter(): void
    {
        $page = $this->getPageWithFileList();
        $item = $this->getItemForHierId($page, "1_1");
        $item->newItemAfter("30", "file_loc3", "image/gif");
        $page->stripHierIDs();
        $page->stripPCIDs();

        $expected = <<<EOT
<PageObject><PageContent><FileList><FileItem><Identifier Catalog="ILIAS" Entry="il__file_10"/><Location Type="LocalFile">file_loc</Location><Format>image/jpeg</Format></FileItem><FileItem><Identifier Catalog="ILIAS" Entry="il__file_30"/><Location Type="LocalFile">file_loc3</Location><Format>image/gif</Format></FileItem><FileItem><Identifier Catalog="ILIAS" Entry="il__file_20"/><Location Type="LocalFile">file_loc2</Location><Format>image/png</Format></FileItem></FileList></PageContent></PageObject>
EOT;

        $this->assertXmlEquals(
            $expected,
            $page->getXMLFromDom()
        );
    }

    public function testNewItemBefore(): void
    {
        $page = $this->getPageWithFileList();
        $item = $this->getItemForHierId($page, "1_2");
        $item->newItemBefore("30", "file_loc3", "image/gif");
        $page->stripHierIDs();
        $page->stripPCIDs();

        $expected = <<<EOT
<PageObject><PageContent><FileList><FileItem><Identifier Catalog="ILIAS" Entry="il__file_10"/><Location Type="LocalFile">file_loc</Location><Format>image/jpeg</Format></FileItem><FileItem><Identifier Catalog="ILIAS" Entry="il__file_30"/><Location Type="LocalFile">file_loc3</Location><Format>image/gif</Format></FileItem><FileItem><Identifier Catalog="ILIAS" Entry="il__file_20"/><Location Type="LocalFile">file_loc2</Location><Format>image/png</Format></FileItem></FileList></PageContent></PageObject>
EOT;

        $this->assertXmlEquals(
            $expected,
            $page->getXMLFromDom()
        );
    }

    public function testDeleteItem(): void
    {
        $page = $this->getPageWithFileList();
        $item = $this->getItemForHierId($page, "1_2");
        $item->deleteItem();
        $page->stripHierIDs();
        $page->stripPCIDs();

        $expected = <<<EOT
<PageObject><PageContent><FileList><FileItem><Identifier Catalog="ILIAS" Entry="il__file_10"/><Location Type="LocalFile">file_loc</Location><Format>image/jpeg</Format></FileItem></FileList></PageContent></PageObject>
EOT;

        $this->assertXmlEquals(
            $expected,
            $page->getXMLFromDom()
        );
    }

    public function testMoveItemDown(): void
    {
        $page = $this->getPageWithFileList();
        $item = $this->getItemForHierId($page, "1_1");
        $item->moveItemDown();
        $page->stripHierIDs();
        $page->stripPCIDs();

        $expected = <<<EOT
<PageObject><PageContent><FileList><FileItem><Identifier Catalog="ILIAS" Entry="il__file_20"/><Location Type="LocalFile">file_loc2</Location><Format>image/png</Format></FileItem><FileItem><Identifier Catalog="ILIAS" Entry="il__file_10"/><Location Type="LocalFile">file_loc</Location><Format>image/jpeg</Format></FileItem></FileList></PageContent></PageObject>
EOT;

        $this->assertXmlEquals(
            $expected,
            $page->getXMLFromDom()
        );
    }

    public function testMoveItemUp(): void
    {
        $page = $this->getPageWithFileList();
        $item = $this->getItemForHierId($page, "1_2");
        $item->moveItemUp();
        $page->stripHierIDs();
        $page->stripPCIDs();

        $expected = <<<EOT
<PageObject><PageContent><FileList><FileItem><Identifier Catalog="ILIAS" Entry="il__file_20"/><Location Type="LocalFile">file_loc2</Location><Format>image/png</Format></FileItem><FileItem><Identifier Catalog="ILIAS" Entry="il__file_10"/><Location Type="LocalFile">file_loc</Location><Format>image/jpeg</Format></FileItem></FileList></PageContent></PageObject>
EOT;

        $this->assertXmlEquals(
            $expected,
            $page->getXMLFromDom()
        );
    }
}
