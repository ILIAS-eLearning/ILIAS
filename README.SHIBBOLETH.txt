Shibboleth Authentication for ILIAS
-------------------------------------------------------------------------------

Requirements:
- Webserver must run Shibboleth target 1.1 or later. 
  See documentation for your Shibboleth federation on how to set up Shibboleth. 

ILIAS Configuration with Dual login
-------------------------------------------------------------------------------

1. Protect the file ilas3/shib_login.php with Shibboleth.
  For apache you could use:

--
<Location ~ "ilias3/shib_login.php">
        AuthType shibboleth
        ShibRequireSession On
        require valid-user
</Location>
--

   To restrict access to ILIAS, replace the access rule 'require valid-user' 
   with something that fits your needs, e.g. 'require affiliation student'.
   
   shib_login.php acutally authenticates the user if the Shibboleth attributes are
   available.

2. As ILIAS admin, go to the 'Administration >> Authentication' Options and 
   select the 'Shibboleth' authentication method from the list. Don't click on 
   'Save' yet on that page.
   
3.   Click on the now available 'configure' button.
   
4. Fill in the fields for login button and login instructions. The first is just
   a path or URL to an image that will be displayed on the login page. The 
   login instructions can be used to place a message below the login button.
   Unfortunately the maximum length of these two fields is limited to 50 chars,
   due to the ILIAS database.
   Read below what you can use the data manipulation API for.
   
5. Fill in the fields of the form. The fields  for'loginname', 'firstname', 
   'surname', etc should contain the name of the environment variables of the 
   Shibboleth attributes that you want to map onto the corresponding ILIAS 
   variable (e.g. 'HTTP_SHIB_PERSON_SURNAME' for the person's last name, refer to 
   the Shibboleth documentation or the documentation of your Shibboleth
   federation for information on which attributes are available).
   Especially the 'loginname' field is of great importance because 
   this attribute is used for the ILIAS authentication of Shibboleth users.
   
   #############################################################################
   Shibboleth Attributes needed by ILIAS:
   For ILIAS to work properly Shibboleth should at least provide the attributes
   that are used as firstname, lastname and email in ILIAS.
   Furthermore, you have to provide an attribute that contains a unique
   value for each use. This could e.g. also be the users emailaddress.
   This unique attribute is needed to map the ILIAS user name to a certain
   Shibboleth user
   #############################################################################

6. Save the changes for the Shibboleth authentication method.

ILIAS Configuration with Shibboleth only login
-------------------------------------------------------------------------------
If you want Shibboleth as your only authentication method, configure ILIAS as
described in the dual login section above and do the following additional step:

3.a If you want to use Shibboleth as your only authentication method (no manual
    login), click on the 'Save' button on the page where you can choose an 
    authentication method.
    After that you should see a confirmation that the authentication method was
    changed to Shibboleth.

How the Shibboleth authentication works
--------------------------------------------------------------------------------
For a user to get Shibboleth authenticated in ILIAS he first must go to the 
Shibboleth-protected page shib_login.php. If he gets access to that page (this
is only the case if he is Shibboleth authenticated), he also gets authenticated 
in ILIAS. 
ILIAS basically checks whether the Shibboleth attribute that you mapped
as the unique Shibboleth attribute is present. This attribute is only present 
if a user is Shibboleth authenticated.

If the user's ILIAS account has not existed yet, it gets automatically created.

To prevent that every Shibboleth user can access your ILIAS site you have to
adapt the 'require valid-user' line in your webserver's config  (see step 1) to 
allow only specific users. 

You can use Shibboleth AND another authentication method (it was tested with 
the 'ILIAS database' method only). So if there are a few users that don't have 
a Shibboleth login, you could manually create ILIAS accounts for them and they 
could use the normal ILIAS login (provide login name and password on login 
page). For other authentication methods you first have to configure them and 
then configure Shibboleth without setting it as main authentication method. 
Users can log in only via one authentication method unless they have two 
accounts in ILIAS

How to customize the way the Shibboleth user data is used in ILIAS
--------------------------------------------------------------------------------
Among the Shibboleth settings in ILIAS there is a field that should contain a
path to a php file that can be used as data manipulation API.
You can use this if you want to further process the way your Shibboleth
attributes are used in ILIAS. 

Example 1: Your Shibboleth federation uses an attribute that specifies the 
           user's preferred language, but the content of this attribute is not
           compatible with the ILIAS data representation, e.g. the Shibboleth
           attribute contains 'German' but ILIAS needs a two letter value like 
           'de'.
Example 2: The username is generated by the Shibboleth part of ILIAS using the
           user's firstname and last name and a number in case several users 
           have the same name. This could give a username 'MusterHans2'. 
           If you are not happy with the way this name is generated you could
           write a file that generates the username the way you want it.

If you want to use this API you have to be a skilled PHP programmer. It is 
strongly recommended that you take a look at the file 
ilias3/classes/class.ilShibboleth.php, especially the function 'login' where
this API file is included. 
The context of the API file is the same as within this login function. So you
can directly edit the object $userObj.

Example file:

--
<?php
	
	// Set the zip code and the adress
	if ($_SERVER[$ilias->getSetting('shib_street')] != '')
	{
		// $address contains something like 'SWITCH$Limmatquai 138$CH-8021 Zurich'
		// We want to split this up to get: 
		// institution, street, zipcode, city and country
		$address = $_SERVER[$ilias->getSetting('shib_street')];
		list($institution, $street, $zip_city) = split('\$', $address);
		
		ereg('([0-9]{4,5})',$zip_city, $regs);
		$zip = $regs[1];
		
		ereg(' (.+)',$zip_city, $regs);
		$city = $regs[1];
		
		ereg('(.+)-',$zip_city, $regs);
		$country = $regs[1];
		
		// Update fields for new user or if it has to be updated
		if ($ilias->getSetting('shib_update_institution') || $newUser)
			$userObj->setInstitution($institution);
		if ($ilias->getSetting('shib_update_street') || $newUser)
			$userObj->setStreet($street);
		if ($ilias->getSetting('shib_update_zipcode') || $newUser)
			$userObj->setZipcode($zip);
		if ($ilias->getSetting('shib_update_city') || $newUser)
			$userObj->setCity($city);
		if ($ilias->getSetting('shib_update_country') || $newUser)
			$userObj->setCountry($country);
	}
	
	// Please ensure that there are no spaces or other characters outside
	// the <?php ?> delimiters
?>
--

Bugs
--------------------------------------------------------------------------------
The current implementation has not yet been extensively tested in a productive
environment with real courses and real users. So there may be bugs. 
Please send bug reports concerning the Shibboleth part to 
Lukas Haemmerle <haemmerle@switch.ch>

--------------------------------------------------------------------------------
In case of problems and questions with Shibboleth authentication, contact 
Lukas Haemmerle <haemmerle@switch.ch>.
