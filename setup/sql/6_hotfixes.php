<#1>
<?php
// Patch for https://mantis.ilias.de/view.php?id=28550
$ilDB->update("settings", ["keyword" => [ilDBConstants::T_TEXT, "db_hotfixes_6"]], ["module" => [ilDBConstants::T_TEXT, "common"], "keyword" => [ilDBConstants::T_TEXT, "db_hotfixes_6_0"]]);
$hostfix_version = $ilDB->queryF('SELECT value from settings WHERE module=%s AND keyword=%s', [ilDBConstants::T_TEXT, ilDBConstants::T_TEXT], ["common", "db_hotfixes_6"])->fetchAssoc()["value"];
if (!empty($hostfix_version)) {
    $nr = $hostfix_version;
}

if (!$ilDB->tableColumnExists("exc_ass_reminders", "last_send_day")) {
    $field = array(
        'type' => 'date',
        'notnull' => false,
    );
    $ilDB->addTableColumn("exc_ass_reminders", "last_send_day", $field);
}
?>
<#2>
<?php
$set = $ilDB->queryF(
    "SELECT * FROM exc_ass_reminders " .
    " WHERE last_send > %s ",
    ["integer"],
    [0]
);
while ($rec = $ilDB->fetchAssoc($set)) {
    $last_send_day = date("Y-m-d", $rec["last_send"]);
    $ilDB->update(
        "exc_ass_reminders",
        [
        "last_send_day" => ["date", $last_send_day]
    ],
        [    // where
            "ass_id" => ["integer", $rec["ass_id"]],
            "last_send" => ["integer", $rec["last_send"]]
        ]
    );
}
?>
<#3>
<?php
    $ilCtrlStructureReader->getStructure();
?>
<#4>
<?php
$ilDB->manipulateF(
    'DELETE FROM ctrl_classfile WHERE class = %s',
    ['text'],
    ['ilwkhtmltopdfconfigformgui']
);
?>
<#5>
<?php
$ilDB->manipulateF(
    'DELETE FROM pdfgen_renderer WHERE renderer = %s',
    ['text'],
    ['WkhtmlToPdf']
);
?>
<#6>
<?php
$ilDB->manipulateF(
    'DELETE FROM pdfgen_renderer_avail WHERE renderer = %s',
    ['text'],
    ['WkhtmlToPdf']
);
?>
<#7>
<?php
require_once './Services/PDFGeneration/classes/class.ilPDFCompInstaller.php';
$renderer = 'WkhtmlToPdf';
$path = 'Services/PDFGeneration/classes/renderer/wkhtmltopdf/class.ilWkhtmlToPdfRenderer.php';
ilPDFCompInstaller::registerRenderer($renderer, $path);
$service = 'Test';
$purpose = 'UserResult'; // According to name given. Call multiple times.
ilPDFCompInstaller::registerRendererAvailability($renderer, $service, $purpose);

$purpose = 'PrintViewOfQuestions'; // According to name given. Call multiple times.
ilPDFCompInstaller::registerRendererAvailability($renderer, $service, $purpose);
?>
<#8>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#9>
<?php
$query = 'select obd.obj_id from object_data obd left join crs_reference_settings crs on obd.obj_id = crs.obj_id  ' .
    'where crs.obj_id IS NULL and type = ' . $ilDB->quote('crsr', \ilDBConstants::T_TEXT);
$res = $ilDB->query($query);
while ($row = $res->fetchRow(\ilDBConstants::FETCHMODE_OBJECT)) {
    $query = 'insert into crs_reference_settings (obj_id, member_update ) values (' .
        $ilDB->quote($row->obj_id, \ilDBConstants::T_INTEGER) . ', ' .
        $ilDB->quote(0, \ilDBConstants::T_INTEGER) .
        ')';
    $ilDB->manipulate($query);
}
?>
<#10>
<?php
$set = $ilDB->queryF(
    "SELECT * FROM object_description ",
    [],
    []
);
while ($rec = $ilDB->fetchAssoc($set)) {
    if ($rec["description"] != "") {
        $ilDB->update(
            "object_translation",
            [
            "description" => ["text", $rec["description"]]
        ],
            [    // where
                "obj_id" => ["integer", $rec["obj_id"]],
                "lang_default" => ["integer", 1]
            ]
        );
    }
}
?>
<#11>
<?php
if ($ilDB->tableColumnExists('prg_settings', 'access_ctrl_org_pos')) {
    $ilDB->dropTableColumn('prg_settings', 'access_ctrl_org_pos');
}
?>
<#12>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#13>
<?php
$ilCtrlStructureReader->getStructure();
?>

<#14>
<?php
global $DIC;
$ilDB = $DIC['ilDB'];

if ($ilDB->tableColumnExists('iass_members', 'record')) {
    $field_infos = [
        'type' => 'clob',
        'notnull' => false,
        'default' => ''
    ];
    $ilDB->modifyTableColumn('iass_members', 'record', $field_infos);
}

if ($ilDB->tableColumnExists('iass_members', 'internal_note')) {
    $field_infos = [
        'type' => 'clob',
        'notnull' => false,
        'default' => ''
    ];
    $ilDB->modifyTableColumn('iass_members', 'internal_note', $field_infos);
}

if ($ilDB->tableColumnExists('iass_settings', 'content')) {
    $field_infos = [
        'type' => 'clob',
        'notnull' => false,
        'default' => ''
    ];
    $ilDB->modifyTableColumn('iass_settings', 'content', $field_infos);
}

if ($ilDB->tableColumnExists('iass_settings', 'record_template')) {
    $field_infos = [
        'type' => 'clob',
        'notnull' => false,
        'default' => ''
    ];
    $ilDB->modifyTableColumn('iass_settings', 'record_template', $field_infos);
}

