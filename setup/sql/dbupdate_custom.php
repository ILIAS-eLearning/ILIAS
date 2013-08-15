<#1>
<?php
$fields = array(
    'id'          => array(
        'type'    => 'integer',
        'length'  => 4,
        'notnull' => true
    ),
    'is_online'   => array(
        'type'    => 'integer',
        'length'  => 1,
        'notnull' => false
    ),
    'service'     => array(
        'type'    => 'text',
        'length'  => 255,
        'fixed'   => false,
        'notnull' => false
    ),
    'root_folder' => array(
        'type'    => 'text',
        'length'  => 255,
        'fixed'   => false,
        'notnull' => false
    ),
    'root_id'     => array(
        'type'    => 'text',
        'length'  => 255,
        'fixed'   => false,
        'notnull' => false
    ),
);

$ilDB->createTable("il_cld_data", $fields);
$ilDB->addPrimaryKey("il_cld_data", array("id"));
?>



<#2>
    <?php

    include_once('./Services/Migration/DBUpdate_3560/classes/class.ilDBUpdateNewObjectType.php');

    $cld_type_id = ilDBUpdateNewObjectType::addNewType('cld', 'Cloud Folder');

    $rbac_ops = array(
        ilDBUpdateNewObjectType::RBAC_OP_EDIT_PERMISSIONS,
        ilDBUpdateNewObjectType::RBAC_OP_VISIBLE,
        ilDBUpdateNewObjectType::RBAC_OP_READ,
        ilDBUpdateNewObjectType::RBAC_OP_WRITE,
        ilDBUpdateNewObjectType::RBAC_OP_DELETE,
        ilDBUpdateNewObjectType::RBAC_OP_COPY
    );
    ilDBUpdateNewObjectType::addRBACOperations($cld_type_id, $rbac_ops);

    $parent_types = array('root', 'cat', 'crs', 'fold', 'grp');
    ilDBUpdateNewObjectType::addRBACCreate('create_cld', 'Create Cloud Folder', $parent_types);


    // re-doing dcl
    $ops_id = ilDBUpdateNewObjectType::addCustomRBACOperation('upload', 'Upload Items', 'object', 3200);
    ilDBUpdateNewObjectType::addRBACOperation($cld_type_id, $ops_id);
    $ops_id = ilDBUpdateNewObjectType::addCustomRBACOperation('delete_files', 'Delete Files', 'object', 3200);
    ilDBUpdateNewObjectType::addRBACOperation($cld_type_id, $ops_id);
    $ops_id = ilDBUpdateNewObjectType::addCustomRBACOperation('delete_folders', 'Delete Folders', 'object', 3200);
    ilDBUpdateNewObjectType::addRBACOperation($cld_type_id, $ops_id);
    $ops_id = ilDBUpdateNewObjectType::addCustomRBACOperation('download', 'Download Items', 'object', 3200);
    ilDBUpdateNewObjectType::addRBACOperation($cld_type_id, $ops_id);
    $ops_id = ilDBUpdateNewObjectType::addCustomRBACOperation('files_visible', 'Files are visible', 'object', 3200);
    ilDBUpdateNewObjectType::addRBACOperation($cld_type_id, $ops_id);
    $ops_id = ilDBUpdateNewObjectType::addCustomRBACOperation('folders_visible', 'Folders are visible', 'object', 3200);
    ilDBUpdateNewObjectType::addRBACOperation($cld_type_id, $ops_id);
    $ops_id = ilDBUpdateNewObjectType::addCustomRBACOperation('create_folders', 'Folders may be created', 'object', 3200);
    ilDBUpdateNewObjectType::addRBACOperation($cld_type_id, $ops_id);

?>
<#3>
<?php
    $ilCtrlStructureReader->getStructure();
?>