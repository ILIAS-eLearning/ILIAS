<#1>
<?php

if( !$ilDB->tableExists('adv_md_values_text') )
{
	$ilDB->renameTable('adv_md_values', 'adv_md_values_text');
}

?>
<#2>
<?php

if( !$ilDB->tableExists('adv_md_values_int') )
{
	$ilDB->createTable('adv_md_values_int', array(
		'obj_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		),
		'sub_type' => array(
			'type' => 'text',
			'length' => 10,
			'notnull' => true,
			'default' => "-"
		),
		'sub_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		),
		'field_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		),
		'value' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => false
		)	
	));
		
	$ilDB->addPrimaryKey('adv_md_values_int', array('obj_id', 'sub_type', 'sub_id'));
}

?>
<#3>
<?php

if( !$ilDB->tableExists('adv_md_values_float') )
{
	$ilDB->createTable('adv_md_values_float', array(
		'obj_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		),
		'sub_type' => array(
			'type' => 'text',
			'length' => 10,
			'notnull' => true,
			'default' => "-"
		),
		'sub_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		),
		'field_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		),
		'value' => array(
			'type' => 'float',			
			'notnull' => false
		)	
	));
		
	$ilDB->addPrimaryKey('adv_md_values_float', array('obj_id', 'sub_type', 'sub_id'));
}

?>
<#4>
<?php

if( !$ilDB->tableExists('adv_md_values_date') )
{
	$ilDB->createTable('adv_md_values_date', array(
		'obj_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		),
		'sub_type' => array(
			'type' => 'text',
			'length' => 10,
			'notnull' => true,
			'default' => "-"
		),
		'sub_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		),
		'field_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		),
		'value' => array(
			'type' => 'date',			
			'notnull' => false
		)	
	));
		
	$ilDB->addPrimaryKey('adv_md_values_date', array('obj_id', 'sub_type', 'sub_id'));
}

?>
<#5>
<?php

if( !$ilDB->tableExists('adv_md_values_datetime') )
{
	$ilDB->createTable('adv_md_values_datetime', array(
		'obj_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		),
		'sub_type' => array(
			'type' => 'text',
			'length' => 10,
			'notnull' => true,
			'default' => "-"
		),
		'sub_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		),
		'field_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		),
		'value' => array(
			'type' => 'timestamp',			
			'notnull' => false
		)	
	));
		
	$ilDB->addPrimaryKey('adv_md_values_datetime', array('obj_id', 'sub_type', 'sub_id'));
}

?>
<#6>
<?php

if( !$ilDB->tableExists('adv_md_values_location') )
{
	$ilDB->createTable('adv_md_values_location', array(
		'obj_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		),
		'sub_type' => array(
			'type' => 'text',
			'length' => 10,
			'notnull' => true,
			'default' => "-"
		),
		'sub_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		),
		'field_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		),
		'loc_lat' => array(
			'type' => 'float',			
			'notnull' => false
		),
		'loc_long' => array(
			'type' => 'float',			
			'notnull' => false
		),
		'loc_zoom' => array(
			'type' => 'integer',			
			'length' => 1,
			'notnull' => false
		)	
	));
		
	$ilDB->addPrimaryKey('adv_md_values_location', array('obj_id', 'sub_type', 'sub_id'));
}

?>
<#7>
<?php

	if (!$ilDB->tableColumnExists('adv_md_values_location', 'disabled'))
	{		
		$ilDB->addTableColumn('adv_md_values_location', 'disabled', array(
			"type" => "integer",
			"length" => 1,
			"notnull" => true,
			"default" => 0
		));
	}
	if (!$ilDB->tableColumnExists('adv_md_values_datetime', 'disabled'))
	{		
		$ilDB->addTableColumn('adv_md_values_datetime', 'disabled', array(
			"type" => "integer",
			"length" => 1,
			"notnull" => true,
			"default" => 0
		));
	}
	if (!$ilDB->tableColumnExists('adv_md_values_date', 'disabled'))
	{		
		$ilDB->addTableColumn('adv_md_values_date', 'disabled', array(
			"type" => "integer",
			"length" => 1,
			"notnull" => true,
			"default" => 0
		));
	}
	if (!$ilDB->tableColumnExists('adv_md_values_float', 'disabled'))
	{		
		$ilDB->addTableColumn('adv_md_values_float', 'disabled', array(
			"type" => "integer",
			"length" => 1,
			"notnull" => true,
			"default" => 0
		));
	}
	if (!$ilDB->tableColumnExists('adv_md_values_int', 'disabled'))
	{		
		$ilDB->addTableColumn('adv_md_values_int', 'disabled', array(
			"type" => "integer",
			"length" => 1,
			"notnull" => true,
			"default" => 0
		));
	}
	