if ($ilDB->tableColumnExists('iass_info_settings', 'mails')) {
    $field_infos = [
        'type' => 'clob',
        'notnull' => false,
        'default' => ''
    ];
    $ilDB->modifyTableColumn('iass_info_settings', 'mails', $field_infos);
}
?>

<#15>
<?php
    $ilCtrlStructureReader->getStructure();
?>

<#16>
<?php
include_once('./Services/Migration/DBUpdate_3560/classes/class.ilDBUpdateNewObjectType.php');
ilDBUpdateNewObjectType::addAdminNode('lsos', 'LearningSequenceAdmin');
?>
<#17>
<?php
$ilDB->manipulate(
    "UPDATE il_cert_cron_queue SET adapter_class = " . $ilDB->quote('ilTestPlaceholderValues', 'text') . " WHERE adapter_class = " . $ilDB->quote('ilTestPlaceHolderValues', 'text')
);
$ilDB->manipulate(
    "UPDATE il_cert_cron_queue SET adapter_class = " . $ilDB->quote('ilExercisePlaceholderValues', 'text') . " WHERE adapter_class = " . $ilDB->quote('ilExercisePlaceHolderValues', 'text')
);
?>
<#18>
<?php
//template for global role il_lti_user

include_once './Services/Migration/DBUpdate_3560/classes/class.ilDBUpdateNewObjectType.php';
ilDBUpdateNewObjectType::addRBACTemplate(
    'root',
    'il_lti_user',
    'LTI user template for global role',
    [
        ilDBUpdateNewObjectType::getCustomRBACOperationId('read')
    ]
);
$new_tpl_id = 0;
$query = 'SELECT obj_id FROM object_data WHERE title = ' . $ilDB->quote('il_lti_user', 'text');
$res = $ilDB->query($query);
while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
    $new_tpl_id = $row->obj_id;
}
if ($new_tpl_id > 0) {
    $ilDB->manipulateF(
        "INSERT INTO rbac_templates (rol_id, type, ops_id, parent)" .
        " VALUES (%s, %s, %s, %s)",
        array("integer", "text", "integer", "integer"),
        array($new_tpl_id, 'cat', ilDBUpdateNewObjectType::getCustomRBACOperationId('read'), 8)
    );
}

// local role
ilDBUpdateNewObjectType::addRBACTemplate(
    'sahs',
    'il_lti_learner',
    'LTI learner template for local role',
    [
        ilDBUpdateNewObjectType::getCustomRBACOperationId('visible'),
        ilDBUpdateNewObjectType::getCustomRBACOperationId('read')
    ]
);
?>
<#19>
<?php
include_once './Services/Migration/DBUpdate_3560/classes/class.ilDBUpdateNewObjectType.php';
$new_tpl_id = 0;
$query = 'SELECT obj_id FROM object_data WHERE title = ' . $ilDB->quote('il_lti_learner', 'text');
$res = $ilDB->query($query);
while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
    $new_tpl_id = $row->obj_id;
}
if ($new_tpl_id > 0) {
    $ilDB->manipulateF(
        "INSERT INTO rbac_templates (rol_id, type, ops_id, parent)" .
        " VALUES (%s, %s, %s, %s)",
        array("integer", "text", "integer", "integer"),
        array($new_tpl_id, 'tst', ilDBUpdateNewObjectType::getCustomRBACOperationId('visible'), 8)
    );
    $ilDB->manipulateF(
        "INSERT INTO rbac_templates (rol_id, type, ops_id, parent)" .
        " VALUES (%s, %s, %s, %s)",
        array("integer", "text", "integer", "integer"),
        array($new_tpl_id, 'tst', ilDBUpdateNewObjectType::getCustomRBACOperationId('read'), 8)
    );
    $ilDB->manipulateF(
        "INSERT INTO rbac_templates (rol_id, type, ops_id, parent)" .
        " VALUES (%s, %s, %s, %s)",
        array("integer", "text", "integer", "integer"),
        array($new_tpl_id, 'lm', ilDBUpdateNewObjectType::getCustomRBACOperationId('visible'), 8)
    );
    $ilDB->manipulateF(
        "INSERT INTO rbac_templates (rol_id, type, ops_id, parent)" .
        " VALUES (%s, %s, %s, %s)",
        array("integer", "text", "integer", "integer"),
        array($new_tpl_id, 'lm', ilDBUpdateNewObjectType::getCustomRBACOperationId('read'), 8)
    );
}

?>
<#20>
<?php
$setting = new ilSetting();
$idx = $setting->get('ilfrmreadidx1', 0);
if (!$idx) {
    $ilDB->addIndex('frm_user_read', ['usr_id', 'post_id'], 'i1');
    $setting->set('ilfrmreadidx1', 1);
}
?>

<#21>
<?php
$ilCtrlStructureReader->getStructure();
?>

<#22>
<?php
require_once './Services/Migration/DBUpdate_3560/classes/class.ilDBUpdateNewObjectType.php';
$type = 'prgr';
$ops_id = ilDBUpdateNewObjectType::RBAC_OP_READ;
$type_id = ilDBUpdateNewObjectType::getObjectTypeId($type);
if (ilDBUpdateNewObjectType::isRBACOperation($type_id, $ops_id)) {
    ilDBUpdateNewObjectType::deleteRBACOperation($type, $ops_id);
}
?>

<#23>
<?php
$ilCtrlStructureReader->getStructure();
?>

<#24>
<?php
/** @var $ilDB ilDBInterface */
$ilDB->manipulateF("DELETE FROM cron_job WHERE job_id  = %s", ['text'], ['bgtsk_gc']);
?>

<#25>
<?php

$query = 'update object_data set offline = 1 where type = '.
    $ilDB->quote('crs',\ilDBConstants::T_TEXT) . '  and offline IS NULL';
$ilDB->manipulate($query);

?>

<#26>
<?php

