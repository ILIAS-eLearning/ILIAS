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

require_once './Services/Payment/exceptions/class.ilERPException.php';

abstract class ilERP 
{
  protected $username;
  protected $password;
  protected $use_ean; // Danish public sector only.
  protected $save_copy;
    
  private $erps_id =0; // future support for several settings  
  private static $erp_id = ERP_NONE;
    
  const preview_pre = "invoice_";
  const preview_ext = ".pdf";
  //abstract erp_id;
  
  abstract public function getName(); 	
	abstract public function loadSettings($erps_id=0);
	//abstract public function saveSettings($settings);
  abstract public function looksValid();
	abstract public function connect();	
  abstract public function disconnect();
  

  
  /**
  * Get filename for preview invoice
  * invoice_[client_id].pdf
  */  
  private static function _getFilename()
  {
    global $ilias;
    return self::preview_pre . $ilias->client_id . self::preview_ext;
  }
  
  public static function getPreviewFile()
  {
    global $ilias;
    return   $ilias->ini_ilias->GROUPS['server']['absolute_path'] . '/' . self::_getFilename();  
  }   
  
  public static function getPreviewUrl()
  {
    global $ilias;
    return $ilias->ini_ilias->GROUPS['server']['http_path'] . '/' . self::_getFilename() ;
  }
  public static function getPreviewLink()
  {
    return self::getPreviewUrl . self::_getFilename() ;
  }  
  public static function getSaveDirectory()  
  {
    global $ilias;
    return $ilias->ini_ilias->GROUPS['clients']['datadir'] . '/' . $ilias->client_id .'/invoices/';
  }
  
  public static function preview_exists()
  {
    return file_exists( self::getPreviewFile() );
  }
  public static function preview_delete()
  {
    if (self::preview_exists())
    unlink (self::getPreviewFile());
  }
  /**
	* Get the list of ILIAS supported ERPs 
	* @return mixed array of ERPs	
	*/
  public static function getAllERPs()
	{
    global $ilDB;
    $res = $ilDB->query('SELECT * FROM payment_erp ORDER BY erp_id' );    
    $a = array();        
    while ( $result = $res->fetchRow(MDB2_FETCHMODE_ASSOC) ) $a[$result['erp_id']] = $result;
    return $a;    
	}  
 
  /**
	* Set settings posted by some form
	*/	
	public function setSettings($a)
	{
    $this->setUsername( $a['username'] );
    $this->setPassword( $a['passsword'] );    
    $this->setSaveCopy( $a['save_copy'] );
    $this->setUseEAN(   $a['use_ean'] );	
	}
	
  public function saveSettings($settings)  
  {	
    global $ilDB;
    unset( $settings['url']);
    unset( $settings['description']);
    unset( $settings['erp_short']);
    unset( $settings['name']);
    
    $settings['save_copy'] = (int) $settings['save_copy'];
    $settings['use_ean'] = (int) $settings['use_ean'];           
    
    if ($settings['erp_id'] == 0) 
    {
      unset($settings);
      $settings['erp_id'] = 0;      
    }    
    
    $ilDB->manipulateF("
      UPDATE payment_erp SET save_copy=%s, use_ean=%s WHERE erp_id=%s",
      array("integer", "integer", "integer"),
      array($settings['save_copy'], $settings['use_ean'], $settings['erp_id'])
    );
    
    unset($settings['save_copy']);
    unset($settings['use_ean']);
	
    $ilDB->manipulateF("
      UPDATE payment_erps SET settings=%s WHERE erps_id=%s AND erp_id=%s",
      array("text", "integer", "integer"),
      array( serialize($settings), $this->erps_id, $settings['erp_id']));
    return true;	
	}		  	
	
   
	
	/**
	* Set the username used to login to ERP
	* @param string $v ERP login name
	*/		
	protected function setUsername( $v ) 
	{
    $this->username = $v;
	}

  /**
	* Set the password used in the ERP
	* @param string $v ERP password
	*/		
	protected function setPassword($v )
	{
    $this->password = $v;
	}
	
	/**
	* Set the directory for saving invoices
	* @param string $v directory
	*/
	protected function setSaveCopy( $v )
	{
    $this->save_copy = (int) $v;
	}
	
	protected function setUseEAN( $v )
	{
    $this->use_ean = (int) $v;
  }
	
	

	/**
	* Sets a specific configuration active and disable all other ERPs.
	* @param int $erp_system predefined constant specifying the ERP-system
	* @param int $erp_settings unsupported currently	
	**/	
	public function setActive($erp_system = 0, $erp_settings = 0)
	{
    global $ilDB;
    $ilDB->query('UPDATE payment_erps SET active=0');
    $ilDB->query('UPDATE payment_erps SET active=1 WHERE erp_id=' . $erp_system . ' AND erps_id=' . $erp_settings);
	}
	
	/**
	* Get information about what ERP is activated
	* @return mixed 
	*/	
	public static function getActive()
	{
    global $ilDB;
    $row = $ilDB->query('SELECT payment_erps.erp_id, payment_erps.erps_id, payment_erp.erp_short,payment_erp.use_ean, payment_erp.save_copy FROM payment_erps,payment_erp WHERE payment_erps.active=1 AND payment_erps.erp_id=payment_erp.erp_id LIMIT 1');
    $values = $row->fetchRow(MDB2_FETCHMODE_ASSOC);
    return $values;
	}
	

	
	
	
	

	
	/**
	* Return all relevant settings for a configuration. 
	* This includes ERP-system constants, general setttings (i.e. username)
	* and subclasses should merge their data into the output.	
	**/	
	public function getSettings($elvis_is_alive = 0)
	{    
    $system = $this->getERPConstants(self::$erp_id);
    $a['username'] = $this->username;
    $a['password'] = $this->password;
    $a['use_ean'] = $this->use_ean;
    $a['save_copy'] = $this->save_copy;
    return array_merge($system, $a);
	}
	
	
	

	/**
	* Get some ERP system specific variables, stored in payment_erp
	*
	* @return mixed
	*/
	public function getERPconstants($erp_system = 0)
	{
    global $ilDB;
    $res = $ilDB->query('SELECT * FROM payment_erp WHERE erp_id=' . $erp_system);
    $result = $res->fetchRow(MDB2_FETCHMODE_ASSOC);    
    return $result;    
	}
	

}
?>