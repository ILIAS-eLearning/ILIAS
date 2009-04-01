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
* Class ilPaymentBillVendor
* 
* @author Stefan Meyer <smeyer@databay.de>
* @version $Id$
*
* @extends ilObject
* @package ilias-core
*/

class ilPaymentBillVendor
{
	var $db = null;
	var $lang = null;
	var $pobject_id = null;

	var $has_data = false;


	/**
	* Constructor
	* @access	public
	*/
	function ilPaymentBillVendor($a_pobject_id)
	{
		global $ilDB,$lng;

		$this->db =& $ilDB;
		$this->lng =& $lng;
		$this->pobject_id = $a_pobject_id;

		$this->__read();
	}

	// METHODS FOR INTERNAL ERROR HANDLING
	function getMessage()
	{
		return $this->message;
	}
	function setMessage($a_message)
	{
		$this->message = $a_message;
	}
	function appendMessage($a_message)
	{
		$this->message .= "<br />".$a_message;
	}

	function delete()
	{
		$statement = $this->db->manipulateF('
			DELETE from payment_bill_vendor
			WHERE pobject_id = %s',
			array('integer'),
			array($this->pobject_id));
	
		return true;
	}

	// SET GET
	function getPobjectId()
	{
		return $this->pobject_id;
	}
	function hasData()
	{
		return (bool) $this->has_data;
	}
	function setGender($a_gender)
	{
		$this->gender = $a_gender;
	}
	function getGender()
	{
		return $this->gender;
	}
	function setFirstname($a_firstname)
	{
		$this->firstname = $a_firstname;
	}
	function getFirstname()
	{
		return $this->firstname;
	}
	function setLastname($a_lastname)
	{
		$this->lastname = $a_lastname;
	}
	function getLastname()
	{
		return $this->lastname;
	}
	function getTitle()
	{
		return $this->title;
	}
	function setTitle($a_title)
	{
		$this->title = $a_title;
	}
	function getInstitution()
	{
		return $this->institution;
	}
	function setInstitution($a_institution)
	{
		$this->institution = $a_institution;
	}
	function getDepartment()
	{
		return $this->department;
	}
	function setDepartment($a_department)
	{
		$this->department = $a_department;
	}
	function getStreet()
	{
		return $this->street;
	}
	function setStreet($a_street)
	{
		$this->street = $a_street;
	}
	function getZipcode()
	{
		return $this->zipcode;
	}
	function setZipcode($a_zipcode)
	{
		$this->zipcode = $a_zipcode;
	}
	function getCity()
	{
		return $this->city;
	}
	function setCity($a_city)
	{
		$this->city = $a_city;
	}
	function getCountry()
	{
		return $this->country;
	}
	function setCountry($a_country)
	{
		$this->country = $a_country;
	}
	function getPhone()
	{
		return $this->phone;
	}
	function setPhone($a_phone)
	{
		$this->phone = $a_phone;
	}
	function getFax()
	{
		return $this->fax;
	}
	function setFax($a_fax)
	{
		$this->fax = $a_fax;
	}
	function getEmail()
	{
		return $this->email;
	}
	function setEmail($a_email)
	{
		$this->email = $a_email;
	}
	function getAccountNumber()
	{
		return $this->account_number;
	}
	function setAccountNumber($a_account_number)
	{
		$this->account_number = $a_account_number;
	}
	function getBankcode()
	{
		return $this->bankcode;
	}
	function setBankcode($a_bankcode)
	{
		$this->bankcode = $a_bankcode;
	}
	function getIban()
	{
		return $this->iban;
	}
	function setIban($a_iban)
	{
		$this->iban = $a_iban;
	}
	function getBic()
	{
		return $this->bic;
	}
	function setBic($a_bic)
	{
		$this->bic = $a_bic;
	}
	function getBankname()
	{
		return $this->bankname;
	}
	function setBankname($a_bankname)
	{
		$this->bankname = $a_bankname;
	}

