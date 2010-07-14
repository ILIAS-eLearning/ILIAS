<?php
/*

How SOAP authentication works.

SOAP authentication can be used for external systems that use ILIAS as a
subsystem in a way that the external system provides links into ILIAS
that look like ILIAS permanent link and include additionally parameters
to provide SSO between the external (master) system and ILIAS.

Basic Workflow

(1) The external system allows users to login and creates a session.
(2) The external system provides links to learning ressources that are located
	in ILIAS. The links include the parameters
	- ext_uid (ID of the user in the external system)
	- soap_pw (a temporary password, the external system may change these
	for every creation of a link if necessary)
(3) The user clicks on the link.
(4) ILIAS recognizes the SOAP parameters in the link and asks the external
	system via a SOAP call, whether the credentials provided belong to a
    valid session.
(5) The external system sends a response, the response may also contain
    basic user data (firstname, lastname, email).
(6) If the session is valid, ILIAS opens a new session for the user. If the
	user does not exist, ILIAS creates a new account using the provided user
    data.

Links:

The links should look like permanent links + soap_pw and ext_uid, e.g.:
goto.php?clientid=abc&target=crs_123&soap_pw=kjWjb34&ext_uid=500

SOAP call to the external server:

There is an example implementation of a SOAP server (=external master system)
in classes/class.ilSoapDummyAuthServer.php). The main soap call is:

isValidSession:
in:
array('ext_uid' => 'xsd:string',
	  'soap_pw' => 'xsd:string',
	  'new_user' => 'xsd:boolean')
out:
array('valid' => 'xsd:boolean',
	'firstname' => 'xsd:string',
	'lastname' => 'xsd:string',
	'email' => 'xsd:string')

*/
?>
