<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/** 
* @defgroup payment payment
*/

/** 
* @author Michael Jansen <mjansen@databay.de>
* @version $Id$
* 
* 
* @ingroup payment
*/
include_once './Services/Payment/classes/class.ilPaymentSettings.php';
class ilBMFSettings
{
	private $db;
	public $pSettings;
	
	private $settings_id;
	
	private $client_id;
	private $bewirtschafter_nr;
	private $haushaltsstelle;
	private $object_id;
	private $kennzeichen_mahnverfahren;
	private $waehrungs_kennzeichen;
	private $epayment_server;
	private $client_certificate;
	private $ca_certificate;
	private $timeout;		
	
	static private $instance = null;
	
	/**
	* Static method to get the singleton instance
	* 
	* @access	public
	* @return	object $instance Singular ilBMFSettings instance
	*/
	static public function getInstance()
	{
		if (!self::$instance)
		{
	    	self::$instance = new ilBMFSettings();
	    }
	    
	    return self::$instance;	    	    
	}

	/**
	* Constructor
	* 
	* @access	private
	*/
	private function ilBMFSettings()
	{
		global $ilDB;

		$this->db = $ilDB;
		$this->pSettings = ilPaymentSettings::_getInstance();
		$this->getSettings();
	}	
	
	/** 
	 * Called from constructor to fetch settings from database
	 *
	 * @access	private
	 */
	private function getSettings()
	{
		$result_bmf = NULL;
		$result_bmf = $this->pSettings->get('bmf');
		$data = array();
		
		if ($result_bmf != "" && $result_bmf != NULL)
		{
			$data = unserialize($result_bmf);
		}

		$this->setClientId($data["mandantNr"]);
		$this->setBewirtschafterNr($data["bewirtschafterNr"]);
		$this->setHaushaltsstelle($data["haushaltsstelle"]);
		$this->setObjectId($data["objektNr"]);
		$this->setKennzeichenMahnverfahren($data["kennzeichenMahnverfahren"]);
		$this->setWaehrungsKennzeichen($data["waehrungskennzeichen"]);
		$this->setEpaymentServer($data["ePaymentServer"]);
		$this->setClientCertificate($data["clientCertificate"]);
		$this->setCaCertificate($data["caCertificate"]);
		$this->setTimeout($data["timeOut"]);
	}
	
	/** 
	 * Fetches and sets the primary key of the payment settings
	 *
	 * @access	private
	 */
	private function fetchSettingsId()
	{
//		$result = $this->db->query('SELECT settings_id FROM payment_settings');
//
//		while($row = $this->db->fetchObject($result))
//		{
//			$this->setSettingsId($row->settings_id);
//		}
	}
	
	public function setSettingsId($a_settings_id = 0)
	{
//		$this->settings_id = $a_settings_id;
	}
	public function getSettingsId()
	{
//		return $this->settings_id;
	}
	public function setClientId($a_client_id)
	{
		$this->client_id = $a_client_id;
	}
	public function getClientId()
	{
		return $this->client_id;
	}
	public function setBewirtschafterNr($a_bewirtschafter_nr)
	{
		$this->bewirtschafter_nr = $a_bewirtschafter_nr;
	}
	public function getBewirtschafterNr()
	{
		return $this->bewirtschafter_nr;
	}
	public function setHaushaltsstelle($a_haushaltsstelle)
	{
		$this->haushaltsstelle = $a_haushaltsstelle;
	}
	public function getHaushaltsstelle()
	{
		return $this->haushaltsstelle;
	}
	public function setObjectId($a_object_id)
	{
		$this->object_id = $a_object_id;
	}
	public function getObjectId()
	{
		return $this->object_id;
	}
	public function setKennzeichenMahnverfahren($a_kennzeichen_mahnverfahren)
	{
		$this->kennzeichen_mahnverfahren = $a_kennzeichen_mahnverfahren;
	}
	public function getKennzeichenMahnverfahren()
	{
		return $this->kennzeichen_mahnverfahren;
	}
	public function setWaehrungsKennzeichen($a_waehrungs_kennzeichen)
	{
		$this->waehrungs_kennzeichen = $a_waehrungs_kennzeichen;
	}
	public function getWaehrungsKennzeichen()
	{
		return $this->waehrungs_kennzeichen;
	}
	public function setEpaymentServer($a_epayment_server)
	{
		$this->epayment_server = $a_epayment_server;
	}
	public function getEpaymentServer()
	{
		return $this->epayment_server;
	}
	public function setClientCertificate($a_client_certificate)
	{
		$this->client_certificate = $a_client_certificate;
	}
	public function getClientCertificate()
	{
		return $this->client_certificate;
	}
	public function setCaCertificate($a_ca_certificate)
	{
		$this->ca_certificate = $a_ca_certificate;
	}
	public function getCaCertificate()
	{
		return $this->ca_certificate;
	}
	public function setTimeout($a_timeout)
	{
		$this->timeout = $a_timeout;
	}
	public function getTimeout()
	{
		return $this->timeout;
	}
	
