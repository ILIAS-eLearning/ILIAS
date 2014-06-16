<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

// Just for debugging the WSDL part
ini_set("soap.wsdl_cache_enabled", "0"); // disabling WSDL cache

/**
* Shibboleth login script for ilias
*
* $Id: shib_login.php 15434 2007-11-27 09:02:01Z smeyer $
* @author Lukas Haemmerle <haemmerle@switch.ch>
* @package ilias-layout
*/

// Requirements:
// PHP 5 with SOAP support (should be available in default deployment)


// Front channel logout

// Note: Generally the back-channel logout should be used once the Shibboleth
//       Identity Provider supports Single Log Out!
//       Front-channel logout is not of much use.

if (
		isset($_GET['return'])
		&& isset($_GET['action'])
		&& $_GET['action'] == 'logout'
   ){
	
	// Load all the IILIAS stuff
	require_once "include/inc.header.php";
	
	global $ilAuth;
	
	// Logout out user from application
	// Destroy application session/cookie etc
	$ilAuth->logout();
	
	// Finally, send user to the return URL
	ilUtil::redirect($_GET['return']);
}

// Back channel logout //

// Note: This is the preferred logout channel because it also allows
//       administrative logout. However, it requires your application to be
//       adapated in the sense that the user's Shibboleth session ID must be
//       stored in the application's session data.
//       See function LogoutNotification below

elseif (!empty($HTTP_RAW_POST_DATA)) {
	
	include_once "Services/Context/classes/class.ilContext.php";
	ilContext::init(ilContext::CONTEXT_SOAP);
	
	// Load ILIAS libraries and initialise ILIAS in non-web context
	require_once("Services/Init/classes/class.ilInitialisation.php");
	ilInitialisation::initILIAS();
	
	// Set SOAP header
	$server = new SoapServer('https://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'].'/LogoutNotification.wsdl');
	$server->addFunction("LogoutNotification");
	$server->handle();
}

// Return WSDL

// Note: This is needed for the PHP SoapServer class.
//       Since I'm not a web service guru it might be that the code below is not
//       absolutely correct but at least it seems to to its job properly when it
//       comes to Shibboleth logout

else {

	header('Content-Type: text/xml');

	echo <<<WSDL
<?xml version ="1.0" encoding ="UTF-8" ?>
<definitions name="LogoutNotification"
  targetNamespace="urn:mace:shibboleth:2.0:sp:notify"
  xmlns:notify="urn:mace:shibboleth:2.0:sp:notify"
  xmlns:soap="http://schemas.xmlsoap.org/wsdl/soap/"
  xmlns="http://schemas.xmlsoap.org/wsdl/">

	<types>
	   <schema targetNamespace="urn:mace:shibboleth:2.0:sp:notify"
		   xmlns="http://www.w3.org/2000/10/XMLSchema"
		   xmlns:notify="urn:mace:shibboleth:2.0:sp:notify">

			<simpleType name="string">
				<restriction base="string">
					<minLength value="1"/>
				</restriction>
			</simpleType>

			<element name="OK" type="notify:OKType"/>
			<complexType name="OKType">
				<sequence/>
			</complexType>

		</schema>
	</types>

	<message name="getLogoutNotificationRequest">
		<part name="SessionID" type="notify:string" />
	</message>

	<message name="getLogoutNotificationResponse" >
		<part name="OK"/>
	</message>

	<portType name="LogoutNotificationPortType">
		<operation name="LogoutNotification">
			<input message="getLogoutNotificationRequest"/>
			<output message="getLogoutNotificationResponse"/>
		</operation>
	</portType>

	<binding name="LogoutNotificationBinding" type="notify:LogoutNotificationPortType">
		<soap:binding style="rpc" transport="http://schemas.xmlsoap.org/soap/http"/>
		<operation name="LogoutNotification">
			<soap:operation soapAction="urn:xmethods-logout-notification#LogoutNotification"/>
		</operation>
	</binding>

	<service name="LogoutNotificationService">
		  <port name="LogoutNotificationPort" binding="notify:LogoutNotificationBinding">
			<soap:address location="https://{$_SERVER['HTTP_HOST']}{$_SERVER['PHP_SELF']}"/>
		  </port>
	</service>
</definitions>
WSDL;
	exit;

}

/******************************************************************************/
/// This function does the actual logout
function LogoutNotification($SessionID){

	// Delete session of user using $SessionID to locate the user's session file
	// on the file system or in the database
	// Then delete this entry or record to clear the session
	// However, for that to work it is essential that the user's Shibboleth
	// SessionID is stored in the user session data!
	
	global $ilDB;
	
	$q = "SELECT session_id, data FROM usr_session WHERE expires > 'NOW()'";
	$r = $ilDB->query($q);
	
	while($session_entry = $r->fetchRow(DB_FETCHMODE_ASSOC)){
		
		$user_session = unserializesession($session_entry['data']);
		
		// Look for session with matching Shibboleth session id 
		// and then delete this ilias session
		foreach($user_session as $user_session_entry){
			if ( 
				   is_array($user_session_entry) 
				&& array_key_exists('shibboleth_session_id', $user_session_entry)
				&& $user_session_entry['shibboleth_session_id'] == $SessionID){
				
				// Delete this session entry
				if (ilSession::_destroy($session_entry['session_id']) !== true){
					return new SoapFault('LogoutError', 'Could not delete session entry in database.');
				}
			}
		}
	}
	
	// If no SoapFault is returned, all is fine
}

/******************************************************************************/
// Deserializes session data and returns it in a hash array of arrays
function unserializesession( $serialized_string ){
	$variables = array( );
	$a = preg_split( "/(\w+)\|/", $serialized_string, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE );
	for( $i = 0; $i < count( $a ); $i = $i+2 ) {
		$variables[$a[$i]] = unserialize( $a[$i+1] );
	}
	return( $variables );
}

?>
