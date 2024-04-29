# Roadmap
- All the steps in this roadmap depend on the availability of funding for
refactoring.
- There are currently no new features on the roadmap.

## Short Term
- Apply Sustainability Package from PHP8-Refactoring

## Mid Term

### Further Refactoring
- Continue application of the Sustainability Package from PHP8-Refactoring
- Move all used UI-Elements to Kitchensink
- Removal of static methods
- Consolidation of ilObject and ilObject2 as well as ilObjectGUI and ilObject2GUI
- Provide individual Titles for all views of an object in coordination with `ilCtrl`
and `GlobalScreen`. Right now many views don't have an individual and distinguishable
title. This is not an issue that can be solved by the object alone, but in order to
not loose sight of it, it is put on this ROADMAP.
- Clarify and Refactoring Process for copying object and providing consistent
landing pages after the copy process.


## Long Term

### Refactoring and Restructuring
Work on a more encompasing refactoring of Services\Object will be started in
the short term, but it will take a while to produce results.
In the Long Term Services\Objects will be moved to a more collaborative maintenance
model. The refactoring aims to facilitate this and provide an easy to understand
Service.
