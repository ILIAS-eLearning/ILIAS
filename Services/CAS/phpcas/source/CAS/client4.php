<?php

/**
 * @file CAS/client.php
 * Main class of the phpCAS library
 */

// include internationalization stuff
include_once(dirname(__FILE__).'/languages/languages.php');

// include PGT storage classes
include_once(dirname(__FILE__).'/PGTStorage/pgt-main.php');

// handle node name
function hnodename($name)
{
	if ($i = is_int(strpos($name, ":")))
	{
		return substr($name, $i);
	}
	else
	{
		return $name;
	}
}

/**
 * @class CASClient
 * The CASClient class is a client interface that provides CAS authentication
 * to PHP applications.
 *
 * @author Pascal Aubry <pascal.aubry at univ-rennes1.fr>
 */

class CASClient
{

  // XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX
  // XX                                                                    XX
  // XX                          CONFIGURATION                             XX
  // XX                                                                    XX
  // XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX

  // ########################################################################
  //  HTML OUTPUT
  // ########################################################################
  /**
   * @addtogroup internalOutput
   * @{
   */  
  
  /**
   * This method filters a string by replacing special tokens by appropriate values
   * and prints it. The corresponding tokens are taken into account:
   * - __CAS_VERSION__
   * - __PHPCAS_VERSION__
   * - __SERVER_BASE_URL__
   *
   * Used by CASClient::PrintHTMLHeader() and CASClient::printHTMLFooter().
   *
   * @param $str the string to filter and output
   *
   * @private
   */
  function HTMLFilterOutput($str)
    {
      $str = str_replace('__CAS_VERSION__',$this->getServerVersion(),$str);
      $str = str_replace('__PHPCAS_VERSION__',phpCAS::getVersion(),$str);
      $str = str_replace('__SERVER_BASE_URL__',$this->getServerBaseURL(),$str);
      echo $str;
    }

  /**
   * A string used to print the header of HTML pages. Written by CASClient::setHTMLHeader(),
   * read by CASClient::printHTMLHeader().
   *
   * @hideinitializer
   * @private
   * @see CASClient::setHTMLHeader, CASClient::printHTMLHeader()
   */
  var $_output_header = '';
  
  /**
   * This method prints the header of the HTML output (after filtering). If
   * CASClient::setHTMLHeader() was not used, a default header is output.
   *
   * @param $title the title of the page
   *
   * @see HTMLFilterOutput()
   * @private
   */
  function printHTMLHeader($title)
    {
      $this->HTMLFilterOutput(str_replace('__TITLE__',
					  $title,
					  (empty($this->_output_header)
					   ? '<html><head><title>__TITLE__</title></head><body><h1>__TITLE__</h1>'
					   : $this->output_header)
					  )
			      );
    }

  /**
   * A string used to print the footer of HTML pages. Written by CASClient::setHTMLFooter(),
   * read by printHTMLFooter().
   *
   * @hideinitializer
   * @private
   * @see CASClient::setHTMLFooter, CASClient::printHTMLFooter()
   */
  var $_output_footer = '';
  
  /**
   * This method prints the footer of the HTML output (after filtering). If
   * CASClient::setHTMLFooter() was not used, a default footer is output.
   *
   * @see HTMLFilterOutput()
   * @private
   */
  function printHTMLFooter()
    {
      $this->HTMLFilterOutput(empty($this->_output_footer)
			      ?('<hr><address>phpCAS __PHPCAS_VERSION__ '.$this->getString(CAS_STR_USING_SERVER).' <a href="__SERVER_BASE_URL__">__SERVER_BASE_URL__</a> (CAS __CAS_VERSION__)</a></address></body></html>')
			      :$this->_output_footer);
    }

  /**
   * This method set the HTML header used for all outputs.
   *
   * @param $header the HTML header.
   *
   * @public
   */
  function setHTMLHeader($header)
    {
      $this->_output_header = $header;
    }

  /**
   * This method set the HTML footer used for all outputs.
   *
   * @param $footer the HTML footer.
   *
   * @public
   */
  function setHTMLFooter($footer)
    {
      $this->_output_footer = $footer;
    }

  /** @} */
  // ########################################################################
  //  INTERNATIONALIZATION
  // ########################################################################
  /**
   * @addtogroup internalLang
   * @{
   */  
  /**
   * A string corresponding to the language used by phpCAS. Written by 
   * CASClient::setLang(), read by CASClient::getLang().

   * @note debugging information is always in english (debug purposes only).
   *
   * @hideinitializer
   * @private
   * @sa CASClient::_strings, CASClient::getString()
   */
  var $_lang = '';
  
  /**
   * This method returns the language used by phpCAS.
   *
   * @return a string representing the language
   *
   * @private
   */
  function getLang()
    {
      if ( empty($this->_lang) )
	$this->setLang(PHPCAS_LANG_DEFAULT);
      return $this->_lang;
    }

  /**
   * array containing the strings used by phpCAS. Written by CASClient::setLang(), read by 
   * CASClient::getString() and used by CASClient::setLang().
   *
   * @note This array is filled by instructions in CAS/languages/<$this->_lang>.php
   *
   * @private
   * @see CASClient::_lang, CASClient::getString(), CASClient::setLang(), CASClient::getLang()
   */
  var $_strings;

  /**
   * This method returns a string depending on the language.
   *
   * @param $str the index of the string in $_string.
   *
   * @return the string corresponding to $index in $string.
   *
   * @private
   */
  function getString($str)
    {
      // call CASclient::getLang() to be sure the language is initialized
      $this->getLang();
      
      if ( !isset($this->_strings[$str]) ) {
	trigger_error('string `'.$str.'\' not defined for language `'.$this->getLang().'\'',E_USER_ERROR);
      }
      return $this->_strings[$str];
    }

  /**
   * This method is used to set the language used by phpCAS. 
   * @note Can be called only once.
   *
   * @param $lang a string representing the language.
   *
   * @public
   * @sa CAS_LANG_FRENCH, CAS_LANG_ENGLISH
   */
  function setLang($lang)
    {
      // include the corresponding language file
      include_once(dirname(__FILE__).'/languages/'.$lang.'.php');

      if ( !is_array($this->_strings) ) {
	trigger_error('language `'.$lang.'\' is not implemented',E_USER_ERROR);
      }
      $this->_lang = $lang;
    }

  /** @} */
  // ########################################################################
  //  CAS SERVER CONFIG
  // ########################################################################
  /**
   * @addtogroup internalConfig
   * @{
   */  
  
  /**
   * a record to store information about the CAS server.
   * - $_server["version"]: the version of the CAS server
   * - $_server["hostname"]: the hostname of the CAS server
   * - $_server["port"]: the port the CAS server is running on
   * - $_server["uri"]: the base URI the CAS server is responding on
   * - $_server["base_url"]: the base URL of the CAS server
   * - $_server["login_url"]: the login URL of the CAS server
   * - $_server["service_validate_url"]: the service validating URL of the CAS server
   * - $_server["proxy_url"]: the proxy URL of the CAS server
   * - $_server["proxy_validate_url"]: the proxy validating URL of the CAS server
   * - $_server["logout_url"]: the logout URL of the CAS server
   *
   * $_server["version"], $_server["hostname"], $_server["port"] and $_server["uri"]
   * are written by CASClient::CASClient(), read by CASClient::getServerVersion(), 
   * CASClient::getServerHostname(), CASClient::getServerPort() and CASClient::getServerURI().
   *
   * The other fields are written and read by CASClient::getServerBaseURL(), 
   * CASClient::getServerLoginURL(), CASClient::getServerServiceValidateURL(), 
   * CASClient::getServerProxyValidateURL() and CASClient::getServerLogoutURL().
   *
   * @hideinitializer
   * @private
   */
  var $_server = array(
		       'version' => -1,
		       'hostname' => 'none',
		       'port' => -1,
		       'uri' => 'none'
		       );
  
