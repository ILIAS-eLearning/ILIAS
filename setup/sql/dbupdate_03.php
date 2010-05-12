<#2949>
<?php
	// empty step
?>
<#2950>
<?php
	$ilDB->modifyTableColumn('table_properties', 'value',
		array("type" => "text", "length" => 4000, "notnull" => true));
?>
<#2951>
<?php
if(!$ilDB->tableExists('payment_paymethods'))
{
	$fields = array (
    'pm_id'    => array ('type' => 'integer', 'length'  => 4,'notnull' => true, 'default' => 0),
    'pm_title'   => array ('type' => 'text', 'notnull' => true, 'length' => 60, 'fixed' => false),
    'pm_enabled'    => array ('type' => 'integer', 'length'  => 1,"notnull" => true,"default" => 0),
    'save_usr_adr'  => array ('type' => 'integer', 'length'  => 1,"notnull" => true,"default" => 0)
  );
  $ilDB->createTable('payment_paymethods', $fields);
  $ilDB->addPrimaryKey('payment_paymethods', array('pm_id'));
  $ilDB->addIndex('payment_paymethods',array('pm_id'),'i1');
  $ilDB->createSequence("payment_paymethods");
}
?>
<#2952>
<?php
	$res = $ilDB->query("SELECT value FROM settings WHERE keyword = ".$ilDB->quote('pm_bill','text'));
	$row = $ilDB->fetchAssoc($res);
	$row['value']== 1 ? $pm_bill = 1 : $pm_bill = 0;
	$res = $ilDB->query("SELECT value FROM settings WHERE keyword = ".$ilDB->quote('save_user_adr_bill','text'));
	$row = $ilDB->fetchAssoc($res);
	$row['value']== 1 ? $adr_bill = 1: $adr_bill = 0;

	$res = $ilDB->query("SELECT value FROM settings WHERE keyword = ".$ilDB->quote('pm_bmf','text'));
	$row = $ilDB->fetchAssoc($res);
	$row['value']== 1 ? $pm_bmf =1: $pm_bmf = 0;
	$res = $ilDB->query("SELECT value FROM settings WHERE keyword = ".$ilDB->quote('save_user_adr_bmf','text'));
	$row = $ilDB->fetchAssoc($res);
	$row['value']== 1 ? $adr_bmf = 1: $adr_bmf = 0;

	$res = $ilDB->query("SELECT value FROM settings WHERE keyword = ".$ilDB->quote('pm_paypal','text'));
	$row = $ilDB->fetchAssoc($res);
	$row['value']== 1 ? $pm_ppal =1: $pm_ppal = 0;
	$res = $ilDB->query("SELECT value FROM settings WHERE keyword = ".$ilDB->quote('save_user_adr_paypal','text'));
	$row = $ilDB->fetchAssoc($res);
	$row['value']== 1 ? $adr_ppal = 1: $adr_ppal = 0;

	$res = $ilDB->query("SELECT value FROM settings WHERE keyword = ".$ilDB->quote('pm_epay','text'));
	$row = $ilDB->fetchAssoc($res);
	$row['value']== 1 ? $pm_epay = 1: $pm_epay = 0;
	$res = $ilDB->query("SELECT value FROM settings WHERE keyword = ".$ilDB->quote('save_user_adr_epay','text'));
	$row = $ilDB->fetchAssoc($res);
	$row['value']==1 ? $adr_epay = 1: $adr_epay = 0;

	$query = 'INSERT INTO payment_paymethods (pm_id, pm_title, pm_enabled, save_usr_adr) VALUES (%s, %s, %s, %s)';
	$types = array("integer", "text", "integer", "integer");

	$nextId = $ilDB->nextId('payment_paymethods');
	$bill_data = array($nextId, 'bill', $pm_bill, $adr_bill);
	$ilDB->manipulateF($query,$types,$bill_data);

	$nextId = $ilDB->nextId('payment_paymethods');
	$bmf_data  = array($nextId, 'bmf',  $pm_bmf, $adr_bmf);
	$ilDB->manipulateF($query,$types,$bmf_data);

	$nextId = $ilDB->nextId('payment_paymethods');
	$paypal_data  = array($nextId, 'paypal',  $pm_ppal, $adr_ppal);
	$ilDB->manipulateF($query,$types,$paypal_data);

	$nextId = $ilDB->nextId('payment_paymethods');
	$epay_data  = array($nextId, 'epay', $pm_epay, $adr_epay);
	  $ilDB->manipulateF($query,$types,$epay_data);
?>
<#2953>
<?php
	$res = $ilDB->manipulate('DELETE FROM settings WHERE keyword = '.$ilDB->quote('pm_bill', 'text'));
	$res = $ilDB->manipulate('DELETE FROM settings WHERE keyword = '.$ilDB->quote('pm_bmf', 'text'));
	$res = $ilDB->manipulate('DELETE FROM settings WHERE keyword = '.$ilDB->quote('pm_paypal', 'text'));
	$res = $ilDB->manipulate('DELETE FROM settings WHERE keyword = '.$ilDB->quote('pm_epay', 'text'));

	$res = $ilDB->manipulate('DELETE FROM settings WHERE keyword = '.$ilDB->quote('save_user_adr_bill', 'text'));
	$res = $ilDB->manipulate('DELETE FROM settings WHERE keyword = '.$ilDB->quote('save_user_adr_bmf', 'text'));
	$res = $ilDB->manipulate('DELETE FROM settings WHERE keyword = '.$ilDB->quote('save_user_adr_paypal', 'text'));
	$res = $ilDB->manipulate('DELETE FROM settings WHERE keyword = '.$ilDB->quote('save_user_adr_epay', 'text'));
?>
<#2954>
<?php
	if($ilDB->tableColumnExists("payment_settings", "hide_filtering"))
	{
		$ilDB->renameTableColumn('payment_settings', 'hide_filtering', 'objects_allow_custom_sorting');
	}
	$ilDB->modifyTableColumn('payment_settings', 'objects_allow_custom_sorting', array(
			"type" => "integer",
			'length' => 4,
			"notnull" => true,
			"default" => 0));
?>
<#2955>
<?php
	if (!$ilDB->tableColumnExists("payment_statistic", "currency_unit"))
	{
		$ilDB->addTableColumn("payment_statistic", "currency_unit", array(
			"type" => "text",
			"notnull" => false,
		 	"length" => 16,
		 	"fixed" => false));
	}
	$res = $ilDB->query('SELECT price from payment_statistic');
	$row = $ilDB->fetchAssoc($res);
	while($row = $ilDB->fetchAssoc($res))
	{
		$exp = explode(' ', $row['price']);
		$amount = $exp['0'];
		$currency = $exp['1'];

		$upd = $ilDB->manipulateF('UPDATE payment_statistic
			SET price = %s, currency_unit = %s',
		array('float','text'), array($amount, $currency));
	}
?>
<#2956>
<?php
if($ilDB->tableExists('payment_currencies'))
	$ilDB->dropTable('payment_currencies');
if($ilDB->tableExists('payment_currencies_seq'))
		$ilDB->dropTable('payment_currencies_seq');