$ilDB->modifyTableColumn(
    'ldap_role_assignments',
    'rule_id',
    [
        'type' => \ilDBConstants::T_INTEGER,
        'length' => 4,
        'notnull' => false
    ]
);
?>
<#27>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#28>
<?php
// remove magpie cache dir
$mcdir = CLIENT_WEB_DIR."/magpie_cache";
ilUtil::delDir($mcdir);
?>
<#29>
<?php
$ilDB->update("il_block_setting", [
    "value" => ["integer", 30]
], [    // where
        "setting" => ["text", "news_pd_period"]
    ]
);
?>
<#30>
<?php

$remove = ['ILIAS\Administration\AdministrationMainBarProvider|adm',
           'ILIAS\Administration\AdministrationMainBarProvider|adm_content',
           'ilBookmarkGlobalScreenProvider|mm_pd_bookm',
           'ILIAS\Certificate\Provider\CertificateMainBarProvider|mm_pd_cal',
           'ILIAS\Contact\Provider\ContactMainBarProvider|mm_pd_contacts',
           'ILIAS\Mail\Provider\MailMainBarProvider|mm_pd_mail',
           'ILIAS\News\Provider\NewsMainBarProvider|mm_pd_news',
           'ILIAS\Notes\Provider\NotesMainBarProvider|mm_pd_notes',
           'ILIAS\PersonalDesktop\PDMainBarProvider|desktop',
           'ILIAS\PersonalDesktop\PDMainBarProvider|mm_pd_achiev',
           'ILIAS\PersonalDesktop\PDMainBarProvider|mm_pd_crs_grp',
           'ILIAS\PersonalDesktop\PDMainBarProvider|mm_pd_sel_items',
           'ILIAS\Portfolio\Provider\PortfolioMainBarProvider|mm_pd_port',
           'ILIAS\Repository\Provider\RepositoryMainBarProvider|last_visited',
           'ILIAS\Repository\Provider\RepositoryMainBarProvider|rep',
           'ILIAS\Repository\Provider\RepositoryMainBarProvider|rep_main_page',
           'ILIAS\MyStaff\Provider\StaffMainBarProvider|mm_pd_mst',
           'ILIAS\PersonalWorkspace\Provider\WorkspaceMainBarProvider|mm_pd_wsp',
           'ILIAS\MainMenu\Provider\CustomMainBarProvider|5f202f3dbefde',];

$ilDB->manipulate("DELETE FROM il_mm_items WHERE ".$ilDB->in('identification', $remove, false, 'text'));

?>
<#31>
<?php
/* Determine threads which cannot be migrated because they are not valid (just to make the script robust) */
$query = "
        SELECT frmpt.thr_fk
        FROM frm_posts_tree frmpt
        INNER JOIN frm_posts fp ON fp.pos_pk = frmpt.pos_fk
        WHERE frmpt.parent_pos = 0
        GROUP BY frmpt.thr_fk
        HAVING COUNT(frmpt.fpt_pk) > 1
    ";
$ignoredThreadIds = [];
$res = $ilDB->query($query);
while ($row = $ilDB->fetchAssoc($res)) {
    $ignoredThreadIds[$row['thr_fk']] = $row['thr_fk'];
}

$hotfixstep = 31;

$GLOBALS['ilLog']->info(sprintf(
    "Started migration of wrong parent relation value in field 'parent_pos' (table: frm_posts_tree) for the former root node (Hotfix Step: %s)",
    $hotfixstep
));

$query = "
	SELECT *
	FROM frm_posts_tree fpt
	INNER JOIN frm_posts fp ON fp.pos_pk = fpt.pos_fk
	WHERE fpt.parent_pos = 0 AND fpt.depth = 1
";
$res = $ilDB->query($query);
while ($rootRow = $ilDB->fetchAssoc($res)) {
    $GLOBALS['ilLog']->info(sprintf(
        "Started parent relation fix for thread with id %s",
        $rootRow['thr_fk']
    ));
    if (isset($ignoredThreadIds[$rootRow['thr_fk']])) {
        $GLOBALS['ilLog']->warning(sprintf(
            "Cannot fix parent relation in thread with id %s because this thread tree has multiple root node postings ...",
            $rootRow['thr_fk']
        ));
        continue;
    }

    $nestedNodes = [$rootRow];

    $query = '
        SELECT *
        FROM frm_posts_tree fpt
        INNER JOIN frm_posts fp
            ON fp.pos_pk = fpt.pos_fk
        WHERE 
        fpt.thr_fk = %s AND
        fp.pos_date = %s AND
        fpt.pos_fk != %s AND
        fpt.depth BETWEEN 1 AND 3
        ORDER BY fpt.depth
    ';
    $posRes    = $ilDB->queryF(
        $query,
        ['integer', 'timestamp', 'integer',],
        [$rootRow['thr_fk'], $rootRow['pos_date'], $rootRow['pos_fk']]
    );
    $lastDepth = (int) $rootRow['depth'];
    $lastLft = (int) $rootRow['lft'];
    $lastRgt = (int) $rootRow['rgt'];
    while ($posRow = $ilDB->fetchAssoc($posRes)) {
        if ((int) $posRow['depth'] !== ($lastDepth + 1)) {
            break;
        }

        if (! ((int) $posRow['lft'] > $lastLft && (int) $posRow['rgt'] < $lastRgt)) {
            break;
        }

        $lastDepth = (int) $posRow['depth'];
        $lastLft = (int) $posRow['lft'];
        $lastRgt = (int) $posRow['rgt'];
        $nestedNodes[] = $posRow;
    }

    /* We can only migrate the thread tree, if we have determined exactly 2 or 3 nested nodes with the same creation date */
    if (count($nestedNodes) < 2 || count($nestedNodes) > 3) {
        $GLOBALS['ilLog']->info(sprintf(
            "Cannot/Don't need to fix parent relation in thread id %s because there aren't " .
            "2 (or 3, if the migration has been already executed in ILIAS 6 a 2nd time) nested postings with the same creation date",
            $rootRow['thr_fk']
        ));
        continue;
    }

    // This is the new/real root node!!!
    $newRoot = $nestedNodes[1];

    /* Repair the parent_pos of the nodes (broken during migration in 5.4.x and 6) */
    $query = '
        UPDATE frm_posts_tree
        SET parent_pos = %s
        WHERE parent_pos = %s AND fpt_pk = %s
        ';
    $ilDB->manipulateF(
        $query,
        ['integer', 'integer', 'integer'],
        [$rootRow['pos_pk'], $rootRow['fpt_pk'], $newRoot['fpt_pk']]
    );

    $GLOBALS['ilLog']->info(sprintf(
        "Set 'parent_pos' value of posting node with fpt_pk:%s (pos_fk:%s) to %s",
        $newRoot['fpt_pk'],
        $newRoot['pos_pk'],
        $rootRow['pos_pk']
    ));

    if (isset($nestedNodes[2])) {
        // This is the the first real node in UI!!!
        $firstVisibleEntry = $nestedNodes[2];
        $ilDB->manipulateF(
            $query,
            ['integer', 'integer', 'integer'],
            [$newRoot['pos_pk'], $newRoot['fpt_pk'], $firstVisibleEntry['fpt_pk']]
        );
        $GLOBALS['ilLog']->info(sprintf(
            "Set 'parent_pos' value of posting node with fpt_pk:%s (pos_fk:%s) to %s",
            $firstVisibleEntry['fpt_pk'],
            $firstVisibleEntry['pos_pk'],
            $newRoot['pos_pk']
        ));
    }
}

