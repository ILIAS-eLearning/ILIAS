# Roadmap

## Short Term

...

## Mid Term

### User Service in DIC

Currently the user service is only represented by an ilObjUser object of the current user in the DIC. However ilObjUser is not a real interface for other components, since it reveals lots of internals that should be hidden.

A decent user service interface needs to be defined that should fit the needs of other components through a well defined interface in the future.

### Favourites

With ILIAS 6 the "Add to Desktop" feature is relabeld as "Favourites" and now currently represented as a subservice under Services/User. This is an intermediate solution.

It might be better to group this service together with other common actions on repository objects (copy, paste, ...). These actinos should get a better common interface in the future. So Service/Repository could be a better and more appropriate location for this subservice, since at least on the UI level the action is performed on the objects, not "on the user" (like Services/User/Actions).

## Long Term

...
