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
 ********************************************************************
 */

/**
 * Class ilObjLanguageDBAccessTest
 * @author  Christian Knof <christian.knof@kroepelin-projekte.de>
 */

class ilObjLanguageDBAccessTest extends ilLanguageBaseTest
{
    private ilDBInterface $ilDB;
    
    protected function setUp(): void
    {
        $ilDB_mock = $this->getMockBuilder(ilDBInterface::class)->getMock();
        $this->ilDB = $ilDB_mock;
    }
    
    public function testCreate(): void
    {
        $key = "en";
        $content = ["acc#:#acc_add_document_btn_label#:#Add Document", "administration#:#adm_achievements#:#Achievements"];
        $local_changes = [];
        
        $ilObjLanguageDBAccess = new ilObjLanguageDBAccess($this->ilDB, $key, $content, $local_changes);
        $this->assertInstanceOf(\ilObjLanguageDBAccess::class, $ilObjLanguageDBAccess);
    }
    
    public function testInsertLangEntriesReturnsArray(): void
    {
        $key = "en";
        $content = ["acc#:#acc_add_document_btn_label#:#Add Document", "administration#:#adm_achievements#:#Achievements"];
        $local_changes = [];
        
        $ilObjLanguageDBAccess = new ilObjLanguageDBAccess($this->ilDB, $key, $content, $local_changes);
        
        $result = $ilObjLanguageDBAccess->insertLangEntries("lang/ilias_en.lang");
        
        $this->assertIsArray($result);
    }
    
    public function testInsertLangEntriesReturnedArrayHasValuesFromContent(): void
    {
        $key = "en";
        $content = ["acc#:#acc_add_document_btn_label#:#Add Document"];
        $local_changes = [];
        
        $ilObjLanguageDBAccess = new ilObjLanguageDBAccess($this->ilDB, $key, $content, $local_changes);
        $result = $ilObjLanguageDBAccess->insertLangEntries("lang/ilias_en.lang");
        
        $this->assertArrayHasKey("acc", $result);
        $this->assertArrayHasKey("acc_add_document_btn_label", $result["acc"]);
        $this->assertEquals("Add Document", $result["acc"]["acc_add_document_btn_label"]);
    }
    
    public function testInsertLangEntriesLocalChangesAreNotOverwritten(): void
    {
        $key = "en";
        $content = ["acc#:#acc_add_document_btn_label#:#Add Document"];
        $local_changes = ["acc"=>["acc_add_document_btn_label"=>"Add Documents"]];
        
        $ilObjLanguageDBAccess = new ilObjLanguageDBAccess($this->ilDB, $key, $content, $local_changes);
        $result = $ilObjLanguageDBAccess->insertLangEntries("lang/ilias_en.lang");
        
        $this->assertEquals("Add Documents", $result["acc"]["acc_add_document_btn_label"]);
    }
    
    public function testInsertLangEntriesManipulateCalledOnce(): void
    {
        $key = "en";
        $content = ["acc#:#acc_add_document_btn_label#:#Add Document"];
        $local_changes = [];
        
        $ilObjLanguageDBAccess = new ilObjLanguageDBAccess($this->ilDB, $key, $content, $local_changes);
        
        $this->ilDB->expects($this->once())->method("manipulate");
        $result = $ilObjLanguageDBAccess->insertLangEntries("lang/ilias_en.lang");
    }
    
    public function testInsertLangEntriesManipulateCalledNeverWhenEveryContentHasALocalChange(): void
    {
        $key = "en";
        $content = ["acc#:#acc_add_document_btn_label#:#Add Document"];
        $local_changes = ["acc"=>["acc_add_document_btn_label"=>"Add Documents"]];
        
        $ilObjLanguageDBAccess = new ilObjLanguageDBAccess($this->ilDB, $key, $content, $local_changes);
        
        $this->ilDB->expects($this->never())->method("manipulate");
        $result = $ilObjLanguageDBAccess->insertLangEntries("lang/ilias_en.lang");
    }
}