<?php
/******************************************************************************
 * An entity component framework for PHP.
 *
 * Copyright (c) 2017 Richard Klees <richard.klees@concepts-and-training.de>
 *
 * This software is licensed under GPLv3. You should have received a copy of
 * the license along with the code.
 */

use CaT\Ente\ILIAS\SeparatedUnboundProvider;
use CaT\Ente\ILIAS\UnboundProvider;
use CaT\Ente\Provider;
use CaT\Ente\ILIAS\ilProviderDB;
use CaT\Ente\Simple\AttachString;
use CaT\Ente\Simple\AttachInt;

if (!class_exists("ilObject")) {
    require_once(__DIR__."/ilObject.php");
}

if (!interface_exists("ilDBInterface")) {
    require_once(__DIR__."/ilDBInterface.php");
}

if (!interface_exists("ilTree")) {
    require_once(__DIR__."/ilTree.php");
}

if (!interface_exists("ilObjectDataCache")) {
    require_once(__DIR__."/ilObjectDataCache.php");
}

class Test_ilProviderDB extends ilProviderDB {
    public $object_ref = [];
    protected function buildObjectByRefId($ref_id) {
        assert(isset($this->object_ref[$ref_id]));
        return $this->object_ref[$ref_id];
    }
    public $object_obj = [];
    protected function buildObjectByObjId($obj_id) {
        assert(isset($this->object_obj[$obj_id]));
        return $this->object_obj[$obj_id];
    }
    public $reference_ids = [];
    protected function getAllReferenceIdsFor($obj_id) {
        assert(isset($this->reference_ids[$obj_id]));
        return $this->reference_ids[$obj_id];
    }
}

class ILIAS_ilProviderDBTest extends PHPUnit_Framework_TestCase {
    protected function il_db_mock() {
        return $this->createMock(\ilDBInterface::class);
    }

    public function il_tree_mock() {
        return $this
            ->getMockBuilder(\ilTree::class)
            ->setMethods(["getSubTreeIds", "getNodePath"])
            ->getMock();
    }

    public function il_object_data_cache_mock() {
        return $this
            ->getMockBuilder(\ilObjectDataCache::class)
            ->setMethods(["preloadReferenceCache", "lookupObjId"])
            ->getMock();
    }

    public function test_createTables() {
        $il_db = $this->il_db_mock();

        $provider_table =
            [ "id" => ["type" => "integer", "length" => 4, "notnull" => true]
            , "owner" => ["type" => "integer", "length" => 4, "notnull" => true]
            , "object_type" => ["type" => "text", "length" => 4, "notnull" => true]
            , "class_name" => ["type" => "text", "length" => ilProviderDB::CLASS_NAME_LENGTH, "notnull" => true]
            , "include_path" => ["type" => "text", "length" => ilProviderDB::PATH_LENGTH, "notnull" => true]
            ];
        $component_table =
            [ "id" => ["type" => "integer", "length" => 4, "notnull" => true]
            , "component_type" => ["type" => "text", "length" => ilProviderDB::CLASS_NAME_LENGTH, "notnull" => true]
            ];

        $il_db
            ->expects($this->exactly(2))
            ->method("createTable")
            ->withConsecutive(
                [ilProviderDB::PROVIDER_TABLE, $provider_table],
                [ilProviderDB::COMPONENT_TABLE, $component_table]);

        $il_db
            ->expects($this->exactly(2))
            ->method("tableExists")
            ->withConsecutive([ilProviderDB::PROVIDER_TABLE], [ilProviderDB::COMPONENT_TABLE])
            ->will($this->onConsecutiveCalls(false, false));

        $il_db
            ->expects($this->exactly(2))
            ->method("addPrimaryKey")
            ->withConsecutive(
                [ilProviderDB::PROVIDER_TABLE, ["id"]],
                [ilProviderDB::COMPONENT_TABLE, ["id", "component_type"]]);

        $il_db
            ->expects($this->once())
            ->method("createSequence")
            ->with(ilProviderDB::PROVIDER_TABLE);

        $il_db
            ->expects($this->once())
            ->method("tableColumnExists")
            ->with(ilProviderDB::PROVIDER_TABLE, "shared")
            ->willReturn(false);

        $il_db
            ->expects($this->once())
            ->method("addTableColumn")
            ->with(ilProviderDB::PROVIDER_TABLE, "shared", ["type" => "integer", "length" => 1, "notnull" => true, "default" => 0]);

        $il_db
            ->expects($this->once())
            ->method("addIndex")
            ->with(ilProviderDB::PROVIDER_TABLE, ["shared"], "ids");
   
        $db = new ilProviderDB($il_db, $this->il_tree_mock(), $this->il_object_data_cache_mock(), []);
        $db->createTables();
    }

