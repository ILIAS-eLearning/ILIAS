<?php
//
// +----------------------------------------------------------------------+
// | PHP Version 4                                                        |
// +----------------------------------------------------------------------+
// | Copyright (c) 1997-2002 The PHP Group                                |
// +----------------------------------------------------------------------+
// | This source file is subject to version 2.02 of the PHP license,      |
// | that is bundled with this package in the file LICENSE, and is        |
// | available at through the world-wide-web at                           |
// | http://www.php.net/license/2_02.txt.                                 |
// | If you did not receive a copy of the PHP license and are unable to   |
// | obtain it through the world-wide-web, please send a note to          |
// | license@php.net so we can mail you a copy immediately.               |
// +----------------------------------------------------------------------+
// | Authors: Shane Caraveo <Shane@Caraveo.com>   Port to PEAR and more   |
// | Authors: Dietrich Ayala <dietrich@ganx4.com> Original Author         |
// +----------------------------------------------------------------------+
//
// $Id$
//

/*
   SOAP_OBJECT_STRUCT makes pear::soap use objects for soap structures
   rather than arrays.  This has been done to provide a closer match to php-soap.
   If the old behaviour is needed, set to false.  The old behaviour is depricated.
*/
$GLOBALS['SOAP_OBJECT_STRUCT'] = TRUE;
/*
   SOAP_RAW_CONVERT makes pear::soap attempt to determine what SOAP type
   a php string COULD be.  This may result in slightly better interoperability when
   you are not using WSDL, and are being lazy and not using ilBMFValue to define
   types for your values.
*/
$GLOBALS['SOAP_RAW_CONVERT'] = FALSE;

require_once 'PEAR.php';
#require_once dirname(__FILE__).'/class.ilBMFFault.php';
require_once dirname(__FILE__).'/Type/class.ilBMFType_dateTime.php';
require_once dirname(__FILE__).'/Type/class.ilBMFType_hexBinary.php';

// optional features
$GLOBALS['SOAP_options'] = array();

@include_once 'Mail/mimePart.php';
@include_once 'Mail/mimeDecode.php';
if (class_exists('Mail_mimePart')) {
    $GLOBALS['SOAP_options']['Mime'] = 1;
    define('MAIL_MIMEPART_CRLF',"\n");
}

@include_once 'Net/DIME.php';
if (class_exists('Net_DIME_Message')) {
    $GLOBALS['SOAP_options']['DIME'] = 1;
}

#error_reporting(E_ALL);

/**
* Enable debugging informations?
*
* @const    SOAP_DEBUG
*/
$GLOBALS['SOAP_DEBUG']=false;

if (!function_exists('version_compare') ||
    version_compare(phpversion(), '4.1', '<')) {
    die("requires PHP 4.1 or higher\n");
}
if (version_compare(phpversion(), '4.1', '>=') &&
    version_compare(phpversion(), '4.2', '<')) {
    define('FLOAT', 'double');
} else {
    define('FLOAT', 'float');
}

# for float support
# is there a way to calculate INF for the platform?
define('INF',   1.8e307); 
define('NAN',   0.0);

define('SOAP_LIBRARY_VERSION', '0.7.1');
define('SOAP_LIBRARY_NAME', 'PEAR-SOAP 0.7.1');
// set schema version
define('SOAP_XML_SCHEMA_VERSION',   'http://www.w3.org/2001/XMLSchema');
define('SOAP_XML_SCHEMA_INSTANCE',  'http://www.w3.org/2001/XMLSchema-instance');
define('SOAP_XML_SCHEMA_1999',      'http://www.w3.org/1999/XMLSchema');
define('SOAP_SCHEMA',               'http://schemas.xmlsoap.org/wsdl/soap/');
define('SOAP_SCHEMA_ENCODING',      'http://schemas.xmlsoap.org/soap/encoding/');
define('SOAP_ENVELOP',              'http://schemas.xmlsoap.org/soap/envelope/');

define('SCHEMA_SOAP',               'http://schemas.xmlsoap.org/wsdl/soap/');
define('SCHEMA_HTTP',               'http://schemas.xmlsoap.org/wsdl/http/');
define('SCHEMA_MIME',               'http://schemas.xmlsoap.org/wsdl/mime/');
define('SCHEMA_WSDL',               'http://schemas.xmlsoap.org/wsdl/');
define('SCHEMA_DIME',               'http://schemas.xmlsoap.org/ws/2002/04/dime/wsdl/');
define('SCHEMA_CONTENT',            'http://schemas.xmlsoap.org/ws/2002/04/content-type/');
define('SCHEMA_REF',                'http://schemas.xmlsoap.org/ws/2002/04/reference/');

// define('SOAP_DEFAULT_ENCODING',  'UTF-8');
define('SOAP_DEFAULT_ENCODING',  'ISO-8859-1');

if (!function_exists('is_a'))
{
   function is_a(&$object, $class_name)
   {
      if (get_class($object) == $class_name) return TRUE;
      else return is_subclass_of($object, $class_name);
   }
}

