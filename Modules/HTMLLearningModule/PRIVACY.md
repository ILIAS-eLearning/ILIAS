# HTML Learning Module Privacy

This documentation does not warrant completeness or correctness. Please report any missing or wrong information using the <a href="https://mantis.ilias.de/">ILIAS issue tracker</a>.

## Data being stored

- The HTML Learning Module component itself does not store any personal data.
- The HTML Learning Module component employs the following services, please consult the respective privacy.mds for 
--Reference to Learning Progress (Learning Progress service stores data on access time speifically last time, number of accesses and the progress status specifically in progress, completed for each user accessing the object.) 
-- Reference to Metadata (Metadata  service contains two branches: LOM and custom metdata. The LOM offers storing personal dates like author. Custom metadata do contain user-created metadata sets which may contain personal dates, which mus be individually checked in the global administration)
-- Reference to Permission (The account which created the very objetc ist stored as it's owner, creation and update timestamps for the object. The permission service stores which users / user roles have what kind of access to the object.) 

## Data presentation

- The HTML Learning Module component itself does not present any personal data.
- Like all repository objects it integrates screens of the Permission service which present information about which users / user roles have what kind of access to the object.
- The module integrates the Info Screen service which reveals owner and creation date of the object.
- The module integrates the Learning Progress service, which presents (depending on global settings) progress and access data to users having the "View learning progress" permission.

## Data Deletion

- The Item group itself does not store or delete any personal data.
- Basic object, permission and learning progress data is deleted once the object is "finally" deleted (removed from trash).
