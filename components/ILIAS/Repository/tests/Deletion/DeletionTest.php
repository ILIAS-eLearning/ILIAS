<?php

namespace ILIAS\Repository\Deletion;

use PHPUnit\Framework\TestCase;

class DeletionTest extends TestCase
{
    protected array $test_tree_data = [
        1 => [ // data id
               1 => [  // tree id
                       1 => ["child" => 1, "parent" => 0],
                       2 => ["child" => 2, "parent" => 1],
                       3 => ["child" => 3, "parent" => 1],
                       4 => ["child" => 4, "parent" => 2],
                       5 => ["child" => 5, "parent" => 3],
                       6 => ["child" => 6, "parent" => 3],
               ]
        ]
    ];

    protected array $tree_data = [];

    protected array $deleted_ref_ids = [];

    protected function setUp(): void
    {
        parent::setUp();
    }

    public function loadTreeData(int $data_id): void
    {
        $this->tree_data = $this->test_tree_data[$data_id];
    }

    public function resetDeleteRefIds(): void
    {
        $this->deleted_ref_ids = [];
    }

    protected function addChilds($id, &$childs, $data): void
    {
        foreach ($data as $k => $c) {
            if ($c["parent"] == $id) {
                $childs[$k] = $c;
                $this->addChilds($c["child"], $childs, $data);
            }
        }
    }

    public function createTreeInterfaceMock(int $tree_id): TreeInterface
    {
        $mock = $this->createMock(TreeInterface::class);

        // isDeleted acts on all data entries of all trees
        $mock->method('isDeleted')
             ->willReturnCallback(function (int $child) {
                 foreach ($this->tree_data as $tree_id => $entries) {
                     if (isset($entries[$child])) {
                         return ($tree_id < 0);
                     }
                 }
                 return false;
             });

        // getNodeData acts only on tree $tree_id
        $mock->method('getNodeData')
             ->willReturnCallback(function (int $child) use ($tree_id) {
                 return $this->tree_data[$tree_id][$child];
             });

        $mock->method('useCache')
             ->willReturnCallback(function (bool $a_use) {
             });

        // getSubTree acts only on tree $tree_id
        $mock->method('getSubTree')
             ->willReturnCallback(function (array $node) use ($tree_id) {
                 $childs[$node["child"]] = $node;
                 $data = $this->tree_data[$tree_id];
                 $this->addChilds($node['child'], $childs, $data);
                 return $childs;
             });

        // getDeletedTreeNodeIds acts on all data entries of all trees
        $mock->method('getDeletedTreeNodeIds')
             ->willReturnCallback(function (array $ids) use ($mock) {
                 $deleted_ids = [];
                 foreach ($ids as $id) {
                     if ($mock->isDeleted($id)) {
                         $deleted_ids[] = $id;
                     }
                 }
                 return $deleted_ids;
             });

        // getTree acts on all data
        $mock->method('getTree')
             ->willReturnCallback(function (int $tree_id) {
                 return $this->createTreeInterfaceMock($tree_id);
             });

        // getTrashTree acts on all data
        $mock->method('getTrashTree')
             ->willReturnCallback(function (int $child) use ($mock) {
                 foreach ($this->tree_data as $tree_id => $entries) {
                     if (isset($entries[$child]) && $tree_id < 0) {
                         return $mock->getTree($tree_id);
                     }
                 }
             });

        // Configure the deleteTree method
        $mock->method('moveToTrash')
             ->willReturnCallback(function (int $child) use ($mock, $tree_id) {
                 $moved = false;
                 foreach ($mock->getSubTree($mock->getNodeData($child)) as $subnode) {
                     if (isset($this->tree_data[$tree_id][$subnode["child"]])) {
                         unset($this->tree_data[$tree_id][$subnode["child"]]);
                         $this->tree_data[-$child][$subnode["child"]] = $subnode;
                         $moved = true;
                     }
                 }
                 return $moved;
             });

        // Configure the deleteTree method
        $mock->method('deleteTree')
             ->willReturnCallback(function (array $node_data) use ($mock, $tree_id) {
                 if ($mock->isDeleted($node_data["child"])) {
                     foreach ($mock->getSubTree($node_data) as $subnode) {
                         unset($this->tree_data[$tree_id][$subnode["child"]]);
                     }
                 }
             });

        // Configure the getTrashedSubtrees method
        $mock->method('getTrashedSubtrees')
             ->willReturnCallback(function (int $ref_id) {
                 $tree_ids = [];
                 foreach ($this->tree_data as $tree_id => $entries) {
                     foreach ($entries as $entry) {
                         if ($entry["parent"] == $ref_id) {
                             if ($tree_id < 0 && $tree_id === -1 * $entry["child"]) {
                                 $tree_ids[] = $tree_id;
                             }
                         }
                     }
                 }
                 return $tree_ids;
             });

        return $mock;
    }