    public function test_create() {
        $il_db = $this->il_db_mock();

        $owner = $this
            ->getMockBuilder(\ilObject::class)
            ->setMethods(["getId"])
            ->getMock();

        $owner_id = 42;
        $owner
            ->method("getId")
            ->willReturn($owner_id);

        $new_provider_id = 23;
        $object_type = "crs";
        $class_name = Test_SeparatedUnboundProvider::class;
        $include_path = __DIR__."/SeparatedUnboundProviderTest.php";

        $insert_provider =
            [ "id" => ["integer", $new_provider_id]
            , "owner" => ["integer", $owner_id]
            , "object_type" => ["string", $object_type]
            , "class_name" => ["string", $class_name]
            , "include_path" => ["string", $include_path]
			, "shared" => ["integer", 0]
            ];

        $insert_component_1 =
            [ "id" => ["integer", $new_provider_id]
            , "component_type" => ["string", AttachString::class]
            ];

        $insert_component_2 =
            [ "id" => ["integer", $new_provider_id]
            , "component_type" => ["string", AttachInt::class]
            ];

        $il_db
            ->expects($this->exactly(3))
            ->method("insert")
            ->withConsecutive(
                [ilProviderDB::PROVIDER_TABLE, $insert_provider],
                [ilProviderDB::COMPONENT_TABLE, $insert_component_1],
                [ilProviderDB::COMPONENT_TABLE, $insert_component_2]);

        $il_db
            ->expects($this->once())
            ->method("nextId")
            ->with(ilProviderDB::PROVIDER_TABLE)
            ->willReturn($new_provider_id);

        $db = new ilProviderDB($il_db, $this->il_tree_mock(), $this->il_object_data_cache_mock(), []);
        $unbound_provider = $db->createSeparatedUnboundProvider($owner, $object_type, $class_name, $include_path);

        $this->assertInstanceOf(Test_SeparatedUnboundProvider::class, $unbound_provider);
    }

    public function test_delete() {
        $il_db = $this->il_db_mock();

        $owner = $this
            ->getMockBuilder(\ilObject::class)
            ->setMethods(["getId"])
            ->getMock();


        $unbound_provider = $this->createMock(UnboundProvider::class);

        $unbound_provider_id = 23;
        $unbound_provider
            ->expects($this->once())
            ->method("idFor")
            ->with($owner)
            ->willReturn($unbound_provider_id);

        $il_db
            ->expects($this->atLeastOnce())
            ->method("quote")
            ->with($unbound_provider_id, "integer")
            ->willReturn("~$unbound_provider_id~");

        $il_db
            ->expects($this->exactly(2))
            ->method("manipulate")
            ->withConsecutive(
                ["DELETE FROM ".ilProviderDB::PROVIDER_TABLE." WHERE id = ~$unbound_provider_id~"],
                ["DELETE FROM ".ilProviderDB::COMPONENT_TABLE." WHERE id = ~$unbound_provider_id~"]);

        $db = new ilProviderDB($il_db, $this->il_tree_mock(), $this->il_object_data_cache_mock(), []);
        $db->delete($unbound_provider, $owner);
    }