  /**
   * This method is used to retrieve the version of the CAS server.
   * @return the version of the CAS server.
   * @private
   */
  function getServerVersion()
    { 
      return $this->_server['version']; 
    }

  /**
   * This method is used to retrieve the hostname of the CAS server.
   * @return the hostname of the CAS server.
   * @private
   */
  function getServerHostname()
    { return $this->_server['hostname']; }

  /**
   * This method is used to retrieve the port of the CAS server.
   * @return the port of the CAS server.
   * @private
   */
  function getServerPort()
    { return $this->_server['port']; }

  /**
   * This method is used to retrieve the URI of the CAS server.
   * @return a URI.
   * @private
   */
  function getServerURI()
    { return $this->_server['uri']; }

  /**
   * This method is used to retrieve the base URL of the CAS server.
   * @return a URL.
   * @private
   */
  function getServerBaseURL()
    { 
      // the URL is build only when needed
      if ( empty($this->_server['base_url']) ) {
	$this->_server['base_url'] = 'https://'
	  .$this->getServerHostname()
	  .':'
	  .$this->getServerPort()
	  .$this->getServerURI();
      }
      return $this->_server['base_url']; 
    }

  /**
   * This method is used to retrieve the login URL of the CAS server.
   * @param $gateway true to check authentication, false to force it
   * @return a URL.
   * @private
   */
  function getServerLoginURL($gateway)
    { 
      phpCAS::traceBegin();
      // the URL is build only when needed
      if ( empty($this->_server['login_url']) ) {
        $this->_server['login_url'] = $this->getServerBaseURL();
        $this->_server['login_url'] .= 'login?service=';
        $this->_server['login_url'] .= preg_replace('/&/','%26',$this->getURL());
        if ($gateway) {
          $this->_server['login_url'] .= '&gateway=true';
        }
      }
      phpCAS::traceEnd($this->_server['login_url']);
      return $this->_server['login_url']; 
    }

  /**
   * This method is used to retrieve the service validating URL of the CAS server.
   * @return a URL.
   * @private
   */
  function getServerServiceValidateURL()
    { 
      // the URL is build only when needed
      if ( empty($this->_server['service_validate_url']) ) {
	switch ($this->getServerVersion()) {
	case CAS_VERSION_1_0:
	  $this->_server['service_validate_url'] = $this->getServerBaseURL().'validate';
	  break;
	case CAS_VERSION_2_0:
	  $this->_server['service_validate_url'] = $this->getServerBaseURL().'serviceValidate';
	  break;
	}
      }
      return $this->_server['service_validate_url'].'?service='.preg_replace('/&/','%26',$this->getURL()); 
    }

  /**
   * This method is used to retrieve the proxy validating URL of the CAS server.
   * @return a URL.
   * @private
   */
  function getServerProxyValidateURL()
    { 
      // the URL is build only when needed
      if ( empty($this->_server['proxy_validate_url']) ) {
	switch ($this->getServerVersion()) {
	case CAS_VERSION_1_0:
	  $this->_server['proxy_validate_url'] = '';
	  break;
	case CAS_VERSION_2_0:
	  $this->_server['proxy_validate_url'] = $this->getServerBaseURL().'proxyValidate';
	  break;
	}
      }
      return $this->_server['proxy_validate_url'].'?service='.preg_replace('/&/','%26',$this->getURL()); 
    }

  /**
   * This method is used to retrieve the proxy URL of the CAS server.
   * @return a URL.
   * @private
   */
  function getServerProxyURL()
    { 
      // the URL is build only when needed
      if ( empty($this->_server['proxy_url']) ) {
	switch ($this->getServerVersion()) {
	case CAS_VERSION_1_0:
	  $this->_server['proxy_url'] = '';
	  break;
	case CAS_VERSION_2_0:
	  $this->_server['proxy_url'] = $this->getServerBaseURL().'proxy';
	  break;
	}
      }
      return $this->_server['proxy_url']; 
    }

  /**
   * This method is used to retrieve the logout URL of the CAS server.
   * @return a URL.
   * @private
   */
  function getServerLogoutURL()
    { 
      // the URL is build only when needed
      if ( empty($this->_server['logout_url']) ) {
	$this->_server['logout_url'] = $this->getServerBaseURL().'logout';
      }
      return $this->_server['logout_url']; 
    }

  // ########################################################################
  //  CONSTRUCTOR
  // ########################################################################
  /**
   * CASClient constructor.
   *
   * @param $server_version the version of the CAS server
   * @param $proxy TRUE if the CAS client is a CAS proxy, FALSE otherwise
   * @param $server_hostname the hostname of the CAS server
   * @param $server_port the port the CAS server is running on
   * @param $server_uri the URI the CAS server is responding on
   * @param $start_session Have phpCAS start PHP sessions (default true)
   *
   * @return a newly created CASClient object
   *
   * @public
   */
  function CASClient($server_version,
		     $proxy,
		     $server_hostname,
		     $server_port,
		     $server_uri,
		     $start_session = true)
    {
      phpCAS::traceBegin();

      // activate session mechanism if desired
      if ($start_session) {
           session_start();
      }

      $this->_proxy = $proxy;

      // check version
      switch ($server_version) {
      case CAS_VERSION_1_0:
	if ( $this->isProxy() )
	  phpCAS::error('CAS proxies are not supported in CAS '
			.$server_version);
	break;
      case CAS_VERSION_2_0:
	break;
      default:
	phpCAS::error('this version of CAS (`'
		      .$server_version
		      .'\') is not supported by phpCAS '
			.phpCAS::getVersion());
      }
      $this->_server['version'] = $server_version;

      // check hostname
      if ( empty($server_hostname) 
	   || !preg_match('/[\.\d\-abcdefghijklmnopqrstuvwxyz]*/',$server_hostname) ) {
	phpCAS::error('bad CAS server hostname (`'.$server_hostname.'\')');
      }
      $this->_server['hostname'] = $server_hostname;

      // check port
      if ( $server_port == 0 
	   || !is_int($server_port) ) {
	phpCAS::error('bad CAS server port (`'.$server_hostname.'\')');
      }
      $this->_server['port'] = $server_port;

      // check URI
      if ( !preg_match('/[\.\d\-_abcdefghijklmnopqrstuvwxyz\/]*/',$server_uri) ) {
	phpCAS::error('bad CAS server URI (`'.$server_uri.'\')');
      }
      // add leading and trailing `/' and remove doubles      
      $server_uri = preg_replace('/\/\//','/','/'.$server_uri.'/');
      $this->_server['uri'] = $server_uri;

      // set to callback mode if PgtIou and PgtId CGI GET parameters are provided 
      if ( $this->isProxy() ) {
	$this->setCallbackMode(!empty($_GET['pgtIou'])&&!empty($_GET['pgtId']));
      }

      if ( $this->isCallbackMode() ) {
	// callback mode: check that phpCAS is secured
	if ( $_SERVER['HTTPS'] != 'on' ) {
	  phpCAS::error('CAS proxies must be secured to use phpCAS; PGT\'s will not be received from the CAS server');
	}
      } else {
	// normal mode: get ticket and remove it from CGI parameters for developpers
	$ticket = $_GET['ticket'];
	// at first check for a Service Ticket
	if( preg_match('/^ST-/',$ticket)) {
	  phpCAS::trace('ST \''.$ticket.'\' found');
	  // ST present
	  $this->setST($ticket);
	} 
	// in a second time check for a Proxy Ticket (CAS >= 2.0)
	else if( ($this->getServerVersion()!=CAS_VERSION_1_0) && preg_match('/^PT-/',$ticket) ) {
	  phpCAS::trace('PT \''.$ticket.'\' found');
	  $this->setPT($ticket);
	} 
	// ill-formed ticket, halt
	else if ( !empty($ticket) ) {
	  phpCAS::error('ill-formed ticket found in the URL (ticket=`'.htmlentities($ticket).'\')');
	}
	// ticket has been taken into account, unset it to hide it to applications
	unset($_GET['ticket']);
      }
      phpCAS::traceEnd();
    }