    public function createPermissionInterfaceMock(
        bool $access_given
    ): PermissionInterface {
        // Create the mock object
        $mock = $this->createMock(PermissionInterface::class);

        // Configure the checkAccess method to return true or false based on operation and ref_id
        $mock->method('checkAccess')
             ->willReturnCallback(function (string $operation, int $ref_id) use ($access_given) {
                 return $access_given;
             });

        // Configure the revokePermission method
        $mock->method('revokePermission')
             ->willReturnCallback(function (int $ref_id) {
             });

        // Configure the getRefIdsWithoutDeletePermission method to return a filtered list of ids
        $mock->method('getRefIdsWithoutDeletePermission')
             ->willReturnCallback(function (array $ids) use ($access_given) {
                 if ($access_given) {
                     return [];
                 }
                 return $ids;
             });

        return $mock;
    }

    public function createObjectInterfaceMock(int $ref_id): ObjectInterface
    {
        // Create the mock object
        $mock = $this->createMock(ObjectInterface::class);

        // Configure the getInstanceByRefId method to return the mock itself or null
        $mock->method('getInstanceByRefId')
             ->willReturnCallback(function (int $ref_id) use ($mock) {
                 return $this->createObjectInterfaceMock($ref_id);
             });

        // Configure the delete method
        $mock->method('delete')
             ->willReturnCallback(function () use ($ref_id) {
                 $this->deleted_ref_ids[] = $ref_id;
             });

        // Configure the getId method
        $mock->method('getId')
             ->willReturn(42);

        // Configure the getType method
        $mock->method('getType')
             ->willReturn('tst');

        // Configure the getTitle method
        $mock->method('getTitle')
             ->willReturn('Sample Object Title');

        // Configure the getRefId method
        $mock->method('getRefId')
             ->willReturnCallback(function () use ($ref_id) {
                 return $ref_id;
             });

        return $mock;
    }

    public function createEventInterfaceMock(): EventInterface
    {
        // Create the mock object
        $mock = $this->createMock(EventInterface::class);

        // Configure the beforeMoveToTrash method
        $mock->method('beforeMoveToTrash')
             ->willReturnCallback(function (int $ref_id, array $subnodes) {
                 // No return value as the method is void
             });

        // Configure the afterMoveToTrash method
        $mock->method('afterMoveToTrash')
             ->willReturnCallback(function (int $ref_id, int $old_parent_ref_id) {
                 // No return value as the method is void
             });

        // Configure the beforeSubtreeRemoval method
        $mock->method('beforeSubtreeRemoval')
             ->willReturnCallback(function (int $obj_id) {
                 // No return value as the method is void
             });

        // Configure the beforeObjectRemoval method
        $mock->method('beforeObjectRemoval')
             ->willReturnCallback(function (int $obj_id, int $ref_id, string $type, string $title) {
                 // No return value as the method is void
             });

        // Configure the afterObjectRemoval method
        $mock->method('afterObjectRemoval')
             ->willReturnCallback(function (int $obj_id, int $ref_id, string $type, int $old_parent_ref_id) {
                 // No return value as the method is void
             });

        // Configure the afterTreeDeletion method
        $mock->method('afterTreeDeletion')
             ->willReturnCallback(function (int $tree_id, int $child) {
                 // No return value as the method is void
             });

        return $mock;
    }

    public function initDeletion(
        TreeInterface $tree_mock = null,
        bool $trash_enabled = true,
        bool $access_given = true,
    ): Deletion {
        return new Deletion(
            $tree_mock,
            $this->createPermissionInterfaceMock($access_given),
            $this->createEventInterfaceMock(),
            $this->createObjectInterfaceMock(0),
            $trash_enabled
        );
    }
    protected function tearDown(): void
    {
    }

    public function testTreeMockTest(): void
    {
        $this->loadTreeData(1);
        $tree_mock = $this->createTreeInterfaceMock(1);

        $node1 = $tree_mock->getNodeData(1);
        $this->assertEquals(0, $node1["parent"]);

        $tree_mock->moveToTrash(2);
        $this->assertEquals(true, $tree_mock->isDeleted(2));
    }

    public function testDeletionInstantiation(): void
    {
        $this->loadTreeData(1);
        $this->resetDeleteRefIds();
        $tree_mock = $this->createTreeInterfaceMock(1);
        $deletion = $this->initDeletion(
            $tree_mock,
            true,
            true
        );

        static::assertInstanceOf(Deletion::class, $deletion);
    }

    public function testDeletionDeleteWithTrash(): void
    {
        $this->loadTreeData(1);
        $this->resetDeleteRefIds();
        $tree_mock = $this->createTreeInterfaceMock(1);
        $deletion = $this->initDeletion(
            $tree_mock,
            true,
            true
        );

        $deletion->deleteObjectsByRefIds([2]);
        $this->assertEquals(true, $tree_mock->isDeleted(2));

        $this->assertEquals([-2], $tree_mock->getTrashedSubtrees(1));
    }

}