    public function test_update() {
        $il_db = $this->il_db_mock();

        $owner1 = $this
            ->getMockBuilder(\ilObject::class)
            ->setMethods(["getId"])
            ->getMock();

        $owner2 = $this
            ->getMockBuilder(\ilObject::class)
            ->setMethods(["getId"])
            ->getMock();

        $unbound_provider = $this->createMock(UnboundProvider::class);

        $unbound_provider
            ->expects($this->once())
            ->method("owners")
            ->willReturn([$owner1, $owner2]);

        $unbound_provider
            ->expects($this->exactly(2))
            ->method("idFor")
            ->withConsecutive([$owner1],[$owner2])
            ->will($this->onConsecutiveCalls(1,2));

        $unbound_provider
            ->expects($this->once())
            ->method("componentTypes")
            ->willReturn([AttachString::class, AttachInt::class]);

        $il_db
            ->expects($this->atLeastOnce())
            ->method("quote")
            ->withConsecutive([1, "integer"],[2, "integer"])
            ->will($this->returnCallback(function($int) { return "~$int~"; }));

        $il_db
            ->expects($this->exactly(2))
            ->method("manipulate")
            ->withConsecutive(
                ["DELETE FROM ".ilProviderDB::COMPONENT_TABLE." WHERE id = ~1~"],
                ["DELETE FROM ".ilProviderDB::COMPONENT_TABLE." WHERE id = ~2~"]);

        $insert_component_1 =
            [ "id" => ["integer", 1]
            , "component_type" => ["string", AttachString::class]
            ];

        $insert_component_2 =
            [ "id" => ["integer", 1]
            , "component_type" => ["string", AttachInt::class]
            ];

        $insert_component_3 =
            [ "id" => ["integer", 2]
            , "component_type" => ["string", AttachString::class]
            ];

        $insert_component_4 =
            [ "id" => ["integer", 2]
            , "component_type" => ["string", AttachInt::class]
            ];

        $il_db
            ->expects($this->exactly(4))
            ->method("insert")
            ->withConsecutive(
                [ilProviderDB::COMPONENT_TABLE, $insert_component_1],
                [ilProviderDB::COMPONENT_TABLE, $insert_component_2],
                [ilProviderDB::COMPONENT_TABLE, $insert_component_3],
                [ilProviderDB::COMPONENT_TABLE, $insert_component_4]);

        $db = new ilProviderDB($il_db, $this->il_tree_mock(), $this->il_object_data_cache_mock(), []);
        $db->update($unbound_provider);
    }

    public function test_unboundProvidersOf() {
        $il_db = $this->il_db_mock();

        $owner = $this
            ->getMockBuilder(\ilObject::class)
            ->setMethods(["getId"])
            ->getMock();
        $owner_id = 42;
        $owner
            ->expects($this->atLeastOnce())
            ->method("getId")
            ->willReturn(42);

        $il_db
            ->expects($this->atLeastOnce())
            ->method("quote")
            ->with($owner_id, "integer")
            ->willReturn("~$owner_id~");

        $result = "RESULT";
        $il_db
            ->expects($this->once())
            ->method("query")
            ->with("SELECT id, object_type, class_name, include_path FROM ".ilProviderDB::PROVIDER_TABLE." WHERE owner = ~$owner_id~")
            ->willReturn($result);

        $object_type = "type";
        $class_name = Test_SeparatedUnboundProvider::class;
        $include_path = __DIR__."/SeparatedUnboundProviderTest.php";

        $il_db
            ->expects($this->exactly(3))
            ->method("fetchAssoc")
            ->with("RESULT")
            ->will($this->onConsecutiveCalls(
                ["id" => 1, "object_type" => $object_type, "class_name" => $class_name, "include_path" => $include_path],
                ["id" => 2, "object_type" => $object_type, "class_name" => $class_name, "include_path" => $include_path],
                null));


        $db = new ilProviderDB($il_db, $this->il_tree_mock(), $this->il_object_data_cache_mock());
        $providers = $db->unboundProvidersOf($owner);

        $this->assertCount(2, $providers);

        foreach ($providers as $provider) {
            $this->assertInstanceOf(Test_SeparatedUnboundProvider::class, $provider);
            $this->assertEquals($object_type, $provider->objectType());
            $this->assertEquals([$owner], $provider->owners());
        }

        list($provider1, $provider2) = $providers;
        $this->assertEquals(1, $provider1->idFor($owner));
        $this->assertEquals(2, $provider2->idFor($owner));
    }