  /** @} */

  // XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX
  // XX                                                                    XX
  // XX                           AUTHENTICATION                           XX
  // XX                                                                    XX
  // XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX

  /**
   * @addtogroup internalAuthentication
   * @{
   */  
  
  /**
   * The Authenticated user. Written by CASClient::setUser(), read by CASClient::getUser().
   * @attention client applications should use phpCAS::getUser().
   *
   * @hideinitializer
   * @private
   */
  var $_user = '';
  
  /**
   * This method sets the CAS user's login name.
   *
   * @param $user the login name of the authenticated user.
   *
   * @private
   */
  function setUser($user)
    {
      $this->_user = $user;
    }

  /**
   * This method returns the CAS user's login name.
   * @warning should be called only after CASClient::forceAuthentication() or 
   * CASClient::isAuthenticated(), otherwise halt with an error.
   *
   * @return the login name of the authenticated user
   */
  function getUser()
    {
      if ( empty($this->_user) ) {
	phpCAS::error('this method should be used only after '.__CLASS__.'::forceAuthentication() or '.__CLASS__.'::isAuthenticated()');
      }
      return $this->_user;
    }

  /**
   * This method is called to be sure that the user is authenticated. When not 
   * authenticated, halt by redirecting to the CAS server; otherwise return TRUE.
   * @return TRUE when the user is authenticated; otherwise halt.
   * @public
   */
  function forceAuthentication()
    {
      phpCAS::traceBegin();

      if ( $this->isAuthenticated() ) {
        // the user is authenticated, nothing to be done.
	    phpCAS::trace('no need to authenticate');
	    $res = TRUE;
      } else {
	    // the user is not authenticated, redirect to the CAS server
        unset($_SESSION['phpCAS']['auth_checked']);
	    $this->redirectToCas(FALSE/* no gateway */);	
	    // never reached
	    $res = FALSE;
      }
      phpCAS::traceEnd($res);
      return $res;
    }
  
  /**
   * This method is called to check whether the ser is authenticated or not.
   * @return TRUE when the user is authenticated, FALSE otherwise.
   * @public
   */
  function checkAuthentication()
    {
      phpCAS::traceBegin();

      if ( $this->isAuthenticated() ) {
	    phpCAS::trace('user is authenticated');
	    $res = TRUE;
      } else if (isset($_SESSION['phpCAS']['auth_checked'])) {
        // the previous request has redirected the client to the CAS server with gateway=true
        unset($_SESSION['phpCAS']['auth_checked']);
        $res = FALSE;
      } else {
        $_SESSION['phpCAS']['auth_checked'] = true;
	    $this->redirectToCas(TRUE/* gateway */);	
	    // never reached
	    $res = FALSE;
      }
      phpCAS::traceEnd($res);
      return $res;
    }
  
  /**
   * This method is called to check if the user is authenticated (previously or by
   * tickets given in the URL
   *
   * @return TRUE when the user is authenticated; otherwise halt.
   *
   * @public
   */
  function isAuthenticated()
    {
      phpCAS::traceBegin();
      $res = FALSE;
      $validate_url = '';

      if ( $this->wasPreviouslyAuthenticated() ) {
	// the user has already (previously during the session) been 
	// authenticated, nothing to be done.
	phpCAS::trace('user was already authenticated, no need to look for tickets');
	$res = TRUE;
      } elseif ( $this->hasST() ) {
	// if a Service Ticket was given, validate it
	phpCAS::trace('ST `'.$this->getST().'\' is present');
	$this->validateST($validate_url,$text_response,$tree_response); // if it fails, it halts
	phpCAS::trace('ST `'.$this->getST().'\' was validated');
	if ( $this->isProxy() ) {
	  $this->validatePGT($validate_url,$text_response,$tree_response); // idem
	  phpCAS::trace('PGT `'.$this->getPGT().'\' was validated');
	  $_SESSION['phpCAS']['pgt'] = $this->getPGT();
	}
	$_SESSION['phpCAS']['user'] = $this->getUser();
	$res = TRUE;
      } elseif ( $this->hasPT() ) {
	// if a Proxy Ticket was given, validate it
	phpCAS::trace('PT `'.$this->getPT().'\' is present');
	$this->validatePT($validate_url,$text_response,$tree_response); // note: if it fails, it halts
	phpCAS::trace('PT `'.$this->getPT().'\' was validated');
	if ( $this->isProxy() ) {
	  $this->validatePGT($validate_url,$text_response,$tree_response); // idem
	  phpCAS::trace('PGT `'.$this->getPGT().'\' was validated');
	  $_SESSION['phpCAS']['pgt'] = $this->getPGT();
	}
	$_SESSION['phpCAS']['user'] = $this->getUser();
	$res = TRUE;
      } else {
	// no ticket given, not authenticated
	phpCAS::trace('no ticket found');
      }

      phpCAS::traceEnd($res);
      return $res;
    }
  
  /**
   * This method tells if the user has already been (previously) authenticated
   * by looking into the session variables.
   *
   * @note This function switches to callback mode when needed.
   *
   * @return TRUE when the user has already been authenticated; FALSE otherwise.
   *
   * @private
   */
  function wasPreviouslyAuthenticated()
    {
      phpCAS::traceBegin();

      if ( $this->isCallbackMode() ) {
	$this->callback();
      }

      $auth = FALSE;

      if ( $this->isProxy() ) {
	// CAS proxy: username and PGT must be present
	if ( !empty($_SESSION['phpCAS']['user']) && !empty($_SESSION['phpCAS']['pgt']) ) {
	  // authentication already done
	  $this->setUser($_SESSION['phpCAS']['user']);
	  $this->setPGT($_SESSION['phpCAS']['pgt']);
	  phpCAS::trace('user = `'.$_SESSION['phpCAS']['user'].'\', PGT = `'.$_SESSION['phpCAS']['pgt'].'\''); 
	  $auth = TRUE;
	} elseif ( !empty($_SESSION['phpCAS']['user']) && empty($_SESSION['phpCAS']['pgt']) ) {
	  // these two variables should be empty or not empty at the same time
	  phpCAS::trace('username found (`'.$_SESSION['phpCAS']['user'].'\') but PGT is empty');
	  // unset all tickets to enforce authentication
	  unset($_SESSION['phpCAS']);
	  $this->setST('');
	  $this->setPT('');
	} elseif ( empty($_SESSION['phpCAS']['user']) && !empty($_SESSION['phpCAS']['pgt']) ) {
	  // these two variables should be empty or not empty at the same time
	  phpCAS::trace('PGT found (`'.$_SESSION['phpCAS']['pgt'].'\') but username is empty'); 
	  // unset all tickets to enforce authentication
	  unset($_SESSION['phpCAS']);
	  $this->setST('');
	  $this->setPT('');
	} else {
	  phpCAS::trace('neither user not PGT found'); 
	}
      } else {
	// `simple' CAS client (not a proxy): username must be present
	if ( !empty($_SESSION['phpCAS']['user']) ) {
	  // authentication already done
	  $this->setUser($_SESSION['phpCAS']['user']);
	  phpCAS::trace('user = `'.$_SESSION['phpCAS']['user'].'\''); 
	  $auth = TRUE;
	} else {
	  phpCAS::trace('no user found');
	}
      }
      
      phpCAS::traceEnd($auth);
      return $auth;
    }
  