/**
*  ilBMFBase
* Common base class of all Soap lclasses
*
* @access   public
* @version  $Id$
* @package  ilBMF::Client
* @author   Shane Caraveo <shane@php.net> Conversion to PEAR and updates
*/
class ilBMFBase extends PEAR
{
    var $_XMLSchema = array('http://www.w3.org/2001/XMLSchema', 'http://www.w3.org/1999/XMLSchema');
    var $_XMLSchemaVersion = 'http://www.w3.org/2001/XMLSchema';
    
    // load types into typemap array
    var $_typemap = array(
        'http://www.w3.org/2001/XMLSchema' => array(
            'string' => 'string',
            'boolean' => 'boolean',
            'float' => FLOAT,
            'double' => FLOAT,
            'decimal' => 'double',
            'duration' => 'integer',
            'dateTime' => 'string',
            'time' => 'string',
            'date' => 'string',
            'gYearMonth' => 'integer',
            'gYear' => 'integer',
            'gMonthDay' => 'integer',
            'gDay' => 'integer',
            'gMonth' => 'integer',
            'hexBinary' => 'string',
            'base64Binary' => 'string',
            // derived datatypes
            'normalizedString' => 'string',
            'token' => 'string',
            'language' => 'string',
            'NMTOKEN' => 'string',
            'NMTOKENS' => 'string',
            'Name' => 'string',
            'NCName' => 'string',
            'ID' => 'string',
            'IDREF' => 'string',
            'IDREFS' => 'string',
            'ENTITY' => 'string',
            'ENTITIES' => 'string',
            'integer' => 'integer',
            'nonPositiveInteger' => 'integer',
            'negativeInteger' => 'integer',
            'long' => 'integer',
            'int' => 'integer',
            'short' => 'integer',
            'byte' => 'string',
            'nonNegativeInteger' => 'integer',
            'unsignedLong' => 'integer',
            'unsignedInt' => 'integer',
            'unsignedShort' => 'integer',
            'unsignedByte' => 'integer',
            'positiveInteger'  => 'integer',
            'anyType' => 'string',
            'anyURI' => 'string',
            'QName' => 'string'
        ),
        'http://www.w3.org/1999/XMLSchema' => array(
            'i4' => 'integer',
            'int' => 'integer',
            'boolean' => 'boolean',
            'string' => 'string',
            'double' => FLOAT,
            'float' => FLOAT,
            'dateTime' => 'string',
            'timeInstant' => 'string',
            'base64Binary' => 'string',
            'base64' => 'string',
            'ur-type' => 'string'
        ),
        'http://schemas.xmlsoap.org/soap/encoding/' => array('base64' => 'string','array' => 'array','Array' => 'array', 'Struct'=>'array')
    );

    // load namespace uris into an array of uri => prefix
    var $_namespaces;
    var $_ns_count = 0;
    # supported encodings, limited by XML extension
    var $_encodings = array('ISO-8859-1','US-ASCII','UTF-8');

    var $_xmlEntities = array ( '&' => '&amp;', '<' => '&lt;', '>' => '&gt;', "'" => '&apos;', '"' => '&quot;' );
    
    var $_doconversion = FALSE;
    
    var $__attachments = array();
    
    /**
    * Store debugging information in $debug_data?
    * 
    * @var  boolean if true debugging informations will be store in $debug_data
    * @see  $debug_data, ilBMFBase
    */
    var $_debug_flag = false;
    
    /**
    * String containing debugging informations if $debug_flag is set to true
    *
    * @var      string  debugging informations - mostyl error messages
    * @see      $debug_flag, ilBMFBase
    * @access   public
    */
    var $_debug_data = '';
    
    /**
    * Fault code
    * 
    * @var  string
    */
    var $_myfaultcode = '';
    
    /**
    * Recent PEAR error object
    * 
    * @var  object  PEAR Error
    */
    var $fault = NULL;
    
    var $_wsdl = NULL;
    
    /**
    * section5
    *
    * @var  boolean  defines if we use section 5 encoding, or false if this is literal
    */
    var $_section5 = TRUE;

    // handle type to class mapping 
    var $_auto_translation = false;
    var $_type_translation = array();
    
    
    /**
    * Constructor
    *
    * @param    string  error code 
    * @see  $debug_data, _debug()
    */
    function ilBMFBase($faultcode = 'Client')
    {
        $this->_myfaultcode = $faultcode;
        $this->_resetNamespaces();
        $this->_debug_flag = $GLOBALS['SOAP_DEBUG'];
        parent::PEAR('SOAPfault');
    }
    
    function _resetNamespaces()
    {
        $this->_namespaces = array(
            'http://schemas.xmlsoap.org/soap/envelope/' => 'SOAP-ENV',
            'http://www.w3.org/2001/XMLSchema' => 'xsd',
            'http://www.w3.org/2001/XMLSchema-instance' => 'xsi',
            'http://schemas.xmlsoap.org/soap/encoding/' => 'SOAP-ENC');
    }