    public function test_load() {
        $il_db = $this->il_db_mock();

        $provider_id = 23;
        $owner_id = 42;

        $il_db
            ->expects($this->atLeastOnce())
            ->method("quote")
            ->with($provider_id, "integer")
            ->willReturn("~$provider_id~");
        $result = "RESULT";
        $il_db
            ->expects($this->once())
            ->method("query")
            ->with("SELECT owner, object_type, class_name, include_path FROM ".ilProviderDB::PROVIDER_TABLE." WHERE id = ~$provider_id~")
            ->willReturn($result);

        $object_type = "type";
        $class_name = Test_SeparatedUnboundProvider::class;
        $include_path = __DIR__."/SeparatedUnboundProviderTest.php";

        $il_db
            ->expects($this->once())
            ->method("fetchAssoc")
            ->with("RESULT")
            ->willReturn(
                ["owner" => $owner_id, "object_type" => $object_type, "class_name" => $class_name, "include_path" => $include_path]
                );

        $db = new Test_ilProviderDB($il_db, $this->il_tree_mock(), $this->il_object_data_cache_mock(), []);
        $owner = $this
            ->getMockBuilder(\ilObject::class)
            ->getMock();
        $db->object_obj[$owner_id] = $owner;
        $provider = $db->load($provider_id);

        $this->assertInstanceOf(Test_SeparatedUnboundProvider::class, $provider);
        $this->assertEquals($object_type, $provider->objectType());
        $this->assertEquals([$owner], $provider->owners());
        $this->assertEquals($provider_id, $provider->idFor($owner));
    }

    public function test_providersFor() {
        $il_db = $this->il_db_mock();
        $il_tree = $this->il_tree_mock();
        $il_cache = $this->il_object_data_cache_mock();

        $object_ref_id = 42;
        $object_type = "crs";
        $object = $this
            ->getMockBuilder(\ilObject::class)
            ->setMethods(["getRefId", "getType"])
            ->getMock();

        $object
            ->expects($this->atLeastOnce())
            ->method("getRefId")
            ->willReturn($object_ref_id);

        $object
            ->expects($this->atLeastOnce())
            ->method("getType")
            ->willReturn($object_type);

        $sub_tree_ids = ["3", "14"];
        $il_tree
            ->expects($this->once())
            ->method("getSubTreeIds")
            ->with($object_ref_id)
            ->willReturn($sub_tree_ids);

		$tree_ids = array_merge([$object_ref_id], $sub_tree_ids);
        $il_cache
            ->expects($this->once())
            ->method("preloadReferenceCache")
            ->with($tree_ids);

        $il_cache
            ->expects($this->exactly(3))
            ->method("lookupObjId")
            ->withConsecutive([$tree_ids[0]],[$tree_ids[1]], [$tree_ids[2]])
            ->will($this->onConsecutiveCalls($tree_ids[0], $tree_ids[1], $tree_ids[2]));

        $il_db
            ->expects($this->exactly(2))
            ->method("in")
            ->with("owner", $tree_ids, false, "integer")
            ->willReturn("~IN~");

        $il_db
            ->expects($this->exactly(2))
            ->method("quote")
            ->with($object_type)
            ->willReturn("~TYPE~");

        $result1 = "RESULT 1";
        $result2 = "RESULT 2";
        $il_db
            ->expects($this->exactly(2))
            ->method("query")
            ->withConsecutive
                ( ["SELECT id, owner, class_name, include_path FROM ".ilProviderDB::PROVIDER_TABLE." WHERE shared = 0 AND ~IN~ AND object_type = ~TYPE~"]

                , ["SELECT GROUP_CONCAT(id SEPARATOR \",\") ids, GROUP_CONCAT(owner SEPARATOR \",\") owners, class_name, include_path FROM ".ilProviderDB::PROVIDER_TABLE." WHERE shared = 1 AND ~IN~ AND object_type = ~TYPE~ GROUP BY class_name, include_path"]
                )
            ->will($this->onConsecutiveCalls($result1, $result2));

        $class_name = Test_SeparatedUnboundProvider::class;
        $include_path = __DIR__."/SeparatedUnboundProviderTest.php";

        $il_db
            ->expects($this->exactly(4))
            ->method("fetchAssoc")
            ->withConsecutive([$result1],[$result1],[$result1],[$result2])
            ->will($this->onConsecutiveCalls(
                ["id" => 1, "owner" => $sub_tree_ids[0], "class_name" => $class_name, "include_path" => $include_path],
                ["id" => 2, "owner" => $sub_tree_ids[1], "class_name" => $class_name, "include_path" => $include_path],
                null,
                null));

        $db = new Test_ilProviderDB($il_db, $il_tree, $il_cache, []);

        $owner_1 = $this
            ->getMockBuilder(\ilObject::class)
            ->getMock();
        $db->object_ref[$sub_tree_ids[0]] = $owner_1;

        $owner_2 = $this
            ->getMockBuilder(\ilObject::class)
            ->getMock();
        $db->object_ref[$sub_tree_ids[1]] = $owner_2;

        $providers = $db->providersFor($object);
        $this->assertCount(2, $providers);

        foreach ($providers as $provider) {
            $this->assertInstanceOf(Provider::class, $provider);
            $this->assertEquals($object, $provider->object());
            $this->assertEquals($object_type, $provider->unboundProvider()->objectType());
        }

        list($provider1, $provider2) = $providers;
        $this->assertEquals(1, $provider1->unboundProvider()->idFor($owner_1));
        $this->assertEquals([$owner_1], $provider1->owners());

        $this->assertEquals(2, $provider2->unboundProvider()->idFor($owner_2));
        $this->assertEquals([$owner_2], $provider2->owners());

    }