?>
<#2957>
<?php
if(!$ilDB->tableExists('payment_currencies'))
{
	$fields = array (
    'currency_id'    => array(
    		'type' => 'integer',
    		'length'  => 4,
    		'notnull' => true,
    		'default' => 0),

    'unit'   => array(
    		'type' => 'text',
    		'notnull' => true,
    		'length' => 16,
    		'fixed' => false),

	'iso_code' => array(
			"type" => "text",
			"notnull" => false,
		 	"length" => 4,
		 	"fixed" => false),

	'symbol' => array(
			"type" => "text",
			"notnull" => false,
		 	"length" => 4,
		 	"fixed" => false),

	'conversion_rate' => array(
			"type" => "float",
			"notnull" => true,
			"default" => 0)
  );
  $ilDB->createTable('payment_currencies', $fields);
  $ilDB->addPrimaryKey('payment_currencies', array('currency_id'));
  $ilDB->createSequence("payment_currencies");
}
?>
<#2958>
<?php
	$res = $ilDB->query('SELECT currency_unit FROM payment_settings');
	$row = $ilDB->fetchAssoc($res);

	while($row = $ilDB->fetchAssoc($res))
	{
		$nextId = $ilDB->nextId('payment_currencies');
		$ins_res = $ilDB->manipulateF('INSERT INTO payment_currencies (currency_id, unit)
			VALUES(%s,%s)',
		array('integer', 'text'),
		array($nextId, $row['currency_unit']));

		$upd_prices = $ilDB->manipulateF('UPDATE payment_prices SET currency = %s',
		array('integer'), array($nextId));
		$ilDB->manipulateF('UPDATE payment_statistic SET currency_unit = %s', array('text'), array($row['currency_unit']));
	}
?>
<#2959>
<?php
	$res = $ilDB->query('SELECT * FROM payment_statistic');
	$statistic_arr = array();
	$counter = 0;
	while($row = $ilDB->fetchAssoc($res))
	{
		$statistic_arr[$counter]['booking_id'] = $row['booking_id'];
		$tmp_p = str_replace(",", ".", $row['price']);
		$pr = str_replace(' ', '', $tmp_p);
		$statistic_arr[$counter]['price'] = (float)$pr;
		$tmp_d =  str_replace(",", ".",  $row['discount']);
		$dis = str_replace(' ', '', $tmp_d);
		$statistic_arr[$counter]['discount'] =(float)$dis;
		$counter++;
	}

	$ilDB->modifyTableColumn('payment_statistic', 'price', array(
			"type" => "float",
			"notnull" => true,
			"default" => 0));

	$ilDB->modifyTableColumn('payment_statistic', 'discount', array(
			"type" => "float",
			"notnull" => true,
			"default" => 0));

	foreach($statistic_arr as $stat)
	{
		$upd = $ilDB->manipulateF('UPDATE payment_statistic SET
			price = %s,
			discount = %s
		WHERE booking_id = %s',
		array('float', 'float','integer'),
		array($stat['price'],$stat['discount'], $stat['booking_id']));
	}
?>
<#2960>
<?php
	if (!$ilDB->tableColumnExists("payment_statistic", "email_extern"))
	{
		$ilDB->addTableColumn('payment_statistic', 'email_extern', array(
			"type" => "text",
			"notnull" => false,
		 	"length" => 80,
		 	"fixed" => false));
	}
	if (!$ilDB->tableColumnExists("payment_statistic", "name_extern"))
	{
		$ilDB->addTableColumn('payment_statistic', 'name_extern', array(
			"type" => "text",
			"notnull" => false,
		 	"length" => 80,
		 	"fixed" => false));
	}
?>
<#2961>
<?php
	if (!$ilDB->tableColumnExists("payment_shopping_cart", "session_id"))
	{
		$ilDB->addTableColumn('payment_shopping_cart', 'session_id', array(
			"type" => "text",
			"notnull" => true,
		 	"length" => 80,
		 	"fixed" => false));
	}
?>
<#2962>
<?php
	if ($ilDB->tableExists('payment_bill_vendor'))
	{
		$ilDB->dropTable('payment_bill_vendor');
	}
?>
<#2963>
<?php
	$ilCtrlStructureReader->getStructure();
?>
<#2964>
<?php
	$ilDB->dropIndex('svy_category', 'i2');
?>
<#2965>
<?php
if($ilDB->tableColumnExists('svy_category','scale'))
{
	$ilDB->dropTableColumn('svy_category', 'scale');
}
?>
<#2966>
<?php
if($ilDB->tableColumnExists('svy_category','other'))
{
	$ilDB->dropTableColumn('svy_category', 'other');
}
?>
<#2967>
<?php
if(!$ilDB->tableColumnExists('svy_variable','other'))
{
  $ilDB->addTableColumn("svy_variable", "other", array("type" => "integer", "length" => 2, "notnull" => true, "default" => 0));
}
?>
<#2968>
<?php
if($ilDB->tableColumnExists('svy_qst_mc','use_other_answer'))
{
	$ilDB->dropTableColumn('svy_qst_mc', 'use_other_answer');
}
?>
<#2969>
<?php
if($ilDB->tableColumnExists('svy_qst_sc','use_other_answer'))
{
	$ilDB->dropTableColumn('svy_qst_sc', 'use_other_answer');
}
?>
<#2970>
<?php
	// mail rcp_to
	$ilDB->addTableColumn("mail", "rcp_to_tmp", array(
		"type" => "clob",
		"notnull" => false,
		"default" => null)
	);

	$ilDB->manipulate('UPDATE mail SET rcp_to_tmp = rcp_to');
	$ilDB->dropTableColumn('mail', 'rcp_to');
	$ilDB->renameTableColumn("mail", "rcp_to_tmp", "rcp_to");
?>
<#2971>
<?php
	// mail rcp_cc
	$ilDB->addTableColumn("mail", "rcp_cc_tmp", array(
		"type" => "clob",
		"notnull" => false,
		"default" => null)
	);

	$ilDB->manipulate('UPDATE mail SET rcp_cc_tmp = rcp_cc');
	$ilDB->dropTableColumn('mail', 'rcp_cc');
	$ilDB->renameTableColumn("mail", "rcp_cc_tmp", "rcp_cc");
?>
<#2972>
<?php
	// mail rcp_bcc
	$ilDB->addTableColumn("mail", "rcp_bcc_tmp", array(
		"type" => "clob",
		"notnull" => false,
		"default" => null)
	);

	$ilDB->manipulate('UPDATE mail SET rcp_bcc_tmp = rcp_bcc');
	$ilDB->dropTableColumn('mail', 'rcp_bcc');
	$ilDB->renameTableColumn("mail", "rcp_bcc_tmp", "rcp_bcc");
?>
<#2973>
<?php
	// mail_saved rcp_to
	$ilDB->addTableColumn("mail_saved", "rcp_to_tmp", array(
		"type" => "clob",
		"notnull" => false,
		"default" => null)
	);

	$ilDB->manipulate('UPDATE mail_saved SET rcp_to_tmp = rcp_to');
	$ilDB->dropTableColumn('mail_saved', 'rcp_to');
	$ilDB->renameTableColumn("mail_saved", "rcp_to_tmp", "rcp_to");