	/** 
	 * Returns array of all bmf settings
	 * 
	 * @access	public
	 * @return	array $values Array of all bmf settings
	 */
	function getAll()
	{
		$values = array(
			"mandantNr" => $this->getClientId(),
			"bewirtschafterNr" => $this->getBewirtschafterNr(),
			"haushaltsstelle" => $this->getHaushaltsstelle(),
			"objektNr" => $this->getObjectId(),
			"kennzeichenMahnverfahren" => $this->getKennzeichenMahnverfahren(),
			"waehrungskennzeichen" => $this->getWaehrungsKennzeichen(),
			"ePaymentServer" => $this->getEpaymentServer(),			
			"clientCertificate" => $this->getClientCertificate(),
			"caCertificate" => $this->getCaCertificate(),
			"timeOut" => $this->getTimeOut()
		);	

		return $values;
	}

	/** 
	 * Clears the payment settings for the bmf payment method 
	 *
	 * @access	public
	 */
	public function clearAll()
	{

	 $this->pSettings->set('bmf', NULL, 'bmf');
//		$statement = $this->db->manipulateF('
//			UPDATE payment_settings
//			SET bmf = %s
//			WHERE settings_id = %s',
//			array('text', 'integer'),
//			array('NULL', $this->getSettingsId())
//		);
	}
	
	/** 
	 * Inserts or updates (if payment settings already exist) the bmf settings data
	 *
	 * @access	public
	 */
	public function save()
	{
	
		global $ilDB;
		
		$values = array(
			"mandantNr" => $this->getClientId(),
			"bewirtschafterNr" => $this->getBewirtschafterNr(),
			"haushaltsstelle" => $this->getHaushaltsstelle(),
			"objektNr" => $this->getObjectId(),
			"kennzeichenMahnverfahren" => $this->getKennzeichenMahnverfahren(),
			"waehrungskennzeichen" => $this->getWaehrungsKennzeichen(),
			"ePaymentServer" => $this->getEpaymentServer(),			
			"clientCertificate" => $this->getClientCertificate(),
			"caCertificate" => $this->getCaCertificate(),
			"timeOut" => $this->getTimeOut()
		);		


		$this->pSettings->set('bmf',serialize($values), 'bmf');

//		if ($this->getSettingsId())
//		{
//
//			$statement = $this->db->manipulateF('
//				UPDATE payment_settings
//				SET bmf = %s
//				WHERE settings_id = %s',
//				array('text', 'integer'),
// 				array(serialize($values), $this->getSettingsId())
//			);
//		}
//		else
//		{
//			$next_id = $ilDB->nextId('payment_settings');
//			$statement = $this->db->manipulateF('
//				INSERT into payment_settings
//				(	settings_id,
//					bmf)
//				VALUES (%s, %s)',
//				array('integer','text'),
//				array($next_id, serialize($values))
//			);
//
//			$this->setSettingsId($next_id);
//
//		}

	}	
}
?>