  /**
   * This method is used to redirect the client to the CAS server.
   * It is used by CASClient::forceAuthentication() and CASClient::checkAuthentication().
   * @param $gateway true to check authentication, false to force it
   * @public
   */
  function redirectToCas($gateway)
    {
      phpCAS::traceBegin();
      $cas_url = $this->getServerLoginURL($gateway);
      header('Location: '.$cas_url);
      $this->printHTMLHeader($this->getString(CAS_STR_AUTHENTICATION_WANTED));
      printf('<p>'.$this->getString(CAS_STR_SHOULD_HAVE_BEEN_REDIRECTED).'</p>',$cas_url);
      $this->printHTMLFooter();
      phpCAS::traceExit();
      exit();
    }
  
  /**
   * This method is used to logout from CAS.
   * @param $url a URL that will be transmitted to the CAS server (to come back to when logged out)
   * @public
   */
  function logout($url = "")
    {
      phpCAS::traceBegin();
      $cas_url = $this->getServerLogoutURL();
      // v0.4.14 sebastien.gougeon at univ-rennes1.fr
      // header('Location: '.$cas_url);
      if ( $url != "" ) {
        $url = '?service=' . $url;
      }
      header('Location: '.$cas_url . $url);
      session_unset();
      session_destroy();
      $this->printHTMLHeader($this->getString(CAS_STR_LOGOUT));
      printf('<p>'.$this->getString(CAS_STR_SHOULD_HAVE_BEEN_REDIRECTED).'</p>',$cas_url);
      $this->printHTMLFooter();
      phpCAS::traceExit();
      exit();
    }
  
  /** @} */

  // XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX
  // XX                                                                    XX
  // XX                  BASIC CLIENT FEATURES (CAS 1.0)                   XX
  // XX                                                                    XX
  // XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX

  // ########################################################################
  //  ST
  // ########################################################################
  /**
   * @addtogroup internalBasic
   * @{
   */  
  
  /**
   * the Service Ticket provided in the URL of the request if present
   * (empty otherwise). Written by CASClient::CASClient(), read by 
   * CASClient::getST() and CASClient::hasPGT().
   *
   * @hideinitializer
   * @private
   */
  var $_st = '';
  
  /**
   * This method returns the Service Ticket provided in the URL of the request.
   * @return The service ticket.
   * @private
   */
  function getST()
    { return $this->_st; }

  /**
   * This method stores the Service Ticket.
   * @param $st The Service Ticket.
   * @private
   */
  function setST($st)
    { $this->_st = $st; }

  /**
   * This method tells if a Service Ticket was stored.
   * @return TRUE if a Service Ticket has been stored.
   * @private
   */
  function hasST()
    { return !empty($this->_st); }

  /** @} */

  // ########################################################################
  //  ST VALIDATION
  // ########################################################################
  /**
   * @addtogroup internalBasic
   * @{
   */  

  /**
   * This method is used to validate a ST; halt on failure, and sets $validate_url,
   * $text_reponse and $tree_response on success. These parameters are used later
   * by CASClient::validatePGT() for CAS proxies.
   * 
   * @param $validate_url the URL of the request to the CAS server.
   * @param $text_response the response of the CAS server, as is (XML text).
   * @param $tree_response the response of the CAS server, as a DOM XML tree.
   *
   * @return bool TRUE when successfull, halt otherwise by calling CASClient::authError().
   *
   * @private
   */
  function validateST($validate_url,&$text_response,&$tree_response)
    {
      phpCAS::traceBegin();
      // build the URL to validate the ticket
      $validate_url = $this->getServerServiceValidateURL().'&ticket='.$this->getST();
      if ( $this->isProxy() ) {
	// pass the callback url for CAS proxies
	$validate_url .= '&pgtUrl='.$this->getCallbackURL();
      }

      // open and read the URL
      if ( !$this->readURL($validate_url,''/*cookies*/,$headers,$text_response,$err_msg) ) {
	phpCAS::trace('could not open URL \''.$validate_url.'\' to validate ('.$err_msg.')');
	$this->authError('ST not validated (1)',
			 $validate_url,
			 TRUE/*$no_response*/);
      }

      // analyze the result depending on the version
      switch ($this->getServerVersion()) {
      case CAS_VERSION_1_0:
	if (preg_match('/^no\n/',$text_response)) {
	  phpCAS::trace('ST has not been validated');
	  $this->authError('ST not validated (2)',
		       $validate_url,
		       FALSE/*$no_response*/,
		       FALSE/*$bad_response*/,
		       $text_response);
	}
	if (!preg_match('/^yes\n/',$text_response)) {
	  phpCAS::trace('ill-formed response');
	  $this->authError('ST not validated (3)',
		       $validate_url,
		       FALSE/*$no_response*/,
		       TRUE/*$bad_response*/,
		       $text_response);
	}
	// ST has been validated, extract the user name
	$arr = preg_split('/\n/',$text_response);
	$this->setUser(trim($arr[1]));
	break;
      case CAS_VERSION_2_0:
	// read the response of the CAS server into a DOM object
	if ( !($dom = domxml_open_mem($text_response))) {
	  phpCAS::trace('domxml_open_mem() failed');
	  $this->authError('ST not validated (4)',
		       $validate_url,
		       FALSE/*$no_response*/,
		       TRUE/*$bad_response*/,
		       $text_response);
	}
	// read the root node of the XML tree
	if ( !($tree_response = $dom->document_element()) ) {
	  phpCAS::trace('document_element() failed');
	  $this->authError('ST not validated (5)',
		       $validate_url,
		       FALSE/*$no_response*/,
		       TRUE/*$bad_response*/,
		       $text_response);
	}
	// insure that tag name is 'serviceResponse'
	if ( hnodename($tree_response->node_name()) != 'serviceResponse' ) {
	  phpCAS::trace('bad XML root node (should be `serviceResponse\' instead of `'.hnodename($tree_response->node_name()).'\'');
	  $this->authError('ST not validated (6)',
		       $validate_url,
		       FALSE/*$no_response*/,
		       TRUE/*$bad_response*/,
		       $text_response);
	}
	if ( sizeof($success_elements = $tree_response->get_elements_by_tagname("authenticationSuccess")) != 0) {
	  // authentication succeded, extract the user name
	  if ( sizeof($user_elements = $success_elements[0]->get_elements_by_tagname("user")) == 0) {
	    phpCAS::trace('<authenticationSuccess> found, but no <user>');
	    $this->authError('ST not validated (7)',
			 $validate_url,
			 FALSE/*$no_response*/,
			 TRUE/*$bad_response*/,
			 $text_response);
	  }
	  $user = trim($user_elements[0]->get_content());
	  phpCAS::trace('user = `'.$user);
	  $this->setUser($user);
	  
	} else if ( sizeof($failure_elements = $tree_response->get_elements_by_tagname("authenticationFailure")) != 0) {
	  phpCAS::trace('<authenticationFailure> found');
	  // authentication failed, extract the error code and message
	  $this->authError('ST not validated (8)',
		       $validate_url,
		       FALSE/*$no_response*/,
		       FALSE/*$bad_response*/,
		       $text_response,
		       $failure_elements[0]->get_attribute('code')/*$err_code*/,
		       trim($failure_elements[0]->get_content())/*$err_msg*/);
	} else {
	  phpCAS::trace('neither <authenticationSuccess> nor <authenticationFailure> found');
	  $this->authError('ST not validated (9)',
		       $validate_url,
		       FALSE/*$no_response*/,
		       TRUE/*$bad_response*/,
		       $text_response);
	}
	break;
      }
      
      // at this step, ST has been validated and $this->_user has been set,
      phpCAS::traceEnd(TRUE);
      return TRUE;
    }

  /** @} */

  // XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX
  // XX                                                                    XX
  // XX                     PROXY FEATURES (CAS 2.0)                       XX
  // XX                                                                    XX
  // XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX

  // ########################################################################
  //  PROXYING
  // ########################################################################
  /**
   * @addtogroup internalProxy
   * @{
   */

  /**
   * A boolean telling if the client is a CAS proxy or not. Written by CASClient::CASClient(), 
   * read by CASClient::isProxy().
   *
   * @private
   */
  var $_proxy;
  
