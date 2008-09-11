Shibboleth Authentication for ILIAS
-------------------------------------------------------------------------------

Requirements:
- Webserver must run Shibboleth target 1.1 or newer.
  See documentation for your Shibboleth federation on how to set up Shibboleth.

Configure ILIAS for Shibboleth authentication
-------------------------------------------------------------------------------

1. Protect the file ilas3/shib_login.php with Shibboleth.
  For apache you could use:

--
<Location ~ "/shib_login.php">
        AuthType shibboleth
        ShibRequireSession On
        require valid-user
</Location>
--

   To restrict access to ILIAS, replace the access rule 'require valid-user'
   with something that fits your needs, e.g. 'require affiliation student'.

   shib_login.php authenticates the user if the required Shibboleth attributes
   are available and if the require rule is satisfied.

2. As ILIAS admin, go to the 'Administration >> Authentication and Registration'
   options and click on the link for the 'Shibboleth' settings.

3. Activate the "Enable Shibboleth Support" checkbox on the top.
   After defining the default user role for new users registering via Shibboleth
   and the name of the Shibboleth federation this service is part of,
   you have to define whether the Shibboleth users shall select their home
   organization directly on the ILIAS login page or on an external page.

   If you have chosen to use the ILIAS WAYF, you have to make sure that
   Shibboleth is configured to have a default applicationId for the <host>
   element and that the default Shibboleth handlerURL is configured to be
   "/Shibboleth.sso", which usually is the default setting for Shibboleth.
   To check that, open the shibboleth.xml configuration file and lookg for the
   <host> element, which must have an attribute 'applicationId', e.g.
   applicationId="default".
   If you don't want to use the default session initiator (for example because
   your ILIAS installation is part of several federation), you can specify
   a location of a session initiator for a Identity Provider as a third
   argument. The session inititors can be found in the shibboleth.xml
   configuration file as well.

   If you chose to use an external WAYF, fill in an URL to an image that is to
   be used for the login button. Default ist 'images/shib_login_button.gif'

   The login instructions can be used to place a message for Shibboleth users
   on the login page. These instructions are independent from the current
   language the user has chosen.

   Read below what you can use the data manipulation file for.

4. Fill in the fields of the form for the attribute mapping. You need to provide
   the names of the environment variables that contain the Shibboleth attributes
   for the unique ID, firstname, surname, etc. This e.g. could be
   'HTTP_SHIB_PERSON_SURNAME' for the person's last name. Refer to
   the Shibboleth documentation or the documentation of your Shibboleth
   federation for information on which attributes are available.
   Especially the field for the 'unique Shibboleth attribute' is of great
   importance because this attribute is used for the user mapping between ILIAS
   and Shibboleth users.

   #############################################################################
   Shibboleth Attributes needed by ILIAS:
   For ILIAS to work properly Shibboleth should at least provide the attributes
   that are used as firstname, lastname and email in ILIAS.
   Furthermore, you have to provide an attribute that contains a unique
   value for each use. This could e.g. also be the users emailaddress.
   This unique attribute is needed to map the ILIAS user name to a certain
   Shibboleth user.
   #############################################################################

5. Save the changes for the Shibboleth authentication method.

6. (optional) Go to Administration -> User Accounts -> Global settings and
   disable that certain fields, which may be provided by Shibboleth, can be
   changed by the users.


How the Shibboleth authentication works
--------------------------------------------------------------------------------
For a user to get Shibboleth authenticated in ILIAS he first must go to the
Shibboleth-protected page shib_login.php. If he gets access to that pag, he also
gets authenticated in ILIAS.
ILIAS checks whether the Shibboleth attribute that you mapped as the unique
Shibboleth attribute is present. This attribute is only present if a user could
be authenticated at his home organization

If the user's ILIAS account has not existed yet, it gets automatically created.
The user only has to accept the terms of use and is logged in automatically.

To prevent that every Shibboleth user can access your ILIAS installation you
have to adapt the 'require valid-user' line in your webserver's config
(see step 1) to allow only specific users.

ILIAS can use Shibboleth AND another authentication methods. So if there are a
few users that don't have a Shibboleth login, you could manually create ILIAS
accounts for them and they could use the normal ILIAS login (provide login name
and password on login page).
Users can log in only via one authentication method unless they have two
accounts in ILIAS.

How to customize the way the Shibboleth user attributes are used in ILIAS
--------------------------------------------------------------------------------
Among the Shibboleth settings in ILIAS there is a field that can contain a
path to a php file that can be used as data manipulation hook (kind of an API).
You can use this if you want to further process the way your Shibboleth
attributes are used in ILIAS.

Example 1: Your Shibboleth federation uses an attribute that specifies the
           user's preferred language, but the content of this attribute is not
           compatible with the ILIAS data representation, e.g. the Shibboleth
           attribute contains 'German' but ILIAS needs a two letter value like
           'de'.
Example 2: The username is generated by the Shibboleth part of ILIAS using the
           user's firstname and last name and a number in case several users
           have the same name. This could give a username 'Hans Muster 2'.
           If you are not happy with the way this name is generated you could
           write a file that generates the username the way you want it.

If you want to use this hook you have to be a skilled PHP programmer. It is
strongly recommended that you take a look at the file
ilias3/Services/AuthShibboleth/classes/class.ilShibboleth.php, especially the
function 'login' where this file is included.
The context of the hook file is the same as within this login function. So you
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

--------------------------------------------------------------------------------
- Thanx to Marco Lehre <lehre@net.ethz.ch> for language suggestions and general
  feedback.
- Thanx to Philipp Tobler <philipp.tobler@id.unibe.ch> and Werner 
  Randelshofer <werner.randelshofer@hslu.ch> for suggesting and implementing
  a better algorithm for generating a user name.
--------------------------------------------------------------------------------
In case of problems and questions with Shibboleth authentication, contact
Lukas Haemmerle <lukas.haemmerle@switch.ch>.
