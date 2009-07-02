<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2008 ILIAS open source, University of Cologne            |
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


include_once 'payment/classes/class.ilGeneralSettings.php';
include_once 'Services/Payment/exceptions/class.ilShopException.php';
include_once 'Services/Payment/classes/class.ilShopUtils.php';

/**
* Class ilShopVats
* 
* @author Nadia Krzywon <nkrzywon@databay.de>
* @version $Id$
* 
* @ingroup ServicesPayment
*  
*/
class ilShopVats
{		
	private $id = 0;
	private $title = '';
	private $rate = 0;
	
	/**
	* Constructor
	* 
	* @param	$a_vat_id	The primary key of a vat dataset.
	* @access	public
	*  
	*/
	public function __construct($a_vat_id = 0)
	{
		global $ilDB, $lng;

		$this->db = $ilDB;
		$this->lng = $lng;
		
		if((int)$a_vat_id)
		{
			$this->id = $a_vat_id;
			$this->read();
		}
	}
	
	/**
	* Fetches the data of a vat dataset from database.
	* 
	* @access	private
	* @throws	ilShopException  
	*/
	private function read()
	{
		if((int)$this->id)
		{
	
			$res = $this->db->queryf('SELECT * FROM payment_vats 
			   			WHERE vat_id = %s',
			array('integer'), array($this->id) );	
			
			while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
			{		
				$this->setTitle($row->vat_title);
				$this->setRate($row->vat_rate);	
				
				return true;	
			}

			throw new ilShopException($this->lng->txt('payment_cannot_find_vat'));
		}
		
		throw new ilShopException($this->lng->txt('payment_cannot_read_nonexisting_vat'));
	}

	/**
	* Public interface to reload the capsuled data of a vat from database. Throws a
	* ilShopException if the object property $this->id has no valid value
	* (because $this->read() is called).
	* 
	* @access	public
	* @return	ilShopVats  
	*/
	public function reloadFromDatabase()
	{
		$this->read();
		
		return $this;
	}
	
	/**
	* Updates an existing vat dataset.
	* 
	* @access	public
	* @return	bool	Returns true if no error occured.
	* @throws   ilShopException
	*/
	public function update()
	{
		if((int)$this->id)
		{
			if(ilShopVatsList::_isVATAlreadyCreated($this->rate, $this->id))
			{
				throw new ilShopException($this->lng->txt('payment_vat_already_created'));
			}
			
				   
			$this->db->manipulatef('			
					UPDATE payment_vats
					SET vat_title = %s,
						vat_rate = %s
					WHERE vat_id = %s',
					array('text', 'float', 'integer'),
					array($this->getTitle(),$this->getRate(),$this->getId())
				);
				   
			return true;
		}
		
		throw new ilShopException($this->lng->txt('payment_cannot_update_nonexisting_vat'));
	}
	
	/**
	* Saves a new vat dataset.
	* 
	* @access	public
	* @return	bool	Returns true if no error occured.
	* @throws   ilShopException
	*/
	public function save()
	{
		if(!(int)$this->id)
		{
			if(ilShopVatsList::_isVATAlreadyCreated($this->rate))
			{
				throw new ilShopException($this->lng->txt('payment_vat_already_created'));
			}

			$next_id = $this->db->nextId('payment_vats');
		
			$this->db->manipulateF('
				INSERT INTO payment_vats
				(vat_id, vat_title, vat_rate)
				VALUES (%s,%s,%s)',
				array('integer', 'text', 'float'),
				array($next_id, $this->getTitle(), $this->getRate())
			);		
			return true;
		}
		
		throw new ilShopException($this->lng->txt('payment_cannot_save_existing_vat'));
	}
	
	/**
	* Deletes an existing vat dataset.
	* 
	* @access	public
	* @return	bool	Returns true if no error occured.
	* @throws   ilShopException
	*/
	public function delete()
	{
		if((int)$this->id)
		{
			$result = $this->db->queryF('
				SELECT * FROM payment_objects 
				WHERE vat_id = %s',
				array('integer'),
				array($this->getId())
			);

			while($row = $result->fetchRow(DB_FETCHMODE_OBJECT))
			{
				throw new ilShopException(sprintf($this->lng->txt('paya_vat_not_deleted'), $this->title));
			}		
		
		 	$this->db->manipulateF('
				DELETE FROM payment_vats
				WHERE vat_id = %s',
				array('integer'),
				array($this->getId())
				
			);
			return true;	
		}

		throw new ilShopException($this->lng->txt('payment_cannot_delete_nonexisting_vat'));
	}
	
	/**
	* Setter for the title.
	* 
	* @access	public
	* @paramt	string	$a_title
	* @return	ilShopVats
	*/
	public function setTitle($a_title)
	{
		$this->title = $a_title;
		
		return $this;
	}
	public function getTitle()
	{
		return $this->title;	
	}
	/**
	* Setter for the id.
	* 
	* @access	public
	* @param	int	$a_id
	* @return	ilShopVats
	*/
	public function setId($a_id)
	{
		$this->id = $a_id;
		
		return $this;
	}
	public function getId()
	{
		return $this->id;
	}
	/**
	* Setter for the vat rate.
	* 
	* @access	public
	* @param	float $a_rate
	* @return	ilShopVats
	*/
	public function setRate($a_rate)
	{
		$this->rate = $a_rate;
		
		return $this;
	}
	public function getRate()
	{
		return $this->rate;
	}
}

?>