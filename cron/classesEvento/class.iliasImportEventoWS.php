<?php

class iliasImportEventoWS {
  /**
  * constructor
  * @param	string	Webservice User
  * @param	string	Webservice Password
  * @param	string	Which data to import: All|Students|Staff|Bachelor|Master|Further|TimeLimit|TutorFurther
  * @access	public
  */
	function iliasImportEventoWS($aWhich = 'All')
	{
		global $ilias;

		$this->client = CLIENT_ID;
		$this->AuthMode = 'ldap';
		//$this->AuthMode = 'shibboleth';
		$this->wsUser = $ilias->getSetting('EventoWSUser','');
		$this->wsPassword = $ilias->getSetting('EventoWSPassword','');
		$this->sendMailsToUsers = $ilias->getSetting('EventoWSSendMails','') == '1';
		$this->nowTime = time();
		$this->webdataDir = dirname(dirname(dirname((__FILE__))))."/evento";
		$this->imageDir = '/export/ilias/import/evento/todo/fotos';
			//Logfile Ordner
		$this->lmDir = $this->webdataDir.'/'.$this->client.'/'.$this->nowTime;
			//Einzelne Files
		$this->logFileLog = $this->lmDir.'/log.php';
		$this->logFileStud = $this->lmDir.'/students.php';
		$this->logFileStaff = $this->lmDir.'/staff.php';
		$this->logFileBachelorMaster = $this->lmDir.'/bachelor_master.php';
		$this->logFileFurtherEducation = $this->lmDir.'/further_education.php';
		$this->logFileTutorTimeLimitsFurtherEducation = $this->lmDir.'/tutors_further_education.php';

		$this->wsdl = "https://saweb.hslu.ch/IliasWebService/ilias.asmx?WSDL";
		$this->now = date('Y-m-d H:i:s', $this->nowTime);
		$this->pastImportStyle = 'pastImport'.date('YmdHis', $this->nowTime);
		$this->runningImportStyle = 'runningImport';
		$this->pagesize = 1200; // larger pagesizes perform faster but need more memory
		$this->maxPages = -1; // -1 = no limit.
		$this->which = strtolower($aWhich);
		$this->maxRetries = 2;
		$this->numberOfSecondsBeforeRetry = 60; // 1 minute
		$this->logfh = null;
		require_once 'Services/AccessControl/classes/class.ilPermissionHelper.php';
		$this->userRoleId = ilPermissionHelper::_getGlobalUserRoleId();
		$this->parentRolesCache	= array();
		$this->localRoleCache = array();
		$this->roleToObjectCache = array();
		// We must not deassign owned users from the same row more than once
		//$this->markedRoles=array();
		$this->markedUsersForDeassignment=array();
		$this->doDeassignSubscriptions=false;
		$this->ldapUsers=array();
	}
	/**
	 * This is the main import function. It calls all other import functions.
	 *
	 * Returns true on success.
	 */
	function import() {
		$start = time();

		@mkdir($this->lmDir, 0777, true);
		chmod($this->lmDir, 0777);
		@mkdir($this->lmDir.'/events', 0777, true);
		chmod($this->lmDir.'/events', 0777);
		//print $this->lmDir;exit;
		$this->writeLogHeader();
		$this->log('Evento-Import begonnen.');

		// Connect to Evento SOAP
		$this->client = new nusoap_client($this->wsdl,true);
		$this->client->setDebugLevel(0); // this is needed to fix a memory leak in nusoap
		$this->client->soap_defencoding = 'UTF-8';
		$this->client->response_timeout = 60;
		$this->client->decode_utf8 = false;
		if ($this->client->getError()) {
			$this->closeWithError($fh);
			return false;
		}

		// Update User Table
		// ----------------------
		$result = &$this->callWebService('UpdateEmployeeTmpTable');
		if($result){
			$this->log('Update User Table erfolgreich gemacht.');
		}else{
			$this->log('FEHLER: Update User Table nicht erfolgreich gemacht.');
		}
		
		// Import Users
		// ----------------------
		$this->importStudents();
		$this->importStaff();
		$this->convertNonLDAPStudents();
		$this->convertNonLDAPStaff();

		// Get rid of stale marks
		// ----------------------
		if (in_array($this->which, array('all','bachelor', 'master', 'further'))) {
			$this->removeStaleMarks();
		}

		// Assign users to courses and groups, and mark assignments which are
		// no longer delivered from evento for deassignments.
		if (in_array($this->which, array('all','bachelor', 'master'))) {
			$this->importBachelorMaster();
		}
		if (in_array($this->which, array('all','further'))) {
			$this->importFurtherEducation();
		}

		// Deassign all marked assignments.
		if (in_array($this->which, array('all','bachelor', 'master', 'further'))) {
			$this->deassignMarkedAssignments();
		}


		if (in_array($this->which, array('all','tutorfurther'))) {
			$this->importTutorTimeLimitsFurtherEducation();
		}

		if (in_array($this->which, array('all','timelimit'))) {
			$this->setUserTimeLimits();
		}

		$end = time();
		$this->log('Import beendet. '.$this->toElapsed($start, $end).'.' );
                $this->writeLogFooter();
	}
	/**
	 * Calls importUsers with param Students
	 *
	 * Returns the number of rows.
	 */
	function importStudents() {
		$start = time();
		$this->log('Import der Studierenden begonnen.');
		$success = $this->importUsers($this->logFileStud, 'Studierende', 'Studierende', 'Studierende', false);
		$end = time();
		$this->log('Import der Studierenden beendet. '.$this->toElapsed($start, $end, false).'.');
		return $success;
	}
	/**
	 * Calls importUsers with param Staff
	 *
	 * Returns the number of rows.
	 */
	function importStaff() {
		$start = time();
		$this->log('Import der Mitarbeiter begonnen.');
		$success = $this->importUsers($this->logFileStaff, 'Mitarbeiter', 'Mitarbeiter', 'Mitarbeiter', false);
		$end = time();
		$this->log('Import der Mitarbeiter beendet. '.$this->toElapsed($start, $end, false).'.');
		return $success;
	}
	/**
	 * Calls importEvents Bachelor/Masterstudium Events (Kurse usw.)
	 *
	 * Returns the number of rows.
	 */
	function importBachelorMaster() {
		$start = time();
		$this->log('Import der Bachelor- & Master-Studiengänge begonnen.');
		$success = $this->importEvents($this->logFileBachelorMaster, 'Modulanlaesse', 'Anlaesse', 'Bachelor- &amp; Master-Anlässe', true);
		$end = time();
		$this->log('Import der Bachelor- & Master-Studiengänge beendet. '.$this->toElapsed($start, $end, false).'.');
		return $success;
	}
	/**
	 * Calls importEvents Weiterbildung
	 *
	 * Returns the number of rows.
	 */
	function importFurtherEducation() {
		$start = time();
		$this->log('Import der Weiterbildungsstudiengänge begonnen.');
		$success = $this->importEvents($this->logFileFurtherEducation, 'HauptAnlaesse', 'Anlaesse', 'Weiterbildungs-Anlässe', true);
		$end = time();
		$this->log('Import der Weiterbildungsstudiengänge beendet. '.$this->toElapsed($start, $end, false).'.');
		return $success;
	}
	
	function convertNonLDAPStudents(){
	
		global $ilDB;
	
		$deletedLdapUsers=array();
	
		$start = time();
		for ($page = 1; $this->maxPages == -1 || $page <= $this->maxPages; $page++) {
			//$result = &$this->callWebService('GetGeloeschteMitarbeiter');
			$result = &$this->callWebService('GetGeloeschteStudenten', array('parameters'=>array('pagesize'=>$this->pagesize,'pagenumber'=>$page)));
			//print_r($result);
			if (!is_array($result['GetGeloeschteStudentenResult']['diffgram'])) {
				$this->log('FEHLER: keine gelöschten Studenten erhalten.');
				break;
			}
			if(is_array($result['GetGeloeschteStudentenResult']['diffgram']['dsGeloeschteUser']['GeloeschteUser'])){
				foreach($result['GetGeloeschteStudentenResult']['diffgram']['dsGeloeschteUser']['GeloeschteUser'] as $user){
					$deletedLdapUsers[]='Evento:'.$user['EvtID'];
				}
			}
			if(count($result['GetGeloeschteStudentenResult']['diffgram']['dsGeloeschteUser']['GeloeschteUser'])<$this->pagesize) break;
		}
		
		$this->log(''.count($deletedLdapUsers).' gelöschte Studenten erhalten.');
		
		if(count($deletedLdapUsers)>0){
			$convertedCount=0;
			$convertedStr='';
			for($i=0; $i<=count($deletedLdapUsers) ; $i+=100){
				//hole immer max 100 user aus der ilias db mit bedingung dass diese noch ldap aktiv sind
				$sql="SELECT login,matriculation FROM `usr_data` WHERE auth_mode='ldap' AND matriculation IN ('".implode("','",array_slice($deletedLdapUsers,$i,100))."')";
				$resultSQL=$ilDB->db->query($sql);
				while ($row = $ilDB->fetchAssoc($resultSQL))
				{
					//nochmals nachfragen, wenn user wiederhergestellt wurde
					$eventoid=substr($row['matriculation'],7);
					$login=$row['login'];
					$result = $this->callWebService('ExistsHSLUDomainUser',array('parameters'=>array('login'=>$login,'evtid'=>$eventoid)));
					
					if(is_array($result) && $result['ExistsHSLUDomainUserResult']=='false'){
						//user nicht mehr aktiv in ldap
						//update user auth mode
						$sql="UPDATE usr_data SET auth_mode='default' WHERE matriculation LIKE '".$row['matriculation']."'";
						//print $sql."\n";
						$r=$ilDB->db->exec($sql);	
						$convertedCount++;
						$convertedStr.=$row['matriculation'].'('.$row['login'].'), ';
					}
				}
			}
			
			$end = time();
			if($convertedStr!='') $this->log($convertedStr.' von LDAP entfernt.');
			$this->log(''.$convertedCount.' Studenten von LDAP entfernt. '.$this->toElapsed($start, $end, false));
		}		
		return true;
	/*
		global $ilDB;
		if(count($this->ldapUsers)>3000){
			$sql="UPDATE usr_data SET auth_mode='default' WHERE auth_mode='ldap' AND login NOT IN ('".implode("','",$this->ldapUsers)."')";
			$r=$ilDB->db->exec($sql);
			$this->log(''.$r.' Benutzerkonten von LDAP entfernt.');
		}
	*/
	}
	
