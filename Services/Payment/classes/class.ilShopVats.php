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
	private static $instance;
		
	private $vat_id = 0;
	private $vat_title = '';
	private $vat_rate = 0;

	
	public function __construct($a_vat_id = null, $a_vat_rate = null)
	{
		global $ilDB;

		$this->db = $ilDB;
				
		$this->vat_id = (int)$a_vat_id;
		$this->vat_title = $a_vat_title;
		$this->vat_rate = $a_vat_rate; 
		
		$this->_read();
	}

	
	public static function _read($a_vat_id = null, $a_vat_rate = null, $a_sort_by = null, $a_sort_order = null, $a_offset = 0)
	{
		global $ilDB;

		$vats = array();

		$data_types = array();
		$data_values = array();
		
		$query = 'SELECT * FROM payment_vats';
		
		if(isset($a_vat_id))
		{
			$query .= ' WHERE vat_id = %s';
			array_push($data_types, 'integer');
			array_push($data_values, (int)$a_vat_id);
		}
		else
		if(isset($a_vat_rate))
		{
	
			$query .= ' WHERE vat_rate = %s ';
			array_push($data_types, 'float');
			array_push($data_values, $a_vat_rate);
		}

		if(isset($a_sort_by))
		{
				$query .= ' ORDER BY '.$a_sort_by;
		}
		else $query .= ' ORDER BY vat_rate ';
		
		if(isset($a_sort_order))
		{ 
				$query .= ' '.$a_sort_order;
		}
		

		if(count($data_types) >= 1 && count($data_values) >= 1)
		{
			
			$res = $ilDB->queryF($query, $data_types, $data_values);
			while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
			{
				$vats['vat_id'] = $row->vat_id;
				$vats['vat_title'] = $row->vat_title;
				$vats['vat_rate'] = $row->vat_rate;			
			}	
		}
		else 
		{	
			$res = $ilDB->query($query);
	
			$counter = 0;
			while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
			{
				$vats[$counter]['vat_id'] = $row->vat_id;
				$vats[$counter]['vat_title'] = $row->vat_title;
				$vats[$counter]['vat_rate'] = $row->vat_rate;			
				$counter++;
			}	
	
		}

		return $vats;		
	}
	
	public static function _getVatId($a_vat_rate)
	{
		global $ilDB;
		
		$res = $ilDB->queryF('
			SELECT * FROM payment_vats WHERE
				vat_rate = %s',
		array('float'),
		array($a_vat_rate));
		
		while($row = $res->fetchRow(DB_FETCHMODE_ASSOC))
		{
			$vat_id = $row['vat_id'];
		}
		return $vat_id;
	}	
	public static function _getVatRate($a_vat_id)
	{
		global $ilDB;
		
		$res = $ilDB->queryF('
			SELECT * FROM payment_vats WHERE
				vat_id = %s',
		array('integer'),
		array($a_vat_id));
		
		while($row = $res->fetchRow(DB_FETCHMODE_ASSOC))
		{
			$vat_rate = $row['vat_rate'];
		}
		return $vat_rate;
	}
	
	function setVatId($a_vat_id)
	{
		$this->vat_id = $a_vat_id;
	}
	
	function getVatId()
	{
		return $this->vat_id;
	}

	function setVatRate($a_vat_rate)
	{
		$this->vat_rate = $a_vat_rate;
	}	
	

	function getVatRate()
	{
		return $this->vat_rate;
	}
	
	function setVatUnit($a_vat_unit)
	{
		$this->vat_unit = $a_vat_unit;
	}	
	function getVatUnit()
	{
		return $this->vat_unit;
	}	
	function setVatTitle($a_vat_title)
	{
		$this->vat_title = $a_vat_title;
	}
	
	function getVatTitle()
	{
		return $this->vat_title;
	}	
	
	function updateVat()
	{
		global $ilDB;
		
		$res = $ilDB->manipulateF('
			UPDATE payment_vats
			SET vat_title = %s,
				vat_rate = %s
			WHERE vat_id = %s',
			array('text', 'float', 'integer'),
			array($this->getVatTitle(),$this->getVatRate(),$this->getVatId())
		);
		return true;
	}
	
	function deleteVat()
	{
		global $ilDB;

		$res = $ilDB->queryF('
			SELECT * FROM payment_objects 
			WHERE vat_rate = %s',
			array('integer'),
			array($this->getVatRate())
		);

		if($ilDB->numRows($res) == 0)
		{
		 	$ilDB->manipulateF('
				DELETE FROM payment_vats
				WHERE vat_id = %s',
				array('integer'),
				array($this->getVatId())
				
			);
			return true;	
		}
		else 
		{
			//vat_id exists in payment_objects table
			return false;	
		}
	}
	
	function insertVat()
	{
		global $ilDB;
		
		$next_id = $ilDB->nextId('payment_vats');
		
		$ilDB->manipulateF('
			INSERT INTO payment_vats
			(vat_id, vat_title, vat_rate)
			VALUES (%s,%s,%s)',
			array('integer', 'text', 'float'),
			array($next_id, $this->getVatTitle(), $this->getVatRate())
		);
	}		
	
}
?>