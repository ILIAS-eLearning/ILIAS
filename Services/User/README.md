# User Service

Currently the user service is only represented by an ilObjUser object of the current user in the DIC via `$DIC->user()`. However ilObjUser is not a real interface for other components, since it reveals lots of internals that should be hidden, see ROADMAP.