?>
<#2974>
<?php
	// mail_saved rcp_cc
	$ilDB->addTableColumn("mail_saved", "rcp_cc_tmp", array(
		"type" => "clob",
		"notnull" => false,
		"default" => null)
	);

	$ilDB->manipulate('UPDATE mail_saved SET rcp_cc_tmp = rcp_cc');
	$ilDB->dropTableColumn('mail_saved', 'rcp_cc');
	$ilDB->renameTableColumn("mail_saved", "rcp_cc_tmp", "rcp_cc");
?>
<#2975>
<?php
	// mail_saved rcp_bcc
	$ilDB->addTableColumn("mail_saved", "rcp_bcc_tmp", array(
		"type" => "clob",
		"notnull" => false,
		"default" => null)
	);

	$ilDB->manipulate('UPDATE mail_saved SET rcp_bcc_tmp = rcp_bcc');
	$ilDB->dropTableColumn('mail_saved', 'rcp_bcc');
	$ilDB->renameTableColumn("mail_saved", "rcp_bcc_tmp", "rcp_bcc");
?>
<#2976>
<?php
	$ilCtrlStructureReader->getStructure();
?>
<#2977>
<?php
	$ilDB->addTableColumn("content_object", "public_scorm_file", array(
		"type" => "text",
		"notnull" => false,
		"length" => 50));
?>
<#2978>
<?php
	$query = 'UPDATE usr_pref SET value = ROUND(value / 60) WHERE keyword = %s AND value IS NOT NULL';
	if($ilDB->getDBType() == 'oracle')
	{
		$query .= " AND LENGTH(TRIM(TRANSLATE (value, ' +-.0123456789',' '))) IS NULL";
		$ilDB->manipulateF($query, array('text'), array('session_reminder_lead_time'));
	}
	else
	{
		$ilDB->manipulateF($query, array('text'), array('session_reminder_lead_time'));
	}
?>
<#2979>
<?php
	$ilCtrlStructureReader->getStructure();
?>

<#2980>
<?php

if(!$ilDB->tableExists('openid_provider'))
{
	$fields = array(
		'provider_id'	=> array(
			'type'		=> 'integer',
			'length'	=> 4,
		),
		'enabled' 		=> array(
			'type' 			=> 'integer',
			'length' 		=> 1,
		),
		'name' 			=> array(
			'type' 			=> 'text',
			'length' 		=> 128,
			'fixed'			=> false,
			'notnull'		=> false
		),
		'url'			=> array(
			'type'			=> 'text',
			'length'		=> 512,
			'fixed'			=> false,
			'notnull'		=> false
		),
		'image'			=> array(
			'type'			=> 'integer',
			'length'		=> 2
		)
	);
	$ilDB->createTable('openid_provider',$fields);
	$ilDB->addPrimaryKey('openid_provider',array('provider_id'));
	$ilDB->createSequence('openid_provider');
	
}
?>

<#2981>
<?php
$query = "INSERT INTO openid_provider (provider_id,enabled,name,url,image) ".
	"VALUES ( ".
	$ilDB->quote($ilDB->nextId('openid_provider'),'integer').','.
	$ilDB->quote(1,'integer').','.
	$ilDB->quote('MyOpenID','text').','.
	$ilDB->quote('http://%s.myopenid.com').','.
	$ilDB->quote(1,'integer').
	")";
$res = $ilDB->query($query);
?>
<#2982>
<?php
	$setting = new ilSetting();
	$st_step = (int) $setting->get('patch_stex_db');
	if ($st_step <= 0)
	{
		$ilDB->createTable("exc_assignment",
			array(
				"id" => array(
					"type" => "integer", "length" => 4, "notnull" => true
				),
				"exc_id" => array(
					"type" => "integer", "length" => 4, "notnull" => true
				),
				"time_stamp" => array(
					"type" => "integer", "length" => 4, "notnull" => false
				),
				"instruction" => array(
					"type" => "clob"
				)
			)
		);

		$ilDB->addPrimaryKey("exc_assignment", array("id"));
		
		$ilDB->createSequence("exc_assignment");
	}
?>
<#2983>
<?php
	$setting = new ilSetting();
	$st_step = (int) $setting->get('patch_stex_db');
	if ($st_step <= 1)
	{
		$ilDB->createTable("exc_mem_ass_status",
			array(
				"ass_id" => array(
					"type" => "integer", "length" => 4, "notnull" => true
				),
				"usr_id" => array(
					"type" => "integer", "length" => 4, "notnull" => true
				),
				"notice" => array(
					"type" => "text", "length" => 4000, "notnull" => false
				),
				"returned" => array(
					"type" => "integer", "length" => 1, "notnull" => true, "default" => 0
				),
				"solved" => array(
					"type" => "integer", "length" => 1, "notnull" => false
				),
				"status_time" => array(
					"type" => "timestamp", "notnull" => false
				),
				"sent" => array(
					"type" => "integer", "length" => 1, "notnull" => false
				),
				"sent_time" => array(
					"type" => "timestamp", "notnull" => false
				),
				"feedback_time" => array(
					"type" => "timestamp", "notnull" => false
				),
				"feedback" => array(
					"type" => "integer", "length" => 1, "notnull" => true, "default" => 0
				),
				"status" => array(
					"type" => "text", "length" => 9, "fixed" => true, "default" => "notgraded", "notnull" => false
				)
			)
		);
			
		$ilDB->addPrimaryKey("exc_mem_ass_status", array("ass_id", "usr_id"));
	}
?>
<#2984>
<?php
	$setting = new ilSetting();
	$st_step = (int) $setting->get('patch_stex_db');
	if ($st_step <= 2)
	{
		$ilDB->addTableColumn("exc_returned",
			"ass_id",
			array("type" => "integer", "length" => 4, "notnull" => false));
	}
?>
<#2985>
<?php
	/*$setting = new ilSetting();
	$st_step = (int) $setting->get('patch_stex_db');
	if ($st_step <= 3)
	{
		$ilDB->createTable("exc_mem_tut_status",
			array (
				"ass_id" => array(
					"type" => "integer", "length" => 4, "notnull" => true
				),
				"mem_id" => array(
					"type" => "integer", "length" => 4, "notnull" => true
				),
				"tut_id" => array(
					"type" => "integer", "length" => 4, "notnull" => true
				),
				"download_time" => array(
					"type" => "timestamp"
				)
			)
		);
	}*/
?>
<#2986>
<?php
	/*$setting = new ilSetting();
	$st_step = (int) $setting->get('patch_stex_db');
	if ($st_step <= 4)
	{
		$ilDB->addPrimaryKey("exc_mem_tut_status", array("ass_id", "mem_id", "tut_id"));
	}*/