?>
<#8>
<?php

// #6/#7/#8

$ilDB->dropPrimaryKey('adv_md_values_int');
$ilDB->addPrimaryKey('adv_md_values_int', array('obj_id', 'sub_type', 'sub_id', 'field_id'));
$ilDB->dropPrimaryKey('adv_md_values_float');
$ilDB->addPrimaryKey('adv_md_values_float', array('obj_id', 'sub_type', 'sub_id', 'field_id'));
$ilDB->dropPrimaryKey('adv_md_values_date');
$ilDB->addPrimaryKey('adv_md_values_date', array('obj_id', 'sub_type', 'sub_id', 'field_id'));
$ilDB->dropPrimaryKey('adv_md_values_datetime');
$ilDB->addPrimaryKey('adv_md_values_datetime', array('obj_id', 'sub_type', 'sub_id', 'field_id'));
$ilDB->dropPrimaryKey('adv_md_values_location');
$ilDB->addPrimaryKey('adv_md_values_location', array('obj_id', 'sub_type', 'sub_id', 'field_id'));

?>
<#9>
<?php
if(!$ilDB->tableExists('orgu_types')) {
    $fields = array (
        'id'    => array ('type' => 'integer', 'length'  => 4,'notnull' => true, 'default' => 0),
        'default_lang'   => array ('type' => 'text', 'notnull' => true, 'length' => 4, 'fixed' => false),
        'icon'    => array ('type' => 'text', 'length'  => 256, 'notnull' => false),
        'owner' => array('type' => 'integer', 'notnull' => true, 'length' => 4),
        'create_date'  => array ('type' => 'timestamp'),
        'last_update' => array('type' => 'timestamp'),
    );
    $ilDB->createTable('orgu_types', $fields);
    $ilDB->addPrimaryKey('orgu_types', array('id'));
    $ilDB->createSequence('orgu_types');
}
?>
<#10>
<?php
if(!$ilDB->tableExists('orgu_data')) {
    $fields = array (
        'orgu_id'    => array ('type' => 'integer', 'length'  => 4,'notnull' => true, 'default' => 0),
        'orgu_type_id'   => array ('type' => 'integer', 'notnull' => false, 'length' => 4),
    );
    $ilDB->createTable('orgu_data', $fields);
    $ilDB->addPrimaryKey('orgu_data', array('orgu_id'));
}
?>
<#11>
<?php
if(!$ilDB->tableExists('orgu_types_trans')) {
    $fields = array (
        'orgu_type_id'    => array ('type' => 'integer', 'length'  => 4,'notnull' => true),
        'lang'   => array ('type' => 'text', 'notnull' => true, 'length' => 4),
        'member'    => array ('type' => 'text', 'length'  => 32, 'notnull' => true),
        'value' => array('type' => 'text', 'length' => 4000, 'notnull' => false),
    );
    $ilDB->createTable('orgu_types_trans', $fields);
    $ilDB->addPrimaryKey('orgu_types_trans', array('orgu_type_id', 'lang', 'member'));
}
?>
<#12>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#13>
<?php
if(!$ilDB->tableExists('orgu_types_adv_md_rec')) {
    $fields = array (
        'type_id'    => array ('type' => 'integer', 'length'  => 4,'notnull' => true),
        'rec_id'   => array ('type' => 'integer', 'notnull' => true, 'length' => 4),
    );
    $ilDB->createTable('orgu_types_adv_md_rec', $fields);
    $ilDB->addPrimaryKey('orgu_types_adv_md_rec', array('type_id', 'rec_id'));
}
?>
<#14>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#15>
<?php

// Automail-Info for the Generali

$fields = array (
     'crs_id' => array(
         'type' => 'integer',
         'length' => 4,
         'notnull' => true
         ),
     'mail_id' => array(
         "type" => "text",
         "length" => "50",
         "notnull" => true
         ),
     'last_send' => array(
         'type' => 'integer',
         'length' => 4,
         'notnull' => true,
         )
);

$ilDB->createTable('gev_automail_info', $fields);
$ilDB->addPrimaryKey('gev_automail_info', array('crs_id', 'mail_id'));