    /**
    * _setSchemaVersion
    *
    * sets the schema version used in the soap message
    *
    * @param string (see globals.php)
    *
    * @access private
    */
    function _setSchemaVersion($schemaVersion)
    {
        if (!in_array($schemaVersion, $this->_XMLSchema)) {
            return $this->_raiseSoapFault("unsuported XMLSchema $schemaVersion");
        }
        $this->_XMLSchemaVersion = $schemaVersion;
        $tmpNS = array_flip($this->_namespaces);
        $tmpNS['xsd'] = $this->_XMLSchemaVersion;
        $tmpNS['xsi'] = $this->_XMLSchemaVersion.'-instance';
        $this->_namespaces = array_flip($tmpNS);
    }
    
    /**
    * Raise a soap error
    * 
    * Please referr to the SOAP definition for an impression of what a certain parameter
    * stands for.
    *
    * Use $debug_flag to store errors to the member variable $debug_data
    * 
    * @param    string  error message
    * @param    string  detailed error message.
    * @param    string  actor
    * @param    mixed
    * @param    mixed
    * @param    mixed
    * @param    boolean
    * @see      $debug_flag, $debug_data
    */
    function &_raiseSoapFault($str, $detail = '', $actorURI = '', $code = null, $mode = null, $options = null, $skipmsg = false)
    {
        # pass through previous faults
        if (is_object($str)) {
            $this->fault = $str;
        } else {
            if (!$code) $code = $this->_myfaultcode;
            $this->fault = new ilBMFFault($str, 
                                          $code, 
                                          $actorURI,
                                          $detail,
                                          $mode,
                                          $options);
        }
        return $this->fault;
    }

    function __isfault()
    {
        return $this->fault != NULL;
    }
    
    function &__getfault()
    {
        return $this->fault;
    }
    
    /**
    * maintains a string of debug data
    *
    * @param    debugging message - sometimes an error message
    */
    function _debug($string)
    {
        if ($this->_debug_flag) {
            $this->_debug_data .= get_class($this) . ': ' . preg_replace("/>/", ">\r\n", $string) . "\n";
        }
    }
    
    function _getNamespacePrefix($ns)
    {
        if (array_key_exists($ns,$this->_namespaces)) {
            return $this->_namespaces[$ns];
        }
        $prefix = 'ns'.count($this->_namespaces);
        $this->_namespaces[$ns] = $prefix;
        return $prefix;
        return NULL;
    }

    function _getNamespaceForPrefix($prefix)
    {
        $flipped = array_flip($this->_namespaces);
        if (array_key_exists($prefix,$flipped)) {
            return $flipped[$prefix];
        }
        return NULL;
    }
        
    function _isSoapValue(&$value)
    {
        return is_object($value) &&
                (get_class($value) == 'ilbmfvalue' ||
                is_subclass_of($value,'ilbmfvalue'));
    }
   