?>
<#2987>
<?php
	$setting = new ilSetting();
	$st_step = (int) $setting->get('patch_stex_db');
	if ($st_step <= 5)
	{
		$set = $ilDB->query("SELECT * FROM exc_data");
		while ($rec  = $ilDB->fetchAssoc($set))
		{
			// Create exc_assignment records for all existing exercises
			// -> instruction and time_stamp fields in exc_data are obsolete
			$next_id = $ilDB->nextId("exc_assignment");
			$ilDB->insert("exc_assignment", array(
				"id" => array("integer", $next_id),
				"exc_id" => array("integer", $rec["obj_id"]),
				"time_stamp" => array("integer", $rec["time_stamp"]),
				"instruction" => array("clob", $rec["instruction"])
				));
		}
	}
?>
<#2988>
<?php
	$setting = new ilSetting();
	$st_step = (int) $setting->get('patch_stex_db');
	if ($st_step <= 6)
	{
		$ilDB->addIndex("exc_members", array("obj_id"), "ob");
	}
?>
<#2989>
<?php
	$setting = new ilSetting();
	$st_step = (int) $setting->get('patch_stex_db');
	if ($st_step <= 7)
	{
		$set = $ilDB->query("SELECT id, exc_id FROM exc_assignment");
		while ($rec  = $ilDB->fetchAssoc($set))
		{
			$set2 = $ilDB->query("SELECT * FROM exc_members ".
				" WHERE obj_id = ".$ilDB->quote($rec["exc_id"], "integer")
				);
			while ($rec2  = $ilDB->fetchAssoc($set2))
			{
				$ilDB->manipulate("INSERT INTO exc_mem_ass_status ".
					"(ass_id, usr_id, notice, returned, solved, status_time, sent, sent_time,".
					"feedback_time, feedback, status) VALUES (".
					$ilDB->quote($rec["id"], "integer").",".
					$ilDB->quote($rec2["usr_id"], "integer").",".
					$ilDB->quote($rec2["notice"], "text").",".
					$ilDB->quote($rec2["returned"], "integer").",".
					$ilDB->quote($rec2["solved"], "integer").",".
					$ilDB->quote($rec2["status_time"], "timestamp").",".
					$ilDB->quote($rec2["sent"], "integer").",".
					$ilDB->quote($rec2["sent_time"], "timestamp").",".
					$ilDB->quote($rec2["feedback_time"], "timestamp").",".
					$ilDB->quote($rec2["feedback"], "integer").",".
					$ilDB->quote($rec2["status"], "text").
					")");
			}
		}
	}
?>
<#2990>
<?php
	$setting = new ilSetting();
	$st_step = (int) $setting->get('patch_stex_db');
	if ($st_step <= 8)
	{
		$ilDB->addIndex("exc_usr_tutor", array("obj_id"), "ob");
	}
?>
<#2991>
<?php
	/*$setting = new ilSetting();
	$st_step = (int) $setting->get('patch_stex_db');
	if ($st_step <= 9)
	{
		$set = $ilDB->query("SELECT id, exc_id FROM exc_assignment");
		while ($rec  = $ilDB->fetchAssoc($set))
		{
			$set2 = $ilDB->query("SELECT * FROM exc_usr_tutor ".
				" WHERE obj_id = ".$ilDB->quote($rec["exc_id"], "integer")
				);
			while ($rec2  = $ilDB->fetchAssoc($set2))
			{
				$ilDB->manipulate("INSERT INTO exc_mem_tut_status ".
					"(ass_id, mem_id, tut_id, download_time) VALUES (".
					$ilDB->quote($rec["id"], "integer").",".
					$ilDB->quote($rec2["usr_id"], "integer").",".
					$ilDB->quote($rec2["tutor_id"], "integer").",".
					$ilDB->quote($rec2["download_time"], "timestamp").
					")");
			}
		}
	}*/
?>
<#2992>
<?php
	$setting = new ilSetting();
	$st_step = (int) $setting->get('patch_stex_db');
	if ($st_step <= 10)
	{
		$set = $ilDB->query("SELECT id, exc_id FROM exc_assignment");
		while ($rec  = $ilDB->fetchAssoc($set))
		{
			$ilDB->manipulate("UPDATE exc_returned SET ".
				" ass_id = ".$ilDB->quote($rec["id"], "integer").
				" WHERE obj_id = ".$ilDB->quote($rec["exc_id"], "integer")
				);
		}
	}
?>
<#2993>
<?php
	$setting = new ilSetting();
	$st_step = (int) $setting->get('patch_stex_db');
	if ($st_step <= 11)
	{
		$ilDB->addTableColumn("exc_assignment",
			"title",
			array("type" => "text", "length" => 200, "notnull" => false));

		$ilDB->addTableColumn("exc_assignment",
			"start_time",
			array("type" => "integer", "length" => 4, "notnull" => false));

		$ilDB->addTableColumn("exc_assignment",
			"mandatory",
			array("type" => "integer", "length" => 1, "notnull" => false, "default" => 0));
	}
?>
<#2994>
<?php
	$setting = new ilSetting();
	$st_step = (int) $setting->get('patch_stex_db');
	if ($st_step <= 12)
	{
		$ilDB->addTableColumn("exc_data",
			"pass_mode",
			array("type" => "text", "length" => 8, "fixed" => false,
				"notnull" => true, "default" => "all"));

		$ilDB->addTableColumn("exc_data",
			"pass_nr",
			array("type" => "integer", "length" => 4, "notnull" => false));
	}
?>
<#2995>
<?php
	$setting = new ilSetting();
	$st_step = (int) $setting->get('patch_stex_db');
	if ($st_step <= 13)
	{
		$ilDB->addTableColumn("exc_assignment",
			"order_nr",
			array("type" => "integer", "length" => 4, "notnull" => true, "default" => 0));
	}
?>
<#2996>
<?php
	$setting = new ilSetting();
	$st_step = (int) $setting->get('patch_stex_db');
	if ($st_step <= 14)
	{
		$ilDB->addTableColumn("exc_data",
			"show_submissions",
			array("type" => "integer", "length" => 1, "notnull" => true, "default" => 0));
	}
?>
<#2997>
<?php
/*	$setting = new ilSetting();
	$st_step = (int) $setting->get('patch_stex_db');
	if ($st_step <= 15)
	{
			$new_ex_path = CLIENT_DATA_DIR."/ilExercise";
			
			$old_ex_path = CLIENT_DATA_DIR."/exercise";
			
			$old_ex_files = array();
			
			if (is_dir($old_ex_path))
			{
				$dh_old_ex_path = opendir($old_ex_path);
				
				// old exercise files into an assoc array to
				// avoid reading of all files each time
				
				while($file = readdir($dh_old_ex_path))
				{
					if(is_dir($old_ex_path."/".$file))
					{
						continue;
					}
					list($obj_id,$rest) = split('_',$file,2);
					$old_ex_files[$obj_id][] = array("full" => $file,
						"rest" => $rest);
				}
			}
//var_dump($old_ex_files);
			
			$set = $ilDB->query("SELECT id, exc_id FROM exc_assignment");
			while ($rec  = $ilDB->fetchAssoc($set))
			{
				// move exercise files to assignment directories
				if (is_array($old_ex_files[$rec["exc_id"]]))
				{
					foreach ($old_ex_files[$rec["exc_id"]] as $file)
					{
						$old = $old_ex_path."/".$file["full"];
						$new = $new_ex_path."/".$this->createPathFromId($rec["exc_id"], "exc").
							"/ass_".$rec["id"]."/".$file["rest"];
							
						if (is_file($old))
						{
							ilUtil::makeDirParents(dirname($new));
							rename($old, $new);
//echo "<br><br>move: ".$old.
//	"<br>to: ".$new;
						}
					}
				}

				// move submitted files to assignment directories
				if (is_dir($old_ex_path."/".$rec["exc_id"]))
				{
					$old = $old_ex_path."/".$rec["exc_id"];
					$new = $new_ex_path."/".$this->createPathFromId($rec["exc_id"], "exc").
						"/subm_".$rec["id"];
					ilUtil::makeDirParents(dirname($new));
					rename($old, $new);
//echo "<br><br>move: ".$old.
//	"<br>to: ".$new;
				}
				
	}*/
