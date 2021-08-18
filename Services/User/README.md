# User Service

Currently the user service is only represented by an ilObjUser object of the current user in the DIC via `$DIC->user()`. However ilObjUser is not a real interface for other components, since it reveals lots of internals that should be hidden, see ROADMAP.

## Starting Point

Business Rules

- The starting point process will be triggered, if a user session starts at the login. If the user browser throught the public area before, ILIAS will keep the users at the reference ID location in the repository. JF decision: https://mantis.ilias.de/view.php?id=30710
