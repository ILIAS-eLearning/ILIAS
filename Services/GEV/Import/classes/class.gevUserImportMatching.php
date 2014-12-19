<?php

/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */#

/**
* 
*
* @author   Nils Haagen <nhaagen@concepts-and-training.de>
* @version	$Id$
*/


class gevUserImportMatching {



	static $GOA2ROLES = array(
		 'Administrator' 		//	Gesamt Admin
		,'Admin-Voll' 			//	Admin exkl. Reiter Administration
		,'Admin-eingeschraenkt' //	Seminaranlage / Orgeinheiten
		,'Admin-Ansicht'		//	Alles ansehen / kein Änderungsrecht
		,'OD/BD'				//	Organisationsdirektor / Bereichsdirektor
		,'FD'					//	Filialdirektor
		,'UA'					// 	Unternehmeragentur
		,'HA 84'				//	Hauptagent HGB 84er
		,'BA 84' 				//	Bezirksagent HGB 84er
		,'Org PV 59' 			//	Organisierender Partnerverkäufer 59
		,'PV 59' 				//	Partnerverkäufer 59
		,'NA' 					//	Nebenberufsagentur
		,'VP' 					//	unabhängige Vertriebspartner
		,'AVL' 					// 	Agenturverkaufsleiter
		,'Ausbildungsbeauftragter'//	Ausbildungsbeauftragter
		,'ID FK'				//  Innendienst Zentrale Führungskraft
		,'ID MA'				//  Innendienst Zentrale Mitarbeiter
		,'OD/FD/BD ID'			//	OD / FD / BD Innendienst
		,'Agt-ID'				//	Innendienstmitarbeiter Agentur
		,'VA 59'				//	Va in Ausbildung - angestellt
		,'VA HGB 84'			//	Va in Ausbildung  - HGB 84
		,'NFK'					//	Nachwuchsführungskraft
		,'FDA'					//	Filialdirektor in Ausbildung
		,'Ausbilder'			//	Ausbilder
		,'Azubi'				//	Auszubildender
		,'Buchhaltung'			//	Buchhaltung
		,'Veranstalter'			//	Veranstalter
		,'int. Trainer'			//	interner Trainer / Referent
		,'ext. Trainer'			//	externer Trainer
		,'OD-Betreuer'			//	Betreuer einer OD (Bildungsbedarf
		,'DBV UVG'				//	Direktionsbevollmächtigter unabhängige Vertriebe
		,'DBV EVG'				//	Direktionsbevollmächtigter Exklusivvertrieb
		,'TP Service'			//	Sonderrolle zur Anmeldung als TP Service
		,'TP Basis'				//	Sonderrolle zur Anmeldung als TP Basis
		,'VFS'					//	Kennzeichen ehem VFS Benutzer
	);



	static $ROLEMAPPINGS = array(

		//'VFS' => array(
			'VD' => '#FROMKEY' //aus Stellungsschlüssel
			,'OD' => '#FROMKEY'
			,'BD' => '#FROMKEY'
			,'HGB 84/1'	=> '#FROMKEY'
			,'HGB 84/2'	=> '#FROMKEY' 
			,'Org PV' => '#FROMKEY'
			,'PV 1'	=> '#FROMKEY'
			,'PV 2'	=> '#FROMKEY'
			,'PV2'	=> '#FROMKEY'
			,'MiZ'	=> 'MiZ / NA'
			,'ID 1'	=> 'ID MA'
			,'Innendienst I'	=> 'ID MA'
			,'ID 2'	=> 'ID FK'
			,'Innendienst II'	=> 'ID FK'
			,'VSPS BD' => 'OD/FD/BD ID'
			,'VSPS-BD' => 'OD/FD/BD ID'
			,'VSPS VD' => 'OD/FD/BD ID'
			,'VSPS-VD' => 'OD/FD/BD ID'
			,'VA-Ausbildung' => 'VA/BA in Ausbildung'
			,'NFK' => 'NFK'
			,'BDA' => 'FDA'
			,'RTL' => '#FROMKEY' //aus Stellungsschlüssel
			,'Trainer intern' => '#FROMKEY'
			,'Interne Trainer' => '#FROMKEY'
			,'Key Account' => 'int. Trainer'
			,'Trainer extern' => 'ext. Trainer'
			,'Admin eingeschränkt' => 'Admin-eingeschraenkt'
			,'EinMan' => '#DROP' //entfällt
			
		//),
		//'GEV' => array(

			,'Administrator' => 'Administrator'
			,'Administrator-Voll' => 'Admin-Voll'
			,'Administrator-Eingeschränkt' => 'Admin-eingeschraenkt'
			,'OD/LD/BD/VD/VTWL' => '#FROMKEY'
			,'LD-ID/BD/ID' => 'OD/FD/BD ID'

			,'DBV/VL-EVG' => '#FROMKEY'
			,'DBV-UVG' => '#FROMKEY'
			,'AVL' => '#FROMKEY'
			,'HA' => '#FROMKEY'
			,'BA' => '#FROMKEY'
			,'NA' => '#FROMKEY'
			
			,'AD-ID' => 'AD ID'
			,'Azubi' => 'Azubi'
			,'VP' => 'VP'
			,'ID-FK' => 'ID FK'
			,'ID-MA' => 'ID MA'
			,'Spezialist' => 'int. Trainer'
			,'Ausbilder' => 'Ausbilder'
			,'int. Referent' => 'int. Trainer'
			,'ext. Referent' => 'ext. Trainer'
			,'Buchhaltung' => 'Buchhaltung'
			,'Veranstalter' => 'Veranstalter'
			,'BA-Ausbildung' => 'VA/BA in Ausbildung'
			,'TP-Basis Registrierung' => 'TP Basis' //this was switched in document....
			,'TP-Service Registrierung' => 'TP Service'//this was switched in document....

		//)

	);





}