?>
<#2998>
<?php
	$setting = new ilSetting();
	$st_step = (int) $setting->get('patch_stex_db');
	if ($st_step <= 16)
	{
		$ilDB->addTableColumn("exc_usr_tutor",
			"ass_id",
			array("type" => "integer", "length" => 4, "notnull" => false));
	}
?>
<#2999>
<?php
	$setting = new ilSetting();
	$st_step = (int) $setting->get('patch_stex_db');
	if ($st_step <= 17)
	{
		$set = $ilDB->query("SELECT id, exc_id FROM exc_assignment");
		while ($rec  = $ilDB->fetchAssoc($set))
		{
			$ilDB->manipulate("UPDATE exc_usr_tutor SET ".
				" ass_id = ".$ilDB->quote($rec["id"], "integer").
				" WHERE obj_id = ".$ilDB->quote($rec["exc_id"], "integer")
				);
		}
	}
?>
<#3000>
<?php
	$setting = new ilSetting();
	$st_step = (int) $setting->get('patch_stex_db');
	if ($st_step <= 18)
	{
		$ilCtrlStructureReader->getStructure();
	}
?>
<#3001>
<?php
	$setting = new ilSetting();
	$st_step = (int) $setting->get('patch_stex_db');
	if ($st_step <= 19)
	{
		$ilDB->addTableColumn("exc_mem_ass_status",
			"mark",
			array("type" => "text", "length" => 32, "notnull" => false));
		$ilDB->addTableColumn("exc_mem_ass_status",
			"u_comment",
			array("type" => "text", "length" => 1000, "notnull" => false));

		$set = $ilDB->query("SELECT id, exc_id FROM exc_assignment");
		while ($rec  = $ilDB->fetchAssoc($set))
		{
			$set2 = $ilDB->query("SELECT * FROM ut_lp_marks WHERE obj_id = ".$ilDB->quote($rec["exc_id"], "integer"));
			while ($rec2 = $ilDB->fetchAssoc($set2))
			{
				$set3 = $ilDB->query("SELECT ass_id FROM exc_mem_ass_status WHERE ".
					"ass_id = ".$ilDB->quote($rec["id"], "integer").
					" AND usr_id = ".$ilDB->quote($rec2["usr_id"], "integer"));
				if ($rec3 = $ilDB->fetchAssoc($set3))
				{
					$ilDB->manipulate("UPDATE exc_mem_ass_status SET ".
						" mark = ".$ilDB->quote($rec2["mark"], "text").",".
						" u_comment = ".$ilDB->quote($rec2["u_comment"], "text").
						" WHERE ass_id = ".$ilDB->quote($rec["id"], "integer").
						" AND usr_id = ".$ilDB->quote($rec2["usr_id"], "integer")
						);
				}
				else
				{
					$ilDB->manipulate("INSERT INTO exc_mem_ass_status (ass_id, usr_id, mark, u_comment) VALUES (".
						$ilDB->quote($rec["id"], "integer").", ".
						$ilDB->quote($rec2["usr_id"], "integer").", ".
						$ilDB->quote($rec2["mark"], "text").", ".
						$ilDB->quote($rec2["u_comment"], "text").")"
						);
				}
			}
		}
	}
?>
<#3002>
<?php
	$setting = new ilSetting();
	$st_step = (int) $setting->get('patch_stex_db');
	if ($st_step <= 20)
	{
		$ilDB->dropPrimaryKey("exc_usr_tutor");
		$ilDB->addPrimaryKey("exc_usr_tutor",
			array("ass_id", "usr_id", "tutor_id"));
	}
?>
<#3003>
<?php
	$setting = new ilSetting();
	$st_step = (int) $setting->get('patch_stex_db');
	if ($st_step <= 23)
	{
		$ilDB->modifyTableColumn("exc_assignment",
			"mandatory",
			array("type" => "integer", "length" => 1, "notnull" => false, "default" => 1));
		$ilDB->manipulate("UPDATE exc_assignment SET ".
			" mandatory = ".$ilDB->quote(1, "integer"));
		
		$set = $ilDB->query("SELECT e.id, e.exc_id, o.title, e.title t2 FROM exc_assignment e JOIN object_data o".
							" ON (e.exc_id = o.obj_id)");
		while ($rec  = $ilDB->fetchAssoc($set))
		{
			if ($rec["t2"] == "")
			{
				$ilDB->manipulate("UPDATE exc_assignment SET ".
					" title = ".$ilDB->quote($rec["title"], "text")." ".
					"WHERE id = ".$ilDB->quote($rec["id"], "text"));
			}
		}
	}
?>
<#3004>
<?php
	$setting = new ilSetting();
	$st_step = (int) $setting->get('patch_stex_db');
	if ($st_step <= 15)
	{
			include_once("./Services/Migration/DBUpdate_3004/classes/class.ilDBUpdate3004.php");
		
			$new_ex_path = CLIENT_DATA_DIR."/ilExercise";
			
			$old_ex_path = CLIENT_DATA_DIR."/exercise";
			
			$old_ex_files = array();
			
			if (is_dir($old_ex_path))
			{
				$dh_old_ex_path = opendir($old_ex_path);
				
				// old exercise files into an assoc array to
				// avoid reading of all files each time
				
				while($file = readdir($dh_old_ex_path))
				{
					if(is_dir($old_ex_path."/".$file))
					{
						continue;
					}
					list($obj_id,$rest) = split('_',$file,2);
					$old_ex_files[$obj_id][] = array("full" => $file,
						"rest" => $rest);
				}
			}

//var_dump($old_ex_files);
			
			$set = $ilDB->query("SELECT id, exc_id FROM exc_assignment");
			while ($rec  = $ilDB->fetchAssoc($set))
			{
				// move exercise files to assignment directories
				if (is_array($old_ex_files[$rec["exc_id"]]))
				{
					foreach ($old_ex_files[$rec["exc_id"]] as $file)
					{
						$old = $old_ex_path."/".$file["full"];
						$new = $new_ex_path."/".ilDBUpdate3004::createPathFromId($rec["exc_id"], "exc").
							"/ass_".$rec["id"]."/".$file["rest"];
							
						if (is_file($old))
						{
							ilUtil::makeDirParents(dirname($new));
							rename($old, $new);
//echo "<br><br>move: ".$old.
//	"<br>to: ".$new;
						}
					}
				}

				// move submitted files to assignment directories
				if (is_dir($old_ex_path."/".$rec["exc_id"]))
				{
					$old = $old_ex_path."/".$rec["exc_id"];
					$new = $new_ex_path."/".ilDBUpdate3004::createPathFromId($rec["exc_id"], "exc").
						"/subm_".$rec["id"];
					ilUtil::makeDirParents(dirname($new));
					rename($old, $new);
//echo "<br><br>move: ".$old.
//	"<br>to: ".$new;
				}
				
			}
	}

