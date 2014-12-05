<?php

/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */#

/**
*
*
* @author   Nils Haagen <nhaagen@concepts-and-training.de>
* @version	$Id$
*/



require_once("Services/GEV/Import/classes/class.gevUserImportMatching.php");

class gevImportedUser {

	static $USERFIELDS = array(
		'ilid' 				//ilias_id GOA2
		,'ilid_vfs' 		//ilias_id vofue
		,'ilid_gev' 		//ilias_id generali
	
		,'login'			//Benutzername
		,'pwd'				//Passwort

		,'created'			//Erstellt am
		,'approved'			//Freigegeben am

		,'last_login'		//Letztes Login
		,'owner'			//Besitzer
		,'active'			//Aktiv
		,'account'			//Zugang
		,'gender'			//Geschlecht
		,'firstname'		//Vorname
		,'lastname'			//Nachname
		,'title'			//Titel

		,'bday'				//Geburtstag

		,'street'			//Straße
		,'city'				//Ort
		,'plz'				//Postleitzahl

		,'country'			//Land
		,'fon_work'			//Telefon Arbeit
		,'fon_mobil'		//Telefon Mobil
		,'mail'				//E-Mail

		,'orgunit_name'		//nur zur Orientiertung, wir dnicht re-impo

		,'adp_gev'			//ADP-Nummer GEV
		,'adp_vfs'			//ADP-Nummer VFS
		,'vnr_gev'			//Vermittlernummer GEV
		,'bcity'			//Geburtsort
		,'bname'			//Geburtstname
		,'ihknr'			//IHK Registernummer
		,'ad_title'			//AD-Titel
		,'vkey_gev'			//Vermittlerschlüssel GEV
		,'vkey_vfs'			//Stellungsschlüssel VFS
		,'pos_vfs'			//Stellung VFS
		,'paisy'			//Paisy-Personalnummer VFS
		,'finaccount'		//Kostenstelle VFS

		,'mail_priv'		//Emailadresse (privat)
		,'street_priv'		//Straße (privat)
		,'city_priv'		//Ort (privat)
		,'plz_priv'			//Postleitzahl (privat)

		,'entry_date'		//Eintrittsdatum
		,'exit_date'		//Austrittsdatum

		,'tp_type'			//TP-Typ
		,'bwvid'			//BWV-ID
		,'last_wbd_update'	//Letztes Update WBD (User!)
		,'wbd_okz'			//Zuweisung WBD OKZ
		,'wbd_status'		//Zuweisung WBD Vermittlerstatus
		,'wbd_cert_begin'	//Beginn erste Zertifizierungsperiode
		,'wbd_registered'	//Hat WBD-Registrierung durchgeführt
		,'mail_wbd'			//Email WBD

		,'isMiz'			//ist MIZ?
		,'isMizOf'			//MIZ-Betreuer
		,'isMizOfADP'		//MIZ-Betreuer, ADP-NR
		,'isMizOfMail'		//MIZ-Betreuer, eMail
	);

	public $userdata = array();
	public $roles = array();
	public $orgunits = array();





	public function __construct() {

		foreach (gevImportedUser::$USERFIELDS as $uf){
			$this->userdata[$uf] = '';
		}

	}

	
	private function initRolesBare(){
		foreach (gevUserImportMatching::$GOA2ROLES as $role){
			$this->roles[$role] = 0; // matching might update values,
									 // so this works as collection
		}
	}


	public function set($a_field, $a_value){
		if(! array_key_exists($a_field, $this->userdata)){
			die("field $a_field not in userfields");
		}
		$this->userdata[$a_field] = mysql_real_escape_string($a_value);
	}
	

}
