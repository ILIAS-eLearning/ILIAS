<?php
//
// +----------------------------------------------------------------------+
// | PHP Version 4                                                        |
// +----------------------------------------------------------------------+
// | Copyright (c) 1997-2003 The PHP Group                                |
// +----------------------------------------------------------------------+
// | This source file is subject to version 2.02 of the PHP license,      |
// | that is bundled with this package in the file LICENSE, and is        |
// | available at through the world-wide-web at                           |
// | http://www.php.net/license/2_02.txt.                                 |
// | If you did not receive a copy of the PHP license and are unable to   |
// | obtain it through the world-wide-web, please send a note to          |
// | license@php.net so we can mail you a copy immediately.               |
// +----------------------------------------------------------------------+
// | Authors: Hartmut Holzgraefe <hholzgra@php.net>                       |
// |          Christian Stocker <chregu@bitflux.ch>                       |
// +----------------------------------------------------------------------+
//
// $Id: Server.php,v 1.28 2005/04/05 22:51:09 hholzgra Exp $
//

require_once "Services/WebDAV/classes/Tools/_parse_propfind.php";
require_once "Services/WebDAV/classes/Tools/_parse_proppatch.php";
require_once "Services/WebDAV/classes/Tools/_parse_lockinfo.php";


/**
 * Virtual base class for implementing WebDAV servers 
 *
 * WebDAV server base class, needs to be extended to do useful work
 * 
 * @package HTTP_WebDAV_Server
 * @author Hartmut Holzgraefe <hholzgra@php.net>
 * @version 0.99.1dev
 */
class HTTP_WebDAV_Server 
{
    // {{{ Member Variables 
    
    /**
     * complete URI for this request
     *
     * @var string 
     */
    var $uri;


    /**
     * base URI for this request
     *
     * @var string 
     */
    var $base_uri;


    /**
     * URI path for this request
     *
     * @var string 
     */
    var $path;

    /**
     * Realm string to be used in authentification popups
     *
     * @var string 
     */
    var $http_auth_realm = "PHP WebDAV";

    /**
     * String to be used in "X-Dav-Powered-By" header
     *
     * @var string 
     */
    var $dav_powered_by = "";

    /**
     * Remember parsed If: (RFC2518/9.4) header conditions  
     *
     * @var array
     */
    var $_if_header_uris = array();

    /**
     * HTTP response status/message
     *
     * @var string
     */
    var $_http_status = "200 OK";

    /**
     * encoding of property values passed in
     *
     * @var string
     */
    var $_prop_encoding = "utf-8";

    // }}}

    // {{{ Constructor 

    /** 
     * Constructor
     *
     * @param void
     */
    private function __construct() 
    {
        // PHP messages destroy XML output -> switch them off
        //ini_set("display_errors", 0);
    }

    // }}}

    // {{{ ServeRequest() 
    /** 
     * Serve WebDAV HTTP request
     *
     * dispatch WebDAV HTTP request to the apropriate method handler
     * 
     * @param  void
     * @return void
     */
    function serveRequest() 
    {
        // default uri is the complete request uri
		// FIXME: use ilHTTPS::isDetected
        $uri = (@$_SERVER["HTTPS"] === "on" ? "https:" : "http:");
        $uri.= "//$_SERVER[HTTP_HOST]$_SERVER[SCRIPT_NAME]";
        
        $this->base_uri = $uri;
        $this->uri      = $uri . $_SERVER[PATH_INFO];

        // identify ourselves
        if (empty($this->dav_powered_by)) {
            header("X-Dav-Powered-By: PHP class: ".get_class($this));
        } else {
            header("X-Dav-Powered-By: ".$this->dav_powered_by );
        }

		$this->writelog(__METHOD__.': Using uri: '.$this->uri);

        // check authentication
        if (!$this->_check_auth()) {
            // RFC2518 says we must use Digest instead of Basic
            // but Microsoft Clients do not support Digest
            // and we don't support NTLM and Kerberos
            // so we are stuck with Basic here
            header('WWW-Authenticate: Basic realm="'.($this->http_auth_realm).'"');

            // Windows seems to require this being the last header sent
            // (changed according to PECL bug #3138)
            $this->http_status('401 Unauthorized');
			$this->writelog('Check auth failed');

            return;
        }
        
        // check 
        if(! $this->_check_if_header_conditions()) {
        	$this->writelog(__METHOD__.': Precondition failed.');
            $this->http_status("412 Precondition failed");
            return;
        }
        
        // set path
        $this->path = $this->_urldecode($_SERVER["PATH_INFO"]);
        if (!strlen($this->path)) {
            header("Location: ".$this->base_uri."/");
 			$this->writelog('HTTP_WebDAV_Server.ServeRequest() missing path info');
			$this->path = '/';
            //exit;
        }
	// BEGIN WebDAV: Don't strip backslashes. Backslashes are a valid part of a unix filename!
	/*
        if(ini_get("magic_quotes_gpc")) {
            $this->path = stripslashes($this->path);
        }*/
	// END PATCH WebDAV: Don't strip backslashes. Backslashes are a valid part of a unix filename!
        
        
        // detect requested method names
        $method = strtolower($_SERVER["REQUEST_METHOD"]);
        $wrapper = "http_".$method;
	
		$this->writelog(__METHOD__.': Using request method: '.$method);
        
        // activate HEAD emulation by GET if no HEAD method found
        if ($method == "head" && !method_exists($this, "head")) 
		{
            $method = "get";
			$this->writelog(__METHOD__.': Using head emulation by get.');
        }
        
        if (method_exists($this, $wrapper) && ($method == "options" || method_exists($this, $method))) 
		{
            $this->writelog(__METHOD__.': Calling wrapper: '.$wrapper);
			$this->$wrapper();  // call method by name
        } 
		else 
		{ // method not found/implemented
            if ($_SERVER["REQUEST_METHOD"] == "LOCK") 
			{
				$this->writelog(__METHOD__.': Method not found/implemented. Sending 412');
                $this->http_status("412 Precondition failed");
            } 
			else 
			{
				$this->writelog(__METHOD__.': Method not found/implemented. Sending allowd methods');
                $this->http_status("405 Method not allowed");
                header("Allow: ".join(", ", $this->_allow()));  // tell client what's allowed
            }
        }
    }

    // }}}

    // {{{ abstract WebDAV methods 

    // {{{ GET() 
    /**
     * GET implementation
     *
     * overload this method to retrieve resources from your server
     * <br>
     * 
     *
     * @abstract 
     * @param array &$params Array of input and output parameters
     * <br><b>input</b><ul>
     * <li> path - 
     * </ul>
     * <br><b>output</b><ul>
     * <li> size - 
     * </ul>
     * @returns int HTTP-Statuscode
     */

    /* abstract
       function GET(&$params) 
       {
           // dummy entry for PHPDoc
       } 
     */

    // }}}

    // {{{ PUT() 
    /**
     * PUT implementation
     *
     * PUT implementation
     *
     * @abstract 
     * @param array &$params
     * @returns int HTTP-Statuscode
     */
    