$GLOBALS['ilLog']->info(sprintf(
    "Finished migration of wrong parent relation value"
));
?>
<#32>
<?php
$setting = new ilSetting();
$migrationExecutionTsAfterBugfix = $setting->get('ilfrmtreemigr_6_af', null);
$hotfixstep = 32;

if (!is_numeric($migrationExecutionTsAfterBugfix)) {
    /* Determine threads which cannot be migrated because they are not valid (just to make the script robust) */
    $GLOBALS['ilLog']->info(sprintf(
        "Started migration of forum thread trees to remove the (potentially) wrong root node (Hotfix Step: %s)",
        $hotfixstep
    ));

    $query = "
            SELECT frmpt.thr_fk
            FROM frm_posts_tree frmpt
            INNER JOIN frm_posts fp ON fp.pos_pk = frmpt.pos_fk
            WHERE frmpt.parent_pos = 0
            GROUP BY frmpt.thr_fk
            HAVING COUNT(frmpt.fpt_pk) > 1
        ";
    $ignoredThreadIds = [];
    $res = $ilDB->query($query);
    while ($row = $ilDB->fetchAssoc($res)) {
        $ignoredThreadIds[$row['thr_fk']] = $row['thr_fk'];
    }

    $query = "
            SELECT *
            FROM frm_posts_tree fpt
            INNER JOIN frm_posts fp ON fp.pos_pk = fpt.pos_fk
            WHERE fpt.parent_pos = 0 AND fpt.depth = 1
        ";
    $res = $ilDB->query($query);
    while ($rootRow = $ilDB->fetchAssoc($res)) {
        $GLOBALS['ilLog']->info(sprintf(
            "Started migration of thread with id %s",
            $rootRow['thr_fk']
        ));
        if (isset($ignoredThreadIds[$rootRow['thr_fk']])) {
            $GLOBALS['ilLog']->warning(sprintf(
                "Cannot migrate forum thread tree with id %s because this thread tree has multiple root node postings ...",
                $rootRow['thr_fk']
            ));
            continue;
        }

        $nestedNodes = [$rootRow];

        $query = '
            SELECT *
            FROM frm_posts_tree fpt
            INNER JOIN frm_posts fp
                ON fp.pos_pk = fpt.pos_fk
            WHERE 
            fpt.thr_fk = %s AND
            fp.pos_date = %s AND
            fpt.pos_fk != %s AND
            fpt.depth BETWEEN 1 AND 3
            ORDER BY fpt.depth
        ';
        $posRes = $ilDB->queryF(
            $query,
            ['integer', 'timestamp', 'integer',],
            [$rootRow['thr_fk'], $rootRow['pos_date'], $rootRow['pos_fk']]
        );
        $lastDepth = (int) $rootRow['depth'];
        $lastParent = (int) $rootRow['pos_fk'];

        while ($posRow = $ilDB->fetchAssoc($posRes)) {
            if ((int) $posRow['depth'] !== ($lastDepth + 1)) {
                break;
            }

            if ((int) $posRow['parent_pos'] !== $lastParent) {
                break;
            }

            $lastDepth = (int) $posRow['depth'];
            $lastParent = (int) $posRow['pos_fk'];
            $nestedNodes[] = $posRow;
        }

        /* We can only migrate the thread tree, if we have determined exactly 3 nested nodes with the same creation date */
        if (count($nestedNodes) !== 3) {
            $GLOBALS['ilLog']->info(sprintf(
                "Cannot/Don't need to migrate forum tree for thread id %s because there aren't " .
                "3 nested postings with the same creation date",
                $rootRow['thr_fk']
            ));
            continue;
        }

        // This is the new root node!!!
        $newRoot = $nestedNodes[1];

        $GLOBALS['ilLog']->info(sprintf(
            "Current/Wrong root node in thread with id %s -> fpt_pk:%s / pos_fk:%s / parent_pos:%s / depth:%s",
            $rootRow['thr_fk'],
            $rootRow['fpt_pk'],
            $rootRow['pos_pk'],
            $rootRow['parent_pos'],
            $rootRow['depth']
        ));

        $GLOBALS['ilLog']->info(sprintf(
            "Determined new root node for thread with id %s -> fpt_pk:%s / pos_fk:%s / parent_pos:%s / depth:%s",
            $newRoot['thr_fk'],
            $newRoot['fpt_pk'],
            $newRoot['pos_pk'],
            $newRoot['parent_pos'],
            $newRoot['depth']
        ));

        $ilAtomQuery = $ilDB->buildAtomQuery();
        $ilAtomQuery->addTableLock('frm_posts');
        $ilAtomQuery->addTableLock('frm_posts_tree');

        $ilAtomQuery->addQueryCallable(static function (ilDBInterface $ilDB) use ($rootRow, $newRoot) {
            /* Now, fetch all children of the current (wrong) root node */
            $query = '
                SELECT *
                FROM frm_posts_tree
                INNER JOIN frm_posts
                    ON frm_posts.pos_pk = frm_posts_tree.pos_fk
                WHERE frm_posts_tree.thr_fk = %s AND frm_posts_tree.parent_pos = %s AND frm_posts.pos_pk != %s
                ORDER BY frm_posts.pos_date ASC
            ';
            $rootsChildrenRes = $ilDB->queryF(
                $query,
                ['integer', 'integer', 'integer'],
                [$rootRow['thr_fk'], $rootRow['pos_pk'], $newRoot['pos_pk']]
            );

            $numChildren = $ilDB->numRows($rootsChildrenRes);
            if ($numChildren > 0) {
                $GLOBALS['ilLog']->info(sprintf(
                    "We have to move %s sub trees (authored by users since the ILIAS 6 migration) to the original root for thread with id %s",
                    $numChildren,
                    $rootRow['thr_fk']
                ));
            }

            while ($child = $ilDB->fetchAssoc($rootsChildrenRes)) {
                $newRootTreeQuery = "
                    SELECT frm_posts_tree.*
                    FROM frm_posts_tree
                    INNER JOIN frm_posts ON frm_posts.pos_pk = frm_posts_tree.pos_fk
                    WHERE frm_posts_tree.pos_fk = %s
                ";
                $newRootTreeRes = $ilDB->queryF($newRootTreeQuery, ['integer'], [$newRoot['pos_pk']]);
                $newRootTree = $ilDB->fetchAssoc($newRootTreeRes);

                $a_target_id = $newRoot['pos_pk'];
                $target_lft = $newRootTree['lft'];
                $target_rgt = $newRootTree['rgt'];
                $target_depth = $newRootTree['depth'];

                $source_lft = $child['lft'];
                $source_rgt = $child['rgt'];
                $source_depth = $child['depth'];
                $source_parent = $child['parent_pos'];

                $spread_diff = $source_rgt - $source_lft + 1;

                /* Spread the thread tree */
                $query = '
                    UPDATE frm_posts_tree
                    SET 
                        lft = CASE WHEN lft >  %s THEN lft + %s ELSE lft END,
                        rgt = CASE WHEN rgt >= %s THEN rgt + %s ELSE rgt END
                    WHERE thr_fk = %s 
                ';
                $ilDB->manipulateF(
                    $query,
                    [
                        'integer',
                        'integer',
                        'integer',
                        'integer',
                        'integer'
                    ],
                    [
                        $target_lft,
                        $spread_diff,
                        $target_lft,
                        $spread_diff,
                        $newRoot['thr_fk']
                    ]
                );

                /* Move nodes */
                if ($source_lft > $target_rgt) {
                    $where_offset = $spread_diff;
                    $move_diff = $target_lft - $source_lft - $spread_diff + 1;
                } else {
                    $where_offset = 0;
                    $move_diff = $target_lft - $source_lft + 1;
                }
                $depth_diff = $target_depth - $source_depth + 1;

                $query = '
                    UPDATE frm_posts_tree
                    SET 
                        parent_pos = CASE WHEN parent_pos = %s THEN %s ELSE parent_pos END, 
                        rgt = rgt + %s,
                        lft = lft + %s,
                        depth = depth + %s
                    WHERE lft >= %s AND rgt <= %s AND thr_fk = %s 
                ';
                $ilDB->manipulateF(
                    $query,
                    [
                        'integer',
                        'integer',
                        'integer',
                        'integer',
                        'integer',
                        'integer',
                        'integer',
                        'integer'
                    ],
                    [
                        $source_parent,
                        $a_target_id,
                        $move_diff,
                        $move_diff,
                        $depth_diff,
                        $source_lft + $where_offset,
                        $source_rgt + $where_offset,
                        $newRoot['thr_fk']
                    ]
                );

                /* Close gabs  */
                $query = '
                    UPDATE frm_posts_tree
                    SET 
                        lft = CASE WHEN lft >= %s THEN lft - %s ELSE lft END,
                        rgt = CASE WHEN rgt >= %s THEN rgt - %s ELSE rgt END
                    WHERE thr_fk = %s 
                ';
                $ilDB->manipulateF(
                    $query,
                    [
                        'integer',
                        'integer',
                        'integer',
                        'integer',
                        'integer'
                    ],
                    [
                        $source_lft + $where_offset,
                        $spread_diff,
                        $source_rgt + $where_offset,
                        $spread_diff,
                        $newRoot['thr_fk']
                    ]
                );
            }

            /* DELETE the current root from 'frm_posts_tree' AND 'frm_posts' */
            $query = '
                DELETE FROM frm_posts_tree
                WHERE fpt_pk = %s 
                ';
            $ilDB->manipulateF($query, ['integer'], [$rootRow['fpt_pk']]);
            $query = '
                DELETE FROM frm_posts
                WHERE pos_pk = %s 
                ';
            $ilDB->manipulateF($query, ['integer'], [$rootRow['pos_pk']]);
            $GLOBALS['ilLog']->info(sprintf(
                "Deleted wrong root node from 'frm_posts_tree' AND 'frm_posts' for thread with id %s",
                $rootRow['thr_fk']
            ));

            /* Fix depth of all nodes */
            $query = '
                UPDATE frm_posts_tree
                SET depth = depth -1,
                lft = lft - 1,
                rgt = rgt - 1
                WHERE thr_fk = %s 
                ';
            $ilDB->manipulateF(
                $query,
                [
                    'integer'
                ],
                [
                    $rootRow['thr_fk']
                ]
            );
            $GLOBALS['ilLog']->info(sprintf(
                "Decremented lft, rgt and depth for all tree nodes in thread %s",
                $rootRow['thr_fk']
            ));

            /* Fix parent of correct root node */
            $query = '
                UPDATE frm_posts_tree
                SET parent_pos = %s
                WHERE fpt_pk = %s
                ';
            $ilDB->manipulateF(
                $query,
                [
                    'integer',
                    'integer'
                ],
                [
                    0,
                    $newRoot['fpt_pk'],
                ]
            );
            $GLOBALS['ilLog']->info(sprintf(
                "Set 'parent_pos' value to 0 for root node posting with fpt_pk:%s (pos_pk:%s) in thread %s",
                $newRoot['fpt_pk'],
                $newRoot['pos_pk'],
                $rootRow['thr_fk']
            ));
        });
        $ilAtomQuery->run();
    }

    $GLOBALS['ilLog']->info(sprintf(
        "Finished migration of forum thread trees"
    ));
    $setting->set('ilfrmtreemigr_6_hf', time());
} else {
    $GLOBALS['ilLog']->info(sprintf(
        "Ignored forum tree migration because it is not necessary (Hotfix step: %s)",
        $hotfixstep
    ));
}
?>
<#33>
<?php
if (!$ilDB->indexExistsByFields('booking_object', array('pool_id'))) {
    $ilDB->addIndex('booking_object', array('pool_id'), 'i1');
}
?>
<#34>
<?php
if (!$ilDB->indexExistsByFields('il_object_subobj', array('subobj'))) {
    $ilDB->addIndex('il_object_subobj', array('subobj'), 'i1');
}
?>
<#35>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#36>
<?php
if (!$ilDB->indexExistsByFields('tax_tree', ['child'])) {
    $ilDB->addIndex('tax_tree', ['child'], 'i1');
}
?>
<#37>
<?php
    $ilCtrlStructureReader->getStructure();
