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
* 
* 
* @ingroup payment
*/

define("ERP_NONE", 0);
define("ERP_ECONOMIC", 1);


class ilERP 
{
  protected $username;
  protected $password;  
  
  protected $db;
  protected $connection_ok;
  protected $last_connection_error;
  
  private $erps_id; // future support for several settings  
  
  const erp_id = ERP_NONE;
  const name = "n/a";
  	
	public function __construct()
	{
		global $ilDB;
		$this->db =& $ilDB;		
		$this->erps_id = 0;		
	}  
	
	/**
	* Virtual function. Should be overridden by subclasses to support specific ERPs
	*
	**/	
	public function loadSettings($erps_id=0)
	{
    assert($erps_id == 0);
  }
  
  /*
  * Retrives the last ERP error
  * @return string
  */  
  public function getLastError()
  {
    return $this->last_connection_error;
  }
  
  /**
  * Get name of current ERP system.
  * Usefull for messages
  * @return string
  */  
  public function getName()
  {
    $class = get_class($this);
    return $class::name;    
  }
   
	
	/**
	* Set the username used to login to ERP
	* @param string $v ERP login name
	*/		
	protected function setUsername ( $v ) 
	{
    $this->username = $v;
	}

  /**
	* Set the password used in the ERP
	* @param string $v ERP password
	*/		
	protected function setPassword ($v )
	{
    $this->password = $v;
	}
	
	/**
	* Get the list of ILIAS supported ERPs 
	* @return mixed array of ERPs	
	*/
  public function getAllERPs()
	{
    $res = $this->db->query('SELECT * FROM payment_erp ORDER BY erp_id' );    
    $a = array();        
    while ( $result = $res->fetchRow(MDB2_FETCHMODE_ASSOC) ) $a[$result['erp_id']] = $result;
    return $a;    
	}

	/**
	* Sets a specific configuration active and disable all other ERPs.
	* @param int $erp_system predefined constant specifying the ERP-system
	* @param int $erp_settings unsupported currently	
	**/	
	public function setActive($erp_system = 0, $erp_settings = 0)
	{
    $this->db->query('UPDATE payment_erps SET active=0');
    $this->db->query('UPDATE payment_erps SET active=1 WHERE erp_id=' . $erp_system . ' AND erps_id=' . $erp_settings);
	}
	
	/**
	* Get information about what ERP is activated
	* @return mixed 
	*/	
	public function getActive()
	{
    $row = $this->db->query('SELECT payment_erps.erp_id, payment_erps.erps_id, payment_erp.erp_short FROM payment_erps,payment_erp WHERE payment_erps.active=1 AND payment_erps.erp_id=payment_erp.erp_id LIMIT 1');
    $values = $row->fetchRow(MDB2_FETCHMODE_ASSOC);
    return $values;
	}
	
  /**
	* Save setup for some generic ERP-system
	*
	* @access public
	**/
	public function saveSettings($settings)
	{	
    //$this->erps_id = 0;    
    unset( $settings['url']);
    unset( $settings['description']);
    unset( $settings['erp_short']);
    unset( $settings['name']);
    
    if ($settings['erp_id'] == 0) 
    {
      unset($settings);
      $settings['erp_id'] = 0;
    }    
	
    $this->db->manipulateF("
      UPDATE payment_erps SET settings=%s WHERE erps_id=%s AND erp_id=%s",
      array("text", "integer", "integer"),
      array( serialize($settings), $this->erps_id, $settings['erp_id']));    
      
    return true;	
	}		
	
	
	/**
	* Set settings posted by some form
	*/	
	public function setSettings($a)
	{   
    $this->setUsername( $a['username'] );
    $this->setPassword( $a['passsword'] );    
	}
	
	/**
	* Return all relevant settings for a configuration. 
	* This includes ERP-system constants, general setttings (i.e. username)
	* and subclasses should merge their data into the output.	
	**/	
	public function getSettings($elvis_is_alive = 0)
	{
    //$system = $this->getERPConstants($this->erp_id);
    $system = $this->getERPConstants(self::erp_id);
    $a['username'] = $this->username;
    $a['password'] = $this->password;
    return array_merge($system, $a);
	}
	
	/**
	*
	*/
	public function looksValid()
	{    
    return true;
  }
	
	public function connect()
	{
    $this->connection_ok = true;
    return true;
  }
  
  public function connected()
  {
    return $this->connection_ok;
  }
	
	/**
	* Get some ERP system specific variables, stored in payment_erp
	*
	* @return mixed
	*/
	public function getERPconstants($erp_system = 0)
	{
    $res = $this->db->query('SELECT * FROM payment_erp WHERE erp_id=' . $erp_system);
    $result = $res->fetchRow(MDB2_FETCHMODE_ASSOC);    
    return $result;    
	}
	

}
?>