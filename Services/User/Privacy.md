# User Service Privacy

This documentation does not warrant completeness or correctness. Please report any
missing or wrong information using the [ILIAS issue tracker](https://mantis.ilias.de)
or contribute a fix via [Pull Request](docs/development/contributing.md#pull-request-to-the-repositories).

## Data being stored

The following user profile data values for user accounts created in "Administration -> Users and Roles -> User Management" 
are stored in the database:
- Username
- External authentication name
- External authentication mode  
- Title
- Salutation  
- Firstname
- Lastname
- Birthday
- Institution
- Department
- Street
- City
- ZIP code
- Country (Text field)
- Country (Selection from list)
- Phone Office
- Phone Home
- Phone Mobile
- Fax
- Email
- Second Email
- Interests / Hobbies  
- Refereral Comment ("How did you hear about ILIAS")
- General Interests
- Offering help
- Looking for help  
- Matriculation number
- Client Ip 
- User Language
- User map (latitude longitude)

Depending on the settings in "Administration -> Users and Roles -> User Management" the above profile data values are available 
(visible and changeable) in the following sections:
- User profile settings
- New account registration
- Local user administration in Categories
- Visibility in Courses / Groups

Once a profile data field is enabled in the registration, local user administration or the personal profile, the assigned values 
are stored in the database. Deactivation of profile fields in the above sections will not delete any profile values automatically.

An user profile image / avatar is stored in the web data directory. 

The user service is responsible for the storage of the following date related settings / information:
- Last Login (automatically updated after each login)
- Last Password Prompt
- Approve Date (approvement date by an administrator)
- Agree Date (date of acceptance of user agreement)

User accounts with local (ILIAS) authentication store an "bcrypted" password. A plain text password is only stored if the
user is imported via xml. After the first login or any password change in the administration or by the user, the
password is stored using a bcrypt algorithm. 

## Data being presented

## Data being deleted

## Data being exported 

  