?>
<#3005>
<?php
	if($ilDB->getDBType() == 'oracle')
	{
		// mail rcp_to
		if (!$ilDB->tableColumnExists('mail', 'rcp_to_tmp'))
		{
			$ilDB->addTableColumn("mail", "rcp_to_tmp", array(
			"type" => "clob",
			"notnull" => false,
			"default" => null));
		}
	}
?>
<#3006>
<?php
	if($ilDB->getDBType() == 'oracle')
	{
		$ilDB->manipulate('UPDATE mail SET rcp_to_tmp = rcp_to');
	}
?>
<#3007>
<?php
	if($ilDB->getDBType() == 'oracle')
	{
		if ($ilDB->tableColumnExists('mail', 'rcp_to'))
			$ilDB->dropTableColumn('mail', 'rcp_to');

		$ilDB->renameTableColumn("mail", "rcp_to_tmp", "rcp_to");
	}
?>
<#3008>
<?php
	if($ilDB->getDBType() == 'oracle')
	{
		// mail rcp_cc
		if (!$ilDB->tableColumnExists('mail', 'rcp_cc_tmp'))
		{
			$ilDB->addTableColumn("mail", "rcp_cc_tmp", array(
			"type" => "clob",
			"notnull" => false,
			"default" => null));
		}
	}
?>
<#3009>
<?php
	if($ilDB->getDBType() == 'oracle')
	{
		$ilDB->manipulate('UPDATE mail SET rcp_cc_tmp = rcp_cc');
	}
?>
<#3010>
<?php
	if($ilDB->getDBType() == 'oracle')
	{
		if ($ilDB->tableColumnExists('mail', 'rcp_cc'))
			$ilDB->dropTableColumn('mail', 'rcp_cc');

		$ilDB->renameTableColumn("mail", "rcp_cc_tmp", "rcp_cc");
	}
?>
<#3011>
<?php
	if($ilDB->getDBType() == 'oracle')
	{
		// mail rcp_bcc
		if (!$ilDB->tableColumnExists('mail', 'rcp_bcc_tmp'))
		{
			$ilDB->addTableColumn("mail", "rcp_bcc_tmp", array(
			"type" => "clob",
			"notnull" => false,
			"default" => null));
		}
	}
?>
<#3012>
<?php
	if($ilDB->getDBType() == 'oracle')
	{
		$ilDB->manipulate('UPDATE mail SET rcp_bcc_tmp = rcp_bcc');
	}
?>
<#3013>
<?php
	if($ilDB->getDBType() == 'oracle')
	{
		if($ilDB->tableColumnExists('mail', 'rcp_bcc'))
			$ilDB->dropTableColumn('mail', 'rcp_bcc');

		$ilDB->renameTableColumn("mail", "rcp_bcc_tmp", "rcp_bcc");
	}
?>
<#3014>
<?php
	if($ilDB->getDBType() == 'oracle')
	{
		// mail_saved rcp_to
		if (!$ilDB->tableColumnExists('mail_saved', 'rcp_to_tmp'))
		{
			$ilDB->addTableColumn("mail_saved", "rcp_to_tmp", array(
			"type" => "clob",
			"notnull" => false,
			"default" => null));
		}
	}
?>
<#3015>
<?php
	if($ilDB->getDBType() == 'oracle')
	{
		$ilDB->manipulate('UPDATE mail_saved SET rcp_to_tmp = rcp_to');
	}
?>
<#3016>
<?php
	if($ilDB->getDBType() == 'oracle')
	{
		if ($ilDB->tableColumnExists('mail_saved', 'rcp_to'))
			$ilDB->dropTableColumn('mail_saved', 'rcp_to');

		$ilDB->renameTableColumn("mail_saved", "rcp_to_tmp", "rcp_to");
	}
?>
<#3017>
<?php
	if($ilDB->getDBType() == 'oracle')
	{
		// mail_saved rcp_cc
		if (!$ilDB->tableColumnExists('mail_saved', 'rcp_cc_tmp'))
		{
			$ilDB->addTableColumn("mail_saved", "rcp_cc_tmp", array(
			"type" => "clob",
			"notnull" => false,
			"default" => null));
		}
	}
?>
<#3018>
<?php
	if($ilDB->getDBType() == 'oracle')
	{
		$ilDB->manipulate('UPDATE mail_saved SET rcp_cc_tmp = rcp_cc');
	}
?>
<#3019>
<?php
	if($ilDB->getDBType() == 'oracle')
	{
		if ($ilDB->tableColumnExists('mail_saved', 'rcp_cc'))
			$ilDB->dropTableColumn('mail_saved', 'rcp_cc');

		$ilDB->renameTableColumn("mail_saved", "rcp_cc_tmp", "rcp_cc");
	}
?>
<#3020>
<?php
	if($ilDB->getDBType() == 'oracle')
	{
		// mail_saved rcp_bcc
		if (!$ilDB->tableColumnExists('mail_saved', 'rcp_bcc_tmp'))
		{
			$ilDB->addTableColumn("mail_saved", "rcp_bcc_tmp", array(
			"type" => "clob",
			"notnull" => false,
			"default" => null));
		}
	}
?>
<#3021>
<?php
	if($ilDB->getDBType() == 'oracle')
	{
		$ilDB->manipulate('UPDATE mail_saved SET rcp_bcc_tmp = rcp_bcc');
	}
?>
<#3022>
<?php
	if($ilDB->getDBType() == 'oracle')
	{
		if ($ilDB->tableColumnExists('mail_saved', 'rcp_bcc'))
			$ilDB->dropTableColumn('mail_saved', 'rcp_bcc');

		$ilDB->renameTableColumn("mail_saved", "rcp_bcc_tmp", "rcp_bcc");
	}
?>
<#3023>
<?php
	if($ilDB->getDBType() == 'mysql')
	{
		 $ilDB->modifyTableColumn('mail','rcp_to', array("type" => "clob", "default" => null, "notnull" => false));
	}
?>
<#3024>
<?php
	if($ilDB->getDBType() == 'mysql')
	{
		 $ilDB->modifyTableColumn('mail','rcp_cc', array("type" => "clob", "default" => null, "notnull" => false));
	}
?>
<#3025>
<?php
	if($ilDB->getDBType() == 'mysql')
	{
		 $ilDB->modifyTableColumn('mail','rcp_bcc', array("type" => "clob", "default" => null, "notnull" => false));
	}