?>
<#38>
<?php
$set = $ilDB->queryF("SELECT availability_id FROM pdfgen_renderer_avail " .
    " WHERE renderer = %s AND service = %s AND purpose = %s",
    ["text", "text", "text"],
    ["PhantomJS", "Survey", "Results"]
);
if (!$ilDB->fetchAssoc($set)) {
    $ilDB->insert("pdfgen_renderer_avail", [
        "availability_id" => ["integer", $ilDB->nextId('pdfgen_renderer_avail')],
        "renderer" => ["text", "PhantomJS"],
        "service" => ["text", "Survey"],
        "purpose" => ["text", "Results"]
    ]);
}
?>
<#39>
<?php
$set = $ilDB->queryF("SELECT availability_id FROM pdfgen_renderer_avail " .
    " WHERE renderer = %s AND service = %s AND purpose = %s",
    ["text", "text", "text"],
    ["WkhtmlToPdf", "Survey", "Results"]
);
if (!$ilDB->fetchAssoc($set)) {
    $ilDB->insert("pdfgen_renderer_avail", [
        "availability_id" => ["integer", $ilDB->nextId('pdfgen_renderer_avail')],
        "renderer" => ["text", "WkhtmlToPdf"],
        "service" => ["text", "Survey"],
        "purpose" => ["text", "Results"]
    ]);
}
?>
<#40>
<?php
// deleted
?>
<#41>
<?php
global $DIC;
$DIC->database()->modifyTableColumn("usr_data", "login", [
    "type" => \ilDBConstants::T_TEXT,
    "length" => 190,
    "notnull" => false,
    "fixed" => false
]);
?>
<#42>
<?php
    $ilCtrlStructureReader->getStructure();
