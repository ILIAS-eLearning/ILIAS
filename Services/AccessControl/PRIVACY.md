# Access Control Service Privacy

This documentation does not warrant completeness or correctness. Please report any
missing or wrong information using the [ILIAS issue tracker](https://mantis.ilias.de)
or contribute a fix via [Pull Request](docs/development/contributing.md#pull-request-to-the-repositories).


## Data being stored

- Each local role configured in administration or repository objects stores the user ID 
  of an account assigned to the specific local role. The purpose is the assignment of
  access control permissions to users via role assignment.
  
- With "Permission Log" enabled in "Administration -> Roles -> Settings" the user ID of an
  account that triggered one of the following actions is stored:
  - Creation of repository object
  - Change permissions of roles
  - Change role template permissions with action "Change existing objects" in upper contexts
  - Add local roles
  - Delete local roles
  
## Data being presented

- An account with "Change Permissions" permission has access to the following user profile
  data of user accounts assigned to local roles of a repository or administration object:
  - Username
  - Firstname
  - Lastname
  
- An account with "Change Permissions" permission has access to user profile data via 
  "Add User to Role" as described in "Service Search -> Repository User Search" 
  
- An account with "Change Permissions" permission and activate "Permission Log" has 
  access to the following user profile data of accounts which triggered access control
  changes:
  - Username
  - Firstname
  - Lastname
  
## Data being deleted

- Assignments of users to roles are deleted once the roles are deleted
- Assignments of users to roles are deleted once the repository objects are deleted from trash
- Rbac log entries are deleted after the defined maximmum age of log entries defined in "Administration ->
  Roles -> Settings". Note: the garbage collection is only triggered after any object is deleted from trash 
  or removed permanently (disabled trash functionality).


## Data being exported 

- The assignment of user accounts to roles can be exported in "Adminsitration -> User Accounts" via 
  user export.
  