    /* abstract
       function PUT() 
       {
           // dummy entry for PHPDoc
       } 
    */
    
    // }}}

    // {{{ COPY() 

    /**
     * COPY implementation
     *
     * COPY implementation
     *
     * @abstract 
     * @param array &$params
     * @returns int HTTP-Statuscode
     */
    
    /* abstract
       function COPY() 
       {
           // dummy entry for PHPDoc
       } 
     */

    // }}}

    // {{{ MOVE() 

    /**
     * MOVE implementation
     *
     * MOVE implementation
     *
     * @abstract 
     * @param array &$params
     * @returns int HTTP-Statuscode
     */
    
    /* abstract
       function MOVE() 
       {
           // dummy entry for PHPDoc
       } 
     */

    // }}}

    // {{{ DELETE() 

    /**
     * DELETE implementation
     *
     * DELETE implementation
     *
     * @abstract 
     * @param array &$params
     * @returns int HTTP-Statuscode
     */
    
    /* abstract
       function DELETE() 
       {
           // dummy entry for PHPDoc
       } 
     */
    // }}}

    // {{{ PROPFIND() 

    /**
     * PROPFIND implementation
     *
     * PROPFIND implementation
     *
     * @abstract 
     * @param array &$params
     * @returns int HTTP-Statuscode
     */
    
    /* abstract
       function PROPFIND() 
       {
           // dummy entry for PHPDoc
       } 
     */

    // }}}

    // {{{ PROPPATCH() 

    /**
     * PROPPATCH implementation
     *
     * PROPPATCH implementation
     *
     * @abstract 
     * @param array &$params
     * @returns int HTTP-Statuscode
     */
    
    /* abstract
       function PROPPATCH() 
       {
           // dummy entry for PHPDoc
       } 
     */
    // }}}

    // {{{ LOCK() 

    /**
     * LOCK implementation
     *
     * LOCK implementation
     *
     * @abstract 
     * @param array &$params
     * @returns int HTTP-Statuscode
     */
    
    /* abstract
       function LOCK() 
       {
           // dummy entry for PHPDoc
       } 
     */
    // }}}

    // {{{ UNLOCK() 

    /**
     * UNLOCK implementation
     *
     * UNLOCK implementation
     *
     * @abstract 
     * @param array &$params
     * @returns int HTTP-Statuscode
     */

    /* abstract
       function UNLOCK() 
       {
           // dummy entry for PHPDoc
       } 
     */
    // }}}

    // }}}

    // {{{ other abstract methods 

    // {{{ check_auth() 

    /**
     * check authentication
     *
     * overload this method to retrieve and confirm authentication information
     *
     * @abstract 
     * @param string type Authentication type, e.g. "basic" or "digest"
     * @param string username Transmitted username
     * @param string passwort Transmitted password
     * @returns bool Authentication status
     */
    
    /* abstract
       function checkAuth($type, $username, $password) 
       {
           // dummy entry for PHPDoc
       } 
    */
    
    // }}}

    // {{{ checklock() 

    /**
     * check lock status for a resource
     *
     * overload this method to return shared and exclusive locks 
     * active for this resource
     *
     * @abstract 
     * @param string resource Resource path to check
     * @returns array An array of lock entries each consisting
     *                of 'type' ('shared'/'exclusive'), 'token' and 'timeout'
     */
    
    /* abstract
       function checklock($resource) 
       {
           // dummy entry for PHPDoc
       } 
     */

    // }}}

    // }}}

    // {{{ WebDAV HTTP method wrappers 

    // {{{ http_OPTIONS() 

    /**
     * OPTIONS method handler
     *
     * The OPTIONS method handler creates a valid OPTIONS reply
     * including Dav: and Allowed: headers
     * based on the implemented methods found in the actual instance
     *
     * @param  void
     * @return void
     */
    function http_OPTIONS() 
    {
        // Microsoft clients default to the Frontpage protocol 
        // unless we tell them to use WebDAV
        header("MS-Author-Via: DAV");

        // get allowed methods
        $allow = $this->_allow();

        // dav header
        $dav = array(1);        // assume we are always dav class 1 compliant
        if (isset($allow['LOCK'])) {
            $dav[] = 2;         // dav class 2 requires that locking is supported 
        }

        // tell clients what we found
        $this->http_status("200 OK");
        header("DAV: "  .join("," , $dav));
        header("Allow: ".join(", ", $allow));
		$this->writelog(__METHOD__.': dav='.var_export($dav,true).' allow='.var_export($allow,true));
        header("Content-length: 0");
    }

    // }}}


    // {{{ http_PROPFIND() 