    function _serializeValue(&$value, $name = '', $type = false, $elNamespace = NULL, $typeNamespace=NULL, $options=array(), $attributes = array(), $artype='', $OBJTypeNS = array())
    {
        $namespaces = array();
        $arrayType = $xmlout_value = NULL;
        $typePrefix = $elPrefix = $xmlout_offset = $xmlout_arrayType = $xmlout_type = $xmlns = '';
        $ptype = $array_type_ns = '';

        if (!$name || is_numeric($name))
        {
            $name = 'item';
        }

        if ($this->_wsdl)
            list($ptype,$arrayType,$array_type_ns) = $this->_wsdl->getSchemaType($type, $name, $typeNamespace);

        if (!$arrayType) $arrayType = $artype;
        if (!$ptype) $ptype = $this->_getType($value);
        if (!$type) $type = $ptype;

        if (strcasecmp($ptype,'Struct')==0 || strcasecmp($type,'Struct')==0)
        {
            // struct
            $vars = NULL;
            if (is_object($value))
            {
                $vars = get_object_vars($value);
            }
            else
            {
                $vars = &$value;
            }
            if (is_array($vars))
            {
                foreach (array_keys($vars) as $k)
                {
                    if ($k[0]=='_')
                       continue; // hide private vars
                    if (is_object($vars[$k]))
                    {
                        if (is_a($vars[$k],'ilbmfvalue'))
                        {
                            $xmlout_value .= $vars[$k]->serialize($this);
                        }
                        else
                        {
                            // XXX get the members and serialize them instead
                            // converting to an array is more overhead than we
                            // should realy do, but php-soap is on it's way.
                            $objarr = get_object_vars ( $vars[$k] );
                            if (!isset ($objarr['OBJTypeNS']) )
                            {
                                $xmlout_value .= $this->_serializeValue(get_object_vars($vars[$k]), $k, false, $this->_section5?NULL:$elNamespace, NULL, NULL, NULL, NULL);
                            }			
                            else
                            {
                                $xmlout_value .= $this->_serializeValue(get_object_vars($vars[$k]), $k, false, $this->_section5?NULL:$elNamespace, NULL, NULL, NULL, NULL, $objarr['OBJTypeNS']);			
                            }
                        }
                    }
                    else
                    {
                        if ( $k != 'OBJTypeNS' )
                        {		
                            $xmlout_value .= $this->_serializeValue($vars[$k],$k, false, $this->_section5?NULL:$elNamespace);
                        }
                    }
                }
            }
        }
        else
        {
            if (strcasecmp($ptype,'Array')==0 || strcasecmp($type,'Array')==0)
            {
                // array
                $typeNamespace = SOAP_SCHEMA_ENCODING;
                $orig_type = $type;
                $type = 'Array';
                $numtypes = 0;
                // XXX this will be slow on larger array's.  Basicly, it flattens array's to allow us
                // to serialize multi-dimensional array's.  We only do this if arrayType is set,
                // which will typicaly only happen if we are using WSDL
                if (isset($options['flatten']) || ($arrayType && (strchr($arrayType,',') || strstr($arrayType,']['))))
                {
                    $numtypes = $this->_multiArrayType($value, $arrayType, $ar_size, $xmlout_value);
                }

                $array_type = $array_type_prefix = '';
                if ($numtypes != 1)
                {
                    $arrayTypeQName = new QName($arrayType);
                    $arrayType = $arrayTypeQName->name;
                    $array_types = array();
                    $array_val = NULL;

                    // serialize each array element
                    $ar_size = count($value);
                    for ($i=0; $i < $ar_size; $i++)
                    {
                        $array_val =& $value[$i];
                        if ($this->_isSoapValue($array_val))
                        {
                            $array_type = $array_val->type;
                            $array_types[$array_type] = 1;
                            $array_type_ns = $array_val->type_namespace;
                            $xmlout_value .= $array_val->serialize($this);
                        }
                        else
                        {
                            $array_type = $this->_getType($array_val);
                            $array_types[$array_type] = 1;
                            $objarr = get_object_vars ( $array_val );
                            if (isset ($objarr['OBJTypeNS']) )
                            {
                                $tmp_arr['item'] = $objarr['OBJTypeNS']['item'];
                                $xmlout_value .= $this->_serializeValue($array_val,'item', $array_type, $this->_section5?NULL:$elNamespace, NULL, NULL, NULL, NULL, $tmp_arr );
                            }
                            else
                            {
                                $xmlout_value .= $this->_serializeValue($array_val,'item', $array_type, $this->_section5?NULL:$elNamespace);
                            }
                        }
                    }
                    
                    $xmlout_offset = " SOAP-ENC:offset=\"[0]\"";
                    if (!$arrayType)
                    {
                        $numtypes = count($array_types);
                        if ($numtypes == 1)
                           $arrayType = $array_type;
                        // using anyType is more interoperable
                        if ($array_type == 'Struct')
                        {
                            $array_type = '';
                        }
                        else
                            if ($array_type == 'Array')
                            {
                                $arrayType = 'anyType';
                                $array_type_prefix = 'xsd';
                            }
                            else
                                if (!$arrayType)
                                   $arrayType = $array_type;
                    }
                }
                if (!isset($arrayType) || $numtypes > 1)
                {
                    $arrayType = 'xsd:anyType'; // should reference what schema we're using
                }
                else
                {
                    if ($array_type_ns)
                    {
                        $array_type_prefix = $this->_getNamespacePrefix($array_type_ns);
                    }
                    else
                        if (array_key_exists($arrayType, $this->_typemap[$this->_XMLSchemaVersion]))
                        {
                            $array_type_prefix = $this->_namespaces[$this->_XMLSchemaVersion];
                        }
                    if ($array_type_prefix)
                        $arrayType = $array_type_prefix.':'.$arrayType;
                }

                if ( isset ($value[0]->OBJTypeNS) )
                {
                    $xmlout_arrayType = ' xmlns:' . $value[0]->OBJTypeNS['nsPrefix'] . '="' . $value[0]->OBJTypeNS['namespace'] . '" xsi:type="' . $value[0]->OBJTypeNS['nsPrefix'] . ':Array" ' . $value[0]->OBJTypeNS['nsPrefix'] . ':arrayType="' . $value[0]->OBJTypeNS['pnsPrefix'] . ':' . get_class($value[0]) . '[' . count($value) . ']"';
                    $xmlout_type = $xmlns = $xmlout_offset = $xml_attr = $arrayType = '';
                }
                else
                {
                    $xmlout_arrayType = " SOAP-ENC:arrayType=\"".$arrayType."[$ar_size]\"";
                }
            }
            else
            {
                if ($this->_isSoapValue($value))
                {
                    $xmlout_value = $value->serialize($this);
                }
                else
                {
                    if ($type == 'string')
                    {
                        $xmlout_value = htmlspecialchars($value);
                    }
                    else
                    {
                        if ($type == 'boolean')
                        {
                            $xmlout_value = $value?'true':'false';
                        }
                        else
                        {
                            $xmlout_value = $value;
                        }
                    }
                }
            }
        }

        // add namespaces
        if ($elNamespace)
        {
            $elPrefix = $this->_getNamespacePrefix($elNamespace);
            $xmlout_name = "$elPrefix:$name";
            $xmlns = " xmlns:$elPrefix=\"$elNamespace\"";
        }
        else
        {
            $xmlout_name = $name;
        }
        
        if ($typeNamespace)
        {
            $typePrefix = $this->_getNamespacePrefix($typeNamespace);
            $xmlout_type = "$typePrefix:$type";
        }
        else
            if ($type && array_key_exists($type, $this->_typemap[$this->_XMLSchemaVersion]))
            {
                $typePrefix = $this->_namespaces[$this->_XMLSchemaVersion];
                $xmlout_type = "$typePrefix:$type";
            }

        // handle additional attributes
        $xml_attr = '';
        if (count($attributes) > 0)
        {
            foreach ($attributes as $k => $v)
            {
                $kqn = new QName($k);
                $vqn = new QName($v);
                $xml_attr .= ' '.$kqn->fqn().'="'.$vqn->fqn().'"';
            }
        }

        // store the attachement for mime encoding
        if (isset($options['attachment']))
            $this->__attachments[] = $options['attachment'];
            
        if ($this->_section5)
        {
            if ($name == 'item' && isset($OBJTypeNS['item']))
            {
                $xmlout_type = $OBJTypeNS['item'];
            }
            if ($xmlout_type)
               $xmlout_type = " xsi:type=\"$xmlout_type\"";

            if (is_null($xmlout_value))
            {
                $xml = "\r\n<$xmlout_name$xmlout_type$xmlns$xmlout_arrayType$xml_attr/>";
            }
            else
            {
                if ( isset ($value[0]->OBJTypeNS) )
                {
                    $xmlout_type = '';
                }
                $xml = "\r\n<$xmlout_name$xmlout_type$xmlns$xmlout_arrayType$xmlout_offset$xml_attr ";
                if ( isset($OBJTypeNS['namespace']) && isset ($OBJTypeNS['type']) )
                {
                    if ( trim($OBJTypeNS['namespace']) != '' || trim($OBJTypeNS['type']) != '' )
                    {
                        if ( isset($OBJTypeNS['namespace']) && $OBJTypeNS['namespace'] != '')
                        {
                           $xml .= ' xmlns:';
                           if ( isset($OBJTypeNS['nsPrefix']))
                              $xml .= $OBJTypeNS['nsPrefix'];
                           else
                               $xml .= 'intf';
                           $xml .= '="' . $OBJTypeNS['namespace'] . '"';
                        }
                        if (isset ($OBJTypeNS['type'] ) && $OBJTypeNS['type'] != '')
                        {
                           $xml .= ' xsi:type="';
                           if ( strpos($OBJTypeNS['type'], "xsd:") === false )
                           {
                               if ( isset($OBJTypeNS['nsPrefix']))
                                  $xml .= $OBJTypeNS['nsPrefix'] . ':';
                               else
                                   $xml .= 'intf:';
                           }
                           $xml .= $OBJTypeNS['type'] . '"';
                        }
                    }
                }
                $xml .= ">" . $xmlout_value . "</$xmlout_name>";
            }
        }
        else
        {
            if (is_null($xmlout_value))
            {
                $xml = "\r\n<$xmlout_name$xmlns$xml_attr/>";
            }
            else
            {
                $xml = "\r\n<$xmlout_name$xmlns$xml_attr>" . $xmlout_value . "</$xmlout_name>";
            }
        }

        return $xml;
    }
    
    
    /**
    * ilBMF::Value::_getType
    *
    * convert php type to soap type
    * @param    string  value
    *
    * @return   string  type  - soap type
    * @access   private
    */
    function _getType(&$value) {
        global $SOAP_OBJECT_STRUCT,$SOAP_RAW_CONVERT;
        $type = gettype($value);
        switch ($type) {
        case 'object':
            if (is_a($value,'ilbmfvalue')) {
                $type = $value->type;
            } else {
                $type = 'Struct';
            }
            break;
        case 'array':
            // XXX hashes always get done as structs by pear::soap
            if ($this->_isHash($value)) {
                $type = 'Struct';
            } else {
                $ar_size = count($value);
                if ($ar_size > 0 && is_a($value[0],'ilbmfvalue')) {
                    // fixme for non-wsdl structs that are all teh same type
                    if ($ar_size > 1 &&
                        $this->_isSoapValue($value[0]) &&
                        $this->_isSoapValue($value[1]) &&
                        $value[0]->name != $value[1]->name) {
                        // this is a struct, not an array
                        $type = 'Struct';
                    } else {
                        $type = 'Array';
                    }
                } else {
                    $type = 'Array';
                }
            }
            break;
        case 'integer':
        case 'long':
            $type = 'int';
            break;
        case 'boolean':
            #$value = $value?'true':'false';
            break;
        case 'double':
            $type = 'decimal'; // double is deprecated in 4.2 and later
            break;
        case 'NULL':
            $type = '';
            break;
        case 'string':
            if (!$SOAP_RAW_CONVERT)
            {
                if (is_numeric($value))
                {
/*                     if (strstr($value,'.'))
                       $type = 'float';
                    else
                        $type = 'int'; */
                }
                else
                {
/*                     if (ilBMFType_hexBinary::is_hexbin($value))
                    {
                        $type = 'hexBinary';
                    }
                    else */
                    {
                        if ($this->_isBase64($value))
                        {
                            $type = 'base64Binary';
                        }
                        else
                        {
                            $dt = new ilBMFType_dateTime($value);
                            if ($dt->toUnixtime() != -1)
                            {
                                $type = 'dateTime';
                                #$value = $dt->toSOAP();
                            }
                        }
                    }
                }
            }
        default:
            break;
        }
        return $type;
    }