?>

<#16>
<?php

$fields = array (
     'id' => array(
         'type' => 'integer',
         'length' => 4,
         'notnull' => true
         ),
     'obj_id' => array(
         'type' => 'integer',
         'length' => 4,
         'notnull' => true
         ),
     'moment' => array(
         'type' => 'integer',
         'length' => 4,
         'notnull' => true
         ),
     'occasion' => array(
         "type" => "text",
         "length" => "100",
         "notnull" => true
         ),
     'mail_to' => array(
         "type" => "text",
         "length" => "200",
         "notnull" => true
         ),
     'mail_from' => array(
         "type" => "text",
         "length" => "200",
         "notnull" => true
         ),
     'cc' => array(
         "type" => "clob"
         ),
     'bcc' => array(
         "type" => "clob"
         ),
     'subject' => array(
         "type" => "text",
         "length" => "200",
         "notnull" => true
         ),
     'message' => array(
         "type" => "clob"
         ),
     'attachments' => array(
         "type" => "clob"
         )
);

$ilDB->createTable('mail_log', $fields);
$ilDB->addPrimaryKey('mail_log', array('id'));
$ilDB->createSequence("mail_log");

?>

<#17>
<?php

$fields = array (
     'obj_id' => array(
         'type' => 'integer',
         'length' => 4,
         'notnull' => true
         ),
     'filename' => array(
         "type" => "text",
         "length" => "255",
         "notnull" => true
         ),
     'lock_count' => array(
         'type' => 'integer',
         'length' => 4,
         'notnull' => true
     )
);


$ilDB->createTable('mail_attachment_locks', $fields);

# set collation since mysql complains about the length of primary 
# key for some collations.
$ilDB->manipulate("ALTER TABLE `mail_attachment_locks` CHANGE `filename` `filename` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL ");

$ilDB->addPrimaryKey('mail_attachment_locks', array('obj_id', 'filename'));

?>

<#18>
<?php

$fields = array (
     'crs_id' => array(
         'type' => 'integer',
         'length' => 4,
         'notnull' => true
         ),
     'send_list_to_accom' => array(
         'type' => 'integer',
         'length' => 1,
         'notnull' => true
         ),
     'send_list_to_venue' => array(
         'type' => 'integer',
         'length' => 1,
         'notnull' => true
         )
);

$ilDB->createTable('gev_crs_addset', $fields);
$ilDB->addPrimaryKey('gev_crs_addset', array('crs_id'));

?>

<#19>
<?php

// INVITATION_MAIL_SETTINGS for the VoFue

$fields = array (
     'crs_id' => array(
         'type' => 'integer',
         'length' => 4,
         'notnull' => true
         ),
     'function_name' => array(
         "type" => "text",
         "length" => "50",
         "notnull" => true
         ),
     'template_id' => array(
         'type' => 'integer',
         'length' => 4,
         'notnull' => true,
         ),
     'attachments' => array(
         "type" => "clob"
         )
);

$ilDB->createTable('gev_crs_invset', $fields);
$ilDB->addPrimaryKey('gev_crs_invset', array('crs_id', 'function_name'));

?>

<#20>
<?php

$ilDB->addTableColumn( "gev_crs_addset"
					 , "inv_mailing_date"
					 , array( "type" 	=> "integer"
					 		, "length"	=> 4
					 		, "notnull"	=> true
					 		)
					 );

?>

<#21>
<?php

// GEV_USER_REGISTRATION_TOKENS for GEV

$fields = array (
     'token' => array(
         'type' => 'text',
         'length' => 32,
         'notnull' => true
         ),
     'stelle' => array(
         "type" => "text",
         "length" => 6,
         "notnull" => true
         ),
     'username' => array(
         'type' => 'text',
         'length' => 100,
         'notnull' => true
         ),
     'email' => array(
         'type' => 'text',
         'length' => 100,
         'notnull' => true
         ),
     'email_sent' => array(
         'type' => 'timestamp',
         'notnull' => false
         ),
     'token_used' => array(
         'type' => 'timestamp',
         'notnull' => false
         ),
     'password_changed' => array(
         'type' => 'timestamp',
         'notnull' => false
         ),
);

$ilDB->createTable('gev_user_reg_tokens', $fields);
$ilDB->addPrimaryKey('gev_user_reg_tokens', array('token'));

?>

<#22>
<?php