?>
<#3026>
<?php
	if($ilDB->getDBType() == 'mysql')
	{
		 $ilDB->modifyTableColumn('mail_saved','rcp_to', array("type" => "clob", "default" => null, "notnull" => false));
	}
?>
<#3027>
<?php
	if($ilDB->getDBType() == 'mysql')
	{
		 $ilDB->modifyTableColumn('mail_saved','rcp_cc', array("type" => "clob", "default" => null, "notnull" => false));
	}
?>
<#3028>
<?php
	if($ilDB->getDBType() == 'mysql')
	{
		$ilDB->modifyTableColumn('mail_saved','rcp_bcc', array("type" => "clob", "default" => null, "notnull" => false));
	}
?>
<#3029>
<?php
	if(!$ilDB->tableColumnExists('usr_session', 'type'))
	{
		$ilDB->addTableColumn(
			'usr_session',
			'type',
			array(
				"type" => "integer",
				"notnull" => false,
				"length" => 4,
				"default" => null
			)
		);
	}
	if(!$ilDB->tableColumnExists('usr_session', 'createtime'))
	{
		$ilDB->addTableColumn(
			'usr_session',
			'createtime',
			array(
				"type" => "integer",
				"notnull" => false,
				"length" => 4,
				"default" => null
			)
		);
	}
 ?>
<#3030>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#3031>
<?php
	// new permission
	$new_ops_id = $ilDB->nextId('rbac_operations');

	$res = $ilDB->manipulatef('
		INSERT INTO rbac_operations (ops_id, operation, description, class)
	 	VALUES(%s, %s, %s, %s)',
	array('integer','text', 'text', 'text'),
	array($new_ops_id, 'mail_to_global_roles','User may send mails to global roles','object'));

	$res = $ilDB->queryF('SELECT obj_id FROM object_data WHERE type = %s AND title = %s',
	array('text', 'text'), array('typ', 'mail'));
	$row = $ilDB->fetchAssoc($res);

	$typ_id = $row['obj_id'];

	$query = $ilDB->manipulateF('INSERT INTO rbac_ta (typ_id, ops_id) VALUES (%s, %s)',
	array('integer','integer'), array($typ_id, $new_ops_id));
?>
<#3032>
<?php
	$query = 'SELECT ref_id FROM object_data '
		   . 'INNER JOIN object_reference ON object_reference.obj_id = object_data.obj_id '
		   . 'WHERE type = '.$ilDB->quote('mail', 'text');
	$res = $ilDB->query($query);
	$ref_ids = array();
	while($row = $ilDB->fetchAssoc($res))
	{
		$ref_ids[] = $row['ref_id'];
	}

	$query = 'SELECT rol_id FROM rbac_fa '
		   . 'WHERE assign = '.$ilDB->quote('y', 'text').' '
		   . 'AND parent = '.$ilDB->quote(ROLE_FOLDER_ID, 'integer');
	$res = $ilDB->query($query);
	$global_roles = array();
	while($row = $ilDB->fetchAssoc($res))
	{
		$global_roles[] = $row['rol_id'];
	}

	$query = 'SELECT ops_id FROM rbac_operations '
	       . 'WHERE operation = '.$ilDB->quote('mail_to_global_roles', 'text');
	$res = $ilDB->query($query);
	$data = $ilDB->fetchAssoc($res);
	$mtgr_permission = array();
	if((int)$data['ops_id'])
		$mtgr_permission[] = $data['ops_id'];

	foreach($global_roles as $role)
	{
		if($role == SYSTEM_ROLE_ID)
		{
			continue;
		}

		foreach($ref_ids as $ref_id)
		{
			$query = 'SELECT ops_id FROM rbac_pa '
			       . 'WHERE rol_id = '.$ilDB->quote($role, 'integer').' '
				   . 'AND ref_id = '.$ilDB->quote($ref_id, 'integer');
			$res = $ilDB->query($query);
			$operations = array();
			while($row = $ilDB->fetchAssoc($res))
			{
				$operations = unserialize($row['ops_id']);
			}
			if(!is_array($operations)) $operations = array();

			$permissions = array_unique(array_merge($operations, $mtgr_permission));

			// convert all values to integer
			foreach($permissions as $key => $operation)
			{
				$permissions[$key] = (int)$operation;
			}

			// Serialization des ops_id Arrays
			$ops_ids = serialize($permissions);

			$query = 'DELETE FROM rbac_pa '
			       . 'WHERE rol_id = %s '
			       . 'AND ref_id = %s';
			$res = $ilDB->queryF(
				$query, array('integer', 'integer'),
				array($role, $ref_id)
			);
			
			if(!count($permissions))
			{
				continue;
			}

			$ilDB->insert('rbac_pa',
				array(
					'rol_id' => array('integer', $role),
					'ops_id' => array('text', $ops_ids),
					'ref_id' => array('integer', $ref_id)
				)
			);
		}
	}
?>
<#3033>
<?php
	$ilCtrlStructureReader->getStructure();
?>
<#3034>
<?php
	$ilCtrlStructureReader->getStructure();
?>
<#3035>
<?php
	$ilDB->addTableColumn("ut_lp_marks", "status", array(
		"type" => "integer",
		"notnull" => true,
		"length" => 1,
		"default" => 0));
	$ilDB->addTableColumn("ut_lp_marks", "status_changed", array(
		"type" => "timestamp",
		"notnull" => false));
	$ilDB->addTableColumn("ut_lp_marks", "status_dirty", array(
		"type" => "integer",
		"notnull" => true,
		"length" => 1,
		"default" => 0));
	$ilDB->addTableColumn("ut_lp_marks", "percentage", array(
		"type" => "integer",
		"notnull" => true,
		"length" => 1,
		"default" => 0));
?>
<#3036>
<?php
	$ilDB->addIndex('mail_obj_data',array('user_id','m_type'),'i1');
?>
<#3037>
<?php
$ilDB->addTableColumn('read_event', 'childs_read_count', array(
	"type" => "integer",
	"notnull" => true,
	"length" => 4,
	"default" => 0));
$ilDB->addTableColumn('read_event', 'childs_spent_seconds', array(
	"type" => "integer",
	"notnull" => true,
	"length" => 4,
	"default" => 0));
?>
<#3038>
<?php
  $ilDB->addIndex('addressbook',array('user_id','login','firstname','lastname'),'i1');
?>
<#3039>
<?php
	$ilDB->addIndex('mail',array('sender_id','user_id'), 'i4');
?>
<#3040>
<?php
		$ilDB->addTableColumn("frm_settings", "new_post_title", array(
		"type" => "integer",
		"notnull" => true,
		"length" => 1,
		"default" => 0));
?>
<#3041>
<?php
// register new object type 'frma' for forum administration
 $id = $ilDB->nextId("object_data");
$ilDB->manipulateF("INSERT INTO object_data (obj_id, type, title, description, owner, create_date, last_update) ".
		"VALUES (%s, %s, %s, %s, %s, %s, %s)",
		array("integer", "text", "text", "text", "integer", "timestamp", "timestamp"),
		array($id, "typ", "frma", "Forum administration", -1, ilUtil::now(), ilUtil::now()));
$typ_id = $id;

