<?php




/**
* soap_fault class, allows for creation of faults
* mainly used for returning faults from deployed functions
* in a server instance.
* @author   Dietrich Ayala <dietrich@ganx4.com>
* @version  $Id$
* @access public
*/
class soap_fault extends nusoap_base {

	var $faultcode;
	var $faultactor;
	var $faultstring;
	var $faultdetail;

	/**
	* constructor
    *
    * @param string $faultcode (client | server)
    * @param string $faultactor only used when msg routed between multiple actors
    * @param string $faultstring human readable error message
    * @param string $faultdetail
	*/
	function soap_fault($faultcode,$faultactor='',$faultstring='',$faultdetail=''){
		$this->faultcode = $faultcode;
		$this->faultactor = $faultactor;
		$this->faultstring = $faultstring;
		$this->faultdetail = $faultdetail;
	}

	/**
	* serialize a fault
	*
	* @access   public
	*/
	function serialize(){
		$ns_string = '';
		foreach($this->namespaces as $k => $v){
			$ns_string .= "\n  xmlns:$k=\"$v\"";
		}
		$return_msg =
			'<?xml version="1.0" encoding="'.$this->soap_defencoding.'"?>'.
			'<SOAP-ENV:Envelope SOAP-ENV:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/"'.$ns_string.">\n".
				'<SOAP-ENV:Body>'.
				'<SOAP-ENV:Fault>'.
					'<faultcode>'.$this->expandEntities($this->faultcode).'</faultcode>'.
					'<faultactor>'.$this->expandEntities($this->faultactor).'</faultactor>'.
					'<faultstring>'.$this->expandEntities($this->faultstring).'</faultstring>'.
					'<detail>'.$this->serialize_val($this->faultdetail).'</detail>'.
				'</SOAP-ENV:Fault>'.
				'</SOAP-ENV:Body>'.
			'</SOAP-ENV:Envelope>';
		return $return_msg;
	}
}




?>