    public function test_providersFor_filtered() {
        $il_db = $this->il_db_mock();
        $il_tree = $this->il_tree_mock();
        $il_cache = $this->il_object_data_cache_mock();

        $object_ref_id = 42;
        $object_type = "crs";
        $object = $this
            ->getMockBuilder(\ilObject::class)
            ->setMethods(["getRefId", "getType"])
            ->getMock();

        $object
            ->expects($this->atLeastOnce())
            ->method("getRefId")
            ->willReturn($object_ref_id);

        $object
            ->expects($this->atLeastOnce())
            ->method("getType")
            ->willReturn($object_type);

        $sub_tree_ids = ["3", "14"];
        $il_tree
            ->expects($this->once())
            ->method("getSubTreeIds")
            ->with($object_ref_id)
            ->willReturn($sub_tree_ids);

		$tree_ids = array_merge([$object_ref_id], $sub_tree_ids);
        $il_cache
            ->expects($this->once())
            ->method("preloadReferenceCache")
            ->with($tree_ids);

        $il_cache
            ->expects($this->exactly(3))
            ->method("lookupObjId")
            ->withConsecutive([$tree_ids[0]],[$tree_ids[1]], [$tree_ids[2]])
            ->will($this->onConsecutiveCalls($tree_ids[0], $tree_ids[1], $tree_ids[2]));

        $il_db
            ->expects($this->exactly(2))
            ->method("in")
            ->with("owner", $tree_ids, false, "integer")
            ->willReturn("~IN~");

        $component_type = "COMPONENT_TYPE";
        $il_db
            ->expects($this->exactly(4))
            ->method("quote")
            ->withConsecutive([$object_type], [$component_type], [$object_type], [$component_type])
            ->will($this->onConsecutiveCalls("~TYPE~", "~COMPONENT_TYPE~", "~TYPE~", "~COMPONENT_TYPE~"));

        $result1 = "RESULT 1";
        $result2 = "RESULT 2";
        $il_db
            ->expects($this->exactly(2))
            ->method("query")
            ->withConsecutive(
                ["SELECT prv.id, prv.owner, prv.class_name, prv.include_path ".
                    "FROM ".ilProviderDB::PROVIDER_TABLE." prv ".
                    "JOIN ".ilProviderDB::COMPONENT_TABLE." cmp ".
                    "ON prv.id = cmp.id ".
                    "WHERE shared = 0 ".
                    "AND ~IN~ ".
                    "AND object_type = ~TYPE~ ".
                    "AND component_type = ~COMPONENT_TYPE~"],
                ["SELECT GROUP_CONCAT(prv.id SEPARATOR \",\") ids, GROUP_CONCAT(prv.owner SEPARATOR \",\") owners, prv.class_name, prv.include_path ".
                    "FROM ".ilProviderDB::PROVIDER_TABLE." prv ".
                    "JOIN ".ilProviderDB::COMPONENT_TABLE." cmp ".
                    "ON prv.id = cmp.id ".
                    "WHERE shared = 1 ".
                    "AND ~IN~ ".
                    "AND object_type = ~TYPE~ ".
                    "AND component_type = ~COMPONENT_TYPE~ ".
                    "GROUP BY prv.class_name, prv.include_path"]
                )
            ->will($this->onConsecutiveCalls($result1, $result2));

        $il_db
            ->expects($this->exactly(2))
            ->method("fetchAssoc")
            ->withConsecutive([$result1], [$result2])
            ->willReturn(null);

        $db = new Test_ilProviderDB($il_db, $il_tree, $il_cache, []);

        $providers = $db->providersFor($object, "COMPONENT_TYPE");
        $this->assertCount(0, $providers);
    }

