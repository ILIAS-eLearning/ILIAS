Shibboleth Authentication for ILIAS
-------------------------------------------------------------------------------

Requirements:
- Webserver must run Shibboleth Service Provider 1.3 or newer.
  Please have a look at the Shibboleth deployment documentation on how to set up Shibboleth.

Configure ILIAS for Shibboleth authentication
-------------------------------------------------------------------------------

1. Protect the file ilias/shib_login.php with Shibboleth.
  For apache one could use:

--
<Location ~ "/shib_login.php">
        AuthType shibboleth
        ShibRequireSession On
        require valid-user
</Location>
--

   To restrict access to ILIAS, replace the access rule 'require valid-user'
   for example with an access control rule like: 'require affiliation student'

   shib_login.php authenticates the user if the required Shibboleth attributes
   are available and if the require rule is satisfied.

   For IIS web servers, one must define in the shibboleth.xml or shibboleth2.xml
   RequestMap element a rule like:

--
<Host name="ilias.host.org">
    <Path name="path/to/ilias/shib_login.php" authType="shibboleth" requireSession="true">
</Host>
--

   See http://www.switch.ch/aai/support/serviceproviders/sp-access-rules.html on
   how one can protect ILIAS in order to grant access only to specific users.

2. As ILIAS admin, go to the 'Administration >> Authentication and Registration'
   options and click on the link for the 'Shibboleth' settings.

3. Activate the "Enable Shibboleth Support" checkbox on the top.
   After defining the default user role for new users registering via Shibboleth
   and the name of the Shibboleth federation this service is part of,
   one has to define whether the Shibboleth users shall select their home
   organization directly on the ILIAS login page or on an external page.

   If it was chosen to use the ILIAS WAYF service, one has to make sure that
   Shibboleth is configured to have a default applicationId for the <host>
   element and that the default Shibboleth handlerURL is configured to be
   "/Shibboleth.sso", which usually is the default setting for Shibboleth.
   To check that, open the shibboleth.xml configuration file and lookg for the
   <host> element, which must have an attribute 'applicationId', e.g.
   applicationId="default".
   If another than the default session initiator is used (for example because
   the ILIAS installation is part of several federations), one can specify
   a location of a session initiator for an Identity Provider as a third
   argument. The session inititors can be found in the shibboleth.xml
   configuration file as well.

   Also see:
   https://spaces.internet2.edu/display/SHIB/SessionInitiator (SP 1.3.x)
   https://spaces.internet2.edu/display/SHIB2/NativeSPSessionInitiator (SP 2.x)

   If one choses to use an external WAYF, provide an URL to an image that is to
   be used for the login button. The default is 'images/shib_login_button.png'

   If the custom login is chosen, the login area can be freely designed using
   the login instructions text area. It is possible to use HTML code in that
   text field if this option is chosen (and only then). This can then be used
   to embedd a JavaScript WAYF or Discovery service.
   
   The login instructions can be used to place a message for Shibboleth users
   on the login page. These instructions are independent from the current
   language the user has chosen.

   Read below what the data manipulation file can be used for.

4. Fill in the fields of the form for the attribute mapping. One needs to provide
   the names of the environment variables that contain the Shibboleth attributes
   for the unique ID, firstname, surname, etc. This e.g. could be
   'Shib-Person-surname' for the person's last name. Refer to
   the Shibboleth documentation or the documentation of the Shibboleth
   federation for information on which attributes are available.
   Especially the field for the 'unique Shibboleth attribute' is of great
   importance because this attribute is used for the user mapping between ILIAS
   and Shibboleth users.

   #############################################################################
   Shibboleth Attributes needed by ILIAS:
   For ILIAS to work properly Shibboleth should at least provide the attributes
   that are used as firstname, lastname and email in ILIAS.
   Furthermore, one has to provide an attribute that contains a unique
   value for each use. This could e.g. also be the users email address if it
   can be ensured that the address is permanent and doesn't change.
   This unique attribute is needed to map the ILIAS user name to a certain
   Shibboleth user.
   #############################################################################

5. Save the changes for the Shibboleth authentication method.

6. (optional) Go to Administration -> User Accounts -> Settings and
   set the fields as not changeable if they are provided by Shibboleth.


How the Shibboleth authentication works
--------------------------------------------------------------------------------
For a user to get Shibboleth authenticated in ILIAS, he first must access the
Shibboleth-protected page shib_login.php. If he gets access to that page, his
ILIAS session will be set up using the attributes provided by Shibboleth.
ILIAS checks whether the Shibboleth attribute that was mapped as the unique
Shibboleth attribute is present. This attribute is only present if a user was successfully authenticated with Shibboleth.

If the user's ILIAS account has not existed yet, it gets automatically created.
The user only has to accept the terms of use and is logged in automatically.

To prevent that every Shibboleth user can access the ILIAS installation, one
has to adapt the 'require valid-user' line in the webserver's config
(see step 1) to allow only specific users.

ILIAS can use Shibboleth AND another authentication methods. So, if there are a
few users that don't have a Shibboleth login, one could manually create ILIAS
accounts for them and they could use the normal ILIAS login (provide login name
and password on login page).
Users can log in only via one authentication method unless they have two
accounts in ILIAS.  Generally, dual login is not recommended because it might be confusing for users.

How to customize the way the Shibboleth user attributes are used in ILIAS
--------------------------------------------------------------------------------
Among the Shibboleth settings in ILIAS there is a field that can contain a
path to a php file that can be used as data manipulation hook (kind of an API).
Once can use this if  Shibboleth attributes shall be processed/modified further.