    /**
     * PROPFIND method handler
     *
     * @param  void
     * @return void
     */
    function http_PROPFIND() 
    {
        $options = Array();
        $options["path"] = $this->path;
        
        // search depth from header (default is "infinity)
        if (isset($_SERVER['HTTP_DEPTH'])) {
            $options["depth"] = $_SERVER["HTTP_DEPTH"];
        } else {
            $options["depth"] = "infinity";
        }       

        // analyze request payload
        $propinfo = new _parse_propfind("php://input");
        if (!$propinfo->success) {
            $this->http_status("400 Error");
            return;
        }
        $options['props'] = $propinfo->props;
        
        // call user handler
	$files = array();
        if (!$this->propfind($options, $files)) {
            $this->http_status("404 Not Found");
            return;
        }
        
        // collect namespaces here
        $ns_hash = array();
        
        // Microsoft Clients need this special namespace for date and time values
        $ns_defs = "xmlns:ns0=\"urn:uuid:c2f41010-65b3-11d1-a29f-00aa00c14882/\"";    
    
        // now we loop over all returned file entries
        foreach($files["files"] as $filekey => $file) {
            
            // nothing to do if no properties were returend for a file
            if (!isset($file["props"]) || !is_array($file["props"])) {
                continue;
            }
            
            // now loop over all returned properties
            foreach($file["props"] as $key => $prop) {
                // as a convenience feature we do not require that user handlers
                // restrict returned properties to the requested ones
                // here we strip all unrequested entries out of the response
                
                switch($options['props']) {
                case "all":
                    // nothing to remove
                    break;
                    
                case "names":
                    // only the names of all existing properties were requested
                    // so we remove all values
                    unset($files["files"][$filekey]["props"][$key]["val"]);
                    break;
                    
                default:
                    $found = false;
                    
                    // search property name in requested properties 
                    foreach((array)$options["props"] as $reqprop) {
                        if (   $reqprop["name"]  == $prop["name"] 
                            && $reqprop["xmlns"] == $prop["ns"]) {
                            $found = true;
                            break;
                        }
                    }
                    
                    // unset property and continue with next one if not found/requested
                    if (!$found) {
                        $files["files"][$filekey]["props"][$key]="";
                        continue(2);
                    }
                    break;
                }
                
                // namespace handling 
                if (empty($prop["ns"])) continue; // no namespace
                $ns = $prop["ns"]; 
                if ($ns == "DAV:") continue; // default namespace
                if (isset($ns_hash[$ns])) continue; // already known

                // register namespace 
                $ns_name = "ns".(count($ns_hash) + 1);
                $ns_hash[$ns] = $ns_name;
                $ns_defs .= " xmlns:$ns_name=\"$ns\"";
            }
        
            // we also need to add empty entries for properties that were requested
            // but for which no values where returned by the user handler
            if (is_array($options['props'])) {
                foreach($options["props"] as $reqprop) {
                    if($reqprop['name']=="") continue; // skip empty entries
                    
                    $found = false;
                    
                    // check if property exists in result
                    foreach($file["props"] as $prop) {
                        if (   $reqprop["name"]  == $prop["name"]
                            && $reqprop["xmlns"] == $prop["ns"]) {
                            $found = true;
                            break;
                        }
                    }
                    
                    if (!$found) {
                        if($reqprop["xmlns"]==="DAV:" && $reqprop["name"]==="lockdiscovery") {
                            // lockdiscovery is handled by the base class
                            $files["files"][$filekey]["props"][] 
                                = $this->mkprop("DAV:", 
                                                "lockdiscovery" , 
                                                $this->lockdiscovery($files["files"][$filekey]['path']));
                        } else {
                            // add empty value for this property
                            $files["files"][$filekey]["noprops"][] =
                                $this->mkprop($reqprop["xmlns"], $reqprop["name"], "");

                            // register property namespace if not known yet
                            if ($reqprop["xmlns"] != "DAV:" && !isset($ns_hash[$reqprop["xmlns"]])) {
                                $ns_name = "ns".(count($ns_hash) + 1);
                                $ns_hash[$reqprop["xmlns"]] = $ns_name;
                                $ns_defs .= " xmlns:$ns_name=\"$reqprop[xmlns]\"";
                            }
                        }
                    }
                }
            }
        }
        
        // now we generate the reply header ...
        $this->http_status("207 Multi-Status");
        header('Content-Type: text/xml; charset="utf-8"');
        
        // ... and payload
        echo "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n";
        echo "<D:multistatus xmlns:D=\"DAV:\">\n";
            
        foreach($files["files"] as $file) {
            // ignore empty or incomplete entries
            if(!is_array($file) || empty($file) || !isset($file["path"])) continue;
            $path = $file['path'];                  
            if(!is_string($path) || $path==="") continue;
 
            echo " <D:response $ns_defs>\n";
        
	    // BEGIN WebDAV W. Randelshofer Don't slashify path because it confuses Mac OS X
            //$href = $this->_slashify($_SERVER['SCRIPT_NAME'] . $path);
            $href = $_SERVER['SCRIPT_NAME'] . $path;
	    //END PATCH WebDAV W. Randelshofer
       
            echo "  <D:href>$href</D:href>\n";
       
            // report all found properties and their values (if any)
            if (isset($file["props"]) && is_array($file["props"])) {
                echo "   <D:propstat>\n";
                echo "    <D:prop>\n";

                foreach($file["props"] as $key => $prop) {
                    
                    if (!is_array($prop)) continue;
                    if (!isset($prop["name"])) continue;
                    if (!isset($prop["val"]) || $prop["val"] === "" || $prop["val"] === false) {
                        // empty properties (cannot use empty() for check as "0" is a legal value here)
                        if($prop["ns"]=="DAV:") {
                            echo "     <D:$prop[name]/>\n";
                        } else if(!empty($prop["ns"])) {
                            echo "     <".$ns_hash[$prop["ns"]].":$prop[name]/>\n";
                        } else {
                            echo "     <$prop[name] xmlns=\"\"/>";
                        }
                    } else if ($prop["ns"] == "DAV:") {
                        // some WebDAV properties need special treatment
                        switch ($prop["name"]) {
                        case "creationdate":
                            echo "     <D:creationdate ns0:dt=\"dateTime.tz\">"
                               // BEGIN WebDAV W. Randelshofer
                                  . gmdate("Y-m-d\\TH:i:s\\Z",$prop['val'])
                               //   . gmdate("D, d M Y H:i:s ", $prop['val'])
                               // END PATCH WebDAV W. Randelshofer
                                . "</D:creationdate>\n";
                            break;
                        case "getlastmodified":
                            echo "     <D:getlastmodified ns0:dt=\"dateTime.rfc1123\">"
                                . gmdate("D, d M Y H:i:s ", $prop['val'])
                                . "GMT</D:getlastmodified>\n";
                            break;
                        case "resourcetype":
                            echo "     <D:resourcetype><D:$prop[val]/></D:resourcetype>\n";
                            break;
                        case "supportedlock":
                            echo "     <D:supportedlock>$prop[val]</D:supportedlock>\n";
                            break;
                        case "lockdiscovery":  
                            echo "     <D:lockdiscovery>\n";
                            echo $prop["val"];
                            echo "     </D:lockdiscovery>\n";
                            break;
                        default:  
                            echo "     <D:$prop[name]>"
                                . $this->_prop_encode(htmlspecialchars($prop['val']))
                                .     "</D:$prop[name]>\n";                               
                            break;
                        }
                    } else {
                        // properties from namespaces != "DAV:" or without any namespace 
                        if ($prop["ns"]) {
                            echo "     <" . $ns_hash[$prop["ns"]] . ":$prop[name]>"
                                . $this->_prop_encode(htmlspecialchars($prop['val']))
                                . "</" . $ns_hash[$prop["ns"]] . ":$prop[name]>\n";
                        } else {
                            echo "     <$prop[name] xmlns=\"\">"
                                . $this->_prop_encode(htmlspecialchars($prop['val']))
                                . "</$prop[name]>\n";
                        }                               
                    }
                }

                echo "   </D:prop>\n";
                echo "   <D:status>HTTP/1.1 200 OK</D:status>\n";
                echo "  </D:propstat>\n";
            }
       
            // now report all properties requested but not found
            if (isset($file["noprops"])) {
                echo "   <D:propstat>\n";
                echo "    <D:prop>\n";

                foreach($file["noprops"] as $key => $prop) {
                    if ($prop["ns"] == "DAV:") {
                        echo "     <D:$prop[name]/>\n";
                    } else if ($prop["ns"] == "") {
                        echo "     <$prop[name] xmlns=\"\"/>\n";
                    } else {
                        echo "     <" . $ns_hash[$prop["ns"]] . ":$prop[name]/>\n";
                    }
                }

                echo "   </D:prop>\n";
                echo "   <D:status>HTTP/1.1 404 Not Found</D:status>\n";
                echo "  </D:propstat>\n";
            }
            
            echo " </D:response>\n";
        }
        
        echo "</D:multistatus>\n";
    }

    
    // }}}
    
    // {{{ http_PROPPATCH() 