  /**
   * Tells if a CAS client is a CAS proxy or not
   *
   * @return TRUE when the CAS client is a CAs proxy, FALSE otherwise
   *
   * @private
   */
  function isProxy()
    {
      return $this->_proxy;
    }

  /** @} */
  // ########################################################################
  //  PGT
  // ########################################################################
  /**
   * @addtogroup internalProxy
   * @{
   */  
  
  /**
   * the Proxy Grnting Ticket given by the CAS server (empty otherwise). 
   * Written by CASClient::setPGT(), read by CASClient::getPGT() and CASClient::hasPGT().
   *
   * @hideinitializer
   * @private
   */
  var $_pgt = '';
  
  /**
   * This method returns the Proxy Granting Ticket given by the CAS server.
   * @return The Proxy Granting Ticket.
   * @private
   */
  function getPGT()
    { return $this->_pgt; }

  /**
   * This method stores the Proxy Granting Ticket.
   * @param $pgt The Proxy Granting Ticket.
   * @private
   */
  function setPGT($pgt)
    { $this->_pgt = $pgt; }

  /**
   * This method tells if a Proxy Granting Ticket was stored.
   * @return TRUE if a Proxy Granting Ticket has been stored.
   * @private
   */
  function hasPGT()
    { return !empty($this->_pgt); }

  /** @} */

  // ########################################################################
  //  CALLBACK MODE
  // ########################################################################
  /**
   * @addtogroup internalCallback
   * @{
   */  
  /**
   * each PHP script using phpCAS in proxy mode is its own callback to get the
   * PGT back from the CAS server. callback_mode is detected by the constructor
   * thanks to the GET parameters.
   */

  /**
   * a boolean to know if the CAS client is running in callback mode. Written by
   * CASClient::setCallBackMode(), read by CASClient::isCallbackMode().
   *
   * @hideinitializer
   * @private
   */
  var $_callback_mode = FALSE;
  
  /**
   * This method sets/unsets callback mode.
   *
   * @param $callback_mode TRUE to set callback mode, FALSE otherwise.
   *
   * @private
   */
  function setCallbackMode($callback_mode)
    {
      $this->_callback_mode = $callback_mode;
    }

  /**
   * This method returns TRUE when the CAs client is running i callback mode, 
   * FALSE otherwise.
   *
   * @return A boolean.
   *
   * @private
   */
  function isCallbackMode()
    {
      return $this->_callback_mode;
    }

  /**
   * the URL that should be used for the PGT callback (in fact the URL of the 
   * current request without any CGI parameter). Written and read by 
   * CASClient::getCallbackURL().
   *
   * @hideinitializer
   * @private
   */
  var $_callback_url = '';

  /**
   * This method returns the URL that should be used for the PGT callback (in
   * fact the URL of the current request without any CGI parameter, except if
   * phpCAS::setFixedCallbackURL() was used).
   *
   * @return The callback URL
   *
   * @private
   */
  function getCallbackURL()
    {
      // the URL is built when needed only
      if ( empty($this->_callback_url) ) {
        $final_uri = '';
	    // remove the ticket if present in the URL
	    $final_uri = 'https://';
	    /* replaced by Julien Marchal - v0.4.6
	     * $this->uri .= $_SERVER['SERVER_NAME'];
	     */
        if(empty($_SERVER['HTTP_X_FORWARDED_SERVER'])){
          /* replaced by teedog - v0.4.12
           * $final_uri .= $_SERVER['SERVER_NAME'];
           */
          if (empty($_SERVER['SERVER_NAME'])) {
            $final_uri .= $_SERVER['HTTP_HOST'];
          } else {
            $final_uri .= $_SERVER['SERVER_NAME'];
          }
        } else {
          $final_uri .= $_SERVER['HTTP_X_FORWARDED_SERVER'];
        }
	    if ( ($_SERVER['HTTPS']=='on' && $_SERVER['SERVER_PORT']!=443)
	       || ($_SERVER['HTTPS']!='on' && $_SERVER['SERVER_PORT']!=80) ) {
	      $final_uri .= ':';
	      $final_uri .= $_SERVER['SERVER_PORT'];
	    }
	    $request_uri = $_SERVER['REQUEST_URI'];
	    $request_uri = preg_replace('/\?.*$/','',$request_uri);
	    $final_uri .= $request_uri;
	    $this->setCallbackURL($final_uri);
      }
      return $this->_callback_url;
    }

  /**
   * This method sets the callback url.
   *
   * @param $callback_url url to set callback 
   *
   * @private
   */
  function setCallbackURL($url)
    {
      return $this->_callback_url = $url;
    }

  /**
   * This method is called by CASClient::CASClient() when running in callback
   * mode. It stores the PGT and its PGT Iou, prints its output and halts.
   *
   * @private
   */
  function callback()
    {
      phpCAS::traceBegin();
      $this->printHTMLHeader('phpCAS callback');
      $pgt_iou = $_GET['pgtIou'];
      $pgt = $_GET['pgtId'];
      phpCAS::trace('Storing PGT `'.$pgt.'\' (id=`'.$pgt_iou.'\')');
      echo '<p>Storing PGT `'.$pgt.'\' (id=`'.$pgt_iou.'\').</p>';
      $this->storePGT($pgt,$pgt_iou);
      $this->printHTMLFooter();
      phpCAS::traceExit();
    }

  /** @} */

  // ########################################################################
  //  PGT STORAGE
  // ########################################################################
  /**
   * @addtogroup internalPGTStorage
   * @{
   */  
    
  /**
   * an instance of a class inheriting of PGTStorage, used to deal with PGT
   * storage. Created by CASClient::setPGTStorageFile() or CASClient::setPGTStorageDB(), used 
   * by CASClient::setPGTStorageFile(), CASClient::setPGTStorageDB() and CASClient::initPGTStorage().
   *
   * @hideinitializer
   * @private
   */
  var $_pgt_storage = null;

  /**
   * This method is used to initialize the storage of PGT's.
   * Halts on error.
   *
   * @private
   */
  function initPGTStorage()
    {
      // if no SetPGTStorageXxx() has been used, default to file
      if ( !is_object($this->_pgt_storage) ) {
	$this->setPGTStorageFile();
      }

      // initializes the storage
      $this->_pgt_storage->init();
    }
  
  /**
   * This method stores a PGT. Halts on error.
   *
   * @param $pgt the PGT to store
   * @param $pgt_iou its corresponding Iou
   *
   * @private
   */
  function storePGT($pgt,$pgt_iou)
    {
      // ensure that storage is initialized
      $this->initPGTStorage();
      // writes the PGT
      $this->_pgt_storage->write($pgt,$pgt_iou);
    }
  
  /**
   * This method reads a PGT from its Iou and deletes the corresponding storage entry.
   *
   * @param $pgt_iou the PGT Iou
   *
   * @return The PGT corresponding to the Iou, FALSE when not found.
   *
   * @private
   */
  function loadPGT($pgt_iou)
    {
      // ensure that storage is initialized
      $this->initPGTStorage();
      // read the PGT
      return $this->_pgt_storage->read($pgt_iou);
    }
  
  /**
   * This method is used to tell phpCAS to store the response of the
   * CAS server to PGT requests onto the filesystem. 
   *
   * @param $format the format used to store the PGT's (`plain' and `xml' allowed)
   * @param $path the path where the PGT's should be stored
   *
   * @public
   */
  function setPGTStorageFile($format='',
			     $path='')
    {
      // check that the storage has not already been set
      if ( is_object($this->_pgt_storage) ) {
	phpCAS::error('PGT storage already defined');
      }

      // create the storage object
      $this->_pgt_storage = &new PGTStorageFile($this,$format,$path);
    }
  
