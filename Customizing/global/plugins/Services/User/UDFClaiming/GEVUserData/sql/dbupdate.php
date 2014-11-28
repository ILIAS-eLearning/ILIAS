<#1>
<?php

require_once("Services/GEV/Utils/classes/class.gevUDFUtils.php");
require_once("Services/User/classes/class.ilUserDefinedFields.php");

$fields = array( "ADP-Nummer"		=> array( gevSettings::USR_UDF_ADP_NUMBER
											, UDF_TYPE_TEXT
											, array( "visible"				=> true
												   , "changeable"			=> true
												   , "searchable"			=> true
												   , "required"				=> false
												   , "export"				=> true
												   , "course_export"		=> true
												   , "group_export"			=> true
												   , "registration_visible"	=> true
												   , "visible_lua"			=> true
												   , "changeable_lua"		=> true
												   , "certificate"			=> true
												   )
											, null
											)
			   , "Vermittlernummer"	=> array( gevSettings::USR_UDF_JOB_NUMMER
											, UDF_TYPE_TEXT
											, array( "visible"				=> true
												   , "changeable"			=> true
												   , "searchable"			=> true
												   , "required"				=> false
												   , "export"				=> true
												   , "course_export"		=> true
												   , "group_export"			=> true
												   , "registration_visible"	=> true
												   , "visible_lua"			=> true
												   , "changeable_lua"		=> true
												   , "certificate"			=> true
												   )
											, null
											)
			   , "Geburtsort" =>	array( gevSettings::USR_UDF_BIRTHPLACE
											, UDF_TYPE_TEXT
											, array( "visible"				=> true
												   , "changeable"			=> true
												   , "searchable"			=> true
												   , "required"				=> false
												   , "export"				=> true
												   , "course_export"		=> true
												   , "group_export"			=> true
												   , "registration_visible"	=> true
												   , "visible_lua"			=> true
												   , "changeable_lua"		=> true
												   , "certificate"			=> true
												   )
											, null
											)
			   , "Geburtsname" =>	array( gevSettings::USR_UDF_BIRTHNAME
											, UDF_TYPE_TEXT
											, array( "visible"				=> true
												   , "changeable"			=> true
												   , "searchable"			=> true
												   , "required"				=> false
												   , "export"				=> true
												   , "course_export"		=> true
												   , "group_export"			=> true
												   , "registration_visible"	=> true
												   , "visible_lua"			=> true
												   , "changeable_lua"		=> true
												   , "certificate"			=> true
												   )
											, null
											)
			   , "IHK Registernummer" =>array( gevSettings::USR_UDF_IHK_NUMBER
											, UDF_TYPE_TEXT
											, array( "visible"				=> true
												   , "changeable"			=> true
												   , "searchable"			=> true
												   , "required"				=> false
												   , "export"				=> true
												   , "course_export"		=> true
												   , "group_export"			=> true
												   , "registration_visible"	=> true
												   , "visible_lua"			=> true
												   , "changeable_lua"		=> true
												   , "certificate"			=> true
												   )
											, null
											)
			   , "AD-Titel" =>	array( gevSettings::USR_UDF_AD_TITLE
										, UDF_TYPE_TEXT
											, array( "visible"				=> true
												   , "changeable"			=> true
												   , "searchable"			=> true
												   , "required"				=> false
												   , "export"				=> true
												   , "course_export"		=> true
												   , "group_export"			=> true
												   , "registration_visible"	=> true
												   , "visible_lua"			=> true
												   , "changeable_lua"		=> true
												   , "certificate"			=> true
												   )
											, null
											)
			   , "Vermittlerschlüssel"=>array( gevSettings::USR_UDF_AGENT_KEY
											, UDF_TYPE_TEXT
											, array( "visible"				=> true
												   , "changeable"			=> true
												   , "searchable"			=> true
												   , "required"				=> false
												   , "export"				=> true
												   , "course_export"		=> true
												   , "group_export"			=> true
												   , "registration_visible"	=> true
												   , "visible_lua"			=> true
												   , "changeable_lua"		=> true
												   , "certificate"			=> true
												   )
											, null
											)
			   , "Gesellschaftstitel" =>array( gevSettings::USR_UDF_COMPANY_TITLE
											, UDF_TYPE_TEXT
											, array( "visible"				=> true
												   , "changeable"			=> true
												   , "searchable"			=> true
												   , "required"				=> false
												   , "export"				=> true
												   , "course_export"		=> true
												   , "group_export"			=> true
												   , "registration_visible"	=> true
												   , "visible_lua"			=> true
												   , "changeable_lua"		=> true
												   , "certificate"			=> true
												   )
											, null
											)
			   , "Emailadresse (privat)" => array( gevSettings::USR_UDF_PRIV_EMAIL
											, UDF_TYPE_TEXT
											, array( "visible"				=> true
												   , "changeable"			=> true
												   , "searchable"			=> true
												   , "required"				=> false
												   , "export"				=> true
												   , "course_export"		=> true
												   , "group_export"			=> true
												   , "registration_visible"	=> true
												   , "visible_lua"			=> true
												   , "changeable_lua"		=> true
												   , "certificate"			=> true
												   )
											, null
											)
			   , "Straße (privat)" =>	array( gevSettings::USR_UDF_PRIV_STREET
											, UDF_TYPE_TEXT
											, array( "visible"				=> true
												   , "changeable"			=> true
												   , "searchable"			=> true
												   , "required"				=> false
												   , "export"				=> true
												   , "course_export"		=> true
												   , "group_export"			=> true
												   , "registration_visible"	=> true
												   , "visible_lua"			=> true
												   , "changeable_lua"		=> true
												   , "certificate"			=> true
												   )
											, null
											)
			   , "Ort (privat)" =>	array( gevSettings::USR_UDF_PRIV_CITY
											, UDF_TYPE_TEXT
											, array( "visible"				=> true
												   , "changeable"			=> true
												   , "searchable"			=> true
												   , "required"				=> false
												   , "export"				=> true
												   , "course_export"		=> true
												   , "group_export"			=> true
												   , "registration_visible"	=> true
												   , "visible_lua"			=> true
												   , "changeable_lua"		=> true
												   , "certificate"			=> true
												   )
											, null
											)
			   , "Postleitzahl (privat)" =>	array( gevSettings::USR_UDF_PRIV_ZIPCODE
											, UDF_TYPE_TEXT
											, array( "visible"				=> true
												   , "changeable"			=> true
												   , "searchable"			=> true
												   , "required"				=> false
												   , "export"				=> true
												   , "course_export"		=> true
												   , "group_export"			=> true
												   , "registration_visible"	=> true
												   , "visible_lua"			=> true
												   , "changeable_lua"		=> true
												   , "certificate"			=> true
												   )
											, null
											)
			   , "Bundesland (privat)" =>	array( gevSettings::USR_UDF_PRIV_STATE
											, UDF_TYPE_TEXT
											, array( "visible"				=> true
												   , "changeable"			=> true
												   , "searchable"			=> true
												   , "required"				=> false
												   , "export"				=> true
												   , "course_export"		=> true
												   , "group_export"			=> true
												   , "registration_visible"	=> true
												   , "visible_lua"			=> true
												   , "changeable_lua"		=> true
												   , "certificate"			=> true
												   )
											, null
											)
			   , "Telefon (privat) " =>	array( gevSettings::USR_UDF_PRIV_PHONE
											, UDF_TYPE_TEXT
											, array( "visible"				=> true
												   , "changeable"			=> true
												   , "searchable"			=> true
												   , "required"				=> false
												   , "export"				=> true
												   , "course_export"		=> true
												   , "group_export"			=> true
												   , "registration_visible"	=> true
												   , "visible_lua"			=> true
												   , "changeable_lua"		=> true
												   , "certificate"			=> true
												   )
											, null
											)
			   , "Fax (privat)" =>	array( gevSettings::USR_UDF_PRIV_FAX
											, UDF_TYPE_TEXT
											, array( "visible"				=> true
												   , "changeable"			=> true
												   , "searchable"			=> true
												   , "required"				=> false
												   , "export"				=> true
												   , "course_export"		=> true
												   , "group_export"			=> true
												   , "registration_visible"	=> true
												   , "visible_lua"			=> true
												   , "changeable_lua"		=> true
												   , "certificate"			=> true
												   )
											, null
											)
			   , "Eintrittsdatum" =>array( gevSettings::USR_UDF_ENTRY_DATE
											, UDF_TYPE_TEXT
											, array( "visible"				=> true
												   , "changeable"			=> true
												   , "searchable"			=> true
												   , "required"				=> false
												   , "export"				=> true
												   , "course_export"		=> true
												   , "group_export"			=> true
												   , "registration_visible"	=> true
												   , "visible_lua"			=> true
												   , "changeable_lua"		=> true
												   , "certificate"			=> true
												   )
											, null
											)
			   , "Austrittsdatum" =>array( gevSettings::USR_UDF_EXIT_DATE
											, UDF_TYPE_TEXT
											, array( "visible"				=> true
												   , "changeable"			=> true
												   , "searchable"			=> true
												   , "required"				=> false
												   , "export"				=> true
												   , "course_export"		=> true
												   , "group_export"			=> true
												   , "registration_visible"	=> true
												   , "visible_lua"			=> true
												   , "changeable_lua"		=> true
												   , "certificate"			=> true
												   )
											, null
											)
			   , "Status" =>		array( gevSettings::USR_UDF_STATUS
											, UDF_TYPE_TEXT
											, array( "visible"				=> true
												   , "changeable"			=> true
												   , "searchable"			=> true
												   , "required"				=> false
												   , "export"				=> true
												   , "course_export"		=> true
												   , "group_export"			=> true
												   , "registration_visible"	=> true
												   , "visible_lua"			=> true
												   , "changeable_lua"		=> true
												   , "certificate"			=> true
												   )
											, null
											)
			   );