    /**
     * PROPPATCH method handler
     *
     * @param  void
     * @return void
     */
    function http_PROPPATCH() 
    {
        if($this->_check_lock_status($this->path)) {
            $options = Array();
            $options["path"] = $this->path;

            $propinfo = new _parse_proppatch("php://input");
            
            if (!$propinfo->success) {
                $this->http_status("400 Error");
                return;
            }
            
            $options['props'] = $propinfo->props;
            
            $responsedescr = $this->proppatch($options);
            
            $this->http_status("207 Multi-Status");
            header('Content-Type: text/xml; charset="utf-8"');
            
            echo "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n";

            echo "<D:multistatus xmlns:D=\"DAV:\">\n";
            echo " <D:response>\n";
            echo "  <D:href>".$this->_urlencode($_SERVER["SCRIPT_NAME"].$this->path)."</D:href>\n";

            foreach($options["props"] as $prop) {
                echo "   <D:propstat>\n";
                echo "    <D:prop><$prop[name] xmlns=\"$prop[ns]\"/></D:prop>\n";
                echo "    <D:status>HTTP/1.1 $prop[status]</D:status>\n";
                echo "   </D:propstat>\n";
            }

            if ($responsedescr) {
                echo "  <D:responsedescription>".
                    $this->_prop_encode(htmlspecialchars($responsedescr)).
                    "</D:responsedescription>\n";
            }

            echo " </D:response>\n";
            echo "</D:multistatus>\n";
        } else {
            $this->http_status("423 Locked");
        }
    }
    
    // }}}


    // {{{ http_MKCOL() 

    /**
     * MKCOL method handler
     *
     * @param  void
     * @return void
     */
    function http_MKCOL() 
    {
        $options = Array();
        $options["path"] = $this->path;

        $stat = $this->mkcol($options);

        $this->http_status($stat);
    }

    // }}}


    // {{{ http_GET() 

    /**
     * GET method handler
     *
     * @param void
     * @returns void
     */
    function http_GET() 
    {
        // TODO check for invalid stream
        $options = Array();
        $options["path"] = $this->path;

        $this->_get_ranges($options);

        if (true === ($status = $this->get($options))) {
            if (!headers_sent()) {
                $status = "200 OK";

                if (!isset($options['mimetype'])) {
                    $options['mimetype'] = "application/octet-stream";
                }
                header("Content-type: $options[mimetype]");
                
                if (isset($options['mtime'])) {
                    header("Last-modified:".gmdate("D, d M Y H:i:s ", $options['mtime'])."GMT");
                }
                
                if (isset($options['stream'])) {
                    // GET handler returned a stream
                    if (!empty($options['ranges']) && (0===fseek($options['stream'], 0, SEEK_SET))) {
                        // partial request and stream is seekable 
                        
                        if (count($options['ranges']) === 1) {
                            $range = $options['ranges'][0];
                            
                            if (isset($range['start'])) {
                                fseek($options['stream'], $range['start'], SEEK_SET);
                                if (feof($options['stream'])) {
                                    $this->http_status("416 Requested range not satisfiable");
                                    exit;
                                }

                                if (isset($range['end'])) {
                                    $size = $range['end']-$range['start']+1;
                                    $this->http_status("206 partial");
                                    header("Content-length: $size");
                                    header("Content-range: $range[start]-$range[end]/"
                                           . (isset($options['size']) ? $options['size'] : "*"));
                                    while ($size && !feof($options['stream'])) {
                                        $buffer = fread($options['stream'], 4096);
                                        $size -= strlen($buffer);
                                        echo $buffer;
                                    }
                                } else {
                                    $this->http_status("206 partial");
                                    if (isset($options['size'])) {
                                        header("Content-length: ".($options['size'] - $range['start']));
                                        header("Content-range: $start-$end/"
                                               . (isset($options['size']) ? $options['size'] : "*"));
                                    }
                                    fpassthru($options['stream']);
                                }
                            } else {
                                header("Content-length: ".$range['last']);
                                fseek($options['stream'], -$range['last'], SEEK_END);
                                fpassthru($options['stream']);
                            }
                        } else {
                            $this->_multipart_byterange_header(); // init multipart
                            foreach ($options['ranges'] as $range) {
                                // TODO what if size unknown? 500?
                                if (isset($range['start'])) {
                                    $from  = $range['start'];
                                    $to    = !empty($range['end']) ? $range['end'] : $options['size']-1; 
                                } else {
                                    $from = $options['size'] - $range['last']-1;
                                    $to = $options['size'] -1;
                                }
                                $total = isset($options['size']) ? $options['size'] : "*"; 
                                $size = $to - $from + 1;
                                $this->_multipart_byterange_header($options['mimetype'], $from, $to, $total);


                                fseek($options['stream'], $start, SEEK_SET);
                                while ($size && !feof($options['stream'])) {
                                    $buffer = fread($options['stream'], 4096);
                                    $size -= strlen($buffer);
                                    echo $buffer;
                                }
                            }
                            $this->_multipart_byterange_header(); // end multipart
                        }
                    } else {
                        // normal request or stream isn't seekable, return full content
                        if (isset($options['size'])) {
                            header("Content-length: ".$options['size']);
                        }
			
			// BEGIN WebDAV W. Randelshofer
			// fpassthru apparently only delivers up to 2 million bytes.
			// use fread instead
			//fpassthru($options['stream']);
			while (! feof($options['stream'])) {
				$buffer = fread($options['stream'], 4096);
				echo $buffer;
			}
			// END PATCH WebDAV W. Randelshofer
			
                        return; // no more headers
                    }
                } elseif (isset($options['data']))  {
                    if (is_array($options['data'])) {
                        // reply to partial request
                    } else {
                        header("Content-length: ".strlen($options['data']));
                        echo $options['data'];
                    }
                }
            } 
        } 

        if (false === $status) {
		// BEGIN WebDAV Randelshofer
		$status = '404 Not Found';
        	//$this->http_status("404 not found");
		// END PATCH WebDAV Randelshofer
	}

        if (!headers_sent()) {
            // TODO: check setting of headers in various code pathes above
            $this->http_status("$status");
        }
    }


    /**
     * parse HTTP Range: header
     *
     * @param  array options array to store result in
     * @return void
     */
    function _get_ranges(&$options) 
    {
        // process Range: header if present
        if (isset($_SERVER['HTTP_RANGE'])) {

            // we only support standard "bytes" range specifications for now
            if (ereg("bytes[[:space:]]*=[[:space:]]*(.+)", $_SERVER['HTTP_RANGE'], $matches)) {
                $options["ranges"] = array();

                // ranges are comma separated
                foreach (explode(",", $matches[1]) as $range) {
                    // ranges are either from-to pairs or just end positions
                    list($start, $end) = explode("-", $range);
                    $options["ranges"][] = ($start==="") 
                                         ? array("last"=>$end) 
                                         : array("start"=>$start, "end"=>$end);
                }
            }
        }
    }

