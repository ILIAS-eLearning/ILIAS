<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Payment/classes/class.ilShopVats.php';
require_once 'Services/Payment/classes/class.ilShopUtils.php';

/**
* Class ilShopVatsList
* 
* @author Michael Jansen <mjansen@databay.de>
* @version $Id$
* 
* @ingroup ServicesPayment
*  
*/
class ilShopVatsList implements Iterator
{
	private $db = null;
	private $num_vat_list = 0;
	private $vat_list = array();
	
	/**
	* Checks if the passed vat rate already exists in database.
	* 
	* @access	public 
	* @static
	* @param	string	$a_vat_rate	The vat rate wish should be checked.
	* @param	int	$a_vat_id Id of an existing vat rate. (optional)
	* @return	bool
	*  
	*/
	public static function _isVATAlreadyCreated($a_vat_rate, $a_vat_id = null)
	{
		global $ilDB;
		
		$types = array();
		$data = array();
		
		$query = "SELECT * FROM payment_vats "
			   . "WHERE vat_rate = ? ";
		$types[] = 'float';
		$data[] = (float)$a_vat_rate;
		
		if((int)$a_vat_id)
		{
			$query .= "AND vat_id != ? ";
			$types[] = 'integer';
			$data[] = $a_vat_id;
		}

		$statement = $ilDB->prepare(trim($query), $types);
		$result = $ilDB->execute($statement, $data);
		while($row = $ilDB->fetchObject($result))
		{
			return true;	
		}		
		
		return false;
	}
	
	/**
	* Constructor
	* 
	* @access	public
	*  
	*/
	public function __construct()
	{
		global $ilDB;
		
		$this->db = $ilDB;
	}
	
	/**
	* Reads the vat datasets from database.
	* 
	* @access	public 
	* @return	ilShopVatsList
	*  
	*/
	public function read()
	{		
		$this->vat_list = array();
		
		$query = "SELECT * FROM payment_vats ";
		$query.= "WHERE 1 = 1 ";		
		if(!in_array($this->getOrderColumn(), array('vat_title', 'vat_rate')))
		{
			$this->setOrderColumn('vat_rate');
			$this->setOrderDirection('ASC');
		}		
		
		$order_limit = " ORDER BY ".$this->getOrderColumn()." ".strtoupper($this->getOrderDirection())." ";		
		if((int)$this->getListMax())
		{
			$order_limit .= "LIMIT ".$this->getListStart().", ".$this->getListMax();
		}

		$res = $this->db->query($query.$order_limit);	
		while($row = $this->db->fetchObject($res))
		{			
			$oVAT = new ilShopVats();
			$oVAT->setId($row->vat_id);			
			$oVAT->setTitle($row->vat_title);
			$oVAT->setRate($row->vat_rate);

			$this->vat_list[$oVAT->getId()] = $oVAT;	
		}
		
		$res = $this->db->query(str_replace('*', 'COUNT(vat_id) AS num_vat_list', $query));	
		while($row = $this->db->fetchObject($res))
		{			
			$this->num_vat_list = $row->num_vat_list;
			break;	
		}

		return $this;
	}
	
	public function getNumItems()
	{
		return $this->num_vat_list;
	}	
	public function hasItems()
	{
		return (bool)count($this->vat_list);
	}	
	public function getItems()
	{
		return is_array($this->vat_list) ? $this->vat_list : array();
	}	
	public function rewind()
	{
		return reset($this->vat_list);
	}	
	public function valid()
	{
		return (bool)current($this->vat_list);
	}
	public function current()
	{
		return current($this->vat_list);
	}
	public function key()
	{
		return key($this->vat_list);
	}
	public function next()
	{
		return next($this->vat_list);
	}
	
	/**
	* Setter for order column.
	* 
	* @access	public
	* @param	string	$a_order_column
	* @return	ilShopVatsList
	*  
	*/
	public function setOrderColumn($a_order_column)
	{
		$this->order_column = $a_order_column;
		
		return $this;
	}
	/**
	* Getter for order column.
	* 
	* @access	public 
	* @return	string
	*  
	*/
	public function getOrderColumn()
	{
		return $this->order_column;
	}
	/**
	* Setter for order direction.
	* 
	* @access	public
	* @param	string	$a_order_column
	* @return	ilShopVatsList
	*  
	*/
	public function setOrderDirection($a_order_column)
	{
		$this->order_direction = $a_order_column;
		
		return $this;
	}
	/**
	* Getter for order direction.
	* 
	* @access	public 
	* @return	string
	*  
	*/
	public function getOrderDirection()
	{
		return $this->order_direction;
	}
	/**
	* Setter for start value.
	* 
	* @access	public
	* @param	int	$a_list_start
	* @return	ilShopVatsList
	*  
	*/
	public function setListStart($a_list_start)
	{
		$this->list_start = $a_list_start;
		
		return $this;
	}
	/**
	* Getter for start value.
	* 
	* @access	public 
	* @return	int
	*  
	*/
	public function getListStart()
	{
		return $this->list_start;
	}
	/**
	* Setter for max value.
	* 
	* @access	public
	* @param	int	$a_list_max
	* @return	ilShopVatsList
	*  
	*/
	public function setListMax($a_list_max)
	{
		$this->list_max = $a_list_max;
		
		return $this;
	}	
	/**
	* Getter for max value.
	* 
	* @access	public 
	* @return	int
	*  
	*/
	public function getListMax()
	{
		return $this->list_max;
	}
}
?>