gevUDFUtils::createUDFFields($fields);

?>

<#2>
<?php

require_once("Services/GEV/Utils/classes/class.gevUDFUtils.php");
require_once("Services/User/classes/class.ilUserDefinedFields.php");

$fields = array( "HPE"		=> array( gevSettings::USR_UDF_HPE
											, UDF_TYPE_TEXT
											, array( "visible"				=> true
												   , "changeable"			=> true
												   , "searchable"			=> true
												   , "required"				=> false
												   , "export"				=> true
												   , "course_export"		=> true
												   , "group_export"			=> true
												   , "registration_visible"	=> true
												   , "visible_lua"			=> true
												   , "changeable_lua"		=> true
												   , "certificate"			=> true
												   )
											, null
											)
				);

gevUDFUtils::createUDFFields($fields);

?>

<#3>
<?php

require_once("Services/GEV/Utils/classes/class.gevUDFUtils.php");
require_once("Services/User/classes/class.ilUserDefinedFields.php");

$fields = array( "TP-Typ"	=> array( gevSettings::USR_TP_TYPE
											, UDF_TYPE_SELECT
											, array( "visible"				=> true
												   , "changeable"			=> false
												   , "searchable"			=> true
												   , "required"				=> false
												   , "export"				=> true
												   , "course_export"		=> false
												   , "group_export"			=> false
												   , "registration_visible"	=> false
												   , "visible_lua"			=> false
												   , "changeable_lua"		=> false
												   , "certificate"			=> false
												   )
											, array( "0 - kein Service"
												   , "1 - Bildungsdienstleister"
												   , "2 - TP-Basis"
												   , "3 - TP-Service"
												   )
											)
				, "BWV-ID"	=> array( gevSettings::USR_BWV_ID
				 							, UDF_TYPE_TEXT
											, array( "visible"				=> true
												   , "changeable"			=> false
												   , "searchable"			=> true
												   , "required"				=> false
												   , "export"				=> true
												   , "course_export"		=> false
												   , "group_export"			=> false
												   , "registration_visible"	=> false
												   , "visible_lua"			=> false
												   , "changeable_lua"		=> false
												   , "certificate"			=> false
												   )
											, null
				 							)
				, "Zuweisung WBD OKZ" => array( gevSettings::USR_WBD_OKZ
											, UDF_TYPE_SELECT
											, array( "visible"				=> true
												   , "changeable"			=> false
												   , "searchable"			=> true
												   , "required"				=> false
												   , "export"				=> true
												   , "course_export"		=> false
												   , "group_export"			=> false
												   , "registration_visible"	=> false
												   , "visible_lua"			=> false
												   , "changeable_lua"		=> false
												   , "certificate"			=> false
												   )
											, array( "0 - aus Rolle"
												   , "1 - OKZ1"
												   , "2 - OKZ2"
												   , "3 - OKZ3"
												   , "4 - keine Zuordnung"
												   )
											)
				, "Zuweisung WBD Vermittlerstatus" => array( gevSettings::USR_WBD_STATUS
											, UDF_TYPE_SELECT
											, array( "visible"				=> true
												   , "changeable"			=> false
												   , "searchable"			=> true
												   , "required"				=> false
												   , "export"				=> true
												   , "course_export"		=> false
												   , "group_export"			=> false
												   , "registration_visible"	=> false
												   , "visible_lua"			=> false
												   , "changeable_lua"		=> false
												   , "certificate"			=> false
												   )
											, array( "0 - aus Rolle"
												   , "1 - Angestellter Außendienst"
												   , "2 - Ausschließlichkeitsvermittler"
												   , "3 - Makler"
												   , "4 - Mehrfachagent"
												   , "5 - Mitarbeiter eines Vermittlers"
												   , "6 - Sonstiges"
												   , "7 - keine Zuordnung"
												   )
											)
				);

