<?php
  /*
   +-----------------------------------------------------------------------------+
   | ILIAS open source                                                           |
   +-----------------------------------------------------------------------------+
   | Copyright (c) 1998-2001 ILIAS open source, University of Cologne            |
   |                                                                             |
   | This program is free software; you can redistribute it and/or               |
   | modify it under the terms of the GNU General Public License                 |
   | as published by the Free Software Foundation; either version 2              |
   | of the License, or (at your option) any later version.                      |
   |                                                                             |
   | This program is distributed in the hope that it will be useful,             |
   | but WITHOUT ANY WARRANTY; without even the implied warranty of              |
   | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
   | GNU General Public License for more details.                                |
   |                                                                             |
   | You should have received a copy of the GNU General Public License           |
   | along with this program; if not, write to the Free Software                 |
   | Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
   +-----------------------------------------------------------------------------+
  */


  /**
   * Soap utitliy functions
   *
   * @author Stefan Meyer <smeyer@databay.de>
   * @version $Id$
   *
   * @package ilias
   */
include_once './webservice/soap/classes/class.ilSoapAdministration.php';

class ilSoapUtils extends ilSoapAdministration
{
	function ilSoapUtils()
	{
		parent::ilSoapAdministration();
	}

	function ignoreUserAbort()
	{
		ignore_user_abort();
	}

	function disableSOAPCheck()
	{
		$this->sauth->disableSOAPCheck();
	}
	
	function sendMail($sid,$to,$cc,$bcc,$sender,$subject,$message,$attach)
	{
		if(!$this->__checkSession($sid))
		{
			return $this->__raiseError($this->sauth->getMessage(),$this->sauth->getMessageCode());
		}			

		// Include main header
		include_once './include/inc.header.php';

		global $ilLog;

		include_once 'Services/Mail/classes/class.ilMimeMail.php';

		$mmail = new ilMimeMail();
		$mmail->autoCheck(false);
		$mmail->From($sender);
		$mmail->To(explode(',',$to));
		$mmail->Subject($subject);
		$mmail->Body($message);

		if($cc)
		{
			$mmail->Cc(explode(',',$cc));
		}

		if($bcc)
		{
			$mmail->Bcc(explode(',',$bcc));
		}
		if($attach)
		{
			$attachments = explode(',',$attach);
			foreach ($attachments as $attachment)
			{
				$mmail->Attach($attachment);
			}
		}

		$mmail->Send();
		$ilLog->write('SOAP: sendMail(): '.$to.', '.$cc.', '.$bcc);

		return true;
	}
	
	function saveTempFileAsMediaObject($sid, $name, $tmp_name)
	{
		if(!$this->__checkSession($sid))
		{
			return $this->__raiseError($this->sauth->getMessage(),$this->sauth->getMessageCode());
		}			

		// Include main header
		include_once './include/inc.header.php';
		include_once "./Services/MediaObjects/classes/class.ilObjMediaObject.php";
		return ilObjMediaObject::_saveTempFileAsMediaObject($name, $tmp_name);
	}
	
	function getMobsOfObject($sid, $a_type, $a_id)
	{
		if(!$this->__checkSession($sid))
		{
			return $this->__raiseError($this->sauth->getMessage(),$this->sauth->getMessageCode());
		}			

		// Include main header
		include_once './include/inc.header.php';
		include_once "./Services/MediaObjects/classes/class.ilObjMediaObject.php";
		return ilObjMediaObject::_getMobsOfObject($a_type, $a_id);
	}
	