?>
<#43>
<?php
if($ilDB->tableExists('cmix_lrs_types'))
{
    if ( !$ilDB->tableColumnExists('cmix_lrs_types', 'only_moveon') ) {
        $ilDB->addTableColumn('cmix_lrs_types', 'only_moveon', array(
            'type' => 'integer',
            'length' => 1,
            'notnull' => true,
            'default' => 0
        ));
    }
    if ( !$ilDB->tableColumnExists('cmix_lrs_types', 'achieved') ) {
        $ilDB->addTableColumn('cmix_lrs_types', 'achieved', array(
            'type' => 'integer',
            'length' => 1,
            'notnull' => true,
            'default' => 1
        ));
    }

    if ( !$ilDB->tableColumnExists('cmix_lrs_types', 'answered') ) {
        $ilDB->addTableColumn('cmix_lrs_types', 'answered', array(
            'type' => 'integer',
            'length' => 1,
            'notnull' => true,
            'default' => 1
        ));
    }

    if ( !$ilDB->tableColumnExists('cmix_lrs_types', 'completed') ) {
        $ilDB->addTableColumn('cmix_lrs_types', 'completed', array(
            'type' => 'integer',
            'length' => 1,
            'notnull' => true,
            'default' => 1
        ));
    }

    if ( !$ilDB->tableColumnExists('cmix_lrs_types', 'failed') ) {
        $ilDB->addTableColumn('cmix_lrs_types', 'failed', array(
            'type' => 'integer',
            'length' => 1,
            'notnull' => true,
            'default' => 1
        ));
    }

    if ( !$ilDB->tableColumnExists('cmix_lrs_types', 'initialized') ) {
        $ilDB->addTableColumn('cmix_lrs_types', 'initialized', array(
            'type' => 'integer',
            'length' => 1,
            'notnull' => true,
            'default' => 1
        ));
    }

    if ( !$ilDB->tableColumnExists('cmix_lrs_types', 'passed') ) {
        $ilDB->addTableColumn('cmix_lrs_types', 'passed', array(
            'type' => 'integer',
            'length' => 1,
            'notnull' => true,
            'default' => 1
        ));
    }

    if ( !$ilDB->tableColumnExists('cmix_lrs_types', 'progressed') ) {
        $ilDB->addTableColumn('cmix_lrs_types', 'progressed', array(
            'type' => 'integer',
            'length' => 1,
            'notnull' => true,
            'default' => 1
        ));
    }

    if ( !$ilDB->tableColumnExists('cmix_lrs_types', 'satisfied') ) {
        $ilDB->addTableColumn('cmix_lrs_types', 'satisfied', array(
            'type' => 'integer',
            'length' => 1,
            'notnull' => true,
            'default' => 1
        ));
    }

    if ( !$ilDB->tableColumnExists('cmix_lrs_types', 'c_terminated') ) {
        $ilDB->addTableColumn('cmix_lrs_types', 'c_terminated', array(
            'type' => 'integer',
            'length' => 1,
            'notnull' => true,
            'default' => 1
        ));
    }

    if ( !$ilDB->tableColumnExists('cmix_lrs_types', 'hide_data') ) {
        $ilDB->addTableColumn('cmix_lrs_types', 'hide_data', array(
            'type' => 'integer',
            'length' => 1,
            'notnull' => true,
            'default' => 0
        ));
    }

    if ( !$ilDB->tableColumnExists('cmix_lrs_types', 'c_timestamp') ) {
        $ilDB->addTableColumn('cmix_lrs_types', 'c_timestamp', array(
            'type' => 'integer',
            'length' => 1,
            'notnull' => true,
            'default' => 0
        ));
    }

    if ( !$ilDB->tableColumnExists('cmix_lrs_types', 'duration') ) {
        $ilDB->addTableColumn('cmix_lrs_types', 'duration', array(
            'type' => 'integer',
            'length' => 1,
            'notnull' => true,
            'default' => 1
        ));
    }

    if ( !$ilDB->tableColumnExists('cmix_lrs_types', 'no_substatements') ) {
        $ilDB->addTableColumn('cmix_lrs_types', 'no_substatements', array(
            'type' => 'integer',
            'length' => 1,
            'notnull' => true,
            'default' => 0
        ));
    }
}
?>
<#44>
<?php
if($ilDB->tableExists('cmix_settings'))
{
    if ( !$ilDB->tableColumnExists('cmix_settings', 'only_moveon') ) {
        $ilDB->addTableColumn('cmix_settings', 'only_moveon', array(
            'type' => 'integer',
            'length' => 1,
            'notnull' => true,
            'default' => 0
        ));
    }
    if ( !$ilDB->tableColumnExists('cmix_settings', 'achieved') ) {
        $ilDB->addTableColumn('cmix_settings', 'achieved', array(
            'type' => 'integer',
            'length' => 1,
            'notnull' => true,
            'default' => 1
        ));
    }

    if ( !$ilDB->tableColumnExists('cmix_settings', 'answered') ) {
        $ilDB->addTableColumn('cmix_settings', 'answered', array(
            'type' => 'integer',
            'length' => 1,
            'notnull' => true,
            'default' => 1
        ));
    }

    if ( !$ilDB->tableColumnExists('cmix_settings', 'completed') ) {
        $ilDB->addTableColumn('cmix_settings', 'completed', array(
            'type' => 'integer',
            'length' => 1,
            'notnull' => true,
            'default' => 1
        ));
    }

    if ( !$ilDB->tableColumnExists('cmix_settings', 'failed') ) {
        $ilDB->addTableColumn('cmix_settings', 'failed', array(
            'type' => 'integer',
            'length' => 1,
            'notnull' => true,
            'default' => 1
        ));
    }

    if ( !$ilDB->tableColumnExists('cmix_settings', 'initialized') ) {
        $ilDB->addTableColumn('cmix_settings', 'initialized', array(
            'type' => 'integer',
            'length' => 1,
            'notnull' => true,
            'default' => 1
        ));
    }

    if ( !$ilDB->tableColumnExists('cmix_settings', 'passed') ) {
        $ilDB->addTableColumn('cmix_settings', 'passed', array(
            'type' => 'integer',
            'length' => 1,
            'notnull' => true,
            'default' => 1
        ));
    }

    if ( !$ilDB->tableColumnExists('cmix_settings', 'progressed') ) {
        $ilDB->addTableColumn('cmix_settings', 'progressed', array(
            'type' => 'integer',
            'length' => 1,
            'notnull' => true,
            'default' => 1
        ));
    }

    if ( !$ilDB->tableColumnExists('cmix_settings', 'satisfied') ) {
        $ilDB->addTableColumn('cmix_settings', 'satisfied', array(
            'type' => 'integer',
            'length' => 1,
            'notnull' => true,
            'default' => 1
        ));
    }

    if ( !$ilDB->tableColumnExists('cmix_settings', 'c_terminated') ) {
        $ilDB->addTableColumn('cmix_settings', 'c_terminated', array(
            'type' => 'integer',
            'length' => 1,
            'notnull' => true,
            'default' => 1
        ));
    }

    if ( !$ilDB->tableColumnExists('cmix_settings', 'hide_data') ) {
        $ilDB->addTableColumn('cmix_settings', 'hide_data', array(
            'type' => 'integer',
            'length' => 1,
            'notnull' => true,
            'default' => 0
        ));
    }

    if ( !$ilDB->tableColumnExists('cmix_settings', 'c_timestamp') ) {
        $ilDB->addTableColumn('cmix_settings', 'c_timestamp', array(
            'type' => 'integer',
            'length' => 1,
            'notnull' => true,
            'default' => 0
        ));
    }

    if ( !$ilDB->tableColumnExists('cmix_settings', 'duration') ) {
        $ilDB->addTableColumn('cmix_settings', 'duration', array(
            'type' => 'integer',
            'length' => 1,
            'notnull' => true,
            'default' => 1
        ));
    }

    if ( !$ilDB->tableColumnExists('cmix_settings', 'no_substatements') ) {
        $ilDB->addTableColumn('cmix_settings', 'no_substatements', array(
            'type' => 'integer',
            'length' => 1,
            'notnull' => true,
            'default' => 0
        ));
    }
}
?>
<#45>
<?php
if (!$ilDB->tableColumnExists('ldap_server_settings', 'escape_dn')) {
    $ilDB->addTableColumn(
        'ldap_server_settings',
        'escape_dn',
        [
            'type' => ilDBConstants::T_INTEGER,
            'length' => 1,
            'notnull' => true,
            'default' => 0
        ]
    );
}
?>
<#46>
<?php
    //
