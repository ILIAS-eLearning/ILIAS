<#1>
<?php
//tableview
$fields = array(
    'id' => array(
        'notnull' => '1',
        'type' => 'integer',
        'length' => '8',

    ),
    'table_id' => array(
        'notnull' => '1',
        'type' => 'integer',
        'length' => '8',

    ),
    'title' => array(
        'notnull' => '1',
        'type' => 'text',
        'length' => '128',

    ),
    'roles' => array(
        'type' => 'text',
        'length' => '256',
    ),
    'description' => array(
        'type' => 'text',
        'length' => '128',

    ),
    'tableview_order' => array(
        'type' => 'integer',
        'length' => '8',

    ),

);
if (! $ilDB->tableExists('il_dcl_tableview')) {
    $ilDB->createTable('il_dcl_tableview', $fields);
    $ilDB->addPrimaryKey('il_dcl_tableview', array( 'id' ));

    if (! $ilDB->sequenceExists('il_dcl_tableview')) {
        $ilDB->createSequence('il_dcl_tableview');
    }
    $ilDB->query('CREATE INDEX tableview_table_index ON il_dcl_tableview (table_id)');
}

//tableview_field_setting
$fields = array(
    'id' => array(
        'notnull' => '1',
        'type' => 'integer',
        'length' => '8',

    ),
    'tableview_id' => array(
        'notnull' => '1',
        'type' => 'integer',
        'length' => '8',

    ),
    'field' => array(
        'notnull' => '1',
        'type' => 'text',
        'length' => '128',

    ),
    'visible' => array(
        'type' => 'integer',
        'length' => '1',

    ),
    'in_filter' => array(
        'type' => 'integer',
        'length' => '1',

    ),
    'filter_value' => array(
        'type' => 'clob',
    ),
    'filter_changeable' => array(
        'type' => 'integer',
        'length' => '1',

    ),

);
if (! $ilDB->tableExists('il_dcl_tview_set')) {
    $ilDB->createTable('il_dcl_tview_set', $fields);
    $ilDB->addPrimaryKey('il_dcl_tview_set', array( 'id' ));

    if (! $ilDB->sequenceExists('il_dcl_tview_set')) {
        $ilDB->createSequence('il_dcl_tview_set');
    }

}

if (! $ilDB->tableExists('il_dcl_tview_set')) {
    $ilDB->createTable('il_dcl_tview_set', $fields);
    $ilDB->addPrimaryKey('il_dcl_tview_set', array( 'id' ));

    if (! $ilDB->sequenceExists('il_dcl_tview_set')) {
        $ilDB->createSequence('il_dcl_tview_set');
    }

    $ilDB->query('CREATE INDEX tview_set_index ON il_dcl_tview_set (tableview_id)');
}

$fields = array(
    'id' => array(
        'notnull' => '1',
        'type' => 'integer',
        'length' => '8',

    ),
    'table_id' => array(
        'notnull' => '1',
        'type' => 'integer',
        'length' => '8',

    ),
    'field' => array(
        'notnull' => '1',
        'type' => 'text',
        'length' => '128',

    ),
    'field_order' => array(
        'type' => 'integer',
        'length' => '8',

    ),
    'exportable' => array(
        'type' => 'integer',
        'length' => '1',

    ),

);
if (! $ilDB->tableExists('il_dcl_tfield_set')) {
    $ilDB->createTable('il_dcl_tfield_set', $fields);
    $ilDB->addPrimaryKey('il_dcl_tfield_set', array( 'id' ));

    if (! $ilDB->sequenceExists('il_dcl_tfield_set')) {
        $ilDB->createSequence('il_dcl_tfield_set');
    }
    $ilDB->query('CREATE UNIQUE INDEX tablefield_index ON il_dcl_tfield_set (table_id, field)');
}
?>
<#2>
<?php
//migration for datacollections:
//ĉreate a standardview for each table, set visibility/filterability for each field
//and delete entries from old view tables
require_once("./Modules/DataCollection/classes/TableView/class.ilDclTableView.php");
require_once("./Modules/DataCollection/classes/TableView/class.ilDclTableViewFieldSetting.php");
require_once("./Modules/DataCollection/classes/Table/class.ilDclTableFieldSetting.php");
require_once("./Modules/DataCollection/classes/Helpers/class.ilDclCache.php");
$roles = array();
$query = $ilDB->query('SELECT rol_id FROM rbac_fa WHERE parent = ' . $ilDB->quote(ROLE_FOLDER_ID, 'integer') . " AND assign='y'");
while ( $global_role = $ilDB->fetchAssoc($query) ) {
    $roles[] = $global_role['rol_id'];
}