// create object data entry
$id = $ilDB->nextId("object_data");
$ilDB->manipulateF("INSERT INTO object_data (obj_id, type, title, description, owner, create_date, last_update) ".
		"VALUES (%s, %s, %s, %s, %s, %s, %s)",
		array("integer", "text", "text", "text", "integer", "timestamp", "timestamp"),
		array($id, "frma", "__ForumAdministration", "Forum Administration", -1, ilUtil::now(), ilUtil::now()));

// create object reference entry
$ref_id = $ilDB->nextId('object_reference');
$res = $ilDB->manipulateF("INSERT INTO object_reference (ref_id, obj_id) VALUES (%s, %s)",
	array("integer", "integer"),
	array($ref_id, $id));

// put in tree
$tree = new ilTree(ROOT_FOLDER_ID);
$tree->insertNode($ref_id, SYSTEM_FOLDER_ID);

// add rbac operations
// 1: edit_permissions, 2: visible, 3: read, 4:write
$ilDB->manipulateF("INSERT INTO rbac_ta (typ_id, ops_id) VALUES (%s, %s)",
	array("integer", "integer"),
	array($typ_id, 1));
$ilDB->manipulateF("INSERT INTO rbac_ta (typ_id, ops_id) VALUES (%s, %s)",
	array("integer", "integer"),
	array($typ_id, 2));
$ilDB->manipulateF("INSERT INTO rbac_ta (typ_id, ops_id) VALUES (%s, %s)",
	array("integer", "integer"),
	array($typ_id, 3));
$ilDB->manipulateF("INSERT INTO rbac_ta (typ_id, ops_id) VALUES (%s, %s)",
	array("integer", "integer"),
	array($typ_id, 4));
?>
<#3042>
<?php
	$ilCtrlStructureReader->getStructure();
?>
<#3043>
<?php
if(!$ilDB->tableExists('reg_registration_codes'))
{
	$fields = array (
		'code_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0),

		'code' => array(
			'type' => 'text',
			'notnull' => false,
			'length' => 50,
			'fixed' => false),

	 	'role' => array(
			'type' => 'integer',
			'notnull' => false,
			'length' => 4,
			'default' => 0),

		'generated' => array(
			'type' => 'integer',
			'notnull' => false,
			'length' => 4,
			'default' => 0),

		'used' => array(
			'type' => 'integer',
			'notnull' => true,
			'length' => 4,
			'default' => 0)
	);
	$ilDB->createTable('reg_registration_codes', $fields);
	$ilDB->addPrimaryKey('reg_registration_codes', array('code_id'));
	$ilDB->addIndex('reg_registration_codes', array('code'), 'i1');
	$ilDB->createSequence("reg_registration_codes");
}
?>
<#3044>
<?php
	$ilDB->update("settings", array("value"=>array("integer", 0)), array("module"=>array("text", "common"), "keyword"=>array("text", "usr_settings_visib_reg_birthday")));
	$ilDB->update("settings", array("value"=>array("integer", 0)), array("module"=>array("text", "common"), "keyword"=>array("text", "usr_settings_visib_reg_instant_messengers")));
?>
<#3045>
<?php
if(!$ilDB->tableExists('org_unit_data'))
{
	$ilDB->createTable('org_unit_data', array(
			'ou_id' => array(
				'type'     => 'integer',
				'length'   => 4,
				'notnull' => true,
				'default' => 0
			),
			'ou_title' => array(
				'type'     => 'text',
				'length'   => 128,
				'notnull' => false,
				'default' => null
			),
			'ou_subtitle' => array(
				'type'     => 'text',
				'length'   => 128,
				'notnull' => false,
				'default' => null
			),
			'ou_import_id' => array(
				'type'     => 'text',
				'length'   => 64,
				'notnull' => false,
				'default' => null
			)
	));
	$ilDB->addPrimaryKey('org_unit_data', array('ou_id'));
	$ilDB->createSequence('org_unit_data');
	$root_unit_id = $ilDB->nextId('org_unit_data');
	$ilDB->insert('org_unit_data', array(
		'ou_id' => array('integer', $root_unit_id),
		'ou_title' => array('text', 'RootUnit')
	));
}

if(!$ilDB->tableExists('org_unit_tree'))
{
	$ilDB->createTable('org_unit_tree', array(
			'tree' => array(
				'type'     => 'integer',
				'length'   => 4,
				'notnull' => true,
				'default' => 0
			),
			'child' => array(
				'type'     => 'integer',
				'length'   => 4,
				'notnull' => true,
				'default' => 0
			),
			'parent' => array(
				'type'     => 'integer',
				'length'   => 4,
				'notnull' => true,
				'default' => 0
			),
			'lft' => array(
				'type'     => 'integer',
				'length'   => 4,
				'notnull' => true,
				'default' => 0
			),
			'rgt' => array(
				'type'     => 'integer',
				'length'   => 4,
				'notnull' => true,
				'default' => 0
			),
			'depth' => array(
				'type'     => 'integer',
				'length'   => 4,
				'notnull' => true,
				'default' => 0
			)
	));
	$ilDB->addPrimaryKey('org_unit_tree', array('tree', 'child'));
	$ilDB->insert('org_unit_tree', array(
		'tree' => array('integer', 1),
		'child' => array('integer', $root_unit_id),
		'parent' => array('integer', 0),
		'lft' => array('integer', 1),
		'rgt' => array('integer', 2),
		'depth' => array('integer', 1)
	));
}

if(!$ilDB->tableExists('org_unit_assignments'))
{
	$ilDB->createTable('org_unit_assignments', array(
			'oa_ou_id' => array(
				'type'     => 'integer',
				'length'   => 4,
				'notnull' => true,
				'default' => 0
			),
			'oa_usr_id' => array(
				'type'     => 'integer',
				'length'   => 4,
				'notnull' => true,
				'default' => 0
			),
			'oa_reporting_access' => array(
				'type'     => 'integer',
				'length'   => 4,
				'notnull' => true,
				'default' => 0
			),
			'oa_cc_compl_invit' => array(
				'type'     => 'integer',
				'length'   => 4,
				'notnull' => true,
				'default' => 0
			),
			'oa_cc_compl_not1' => array(
				'type'     => 'integer',
				'length'   => 4,
				'notnull' => true,
				'default' => 0
			),
			'oa_cc_compl_not2' => array(
				'type'     => 'integer',
				'length'   => 4,
				'notnull' => true,
				'default' => 0
			)
	));
	$ilDB->addPrimaryKey('org_unit_assignments', array('oa_ou_id', 'oa_usr_id'));
}

?>

<#3046>
<?php
	$ilDB->modifyTableColumn('glossary_definition', 'short_text',
		array("type" => "text", "length" => 4000, "notnull" => false));
?>

<#3047>
<?php
$ilDB->addTableColumn('glossary', 'pres_mode', array(
	"type" => "text",
	"notnull" => true,
	"length" => 10,
	"default" => "table"));
?>
<#3048>
<?php
$ilDB->addTableColumn('glossary', 'snippet_length', array(
	"type" => "integer",
	"notnull" => true,
	"length" => 4,
	"default" => 200));
?>

