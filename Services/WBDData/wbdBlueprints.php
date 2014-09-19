<?php

$WBD_USER_RECORD = array(
	//debug:
	'row_id'=>'',
	//"last_wbd_report" => '',
	

	'title' => '', //Herr:Frau
	'degree' => '',
	'first_name' => '',
	'last_name' => '',
	'name_affix' => '',
	'birthday' => '',
	'auth_email' => '',
	'auth_phone_nr' => '',
	'zipcode' => '',
	'city' => '',
	'street' => '',
	'house_number' => '',
	'pob' => '',
	'free_text' => '',
	'email' => '',
	'phone_nr' => '',
	'mobile_phone_nr' => '',
	'url' => '',
	'agent_registration_nr' => '',
	'agency_work' => '',
	'agent_state' => '',
	'email_confirmation' => '',
	'internal_agent_id' => '',

	//constant, don't bother:
	'tp_service'  => 'Ja',
	'country_code' => 'D',
	'address_code' => 'privat',
	'data_transfer_code'  => 'Ja',
	'data_protection_code'  => 'Ja',
	'training_pass' => 'Nein'
);


$WBD_EDU_RECORD = array(
	//debug:
	'row_id'=>'',
	//"last_wbd_report" => '',

	"name" => "", //lastname
	"first_name" => "",
	"birthday_or_internal_agent_id" => '', //$record['user_id'],
	"agent_id" => "", //$record['user_bwv_id'],
	"training" => "", // $record['crs_template_title'],
	"from" => "", //date('d.m.Y', $record['crs_start_date']),
	"till" => "", //date('d.m.Y', $record['crs_end_date']),
	"score" => "", //$record['crs_credit_points'],
	"internal_booking_id" => "", //$record['crs_ref_id'],
	"contact_degree" => "",
	"contact_first_name" => "",
	"contact_last_name" => "",
	"contact_phone" => "",
	"contact_email" => "",
	"study_type_selection" => "", // "Präsenzveranstaltung" | "Selbstgesteuertes E-Learning" | "Gesteuertes E-Learning";
	"study_content" => "", //Spartenübergreifend",
	"score_code" => ""
			
);


$VALUE_MAPPINGS = array(
	"course_type" => array(
		"Präsenztraining" => "Präsenzveranstaltung",
		"XX" => "Selbstgesteuertes E-Learning",
		"XX" => "Gesteuertes E-Learning",
	),
	"salutation" => array(
		"m" => "Herr",
		"f" => "Frau",
		"w" => "Frau"
	)

);



$CSV_LABELS = array(
	//debug:
	'row_id'=>'ROW-ID',
	"last_wbd_report" => 'LAST_WBD_REPORT',
	

	"address_code" => "Adresskennzeichen",
	"administration_id" => "VerwaltungsID",
	"agency_work" => "Vermittlungstätigkeit",
	"agent_id" => "VermittlerID",
	"agent_id_import" => "Vermittler Id",
	"agent_registration_nr" => "Vermittlerregisternummer",
	"agent_state" => "Vermittlerstatus",
	"auth_email" => "Authentifizierungs Email",
	"auth_phone_nr" => "Authentifizierungs Telefonnummer",
	"birthday" => "Geburtsdatum",
	"birthday_or_internal_agent_id" => "Geburtsdatum (oder interne VermittlerID)",
	"booking_nr" => "Buchungsnummer",
	"cancelled" => "Storniert",
	"city" => "Ort",
	"contact_degree" => "Ansprechpartner Titel",
	"contact_email" => "Ansprechpartner E-Mail",
	"contact_first_name" => "Ansprechpartner Vorname",
	"contact_last_name" => "Ansprechpartner Nachname",
	"contact_phone" => "Ansprechpartner Telefon",
	"country_code" => "Länderkennzeichen",
	"current_okz" => "Aktuelles OKZ",
	"data_protection_code" => "Datenschutzkennzeichen",
	"data_transfer_code" => "Datenübermittlungskennzeichen",
	"degree" => "Titel",
	"email" => "Emailadresse",
	"email_confirmation" => "Benachrichtigung Per Email",
	"first_name" => "Vorname",
	"free_text" => "Freitext",
	"from" => "von",
	"house_number" => "Hausnummer",
	"internal_agent_id" => "Interne Vermittler Id",
	//"internal_agent_id_import" => "interne Vermittler Id",
	"internal_booking_id" => "InterneBuchungsID",
	"internal_booking_id_import" => "interneBuchungsId",
	"last_name" => "Nachname",
	"mobile_phone_nr" => "Mobilfunknummer",
	"name" => "Name",
	"name_affix" => "Namenszusatz",
	"phone_nr" => "Telefonnummer",
	"pob" => "Postfach",
	"score" => "Punkte",
	"score_code" => "KennzeichenPunkte",
	"seminar_end_date" => "SeminarDatumbis",
	"seminar_start_date" => "SeminarDatumab",
	"seminar_title" => "Name der Maßnahme",
	"service_end" => "ServiceBis",
	"service_since" => "ServiceSeit",
	"end_date" => "Enddatum",
	"start_date" => "Startdatum",
	"street" => "Straße",
	"study_content" => "Lerninhalt",
	"study_type" => "Lernart",
	"study_type_selection" => "Lernart (Auswahl)",
	"till" => "bis",
	"title" => "Anrede",
	"tp_service" => "TP Service",
	"training" => "Weiterbildung",
	"training_pass" => "Weiterbildungausweis beantragt",
	"training_score_booking_id" => "WeiterbildungsPunkteBuchungsID",
	"training_score_booking_id_import" => "WeiterbildungspunktebuchungsID",
	"url" => "URL",
	"zipcode" => "Postleitzahl"
);

?>
