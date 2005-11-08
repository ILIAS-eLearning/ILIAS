<?php



/**
* for creating serializable abstractions of native PHP types
* NOTE: this is only really used when WSDL is not available.
*
* @author   Dietrich Ayala <dietrich@ganx4.com>
* @version  $Id$
* @access   public
*/
class soapval extends nusoap_base {
	/**
	* constructor
	*
	* @param    string $name optional name
	* @param    string $type optional type name
	* @param	mixed $value optional value
	* @param	string $namespace optional namespace of value
	* @param	string $type_namespace optional namespace of type
	* @param	array $attributes associative array of attributes to add to element serialization
	* @access   public
	*/
  	function soapval($name='soapval',$type=false,$value=-1,$element_ns=false,$type_ns=false,$attributes=false) {
		$this->name = $name;
		$this->value = $value;
		$this->type = $type;
		$this->element_ns = $element_ns;
		$this->type_ns = $type_ns;
		$this->attributes = $attributes;
    }

	/**
	* return serialized value
	*
	* @return	string XML data
	* @access   private
	*/
	function serialize($use='encoded') {
		return $this->serialize_val($this->value,$this->name,$this->type,$this->element_ns,$this->type_ns,$this->attributes,$use);
    }

	/**
	* decodes a soapval object into a PHP native type
	*
	* @param	object $soapval optional SOAPx4 soapval object, else uses self
	* @return	mixed
	* @access   public
	*/
	function decode(){
		return $this->value;
	}
}



?>