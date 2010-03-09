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