	function saveQuestionResult($sid,$user_id,$test_id,$question_id,$pass,$solution)
	{
		if(!$this->__checkSession($sid))
		{
			return $this->__raiseError($this->sauth->getMessage(),$this->sauth->getMessageCode());
		}			

		// Include main header
		include_once './include/inc.header.php';
		include_once "./Modules/Test/classes/class.ilObjTest.php";
		$active_id = ilObjTest::_getActiveIdOfUser($user_id, $test_id);
		$ilDB = $GLOBALS['ilDB'];
		if (($active_id > 0) && ($question_id > 0) && (strlen($pass) > 0))
		{
			$deletequery = sprintf("DELETE FROM tst_solutions WHERE active_fi = %s AND question_fi = %s AND pass = %s",
				$ilDB->quote($active_id . ""),
				$ilDB->quote($question_id . ""),
				$ilDB->quote($pass . "")
			);
			$ilDB->query($deletequery);
		}
		for($i = 0; $i < count($solution); $i += 3)
		{
			$query = sprintf("INSERT INTO tst_solutions ".
				"SET active_fi = %s, ".
				"question_fi = %s, ".
				"value1 = %s, ".
				"value2 = %s, ".
				"points = %s, ".
				"pass = %s",
				$ilDB->quote($active_id . ""),
				$ilDB->quote($question_id . ""),
				$ilDB->quote($solution[$i]),
				$ilDB->quote($solution[$i+1]),
				$ilDB->quote($solution[$i+2]),
				$ilDB->quote($pass . "")
			);

			$ilDB->query($query);
		}
		return true;
	}
	
	/**
	 * clone object dependencies (e.g. course start objects, preconditions ...)
	 *
	 * @access public
	 * @param
	 * 
	 */
	public function ilCloneDependencies($sid,$copy_identifier)
	{
		if(!$this->__checkSession($sid))
		{
			return $this->__raiseError($this->sauth->getMessage(),$this->sauth->getMessageCode());
		}			

		// Include main header
		include_once './include/inc.header.php';
		
		global $ilLog,$ilUser;

		include_once('Services/CopyWizard/classes/class.ilCopyWizardOptions.php');
		$cp_options = ilCopyWizardOptions::_getInstance($copy_identifier);
		
		// Check owner of copy procedure
		if(!$cp_options->checkOwner($ilUser->getId()))
		{
			$ilLog->write(__METHOD__.': Permission check failed for user id: '.$ilUser->getId().', copy id: '.$copy_identifier);
			return false;
		}
		
		// Fetch first node
		if(($node = $cp_options->fetchFirstDependenciesNode()) === false)
		{
			$cp_options->deleteAll();
			$ilLog->write(__METHOD__.': Finished copy step 2. Copy completed');
			
			return true;
		}

		// Check options of this node
		$options = $cp_options->getOptions($node['child']);
		$new_ref_id = 0;
		switch($options['type'])
		{
			case ilCopyWizardOptions::COPY_WIZARD_OMIT:
				$ilLog->write(__METHOD__.': Omitting node: '.$node['obj_id'].', '.$node['title'].', '.$node['type']);
				$this->callNextDependency($sid,$cp_options);
				break;
			
			case ilCopyWizardOptions::COPY_WIZARD_LINK:
				$ilLog->write(__METHOD__.': Nothing to do for node: '.$node['obj_id'].', '.$node['title'].', '.$node['type']);
				$this->callNextDependency($sid,$cp_options);
				break;
				
			case ilCopyWizardOptions::COPY_WIZARD_COPY:
				$ilLog->write(__METHOD__.': Start cloning dependencies: '.$node['obj_id'].', '.$node['title'].', '.$node['type']);
				$this->cloneDependencies($node,$cp_options);
				$this->callNextDependency($sid,$cp_options);
				break;
				
			default:
				$ilLog->write(__METHOD__.': No valid action type given for node: '.$node['obj_id'].', '.$node['title'].', '.$node['type']);
				$this->callNextDependency($sid,$cp_options);
				break;
		}
	 	return true;
	}
	