    /**
     * generate separator headers for multipart response
     *
     * first and last call happen without parameters to generate 
     * the initial header and closing sequence, all calls inbetween
     * require content mimetype, start and end byte position and
     * optionaly the total byte length of the requested resource
     *
     * @param  string  mimetype
     * @param  int     start byte position
     * @param  int     end   byte position
     * @param  int     total resource byte size
     */
    function _multipart_byterange_header($mimetype = false, $from = false, $to=false, $total=false) 
    {
        if ($mimetype === false) {
            if (!isset($this->multipart_separator)) {
                // initial

                // a little naive, this sequence *might* be part of the content
                // but it's really not likely and rather expensive to check 
                $this->multipart_separator = "SEPARATOR_".md5(microtime());

                // generate HTTP header
                header("Content-type: multipart/byteranges; boundary=".$this->multipart_separator);
            } else {
                // final 

                // generate closing multipart sequence
                echo "\n--{$this->multipart_separator}--";
            }
        } else {
            // generate separator and header for next part
            echo "\n--{$this->multipart_separator}\n";
            echo "Content-type: $mimetype\n";
            echo "Content-range: $from-$to/". ($total === false ? "*" : $total);
            echo "\n\n";
        }
    }

            

    // }}}

    // {{{ http_HEAD() 

    /**
     * HEAD method handler
     *
     * @param  void
     * @return void
     */
    function http_HEAD() 
    {
        $status = false;
        $options = Array();
        $options["path"] = $this->path;
        
        if (method_exists($this, "HEAD")) {
            $status = $this->head($options);
        } else if (method_exists($this, "GET")) {
            ob_start();
            $status = $this->GET($options);
            ob_end_clean();
        }
        
        if($status===true)  $status = "200 OK";
        if($status===false) $status = "404 Not found";
        
        $this->http_status($status);
    }

    // }}}

    // {{{ http_PUT() 

    /**
     * PUT method handler
     *
     * @param  void
     * @return void
     */
    function http_PUT() 
    {
        if ($this->_check_lock_status($this->path)) {
            $options = Array();
            $options["path"] = $this->path;
            $options["content_length"] = $_SERVER["CONTENT_LENGTH"];

            // get the Content-type 
            if (isset($_SERVER["CONTENT_TYPE"])) {
                // for now we do not support any sort of multipart requests
                if (!strncmp($_SERVER["CONTENT_TYPE"], "multipart/", 10)) {
                    $this->http_status("501 not implemented");
                    echo "The service does not support mulipart PUT requests";
                    return;
                }
                $options["content_type"] = $_SERVER["CONTENT_TYPE"];
            } else {
                // default content type if none given
                $options["content_type"] = "application/octet-stream";
            }

            /* RFC 2616 2.6 says: "The recipient of the entity MUST NOT 
               ignore any Content-* (e.g. Content-Range) headers that it 
               does not understand or implement and MUST return a 501 
               (Not Implemented) response in such cases."
            */ 
            foreach ($_SERVER as $key => $val) {
                if (strncmp($key, "HTTP_CONTENT", 11)) continue;
                switch ($key) {
                case 'HTTP_CONTENT_ENCODING': // RFC 2616 14.11
                    // TODO support this if ext/zlib filters are available
                    $this->http_status("501 not implemented"); 
                    echo "The service does not support '$val' content encoding";
                    return;

                case 'HTTP_CONTENT_LANGUAGE': // RFC 2616 14.12
                    // we assume it is not critical if this one is ignored
                    // in the actual PUT implementation ...
                    $options["content_language"] = $value;
                    break;

                case 'HTTP_CONTENT_LOCATION': // RFC 2616 14.14
                    /* The meaning of the Content-Location header in PUT 
                       or POST requests is undefined; servers are free 
                       to ignore it in those cases. */
                    break;

                case 'HTTP_CONTENT_RANGE':    // RFC 2616 14.16
                    // single byte range requests are supported
                    // the header format is also specified in RFC 2616 14.16
                    // TODO we have to ensure that implementations support this or send 501 instead
                    if (!preg_match('@bytes\s+(\d+)-(\d+)/((\d+)|\*)@', $value, $matches)) {
                        $this->http_status("400 bad request"); 
                        echo "The service does only support single byte ranges";
                        return;
                    }
                    
                    $range = array("start"=>$matches[1], "end"=>$matches[2]);
                    if (is_numeric($matches[3])) {
                        $range["total_length"] = $matches[3];
                    }
                    $option["ranges"][] = $range;

                    // TODO make sure the implementation supports partial PUT
                    // this has to be done in advance to avoid data being overwritten
                    // on implementations that do not support this ...
                    break;

                case 'HTTP_CONTENT_MD5':      // RFC 2616 14.15
                    // TODO: maybe we can just pretend here?
                    $this->http_status("501 not implemented"); 
                    echo "The service does not support content MD5 checksum verification"; 
                    return;

                default: 
                    // any other unknown Content-* headers
                    $this->http_status("501 not implemented"); 
                    echo "The service does not support '$key'"; 
                    return;
                }
            }

            $options["stream"] = fopen("php://input", "r");

            $stat = $this->PUT($options);

            if ($stat == false) {
                $stat = "403 Forbidden";
            } else if (is_resource($stat) && get_resource_type($stat) == "stream") {
                $stream = $stat;

                $stat = $options["new"] ? "201 Created" : "204 No Content";

                if (!empty($options["ranges"])) {
                    // TODO multipart support is missing (see also above)
                    if (0 == fseek($stream, $range[0]["start"], SEEK_SET)) {
                        $length = $range[0]["end"]-$range[0]["start"]+1;
                        if (!fwrite($stream, fread($options["stream"], $length))) {
                            $stat = "403 Forbidden"; 
                        }
                    } else {
                        $stat = "403 Forbidden"; 
                    }
                } else {
                    while (!feof($options["stream"])) {
		    	// BEGIN WebDAV W. Randelshofer explicitly compare with false. 
                        if (false === ($written = fwrite($stream, fread($options["stream"], 4096)))) {
		    	// END WebDAV W. Randelshofer explicitly compare with false. 
                            $stat = "403 Forbidden"; 
                            break;
                        }
			$count += $written;
                    }
                }

                fclose($stream);            
		//$this->writelog('PUT wrote '.$written.' bytes');
        	// BEGIN WebDAV W. Randelshofer finish the put-operation
                $this->PUTfinished($options);
        	// END WebDAV W. Randelshofer finish the put-operation
            } 

            $this->http_status($stat);
        } else {
            $this->http_status("423 Locked");
        }
    }

    // }}}


    // {{{ http_DELETE() 

    /**
     * DELETE method handler
     *
     * @param  void
     * @return void
     */
    function http_DELETE() 
    {
        // check RFC 2518 Section 9.2, last paragraph
        if (isset($_SERVER["HTTP_DEPTH"])) {
            if ($_SERVER["HTTP_DEPTH"] != "infinity") {
                $this->http_status("400 Bad Request");
                return;
            }
        }

        // check lock status
        if ($this->_check_lock_status($this->path)) {
            // ok, proceed
            $options = Array();
            $options["path"] = $this->path;

            $stat = $this->delete($options);

            $this->http_status($stat);
        } else {
            // sorry, its locked
            $this->http_status("423 Locked");
        }
    }

