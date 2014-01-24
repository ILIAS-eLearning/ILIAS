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
* @author Jesper Godvad <jesper@ilias.dk>
* @author Nicolai Lundgaard <nicolai@ilias.dk>
* @version $Id$
* 
* @ingroup payment
*/


require_once './Services/Payment/classes/class.ilERP.php';

class ilERP_none extends ilERP
{ 
  private $agreement;
  private $product;
  private $terms;
  private $layout;
  private $code;
  
  private static $instance = null;
  
  public $client;
    
  const erp_id = ERP_NONE;
  const name = "none";
	
	public function __construct()
	{    
    $this->loadSettings(0);    
  }
  
  
  
  /**
	* Static method to get the singleton instance
	* 
	* @access	public
	* @return	object $instance Singular E-conomic instance
	*/
	public static function _getInstance()
	{
		if (isset(self::$instance) and self::$instance)
		{
      return self::$instance;    
    }
    return self::$instance = new ilERP_eco();
    
	}

	
	/**
	* Return e-conomic settings as an array
	* 
	* @access public
	* @return	array
	*/
	public function getSettings($erps_id = 0)	
	{    
    return parent::getSettings();	
    
	}
	   
  public function connect()
  {
    $this->connection_ok = true;
  }  
  public function disconnect()
  {
    $this->connection_ok = false;
  }
  
  public function getName()
  {
    return "none";
  }

	
	
	
	
	/**
	* Sets e-conomic settings.
	*
	* @access public
	*/
	public function setSettings( $a )
  {
  parent::setSettings($a);    
	}
	
	/**
	* Returns true if the e-conomic settings looks valid to the interface
	* Done without testing conection etc.
	*
	* @access public
	*/	
	public function looksValid()
	{
    return true;
	}
	
	
	
	/** 
	 * Called from constructor to fetch settings from database
	 *
	 * @access	private
	 */
	public function loadSettings($erps_id = 0)
	{
	}
	
  
	
}
?>