$stmt = $ilDB->prepareManip("INSERT INTO tep_type (id, title, bg_color, font_color, tep_active) VALUES (?, ?, ?, ?, ?) "
						   , array("integer", "text", "text", "text", "integer"));
$data = array( array(1, "Training", "f6000d", "000000", "0")
			 , array(2, "Veranstaltung (LD)", "f76809", "000000", "1")
			 , array(3, "Projekt/Sondermaßnahme", "b90007", "000000", "1")
			 , array(4, "Trainingsvorbereitung", "a2ff2c", "000000", "1")
			 , array(5, "Büro", "49ff00", "000000", "1")
			 , array(6, "Bereichs-/Abteilungsmeeting", "1900ff", "ffffff", "1")
			 , array(7, "Besprechung", "4effff", "000000", "1")
			 , array(8, "Besuch Infoveranstaltung", "feff98", "000000", "1")
			 , array(9, "Weiterbildung", "feff00", "000000", "1")
			 , array(10, "Urlaub/Gleittag", "e0e0e0", "000000", "1")
			 , array(11, "Feiertag im Bundesland", "c0c0c0", "000000", "1")
			 );
$ilDB->executeMultiple($stmt, $data);
$ilDB->free($stmt);
?>

<#23>
<?php

$ilDB->addTableColumn( "bill"
					 , "bill_recipient_email"
					 , array( "type" 	=> "text"
					 		, "length"	=> 255
					 		, "notnull"	=> false
					 		, "default" => null
					 		)
					 );

?>

<#24>
<?php

$ilDB->addTableColumn( "hist_course"
					 , "is_template"
					 , array( "type" 	=> "text"
					 		, "length" 	=> 8
					 		, "notnull" => false
					 		)
					 );

?>

<#25>
<?php

// Tracking Infos about cron jobs for mail

$fields = array (
    'crs_id' => array(
        'type' => 'integer',
        'length' => 4,
        'notnull' => true
        ),
    'title' => array(
        "type" => "text",
        "length" => 64,
        "notnull" => true
        ),
    'send_at' => array(
        'type' => 'timestamp',
        'notnull' => false
        )
);

$ilDB->createTable('gev_crs_dl_mail_cron', $fields);
$ilDB->addPrimaryKey('gev_crs_dl_mail_cron', array('crs_id', 'title'));

?>


<#26>
<?php

// Tracking Infos about cron jobs for mail

$fields = array (
    'crs_id' => array(
        'type' => 'integer',
        'length' => 4,
        'notnull' => true
        ),
    'mail_id' => array(
        "type" => "text",
        "length" => 64,
        "notnull" => true
        ),
    'recipient' => array(
        'type' => 'text',
        "length" => 128,
        'notnull' => false
        )
    , "occasion" => array(
        'type' => 'text',
        "length" => 256,
        'notnull' => true
    )
);

$ilDB->createTable('gev_crs_deferred_mails', $fields);
$ilDB->addPrimaryKey('gev_crs_deferred_mails', array('crs_id', 'mail_id', 'recipient'));

?>

<#27>
<?php

// Tracking relation between coupons and bills they were created for.

$fields = array (
	'bill_pk' => array(
		'type'    => 'integer',
		'length'  => 4,
		'notnull' => true,
		'default' => 0
	),
	'coupon_code' => array(
		'type'    => 'text',
		'length'  => 255,
		'notnull' => true
	)
);

$ilDB->createTable('gev_bill_coupon', $fields);
$ilDB->addPrimaryKey('gev_bill_coupon', array('bill_pk'));

?>


<#28>
<?php

// more fields in GEV_USER_REGISTRATION_TOKENS for GEV
// for sending registration mail

$ilDB->addTableColumn( "gev_user_reg_tokens"
					 , "firstname"
					 , array( "type" 	=> "text"
					 		, "length"	=> 32
					 		, "notnull"	=> true
					 		)
					 );

$ilDB->addTableColumn( "gev_user_reg_tokens"
					 , "lastname"
					 , array( "type" 	=> "text"
					 		, "length"	=> 32
					 		, "notnull"	=> true
					 		)
					 );

$ilDB->addTableColumn( "gev_user_reg_tokens"
					 , "gender"
					 , array( "type" 	=> "text"
					 		, "length"	=> 1
					 		, "notnull"	=> true
					 		)
					 );

?>

<#29>
<?php

$ilDB->renameTableColumn("hist_certfile", "function", "certfile");

?>

<#30>
<?php
$ilCtrlStructureReader->getStructure();
?>