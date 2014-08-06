<?php

chdir(dirname(__FILE__));
$ilias_main_directory = './';
while(!file_exists($ilias_main_directory . 'ilias.ini.php'))
{
	$ilias_main_directory .= '../';
}
chdir($ilias_main_directory);

$_GET['baseClass'] = 'ilStartupGUI';

include_once "Services/Context/classes/class.ilContext.php";
ilContext::init(ilContext::CONTEXT_SESSION_REMINDER);

require_once("Services/Init/classes/class.ilInitialisation.php");
ilInitialisation::initILIAS();

/**
 * @var $ilDB ilDB
 */
global $ilDB;

echo "Started installing billing service...<br />";

// Move these steps to separate database update steps in a dbupdate_custom.php file
/* * ************************************************************************* */
/*if(!$ilDB->tableExists('bill'))
{
	$fields = array(
		'bill_pk'               => array(
			'type'    => 'integer',
			'length'  => 4,
			'notnull' => true,
			'default' => 0
		),
		'bill_number'           => array(
			'type'    => 'text',
			'length'  => 255,
			'notnull' => false,
			'default' => null
		),
		'bill_recipient_name'   => array(
			'type'    => 'text',
			'length'  => 255,
			'notnull' => false,
			'default' => null
		),
		'bill_recipient_street' => array(
			'type'    => 'text',
			'length'  => 255,
			'notnull' => false,
			'default' => null
		),
		'bill_recipient_hnr'    => array(
			'type'    => 'text',
			'length'  => 255,
			'notnull' => false,
			'default' => null
		),
		'bill_recipient_zip'    => array(
			'type'    => 'text',
			'length'  => 255,
			'notnull' => false,
			'default' => null
		),
		'bill_recipient_city'   => array(
			'type'    => 'text',
			'length'  => 255,
			'notnull' => false,
			'default' => null
		),
		'bill_recipient_cntry'  => array(
			'type'    => 'text',
			'length'  => 255,
			'notnull' => false,
			'default' => null
		),
		'bill_date'             => array(
			'type'    => 'integer',
			'length'  => 4,
			'notnull' => true,
			'default' => 0
		),
		'bill_title'            => array(
			'type'    => 'text',
			'length'  => 255,
			'notnull' => true
		),
		'bill_description'      => array(
			'type'    => 'text',
			'length'  => 4000,
			'notnull' => false,
			'default' => null
		),
		'bill_vat'              => array(
			'type'    => 'float',
			'notnull' => true,
			'default' => 0
		),
		'bill_cost_center'      => array(
			'type'    => 'text',
			'length'  => 255,
			'notnull' => false,
			'default' => null
		),
		'bill_currency'         => array(
			'type'    => 'text',
			'length'  => 255,
			'notnull' => true
		),
		'bill_usr_id'           => array(
			'type'    => 'integer',
			'length'  => 4,
			'notnull' => true,
			'default' => 0
		),
		'bill_year'             => array(
			'type'    => 'integer',
			'length'  => 2,
			'notnull' => true,
			'default' => 0
		),
		'bill_final'            => array(
			'type'    => 'integer',
			'length'  => 1,
			'notnull' => true,
			'default' => 0
		),
		'bill_context_id'       => array(
			'type'    => 'integer',
			'length'  => 4,
			'notnull' => false,
			'default' => null
		)
	);

	$ilDB->createTable('bill', $fields);
	$ilDB->addPrimaryKey('bill', array('bill_pk'));
	$ilDB->createSequence('bill');
}*/
/* * ************************************************************************* */
/*if(!$ilDB->tableExists('billitem'))
{
	$fields = array(
		'billitem_pk'          => array(
			'type'    => 'integer',
			'length'  => 4,
			'notnull' => true,
			'default' => 0
		),
		'bill_fk'          => array(
			'type'    => 'integer',
			'length'  => 4,
			'notnull' => true,
			'default' => 0
		),
		'billitem_title'       => array(
			'type'    => 'text',
			'length'  => 255,
			'notnull' => true
		),
		'billitem_description' => array(
			'type'    => 'text',
			'length'  => 4000,
			'notnull' => false,
			'default' => null
		),
		'billitem_pta'         => array(
			'type'    => 'float',
			'notnull' => true,
			'default' => 0
		),
		'billitem_vat'         => array(
			'type'    => 'float',
			'notnull' => true,
			'default' => 0
		),
		'billitem_currency'    => array(
			'type'    => 'text',
			'length'  => 255,
			'notnull' => true
		),
		'billitem_context_id'  => array(
			'type'    => 'integer',
			'length'  => 4,
			'notnull' => false,
			'default' => null
		),
		'billitem_final'       => array(
			'type'    => 'integer',
			'length'  => 1,
			'notnull' => true,
			'default' => 0
		)
	);

	$ilDB->createTable('billitem', $fields);
	$ilDB->addPrimaryKey('billitem', array('billitem_pk'));
	$ilDB->createSequence('billitem');
}*/
/* * ************************************************************************* */
/*if(!$ilDB->tableExists('coupon'))
{
	$fields = array(
		'coupon_pk'          => array(
			'type'    => 'integer',
			'length'  => 4,
			'notnull' => true,
			'default' => 0
		),
		'coupon_code'        => array(
			'type'    => 'text',
			'length'  => 255,
			'notnull' => true
		),
		'coupon_value'       => array(
			'type'    => 'float',
			'notnull' => true,
			'default' => 0
		),
		'coupon_last_change' => array(
			'type'    => 'integer',
			'length'  => 4,
			'notnull' => true,
			'default' => 0
		),
		'coupon_expires'     => array(
			'type'    => 'integer',
			'length'  => 4,
			'notnull' => true,
			'default' => 0
		),
		'coupon_usr_id'      => array(
			'type'    => 'integer',
			'length'  => 4,
			'notnull' => true,
			'default' => 0
		),
		'coupon_active'      => array(
			'type'    => 'integer',
			'length'  => 1,
			'notnull' => true,
			'default' => 0
		),
		'coupon_created'     => array(
			'type'    => 'integer',
			'length'  => 4,
			'notnull' => true,
			'default' => 0
		)
	);

	$ilDB->createTable('coupon', $fields);
	$ilDB->addPrimaryKey('coupon', array('coupon_pk'));
	$ilDB->createSequence('coupon');
}

if(in_array(strtolower($ilDB->getDBType()), array('mysql', 'innodb')))
{
	$ilDB->query('ALTER TABLE bill ENGINE=INNODB');
	$ilDB->query('ALTER TABLE bill_seq ENGINE=INNODB');
	$ilDB->query('ALTER TABLE billitem ENGINE=INNODB');
	$ilDB->query('ALTER TABLE billitem_seq ENGINE=INNODB');
	$ilDB->query('ALTER TABLE coupon ENGINE=INNODB');
	$ilDB->query('ALTER TABLE coupon_seq ENGINE=INNODB');
}

if($ilDB->tableColumnExists('bill', 'bill_year'))
{
	$ilDB->modifyTableColumn('bill', 'bill_year', array(
		'type'    => 'integer',
		'length'  => 2,
		'notnull' => true,
		'default' => 0
	));
}

if(!$ilDB->tableColumnExists('coupon', 'coupon_created'))
{
	$ilDB->addTableColumn('coupon', 'coupon_created', array(
		'type'    => 'integer',
		'length'  => 4,
		'notnull' => true,
		'default' => 0
	));
}

if($ilDB->tableColumnExists('bill', 'bill_usr_id'))
{
	$ilDB->modifyTableColumn('bill', 'bill_usr_id', array(
		'type'    => 'integer',
		'length'  => 4,
		'notnull' => true,
		'default' => 0
	));
}

if(
	$ilDB->tableExists('bill') &&
	$ilDB->tableColumnExists('bill', 'bill_number')
)
{
	$ilDB->modifyTableColumn('bill', 'bill_number', array(
		'type'    => 'text',
		'length'  => 255,
		'notnull' => false,
		'default' => null
	));

	if(!$ilDB->indexExistsByFields('bill', array('bill_number')))
	{
		$ilDB->addUniqueConstraint('bill', array('bill_number'), 'c1');
	}
}
*/
echo "Started installing language variables...<br />";