    function _multiArrayType(&$value, &$type, &$size, &$xml)
    {
        $sz = count($value);
        if ($sz > 1) {
            // seems we have a multi dimensional array, figure it out if we do
            $c = count($value);
            for ($i=0; $i<$c; $i++) {
                $this->_multiArrayType($value[$i], $type, $size, $xml);
            }
            
            if ($size) {
                $size = $sz.','.$size;
            } else {
                $size = $sz;
            }
            return 1;
        } else {
            if (is_object($value)) {
                $type = $value->type;
                $xml .= $value->serialize($this); 
            } else {
                $type = $this->_getType($value);
                $xml .= $this->_serializeValue($value,'item',$type);
            }
        }
        $size = NULL;
        return 1;
    }
    // support functions
    /**
    *
    * @param    string
    * @return   string
    */
    function _isBase64(&$value)
    {
        $l = strlen($value);
        if ($l > 0)
            return $value[$l-1] == '=' && preg_match("/[A-Za-z=\/\+]+/",$value);
        return FALSE;
    }

    /**
    *
    * @param    mixed
    * @return   boolean
    */
    function _isHash(&$a) {
        # XXX I realy dislike having to loop through this in php code,
        # realy large arrays will be slow.  We need a C function to do this.
        $names = array();
        $it = 0;
        foreach ($a as $k => $v) {
            # checking the type is faster than regexp.
            $t = gettype($k);
            if ($t != 'integer') {
                return TRUE;
            } else if ($this->_isSoapValue($v)) {
                $names[$v->name] = 1;
            }
            // if someone has a large hash they should realy be defining the type
            if ($it++ > 10) return FALSE;
        }
        return count($names)>1;
    }
    