gevUDFUtils::createUDFFields($fields);

?>

<#4>
<?php

require_once("Services/GEV/Utils/classes/class.gevUDFUtils.php");
require_once("Services/User/classes/class.ilUserDefinedFields.php");

gevUDFUtils::createUDFFields(array(
	"Beginn erste Zertifizierungsperiode" => array( gevSettings::USR_WBD_CERT_PERIOD_BEGIN
											, UDF_TYPE_TEXT
											, array( "visible"				=> true
												   , "changeable"			=> false
												   , "searchable"			=> true
												   , "required"				=> false
												   , "export"				=> true
												   , "course_export"		=> false
												   , "group_export"			=> false
												   , "registration_visible"	=> false
												   , "visible_lua"			=> false
												   , "changeable_lua"		=> false
												   , "certificate"			=> false
												   )
											, null
											)
	));


?>

<#5>
<?php

require_once("Services/GEV/Utils/classes/class.gevUDFUtils.php");
require_once("Services/User/classes/class.ilUserDefinedFields.php");

gevUDFUtils::createUDFFields(array(
	"Hat WBD-Registrierung durchgeführt" => array( gevSettings::USR_WBD_DID_REGISTRATION
											, UDF_TYPE_SELECT
											, array( "visible"				=> true
												   , "changeable"			=> false
												   , "searchable"			=> true
												   , "required"				=> false
												   , "export"				=> true
												   , "course_export"		=> false
												   , "group_export"			=> false
												   , "registration_visible"	=> false
												   , "visible_lua"			=> false
												   , "changeable_lua"		=> false
												   , "certificate"			=> false
												   )
											, array( "0 - Nein"
												   , "1 - Ja"
												   )
											)
	));

