<?php declare(strict_types=1);

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
use ILIAS\DI\Container;

class ilObjectTest extends TestCase
{
    private ?\ILIAS\DI\Container $dic_backup = null;
    /**
     * @var ilDBInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected ilDBInterface $db_mock;
    
    protected function setUp() : void
    {
        global $DIC;
        $this->dic_backup = is_object($DIC) ? clone $DIC : $DIC;
        
        $DIC = new Container();
        $DIC['ilias'] = $this->createMock(ILIAS::class);
        $DIC['objDefinition'] = $this->createMock(ilObjectDefinition::class);
        $DIC['ilDB'] = $this->db_mock = $this->createMock(ilDBInterface::class);
        $DIC['ilLog'] = $this->createMock(ilLogger::class);
        $DIC['ilErr'] = $this->createMock(ilErrorHandling::class);
        $DIC['tree'] = $this->createMock(ilTree::class);
        $DIC['ilAppEventHandler'] = $this->createMock(ilAppEventHandler::class);
        $DIC['ilUser'] = $this->createMock(ilObjUser::class);
    }
    
    protected function tearDown() : void
    {
        global $DIC;
        $DIC = $this->dic_backup;
    }
    
    public function testCreationDeletion() : void
    {
        $obj = new ilObject();
        $obj->setType("xxx");
        
        $this->db_mock->expects($this->any())
                      ->method('nextId')
                      ->with(ilObject::TABLE_OBJECT_DATA)
                      ->willReturnOnConsecutiveCalls(21, 22, 23);
        
        $str = '2022-04-28 08:00:00';
        $this->db_mock->expects($this->any())
                      ->method('fetchAssoc')
                      ->willReturnOnConsecutiveCalls(
                          ['last_update' => $str, 'create_date' => $str],
                          ['last_update' => $str, 'create_date' => $str],
                          ['last_update' => $str, 'create_date' => $str]
                      );
        
        $obj->create();
        $id = $obj->getId();
        $this->assertEquals(21, $id);
        
        $obj->create();
        $id = $obj->getId();
        $this->assertEquals(22, $id);
        
        $obj->create();
        $id = $obj->getId();
        $this->assertEquals(23, $id);
    }
    
    public function testSetGetLookup() : void
    {
        global $DIC;
        $ilUser = $DIC->user();
        
        $this->db_mock->expects($this->any())
                      ->method('nextId')
                      ->withConsecutive([ilObject::TABLE_OBJECT_DATA], ['object_reference'])
                      ->willReturnOnConsecutiveCalls(21, 22);
        
        $str = '2022-04-28 08:00:00';
        $this->db_mock->expects($this->any())
                      ->method('fetchAssoc')
                      ->willReturnOnConsecutiveCalls(
                          ['last_update' => $str, 'create_date' => $str],
                          ['last_update' => $str, 'create_date' => $str],
                          ['last_update' => $str, 'create_date' => $str]
                      );
        
        
        $obj = new ilObject();
        $obj->setType("xxx");                // otherwise type check will fail
        $obj->setTitle("TestObject");
        $obj->setDescription("TestDescription");
        $obj->setImportId("imp_44");
        $obj->create();
        $obj->createReference();
        $id = $obj->getId();
        $ref_id = $obj->getRefId();
        $this->assertEquals(21, $id);
        $this->assertEquals(22, $ref_id);
    
        
        // Reading
        $DIC['ilDB'] = $this->db_mock = $this->createMock(ilDBInterface::class);
        $ilDBStatement = $this->createMock(ilDBStatement::class);
        $this->db_mock->expects($this->any())
                      ->method('query')
                      ->with("SELECT obj_id, type, title, description, owner, create_date, last_update, import_id, offline" . PHP_EOL
                          . "FROM " . ilObject::TABLE_OBJECT_DATA . PHP_EOL
                          . "WHERE obj_id = " . $this->db_mock->quote(21, "integer") . PHP_EOL)
                      ->willReturn($ilDBStatement);
        
        $this->db_mock->expects($this->once())
            ->method('numRows')
            ->with($ilDBStatement)
            ->willReturn(1);
        
        $this->db_mock->expects($this->once())
            ->method('fetchAssoc')
            ->with($ilDBStatement)
            ->willReturn([
                'obj_id' => 21,
                'type' => 'xxx',
                'title' => 'TestObject',
                'description' => 'TestDescription',
                'owner' => 6,
                'create_date' => '',
                'last_update' => '',
                'import_id' => 'imp_44',
                'offline' => false,
            ]);
        
        $obj = new ilObject($id, false);
    
        $this->assertEquals(21, $obj->getId());
        $this->assertEquals('TestObject', $obj->getTitle());
        $this->assertEquals('TestDescription', $obj->getDescription());
        $this->assertEquals('imp_44', $obj->getImportId());
        $this->assertEquals(6, $obj->getOwner());
    }
}