  /**
   * This method is used to tell phpCAS to store the response of the
   * CAS server to PGT requests into a database. 
   * @note The connection to the database is done only when needed. 
   * As a consequence, bad parameters are detected only when 
   * initializing PGT storage.
   *
   * @param $user the user to access the data with
   * @param $password the user's password
   * @param $database_type the type of the database hosting the data
   * @param $hostname the server hosting the database
   * @param $port the port the server is listening on
   * @param $database the name of the database
   * @param $table the name of the table storing the data
   *
   * @public
   */
  function setPGTStorageDB($user,
			   $password,
			   $database_type,
			   $hostname,
			   $port,
			   $database,
			   $table)
    {
      // check that the storage has not already been set
      if ( is_object($this->_pgt_storage) ) {
	phpCAS::error('PGT storage already defined');
      }

      // warn the user that he should use file storage...
      trigger_error('PGT storage into database is an experimental feature, use at your own risk',E_USER_WARNING);

      // create the storage object
      $this->_pgt_storage = & new PGTStorageDB($this,$user,$password,$database_type,$hostname,$port,$database,$table);
    }
  
  // ########################################################################
  //  PGT VALIDATION
  // ########################################################################
  /**
   * This method is used to validate a PGT; halt on failure.
   * 
   * @param $validate_url the URL of the request to the CAS server.
   * @param $text_response the response of the CAS server, as is (XML text); result
   * of CASClient::validateST() or CASClient::validatePT().
   * @param $tree_response the response of the CAS server, as a DOM XML tree; result
   * of CASClient::validateST() or CASClient::validatePT().
   *
   * @return bool TRUE when successfull, halt otherwise by calling CASClient::authError().
   *
   * @private
   */
  function validatePGT(&$validate_url,$text_response,$tree_response)
    {
      phpCAS::traceBegin();
      if ( sizeof($arr = $tree_response->get_elements_by_tagname("proxyGrantingTicket")) == 0) {
	phpCAS::trace('<proxyGrantingTicket> not found');
	// authentication succeded, but no PGT Iou was transmitted
	$this->authError('Ticket validated but no PGT Iou transmitted',
		     $validate_url,
		     FALSE/*$no_response*/,
		     FALSE/*$bad_response*/,
		     $text_response);
      } else {
	// PGT Iou transmitted, extract it
	$pgt_iou = trim($arr[0]->get_content());
	$pgt = $this->loadPGT($pgt_iou);
	if ( $pgt == FALSE ) {
	  phpCAS::trace('could not load PGT');
	  $this->authError('PGT Iou was transmitted but PGT could not be retrieved',
		       $validate_url,
		       FALSE/*$no_response*/,
		       FALSE/*$bad_response*/,
		       $text_response);
	}
	$this->setPGT($pgt);
      }
      phpCAS::traceEnd(TRUE);
      return TRUE;
    }

  // ########################################################################
  //  PGT VALIDATION
  // ########################################################################

  /**
   * This method is used to retrieve PT's from the CAS server thanks to a PGT.
   * 
   * @param $target_service the service to ask for with the PT.
   * @param $err_code an error code (PHPCAS_SERVICE_OK on success).
   * @param $err_msg an error message (empty on success).
   *
   * @return a Proxy Ticket, or FALSE on error.
   *
   * @private
   */
  function retrievePT($target_service,&$err_code,&$err_msg)
    {
      phpCAS::traceBegin();

      // by default, $err_msg is set empty and $pt to TRUE. On error, $pt is
      // set to false and $err_msg to an error message. At the end, if $pt is FALSE 
      // and $error_msg is still empty, it is set to 'invalid response' (the most
      // commonly encountered error).
      $err_msg = '';

      // build the URL to retrieve the PT
      $cas_url = $this->getServerProxyURL().'?targetService='.preg_replace('/&/','%26',$target_service).'&pgt='.$this->getPGT();

      // open and read the URL
      if ( !$this->readURL($cas_url,''/*cookies*/,$headers,$cas_response,$err_msg) ) {
	phpCAS::trace('could not open URL \''.$cas_url.'\' to validate ('.$err_msg.')');
	$err_code = PHPCAS_SERVICE_PT_NO_SERVER_RESPONSE;
	$err_msg = 'could not retrieve PT (no response from the CAS server)';
	phpCAS::traceEnd(FALSE);
	return FALSE;
      }

      $bad_response = FALSE;

      if ( !$bad_response ) {
	// read the response of the CAS server into a DOM object
	if ( !($dom = @domxml_open_mem($cas_response))) {
	  phpCAS::trace('domxml_open_mem() failed');
	  // read failed
	  $bad_response = TRUE;
	} 
      }

      if ( !$bad_response ) {
	// read the root node of the XML tree
	if ( !($root = $dom->document_element()) ) {
	  phpCAS::trace('document_element() failed');
	  // read failed
	  $bad_response = TRUE;
	} 
      }

      if ( !$bad_response ) {
	// insure that tag name is 'serviceResponse'
	if ( hnodename($root->node_name()) != 'serviceResponse' ) {
	  phpCAS::trace('node_name() failed');
	  // bad root node
	  $bad_response = TRUE;
	} 
      }

      if ( !$bad_response ) {
	// look for a proxySuccess tag
	if ( sizeof($arr = $root->get_elements_by_tagname("proxySuccess")) != 0) {
	  // authentication succeded, look for a proxyTicket tag
	  if ( sizeof($arr = $root->get_elements_by_tagname("proxyTicket")) != 0) {
	    $err_code = PHPCAS_SERVICE_OK;
	    $err_msg = '';
	    $pt = trim($arr[0]->get_content());
	    phpCAS::traceEnd($pt);
	    return $pt;
	  } else {
	    phpCAS::trace('<proxySuccess> was found, but not <proxyTicket>');
	  }
	} 
	// look for a proxyFailure tag
	else if ( sizeof($arr = $root->get_elements_by_tagname("proxyFailure")) != 0) {
	  // authentication failed, extract the error
	  $err_code = PHPCAS_SERVICE_PT_FAILURE;
	  $err_msg = 'PT retrieving failed (code=`'
	    .$arr[0]->get_attribute('code')
	    .'\', message=`'
	    .trim($arr[0]->get_content())
	    .'\')';
	  phpCAS::traceEnd(FALSE);
	  return FALSE;
	} else {
	  phpCAS::trace('neither <proxySuccess> nor <proxyFailure> found');
	}
      }

      // at this step, we are sure that the response of the CAS server was ill-formed
      $err_code = PHPCAS_SERVICE_PT_BAD_SERVER_RESPONSE;
      $err_msg = 'Invalid response from the CAS server (response=`'.$cas_response.'\')';

      phpCAS::traceEnd(FALSE);
      return FALSE;
    }

  // ########################################################################
  // ACCESS TO EXTERNAL SERVICES
  // ########################################################################

  /**
   * This method is used to acces a remote URL.
   *
   * @param $url the URL to access.
   * @param $cookies an array containing cookies strings such as 'name=val'
   * @param $headers an array containing the HTTP header lines of the response
   * (an empty array on failure).
   * @param $body the body of the response, as a string (empty on failure).
   * @param $err_msg an error message, filled on failure.
   *
   * @return TRUE on success, FALSE otherwise (in this later case, $err_msg
   * contains an error message).
   *
   * @private
   */
  function readURL($url,$cookies,&$headers,&$body,&$err_msg)
    {
      phpCAS::traceBegin();
      $headers = '';
      $body = '';
      $err_msg = '';

      $res = TRUE;

      // initialize the CURL session
      $ch = curl_init($url);
	
	  // verify the the server's certificate corresponds to its name
	  curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 1);
	  // but do not verify the certificate itself
	  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