?>

<#6>
<?php

require_once("Services/GEV/Utils/classes/class.gevUDFUtils.php");
require_once("Services/User/classes/class.ilUserDefinedFields.php");

gevUDFUtils::createUDFFields(array(
	"Email WBD" => array( gevSettings::USR_WBD_COM_EMAIL
											, UDF_TYPE_TEXT
											, array( "visible"				=> true
												   , "changeable"			=> false
												   , "searchable"			=> true
												   , "required"				=> false
												   , "export"				=> true
												   , "course_export"		=> false
												   , "group_export"			=> false
												   , "registration_visible"	=> false
												   , "visible_lua"			=> false
												   , "changeable_lua"		=> false
												   , "certificate"			=> false
												   )
											, null
											)
	));

?>



<#7>
<?php

require_once("Services/GEV/Utils/classes/class.gevUDFUtils.php");
require_once("Services/User/classes/class.ilUserDefinedFields.php");


$new_fields = array(
	 gevSettings::USR_UDF_ADP_VFS_NUMBER 	=> "ADP-Nummer VFS"
	,gevSettings::USR_UDF_PAISY_NUMBER 		=> "Paisy-Personalnummer VFS"
	,gevSettings::USR_UDF_FINANCIAL_ACCOUNT	=> "Kostenstelle VFS"
	
);