	/**
	 * Clone object
	 *
	 * @access public
	 * @param string soap session id
	 * @param int copy identifier (ilCopyWizarardOptions)
	 * 
	 */
	public function ilClone($sid,$copy_identifier)
	{
		if(!$this->__checkSession($sid))
		{
			return $this->__raiseError($this->sauth->getMessage(),$this->sauth->getMessageCode());
		}			

		// Include main header
		include_once './include/inc.header.php';
		
		global $ilLog,$ilUser;
		
		include_once('Services/CopyWizard/classes/class.ilCopyWizardOptions.php');
		$cp_options = ilCopyWizardOptions::_getInstance($copy_identifier);
		
		// Check owner of copy procedure
		if(!$cp_options->checkOwner($ilUser->getId()))
		{
			$ilLog->write(__METHOD__.': Permission check failed for user id: '.$ilUser->getId().', copy id: '.$copy_identifier);
			return false;
		}
		
		
		// Fetch first node
		if(($node = $cp_options->fetchFirstNode()) === false)
		{
			$ilLog->write(__METHOD__.': Finished copy step 1. Starting copying of object dependencies...');
			return $this->ilCloneDependencies($sid,$copy_identifier);
		}
	
		// Check options of this node
		$options = $cp_options->getOptions($node['child']);
		
		$new_ref_id = 0;
		switch($options['type'])
		{
			case ilCopyWizardOptions::COPY_WIZARD_OMIT:
				$ilLog->write(__METHOD__.': Omitting node: '.$node['obj_id'].', '.$node['title'].', '.$node['type']);
				// set mapping to zero
				$cp_options->appendMapping($node['child'],0);
				$this->callNextNode($sid,$cp_options);
				break;
				
			case ilCopyWizardOptions::COPY_WIZARD_COPY:
				$ilLog->write(__METHOD__.': Start cloning node: '.$node['obj_id'].', '.$node['title'].', '.$node['type']);
				$new_ref_id = $this->cloneNode($node,$cp_options);
				$this->callNextNode($sid,$cp_options);
				break;
			
			case ilCopyWizardOptions::COPY_WIZARD_LINK:
				$ilLog->write(__METHOD__.': Start linking node: '.$node['obj_id'].', '.$node['title'].', '.$node['type']);
				$new_ref_id = $this->linkNode($node,$cp_options);
				$this->callNextNode($sid,$cp_options);
				break;

			default:
				$ilLog->write(__METHOD__.': No valid action type given for node: '.$node['obj_id'].', '.$node['title'].', '.$node['type']);
				$this->callNextNode($sid,$cp_options);
				break;
				
		}
	 	return $new_ref_id;
	}
	
	/**
	 * Call next node using soap
	 * @param object copx wizard options instance
	 * @access private
	 * 
	 */
	private function callNextNode($sid,$cp_options)
	{
		global $ilLog;

		$cp_options->dropFirstNode();

		if($cp_options->isSOAPEnabled())
		{
		 	// Start next soap call
		 	include_once 'Services/WebServices/SOAP/classes/class.ilSoapClient.php';
			$soap_client = new ilSoapClient();
			$soap_client->setTimeout(1);
			$soap_client->setResponseTimeout(1);
			$soap_client->enableWSDL(true);
			$soap_client->init();
			$soap_client->call('ilClone',array($sid,$cp_options->getCopyId()));
		}
		else
		{
			$ilLog->write(__METHOD__.': Cannot call SOAP server');
			$cp_options->read();			
			include_once('./webservice/soap/include/inc.soap_functions.php');
			$res = ilClone($sid,$cp_options->getCopyId());
		}
		return true;
	}
	
	private function callNextDependency($sid,$cp_options)
	{
		global $ilLog;

		$cp_options->dropFirstDependenciesNode();
		
		if($cp_options->isSOAPEnabled())
		{
		 	// Start next soap call
		 	include_once 'Services/WebServices/SOAP/classes/class.ilSoapClient.php';
			$soap_client = new ilSoapClient();
			$soap_client->setTimeout(1);
			$soap_client->setResponseTimeout(1);
			$soap_client->enableWSDL(true);
			$soap_client->init();
			$soap_client->call('ilCloneDependencies',array($sid,$cp_options->getCopyId()));
		}
		else
		{
			$ilLog->write(__METHOD__.': Cannot call SOAP server');
			$cp_options->read();
			include_once('./webservice/soap/include/inc.soap_functions.php');
			$res = ilCloneDependencies($sid,$cp_options->getCopyId());
		}
		return true;
	}
	