    function &_un_htmlentities($string)
    {
       $trans_tbl = get_html_translation_table (HTML_ENTITIES);
       $trans_tbl = array_flip($trans_tbl);
       return strtr($string, $trans_tbl);
    }
    
    /**
    *
    * @param    mixed
    */
    function &_decode(&$soapval)
    {
        global $SOAP_OBJECT_STRUCT;
        
        if (!$this->_isSoapValue($soapval)) {
            return $soapval;
        } else if (is_array($soapval->value)) {
            if ($SOAP_OBJECT_STRUCT && $soapval->type != 'Array') {
                $classname = 'stdClass';
                if (isset($this->_type_translation[$soapval->tqn->fqn()])) {
                    // this will force an error in php if the
                    // class does not exist
                    $classname = $this->_type_translation[$soapval->tqn->fqn()];
                } else if (isset($this->_type_translation[$soapval->type])) {
                    // this will force an error in php if the
                    // class does not exist
                    $classname = $this->_type_translation[$soapval->type];
                } else if ($this->_auto_translation) {
                    if (class_exists($soapval->type)) {
                        $classname = $soapval->type;
                    } else if ($this->_wsdl) {
                        $t = $this->_wsdl->getComplexTypeNameForElement($soapval->name, $soapval->namespace);
                        if ($t && class_exists($t)) $classname = $t;
                    }
                }
                $return = new $classname;
            } else {
                $return = array();
            }
            
            $counter = 1;
            $isstruct = !$SOAP_OBJECT_STRUCT || !is_array($return);
            foreach ($soapval->value as $item) {
                if (is_object($return)) {
                    if ($this->_wsdl) {
                        // get this childs wsdl information
                        // /$soapval->ns/$soapval->type/$item->ns/$item->name
                        $child_type = $this->_wsdl->getComplexTypeChildType(
                                                $soapval->namespace,
                                                $soapval->name,
                                                $item->namespace,
                                                $item->name);
                        if ($child_type) $item->type = $child_type;
                    }
                    if (!$isstruct || $item->type == 'Array') {
                        if (isset($return->{$item->name}) &&
                          is_object($return->{$item->name})) {
                            $return->{$item->name} = $this->_decode($item);
                        } else if (isset($return->{$item->name}) &&
                          is_array($return->{$item->name})) {
                            $return->{$item->name}[] = $this->_decode($item);
                        } else if (is_array($return)) {
                            $return[] = $this->_decode($item);
                        } else {
                            $return->{$item->name} = $this->_decode($item);
                        }
                    } else if (isset($return->{$item->name})) {
                        $isstruct = FALSE;
                        if (count(get_object_vars($return)) == 1) {
                            $return = array($return->{$item->name}, $this->_decode($item));
                        } else {
                            $return->{$item->name} = array($return->{$item->name}, $this->_decode($item));
                        }
                    } else {
                        $return->{$item->name} = $this->_decode($item);
                    }
                    /* set the attributes as members in the class */
                    if (method_exists($return,'__set_attribute')) {
                        foreach ($soapval->attributes as $key=>$value) {
                            call_user_func_array(array(&$return,'__set_attribute'),array($key,$value));
                        }
                    }
                } else {
                    if ($soapval->arrayType && $this->_isSoapValue($item)) {
                        $item->type = $soapval->arrayType;
                    }
                    if (!$isstruct) {
                        $return[] = $this->_decode($item);
                    } else if (isset($return[$item->name])) {
                        $isstruct = FALSE;
                        $return = array($return[$item->name], $this->_decode($item));
                    } else {
                        $return[$item->name] = $this->_decode($item);
                    }
                }
            }
            return $return;
        }
        
        if ($soapval->type == 'boolean') {
            if ($soapval->value != '0' && strcasecmp($soapval->value,'false') !=0) {
                $soapval->value = TRUE;
            } else {
                $soapval->value = FALSE;
            }
        } else if ($soapval->type && array_key_exists($soapval->type, $this->_typemap[SOAP_XML_SCHEMA_VERSION])) {
            # if we can, lets set php's variable type
            settype($soapval->value, $this->_typemap[SOAP_XML_SCHEMA_VERSION][$soapval->type]);
        }
        return $soapval->value;
    }
    