      // return the CURL output into a variable
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
      // include the HTTP header with the body
      curl_setopt($ch, CURLOPT_HEADER, 1);
      // add cookies headers
      if ( is_array($cookies) ) {
	curl_setopt($ch,CURLOPT_COOKIE,implode(';',$cookies));
      }
      // perform the query
      $buf = curl_exec ($ch);
      if ( $buf === FALSE ) {
	phpCAS::trace('cur_exec() failed');
	$err_msg = 'CURL error #'.curl_errno($ch).': '.curl_error($ch);
	// close the CURL session
	curl_close ($ch);
	$res = FALSE;
      } else {
	// close the CURL session
	curl_close ($ch);
	
	// find the end of the headers
	// note: strpos($str,"\n\r\n\r") does not work (?)
	$pos = FALSE;
	for ($i=0; $i<strlen($buf); $i++) {
	  if ( $buf[$i] == chr(13) ) 
	    if ( $buf[$i+1] == chr(10) ) 
	      if ( $buf[$i+2] == chr(13) ) 
		if ( $buf[$i+3] == chr(10) ) {
		  // header found
		  $pos = $i;
		  break;
		}
	}
	
	if ( $pos === FALSE ) {
	  // end of header not found
	  $err_msg = 'no header found';
	  phpCAS::trace($err_msg);
	  $res = FALSE;
	} else { 
	  // extract headers into an array
	  $headers = preg_split ("/[\n\r]+/",substr($buf,0,$pos));	  
	  // extract body into a string
	  $body = substr($buf,$pos+4);
	}
      }