    // }}}

    // {{{ http_COPY() 

    /**
     * COPY method handler
     *
     * @param  void
     * @return void
     */
    function http_COPY() 
    {
        // no need to check source lock status here 
        // destination lock status is always checked by the helper method
        $this->_copymove("copy");
    }

    // }}}

    // {{{ http_MOVE() 

    /**
     * MOVE method handler
     *
     * @param  void
     * @return void
     */
    function http_MOVE() 
    {
	    //$this->writelog('MOVE()');
        if ($this->_check_lock_status($this->path)) {
            // destination lock status is always checked by the helper method
            $this->_copymove("move");
        } else {
	    //$this->writelog('MOVE():423 Locked');
            $this->http_status("423 Locked");
        }
    }

    // }}}


    // {{{ http_LOCK() 

    /**
     * LOCK method handler
     *
     * @param  void
     * @return void
     */
    function http_LOCK() 
    {
        $options = Array();
        $options["path"] = $this->path;
        
        if (isset($_SERVER['HTTP_DEPTH'])) {
            $options["depth"] = $_SERVER["HTTP_DEPTH"];
        } else {
            $options["depth"] = "infinity";
        }
        
        if (isset($_SERVER["HTTP_TIMEOUT"])) {
            $options["timeout"] = explode(",", $_SERVER["HTTP_TIMEOUT"]);
        }
        
        if(empty($_SERVER['CONTENT_LENGTH']) && !empty($_SERVER['HTTP_IF'])) {
            // check if locking is possible
            if(!$this->_check_lock_status($this->path)) {
                $this->http_status("423 Locked");
                return;
            }

            // refresh lock
            $options["update"] = substr($_SERVER['HTTP_IF'], 2, -2);
            $stat = $this->lock($options);
        } else { 
            // extract lock request information from request XML payload
            $lockinfo = new _parse_lockinfo("php://input");
            if (!$lockinfo->success) {
                $this->http_status("400 bad request"); 
            }

            // check if locking is possible
            if(!$this->_check_lock_status($this->path, $lockinfo->lockscope === "shared")) {
                $this->http_status("423 Locked");
                return;
            }

            // new lock 
            $options["scope"] = $lockinfo->lockscope;
            $options["type"]  = $lockinfo->locktype;
            $options["owner"] = $lockinfo->owner;
            
            $options["locktoken"] = $this->_new_locktoken();
            
            $stat = $this->lock($options);              
        }
        
        if(is_bool($stat)) {
            $http_stat = $stat ? "200 OK" : "423 Locked";
        } else {
            $http_stat = $stat;
        }
        
        $this->http_status($http_stat);
        
        if ($http_stat{0} == 2) { // 2xx states are ok 
            if($options["timeout"]) {
                // more than a million is considered an absolute timestamp
                // less is more likely a relative value
                if($options["timeout"]>1000000) {
                    $timeout = "Second-".($options['timeout']-time());
                } else {
                    $timeout = "Second-$options[timeout]";
                }
            } else {
                $timeout = "Infinite";
            }
            /*
	    $this->writelog(
            'Content-Type: text/xml; charset="utf-8"'
            ."Lock-Token: <$options[locktoken]>"
            . "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n"
            . "<D:prop xmlns:D=\"DAV:\">\n"
            . " <D:lockdiscovery>\n"
            . "  <D:activelock>\n"
            . "   <D:lockscope><D:$options[scope]/></D:lockscope>\n"
            . "   <D:locktype><D:$options[type]/></D:locktype>\n"
            . "   <D:depth>$options[depth]</D:depth>\n"
            . "   <D:owner>$options[owner]</D:owner>\n"
            . "   <D:timeout>$timeout</D:timeout>\n"
            . "   <D:locktoken><D:href>$options[locktoken]</D:href></D:locktoken>\n"
            . "  </D:activelock>\n"
            . " </D:lockdiscovery>\n"
            . "</D:prop>\n\n"
	    );*/
            header('Content-Type: text/xml; charset="utf-8"');
            header("Lock-Token: <$options[locktoken]>");
            echo "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n";
            echo "<D:prop xmlns:D=\"DAV:\">\n";
            echo " <D:lockdiscovery>\n";
            echo "  <D:activelock>\n";
            echo "   <D:lockscope><D:$options[scope]/></D:lockscope>\n";
            echo "   <D:locktype><D:$options[type]/></D:locktype>\n";
            echo "   <D:depth>$options[depth]</D:depth>\n";
            echo "   <D:owner>$options[owner]</D:owner>\n";
            echo "   <D:timeout>$timeout</D:timeout>\n";
            echo "   <D:locktoken><D:href>$options[locktoken]</D:href></D:locktoken>\n";
            echo "  </D:activelock>\n";
            echo " </D:lockdiscovery>\n";
            echo "</D:prop>\n\n";
        }
    }
    

    // }}}

    // {{{ http_UNLOCK() 

    /**
     * UNLOCK method handler
     *
     * @param  void
     * @return void
     */
    function http_UNLOCK() 
    {
        $options = Array();
        $options["path"] = $this->path;

        if (isset($_SERVER['HTTP_DEPTH'])) {
            $options["depth"] = $_SERVER["HTTP_DEPTH"];
        } else {
            $options["depth"] = "infinity";
        }

        // strip surrounding <>
        $options["token"] = substr(trim($_SERVER["HTTP_LOCK_TOKEN"]), 1, -1);  
//$this->writelog('http_UNLOCK HTTP_LOCK_TOKEN='.$_SERVER["HTTP_LOCK_TOKEN"]);
        // call user method
        $stat = $this->unlock($options);

        $this->http_status($stat);
    }

    // }}}

    // }}}

    // {{{ _copymove() 

    function _copymove($what) 
    {
	    //$this->writelog('_copymove('.$what.')');
        $options = Array();
        $options["path"] = $this->path;

        if (isset($_SERVER["HTTP_DEPTH"])) {
            $options["depth"] = $_SERVER["HTTP_DEPTH"];
        } else {
            $options["depth"] = "infinity";
        }
//$this->writelog('_copymove dest='.$_SERVER["HTTP_DESTINATION"]);
        extract(parse_url($_SERVER["HTTP_DESTINATION"]));
	// BEGIN WebDAV: decode path     (bereits in PEAR CVS gefixt)
	// We must decode the target path too.
	$path = $this->_urldecode($path);
	// END Patch WebDAV: decode path     
        $http_host = $host;
        if (isset($port) && $port != 80)
            $http_host.= ":$port";

        list($http_header_host,$http_header_port)  = explode(":",$_SERVER["HTTP_HOST"]);
        if (isset($http_header_port) && $http_header_port != 80) { 
            $http_header_host .= ":".$http_header_port;
        }

        if ($http_host == $http_header_host &&
            !strncmp($_SERVER["SCRIPT_NAME"], $path,
                     strlen($_SERVER["SCRIPT_NAME"]))) {
            $options["dest"] = substr($path, strlen($_SERVER["SCRIPT_NAME"]));
	    //$this->writelog('_copymove() dest='.$options['dest']);
            if (!$this->_check_lock_status($options["dest"])) {
	    //$this->writelog('_copymove():423 Locked');
                $this->http_status("423 Locked");
                return;
            }
	    //$this->writelog('_copymove() ...');

        } else {
            $options["dest_url"] = $_SERVER["HTTP_DESTINATION"];
        }

        // see RFC 2518 Sections 9.6, 8.8.4 and 8.9.3
        if (isset($_SERVER["HTTP_OVERWRITE"])) {
            $options["overwrite"] = $_SERVER["HTTP_OVERWRITE"] == "T";
        } else {
            $options["overwrite"] = true;
        }

        $stat = $this->$what($options);
        $this->http_status($stat);
    }