?>
<#47>
<?php
if (!$ilDB->indexExistsByFields('exc_returned', array('filetitle'))) {
    $ilDB->addIndex('exc_returned', array('filetitle'), 'i3');
}
?>
<#48>
<?php
if ($ilDB->uniqueConstraintExists('cmi_gobjective', array('user_id','objective_id','scope_id'))) {
    $ilDB->dropUniqueConstraintByFields('cmi_gobjective', array('user_id','objective_id','scope_id'));
}
$query = "show index from cmi_gobjective where Key_name = 'PRIMARY'";
$res = $ilDB->query($query);
if (!$ilDB->numRows($res)) {
    $ilDB->addPrimaryKey('cmi_gobjective', array('user_id', 'scope_id', 'objective_id'));
}
?>
<#49>
<?php
if ($ilDB->uniqueConstraintExists('cp_suspend', array('user_id','obj_id'))) {
    $ilDB->dropUniqueConstraintByFields('cp_suspend', array('user_id','obj_id'));
}
$query = "show index from cp_suspend where Key_name = 'PRIMARY'";
$res = $ilDB->query($query);
if (!$ilDB->numRows($res)) {
    $ilDB->addPrimaryKey('cp_suspend', array('user_id', 'obj_id'));
}
?>
<#50>
<?php
//get all cmix objekts with read_learning_progress
$read_learning_progress = 0;
$read_outcomes = 0;
$res = $ilDB->queryF(
    "SELECT ops_id FROM rbac_operations WHERE operation = %s",
    array('text'),
    array('read_learning_progress')
    );