//set order of main tables, since main_table_id will be removed
$ilDB->addTableColumn('il_dcl_table', 'table_order', array('type' => 'integer', 'length' => 8));
$main_table_query = $ilDB->query('SELECT main_table_id FROM il_dcl_data');
while ($rec = $ilDB->fetchAssoc($main_table_query)) {
    $table = ilDclCache::getTableCache($rec['main_table_id']);
    $table->setOrder(10);
    $table->doUpdate();
}
$ilDB->dropTableColumn('il_dcl_data', 'main_table_id');

//
$table_query = $ilDB->query('SELECT id, ref_id FROM il_dcl_table 
                              INNER JOIN object_reference ON (object_reference.obj_id = il_dcl_table.obj_id)');

$mapping = array();
while ($rec = $ilDB->fetchAssoc($table_query)) {
    $query = $ilDB->query('SELECT rol_id FROM rbac_fa WHERE parent = ' . $ilDB->quote($rec['ref_id'], 'integer') . " AND assign='y'");
    while ( $local_role = $ilDB->fetchAssoc($query)) {
        $roles[] = $local_role['rol_id'];
    }
    //create standardviews for each DCL Table and set id mapping
    $tableview = new ilDclTableView();
    $tableview->setTableId($rec['id']);
    $tableview->setTitle('Standardview');
    $tableview->setRoles($roles);
    $tableview->setOrder(10);
    $tableview->create(false);
    $mapping[$rec['id']] = $tableview->getId();
}

//fetch information about visibility/filterability
$view_query = $ilDB->query(
    "SELECT il_dcl_view.table_id, tbl_visible.field, tbl_visible.is_set as visible, f.filterable
        FROM il_dcl_viewdefinition tbl_visible
            INNER JOIN il_dcl_view ON (il_dcl_view.id = tbl_visible.view_id
            AND il_dcl_view.type = 1)
            INNER JOIN
                (SELECT table_id, field, tbl_filterable.is_set as filterable
                    FROM il_dcl_view
                    INNER JOIN il_dcl_viewdefinition tbl_filterable ON (il_dcl_view.id = tbl_filterable.view_id
                    AND il_dcl_view.type = 3)) f ON (f.field = tbl_visible.field AND f.table_id = il_dcl_view.table_id)");

//set visibility/filterability
$view_id_cache = array();
while ($rec = $ilDB->fetchAssoc($view_query)) {
    $field_set = new ilDclTableViewFieldSetting();
    $field_set->setTableviewId($mapping[$rec['table_id']]);
    $field_set->setField($rec['field']);
    $field_set->setVisible($rec['visible']);
    $field_set->setInFilter($rec['filterable']);
    $field_set->setFilterChangeable(true);
    $field_set->create();

}

//fetch information about editability/exportability
$view_query = $ilDB->query(
    "SELECT il_dcl_view.table_id, tbl_exportable.field, tbl_exportable.is_set as exportable, tbl_exportable.field_order
        FROM il_dcl_viewdefinition tbl_exportable
            INNER JOIN il_dcl_view ON (il_dcl_view.id = tbl_exportable.view_id
            AND il_dcl_view.type = 4)");


//set editability/exportability
while ($rec = $ilDB->fetchAssoc($view_query)) {
    $field_set = new ilDclTableFieldSetting();
    $field_set->setTableId($rec['table_id']);
    $field_set->setField($rec['field']);
    $field_set->setExportable($rec['exportable']);
    $field_set->setFieldOrder($rec['field_order']);
    $field_set->create();
}

//migrate page object
$query = $ilDB->query('SELECT * 
        FROM il_dcl_view 
        INNER JOIN page_object on (il_dcl_view.id = page_object.page_id)
          WHERE il_dcl_view.type = 0
            AND page_object.parent_type = ' . $ilDB->quote('dclf', 'text'));

while ($rec = $ilDB->fetchAssoc($query)) {
    $ilDB->query('UPDATE page_object 
                  SET page_id = ' . $ilDB->quote($mapping[$rec['table_id']], 'integer') . ' 
                  WHERE page_id = ' . $ilDB->quote($rec['id'], 'integer') . ' 
                      AND page_object.parent_type = ' . $ilDB->quote('dclf', 'text'));
}

//delete old tables
$ilDB->dropTable('il_dcl_viewdefinition');
$ilDB->dropTable('il_dcl_view');

?>
<#3>
<?php
$ilCtrlStructureReader->getStructure();
?>


