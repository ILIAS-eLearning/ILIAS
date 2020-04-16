# Roadmap

## Short Term

### README

A README should introduce the main concepts and business rules. README should replace DEV Guide.


### Introduce Repository Pattern

Main db table access should be moved to repository classes.

### Directory Structure

Use subdirectories to improve overview. (after removing require/include)

### Service API via $DIC

A main service object should be available in the DIC. Writing and querying competence level user entries should be possible in a first step.

Main Issues:
* Writing user skills: ilBasicSkill::writeUserSkillLevelStatus
* Using Skill UI: ilPersonalSkillsGUI();
* ilSkillProfile::getProfilesOfUser
* ...

e.g.

* $DIC->skills()->user($id)->getProfiles();
* $DIC->skills()->ui()->getGapUI($user_id, $profile_id, ...);
* $DIC->skills()->user($id)->writeSkillLevel(...);

An example on how to implement such a service could be found in Services/Object/Service the way ilObjectService implements ilObjectServiceInterface. But insted of the "il" prefix namespaces should be used.

The skill service may include an internal part that does not serve as an external API but as an internal service, e.g. to provide access to internal repo objects. An example can be found in Modules/BookingManager/Service.

E.g.

$DIC->skills()->user($id)->writeSkillLevel(...);

could make a call to

$DIC->skills()->interal()->repo()->getUserSkillRepo()->writeSkillLevel(...);

In a first step the implementation of this writeSkillLevel() procedure could look like the current ilBasicSkill::writeUserSkillLevelStatus but without the deprecated parameters.

Additionally the next level percentage fullfilment value (value must be >=0 and <1) can be passed to the function, see https://docu.ilias.de/goto.php?target=wiki_1357_Storing_Specific_Values_for_Competence_Levels#ilPageTocA112

### Deconstruct UI

* ilPersonalSkillsGUI should be deconstructed into smaller UI components, maybe using Listing Panel with Lead Text or something similar.

## Mid Term


### ...



## Long Term

### ...