      phpCAS::traceEnd($res);
      return $res;
    }

  /**
   * This method is used to access an HTTP[S] service.
   * 
   * @param $url the service to access.
   * @param $err_code an error code Possible values are PHPCAS_SERVICE_OK (on
   * success), PHPCAS_SERVICE_PT_NO_SERVER_RESPONSE, PHPCAS_SERVICE_PT_BAD_SERVER_RESPONSE,
   * PHPCAS_SERVICE_PT_FAILURE, PHPCAS_SERVICE_NOT AVAILABLE.
   * @param $output the output of the service (also used to give an error
   * message on failure).
   *
   * @return TRUE on success, FALSE otherwise (in this later case, $err_code
   * gives the reason why it failed and $output contains an error message).
   *
   * @public
   */
  function serviceWeb($url,&$err_code,&$output)
    {
      phpCAS::traceBegin();
      // at first retrieve a PT
      $pt = $this->retrievePT($url,$err_code,$output);

      $res = TRUE;
      
      // test if PT was retrieved correctly
      if ( !$pt ) {
	// note: $err_code and $err_msg are filled by CASClient::retrievePT()
	phpCAS::trace('PT was not retrieved correctly');
	$res = FALSE;
      } else {
	// add cookies if necessary
	if ( is_array($_SESSION['phpCAS']['services'][$url]['cookies']) ) {
	  foreach ( $_SESSION['phpCAS']['services'][$url]['cookies'] as $name => $val ) { 
	    $cookies[] = $name.'='.$val;
	  }
	}
	
	// build the URL including the PT
	if ( strstr($url,'?') === FALSE ) {
	  $service_url = $url.'?ticket='.$pt;
	} else {
	  $service_url = $url.'&ticket='.$pt;
	}
	
	phpCAS::trace('reading URL`'.$service_url.'\'');
	if ( !$this->readURL($service_url,$cookies,$headers,$output,$err_msg) ) {
	  phpCAS::trace('could not read URL`'.$service_url.'\'');
	  $err_code = PHPCAS_SERVICE_NOT_AVAILABLE;
	  // give an error message
	  $output = sprintf($this->getString(CAS_STR_SERVICE_UNAVAILABLE),
			    $service_url,
			    $err_msg);
	  $res = FALSE;
	} else {
	  // URL has been fetched, extract the cookies
	  phpCAS::trace('URL`'.$service_url.'\' has been read, storing cookies:');
	  foreach ( $headers as $header ) {
	    // test if the header is a cookie
	    if ( preg_match('/^Set-Cookie:/',$header) ) {
	      // the header is a cookie, remove the beginning
	      $header_val = preg_replace('/^Set-Cookie: */','',$header);
	      // extract interesting information
	      $name_val = strtok($header_val,'; ');
	      // extract the name and the value of the cookie
	      $cookie_name = strtok($name_val,'=');
	      $cookie_val = strtok('=');
	      // store the cookie 
	      $_SESSION['phpCAS']['services'][$url]['cookies'][$cookie_name] = $cookie_val;
	      phpCAS::trace($cookie_name.' -> '.$cookie_val);
	    }
	  }
	}
      }

      phpCAS::traceEnd($res);
      return $res;
  }

  /**
   * This method is used to access an IMAP/POP3/NNTP service.
   * 
   * @param $url a string giving the URL of the service, including the mailing box
   * for IMAP URLs, as accepted by imap_open().
   * @param $flags options given to imap_open().
   * @param $err_code an error code Possible values are PHPCAS_SERVICE_OK (on
   * success), PHPCAS_SERVICE_PT_NO_SERVER_RESPONSE, PHPCAS_SERVICE_PT_BAD_SERVER_RESPONSE,
   * PHPCAS_SERVICE_PT_FAILURE, PHPCAS_SERVICE_NOT AVAILABLE.
   * @param $err_msg an error message on failure
   * @param $pt the Proxy Ticket (PT) retrieved from the CAS server to access the URL
   * on success, FALSE on error).
   *
   * @return an IMAP stream on success, FALSE otherwise (in this later case, $err_code
   * gives the reason why it failed and $err_msg contains an error message).
   *
   * @public
   */
  function serviceMail($url,$flags,&$err_code,&$err_msg,&$pt)
    {
      phpCAS::traceBegin();
      // at first retrieve a PT
      $pt = $this->retrievePT($target_service,$err_code,$output);

      $stream = FALSE;
      
      // test if PT was retrieved correctly
      if ( !$pt ) {
	// note: $err_code and $err_msg are filled by CASClient::retrievePT()
	phpCAS::trace('PT was not retrieved correctly');
      } else {
	phpCAS::trace('opening IMAP URL `'.$url.'\'...');
	$stream = @imap_open($url,$this->getUser(),$pt,$flags);
	if ( !$stream ) {
	  phpCAS::trace('could not open URL');
	  $err_code = PHPCAS_SERVICE_NOT_AVAILABLE;
	  // give an error message
	  $err_msg = sprintf($this->getString(CAS_STR_SERVICE_UNAVAILABLE),
			     $service_url,
			     var_export(imap_errors(),TRUE));
	  $pt = FALSE;
	  $stream = FALSE;
	} else {
	  phpCAS::trace('ok');
	}
      }

      phpCAS::traceEnd($stream);
      return $stream;
  }

  /** @} */

  // XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX
  // XX                                                                    XX
  // XX                  PROXIED CLIENT FEATURES (CAS 2.0)                 XX
  // XX                                                                    XX
  // XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX

  // ########################################################################
  //  PT
  // ########################################################################
  /**
   * @addtogroup internalProxied
   * @{
   */  
  
  /**
   * the Proxy Ticket provided in the URL of the request if present
   * (empty otherwise). Written by CASClient::CASClient(), read by 
   * CASClient::getPT() and CASClient::hasPGT().
   *
   * @hideinitializer
   * @private
   */
  var $_pt = '';
  
  /**
   * This method returns the Proxy Ticket provided in the URL of the request.
   * @return The proxy ticket.
   * @private
   */
  function getPT()
    { return $this->_pt; }

  /**
   * This method stores the Proxy Ticket.
   * @param $pt The Proxy Ticket.
   * @private
   */
  function setPT($pt)
    { $this->_pt = $pt; }

  /**
   * This method tells if a Proxy Ticket was stored.
   * @return TRUE if a Proxy Ticket has been stored.
   * @private
   */
  function hasPT()
    { return !empty($this->_pt); }

  /** @} */
  // ########################################################################
  //  PT VALIDATION
  // ########################################################################
  /**
   * @addtogroup internalProxied
   * @{
   */  

  /**
   * This method is used to validate a PT; halt on failure
   * 
   * @return bool TRUE when successfull, halt otherwise by calling CASClient::authError().
   *
   * @private
   */
  function validatePT(&$validate_url,&$text_response,&$tree_response)
    {
      phpCAS::traceBegin();
      // build the URL to validate the ticket
      $validate_url = $this->getServerProxyValidateURL().'&ticket='.$this->getPT();

      if ( $this->isProxy() ) {
      // pass the callback url for CAS proxies
	$validate_url .= '&pgtUrl='.$this->getCallbackURL();
      }

      // open and read the URL
      if ( !$this->readURL($validate_url,''/*cookies*/,$headers,$text_response,$err_msg) ) {
	phpCAS::trace('could not open URL \''.$validate_url.'\' to validate ('.$err_msg.')');
	$this->authError('PT not validated',
			 $validate_url,
			 TRUE/*$no_response*/);
      }

      // read the response of the CAS server into a DOM object
      if ( !($dom = domxml_open_mem($text_response))) {
	// read failed
	$this->authError('PT not validated',
		     $alidate_url,
		     FALSE/*$no_response*/,
		     TRUE/*$bad_response*/,
		     $text_response);
      }
      // read the root node of the XML tree
      if ( !($tree_response = $dom->document_element()) ) {
	// read failed
	$this->authError('PT not validated',
		     $validate_url,
		     FALSE/*$no_response*/,
		     TRUE/*$bad_response*/,
		     $text_response);
      }
      // insure that tag name is 'serviceResponse'
      if ( hnodename($tree_response->node_name()) != 'serviceResponse' ) {
	// bad root node
	$this->authError('PT not validated',
		     $validate_url,
		     FALSE/*$no_response*/,
		     TRUE/*$bad_response*/,
		     $text_response);
      }
      if ( sizeof($arr = $tree_response->get_elements_by_tagname("authenticationSuccess")) != 0) {
	// authentication succeded, extract the user name
	if ( sizeof($arr = $tree_response->get_elements_by_tagname("user")) == 0) {
	  // no user specified => error
	  $this->authError('PT not validated',
		       $validate_url,
		       FALSE/*$no_response*/,
		       TRUE/*$bad_response*/,
		       $text_response);
	}
	$this->setUser(trim($arr[0]->get_content()));
	
      } else if ( sizeof($arr = $tree_response->get_elements_by_tagname("authenticationFailure")) != 0) {
	// authentication succeded, extract the error code and message
	$this->authError('PT not validated',
		     $validate_url,
		     FALSE/*$no_response*/,
		     FALSE/*$bad_response*/,
		     $text_response,
		     $arr[0]->get_attribute('code')/*$err_code*/,
		     trim($arr[0]->get_content())/*$err_msg*/);
      } else {
	$this->authError('PT not validated',
		     $validate_url,	
		     FALSE/*$no_response*/,
		     TRUE/*$bad_response*/,
		     $text_response);
      }
      
      // at this step, PT has been validated and $this->_user has been set,

      phpCAS::traceEnd(TRUE);
      return TRUE;
    }

  /** @} */

  // XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX
  // XX                                                                    XX
  // XX                               MISC                                 XX
  // XX                                                                    XX
  // XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX

  /**
   * @addtogroup internalMisc
   * @{
   */  
  
  // ########################################################################
  //  URL
  // ########################################################################
  /**
   * the URL of the current request (without any ticket CGI parameter). Written 
   * and read by CASClient::getURL().
   *
   * @hideinitializer
   * @private
   */
  var $_url = '';

  /**
   * This method returns the URL of the current request (without any ticket
   * CGI parameter).
   *
   * @return The URL
   *
   * @private
   */
  function getURL()
    {
      phpCAS::traceBegin();
      // the URL is built when needed only
      if ( empty($this->_url) ) {
	    $final_uri = '';
	    // remove the ticket if present in the URL
	    $final_uri = ($_SERVER['HTTPS'] == 'on') ? 'https' : 'http';
	    $final_uri .= '://';
	    /* replaced by Julien Marchal - v0.4.6
	     * $this->_url .= $_SERVER['SERVER_NAME'];
	     */
        if(empty($_SERVER['HTTP_X_FORWARDED_SERVER'])){
          /* replaced by teedog - v0.4.12
           * $this->_url .= $_SERVER['SERVER_NAME'];
           */
          if (empty($_SERVER['SERVER_NAME'])) {
            $final_uri .= $_SERVER['HTTP_HOST'];
          } else {
            $final_uri .= $_SERVER['SERVER_NAME'];
          }
        } else {
          $final_uri .= $_SERVER['HTTP_X_FORWARDED_SERVER'];
        }
	  if ( ($_SERVER['HTTPS']=='on' && $_SERVER['SERVER_PORT']!=443)
	     || ($_SERVER['HTTPS']!='on' && $_SERVER['SERVER_PORT']!=80) ) {
	    $final_uri .= ':';
	    $final_uri .= $_SERVER['SERVER_PORT'];
	  }

	  $final_uri .= strtok($_SERVER['REQUEST_URI'],"?");
	  $cgi_params = '?'.strtok("?");
	  // remove the ticket if present in the CGI parameters
	  $cgi_params = preg_replace('/&ticket=[^&]*/','',$cgi_params);
	  $cgi_params = preg_replace('/\?ticket=[^&;]*/','?',$cgi_params);
	  $cgi_params = preg_replace('/\?$/','',$cgi_params);
	  $final_uri .= $cgi_params;
	  $this->setURL($final_uri);
    }
    phpCAS::traceEnd($this->_url);
    return $this->_url;
  }

  /**
   * This method sets the URL of the current request 
   *
   * @param $url url to set for service
   *
   * @private
   */
  function setURL($url)
    {
      $this->_url = $url;
    }
  
  // ########################################################################
  //  AUTHENTICATION ERROR HANDLING
  // ########################################################################
  /**
   * This method is used to print the HTML output when the user was not authenticated.
   *
   * @param $failure the failure that occured
   * @param $cas_url the URL the CAS server was asked for
   * @param $no_response the response from the CAS server (other 
   * parameters are ignored if TRUE)
   * @param $bad_response bad response from the CAS server ($err_code
   * and $err_msg ignored if TRUE)
   * @param $cas_response the response of the CAS server
   * @param $err_code the error code given by the CAS server
   * @param $err_msg the error message given by the CAS server
   *
   * @private
   */
  function authError($failure,$cas_url,$no_response,$bad_response='',$cas_response='',$err_code='',$err_msg='')
    {
      phpCAS::traceBegin();

      $this->printHTMLHeader($this->getString(CAS_STR_AUTHENTICATION_FAILED));
      printf($this->getString(CAS_STR_YOU_WERE_NOT_AUTHENTICATED),$this->getURL(),$_SERVER['SERVER_ADMIN']);
      phpCAS::trace('CAS URL: '.$cas_url);
      phpCAS::trace('Authentication failure: '.$failure);
      if ( $no_response ) {
	phpCAS::trace('Reason: no response from the CAS server');
      } else {
	if ( $bad_response ) {
	    phpCAS::trace('Reason: bad response from the CAS server');
	} else {
	  switch ($this->getServerVersion()) {
	  case CAS_VERSION_1_0:
	    phpCAS::trace('Reason: CAS error');
	    break;
	  case CAS_VERSION_2_0:
	    if ( empty($err_code) )
	      phpCAS::trace('Reason: no CAS error');
	    else
	      phpCAS::trace('Reason: ['.$err_code.'] CAS error: '.$err_msg);
	    break;
	  }
	}
	phpCAS::trace('CAS response: '.$cas_response);
      }
      $this->printHTMLFooter();
      phpCAS::traceExit();
      exit();
    }

  /** @} */
}

?>