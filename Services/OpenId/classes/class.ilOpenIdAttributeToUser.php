<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/OpenId/classes/class.ilOpenIdSettings.php';

/**
 * @classDescription Open ID auth class
 * 
 * @author Stefan Meyer <meyer@leifos.com>
 * @version $Id$
 * 
 */
class ilOpenIdAttributeToUser
{
	private $settings = null;
	private $writer = null;

	/**
	 *
	 */
	public function __construct()
	{
		global $ilLog;
		
		$this->log = $ilLog;
		
	 	$this->settings = ilOpenIdSettings::getInstance();

		include_once('./Services/Xml/classes/class.ilXmlWriter.php');
	 	$this->writer = new ilXmlWriter();
	
	}
	
	/**
	 * Create new ILIAS account
	 *
	 * @access public
	 * 
	 * @param string external username
	 */
	public function create($a_username,$a_userdata = array())
	{
		$a_userdata = $this->parseFullname($a_userdata);

		$this->writer->xmlStartTag('Users');
		
		// Single users
		// Required fields
		// Create user
		$this->writer->xmlStartTag('User',array('Action' => 'Insert'));
		$this->writer->xmlElement('Login',array(),$new_name = ilAuthUtils::_generateLogin($a_username));
				
		// Assign to role only for new users
		$this->writer->xmlElement('Role',array('Id' => $this->settings->getDefaultRole(),
			'Type' => 'Global',
			'Action' => 'Assign'),'');
			
		if(isset($a_userdata['email']))
		{
			$this->writer->xmlElement('Email',array(),$a_userdata['email']);
		}
		if(isset($a_userdata['postcode']))
		{
			$this->writer->xmlElement('PostalCode',array(),$a_userdata['postcode']);
		}
		if(isset($a_userdata['dob']) and $a_userdata['dob'])
		{
			$this->writer->xmlElement('Birthday',array(),$a_userdata['dob']);
		}
		if(isset($a_userdata['gender']))
		{
			$this->writer->xmlElement('Gender',array(),strtolower($a_userdata['gender']));
		}
		if(isset($a_userdata['title']))
		{
			$this->writer->xmlElement('Title',array(),$a_userdata['title']);
		}
		if(isset($a_userdata['firstname']))
		{
			$this->writer->xmlElement('Firstname',array(),$a_userdata['firstname']);
		}
		if(isset($a_userdata['lastname']))
		{
			$this->writer->xmlElement('Lastname',array(),$a_userdata['lastname']);
		}
		
		$this->writer->xmlElement('Active',array(),"true");
		$this->writer->xmlElement('TimeLimitOwner',array(),7);
		$this->writer->xmlElement('TimeLimitUnlimited',array(),1);
		$this->writer->xmlElement('TimeLimitFrom',array(),time());
		$this->writer->xmlElement('TimeLimitUntil',array(),time());
		$this->writer->xmlElement('AuthMode',array('type' => 'openid'),'openid');
		$this->writer->xmlElement('ExternalAccount',array(),$a_username);
			
		$this->writer->xmlEndTag('User');
		$this->writer->xmlEndTag('Users');
		$this->log->write('OpenId: Started creation of user: '.$new_name);
		
		include_once './Services/User/classes/class.ilUserImportParser.php';
		$importParser = new ilUserImportParser();
		$importParser->setXMLContent($this->writer->xmlDumpMem(false));
		$importParser->setRoleAssignment(array($this->settings->getDefaultRole() => $this->settings->getDefaultRole()));
		$importParser->setFolderId(7);
		$importParser->startParsing();
		
		// Assign timezone
		if(isset($a_userdata['timezone']))
		{
			include_once './Services/Calendar/classes/class.ilCalendarUtil.php';
			$tzs = ilCalendarUtil::_getShortTimeZoneList();
			
			if(isset($tzs[$a_userdata['timezone']]))
			{
				$usr_id = ilObjUser::_lookupId($new_name);
				ilObjUser::_writePref($usr_id, 'user_tz', $a_userdata['timezone']);
			}
		}
		
	 	return $new_name;
	}
	
	protected function parseFullname($a_userdata)
	{
		include_once './Services/User/classes/class.ilFullnameParser.php';
		
		$parser = new ilFullnameParser($a_userdata['fullname']);
		
		if($parser->getNotParseable())
		{
			return $a_userdata;
		}
		
		$a_userdata['firstname'] = $parser->getFirstName();
		$a_userdata['lastname'] = $parser->getLastName();
		$a_userdata['title'] = $parser->getTitle();
		
		return $a_userdata;
	}
	
}
?>