	function convertNonLDAPStaff(){
		global $ilDB;
	
		$deletedLdapUsers=array();
	
		$start = time();
		for ($page = 1; $this->maxPages == -1 || $page <= $this->maxPages; $page++) {
			//$result = &$this->callWebService('GetGeloeschteMitarbeiter');
			$result = &$this->callWebService('GetGeloeschteMitarbeiter', array('parameters'=>array('pagesize'=>$this->pagesize,'pagenumber'=>$page)));
			//print_r($result);
			if (!is_array($result['GetGeloeschteMitarbeiterResult']['diffgram'])) {
				$this->log('FEHLER: keine gelöschten Studenten erhalten.');
				break;
			}
			if(is_array($result['GetGeloeschteMitarbeiterResult']['diffgram']['dsGeloeschteUser']['GeloeschteUser'])){
				foreach($result['GetGeloeschteMitarbeiterResult']['diffgram']['dsGeloeschteUser']['GeloeschteUser'] as $user){
					$deletedLdapUsers[]='Evento:'.$user['EvtID'];
				}
			}
			if(count($result['GetGeloeschteMitarbeiterResult']['diffgram']['dsGeloeschteUser']['GeloeschteUser'])<$this->pagesize) break;
		}
		
		$this->log(''.count($deletedLdapUsers).' gelöschte Mitarbeiter erhalten.');
		
		if(count($deletedLdapUsers)>0){
			$convertedCount=0;
			$convertedStr='';
			for($i=0; $i<=count($deletedLdapUsers) ; $i+=100){
				//hole immer max 100 user aus der ilias db mit bedingung dass diese noch ldap aktiv sind
				$sql="SELECT login,matriculation FROM `usr_data` WHERE auth_mode='ldap' AND matriculation IN ('".implode("','",array_slice($deletedLdapUsers,$i,100))."')";
				$resultSQL=$ilDB->db->query($sql);
				while ($row = $ilDB->fetchAssoc($resultSQL))
				{
					//nochmals nachfragen, wenn user wiederhergestellt wurde
					$eventoid=substr($row['matriculation'],7);
					$login=$row['login'];
					$result = $this->callWebService('ExistsHSLUDomainUser',array('parameters'=>array('login'=>$login,'evtid'=>$eventoid)));
					
					if(is_array($result) && $result['ExistsHSLUDomainUserResult']=='false'){
						//user nicht mehr aktiv in ldap
						//setze zeitlimit-until auf jetzt
						$sql="UPDATE usr_data SET auth_mode='default', time_limit_until=UNIX_TIMESTAMP() WHERE matriculation LIKE '".$row['matriculation']."'";
						$r=$ilDB->db->exec($sql);						
						$convertedCount++;
						$convertedStr.=$row['matriculation'].'('.$row['login'].'), ';
					}
				}
			}
		}
		
		$end = time();
		if($convertedStr!='') $this->log($convertedStr.' deaktiviert.');
		$this->log(''.$convertedCount.' Mitarbeiter deaktiviert. '.$this->toElapsed($start, $end, false));
		return true;
	}
	