    /**
     * creates the soap envelope with the soap envelop data
     *
     * @param string $payload       soap data (in xml)
     * @return associative array (headers,body)
     * @access private
     */
    function &_makeEnvelope(&$method, &$headers, $encoding = SOAP_DEFAULT_ENCODING,$options = array())
    {
        $smsg = $header_xml = $ns_string = '';

        if ($headers) {
            $c = count($headers);
            for ($i=0; $i < $c; $i++) {
                $header_xml .= $headers[$i]->serialize($this);
            }
            $header_xml = "<SOAP-ENV:Header>\r\n$header_xml\r\n</SOAP-ENV:Header>\r\n";
        }
        if (!isset($options['input']) || $options['input'] == 'parse') {
            if (is_array($method)) {
                $c = count($method);
                for ($i = 0; $i < $c; $i++) {
                    $smsg .= $method[$i]->serialize($this);
                }
            }  else {
                $smsg = $method->serialize($this);
            }
        } else {
            $smsg = $method;
        }
        $body = "<SOAP-ENV:Body>\r\n".$smsg."\r\n</SOAP-ENV:Body>\r\n";
        $a = 0;
        foreach ($this->_namespaces as $k => $v)
        {
            if ($a > 3) break;
            $ns_string .= " xmlns:$v=\"$k\"\r\n";
            $a ++;
        }
        
        /* if use='literal', we do not put in the encodingStyle.  This is denoted by
           $this->_section5 being false.
           XXX use can be defined at a more granular level than we are dealing with
           here, so this does not work for all services.
        */
        $xml = "<?phpxml version=\"1.0\" encoding=\"$encoding\"?>\r\n\r\n".
            "<SOAP-ENV:Envelope $ns_string".
            ($this->_section5?" SOAP-ENV:encodingStyle=\"" . SOAP_SCHEMA_ENCODING . "\"":'').
            ">\r\n".
            "$header_xml$body</SOAP-ENV:Envelope>\r\n";
        
        return $xml;
    }
    
    function _makeMimeMessage(&$xml, $encoding = SOAP_DEFAULT_ENCODING)
    {
        global $SOAP_options;
        
        if (!isset($SOAP_options['Mime'])) {
            return $this->_raiseSoapFault('Mime is not installed');
        }
        
        // encode any attachments
        // see http://www.w3.org/TR/SOAP-attachments
        // now we have to mime encode the message
        $params = array('content_type' => 'multipart/related; type=text/xml');
        $msg = new Mail_mimePart('', $params);
        // add the xml part
        $params['content_type'] = 'text/xml';
        $params['charset'] = $encoding;
        $params['encoding'] = 'base64';
        $msg->addSubPart($xml, $params);
        
        // add the attachements
        $c = count($this->__attachments);
        for ($i=0; $i < $c; $i++) {
            $attachment =& $this->__attachments[$i];
            $msg->addSubPart($attachment['body'],$attachment);
        }
        return $msg->encode();
    }
    
    // XXX this needs to be used from the Transport system
    function _makeDIMEMessage(&$xml)
    {
        global $SOAP_options;
        
        if (!isset($SOAP_options['DIME'])) {
            return $this->_raiseSoapFault('DIME is not installed');
        }
        
        // encode any attachments
        // see http://search.ietf.org/internet-drafts/draft-nielsen-dime-soap-00.txt
        // now we have to DIME encode the message
        $dime = new Net_DIME_Message();
        $msg = $dime->encodeData($xml,SOAP_ENVELOP,NULL,NET_DIME_TYPE_URI);
        
        // add the attachements
        $c = count($this->__attachments);
        for ($i=0; $i < $c; $i++) {
            $attachment =& $this->__attachments[$i];
            $msg .= $dime->encodeData($attachment['body'],$attachment['content_type'],$attachment['cid'],NET_DIME_TYPE_MEDIA);
        }
        $msg .= $dime->endMessage();
        return $msg;
    }