while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
    $read_learning_progress = $row->ops_id;
}
$res = $ilDB->queryF(
    "SELECT ops_id FROM rbac_operations WHERE operation = %s",
    array('text'),
    array('read_outcomes')
    );
while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
    $read_outcomes = $row->ops_id;
}
if ($read_outcomes > 0 && $read_learning_progress > 0) {
    $res = $ilDB->queryF(
        "SELECT rol_id, parent, type FROM rbac_templates WHERE (type=%s OR type=%s) AND ops_id=%s",
        array('text', 'text', 'integer'),
        array('cmix', 'lti', $read_learning_progress)
        );
    while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
        $resnum = $ilDB->queryF(
            "SELECT rol_id FROM rbac_templates WHERE rol_id = %s AND type = %s AND ops_id = %s AND parent = %s",
            array('integer', 'text', 'integer', 'integer'),
            array($row->rol_id, $row->type, $read_outcomes, $row->parent)
        );
        if (!$ilDB->numRows($resnum)) {
            $ilDB->insert('rbac_templates', array(
                    'rol_id' => array('integer', $row->rol_id),
                    'type' => array('text', $row->type),
                    'ops_id' => array('integer', $read_outcomes),
                    'parent' => array('integer', $row->parent)
                ));
        }
    }
}
?>
<#51>
<?php
$ilDB->update("rbac_operations", [
    "op_order" => ["integer", 3900]
], [    // where
        "operation" => ["text", "redact"]
    ]
);
?>
<#52>
<?php
if (!$ilDB->indexExistsByFields('booking_reservation', array('date_from'))) {
    $ilDB->addIndex('booking_reservation', array('date_from'), 'i3');
}
?>
<#53>
<?php
if (!$ilDB->indexExistsByFields('booking_reservation', array('date_to'))) {
    $ilDB->addIndex('booking_reservation', array('date_to'), 'i4');
}
?>
<#54>
<?php
if (!$ilDB->indexExistsByFields('il_meta_oer_stat', ['obj_id'])) {
	$ilDB->addPrimaryKey('il_meta_oer_stat', ['obj_id']);
}
?>
<#55>
<?php
if (!$ilDB->tableColumnExists('il_bt_value', 'position')) {
    $ilDB->addTableColumn(
        'il_bt_value',
        'position',
        [
            'type' => ilDBConstants::T_INTEGER,
            'length' => 8,
            'notnull' => true,
            'default' => 0
        ]
    );
}
?>
<#56>
<?php
if (!$ilDB->indexExistsByFields('il_bt_value', array('bucket_id'))) {
    $ilDB->addIndex(
        'il_bt_value',
        array('bucket_id'),
        'i1'
    );
}
if (!$ilDB->indexExistsByFields('il_bt_value_to_task', array('task_id'))) {
    $ilDB->addIndex(
        'il_bt_value_to_task',
        array('task_id'),
        'i1'
    );
}
if (!$ilDB->indexExistsByFields('il_bt_value_to_task', array('value_id'))) {
    $ilDB->addIndex(
        'il_bt_value_to_task',
        array('value_id'),
        'i2'
    );
}
?>
<#57>
<?php
if (!$ilDB->tableColumnExists('il_bt_value_to_task', 'position')) {
    $ilDB->addTableColumn(
        'il_bt_value_to_task',
        'position',
        [
            'type' => ilDBConstants::T_INTEGER,
            'length' => 8,
            'notnull' => true,
            'default' => 0
        ]
    );
}
?>