	/**
	 * Complicated function that makes something :)
	 *
	 * Returns the number of rows.
	 */
	function importUsers($aFile, $aRequest, $aDataset, $aTitle, $withSubscription = false, $withData = true) {
		$start = time();

		// Write Students
		$fh = fopen($aFile, 'wb');
		chmod($aFile, 0777);

		$isFirstRow = true;
		$columnKeys = array();
		$count = 0;
		$errors = new ErrorsInPage($this, '../'.basename($aFile));
		fwrite($fh,'<table id="importUsers'.$aRequest.'Table" class="sortable">'."\n");

			// Abort if too many pages
		for ($page = 1; $this->maxPages == -1 || $page <= $this->maxPages; $page++) {
			unset($result);
			$result = &$this->callWebService('Get'.$aRequest, array('parameters'=>array('pagesize'=>$this->pagesize,'pagenumber'=>$page)));
			if ($result == false) {
				fwrite($fh,'</table>'."\n");
				$this->closeWithError($fh);
				return false;
			}
			if (! is_array($result['Get'.$aRequest.'Result']['schema']['element']['complexType']['choice']['element']['complexType']['sequence']['element'])) {
				$errorDescription = 'XML Schema fehlt.';
				$this->log($errorDescription, '<span style="error"><a href="../'.basename($aFile).'">'.$errorDescription.'</a></span>');
				fwrite($fh,'</table>'."\n");
				$this->closeWithError($fh);
				return false;
			}
			// extract column keys, and write table header
			if ($isFirstRow) {
				$isFirstRow = false;
				$columnKeys = array();
				foreach ($result['Get'.$aRequest.'Result']['schema']['element']['complexType']['choice']['element']['complexType']['sequence']['element'] as $def) {
					$columnKeys[] = $def['!name'];
				}
				fwrite($fh,'<thead><tr><th>#</th>'."\n");
				foreach ($columnKeys as $columnKey) {
					fwrite($fh,'<th>
					'.htmlspecialchars($columnKey)
					.'</th>'."\n");
				}
				if ($withData) {
					fwrite($fh,'<th>ILIAS</th>'."\n");
				}
				if ($withSubscription) {
					fwrite($fh,'<th>Teilnahmen</th>'."\n");
				}
				fwrite($fh,'<th>!!</th>
				</tr>
				</thead>'."\n");
			}
			// write table rows
			if (! is_array($result['Get'.$aRequest.'Result']['diffgram'])) {
				break;
			}
			$data = &$result['Get'.$aRequest.'Result']['diffgram']['ds'.$aDataset][$aDataset];
			if ($this->isAssocArray($data)) {
				$data = array($data);
			}
			if (count($data) == 0) {
				break;
			}
			
			$fwriteBuffer2 = '';
			foreach ($data as $row) {
				$count++;
				
				if($count%100==0){
					fwrite($fh,$fwriteBuffer2);
					$fwriteBuffer2 = '';
				}
				
				$rowId = $count;
				$this->ldapUsers[]='Evento:'.$row['EvtID'];

				$fwriteBuffer2.='<tr><td><a name="'.$rowId.'" id="'.$rowId.'">'.$rowId.'</a></td>'."\n";
				foreach ($columnKeys as $columnKey) {
					$value = $row[$columnKey];
					$sortValue = $this->toSortValue($columnKey, $value, $rowId, $row['Login'], $errors);
					$formattedValue = $this->toFormattedValue($columnKey, $value, $rowId, $row['Login'], $errors);

					if ($withSubscription) {
						if ($columnKey=='EvtID') {
							$formattedValue = '<a href="users/user'.htmlspecialchars($value).'.php'.'">'.$formattedValue.'</a>';
						}
					}
					if ($columnKey == 'Login') {
						if (strlen(trim($value)) == 0) {
							$formattedValue = '<span class="error">-Login fehlt-</span>';
							$errors->addError($rowId, $row['EvtID'], 'Login fehlt');
						} else if (! preg_match('/^[a-z]+[a-z0-9]{2,}$/', $value)) {
							$formattedValue = '<span class="error">'.$formattedValue.'</span>';
							$errors->addError($rowId, $row['Login'], 'Login ungültig');
						}
					}

					$fwriteBuffer2.='<td sorttable_customkey="'.$sortValue.'">'.$formattedValue.'</td>'."\n";
				}
				if ($withData) {
					$fwriteBuffer2.='<td>';
					if (! $errors->hasErrorsInRow($rowId)) {
						$message = $this->importUserData($row, $rowId, $errors);
						if (preg_match('/^Insert/', $message)) {
							$fwriteBuffer2.='<span class="insert">'.htmlspecialchars($message).'</span>';
						} elseif (preg_match('/^Update/', $message)) {
							$fwriteBuffer2.='<span class="update">'.htmlspecialchars($message).'</span>';
						} else {
							$fwriteBuffer2.='<span class="error">'.htmlspecialchars($message).'</span>';
							$errors->addError($rowId, $row['Login'], 'Daten fehlerhaft');
						}
					} else {
							$fwriteBuffer2.='<span class="error">Ignoriert</span>';
					}
					$fwriteBuffer2.='</td>';
				}
				if ($withSubscription) {
					if ($errors->hasErrorsInRow($rowId)) {
						$fwriteBuffer2.='<td>';
					} else {
						$subscriberCount = $this->importUserSubscriptions(dirname($aFile).'/users/user'.$row['EvtID'].'.php', 'AnmeldungenByEvtID', 'Anmeldungen', $row['EvtID'], $row['FirstName'].' '.$row['LastName']);
						$fwriteBuffer2.='<td sorttable_customkey="'.(($subscriberCount === false) ? -1 : $subscriberCount).'">';
						if ($subscriberCount === false) {
							$fwriteBuffer2.='<span class="error">Fehler</span>';
							$errors->addError($rowId, $row['Login'], 'Anmeldungen fehlerhaft');
						} else {
							$fwriteBuffer2.= '<span class="success">'.$subscriberCount.'</span>';
						}
					}
					$fwriteBuffer2.='</td>'."\n";
				}
				if ($errors->hasErrorsInRow($rowId)) {
					$fwriteBuffer2.='<td><span class="info">!!</span></td>'."\n";
				} elseif ($errors->hasWarningsInRow($rowId)) {
					$fwriteBuffer2.='<td><span class="error">**</span></td>'."\n";
				} else {
					$fwriteBuffer2.='<td sorttable_customkey="z"></td>'."\n";
				}
				$fwriteBuffer2.='</tr>'."\n";
				
			}
			fwrite($fh,$fwriteBuffer2);
		}

		fwrite($fh,'</table>
		<div class="footer">
		<p>'.$count.' Datensätze</p>'."\n");
		$errors->writeErrors($fh);

		$end = time();
		fwrite($fh, '<p>'.$this->toElapsed($start, $end).'</p>
		</div>
		</body>
		</html>'."\n");
		fclose($fh);
		return $count;
	}

	/**
	 *
	 * @param associative array $data with keys 'Login', 'FirstName', 'LastName',
	 * 'Password', 'Gender', 'PictureFilename', 'Email', 'Email2', 'Email3'.
	 * @return Returns a message describing the action that was taken.
	 */
	function importUserData($data, $rowIndex, $errors) {
		$message = '';

		require_once 'Services/User/classes/class.ilObjUser.php';
		$idByLogin = ilObjUser::getUserIdByLogin($data['Login']);
		$idsByMatriculation = $this->_getUserIdsByMatriculation('Evento:'.$data['EvtID']);
		$idsByEmail = strlen(trim($data['Email'])) == 0 ? array() : $this->_reallyGetUserIdsByEMail($data['Email']);
		foreach (strlen(trim($data['Email2'])) == 0 ? array() : $this->_reallyGetUserIdsByEMail($data['Email2']) as $id) {
			if (! in_array($id, $idsByEmail)) {
				$idsByEmail[] = $id;
			}
		}
		foreach (strlen(trim($data['Email3'])) == 0 ? array() : $this->_reallyGetUserIdsByEmail($data['Email3']) as $id) {
			if (! in_array($id, $idsByEmail)) {
				$idsByEmail[] = $id;
			}
		}
		$usrId = 0;
                
		//print_r($data);

/*
print 	' idsByMatr:'.print_r($idsByMatriculation,true)."\n".
	' idByLogin:'.print_r($idByLogin,true)."\n".
	' idsByEmail:'.print_r($idsByEmail,true)."\n".
	"\n";
*/


		if (count($idsByMatriculation) == 0 &&
			$idByLogin == 0 &&
			count($idsByEmail) == 0) {

			// We couldn't find a user account neither by
			// matriculation, login nor e-mail
			// --> Insert new user account.
			$action = 'new';
			$message .= 'Insert';

		} else if (count($idsByMatriculation) == 0 &&
			$idByLogin != 0) {

			// We couldn't find a user account by matriculation, but we found
			// one by login.

			$objByLogin = new ilObjUser($idByLogin);
			$objByLogin->read();

            		if (substr($objByLogin->getMatriculation(),0,7) == 'Evento:') {
				// The user account by login has a different evento number.
				// --> Rename and deactivate conflicting account
				//     and then insert new user account.
				$message .= 'Insert.';
				$errors->addWarning($rowIndex, $data['Login'], 'Bereinigter Benutzernamen-Konflikt');
				$message .= $this->renameAndDeactivateUser($idByLogin);
				$action = 'new';

			} else if ($objByLogin->getMatriculation() == $objByLogin->getLogin()) {
				// The user account by login has a matriculation from ldap
				// --> Update user account.
				$message .= 'Update Zuordnung über login';
				$action = 'update';
				$usrId = $idByLogin;
				
			} else if (strlen($objByLogin->getMatriculation()) != 0) {
				// The user account by login has a matriculation of some kind
				// --> Bail
				$message .= 'Konflikt. Matrikelnr. des alten Kontos stammt nicht aus Evento:'.$objByLogin->getMatriculation();
				$errors->addError($rowIndex, $data['Login'], 'Unbereinigter Konflikt');
				$action = 'conflict';

			} else {
				// The user account by login has no matriculation
				// --> Update user account.
				$message .= 'Update Zuordnung über login';
				$action = 'update';
				$usrId = $idByLogin;
			}

		} else if (count($idsByMatriculation) == 0 &&
			$idByLogin == 0 &&
			count($idsByEmail) == 1) {

			// We couldn't find a user account by matriculation, but we found
			// one by e-mail.

			$objByEmail = new ilObjUser($idsByEmail[0]);
			$objByEmail->read();
			if (substr($objByEmail->getLogin(),0,4) == 'hsw_' ||
				substr($objByEmail->getLogin(),0,6) == 'hsluw_') {
				// The user account by e-mail has a login which starts with
				// 'hsw_' or 'hsluw_'.
				// Therefore, this is a temporary user account which is used
				// during the application procedure and then is deactivated.
				// --> Insert new user account.
				$message .= 'Insert.';
				$message .= ' HSLU-W Aufnahme-Konto "'.$objByEmail->getLogin().'" ignoriert.';
				$errors->addWarning($rowIndex, $data['Login'], 'HSLU-W Aufnahme-Konto "'.$objByEmail->getLogin().'" ignoriert.');
				$action = 'new';

			} else if (substr($objByEmail->getMatriculation(),0,7) == 'Evento:') {
				// The user account by e-mail has a different evento number.
				// --> Rename and deactivate conflicting account
				//     and then insert new user account.
				// XXX - Does this action make sense? Shouldn't we just leave
				//       the other user account alone?
				$message .= 'Insert.';
				$errors->addWarning($rowIndex, $data['Login'], 'Bereinigter Benutzernamen-Konflikt');
				$message .= $this->renameAndDeactivateUser($idsByEmail[0]);
				$action = 'new';

			} else if (strlen($objByEmail->getMatriculation()) != 0) {
				// The user account by login has a matriculation of some kind
				// --> Bail
				$message .= 'Konflikt';
				$errors->addError($rowIndex, $data['Login'], 'Unbereinigter Konflikt');
				$action = 'conflict';

			} else {
				// The user account by login has no matriculation
				// --> Update user account.
				$message .= 'Update Zuordnung über E-Mail.';
				$action = 'update';
				$usrId = $idsByEmail[0];
			}

		} else if (count($idsByMatriculation) == 1 &&
			$idByLogin != 0 &&
			in_array($idByLogin, $idsByMatriculation)) {

			// We found a user account by matriculation and by login.
			// --> Update user account.
			$message .= 'Update';
			$action = 'update';
			$usrId = $idsByMatriculation[0];

		} else if (count($idsByMatriculation) == 1 &&
			$idByLogin == 0) {

			// We found a user account by matriculation but with the wrong login.
			// The correct login is not taken by another user account.
			// --> Update user account.
			$message .= 'Update Zuordnung über Matrikelnr.';
			$action = 'update';
			$usrId = $idsByMatriculation[0];

		} else if (count($idsByMatriculation) == 1 &&
			$idByLogin != 0 &&
			!in_array($idByLogin, $idsByMatriculation)) {

			// We found a user account by matriculation but with the wrong
			// login. The login is taken by another user account.
			// --> Rename and deactivate conflicting account, then update user account.
			$message .= 'Update.';
			$errors->addWarning($rowIndex, $data['Login'], 'Bereinigter Benutzernamen-Konflikt');
			$message .= $this->renameAndDeactivateUser($idByLogin);
			$action = 'update';
			$usrId = $idsByMatriculation[0];

		} else {
			$action = 'error';
			$errors->addError($rowIndex, $data['Login'], 'Unbereinigter Konflikt');
		}

		// perform action
		switch ($action) {
			case 'new' :
				$usrObj = $this->insertUser($data, $rowIndex, $errors);
				$usrId = $usrObj->getId();
				break;
			case 'update' :
				$message .= $this->updateUser($usrId, $data, $rowIndex, $errors);
				break;
		}

		// Append additional remarks to message
		if (count($idsByMatriculation) > 1 ||
			count($idsByMatriculation) == 1 && $idsByMatriculation[0] != $usrId) {
			$message .= "\n".'Gleiche Matrikelnr. wie ';
			$isFirst = true;
			foreach ($idsByMatriculation as $index => $id) {
				if (! $isFirst) {
					$message .= ', ';
				}
				if ($id != $usrId) {
					$message .= ilObjUser::_lookupLogin($id);
					$isFirst = false;
				}
			}
		}
		if (count($idsByEmail) > 1 ||
			count($idsByEmail) == 1 && $idsByEmail[0] != $usrId) {
			$message .= "\n".'Gleiches E-Mail wie ';
			$isFirst = true;
			foreach ($idsByEmail as $index => $id) {
				$aLogin = ilObjUser::_lookupLogin($id);
				if ($aLogin != $data['Login']) {
					if ($isFirst) {
						$isFirst = false;
					} else {
						$message .= ', ';
					}
					$message .= $aLogin;
				}
			}
		}


		return $message;
	}

	function insertUser($data, $rowIndex, $errors) {
		global $ilSetting;

		$userObj = new ilObjUser();

		$userObj->setLogin($data['Login']);
		$userObj->setFirstname($data['FirstName']);
		$userObj->setLastname($data['LastName']);
		$userObj->setGender(($data['Gender']=='F') ? 'f':'m');
		$userObj->setEmail($data['Email']);
		$userObj->setTitle($userObj->getFullname());
		$userObj->setDescription($userObj->getEmail());
		$userObj->setMatriculation('Evento:'.$data['EvtID']);
		$userObj->setExternalAccount($data['EvtID'].'@hslu.ch');
		$userObj->setAuthMode($this->AuthMode);
		if($this->AuthMode!='ldap') $userObj->setPasswd(strtolower($data['Password']), IL_PASSWD_MD5);

		$userObj->setActive(true);
		$userObj->setTimeLimitUnlimited(false);
		$userObj->setTimeLimitFrom($this->nowTime);
		$userObj->setTimeLimitUntil($this->nowTime+365*24*60*60);


		// Finally before saving new user.
		// Check if profile is incomplete
		//$userObj->setProfileIncomplete($this->checkProfileIncomplete($userObj));
		$userObj->create();

		//insert user data in table user_data
		$userObj->saveAsNew(false);

		// Set default prefs
		$userObj->setPref('hits_per_page','100'); //100 hits per page
		$userObj->setPref('show_users_online','associated'); //nur Leute aus meinen Kursen zeigen
		
		$userObj->setPref('public_profile','y'); //profil standard öffentlich
		$userObj->setPref('public_upload','y'); //profilbild öffentlich
		$userObj->setPref('public_email','y'); //profilbild öffentlich

		$userObj->writePrefs();

		// update mail preferences
		$this->updateMailPreferences($userObj->getId());

		// Assign user to global user role
		$this->assignToRole($userObj->getId(), $this->userRoleId);

		// Upload image
		$imageFile = $this->imageDir.'/'.$data['PictureFilename'];
		if (file_exists($imageFile)) {
			ilObjUser::_uploadPersonalPicture($imageFile, $userObj->getId());
		}
		return $userObj;
	}
	
	function updateMailPreferences($usrId){
			global $ilDB;
			$ilDB->manipulateF("UPDATE mail_options SET incoming_type = '2' WHERE user_id = %s", array("integer"), array($usrId)); //mail nur intern nach export
	}
	
	function updateUser($usrId, $data, $rowIndex, $errors) {
		$message = "";

		global $ilSetting, $ilUser;

		$userObj = new ilObjUser($usrId);
		$userObj->read();
		$userObj->readPrefs();

		$userObj->setFirstname($data['FirstName']);
		$userObj->setLastname($data['LastName']);
		$userObj->setGender(($data['Gender']=='F') ? 'f':'m');

		if (preg_match('/(@|\.)fhz\.ch$/',$userObj->getEmail())
            && $data['Email']
            && ! preg_match('/(@|\.)fhz\.ch$/',$data['Email']) ) {
			$message = ' Altes Email:'.$userObj->getEmail()."\n".$message;
			$errors->addWarning($rowIndex, $data['Login'], 'E-Mail Adresse aktualisiert');
			$userObj->setEmail($data['Email']);
		}
		$userObj->setTitle($userObj->getFullname());
		$userObj->setDescription($userObj->getEmail());
		$userObj->setMatriculation('Evento:'.$data['EvtID']);
		$userObj->setExternalAccount($data['EvtID'].'@hslu.ch');
		$userObj->setAuthMode($this->AuthMode);
		if($this->AuthMode=='ldap') $userObj->setPasswd('', IL_PASSWD_MD5);

		$userObj->setActive(true);
		$userObj->setTimeLimitUnlimited(false);
		if ($userObj->getTimeLimitFrom() == 0 ||
				$userObj->getTimeLimitFrom() > $this->nowTime) {

			$userObj->setTimeLimitFrom($this->nowTime);
		}
		$userObj->setTimeLimitUntil($this->nowTime+365*24*60*60);

		//make profile public by default - added smoor 2010-11
		$userObj->setPref('public_profile','y'); //profil standard öffentlich
		$userObj->setPref('public_upload','y'); //profilbild öffentlich
		$userObj->setPref('public_email','y'); //profilbild öffentlich
		//
		// Finally before saving new user.
		// Check if profile is incomplete
		//$userObj->setProfileIncomplete($this->checkProfileIncomplete($userObj));
		$userObj->setPasswd('', IL_PASSWD_PLAIN);
		$userObj->update();
		//$userObj->writePrefs(); //this is not needed. already called in $userObj->update();

		// Assign user to global user role
		$this->assignToRole($userObj->getId(), $this->userRoleId);

		// Upload image
		if (strpos(ilObjUser::_getPersonalPicturePath($userObj->getId(), "small", false),'/no_photo') !== false) {
			$imageFile = $this->imageDir.'/'.$data['PictureFilename'];
			if (file_exists($imageFile)) {
				ilObjUser::_uploadPersonalPicture($imageFile, $userObj->getId());
			}
		}

		$oldLogin = $userObj->getLogin();
		if ($oldLogin != $data['Login']) {
			$message = ' Altes Login: "'.$oldLogin."\"\n".$message;
			$errors->addWarning($rowIndex, $data['Login'], 'Login umbenannt von "'.$oldLogin.'" nach "'.$data['Login'].'".');
			
			$this->changeLoginName($userObj->getId(),$data['Login']);
			//$userObj->login = $data['Login'];
			$newlogin = $data['Login'];
			
			//include_once('Services/Mail/classes/class.ilMail.php');
			//$mail = new ilMail($ilUser->getId());
			$subject = 'Änderung Ihrer Zugangsdaten für die Lernplattform ILIAS';
			$gen = $userObj->getGender();
			$body = ($gen=='m'?'Sehr geehrter Herr ':'Sehr geehrte Frau ').$userObj->getLastname().",\n".
				"\n".
				"Aus administrativen Gründen musste Ihr Benutzerkonto auf der Lernplattform ".
				"ILIAS an der Hochschule Luzern geändert werden.\n\n".
				"Ihr Benutzername lautet neu ".$newlogin." statt ".$oldLogin.".\n\n".
				"Ihre neuen Zugriffsdaten lauten:\n\n".
				"Einstiegsseite: https://elearning.hslu.ch\n\n".
				"Benutzername: ".$newlogin."\n\n".
				"E-Mail Adresse: ".$userObj->getEmail()."\n\n".
				"Das Passwort ist nicht geändert worden. Falls Sie das Passwort nicht mehr haben, können Sie sich mit dem Link ".
				"\"Passwortunterstützung\" auf der Einstiegsseite ein neues Passwort setzen.\n\n".
				"Dies ist eine automatisch generierte Nachricht. Falls Sie Fragen zu dieser ".
				"Änderung haben, nehmen Sie bitte mit der IT Hotline Kontakt auf.\n\n".
				"Mit freundlichen Grüssen\n\n".
				"Hochschule Luzern\n".
				"IT Support\n".
				"Telefon IT-Support: 041 228 21 21\n".
				"E-Mail: informatikhotline@hslu.ch\n"
				;
			
			mail ( $userObj->getEmail(), $subject, $body );
			
			/*
				
			// @ToDo - We should retrieve the admin e-mail address from ILIAS Settings!
			if ($this->sendMailsToUsers) {
				$cc = array();
				$cc[] = $userObj->getEmail();
				if (strlen($data['Email']) > 0 && ! in_array($data['Email'],$cc)) {
					$cc[] = $data['Email'];
				}
				if (strlen($data['Email2']) > 0 && ! in_array($data['Email2'],$cc)) {
					$cc[] = $data['Email2'];
				}
				if (strlen($data['Email3']) > 0 && ! in_array($data['Email3'],$cc)) {
					$cc[] = $data['Email3'];
				}
				
				//$mail->sendMail($newlogin,implode(',',$cc),'',$subject,$body,array(),array('system'));
			} else {
				//$mail->sendMail('simon.moor@hslu.ch','','',$subject,$body,array(),array('system'));
			}
			*/
		}
		return $message;
	}
	function renameAndDeactivateUser($usrId) {
		$message = "";

		global $ilSetting, $ilUser;

		$userObj = new ilObjUser($usrId);
		$userObj->read();
		$userObj->setActive(false);
		$userObj->update();
		$userObj->setLogin(date('Ymd').'_'.$userObj->getLogin());
		$userObj->updateLogin($userObj->getLogin());
		$message .= "\n".'Namenskonflikt mit '.$userObj->getLogin().', '.
			$userObj->getFirstname().' '.$userObj->getLastname().', '.
			$userObj->getEmail().', '.$userObj->getMatriculation().' bereinigt.';

		return $message;
	}

	/**
	 * Imports events from evento and optionally assigns users to the corresponding
	 * groups and courses in ILIAS.
	 *
	 * param $aFile the filename into which to write log data
	 * param $aRequest the name of the Soap-Request for Evento
	 * param $aDataset the name of the dataset object returned by Evento.
	 * param $aTitle the title used on the log data
	 *
	 * Returns false on failure, returns the number of events in case of success.
	 */
	function importEvents($aFile, $aRequest, $aDataset, $aTitle, $withSubscription=false) {
		global $rbacreview;


		$fh = fopen($aFile, 'wb');
		chmod($aFile, 0777);
		$isFirstRow = true;
		$columnKeys = array();
		$count = 0;
		$errors = new ErrorsInPage($this, '../'.basename($aFile));
		fwrite($fh,'<table id="importEvents'.$aRequest.'Table" class="sortable">'."\n");
		for ($page = 1; $this->maxPages == -1 || $page <= $this->maxPages; $page++) {
			unset($result);
			$result = &$this->callWebService('Get'.$aRequest, array('parameters'=>array('pagesize'=>$this->pagesize,'pagenumber'=>$page)));
			if ($result == false) {
				fwrite($fh,'</table>'."\n");
				$this->closeWithError($fh);
				return false;
			}
			// extract column keys, and write table header
			if ($isFirstRow) {
				$isFirstRow = false;
				$columnKeys = array();
				foreach ($result['Get'.$aRequest.'Result']['schema']['element']['complexType']['choice']['element']['complexType']['sequence']['element'] as $def) {
					$columnKeys[] = $def['!name'];
				}
				fwrite($fh,'<thead><tr><th>#</th>'."\n");
				foreach ($columnKeys as $columnKey) {
					fwrite($fh,'<th>'.htmlspecialchars($columnKey).'</th>'."\n");
				}
				if ($withSubscription) {
					fwrite($fh,'<th>ILIAS</th><th>Anz. Teilnehmer</th>'."\n");
				}
				fwrite($fh,'<th>!!</th></tr></thead>'."\n");
			}
			// write table rows, if there are any
			if (! is_array($result['Get'.$aRequest.'Result']['diffgram'])) {
				break;
			}
			$data = &$result['Get'.$aRequest.'Result']['diffgram']['ds'.$aDataset][$aDataset];
			if ($this->isAssocArray($data)) {
				$data = array($data);
			}
			if (count($data) == 0) {
				break;
			}
            
			$fwriteBuffer2='';
			foreach ($data as $row) {
				$count++;

				if($count%100==0){
					fwrite($fh,$fwriteBuffer2);
					$fwriteBuffer2 = '';
				}
				
				$fwriteBuffer2.='<tr>
				<td><a name="'.$count.'" id="'.$count.'">'.$count.'</a></td>'."\n";

				foreach ($columnKeys as $columnKey) {
					$value = $row[$columnKey];
					$sortValue = $this->toSortValue($columnKey, $value, $row['AnlassBezKurz'], $errors);
					$formattedValue = $this->toFormattedValue($columnKey, $value, $row['AnlassBezKurz'], $errors);

					if ($withSubscription) {
						if ($columnKey=='AnlassID') {
							$formattedValue = '<a rel="uidialog" onclick="return false;" href="'.$this->nowTime.'/events/event'.htmlspecialchars($value).'.php'.'">'.$formattedValue.'</a>';
						}
					}

					if ($columnKey == 'AnlassBezKurz') {
						if (! preg_match('/^(HSLU|DK|SA|M|TA|W)(\\.[A-Z0-9]([A-Za-z0-9\\-+_&]*[A-Za-z0-9])?){2,}$/', $value)) {
							$formattedValue = '<span class="info">'.$formattedValue.'</span>';
							$errors->addWarning($count, $row['AnlassBezKurz'], 'Bezeichnung ungültig', false);
						}
					}

					$fwriteBuffer2.='<td sorttable_customkey="'.$sortValue.'">'.$formattedValue.'</td>'."\n";
				}
				if ($withSubscription) {
					if ($errors->hasErrorsInRow($count)) {
						$fwriteBuffer2.='<td><span class="info">Ignoriert</span></td>'."\n";
					} else {
						$searchName = '#member@['.$row['AnlassBezKurz'].']';
						$roleIds = $rbacreview->searchRolesByMailboxAddressList($searchName);
						$fwriteBuffer2.='<td>';
						if (count($roleIds) == 0) {
							$fwriteBuffer2.='<span class="info2">'.$searchName.'</span>';
							$errors->addError($count, $row['AnlassBezKurz'], 'Existiert nicht in ILIAS', false);
						} elseif (count($roleIds) == 1) {
							$fwriteBuffer2.='<span class="success">'.$this->toRolePath($roleIds[0], true).'</span>';
						} else {
							foreach ($roleIds as $roleId) {
								$fwriteBuffer2.='<p class="error">'.$this->toRolePath($roleId, true).'</p>';
							}
							$errors->addError($count, $row['AnlassBezKurz'], 'Mehrmals gleichnamig in ILIAS');
						}
						$fwriteBuffer2.='</td>'."\n";
					}
					if ($errors->hasErrorsInRow($count)) {
						//fwrite($fh,'<td><span class="info">Ignoriert</span></td>'."\n");
						$fwriteBuffer2.='<td>Ignoriert</td>'."\n";
					} else {
						$importDisabledBy=$this->getImportDisabledBy($roleIds[0]);
						if ($importDisabledBy!=null) {
							// Ignore if import is disabled for this role
							$errors->addWarning($count, $row['AnlassBezKurz'], 'Import deaktiviert in "'.$importDisabledBy['title'].'".');
							$fwriteBuffer2.='<td><span class="error">Import deaktiviert in "'.$importDisabledBy['title'].'"</span></td>'."\n";
						} else {
							$fwriteBuffer2.='<td sorttable_customkey="'.(($subscriberCount === false) ? -1 : $subscriberCount).'">';
							$subscriberCount = $this->importEventSubscriptions(dirname($aFile).'/events/event'.$row['AnlassID'].'.php', 'AnmeldungenByAnlassID', 'Anmeldungen', $row['AnlassID'], $row['AnlassBezKurz']);
							if ($subscriberCount === false) {
								$fwriteBuffer2.='<span class="error">Fehler</span>';
								$errors->addError($count, $row['AnlassBezKurz'], 'Anmeldungen fehlerhaft.');
							} else {
								$fwriteBuffer2.='<span class="success">'.$subscriberCount.'</span>';
							}
							$objId = $rbacreview->getObjectOfRole($roleIds[0]);
							$descriptionUpdated = $this->updateObjectDescription($objId, $row);
							if ($descriptionUpdated) {
								$fwriteBuffer2.='<br><span class="success">Beschreibung aktualisiert.</span>';
							}
							$fwriteBuffer2.='</td>'."\n";
						}
					}
				}
				if ($errors->hasErrorsInRow($count)) {
					$fwriteBuffer2.='<td><span class="error2">!!</span></td>'."\n";
				} elseif ($errors->hasWarningsInRow($count)) {
					$fwriteBuffer2.='<td><span class="error">**</span></td>'."\n";
				} else {
					$fwriteBuffer2.='<td sorttable_customkey="z"></td>'."\n";
				}
				$fwriteBuffer2.='</tr>'."\n";
			}
			fwrite($fh,$fwriteBuffer2);
			
			if (count($data) < $this->pagesize ) {
				break;
			}
		}

		fwrite($fh,'</table>'."\n");
		fwrite($fh,'<div class="footer">'."\n");
		fwrite($fh,'<p>'.$count.' Datensätze</p>'."\n");
		$errors->writeErrors($fh);
		$end = time();
		fwrite($fh, '<p>'.$this->toElapsed($start, $end).'</p>'."\n");
		fwrite($fh,'</div>'."\n");
		//fclose($fh);
		return $count;
	}
	/**
	 * Returns the number of rows.
	 */
	function importEventSubscriptions($aFile, $aRequest, $aDataset, $aID, $aTitle) {
		global $rbacreview, $ilUser;

		$start = time();

		// Write Students
		$fh = fopen($aFile, 'wb');
		chmod($aFile, 0777);

		$searchName = '#member@['.$aTitle.']';
		$roleIds = $rbacreview->searchRolesByMailboxAddressList($searchName);
		$roleId = null;
		if (count($roleIds) == 1) {
			$roleId = $roleIds[0];
			fwrite($fh, '<p class="success">'.$this->toRolePath($roleId).'</p>'."\n");
		} else {
			fwrite($fh, '<p class="error">'.$searchName.'</p>'."\n");
		}

		// Marks all assignments in the specified role for potential deassignment
		//$this->markForDeassignment($roleId); //this was a call to the old function
		
		$assignedUsers=$rbacreview->assignedUsers($roleId);
		foreach($assignedUsers as $u){
			$this->markForDeassignment($u,$roleId);
		}
		
		$isFirstRow = true;
		$columnKeys = array();
		$count = 0;
		$errors = new ErrorsInPage($this, '../'.basename($aFile));
		fwrite($fh,'<table class="sortable">'."\n");
		for ($page = 1; $this->maxPages == -1 || $page <= $this->maxPages; $page++) {
			unset ($result);
			$result = &$this->callWebService('Get'.$aRequest, array('parameters'=>array('anlassid'=>$aID, 'pagesize'=>$this->pagesize,'pagenumber'=>$page)));
			if ($result == false) {
				fwrite($fh,'</table>'."\n");
				$this->closeWithError($fh);
				return false;
			}
			if (! @is_array($result['Get'.$aRequest.'Result']['schema']['element']['complexType']['choice']['element']['complexType']['sequence']['element'])) {
				fwrite($fh,'</table>'."\n");
				fwrite($fh,'<p class="error">No data received.</p>'."\n");
				//fwrite($fh,'</body>'."\n");
				//fwrite($fh,'</html>'."\n");
				fclose($fh);
				return false;
			}
			// extract column keys, and write table header
			if ($isFirstRow) {
				$isFirstRow = false;
				$columnKeys = array();
				foreach ($result['Get'.$aRequest.'Result']['schema']['element']['complexType']['choice']['element']['complexType']['sequence']['element'] as $def) {
					$columnKeys[] = $def['!name'];
				}
				fwrite($fh,'<thead>'."\n");
				fwrite($fh,'<tr>'."\n");
				fwrite($fh,'<th>#</th>'."\n");
				foreach ($columnKeys as $columnKey) {
					fwrite($fh,'<th>');
					fwrite($fh,htmlspecialchars($columnKey));
					fwrite($fh,'</th>'."\n");
				}
				fwrite($fh,'<th>!!</th>'."\n");
				fwrite($fh,'</tr>'."\n");
				fwrite($fh,'</thead>'."\n");
			}
			// write table rows
			if (! is_array($result['Get'.$aRequest.'Result']['diffgram'])) {
				break;
			}
			$data = $result['Get'.$aRequest.'Result']['diffgram']['ds'.$aDataset][$aDataset];
			if ($this->isAssocArray($data)) {
				$data = array($data);
			}
			if (count($data) == 0) {
				break;
			}
			foreach ($data as $row) {
				$count++;

				fwrite($fh,'<tr>'."\n");
				fwrite($fh,'<td>');
				fwrite($fh, '<a name="'.$count.'" id="'.$count.'">');
				fwrite($fh,$count);
				fwrite($fh, '</a>');
				fwrite($fh,'</td>'."\n");

				if ($roleId != null) {
					$idsByMatriculation = array(0);
					$idsByMatriculation = $this->_getUserIdsByMatriculation('Evento:'.$row['EvtID']);
					if (count($idsByMatriculation) == 0) {
						$errors->addError($count, $row['EvtID'], 'Benutzer existiert nicht');
					} else if (count($idsByMatriculation) > 1) {
						$errors->addError($count, $row['EvtID'], 'Mehrere Benutzer mit gleicher EvtID');
					} else {
						$this->assignToRoleWithParents($idsByMatriculation[0], $roleId);
						$assigned=true;
					}
					$this->unmarkForDeassignment($idsByMatriculation[0],$roleId);
				}

				foreach ($columnKeys as $columnKey) {
					$value = $row[$columnKey];
					$sortValue = $this->toSortValue($columnKey, $value, $row['EvtID'], $errors);
					$formattedValue = $this->toFormattedValue($columnKey, $value, $row['EvtID'], $errors);

					if ($columnKey == 'EvtID') {
						if ($roleId != null) {
							if (count($idsByMatriculation) == 1) {
								$formattedValue = '<span class="success">'.$formattedValue.'</span>';
							} else {
								$formattedValue = '<span class="error">'.$formattedValue.'</span>';
							}
						}
					} elseif ($columnKey == 'AnlassID') {
						if ($roleId == null) {
							$errors->addError($count, $row['AnlassID'], 'Anlass in ILIAS nicht gefunden');
							$formattedValue = '<span class="error">'.$formattedValue.'</span>';
						} else {
							$formattedValue = '<span class="success">'.$formattedValue.'</span>';
						}
					}

					fwrite($fh,'<td sorttable_customkey="'.$sortValue.'">');
					fwrite($fh, $formattedValue);
					fwrite($fh,'</td>'."\n");
				}



				if ($errors->hasErrorsInRow($count)) {
					fwrite($fh,'<td><span class="error">!!</span></td>'."\n");
				} elseif ($errors->hasWarningsInRow($count)) {
					fwrite($fh,'<td><span class="error">**</span></td>'."\n");
				} else {
					fwrite($fh,'<td sorttable_customkey="z"></td>'."\n");
				}
				fwrite($fh,'</tr>'."\n");
			}
			if (count($data) < $this->pagesize) {
				break;
			}
		}

		fwrite($fh,'</table>'."\n");
		//fwrite($fh,'<p>'.(memory_get_usage()/1024/1024).' MB used</p>'."\n");
		//fwrite($fh,'<p>'.(memory_get_peak_usage()/1024/1024).' MB peak</p>'."\n");
		fwrite($fh,'<p>'.$count.' Datensätze</p>'."\n");
		$errors->writeErrors($fh);
		$end = time();
		fwrite($fh, '<p>'.$this->toElapsed($start, $end).'</p>'."\n");

		fclose($fh);
		return ($errors->hasErrorsInPage() ? false : $count);
	}
	/**
	 * Returns the number of rows.
	 */
	function importUserSubscriptions($aFile, $aRequest, $aDataset, $aID, $aTitle, $aRoleID) {
		$start = time();

		// Write Students
		$fh = fopen($aFile, 'wb');
		chmod($aFile,0777);
		
		$isFirstRow = true;
		$columnKeys = array();
		$count = 0;
		$errors = array();
		fwrite($fh,'<table class="sortable">'."\n");
		for ($page = 1; $this->maxPages == -1 || $page <= $this->maxPages; $page++) {
			unset ($result);
			$result = &$this->callWebService('Get'.$aRequest, array('parameters'=>array('evtid'=>$aID, 'pagesize'=>$this->pagesize,'pagenumber'=>$page)));
			if ($result == false) {
				fwrite($fh,'</table>'."\n");
				$this->closeWithError($fh);
				return false;
			}
			if (! @is_array($result['Get'.$aRequest.'Result']['schema']['element']['complexType']['choice']['element']['complexType']['sequence']['element'])) {
				fwrite($fh,'</table>'."\n");
				fwrite($fh,'<p class="error">No data received.</p>'."\n");
				fclose($fh);
				return false;
			}
			// extract column keys, and write table header
			if ($isFirstRow) {
				$isFirstRow = false;
				$columnKeys = array();
				foreach ($result['Get'.$aRequest.'Result']['schema']['element']['complexType']['choice']['element']['complexType']['sequence']['element'] as $def) {
					$columnKeys[] = $def['!name'];
				}
				fwrite($fh,'<thead>'."\n");
				fwrite($fh,'<tr>'."\n");
				fwrite($fh,'<th>#</th>'."\n");
				foreach ($columnKeys as $columnKey) {
					fwrite($fh,'<th>');
					fwrite($fh,htmlspecialchars($columnKey));
					fwrite($fh,'</th>'."\n");
				}
				fwrite($fh,'<th>!!</th>'."\n");
				fwrite($fh,'</tr>'."\n");
				fwrite($fh,'</thead>'."\n");
			}
			// write table rows
			if (! is_array($result['Get'.$aRequest.'Result']['diffgram'])) {
				break;
			}
			$data = $result['Get'.$aRequest.'Result']['diffgram']['ds'.$aDataset][$aDataset];
			if ($this->isAssocArray($data)) {
				$data = array($data);
			}
			if (count($data) == 0) {
				break;
			}
			foreach ($data as $row) {
				$count++;

				fwrite($fh,'<tr>'."\n");
				fwrite($fh,'<td>');
				fwrite($fh, '<a name="'.$count.'" id="'.$count.'">');
				fwrite($fh,$count);
				fwrite($fh, '</a>');
				fwrite($fh,'</td>'."\n");
				foreach ($columnKeys as $columnKey) {
					$value = $row[$columnKey];
					$sortValue = $this->toSortValue($columnKey, $value, $errors);
					$formattedValue = $this->toFormattedValue($columnKey, $value, $errors);

					fwrite($fh,'<td sorttable_customkey="'.$sortValue.'">');
					fwrite($fh, $formattedValue);
					fwrite($fh,'</td>'."\n");
				}

				if (array_key_exists($count, $errors)) {
					fwrite($fh,'<td><span class="error">!!</span></td>'."\n");
				} else {
					fwrite($fh,'<td sorttable_customkey="z"></td>'."\n");
				}
				fwrite($fh,'</tr>'."\n");
			}
		}

		fwrite($fh,'</table>'."\n");
		fwrite($fh,'<div class="footer">'."\n");
		fwrite($fh,'<p>'.$count.' Datensätze</p>'."\n");
		if (count($errors)) {
			fwrite($fh,'<p>'.count($errors).' Fehler in: ');
			$isFirst = true;
			foreach ($errors as $fe => $errorDescription) {
				if ($isFirst) {
					$isFirst = false;
				} else {
					fwrite($fh, ', ');
				}
				fwrite($fh,'<a href="#'.$fe.'">'.$fe.'</a>');
				$this->log($errorDescription, '<span style="error"><a href="../'.basename($aFile).'#'.$errorDescription.'">'.$fe.'</a></span>');
			}
			fwrite($fh,'</p>'."\n");
		}
		$end = time();
		fwrite($fh, '<p>'.$this->toElapsed($start, $end).'</p>'."\n");
		fwrite($fh,'</div>'."\n");
		fclose($fh);
		return (count($errors) > 0 ? false : $count);
	}
	/**
	 * Returns true on success.
	 */
	function closeWithError($fh) {
		fwrite($fh,'<h2>Constructor error</h2><pre>' . $this->client->getError() . '</pre>');
		fwrite($fh,'<h2>Debug</h2><pre>' . htmlspecialchars($this->client->getDebug(), ENT_QUOTES) . '</pre>');
		fwrite($fh,'</body>'."\n");
		fwrite($fh,'</html>'."\n");
		fclose($fh);
	}
	/**
	 * Assigns a user to a role.
	*/
	function assignToRole($a_user_id, $a_role_id)
	{
		require_once 'Services/AccessControl/classes/class.ilObjRole.php';
		require_once 'Services/Object/classes/class.ilObject.php';

		global $rbacreview, $rbacadmin, $tree, $ilUser;

		//save that its an automatic assignment
		global $ilDB;
		$q="INSERT IGNORE INTO rbac_evento (usr_id,role_id) VALUES ('".$a_user_id."','".$a_role_id."');";
		if((int)$a_role_id!=109) $r = $ilDB->db->exec($q); //109 = standard benutzer rolle
		
		// If the user is already assigned to the role, we
		// aquire ownership of the role assignment if it is not owned yet,
		// and we remove the marker.
		if ($rbacreview->isAssigned($a_user_id, $a_role_id))
		{
			//$rbacadmin->replaceAssignmentOwner($a_role_id, $a_user_id,null,$ilUser->getId());
			//$rbacadmin->replaceAssignmentOwner($a_role_id, $a_user_id,-($ilUser->getId()),$ilUser->getId());
			return;
		}

		// If it is a course role, use the ilCourseMember object to assign
		// the user to the role

		$rbacadmin->assignUser($a_role_id, $a_user_id, true);
		if (in_array($a_role_id,$this->roleToObjectCache)) {
			$obj_id = $this->roleToObjectCache[$a_role_id];
		} else {
			$obj_id = $rbacreview->getObjectOfRole($a_role_id);
			$this->roleToObjectCache[$a_role_id]=$obj_id;
		}
		switch($type = ilObject::_lookupType($obj_id))
		{
			case 'grp':
			case 'crs':
				$ref_ids = ilObject::_getAllReferences($obj_id);
				$ref_id = current((array) $ref_ids);
				if($ref_id)
				{
					ilObjUser::_addDesktopItem($a_user_id,$ref_id,$type);
				}
				break;
			default:
				break;
		}
	}
	/**
	 * Get array of parent role ids from cache.
	 * If necessary, create a new cache entry.
	 */
	function getParentRoleIds($a_role_id)
	{
		global $rbacreview;

		if (! array_key_exists($a_role_id, $this->parentRolesCache))
		{
			$parent_role_ids = array();

			$role_obj = $this->getRoleObject($a_role_id);
			$short_role_title = substr($role_obj->getTitle(),0,12);
			$folders = $rbacreview->getFoldersAssignedToRole($a_role_id, true);
			if (count($folders) > 0)
				{
				$all_parent_role_ids = $rbacreview->getParentRoleIds($folders[0]);
				foreach ($all_parent_role_ids as $parent_role_id => $parent_role_data)
				{
					if ($parent_role_id != $a_role_id)
					{
						switch (substr($parent_role_data['title'],0,12))
						{
							case 'il_crs_admin' :
							case 'il_grp_admin' :
								if ($short_role_title == 'il_crs_admin' || $short_role_title == 'il_grp_admin')
								{
									$parent_role_ids[] = $parent_role_id;
								}
								break;
							case 'il_crs_tutor' :
							case 'il_grp_tutor' :
								if ($short_role_title == 'il_crs_tutor' || $short_role_title == 'il_grp_tutor')
								{
									$parent_role_ids[] = $parent_role_id;
								}
								break;
							case 'il_crs_membe' :
							case 'il_grp_membe' :
								if ($short_role_title == 'il_crs_membe' || $short_role_title == 'il_grp_membe')
								{
									$parent_role_ids[] = $parent_role_id;
								}
								break;
							default :
								break;
						}
					}
				}
			}
			$this->parentRolesCache[$a_role_id] = $parent_role_ids;
		}
		return $this->parentRolesCache[$a_role_id];
	}
	/**
	 * Assigns a user to a role and to all parent roles.
        */
	function assignToRoleWithParents($a_user_id, $a_role_id)
	{
		$this->assignToRole($a_user_id, $a_role_id);

		$parent_role_ids = $this->getParentRoleIds($a_role_id);
		foreach ($parent_role_ids as $parent_role_id)
		{
			$this->assignToRole($a_user_id, $parent_role_id);
		}
	}
	/**
	 * Removes all stale marks from a previous run of the import.
         */
	function removeStaleMarks()
	{
		global $rbacadmin, $ilUser;
		$a_owner_id=$ilUser->getId();
		//$rbacadmin->replaceAssignmentOwnerInILIAS(-$a_owner_id,$a_owner_id);
	}
	/**
	 * Marks assignments in the specified role for potential deassignment
	 * if the assignment has been made from the user which runs the import.
	 * <p>
	 * The marker is the negative value of the user id.
	 *
	 * Note: we mark users only once from a given role and its parents.
	 *       If we did it multiple times, then a parent course would only
	 *       have the members of the last group for which we deassigned
	 *       the members.
	 */
	 /*
	function markForDeassignment($a_role_id)
	{
		global $rbacadmin,$ilUser;

		$a_owner_id=$ilUser->getId();

		if (! array_key_exists($a_role_id, $this->markedRoles)) {
		    //$this->log('deassigning users from '.$a_role_id.' owned by '.$a_owner_id);

			//$rbacadmin->replaceAssignmentOwnerInRole($a_role_id, $a_owner_id, -$a_owner_id);
			$this->markedRoles[$a_role_id]=true;
		}

		$parent_role_ids = $this->getParentRoleIds($a_role_id);
		foreach ($parent_role_ids as $parent_role_id)
		{
			if (! array_key_exists($parent_role_id, $this->markedRoles)) {
				//$this->log('deassigning users from '.$parent_role_id.' owned by '.$a_owner_id);

				//$rbacadmin->replaceAssignmentOwnerInRole($parent_role_id, $a_owner_id, -$a_owner_id);
				$this->markedRoles[$parent_role_id]=true;
			}
		}
	}
	*/
	function markForDeassignment($userid,$a_role_id)
	{
		if(!isset($this->markedUsersForDeassignment[$userid])){
			$this->markedUsersForDeassignment[$userid]=array();
		}
		$this->markedUsersForDeassignment[$userid][$a_role_id]=$a_role_id;
	}
	function unmarkForDeassignment($userid,$a_role_id)
	{
		if(!isset($this->markedUsersForDeassignment[$userid])){
			$this->markedUsersForDeassignment[$userid]=array();
		}
		if(isset($this->markedUsersForDeassignment[$userid][$a_role_id])){
			unset($this->markedUsersForDeassignment[$userid][$a_role_id]);
		}
	}
	/**
	 * Deassigns all marked assignments.
	 */
	 /*
	function deassignMarkedAssignments()
	{
		global $rbacadmin,$ilUser;

		if(!$this->doDeassignSubscriptions) return true;
		
		$a_owner_id=$ilUser->getId();

		//$rowCount = $rbacadmin->deassignAllAssignmentsOwnedBy(-$a_owner_id);

		$this->log('Anzahl Kurszuweisungen aus Evento her gelöscht: '.$rowCount.'.');

	}
	*/
	function deassignMarkedAssignments()
	{
		global $rbacadmin,$ilUser,$ilDB;

		//if(!$this->doDeassignSubscriptions) return true;
		
		$rowCount=0;
		/*
		foreach($this->markedUsersForDeassignment as $userid=>$rolearr){
			
			if(count($rolearr)<1) continue;
			
			$inStr=implode(',', $rolearr);
			
			$q="SELECT * FROM `rbac_evento` WHERE `usr_id` ='".$userid."' AND role_id IN (".$inStr.")";
			$r = $ilDB->db->query($q);
			$rowCount+=(int)$r->numRows();
			while ($row = $r->fetchRow(DB_FETCHMODE_OBJECT))
			{
				if($this->doDeassignSubscriptions) $rbacadmin->deassignUser($row->role_id,$row->usr_id);
			}
			
			$q="DELETE FROM rbac_evento WHERE `usr_id` ='".$userid."' AND role_id IN (".$inStr.")";
			if($this->doDeassignSubscriptions) $r = $ilDB->db->query($q);
			
			if(!$this->doDeassignSubscriptions) $this->log('Würde User entfernen '.$userid.' von Rolle(n) '.$inStr);
		}
		*/
		
		if($this->doDeassignSubscriptions) $this->log('Anzahl Kurszuweisungen aus Evento her gelöscht: '.$rowCount.'.');
		if(!$this->doDeassignSubscriptions) $this->log('Anzahl Kurszuweisungen aus Evento her wären gelöscht: '.$rowCount.'.');

	}
	/**
	 * Returns the parent object of the role folder object which contains the specified role.
	 */
	function getRoleObject($a_role_id)
	{
		if (array_key_exists($a_role_id, $this->localRoleCache))
		{
			return $this->localRoleCache[$a_role_id];
		}
		else
		{
			$role_obj = new ilObjRole($a_role_id, false);
			$role_obj->read();
			$this->localRoleCache[$a_role_id] = $role_obj;
			return $role_obj;
		}

	}

	function toRolePath($roleId, $asHTML = true) {
		require_once 'Services/Link/classes/class.ilLink.php';
		global $tree, $rbacreview;

		$rolf = $rbacreview->getFoldersAssignedToRole($roleId,true);

		define("ILIAS_HTTP_PATH","../..");
		if ($tree->isInTree($rolf[0]))
		{
			// Create path. Paths which have more than 4 segments
			// are truncated in the middle.
			$tmpPath = $tree->getPathFull($rolf[0]);
			for ($i = 1, $n = count($tmpPath) - 1; $i < $n; $i++)
			{
				if ($i > 1)
				{
					if ($asHTML) {
						$path = $path.' &gt; ';
					} else {
						$path = $path.' > ';
					}
				}
				if ($i < 3 || $i > $n - 3)
				{
					if ($asHTML) {
						$link = ilLink::_getStaticLink($tmpPath[$i]['ref_id'],$tmpPath[$i]['type'],true);
						$path = $path.'<a href="'.$link.'" target="_blank">'.htmlspecialchars($tmpPath[$i]['title']).'</a>';
					} else {
						$path = $path.$tmpPath[$i]['title'];
					}
				}
				else if ($i == 3 || $i == $n - 3)
				{
					$path = $path.'...';
				}

			}
		}
		else
		{
			$path = 'Rolefolder '.$rolf[0].' not found in tree! (Role '.$roleId.')';
			if ($asHTML) {
				$path = '<b>'.htmlspecialchars($path).'</b>';
			}
		}
		$roleMailboxAddress = $rbacreview->getRoleMailboxAddress($roleId);
		if ($asHTML) {
			$roleMailboxAddress = htmlspecialchars($roleMailboxAddress);
		}

		return $roleMailboxAddress.', '.$path;
	}

	function toSortValue($columnKey, $value, $rowId, &$errors) {
		$sortValue = $value;
		if (is_null($value)) {
			$sortValue = '0';
		} elseif (is_string($value)) {
			if ($columnKey == 'Password') {
				$sortValue = '0';
			} else {
				$sortValue = UtfNormal::NFKD($sortValue);
				$sortValue = preg_replace('/\p{Mc}/u','', $sortValue);
				$sortValue = preg_replace('/\p{Mn}/u','', $sortValue);
				$sortValue = preg_replace('/\p{Me}/u','', $sortValue);
				$sortValue = preg_replace('/\p{Sk}/u','', $sortValue);
				$sortValue = htmlspecialchars(strtolower($sortValue));
			}
		} else {
			$sortValue = '-9';
		}

		return $sortValue;
	}
	function toFormattedValue($columnKey, $value, $rowId, &$errors) {
		$formattedValue = $value;
		if (is_null($value)) {
			$formattedValue = '';
		} elseif (is_string($value)) {
			if ($columnKey == 'Password') {
				$formattedValue = '******';
			} else if ($columnKey == 'AnlassBezLang') {
				$formattedValue = htmlspecialchars($value).'<br><span class="info3">'.$this->toFormattedAnlassBezLang($value).'</span>';
			} else {
				$formattedValue = htmlspecialchars($value);
			}
		} else {
			$formattedValue = '<span class="error">'.
			htmlspecialchars(var_export($row[$columnKey], true)).
			'</span>';
			$msg = $columnKey.' ungültig';
			if (!array_key_exists($msg, $errors)) {
				$errors[$msg] = array();
			}
			$errors[$msg][] = $rowId;
			$errors['rows'][] = $rowId;
		}
		return $formattedValue;
	}

	function toElapsed($start, $end, $isLong = true) {
		$seconds = $end - $start;
		$elapsed = ($seconds % 60).' s ';
		if ($seconds >= 60) {
			$minutes = floor($seconds / 60);
			$elapsed = ($minutes % 60).' m '.$elapsed;
			if ($minutes >= 60) {
				$hours = floor($minutes / 60);
				$elapsed = ($hours % 24).' h '.$elapsed;
			}
			if ($hours >= 24) {
				$days = floor($hours / 24);
				$elapsed = $days.'t '.$elapsed;
			}
		}

		return (($isLong) ? 'Start: '.date('Y-m-d H:i:s', $start).
		', Ende: '.date('Y-m-d H:i:s', $end) : '').
		', Dauer: '.$elapsed;
	}
	function writeLogHeader() {
		$fh = fopen($this->logFileLog, 'a+');
		chmod($this->logFileLog, 0777);
		fwrite($fh,'<table class="sortable"><thead><tr><th>Zeitpunkt</th><th>Ereignis</th></tr></thead><tbody>'."\n");
		$this->logfh = $fh;
	}
	function writeLogFooter() {
		$fh = fopen($this->logFileLog, 'a+');
		fwrite($fh,'</tbody></table>'."\n");

		// Append finished style to stylesheet
		fwrite($fh,"\n".'<style type="text/css">.'.$this->runningImportStyle."{\n display:none; \n } \n </style> \n \n");
		fclose($fh);
	}
	function log($aMessage, $aHTMLMessage = null) {
		if ($aHTMLMessage == null) {
			$aHTMLMessage = htmlspecialchars($aMessage);
		}
		$date = date('Y-m-d H:i:s ');
		echo $date.' '.$aMessage."\n";
		error_log($aMessage);

		$fh = fopen($this->logFileLog,'a+');
		fwrite($fh, '<tr><td class="timestamp">'.$date.'</td><td>'.$aHTMLMessage.'</td></tr>'."\n");
		fclose($fh);
	}

	function isAssocArray($mixed) {
		if (is_array($mixed)) {
			foreach (array_keys($mixed) as $k => $v) {
				if ($k !== $v) {
					return true;
				}
			}
		}
		return false;
	}

	/** Calls the WebService and retries maxRetries in case of an error. */
	function callWebService($operation,$params=array(),$namespace='http://tempuri.org',$soapAction='',$headers=false,$rpcParams=null,$style='rpc',$use='encoded'){
		for ($i=0; $i < $this->maxRetries; $i++) {

			// Log into Webservice
			if ($this->token == null) {
				$result = &$this->client->call('Login', array('parameters'=>array('username'=>$this->wsUser, 'password'=>$this->wsPassword)));

				if ($this->client->getError()) {
					$this->log('Login an Evento WS fehlgeschlagen: '.$this->client->getError());
					continue; // retry, maybe we can login on the next try
				}
				if ($result['LoginResult'] == null || $result['LoginResult'] == 'wrong credentials') {
					$this->log('Login an Evento WS fehlgeschlagen: '.$result['LoginResult']);
					return false;
				} else {
					$this->log('Login an Evento WS erfolgreich gemacht.');
					$this->token = $result['LoginResult'];
				}
			}

			$startTime = time();
			$params['parameters']['token'] = $this->token;
			$result = &$this->client->call($operation, $params, $namespace, $soapAction, $headers, $rpcParams ,$style, $use);
			$endTime = time();

			if ($endTime - $startTime > 30) {
				$msg = 'Langsame Abfrage: '.$operation.'('.var_export($params,true).') Dauer:'.($endTime-$startTime).' sec';
				$this->log($msg);
			}

			if ($this->client->getError()) {
				$errorDescription = $this->client->getError();
				$msg = 'Fehler bei Abfrage: '.$operation.'('.var_export($params,true).') '.$errorDescription;
				$this->log($msg);

				if (strpos($errorDescription,'Token:') !== false) {
					// Session timed out. Clear token, wait and then retry
					$this->token = null;
					sleep($this->numberOfSecondsBeforeRetry);
					continue;

				} else if (strpos($errorDescription,'Operation timed out') !== false ||
					strpos($errorDescription,'deadlock') !== false) {

					if ($i < $this->maxRetries) {
						// wait and then retry
						sleep($this->numberOfSecondsBeforeRetry);
						continue;
					}
				}
			}

			// break loop if we were successful or encountered an error which was not a timeout
			break;
		}
		return $result;
	}
	/**
	 * User accounts which don't have a time limitation are limited to
	 * two years since their creation.
	 */
	function setUserTimeLimits() {
		global $ilias, $ilDB, $ilLog, $ilUser;

		$start = time();
		
		//jeder user hat mind. 90 tage zugriff (fix shibboleth)
		$q="UPDATE `usr_data` SET time_limit_until=time_limit_until+7889229 WHERE DATEDIFF(FROM_UNIXTIME(time_limit_until),create_date)<90";
		$r = $ilDB->db->exec($q);
		
		//keine unlimited user 
		$q = "UPDATE usr_data set time_limit_unlimited=0 WHERE time_limit_unlimited=1 AND login NOT IN ('root','anonymous')";
		$r = $ilDB->db->exec($q);
		$this->log('Zeitlimite für '.$r.' Benutzerkonten von unlimited auf normal gestellt.');
		
		//alle user reset auf max 2 jahre lang zugriff (heute +2jahre)
		$time2Years=mktime(date('H'), date('i'), date('s'), date('n'), date('j'), date('Y')+2);
		$q = "UPDATE usr_data set time_limit_until='".$time2Years."' WHERE time_limit_until>'".$time2Years."'";
		$r = $ilDB->db->exec($q);
		$this->log('Zeitlimite für '.$r.' Benutzerkonten auf 2 Jahre limitiert.');
		
		$end = time();
		$this->log('Setzen der Zeitlimiten für Benutzerkonten beendet. '.$this->toElapsed($start, $end, false).'.');
		return $success;
	}
	/**
	 * Ensures that the time limit for all tutors and administrators in an active
	 * course or group of all further education events lies at least 1 month in
	 * the future.
	 *
	 * Returns false on failure, returns the number of events in case of success.
	 */
	function importTutorTimeLimitsFurtherEducation() {
		$start2 = time();
		$this->log('Import der Zeitlimiten für Tutoren in der Weiterbildung begonnen.');
		$success = $this->importTutorTimeLimits($this->logFileTutorTimeLimitsFurtherEducation, 'HauptAnlaesse', 'Anlaesse', 'Zeitlimiten von Tutoren in Weiterbildungs-Anlässen', true);
		$end2 = time();
		$this->log('Import der Zeitlimiten für Tutoren in der Weiterbildung beendet. '.$this->toElapsed($start2, $end2, false).'.');
		return $success;
	}
	/**
	 * Ensures that the time limit for all tutors and administrators in an active
	 * course or group lies at least 1 month in the future.
	 *
	 * The list of courses is imported from Evento.
	 *
	 * param $aFile the filename into which to write log data
	 * param $aRequest the name of the Soap-Request for Evento
	 * param $aDataset the name of the dataset object returned by Evento.
	 * param $aTitle the title used on the log data
	 *
	 * Returns false on failure, returns the number of events in case of success.
	 */
	function importTutorTimeLimits($aFile, $aRequest, $aDataset, $aTitle, $withSubscription=false) {
		global $rbacreview, $ilDB;

		$start = time();

		$fh = fopen($aFile, 'wb');
		chmod($aFile, 0777);
		
		$isFirstRow = true;
		$columnKeys = array();
		$count = 0;
		$errors = new ErrorsInPage($this, '../'.basename($aFile));
		fwrite($fh,'<table id="importTutorTimeLimitsTable" class="sortable">'."\n");
		for ($page = 1; $this->maxPages == -1 || $page <= $this->maxPages; $page++) {
			unset($result);
			$result = &$this->callWebService('Get'.$aRequest, array('parameters'=>array('pagesize'=>$this->pagesize,'pagenumber'=>$page)));
			if ($result == false) {
				fwrite($fh,'</table>'."\n");
				$this->closeWithError($fh);
				return false;
			}
			// extract column keys, and write table header
			if ($isFirstRow) {
				$isFirstRow = false;
				$columnKeys = array();
				foreach ($result['Get'.$aRequest.'Result']['schema']['element']['complexType']['choice']['element']['complexType']['sequence']['element'] as $def) {
					$columnKeys[] = $def['!name'];
				}
				fwrite($fh,'<thead>'."\n");
				fwrite($fh,'<tr>'."\n");
				fwrite($fh,'<th>#</th>'."\n");
				foreach ($columnKeys as $columnKey) {
					fwrite($fh,'<th>');
					fwrite($fh,htmlspecialchars($columnKey));
					fwrite($fh,'</th>'."\n");
				}
				fwrite($fh,'<th>ILIAS Tutor rol_id</th>'."\n");
				fwrite($fh,'<th>ILIAS Admin rol_id</th>'."\n");
				fwrite($fh,'<th>Ablaufende Tutoren-Konten</th>'."\n");
				fwrite($fh,'<th>!!</th>'."\n");
				fwrite($fh,'</tr>'."\n");
				fwrite($fh,'</thead>'."\n");
			}
			// write table rows, if there are any
			if (! is_array($result['Get'.$aRequest.'Result']['diffgram'])) {
				break;
			}
			$data = &$result['Get'.$aRequest.'Result']['diffgram']['ds'.$aDataset][$aDataset];
			if ($this->isAssocArray($data)) {
				$data = array($data);
			}
			if (count($data) == 0) {
				break;
			}
			foreach ($data as $row) {
				$count++;

				fwrite($fh,'<tr>'."\n");
				fwrite($fh,'<td>');
				fwrite($fh, '<a name="'.$count.'" id="'.$count.'">');
				fwrite($fh,$count);
				fwrite($fh, '</a>');
				fwrite($fh,'</td>'."\n");

				foreach ($columnKeys as $columnKey) {
					$value = $row[$columnKey];
					$sortValue = $this->toSortValue($columnKey, $value, $row['AnlassBezKurz'], $errors);
					$formattedValue = $this->toFormattedValue($columnKey, $value, $row['AnlassBezKurz'], $errors);

					fwrite($fh,'<td sorttable_customkey="'.$sortValue.'">');
					fwrite($fh, $formattedValue);
					fwrite($fh,'</td>'."\n");
				}

				// The actual work is performed here
				$aTitle= $row['AnlassBezKurz'];
				$searchName = '#tutor@['.$aTitle.']';
				$tutorRoleIds = $rbacreview->searchRolesByMailboxAddressList($searchName);
				if (count($tutorRoleIds) > 0) {
					fwrite($fh,'<td>');
					fwrite($fh, '<span class="success">');
					for ($i=0;$i<count($tutorRoleIds);$i++) {
						if ($i > 0) {
							fwrite($fh, ', ');
						}
						fwrite($fh, $tutorRoleIds[$i]);
					}
					fwrite($fh, '</span>');
					fwrite($fh,'</td>'."\n");
				} else {
					fwrite($fh,'<td></td>'."\n");
				}
				$searchName = '#admin@['.$aTitle.']';
				$adminRoleIds = $rbacreview->searchRolesByMailboxAddressList($searchName);
				if (count($adminRoleIds) > 0) {
					fwrite($fh,'<td>');
					fwrite($fh, '<span class="success">');
					for ($i=0;$i<count($adminRoleIds);$i++) {
						if ($i > 0) {
							fwrite($fh, ', ');
						}
						fwrite($fh, $adminRoleIds[$i]);
					}
					fwrite($fh, '</span>');
					fwrite($fh,'</td>'."\n");
				} else {
					fwrite($fh,'<td></td>'."\n");
				}
				$roleIds = array_merge($tutorRoleIds,$adminRoleIds);

				if (count($roleIds)==0) {

				} else {
                                        $inStr='';$sep='';
                                        foreach($roleIds as $r){
                                            $inStr.=$sep.$r;$sep=',';
                                        }
					$q = "SELECT DISTINCT d.login, DATE(FROM_UNIXTIME(d.time_limit_until)) AS time_limit_until ".
						"FROM rbac_ua AS ua ".
						"JOIN usr_data AS d ON ua.usr_id=d.usr_id ".
						"WHERE 
							ua.rol_id IN (".$inStr.") ".
							"AND d.active=1 AND d.time_limit_unlimited=0 ".
							"AND FROM_UNIXTIME(d.time_limit_until) < DATE_ADD(NOW(), INTERVAL 1 MONTH) ".
							"AND FROM_UNIXTIME(d.time_limit_until) > DATE_SUB(NOW(), INTERVAL 1 DAY) ".
							"AND d.login NOT IN ('root','anonymous')";

					$r = $ilDB->db->query($q);
					if ($ilDB->isError($r) || $ilDB->isError($r->result)) {
                                                $errors->addError($count, $row['AnlassBezKurz'], 'Ermitteln des Ablaufdatums misslungen:'.
							(($ilDB->isError($r->result)) ? $r->result->getMessage() : $r->getMessage()));
						fwrite($fh,'<td>');
						fwrite($fh, '<span class="success">');
						fwrite($fh, '</span>');
						fwrite($fh,'</td>'."\n");
					} else {
						fwrite($fh,'<td>');
						fwrite($fh, '<span class="error">');
						if ($r->numRows() == 0) {
							// no tutor will reach its time limit
						} else {
							// the following tutors will reach their time limit
							$i=0;
							while ($row = $r->fetchRow(DB_FETCHMODE_OBJECT))
							{
								$errors->addWarning($count,  $row->login.': '.$row->time_limit_until,'Tutoren-Konto läuft bald ab.');
								if ($i++ > 0) {
									fwrite($fh, ', ');
								}
								fwrite($fh, $row->login.': '.$row->time_limit_until);
							}
						}
						fwrite($fh, '</span>');
						fwrite($fh,'</td>'."\n");
					}
				}

				// Print errors if any
				if ($errors->hasErrorsInRow($count)) {
					fwrite($fh,'<td><span class="error">!!</span></td>'."\n");
				} elseif ($errors->hasWarningsInRow($count)) {
					fwrite($fh,'<td><span class="error">**</span></td>'."\n");
				} else {
					fwrite($fh,'<td sorttable_customkey="z"></td>'."\n");
				}
				fwrite($fh,'</tr>'."\n");
			}
		}

		fwrite($fh,'</table>'."\n");
		fwrite($fh,'<div class="footer">'."\n");
		fwrite($fh,'<p>'.$count.' Datensätze</p>'."\n");
		$errors->writeErrors($fh);
		$end = time();
		fwrite($fh, '<p>'.$this->toElapsed($start, $end).'</p>'."\n");
		fwrite($fh,'</div>'."\n");
		fwrite($fh,'</body>'."\n");
		fwrite($fh,'</html>'."\n");
		fclose($fh);
		return $count;
	}

	/**
	 * Aktualisiert das Beschreibungsfeld des Objekts, falls es leer war.
	 * Gibt true zurück, falls eine Aktualisierung erfolgt ist.
	 */
	private function updateObjectDescription($objId, $data) {
		global $ilias, $ilDB, $ilLog, $ilUser;

		$description = $this->toFormattedAnlassBezLang($data['AnlassBezLang']);
		if (strlen($description) == 0) {
			return;
		}

		$q = "UPDATE object_data ".
				"SET ".
					"description = ".$ilDB->quote($description)." ".
				"WHERE ".
					"obj_id = ".$ilDB->quote($objId)." AND ".
					"description = ''".
				"";

		$r = $ilDB->db->exec($q);
		if ($ilDB->isError($r) || $ilDB->isError($r->result)) {
			$this->log('Fehler beim Setzen der Beschreibung für obj_id:'.$obj_id.' "'.$description.'" : '.
				(($ilDB->isError($r->result)) ? $r->result->getMessage() : $r->getMessage()));
			$success = false;
		} else {
			$success = $r == 1;
		}

		if ($success) {
			require_once 'Services/Object/classes/class.ilObjectFactory.php';
			$obj = ilObjectFactory::getInstanceByObjId($objId);
			if ($obj != null) {
				$obj->setDescription($description);
				$obj->update();
			}
			/*
			$q = "UPDATE il_meta_description ".
					"SET ".
						"description = ".$ilDB->quote($description)." ".
					"WHERE ".
						"obj_id = ".$ilDB->quote($objId)." AND ".
						"description = ''".
					"";

			$r = $ilDB->db->exec($q);
			if ($ilDB->isError($r) || $ilDB->isError($r->result)) {
				$this->log('Fehler beim Setzen der Metadaten für obj_id:'.$obj_id.' "'.$description.'" : '.
					(($ilDB->isError($r->result)) ? $r->result->getMessage() : $r->getMessage()));
				$success = false;
			}*/
		}
		return $success;
	}

	/**
	 * Formats the KursBezLang value.
	 */
	 private function toFormattedAnlassBezLang($value) {

		// Remove the prefix from the description.
		$value = preg_replace('/^([a-zA-Z0-9._]+_|[a-zA-Z0-9]+\.)/u','',$value);

		// Remove the suffix from the description.
		$value = preg_replace('/(\.[a-zA-Z0-9]+| [A-Z]S [0-9]{4})$/u','',$value);

		return $value;
	}

	/**
	 * If import is disabled by a parent object, returns its object data.
	 * Otherwise returns null.
	 */
	private function getImportDisabledBy($role_id) {
		global $tree, $rbacreview;

		$folders=$rbacreview->getFoldersAssignedToRole($role_id,true);
		$nodePath=$tree->getNodePath($folders[0]);
		$parentCat = null;
		$parentCrs = null;
		for ($i=count($nodePath)-1;$i>=0;$i--) {
			$node=$nodePath[$i];
			if ($node['type']=='cat') {
				$parentCat=$node;
				break;
			}
			if ($node['type']=='crs' && $parentCrs == null) {
				$parentCrs=$node;
			}
		}

		require_once 'Services/Container/classes/class.ilContainerGUI.php';

		// Check whether the import is not enabled in the parent course
		if ($parentCrs != null) {
			$isImportEnabled=ilContainer::_lookupContainerSetting($parentCrs['obj_id'],'evento_import');
			//print_r($isImportEnabled);print '-:-';exit;
			if ($isImportEnabled===null or $isImportEnabled=='') {
				$isImportEnabled=true;
			}
			if (! $isImportEnabled) {
				return $parentCrs;
			}
		}

		// Check whether the import is not enabled in the parent category
		if ($parentCat != null) {
			$isImportEnabled=ilContainer::_lookupContainerSetting($parentCat['obj_id'],'evento_import');
			if ($isImportEnabled===null or $isImportEnabled=='') {
				$isImportEnabled=true;
			}
			if (! $isImportEnabled) {
				return $parentCat;
			}
		}

		// Import is not disabled by a parent object
		return null;
	}

	/**
	 * lookup id by matriculation
	 */
	public function _getUserIdsByMatriculation($matriculation)
	{
		global $ilDB;
	
		$res = $ilDB->queryF("SELECT usr_id FROM usr_data WHERE matriculation = %s",
				array("text"), array($matriculation));
		$ids=array();
		while($user_rec = $ilDB->fetchAssoc($res)){
			$ids[]=$user_rec["usr_id"];
		}
		return $ids;
	}
	
	/**
	 * lookup id by email
	 */
	public function _reallyGetUserIdsByEmail($a_email)
	{
		global $ilDB;
	
		$res = $ilDB->queryF("SELECT usr_id FROM usr_data WHERE email = %s AND active=1",
				array("text"), array($a_email));
		$ids=array();
		while($user_rec = $ilDB->fetchAssoc($res)){
			$ids[]=$user_rec["usr_id"];
		}
		return $ids;
	}
	
	/**
	 * change login name of a user
	 */
	public function changeLoginName($usr_id, $new_login)
	{
		global $ilDB;
		
		$q="UPDATE usr_data SET login = '".$new_login."' WHERE usr_id = '".$usr_id."'";
		$ilDB->db->exec($q);
		
	}
	
}

class ErrorsInPage {
	function ErrorsInPage($importEventoWS, $href) {
		$this->importer = $importEventoWS;
		$this->href = $href;
		$this->count = 0;
		$this->errorsByMsg = array(); // associative array key=msg, value=array of row indices
		$this->errorsByRow = array(); // associative array key=row, value=array of row messages
		$this->warningsByMsg = array(); // associative array key=msg, value=array of row indices
		$this->warningsByRow = array(); // associative array key=row, value=messages
		$this->idsByRow = array(); // associative array key=row, value=id
	}
	function addError($rowIndex, $id, $msg, $logThisError=true) {
		$this->count++;
		if ($logThisError) {
			$this->importer->log($id.': '.$msg, htmlspecialchars($id).': '.htmlspecialchars($msg));
		}
		if (! array_key_exists($msg, $this->errorsByMsg)) {
			$this->errorsByMsg[$msg] = array();
		}
		if (! in_array($rowIndex, $this->errorsByMsg[$msg])) {
			$this->errorsByMsg[$msg][] = $rowIndex;
		}

		if (! array_key_exists($rowIndex, $this->errorsByRow)) {
			$this->errorsByRow[$rowIndex] = array();
		}
		$this->errorsByRow[$rowIndex][] = $id.': '.$msg;
		$this->idsByRow[$rowIndex] = $id ? $id : $rowIndex;
	}
	function addWarning($rowIndex, $id, $msg, $logThisError=true) {
		$this->count++;
		if ($logThisError) {
			$this->importer->log($id.': '.$msg, htmlspecialchars($id).': '.htmlspecialchars($msg));
		}
		if (! array_key_exists($msg, $this->warningsByMsg)) {
			$this->warningsByMsg[$msg] = array();
		}
		if (! in_array($rowIndex, $this->warningsByMsg[$msg])) {
			$this->warningsByMsg[$msg][] = $rowIndex;
		}

		if (! array_key_exists($rowIndex, $this->warningsByRow)) {
			$this->warningsByRow[$rowIndex] = array();
		}
		$this->warningsByRow[$rowIndex][] = $id.': '.$msg;
		$this->idsByRow[$rowIndex] = $id ? $id : $rowIndex;
	}
	function hasErrorsInRow($rowIndex) {
		return array_key_exists($rowIndex, $this->errorsByRow);
	}
	function hasWarningsInRow($rowIndex) {
		return array_key_exists($rowIndex, $this->warningsByRow);
	}
	function hasErrorsInPage() {
		return $this->count > 0;
	}
	function writeErrors($fh) {
		fwrite($fh,'<p>'.$this->count.' Problemfälle gefunden.</p>');
		if (count($this->errorsByMsg) > 0) {
			foreach ($this->errorsByMsg as $msg => $rowIndices) {
				fwrite($fh,'<p>'.count($rowIndices).' x '.$msg.': ');
				$isFirst = true;
				foreach ($rowIndices as $row) {
					if ($isFirst) {
						$isFirst = false;
					} else {
						fwrite($fh, ', ');
					}
					//fwrite($fh,'<a href="#'.$row.'">'.$this->idsByRow[$row].'</a>');
					fwrite($fh,$this->idsByRow[$row]);
					//$this->importer->log($errorDescription, '<span style="error"><a href="../'.basename($aFile).'#'.$fe.'">'.$errorDescription.'</a></span>');
				}
				fwrite($fh,'</p>'."\n");
			}
		}
		if (count($this->warningsByMsg) > 0) {
			foreach ($this->warningsByMsg as $msg => $rowIndices) {
				fwrite($fh,'<p>'.count($rowIndices).' x '.$msg.': ');
				$isFirst = true;
				foreach ($rowIndices as $row) {
					if ($isFirst) {
						$isFirst = false;
					} else {
						fwrite($fh, ', ');
					}
					//fwrite($fh,'<a href="#'.$row.'">'.$this->idsByRow[$row].'</a>');
					fwrite($fh,$this->idsByRow[$row]);
					//$this->importer->log($errorDescription, '<span style="error"><a href="../'.basename($aFile).'#'.$fe.'">'.$errorDescription.'</a></span>');
				}
				fwrite($fh,'</p>'."\n");
			}
		}
	}
}


?>