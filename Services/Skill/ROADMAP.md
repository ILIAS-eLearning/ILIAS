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

### Deconstruct UI

* ilPersonalSkillsGUI should be deconstructed into smaller UI components, maybe using Listing Panel with Lead Text or something similar.

## Mid Term


### ...



## Long Term

### ...

