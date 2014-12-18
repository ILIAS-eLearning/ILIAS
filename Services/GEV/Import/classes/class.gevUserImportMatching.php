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







	public function matchRoleVFS($a_role){

	}


}