    function _decodeMimeMessage(&$data, &$headers, &$attachments)
    {
        global $SOAP_options;
        if (!isset($SOAP_options['Mime'])) {
            $this->_raiseSoapFault('Mime Unsupported, install PEAR::Mail::Mime','','','Server');
            return;
        }
        
        $params['include_bodies'] = TRUE;
        $params['decode_bodies']  = TRUE;
        $params['decode_headers'] = TRUE;

        // XXX lame thing to have to do for decoding
        $decoder = new Mail_mimeDecode($data);
        $structure = $decoder->decode($params);
        
        if (isset($structure->body)) {
            $data = $structure->body;
            $headers = $structure->headers;
            return;
        } else if (isset($structure->parts)) {
            $data = $structure->parts[0]->body;
            $headers = array_merge($structure->headers,$structure->parts[0]->headers);
            if (count($structure->parts) > 1) {
                $mime_parts = array_splice($structure->parts,1);
                // prepare the parts for the soap parser
                
                $c = count($mime_parts);
                for ($i = 0; $i < $c; $i++) {
                    $p =& $mime_parts[$i];
                    if (isset($p->headers['content-location'])) {
                        // XXX TODO: modify location per SwA note section 3
                        // http://www.w3.org/TR/SOAP-attachments
                        $attachments[$p->headers['content-location']] = $p->body;
                    } else {
                        $cid = 'cid:'.substr($p->headers['content-id'],1,strlen($p->headers['content-id'])-2);
                        $attachments[$cid] = $p->body;
                    }
                }
            }
            return;
        }
        $this->_raiseSoapFault('Mime parsing error','','','Server');
    }
    
    function _decodeDIMEMessage(&$data, &$headers, &$attachments)
    {
        global $SOAP_options;
        if (!isset($SOAP_options['DIME'])) {
            $this->_raiseSoapFault('DIME Unsupported, install PEAR::Net::DIME','','','Server');
        }
        
        // XXX this SHOULD be moved to the transport layer, e.g. PHP  itself
        // should handle parsing DIME ;)
        $dime = new Net_DIME_Message();
        $dime->decodeData($data);
        if (strcasecmp($dime->parts[0]['type'],SOAP_ENVELOP) !=0 ||
            strcasecmp($dime->parts[0]['type'],SOAP_ENVELOP) !=0) {
            $this->_raiseSoapFault('Dime record 1 is not a SOAP envelop!','','','Server');
        } else {
            $data = $dime->parts[0]['data'];
            $headers['content-type'] = 'text/xml'; // fake it for now
            $c = count($dime->parts);
            for ($i = 0; $i < $c; $i++) {
                $part =& $dime->parts[$i];
                // XXX we need to handle URI's better
                $attachments['cid:'.$part['id']] = $part['data'];
            }
        }
    }
    
    function __set_type_translation($type, $class=NULL)
    {
        $tq = new QName($type);
        if (!$class) {
            $class = $tq->name;
        }
        $this->_type_translation[$type]=$class;
    }
}

/**
*  QName
* class used to handle QNAME values in XML
*
* @access   public
* @version  $Id$
* @package  ilBMF::Client
* @author   Shane Caraveo <shane@php.net> Conversion to PEAR and updates
*/
class QName
{
    var $name = '';
    var $ns = '';
    var $namespace='';
    #var $arrayInfo = '';
    
    function QName($name, $namespace = '')
    {
        if ($name && $name[0] == '{' )
        {
            preg_match('/\{(.*?)\}(.*)/',$name, $m);
            $this->name = $m[2];
            $this->namespace = $m[1];
        }
        else
        {
            if (strpos($name, ':') != FALSE)
            {
                $s = split(':',$name);
                $s = array_reverse($s);
                $this->name = $s[0];
                $this->ns = $s[1];
                $this->namespace = $namespace;
            }
            else
            {
                $this->name = $name;
                $this->namespace = $namespace;
            }
        }
        
        # a little more magic than should be in a qname
        $p = strpos($this->name, '[');
        if ($p)
        {
            # XXX need to re-examine this logic later
            # chop off []
            $this->arraySize = split(',',substr($this->name,$p+1, strlen($this->name)-$p-2));
            $this->arrayInfo = substr($this->name, $p);
            $this->name = substr($this->name, 0, $p);
        }
    }
    
    function fqn()
    {
        if ($this->namespace)
        {
            return '{'.$this->namespace.'}'.$this->name;
        }
        else
        {
            if ($this->ns)
            {
                return $this->ns.':'.$this->name;
            }
        }
        return $this->name;
    }
    
}
?>
