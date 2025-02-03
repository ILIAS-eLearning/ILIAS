<?php

namespace ILIAS\Repository\Deletion;

use PHPUnit\Framework\TestCase;

class DeletionTest extends TestCase
{
    protected array $test_tree_data = [
        1 => [ // data id
               1 => [  // tree id
                       /**
                        * 1
                        * - 2
                        *   - 4
                        * - 3
                        *   - 5
                        *   - 6
                        *     - 7
                        *     - 8
                        *       - 9
                        */
                       1 => ["tree" => 1, "child" => 1, "ref_id" => 1, "obj_id" => 1, "parent" => 0],
                       2 => ["tree" => 1, "child" => 2, "ref_id" => 2, "obj_id" => 2, "parent" => 1],
                       3 => ["tree" => 1, "child" => 3, "ref_id" => 3, "obj_id" => 3, "parent" => 1],
                       4 => ["tree" => 1, "child" => 4, "ref_id" => 4, "obj_id" => 4, "parent" => 2],
                       5 => ["tree" => 1, "child" => 5, "ref_id" => 5, "obj_id" => 5, "parent" => 3],
                       6 => ["tree" => 1, "child" => 6, "ref_id" => 6, "obj_id" => 6, "parent" => 3],
                       7 => ["tree" => 1, "child" => 7, "ref_id" => 7, "obj_id" => 7, "parent" => 6],
                       8 => ["tree" => 1, "child" => 8, "ref_id" => 8, "obj_id" => 8, "parent" => 6],
                       9 => ["tree" => 1, "child" => 9, "ref_id" => 9, "obj_id" => 9, "parent" => 8],
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
                         $subnode["tree"] = -$child;
                         $this->tree_data[-$child][$subnode["child"]] = $subnode;
                         $moved = true;
                     }
                 }
                 return $moved;
             });

        // Configure the deleteTree method
        $mock->method('deleteTree')
             ->willReturnCallback(function (array $node_data) use ($mock, $tree_id) {
                 foreach ($mock->getSubTree($node_data) as $subnode) {
                     unset($this->tree_data[$tree_id][$subnode["child"]]);
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

    public function createObjectInterfaceMock(int $ref_id, array $failing_obj_ids = []): ObjectInterface
    {
        // Create the mock object
        $mock = $this->createMock(ObjectInterface::class);

        // Configure the getInstanceByRefId method to return the mock itself or null
        $mock->method('getInstanceByRefId')
             ->willReturnCallback(function (int $ref_id) use ($mock, $failing_obj_ids) {
                 return $this->createObjectInterfaceMock($ref_id, $failing_obj_ids);
             });

        // Configure the delete method
        $mock->method('delete')
             ->willReturnCallback(function () use ($ref_id, $failing_obj_ids) {
                 if (in_array($ref_id, $failing_obj_ids)) {
                     throw new \Exception("Failed to do something");
                 }
                 $this->deleted_ref_ids[] = $ref_id;
             });

        // Configure the getId method
        $mock->method('getId')
            ->willReturnCallback(function () use ($ref_id) {
                return $ref_id;
            });

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

    protected function log(string $message): void
    {
        $log = false;
        if ($log) {
            echo $message . PHP_EOL;
        }
    }

    public function createEventInterfaceMock(): EventInterface
    {
        // Create the mock object
        $mock = $this->createMock(EventInterface::class);

        // Configure the beforeMoveToTrash method
        $mock->method('beforeMoveToTrash')
             ->willReturnCallback(function (int $ref_id, array $subnodes) {
                 $this->log('beforeMoveToTrash: ' . $ref_id);
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
                 $this->log('beforeSubtreeRemoval: ' . $obj_id);
                 // No return value as the method is void
             });

        // Configure the beforeObjectRemoval method
        $mock->method('beforeObjectRemoval')
             ->willReturnCallback(function (int $obj_id, int $ref_id, string $type, string $title) {
                 $this->log('beforeObjectRemoval: ' . $obj_id);
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
                 $this->log('afterTreeDeletion: ' . $tree_id . ", " . $child);
                 // No return value as the method is void
             });

        return $mock;
    }

    public function initDeletion(
        ?TreeInterface $tree_mock = null,
        bool $trash_enabled = true,
        bool $access_given = true,
        array $failing_obj_ids = []
    ): Deletion {
        return new Deletion(
            $tree_mock,
            $this->createPermissionInterfaceMock($access_given),
            $this->createEventInterfaceMock(),
            $this->createObjectInterfaceMock(0, $failing_obj_ids),
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

    /**
     * - trash enabled
     * - access given
     * - delete 2
     * - check if 2 is in trash (isDeleted)
     * - check if -2 is trashed subtree of 1
     * - check if nothing has been finally deleted
     */
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

        // nothing has been finally deleted
        $this->assertEquals([], $this->deleted_ref_ids);
    }

    /**
     * - trash enabled
     * - access given
     * - delete 2
     * - delete 3
     * - check if 2,3 are in trash (isDeleted)
     * - check if -2,-3 are trashed subtrees of 1
     * - check if nothing has been finally deleted
     */
    public function testDeletionDeleteWithTrashMultiple(): void
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
        $deletion->deleteObjectsByRefIds([3]);
        $this->assertEquals(true, $tree_mock->isDeleted(2));
        $this->assertEquals(true, $tree_mock->isDeleted(3));

        $this->assertEquals([-2, -3], $tree_mock->getTrashedSubtrees(1));

        // nothing has been finally deleted
        $this->assertEquals([], $this->deleted_ref_ids);
    }

    /**
     * - trash disabled
     * - access given
     * - delete 2
     * - check if 2 is NOT in trash (isDeleted)
     * - check if 1 does not have trashed subtrees
     * - check if 2,4 are finally deleted
     */
    public function testDeletionDeleteWithoutTrash(): void
    {
        $this->loadTreeData(1);
        $this->resetDeleteRefIds();
        $tree_mock = $this->createTreeInterfaceMock(1);
        $deletion = $this->initDeletion(
            $tree_mock,
            false,
            true
        );

        // delete tree 2
        $deletion->deleteObjectsByRefIds([2]);

        // tree is not in trash
        $this->assertEquals(false, $tree_mock->isDeleted(2));

        // no trashed subtrees
        $this->assertEquals([], $tree_mock->getTrashedSubtrees(1));

        // 2 has been deleted
        $this->assertEquals([2,4], $this->deleted_ref_ids);
    }

    /**
     * - trash enabled
     * - access given
     * - delete 2
     * - removeFromSystem 2
     * - check if 2 is NOT in trash (isDeleted)
     * - check if 1 does not have trashed subtrees
     * - check if 2,4 are finally deleted
     */
    public function testDeletionDeleteRemoveFromSystem(): void
    {
        $this->loadTreeData(1);
        $this->resetDeleteRefIds();
        $tree_mock = $this->createTreeInterfaceMock(1);
        $deletion = $this->initDeletion(
            $tree_mock,
            true,
            true
        );

        // delete tree 2
        $deletion->deleteObjectsByRefIds([2]);

        $deletion->removeObjectsFromSystemByRefIds([2]);

        // 2 is not in trash
        $this->assertEquals(false, $tree_mock->isDeleted(2));

        // no left trashed subtrees
        $this->assertEquals([], $tree_mock->getTrashedSubtrees(1));

        // 2,4 have been deleted
        $this->assertEquals([2,4], $this->deleted_ref_ids);
    }

    /**
     * - trash enabled
     * - access given
     * - delete 6
     * - delete 3
     * - removeFromSystem 3
     * - check if 3,5,6,7,8,9 are finally deleted
     */
    public function testDeletionRemoveFromSystemMultiple(): void
    {
        $this->log(PHP_EOL . "---testDeletionDeleteRemoveFromSystemMultiple");
        $this->loadTreeData(1);
        $this->resetDeleteRefIds();
        $tree_mock = $this->createTreeInterfaceMock(1);
        $deletion = $this->initDeletion(
            $tree_mock,
            true,
            true
        );

        // delete tree 2
        $this->log("---call: deleteObjectsByRefIds 6");
        $deletion->deleteObjectsByRefIds([6]);
        $this->log("---call: deleteObjectsByRefIds 3");
        $deletion->deleteObjectsByRefIds([3]);

        $this->log("---call: removeObjectsFromSystemByRefIds 3");
        $deletion->removeObjectsFromSystemByRefIds([3]);

        // 3,5,6,7,8 are not in trash
        $this->assertEquals(false, $tree_mock->isDeleted(3));
        $this->assertEquals(false, $tree_mock->isDeleted(5));
        $this->assertEquals(false, $tree_mock->isDeleted(6));
        $this->assertEquals(false, $tree_mock->isDeleted(7));
        $this->assertEquals(false, $tree_mock->isDeleted(8));
        $this->assertEquals(false, $tree_mock->isDeleted(9));

        // no left trashed subtrees
        $this->assertEquals([], $tree_mock->getTrashedSubtrees(1));
        $this->assertEquals([], $tree_mock->getTrashedSubtrees(2));

        // 3,5,6,7,8 have been deleted
        $this->assertEqualsCanonicalizing([3,5,6,7,8,9], $this->deleted_ref_ids);
        $this->log("---END---testDeletionDeleteRemoveFromSystemMultiple");
    }

    /**
     * - trash enabled
     * - access given
     * - delete 8
     * - delete 3
     * - removeFromSystem 3
     * - check if 3,5,6,7,8,9 are finally deleted
     */
    public function testDeletionRemoveFromSystemDeepSubtree(): void
    {
        $this->log(PHP_EOL . "---testDeletionDeleteRemoveFromSystemMultiple");
        $this->loadTreeData(1);
        $this->resetDeleteRefIds();
        $tree_mock = $this->createTreeInterfaceMock(1);
        $deletion = $this->initDeletion(
            $tree_mock,
            true,
            true
        );

        // delete tree 2
        $this->log("---call: deleteObjectsByRefIds 8");
        $deletion->deleteObjectsByRefIds([8]);
        $this->log("---call: deleteObjectsByRefIds 3");
        $deletion->deleteObjectsByRefIds([3]);

        $this->log("---call: removeObjectsFromSystemByRefIds 3");
        $deletion->removeObjectsFromSystemByRefIds([3]);

        // 3,5,6,7,8 are not in trash
        $this->assertEquals(false, $tree_mock->isDeleted(3));
        $this->assertEquals(false, $tree_mock->isDeleted(5));
        $this->assertEquals(false, $tree_mock->isDeleted(6));
        $this->assertEquals(false, $tree_mock->isDeleted(7));
        $this->assertEquals(false, $tree_mock->isDeleted(8));
        $this->assertEquals(false, $tree_mock->isDeleted(9));

        // no left trashed subtrees
        $this->assertEquals([], $tree_mock->getTrashedSubtrees(1));
        $this->assertEquals([], $tree_mock->getTrashedSubtrees(2));

        // 3,5,6,7,8,9 have been deleted
        $this->assertEqualsCanonicalizing([3,5,6,7,8,9], $this->deleted_ref_ids);
        $this->log("---END---testDeletionDeleteRemoveFromSystemMultiple");
    }

    /**
     * - trash enabled
     * - access given
     * - delete 9
     * - delete 8
     * - delete 3
     * - removeFromSystem 3
     * - check if 3,5,6,7,8,9 are finally deleted
     */
    public function testDeletionRemoveFromSystemTrashInTrash(): void
    {
        $this->log(PHP_EOL . "---testDeletionRemoveFromSystemTrashInTrash");
        $this->loadTreeData(1);
        $this->resetDeleteRefIds();
        $tree_mock = $this->createTreeInterfaceMock(1);
        $deletion = $this->initDeletion(
            $tree_mock,
            true,
            true
        );

        $this->log("---call: deleteObjectsByRefIds 9");
        $deletion->deleteObjectsByRefIds([9]);
        $this->log("---call: deleteObjectsByRefIds 8");
        $deletion->deleteObjectsByRefIds([8]);
        $this->log("---call: deleteObjectsByRefIds 3");
        $deletion->deleteObjectsByRefIds([3]);

        $this->log("---call: removeObjectsFromSystemByRefIds 3");
        $deletion->removeObjectsFromSystemByRefIds([3]);

        // 3,5,6,7,8 are not in trash
        $this->assertEquals(false, $tree_mock->isDeleted(3));
        $this->assertEquals(false, $tree_mock->isDeleted(5));
        $this->assertEquals(false, $tree_mock->isDeleted(6));
        $this->assertEquals(false, $tree_mock->isDeleted(7));
        $this->assertEquals(false, $tree_mock->isDeleted(8));
        $this->assertEquals(false, $tree_mock->isDeleted(9));

        // no left trashed subtrees
        $this->assertEquals([], $tree_mock->getTrashedSubtrees(1));
        $this->assertEquals([], $tree_mock->getTrashedSubtrees(2));

        // 3,5,6,7,8,9 have been deleted
        $this->assertEqualsCanonicalizing([3,5,6,7,8,9], $this->deleted_ref_ids);
        $this->log("---END---testDeletionRemoveFromSystemTrashInTrash");
    }

    /**
     * - trash enabled
     * - access given
     * - delete 8
     * - delete 3
     * - removeFromSystem 3 (8 fails)
     * - check if 3,5,6,7,9 are finally deleted
     */
    public function testDeletionRemoveFromSystemFailingObject(): void
    {
        $this->log(PHP_EOL . "---testDeletionRemoveFromSystemFailingObject");
        $this->loadTreeData(1);
        $this->resetDeleteRefIds();
        $tree_mock = $this->createTreeInterfaceMock(1);
        $deletion = $this->initDeletion(
            $tree_mock,
            true,
            true,
            [8]
        );

        $this->log("---call: deleteObjectsByRefIds 9");
        $deletion->deleteObjectsByRefIds([9]);
        $this->log("---call: deleteObjectsByRefIds 8");
        $deletion->deleteObjectsByRefIds([8]);
        $this->log("---call: deleteObjectsByRefIds 3");
        $deletion->deleteObjectsByRefIds([3]);

        $this->log("---call: removeObjectsFromSystemByRefIds 3");
        $deletion->removeObjectsFromSystemByRefIds([3]);

        // 3,5,6,7,8 are not in trash
        $this->assertEquals(false, $tree_mock->isDeleted(3));
        $this->assertEquals(false, $tree_mock->isDeleted(5));
        $this->assertEquals(false, $tree_mock->isDeleted(6));
        $this->assertEquals(false, $tree_mock->isDeleted(7));
        $this->assertEquals(false, $tree_mock->isDeleted(8));
        $this->assertEquals(false, $tree_mock->isDeleted(9));

        // no left trashed subtrees
        $this->assertEquals([], $tree_mock->getTrashedSubtrees(1));
        $this->assertEquals([], $tree_mock->getTrashedSubtrees(2));

        // 3,5,6,7,8,9 have been deleted
        $this->assertEqualsCanonicalizing([3,5,6,7,9], $this->deleted_ref_ids);
        $this->log("---END---testDeletionRemoveFromSystemFailingObject");
    }

    /**
     * - trash enabled
     * - access given
     * - delete 9
     * - delete 8
     * - delete 6
     * - delete 3
     * - removeFromSystem 3
     * - check if 3,5,6,7,9 are finally deleted
     */
    public function testDeletionRemoveFromSystemTrashInTrashInTrash(): void
    {
        $this->log(PHP_EOL . "---testDeletionRemoveFromSystemTrashInTrashInTrash");
        $this->loadTreeData(1);
        $this->resetDeleteRefIds();
        $tree_mock = $this->createTreeInterfaceMock(1);
        $deletion = $this->initDeletion(
            $tree_mock,
            true,
            true,
            [8]
        );

        $this->log("---call: deleteObjectsByRefIds 9");
        $deletion->deleteObjectsByRefIds([9]);
        $this->log("---call: deleteObjectsByRefIds 8");
        $deletion->deleteObjectsByRefIds([8]);
        $this->log("---call: deleteObjectsByRefIds 6");
        $deletion->deleteObjectsByRefIds([6]);
        $this->log("---call: deleteObjectsByRefIds 3");
        $deletion->deleteObjectsByRefIds([3]);

        $this->log("---call: removeObjectsFromSystemByRefIds 3");
        $deletion->removeObjectsFromSystemByRefIds([3]);

        // 3,5,6,7,8 are not in trash
        $this->assertEquals(false, $tree_mock->isDeleted(3));
        $this->assertEquals(false, $tree_mock->isDeleted(5));
        $this->assertEquals(false, $tree_mock->isDeleted(6));
        $this->assertEquals(false, $tree_mock->isDeleted(7));
        $this->assertEquals(false, $tree_mock->isDeleted(8));
        $this->assertEquals(false, $tree_mock->isDeleted(9));

        // no left trashed subtrees
        $this->assertEquals([], $tree_mock->getTrashedSubtrees(1));
        $this->assertEquals([], $tree_mock->getTrashedSubtrees(2));

        // 3,5,6,7,8,9 have been deleted
        $this->assertEqualsCanonicalizing([3,5,6,7,9], $this->deleted_ref_ids);
        $this->log("---END---testDeletionRemoveFromSystemTrashInTrashInTrash");
    }

    public function testDeletionNoDeletePermission(): void
    {
        $this->log(PHP_EOL . "---testDeletionNoDeletePermission");
        $this->loadTreeData(1);
        $this->resetDeleteRefIds();
        $tree_mock = $this->createTreeInterfaceMock(1);
        $deletion = $this->initDeletion(
            $tree_mock,
            true,
            false,
            [8]
        );

        $this->log("---call: deleteObjectsByRefIds 9");
        $this->expectException(MissingPermissionException::class);
        $deletion->deleteObjectsByRefIds([9]);

        // 9 not in trash
        $this->assertEquals(false, $tree_mock->isDeleted(9));

        // no trashed subtrees
        $this->assertEquals([], $tree_mock->getTrashedSubtrees(1));
        $this->assertEquals([], $tree_mock->getTrashedSubtrees(2));

        // nothing deleted
        $this->assertEqualsCanonicalizing([], $this->deleted_ref_ids);
        $this->log("---END---testDeletionNoDeletePermission");
    }

    /**
     * - trash disabled
     * - access given
     * - delete 9
     * - delete 8
     * - delete 6
     * - delete 3
     * - check if 3,5,6,7,9 are finally deleted
     */
    public function testDeletionTrashDisabledTrashInTrashInTrash(): void
    {
        $this->log(PHP_EOL . "---testDeletionTrashDisabledTrashInTrashInTrash");
        $this->loadTreeData(1);
        $this->resetDeleteRefIds();
        $tree_mock = $this->createTreeInterfaceMock(1);
        $deletion = $this->initDeletion(
            $tree_mock,
            false,
            true,
            []
        );

        $this->log("---call: deleteObjectsByRefIds 9");
        $deletion->deleteObjectsByRefIds([9]);
        $this->log("---call: deleteObjectsByRefIds 8");
        $deletion->deleteObjectsByRefIds([8]);
        $this->log("---call: deleteObjectsByRefIds 6");
        $deletion->deleteObjectsByRefIds([6]);
        $this->log("---call: deleteObjectsByRefIds 3");
        $deletion->deleteObjectsByRefIds([3]);

        // 3,5,6,7,8,9 are not in trash
        $this->assertEquals(false, $tree_mock->isDeleted(3));
        $this->assertEquals(false, $tree_mock->isDeleted(5));
        $this->assertEquals(false, $tree_mock->isDeleted(6));
        $this->assertEquals(false, $tree_mock->isDeleted(7));
        $this->assertEquals(false, $tree_mock->isDeleted(8));
        $this->assertEquals(false, $tree_mock->isDeleted(9));

        // no left trashed subtrees
        $this->assertEquals([], $tree_mock->getTrashedSubtrees(1));
        $this->assertEquals([], $tree_mock->getTrashedSubtrees(2));

        // 3,5,6,7,8,9 have been deleted
        $this->assertEqualsCanonicalizing([3,5,6,7,8,9], $this->deleted_ref_ids);
        $this->log("---END---testDeletionTrashDisabledTrashInTrashInTrash");
    }

}