function addLangData($a_module, $a_lang_map, array $a_data, $a_remark)
{
	global $ilDB;

	if(!is_array($a_lang_map))
	{
		$a_lang_map = array($a_lang_map);
	}

	$mod_data = array();


	// lng_data

	$ilDB->manipulate("DELETE FROM lng_data WHERE module = ".$ilDB->quote($a_module, "text"));

	$now = date("Y-m-d H:i:s");
	foreach($a_data as $lang_item_id => $lang_item)
	{
		if(!is_array($lang_item))
		{
			$lang_item = array($lang_item);
		}

		$lang_item_id = $a_module."_".$lang_item_id;

		$fields = array(
			"module" => array("text", $a_module)
			,"identifier" => array("text", $lang_item_id)
			,"lang_key" => array("text", null) // see below
			,"value" => array("text", null) // see below
			,"local_change" => array("timestamp", $now)
			,"remarks" => array("text", $a_remark)
		);

		foreach($a_lang_map as $lang_idx => $lang_id)
		{
			$fields["lang_key"][1] = $lang_id;
			$fields["value"][1] = $lang_item[$lang_idx];
			$ilDB->insert("lng_data", $fields);

			$mod_data[$lang_id][$lang_item_id] = $lang_item[$lang_idx];
		}
	}

	// lng_modules

	$ilDB->manipulate("DELETE FROM lng_modules WHERE module = ".$ilDB->quote($a_module, "text"));
	
	
	$fields = array(
		"module" => array("text", $a_module)
		,"lang_key" => array("text", null) // see below
		,"lang_array" => array("text", null) // see below
	);
	foreach($a_lang_map as $lang_id)
	{
		$fields["lang_key"][1] = $lang_id;
		$fields["lang_array"][1] = serialize($mod_data[$lang_id]);
		$ilDB->insert("lng_modules", $fields);
	}
}

$lang_data = array(
	"numberlabel" => array("Rechnungsnummer", "Bill Number"),
	"vat"         => array("Umsatzsteuer", "Sales Tax"),
	"net"         => array("(netto)", "(after tax)"),
	"bru"         => array("(brutto)", "(pre-tax)"),
	"val"         => array("Rechnungsbetrag", "Amount")
);
addLangData("billing", array("de", "en"), $lang_data, "patch generali - Billing");

echo "Finished!<br />";