	function validate()
	{
		$this->setMessage('');

		if(!$this->getGender())
		{
			$this->appendMessage($this->lng->txt('gender'));
		}
		if(!$this->getFirstname())
		{
			$this->appendMessage($this->lng->txt('firstname'));
		}
		if(!$this->getLastname())
		{
			$this->appendMessage($this->lng->txt('lastname'));
		}
		if(!$this->getStreet())
		{
			$this->appendMessage($this->lng->txt('street'));
		}
		if(!$this->getZipcode())
		{
			$this->appendMessage($this->lng->txt('zipcode'));
		}
		if(!$this->getCity())
		{
			$this->appendMessage($this->lng->txt('city'));
		}
		if(!$this->getCountry())
		{
			$this->appendMessage($this->lng->txt('country'));
		}
		if(!$this->getEmail())
		{
			$this->appendMessage($this->lng->txt('email'));
		}
		if(!$this->getAccountNumber())
		{
			$this->appendMessage($this->lng->txt('account_number'));
		}
		if(!$this->getBankcode())
		{
			$this->appendMessage($this->lng->txt('bankcode'));
		}
		if(!$this->getBankname())
		{
			$this->appendMessage($this->lng->txt('bankname'));
		}

		return $this->getMessage() ? false : true;
	}
	function update()
	{
		if(!$this->hasData())
		{
			$statement = $this->db->manipulateF('
				INSERT INTO payment_bill_vendor
				(pobject_id)
				VALUES(%s)',
				array('integer'),
				array($this->getPobjectId())
			);
		
		}
		
		$statement = $this->db->manipulateF('
			UPDATE payment_bill_vendor
			SET	gender = %s,
				firstname = %s,
				lastname = %s,
				title = %s,
				institution = %s,
				department = %s,
				street = %s,
				zipcode = %s,
				city = %s,
				country = %s,
				phone = %s,
				fax = %s,
				email = %s,
				account_number = %s,
				bankcode = %s,
				iban = %s,
				bic = %s,
				bankname = %s
			WHERE pobject_id = %s',
			array(	'integer',
					'text',
					'text',
					'text',
					'text',
					'text',
					'text',
					'text',
					'text',
					'text',
					'text',
					'text',
					'text',
					'text',
					'text',
					'text',
					'text'),
			array(	
				$this->getGender(),
				$this->getFirstname(),
				$this->getLastname(),
				$this->getTitle(),
				$this->getInstitution(),
				$this->getDepartment(),
				$this->getStreet(),
				$this->getZipcode(),
				$this->getCity(),
				$this->getCountry(),
				$this->getPhone(),
				$this->getFax(),
				$this->getEmail(),
				$this->getAccountNumber(),
				$this->getBankcode(),			
				$this->getIban(),
				$this->getBic(),
				$this->getBankname(),
				$this->getPobjectId()
			)
		);

		$this->__read();
	}


	// PIRVATE
	function __read()
	{
		$res = $this->db->queryf('
			SELECT * FROM payment_bill_vendor
			WHERE pobject_id = %s',
			array('integer'),
			array($this->getPobjectId()));
					
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$this->has_data = true;

			$this->setGender($row->gender);
			$this->setFirstname($row->firstname);
			$this->setLastname($row->lastname);
			$this->setTitle($row->title);
			$this->setInstitution($row->institution);
			$this->setDepartment($row->department);
			$this->setStreet($row->street);
			$this->setZipcode($row->zipcode);
			$this->setCity($row->city);
			$this->setCountry($row->country);
			$this->setPhone($row->phone);
			$this->setFax($row->fax);
			$this->setEmail($row->email);
			$this->setAccountNumber($row->account_number);
			$this->setBankcode($row->bankcode);
			$this->setIban($row->iban);
			$this->setBic($row->bic);
			$this->setBankname($row->bankname);
		}
		return true;
	}
} 
?>