    // }}}

    // {{{ _allow() 

    /**
     * check for implemented HTTP methods
     *
     * @param  void
     * @return array something
     */
    function _allow() 
    {
        // OPTIONS is always there
        $allow = array("OPTIONS" =>"OPTIONS");

        // all other METHODS need both a http_method() wrapper
        // and a method() implementation
        // the base class supplies wrappers only
        foreach(get_class_methods($this) as $method) {
            if (!strncmp("http_", $method, 5)) {
                $method = strtoupper(substr($method, 5));
                if (method_exists($this, $method)) {
                    $allow[$method] = $method;
                }
            }
        }

        // we can emulate a missing HEAD implemetation using GET
        if (isset($allow["GET"]))
            $allow["HEAD"] = "HEAD";

        // no LOCK without checklok()
        if (!method_exists($this, "checklock")) {
            unset($allow["LOCK"]);
            unset($allow["UNLOCK"]);
        }

        return $allow;
    }

    // }}}

    /**
     * helper for property element creation
     *
     * @param  string  XML namespace (optional)
     * @param  string  property name
     * @param  string  property value
     * @return array   property array
     */
    function mkprop() 
    {
        $args = func_get_args();
        if (count($args) == 3) {
            return array("ns"   => $args[0], 
                         "name" => $args[1],
                         "val"  => $args[2]);
        } else {
            return array("ns"   => "DAV:", 
                         "name" => $args[0],
                         "val"  => $args[1]);
        }
    }

    // {{{ _check_auth 

    /**
     * check authentication if check is implemented
     * 
     * @param  void
     * @return bool  true if authentication succeded or not necessary
     */
    function _check_auth() 
    {
        if (method_exists($this, "checkAuth")) {
            // PEAR style method name
            return $this->checkAuth(@$_SERVER["AUTH_TYPE"],
                                     @$_SERVER["PHP_AUTH_USER"],
                                     @$_SERVER["PHP_AUTH_PW"]);
        } else if (method_exists($this, "check_auth")) {
            // old (pre 1.0) method name
            return $this->check_auth(@$_SERVER["AUTH_TYPE"],
                                     @$_SERVER["PHP_AUTH_USER"],
                                     @$_SERVER["PHP_AUTH_PW"]);
        } else {
            // no method found -> no authentication required
            return true;
        }
    }

    // }}}

    // {{{ UUID stuff 
    
    /**
     * generate Unique Universal IDentifier for lock token
     *
     * @param  void
     * @return string  a new UUID
     */
    function _new_uuid() 
    {
        // use uuid extension from PECL if available
        if (function_exists("uuid_create")) {
            return uuid_create();
        }

        // fallback
        $uuid = md5(microtime().getmypid());    // this should be random enough for now

        // set variant and version fields for 'true' random uuid
        $uuid{12} = "4";
        $n = 8 + (ord($uuid{16}) & 3);
        $hex = "0123456789abcdef";
        $uuid{16} = $hex{$n};

        // return formated uuid
        return substr($uuid,  0, 8)."-"
            .  substr($uuid,  8, 4)."-"
            .  substr($uuid, 12, 4)."-"
            .  substr($uuid, 16, 4)."-"
            .  substr($uuid, 20);
    }

    /**
     * create a new opaque lock token as defined in RFC2518
     *
     * @param  void
     * @return string  new RFC2518 opaque lock token
     */
    function _new_locktoken() 
    {
        return "opaquelocktoken:".$this->_new_uuid();
    }

    // }}}

    // {{{ WebDAV If: header parsing 

    /**
     * 
     *
     * @param  string  header string to parse
     * @param  int     current parsing position
     * @return array   next token (type and value)
     */
    function _if_header_lexer($string, &$pos) 
    {
        // skip whitespace
        while (ctype_space($string{$pos})) {
            ++$pos;
        }

        // already at end of string?
        if (strlen($string) <= $pos) {
            return false;
        }

        // get next character
        $c = $string{$pos++};

        // now it depends on what we found
        switch ($c) {
            case "<":
                // URIs are enclosed in <...>
                $pos2 = strpos($string, ">", $pos);
                $uri = substr($string, $pos, $pos2 - $pos);
                $pos = $pos2 + 1;
                return array("URI", $uri);

            case "[":
                //Etags are enclosed in [...]
                if ($string{$pos} == "W") {
                    $type = "ETAG_WEAK";
                    $pos += 2;
                } else {
                    $type = "ETAG_STRONG";
                }
                $pos2 = strpos($string, "]", $pos);
                $etag = substr($string, $pos + 1, $pos2 - $pos - 2);
                $pos = $pos2 + 1;
                return array($type, $etag);

            case "N":
                // "N" indicates negation
                $pos += 2;
                return array("NOT", "Not");

            default:
                // anything else is passed verbatim char by char
                return array("CHAR", $c);
        }
    }

    /** 
     * parse If: header
     *
     * @param  string  header string
     * @return array   URIs and their conditions
     */
    function _if_header_parser($str) 
    {
        $pos = 0;
        $len = strlen($str);

        $uris = array();

        // parser loop
        while ($pos < $len) {
            // get next token
            $token = $this->_if_header_lexer($str, $pos);

            // check for URI
            if ($token[0] == "URI") {
                $uri = $token[1]; // remember URI
                $token = $this->_if_header_lexer($str, $pos); // get next token
            } else {
                $uri = "";
            }

            // sanity check
            if ($token[0] != "CHAR" || $token[1] != "(") {
                return false;
            }

            $list = array();
            $level = 1;
            $not = "";
            while ($level) {
                $token = $this->_if_header_lexer($str, $pos);
                if ($token[0] == "NOT") {
                    $not = "!";
                    continue;
                }
                switch ($token[0]) {
                    case "CHAR":
                        switch ($token[1]) {
                            case "(":
                                $level++;
                                break;
                            case ")":
                                $level--;
                                break;
                            default:
                                return false;
                        }
                        break;

                    case "URI":
                        $list[] = $not."<$token[1]>";
                        break;

                    case "ETAG_WEAK":
                        $list[] = $not."[W/'$token[1]']>";
                        break;

                    case "ETAG_STRONG":
                        $list[] = $not."['$token[1]']>";
                        break;

                    default:
                        return false;
                }
                $not = "";
            }

            if (@is_array($uris[$uri])) {
                $uris[$uri] = array_merge($uris[$uri],$list);
            } else {
                $uris[$uri] = $list;
            }
        }

        return $uris;
    }

