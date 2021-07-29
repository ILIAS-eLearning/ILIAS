<#1>
<?php
include_once 'Services/Migration/DBUpdate_3560/classes/class.ilDBUpdateNewObjectType.php';

$skill_tree_type_id = ilDBUpdateNewObjectType::getObjectTypeId('skee');

if (!$skill_tree_type_id) {
    // add basic object type
    $skill_tree_type_id = ilDBUpdateNewObjectType::addNewType('skee', 'Skill Tree');

    $opsId = [];
    $opsId[] = ilDBUpdateNewObjectType::addCustomRBACOperation(
        'read_comp',
        'Read Competences',
        'object',
        6500
    );

    $opsId[] = ilDBUpdateNewObjectType::addCustomRBACOperation(
        'read_profiles',
        'Read Competence Profiles',
        'object',
        6510
    );

    $opsId[] = ilDBUpdateNewObjectType::addCustomRBACOperation(
        'manage_comp',
        'Manage Competences',
        'object',
        8500
    );

    $opsId[] = ilDBUpdateNewObjectType::addCustomRBACOperation(
        'manage_comp_temp',
        'Manage Competence Templates',
        'object',
        8510
    );

    $opsId[] = ilDBUpdateNewObjectType::addCustomRBACOperation(
        'manage_profiles',
        'Manage Competence Profiles',
        'object',
        8520
    );

    // addRBACOperations only accepts standard not custom ops, fix see step 2/3
    //ilDBUpdateNewObjectType::addRBACOperations($skill_tree_type_id, $opsId);

    // common rbac operations
    $rbacOperations = array(
        ilDBUpdateNewObjectType::RBAC_OP_EDIT_PERMISSIONS,
        ilDBUpdateNewObjectType::RBAC_OP_VISIBLE,
        ilDBUpdateNewObjectType::RBAC_OP_READ,
        ilDBUpdateNewObjectType::RBAC_OP_WRITE,
        ilDBUpdateNewObjectType::RBAC_OP_DELETE,
        ilDBUpdateNewObjectType::RBAC_OP_COPY
    );

    ilDBUpdateNewObjectType::addRBACOperations($skill_tree_type_id, $rbacOperations);

    // add create operation for relevant container types

    $parentTypes = array('skmg');
    ilDBUpdateNewObjectType::addRBACCreate('create_skee', 'Create Skill Tree', $parentTypes);

    //ilDBUpdateNewObjectType::applyInitialPermissionGuideline('skee', false);
}
?>
<#2>
<?php

include_once 'Services/Migration/DBUpdate_3560/classes/class.ilDBUpdateNewObjectType.php';
$skill_tree_type_id = ilDBUpdateNewObjectType::getObjectTypeId('skee');
$ops_id = ilDBUpdateNewObjectType::getCustomRBACOperationId('read_comp');
ilDBUpdateNewObjectType::addRBACOperation($skill_tree_type_id, $ops_id);

?>
<#3>
<?php

include_once 'Services/Migration/DBUpdate_3560/classes/class.ilDBUpdateNewObjectType.php';
$skill_tree_type_id = ilDBUpdateNewObjectType::getObjectTypeId('skee');
$ops_id = ilDBUpdateNewObjectType::getCustomRBACOperationId('read_profiles');
ilDBUpdateNewObjectType::addRBACOperation($skill_tree_type_id, $ops_id);
$ops_id = ilDBUpdateNewObjectType::getCustomRBACOperationId('manage_comp');
ilDBUpdateNewObjectType::addRBACOperation($skill_tree_type_id, $ops_id);
$ops_id = ilDBUpdateNewObjectType::getCustomRBACOperationId('manage_comp_temp');
ilDBUpdateNewObjectType::addRBACOperation($skill_tree_type_id, $ops_id);
$ops_id = ilDBUpdateNewObjectType::getCustomRBACOperationId('manage_profiles');
ilDBUpdateNewObjectType::addRBACOperation($skill_tree_type_id, $ops_id);

?>
<#4>
<?php

// get skill managemenet object id
$set = $ilDB->queryF("SELECT * FROM object_data " .
    " WHERE type = %s ",
    ["text"],
    ["skmg"]
);
$rec = $ilDB->fetchAssoc($set);

// get skill management ref id
$set = $ilDB->queryF("SELECT * FROM object_reference " .
    " WHERE obj_id = %s ",
    ["integer"],
    [$rec["obj_id"]]
);
$rec = $ilDB->fetchAssoc($set);
$skmg_ref_id = $rec["ref_id"];

// create default tree object entry
$obj_id = $ilDB->nextId('object_data');
$ilDB->manipulate("INSERT INTO object_data " .
    "(obj_id, type, title, description, owner, create_date, last_update) VALUES (" .
    $ilDB->quote($obj_id, "integer") . "," .
    $ilDB->quote("skee", "text") . "," .
    $ilDB->quote("Default", "text") . "," .
    $ilDB->quote("", "text") . "," .
    $ilDB->quote(-1, "integer") . "," .
    $ilDB->now() . "," .
    $ilDB->now() .
    ")");

// get ref id for default tree object
$ref_id = $ilDB->nextId('object_reference');
$ilDB->manipulate("INSERT INTO object_reference " .
    "(obj_id, ref_id) VALUES (" .
    $ilDB->quote($obj_id, "integer") . "," .
    $ilDB->quote($ref_id, "integer") .
    ")");

// put in tree
require_once("Services/Tree/classes/class.ilTree.php");
$tree = new ilTree(ROOT_FOLDER_ID);
$tree->insertNode($ref_id, $skmg_ref_id);

?>
<#5>
<?php

$set = $ilDB->queryF("SELECT * FROM object_data " .
    " WHERE type = %s AND title = %s",
    ["string", "string"],
    ["skee", "Default"]
);
$rec = $ilDB->fetchAssoc($set);

$ilDB->update("skl_tree", [
    "skl_tree_id" => ["integer", $rec["obj_id"]]
], [    // where
        "skl_tree_id" => ["integer", 1]
    ]
);

?>
<#6>
<?php

if (!$ilDB->tableColumnExists("skl_profile", "skee_id")) {
    $ilDB->addTableColumn("skl_profile", "skee_id", array(
        "type" => "integer",
        "notnull" => true,
        "default" => 0,
        "length" => 4
    ));
}

?>
<#7>
<?php

$set = $ilDB->queryF("SELECT * FROM object_data " .
    " WHERE type = %s AND title = %s",
    ["string", "string"],
    ["skee", "Default"]
);
$rec = $ilDB->fetchAssoc($set);

$ilDB->update("skl_profile", [
    "skee_id" => ["integer", $rec["obj_id"]]
], [    // where
        "skee_id" => ["integer", 0]
    ]
);

?>