    public function test_providersFor_shared() {
        $il_db = $this->il_db_mock();
        $il_tree = $this->il_tree_mock();
        $il_cache = $this->il_object_data_cache_mock();

        $object_ref_id = 42;
        $object_type = "crs";
        $object = $this
            ->getMockBuilder(\ilObject::class)
            ->setMethods(["getRefId", "getType"])
            ->getMock();

        $object
            ->expects($this->atLeastOnce())
            ->method("getRefId")
            ->willReturn($object_ref_id);

        $object
            ->expects($this->atLeastOnce())
            ->method("getType")
            ->willReturn($object_type);

        $sub_tree_ids = ["3", "14"];
        $il_tree
            ->expects($this->once())
            ->method("getSubTreeIds")
            ->with($object_ref_id)
            ->willReturn($sub_tree_ids);

		$tree_ids = array_merge([$object_ref_id], $sub_tree_ids);
        $il_cache
            ->expects($this->once())
            ->method("preloadReferenceCache")
            ->with($tree_ids);

        $il_cache
            ->expects($this->exactly(3))
            ->method("lookupObjId")
            ->withConsecutive([$tree_ids[0]],[$tree_ids[1]], [$tree_ids[2]])
            ->will($this->onConsecutiveCalls($tree_ids[0], $tree_ids[1], $tree_ids[2]));

        $il_db
            ->expects($this->exactly(2))
            ->method("in")
            ->with("owner", $tree_ids, false, "integer")
            ->willReturn("~IN~");

        $il_db
            ->expects($this->exactly(2))
            ->method("quote")
            ->with($object_type)
            ->willReturn("~TYPE~");

        $result1 = "RESULT 1";
        $result2 = "RESULT 2";
        $il_db
            ->expects($this->exactly(2))
            ->method("query")
            ->withConsecutive
                ( ["SELECT id, owner, class_name, include_path FROM ".ilProviderDB::PROVIDER_TABLE." WHERE shared = 0 AND ~IN~ AND object_type = ~TYPE~"]

                , ["SELECT GROUP_CONCAT(id SEPARATOR \",\") ids, GROUP_CONCAT(owner SEPARATOR \",\") owners, class_name, include_path FROM ".ilProviderDB::PROVIDER_TABLE." WHERE shared = 1 AND ~IN~ AND object_type = ~TYPE~ GROUP BY class_name, include_path"]
                )
            ->will($this->onConsecutiveCalls($result1, $result2));

        $class_name = "Test_SharedUnboundProvider";
        $include_path = __DIR__."/SharedUnboundProviderTest.php";

        $il_db
            ->expects($this->exactly(3))
            ->method("fetchAssoc")
            ->withConsecutive([$result1],[$result2],[$result2])
            ->will($this->onConsecutiveCalls(
                null,
                ["ids" => "1,2", "owners" => $sub_tree_ids[0].",".$sub_tree_ids[1], "class_name" => $class_name, "include_path" => $include_path],
                null));

        $db = new Test_ilProviderDB($il_db, $il_tree, $il_cache, []);

        $owner_1 = $this
            ->getMockBuilder(\ilObject::class)
            ->getMock();
        $owner_1
            ->method("getId")
            ->willReturn("23");
        $db->object_ref[$sub_tree_ids[0]] = $owner_1;

        $owner_2 = $this
            ->getMockBuilder(\ilObject::class)
            ->getMock();
        $owner_2
            ->method("getId")
            ->willReturn("24");
        $db->object_ref[$sub_tree_ids[1]] = $owner_2;

        $providers = $db->providersFor($object);
        $this->assertCount(1, $providers);
        $provider = array_shift($providers);

        $this->assertInstanceOf(Provider::class, $provider);
        $this->assertEquals($object, $provider->object());
        $this->assertEquals($object_type, $provider->unboundProvider()->objectType());
        $this->assertEquals([$owner_1, $owner_2], $provider->unboundProvider()->owners());

        $this->assertEquals(1, $provider->unboundProvider()->idFor($owner_1));
        $this->assertEquals(2, $provider->unboundProvider()->idFor($owner_2));
    }
}