$rename_fields = array(
	 gevSettings::USR_UDF_ADP_NUMBER 	=> 'ADP-Nummer GEV'
	,gevSettings::USR_UDF_JOB_NUMMER 	=> 'Vermittlernummer GEV'
	,gevSettings::USR_UDF_AGENT_KEY 	=> 'Vermittlerschlüssel GEV'
);

$delete_fields = array(
	 gevSettings::USR_UDF_COMPANY_TITLE
	,gevSettings::USR_UDF_PRIV_STATE
	,gevSettings::USR_UDF_PRIV_FAX
	,gevSettings::USR_UDF_STATUS
	,gevSettings::USR_UDF_HPE

);


$udfUtils = gevUDFUtils::getInstance();

foreach ($new_fields as $udf_const => $title) {
	$udfUtils->createUDFFields(array(
		$title => array( $udf_const
						, UDF_TYPE_TEXT
						, array( "visible"				=> true
							   , "changeable"			=> false
							   , "searchable"			=> true
							   , "required"				=> false
							   , "export"				=> true
							   , "course_export"		=> false
							   , "group_export"			=> false
							   , "registration_visible"	=> false
							   , "visible_lua"			=> false
							   , "changeable_lua"		=> false
							   , "certificate"			=> false
							   )
						, null
						)
		));
}
foreach ($rename_fields as $udf_const => $title) {
	$udfUtils->renameUDFField($udf_const, $title);
}

foreach ($delete_fields as $udf_const) {
	$udfUtils->removeUDFField($udf_const);
}

?>

<#8>
<?php

gevUDFUtils::updateUDFFields(array(
		  gevSettings::USR_WBD_OKZ => array( "Zuweisung WBD OKZ"
											, array( "visible"				=> true
												   , "changeable"			=> false
												   , "searchable"			=> true
												   , "required"				=> false
												   , "export"				=> true
												   , "course_export"		=> false
												   , "group_export"			=> false
												   , "registration_visible"	=> false
												   , "visible_lua"			=> false
												   , "changeable_lua"		=> false
												   , "certificate"			=> false
												   )
											, array( "0 - aus Rolle"
												   , "1 - OKZ1"
												   , "2 - OKZ2"
												   , "3 - OKZ3"
												   , "4 - keine Zuordnung"
												   )
											)
		,  gevSettings::USR_WBD_STATUS => array( "Zuweisung WBD Vermittlerstatus"
											, array( "visible"				=> true
												   , "changeable"			=> false
												   , "searchable"			=> true
												   , "required"				=> false
												   , "export"				=> true
												   , "course_export"		=> false
												   , "group_export"			=> false
												   , "registration_visible"	=> false
												   , "visible_lua"			=> false
												   , "changeable_lua"		=> false
												   , "certificate"			=> false
												   )
											, array( "0 - aus Rolle"
												   , "1 - Angestellter Außendienst"
												   , "2 - Ausschließlichkeitsvermittler"
												   , "3 - Makler"
												   , "4 - Mehrfachagent"
												   , "5 - Mitarbeiter eines Vermittlers"
												   , "6 - Sonstiges"
												   , "7 - keine Zuordnung"
												   )
											)
		));

?>