	/**
	 * Clone node
	 *
	 * @access private
	 * @param
	 * 
	 */
	private function cloneNode($node,$cp_options)
	{
		global $ilLog,$tree,$ilAccess;
		
		#sleep(20);
		
		$source_id = $node['child'];
		$parent_id = $node['parent'];
		$options = $cp_options->getOptions($node['child']);
		$mappings = $cp_options->getMappings();
		
		if(!$ilAccess->checkAccess('copy','',$node['child']))
		{
			$ilLog->write(__METHOD__.': No copy permission granted: '.$source_id.', '.$node['title'].', '.$node['type']);
			return false;
			
		}
		if(!isset($mappings[$parent_id]))
		{
			$ilLog->write(__METHOD__.': Omitting node '.$source_id.', '.$node['title'].', '.$node['type']. '. No target found.');
			return true;
		}
		$target_id = $mappings[$parent_id];
		
		if(!$tree->isInTree($target_id))
		{
			$ilLog->write(__METHOD__.': Omitting node '.$source_id.', '.$node['title'].', '.$node['type']. '. Object has been deleted.');
			return false;
		}

		$orig = ilObjectFactory::getInstanceByRefId((int) $source_id);
		$new_obj = $orig->cloneObject((int) $target_id,$cp_options->getCopyId());
		
		if(!is_object($new_obj))
		{
			$ilLog->write(__METHOD__.': Error copying '.$source_id.', '.$node['title'].', '.$node['type'].'. No target found.');
			return false;
		}
		
		// Finally add new mapping entry
		$cp_options->appendMapping($source_id,$new_obj->getRefId());
		return $new_obj->getRefId();
	}
	
	/**
	 * cloneDependencies
	 *
	 * @access private
	 * @param 
	 * 
	 */
	private function cloneDependencies($node,$cp_options)
	{
		global $ilLog;
		
		$source_id = $node['child'];
		$mappings = $cp_options->getMappings();
		
		if(!isset($mappings[$source_id]))
		{
			$ilLog->write(__METHOD__.': Omitting node '.$source_id.', '.$node['title'].', '.$node['type']. '. No mapping found.');
			return true;
		}
		$target_id = $mappings[$source_id];

		$orig = ilObjectFactory::getInstanceByRefId((int) $source_id);
		$orig->cloneDependencies($target_id,$cp_options->getCopyId());
		return true;
	}
	
	/**
	 * Link node
	 *
	 * @access private
	 * @param
	 * 
	 */
	private function linkNode($node,$cp_options)
	{
		global $ilLog,$ilAccess;
		
		$source_id = $node['child'];
		$parent_id = $node['parent'];
		$options = $cp_options->getOptions($node['child']);
		$mappings = $cp_options->getMappings();
		
		if(!$ilAccess->checkAccess('write','',$node['child']))
		{
			$ilLog->write(__METHOD__.': No write permission granted: '.$source_id.', '.$node['title'].', '.$node['type']);
			return false;
			
		}
		if(!isset($mappings[$parent_id]))
		{
			$ilLog->write(__METHOD__.': Omitting node '.$source_id.', '.$node['title'].', '.$node['type']. '. No target found.');
			return true;
		}
		$target_id = $mappings[$parent_id];

		$orig = ilObjectFactory::getInstanceByRefId((int) $source_id);
		$new_ref_id = $orig->createReference();
		$orig->putInTree($target_id);
		$orig->setPermissions($target_id);
		$orig->setRefId($new_ref_id);
		$orig->initDefaultRoles();
		
		
		if(!($new_ref_id))
		{
			$ilLog->write(__METHOD__.': Error linking '.$source_id.', '.$node['title'].', '.$node['type'].'. No target found.');
			return false;
		}
		
		// Finally add new mapping entry
		$cp_options->appendMapping($source_id,$new_ref_id);
		return $new_ref_id;		
	}
}
?>