Example 1: The Shibboleth federation uses an attribute that specifies the
           user's preferred language, but the content of this attribute is not
           compatible with the ILIAS data representation, e.g. the Shibboleth
           attribute contains 'German' but ILIAS needs a two letter value like
           'de'.
Example 2: The username is generated by the Shibboleth part of ILIAS using the
           user's firstname and last name and a number in case several users
           have the same name. This could give a username 'Hans Muster 2'.
           If the generated name is satisfying it is possible to write a file 
           that generates the username in a different way.

Using that hook implies requires some PHP programming skills. It is strongly recommended to take a look at the file
ilias/Services/AuthShibboleth/classes/class.ilShibboleth.php, especially the
function 'login' where this file is included.
The context of the hook file is the same as within this login function. So one
can directly edit the object $userObj.

Example file:

--
<?php

        // Set the zip code and the adress
        if ($_SERVER[$ilias->getSetting('shib_street')] != '')
        {
                // $address contains something like
                // 'SWITCH$Limmatquai 138$CH-8021 Zurich'
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


How to upgrade the Service Provider to 2.x
-------------------------------------------------------------------------------

In case the Service Provider shall be upgraded from 1.3.x to 2.x, be aware 
that in version 2.x the default behaviour regarding attribute names in the web 
server environment changed.
While the Service Provider 1.3.x published the Shibboleth attributes to the
web server environment as HTTP Request headers, the Service Provider 2.x 
publishes attributes as environment variables, which increases the security for
some platforms and allows the attributes to have the exact names as defined in 
the Shibboleth attribute-map.xml file.
However, this change has the effect that the attribute names change.
E.g. while the surname attribute was published as HTTP header 
'HTTP_SHIB_PERSON_SURNAME' by 1.3.x, this attribute will be available in $_SERVER['Shib-Person-surname'] (depending on /etc/shibboleth/attribute-map.xml)
or just as $_SERVER['sn'].
Because ILIAS needs to know what Shibboleth attribute names shall be mapped for 
an ILIAS user profile field, one has to make sure the mapping is updated after 
the Shibboleth Service Provider upgrade.

********************************************************************************
Because there is a risk of locking oneself out of ILIAS it is strongly 
recommended to use the following approach when upgrading the Service Provider:
1. Enable Database authentication before the upgrade. 
2. Make sure that to have at least one manual account with administration 
   privileges working before upgrading the Service Provider to 2.x.
3. After the SP upgrade, use this account to log into ILIAS and adapt the 
   attribute mapping in 'Administration -> Authentication and Registration -> 
   Shibboleth' to reflect the changed attribute names.
   One finds the attribute names in the file /etc/shibboleth/attribute-map.xml 
   listed as the 'id' value of an attribute definition.
4. Test the login with a Shibboleth account
5. If all is working, disable database authentication again if it was 
   enabled before the upgrade
********************************************************************************


How to add logout support
--------------------------------------------------------------------------------

In order make ILIAS support Shibboleth logout, one has to make the Shibboleth 
Service Provider (SP) aware of the ILIAS' logout capability. Only then the SP 
can trigger ILIAS's front or back channel logout handler.

To make the SP aware of the ILIAS logout, one has to add the following to the
Shibboleth main configuration file shibboleth2.xml (usually in /etc/shibboleth/)
just before the <MetadataProvider> element.

--
<Notify 
	Channel="back"
	Location="https://#MY_ILIAS_HOSTNAME#/#PATH_TO_ILIAS_DIR#/shib_logout.php" />
--

Then restart the Shibboleth daemon and check the log file for errors. If there 
were no errors, one can test the logout feature by accessing ILIAS, 
authenticating via Shibboleth and the access the URL:
#MY_ILIAS_HOSTNAME#/Shibboleth.sso/Logout (assuming a standard 
Shibboleth installation). If everything worked well, one should see a Shibboleth
page saying that log out was successful and if one returns to ILIAS it, one 
should also be logged out from ILIAS.

Limitations:
Single Logout is only supported with SAML2 and so far only with the Shibboleth 
Service Provider 2.x. 
As of January 2010, the Shibboleth Identity Provider does not yet support
Single Logout (SLO). Therefore, the single logout feature cannot be used yet. 
One of the reasons why SLO isn't supported yet is because there aren't many 
applications yet that were adapted to support front and back channel 
logout. Hopefully, the ILIAS logout helps to motivate the developers to 
implement SLO :)

Also see https://spaces.internet2.edu/display/SHIB2/SLOIssues for some 
background information on this topic.

Warning: 
Due to the above limitations one should be aware of the following.
Although a user might be logged out form ILIAS as well as the 
Shibboleth Service Provider, he still might have a valid session at the 
Identity Provider. Therefore, clicking on the ILIAS login button will 
immediately log in the user again. Therefore, the best logout solution as for 
now still is to tell users to quit their web browser! Only if the Identity 
Provider also supports Single Log Out (SLO), a "real" logout becomes possible.

--------------------------------------------------------------------------------
- Thanx to Marco Lehre <lehre@net.ethz.ch> for language suggestions and general
  feedback.
- Thanx to Philipp Tobler <philipp.tobler@id.unibe.ch> and Werner 
  Randelshofer <werner.randelshofer@hslu.ch> for suggesting and implementing
  a better algorithm for generating a user name.
--------------------------------------------------------------------------------
In case of problems and questions with Shibboleth authentication, contact
Lukas Haemmerle <lukas.haemmerle@switch.ch>.
