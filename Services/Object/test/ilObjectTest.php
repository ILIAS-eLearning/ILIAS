<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */
/**
 * @group needsInstalledILIAS
 */
class ilObjectTest extends PHPUnit_Framework_TestCase
{
    protected $backupGlobals = false;

    protected function setUp()
    {
        include_once("./Services/PHPUnit/classes/class.ilUnitUtil.php");
        ilUnitUtil::performInitialisation();
    }

    /**
     * @group IL_Init
     */
    public function testCreationDeletion()
    {
        $obj = new ilObject();
        $obj->setType("xxx");
        $obj->create();
        $id = $obj->getId();
        
        $obj2 = new ilObject();
        $obj2->setType("xxx");
        $obj2->create();
        $id2 = $obj2->getId();
        
        if ($id2 == ($id + 1)) {
            $value.= "create1-";
        }
        
        if (ilObject::_exists($id)) {
            $value.= "create2-";
        }
        
        $obj->delete();
        $obj2->delete();
        
        if (!ilObject::_exists($id)) {
            $value.= "create3-";
        }

        
        $this->assertEquals("create1-create2-create3-", $value);
    }

    /**
     * @group IL_Init
     */
    public function testSetGetLookup()
    {
        global $DIC;
        $ilUser = $DIC->user();


        $obj = new ilObject();
        $obj->setType("");				// otherwise type check will fail
        $obj->setTitle("TestObject");
        $obj->setDescription("TestDescription");
        $obj->setImportId("imp_44");
        $obj->create();
        $obj->createReference();
        $id = $obj->getId();
        $ref_id = $obj->getRefId();
        $obj = new ilObject($id, false);

        if ($obj->getType() == "") {
            $value.= "sg1-";
        }
        if ($obj->getTitle() == "TestObject") {
            $value.= "sg2-";
        }
        if ($obj->getDescription() == "TestDescription") {
            $value.= "sg3-";
        }
        if ($obj->getImportId() == "imp_44") {
            $value.= "sg4-";
        }
        if ($obj->getOwner() == $ilUser->getId()) {
            $value.= "sg5-";
        }
        
        $obj = new ilObject($ref_id);
        if ($obj->getTitle() == "TestObject") {
            $value.= "sg6-";
        }
        
        if ($obj->getCreateDate() == ($lu = $obj->getLastUpdateDate())) {
            $value.= "sg7-";
        }
        $obj->setTitle("TestObject2");
        sleep(2);			// we want a different date here...
        $obj->update();
        
        $obj = new ilObject($ref_id);
        if ($lu != ($lu2 = $obj->getLastUpdateDate())) {
            $value.= "up1-";
        }
        if ($obj->getTitle() == "TestObject2") {
            $value.= "up2-";
        }

        if ($id == ilObject::_lookupObjIdByImportId("imp_44")) {
            $value.= "lu1-";
        }
        
        if ($ilUser->getFullname() == ilObject::_lookupOwnerName(ilObject::_lookupOwner($id))) {
            $value.= "lu2-";
        }
        
        if (ilObject::_lookupTitle($id) == "TestObject2") {
            $value.= "lu3-";
        }
        if (ilObject::_lookupDescription($id) == "TestDescription") {
            $value.= "lu4-";
        }
        if (ilObject::_lookupLastUpdate($id) == $lu2) {
            $value.= "lu5-";
        }
        if (ilObject::_lookupObjId($ref_id) == $id) {
            $value.= "lu6-";
        }
        if (ilObject::_lookupType($id) == "") {
            $value.= "lu7-";
        }
        if (ilObject::_lookupObjectId($ref_id) == $id) {
            $value.= "lu8-";
        }
        $ar = ilObject::_getAllReferences($id);
        if (is_array($ar) && count($ar) == 1 &&  $ar[$ref_id] == $ref_id) {
            $value.= "lu9-";
        }
        
        $ids = ilObject::_getIdsForTitle("TestObject2");
        foreach ($ids as $i) {
            if ($i == $id) {
                $value.= "lu10-";
            }
        }

        $obs = ilObject::_getObjectsByType("usr");
        foreach ($obs as $ob) {
            if ($ob["obj_id"] == $ilUser->getId()) {
                $value.= "lu11-";
            }
        }
        
        $d1 = ilObject::_lookupDeletedDate($ref_id);
        ilObject::_setDeletedDate($ref_id);
        $d2 = ilObject::_lookupDeletedDate($ref_id);
        ilObject::_resetDeletedDate($ref_id);
        $d3 = ilObject::_lookupDeletedDate($ref_id);
        if ($d1 != $d2 && $d1 == $d3 && $d3 == null) {
            $value.= "dd1-";
        }
        
        $obj->delete();
        
        $this->assertEquals("sg1-sg2-sg3-sg4-sg5-sg6-sg7-up1-up2-" .
            "lu1-lu2-lu3-lu4-lu5-lu6-lu7-lu8-lu9-lu10-lu11-dd1-", $value);
    }

    /**
     * @group IL_Init
     */
    public function testTreeTrash()
    {
        global $DIC;
        $tree = $DIC->repositoryTree();
        
        $obj = new ilObject();
        $obj->setType("xxx");
        $obj->setTitle("TestObject");
        $obj->setDescription("TestDescription");
        $obj->setImportId("imp_44");
        $obj->create();
        $obj->createReference();
        $id = $obj->getId();
        $ref_id = $obj->getRefId();
        $obj = new ilObject($ref_id);
        
        $obj->putInTree(ROOT_FOLDER_ID);
        $obj->setPermissions(ROOT_FOLDER_ID);
        if ($tree->isInTree($ref_id)) {
            $value.= "tree1-";
        }
        if (ilObject::_hasUntrashedReference($id)) {
            $value.= "tree2-";
        }
        
        // isSaved() uses internal cache!
        $tree->useCache(false);
        
        $tree->saveSubTree($ref_id, true);
        if ($tree->isDeleted($ref_id)) {
            $value.= "tree3-";
        }
        if ($tree->isSaved($ref_id)) {
            $value.= "tree4-";
        }
        if (ilObject::_isInTrash($ref_id)) {
            $value.= "tree5-";
        }
        if (!ilObject::_hasUntrashedReference($id)) {
            $value.= "tree6-";
        }
        
        $saved_tree = new ilTree(-(int) $ref_id);
        $node_data = $saved_tree->getNodeData($ref_id);
        $saved_tree->deleteTree($node_data);

        if (!ilObject::_isInTrash($ref_id)) {
            $value.= "tree7-";
        }
        
        $obs = ilUtil::_getObjectsByOperations("cat", "read");
        foreach ($obs as $ob) {
            if (ilObject::_lookupType(ilObject::_lookupObjId($ob)) != "cat") {
                $value.= "nocat-";
            }
        }

        $obj->delete();
        
        $this->assertEquals("tree1-tree2-tree3-tree4-tree5-tree6-tree7-", $value);
    }
    
    /**
     * test object reference queries
     * @group IL_Init
     */
    public function testObjectReference()
    {
        include_once './Services/Object/classes/class.ilObject.php';
        
        $ref_ids = ilObject::_getAllReferences(1);
        $bool = ilObject::_setDeletedDate(1);
        $bool = ilObject::_resetDeletedDate(1);
        $date = ilObject::_lookupDeletedDate(1);
        
        $this->assertEquals($date, null);
    }
}