    /**
     * check if conditions from "If:" headers are meat 
     *
     * the "If:" header is an extension to HTTP/1.1
     * defined in RFC 2518 section 9.4
     *
     * @param  void
     * @return void
     */
    function _check_if_header_conditions() 
    {
        if (isset($_SERVER["HTTP_IF"])) {
            $this->_if_header_uris =
                $this->_if_header_parser($_SERVER["HTTP_IF"]);

            foreach($this->_if_header_uris as $uri => $conditions) {
                if ($uri == "") {
                    $uri = $this->uri;
                }
                // all must match
                $state = true;
                foreach($conditions as $condition) {
                    // lock tokens may be free form (RFC2518 6.3)
                    // but if opaquelocktokens are used (RFC2518 6.4)
                    // we have to check the format (litmus tests this)
                    if (!strncmp($condition, "<opaquelocktoken:", strlen("<opaquelocktoken"))) {
                        if (!ereg("^<opaquelocktoken:[[:xdigit:]]{8}-[[:xdigit:]]{4}-[[:xdigit:]]{4}-[[:xdigit:]]{4}-[[:xdigit:]]{12}>$", $condition)) {
                            return false;
                        }
                    }
                    if (!$this->_check_uri_condition($uri, $condition)) {
                        $state = false;
                        break;
                    }
                }

                // any match is ok
                if ($state == true) {
                    return true;
                }
            }
            return false;
        }
        return true;
    }

    /**
     * Check a single URI condition parsed from an if-header
     *
     * Check a single URI condition parsed from an if-header
     *
     * @abstract 
     * @param string $uri URI to check
     * @param string $condition Condition to check for this URI
     * @returns bool Condition check result
     */
    function _check_uri_condition($uri, $condition) 
    {
        // not really implemented here, 
        // implementations must override
        return true;
    }


    /**
     * 
     *
     * @param  string  path of resource to check
     * @param  bool    exclusive lock?
     */
    function _check_lock_status($path, $exclusive_only = false) 
    {
        // FIXME depth -> ignored for now
        if (method_exists($this, "checkLock")) {
            // is locked?
            $lock = $this->checkLock($path);

            // ... and lock is not owned?
            if (is_array($lock) && count($lock)) {
                // FIXME doesn't check uri restrictions yet
                if (!strstr($_SERVER["HTTP_IF"], $lock["token"])) {
                    if (!$exclusive_only || ($lock["scope"] !== "shared"))
                        return false;
                }
            }
        }
        return true;
    }


    // }}}


    /**
     * Generate lockdiscovery reply from checklock() result
     *
     * @param   string  resource path to check
     * @return  string  lockdiscovery response
     */
    function lockdiscovery($path) 
    {
        // no lock support without checklock() method
        if (!method_exists($this, "checklock")) {
            return "";
        }

        // collect response here
        $activelocks = "";

        // get checklock() reply
        $lock = $this->checklock($path);

        // generate <activelock> block for returned data
        if (is_array($lock) && count($lock)) {
            // check for 'timeout' or 'expires'
            if (!empty($lock["expires"])) {
                $timeout = "Second-".($lock["expires"] - time());
            } else if (!empty($lock["timeout"])) {
                $timeout = "Second-$lock[timeout]";
            } else {
                $timeout = "Infinite";
            }

            // genreate response block
            $activelocks.= "
              <D:activelock>
               <D:lockscope><D:$lock[scope]/></D:lockscope>
               <D:locktype><D:$lock[type]/></D:locktype>
               <D:depth>$lock[depth]</D:depth>
               <D:owner>$lock[owner]</D:owner>
               <D:timeout>$timeout</D:timeout>
               <D:locktoken><D:href>$lock[token]</D:href></D:locktoken>
              </D:activelock>
             ";
        }
	//$this->writelog('lockdiscovery('.$path.'):'.$activeclocks);

        // return generated response
        return $activelocks;
    }

    /**
     * set HTTP return status and mirror it in a private header
     *
     * @param  string  status code and message
     * @return void
     */
    function http_status($status) 
    {
        // simplified success case
        if($status === true) {
            $status = "200 OK";
        }
	//$this->writelog('http_status('.$status.')');
	
        // remember status
        $this->_http_status = $status;

        // generate HTTP status response
        header("HTTP/1.1 $status");
        header("X-WebDAV-Status: $status", true);
    }

    /**
     * private minimalistic version of PHP urlencode()
     *
     * only blanks and XML special chars must be encoded here
     * full urlencode() encoding confuses some clients ...
     *
     * @param  string  URL to encode
     * @return string  encoded URL
     */
    function _urlencode($url) 
    {
        return strtr($url, array(" "=>"%20",
                                 "&"=>"%26",
                                 "<"=>"%3C",
                                 ">"=>"%3E",
                                 ));
    }

    /**
     * private version of PHP urldecode
     *
     * not really needed but added for completenes
     *
     * @param  string  URL to decode
     * @return string  decoded URL
     */
    function _urldecode($path) 
    {
    	// BEGIN WebDAV
	// urldecode wrongly replaces '+' characters by ' ' characters.
	// We replace '+' into '%2b' before passing the path through urldecode.
        //return urldecode($path);
	$result =& urldecode(str_replace('+','%2b',$path));
	//$this->writelog('_urldecode('.$path.'):'.$result);
	return $result;
    	// END PATCH WebDAV
    }

    /**
     * UTF-8 encode property values if not already done so
     *
     * @param  string  text to encode
     * @return string  utf-8 encoded text
     */
    function _prop_encode($text) 
    {
        switch (strtolower($this->_prop_encoding)) {
        case "utf-8":
            return $text;
        case "iso-8859-1":
        case "iso-8859-15":
        case "latin-1":
        default:
            return utf8_encode($text);
        }
    }

    /**
     * Slashify - make sure path ends in a slash
     *
     * @param   string directory path
     * @returns string directory path wiht trailing slash
     */
    function _slashify($path) {
        if ($path[strlen($path)-1] != '/') {
            $path = $path."/";
        }
        return $path;
    }
// BEGIN WebDAV
        /**
         * Writes a message to the logfile.,
         *
         * @param  message String.
         * @return void.
         */
	private function writelog($message) 
	{
		global $DIC;
		$log = $DIC['log'];
		$ilUser = $DIC['ilUser'];
		
		$log->write(
			$ilUser->getLogin()
			.' DAV Server.'.str_replace("\n",";",$message)
		);
	}
// END PATCH WebDAV
}
?>
