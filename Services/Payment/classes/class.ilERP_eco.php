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

class ilERP_eco extends ilERP
{ 
  private $agreement;
  private $product;
  private $terms;
  private $layout;
  private $code;
  
  private static $instance = null;
  
  public $client;
  
  const wsdl = "https://www.e-conomic.com/secure/api1/EconomicWebservice.asmx?WSDL";
  private static $erp_id = ERP_ECONOMIC;
  const name = "E-conomic";
	
	public function __construct()
	{
    //parent::__construct();
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
    $this->loadSettings($erps_id);
    $ap = parent::getSettings();	
    $ret = array();
    $ret['agreement'] = $this->agreement;   
    $ret['product'] = $this->product;
    $ret['terms'] = $this->terms;
    $ret['layout'] = $this->layout;
    $ret['code'] = $this->code;    
    
    return array_merge($ap, $ret);    
	}
	   
  public function connect()
  {
    if ($this->connection_ok) return;    
    try 
    {      
      $this->client = new SoapClient(self::wsdl, array("trace" => 1, "exceptions" => 1));
      $this->client->Connect(array(
        'agreementNumber' => $this->agreement,
        'userName' => $this->username,
        'password' => $this->password)
      );
    }
    catch (Exception $e)
    {
      $this->connection_ok = false;
      throw new ilERPException(__FILE__ . ":" . __LINE__ . " " . $e->getMessage());
    }
    $this->connection_ok = true;
  }
  public function disconnect()
  {
    unset($this->client);
    $this->connection_ok = false;
  }
  
  public function getName()
  {
    return "E-conomic";
  }

	
	/**
	* Set the e-conomic agreement number
	* @access private
	*/	
	private function setAgreement( $v ) 
	{
    $this->agreement = (int) $v;
	}	
  private function setProduct( $v )
  {
    $this->product = (int) $v;
  }
  private function setTerms( $v )
  {
    $this->terms = (int) $v;
  }
  private function setLayout( $v )
  {
    $this->layout = (int) $v;
  }
  private function setCode( $v )
  {
    $this->code = $v;
  }
 
	
	
	/**
	* Sets e-conomic settings.
	*
	* @access public
	*/
	public function setSettings( $a )
	{    
    $this->setAgreement( $a['agreement'] );    
    $this->setProduct( $a['product'] );
    $this->setTerms( $a['terms'] );
    $this->setLayout( $a['layout'] );
    $this->setCode( $a['code'] );
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
      //if (!parent::looksValid()) return false;
	
      $s = $this->getSettings();
      $ok = true;
      return true;
      if ($s['agreement'] == 0) $ok = false;      
      if (($s['product']==0) || ($s['terms']==0) || ($s['layout']==0) ) $ok = false;       
      return $ok;	
	}
	
	/** 
	 * Called from constructor to fetch settings from database
	 *
	 * @access	private
	 */
	public function loadSettings($erps_id = 0)
	{
    global $ilDB;

		$res = $ilDB->queryf('SELECT * FROM payment_erps WHERE erps_id=%s AND erp_id=%s', 
      array("integer", "integer"),
      array($erps_id, ERP_ECONOMIC));
		
		$result = $res->fetchRow(DB_FETCHMODE_OBJECT);
				
		if (is_object($result))
		{
			if ($result->settings != "") $data = unserialize($result->settings);
			else 
			{
        // set some defaults
        $data['agreement']=0;
        $data['username']='erpuser';
        $data['password']='pasword1234';
        $data['product']=0;
        $data['terms']=0;
        $data['layout']=0;
        $data['code']='EUR';
			}
		}		
		$this->setAgreement( $data['agreement'] );
		$this->setUsername( $data['username'] );
		$this->setPassword( $data['password'] );
		$this->setProduct( $data['product']);
		$this->setTerms( $data['terms']);
		$this->setLayout( $data['layout']);
		$this->setCode( $data['code']);		
	}	
}
?>