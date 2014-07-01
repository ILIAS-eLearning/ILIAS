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
			   , "Stellennummer"=>	array( gevSettings::USR_UDF_JOB_NUMMER
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