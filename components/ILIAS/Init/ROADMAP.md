# Roadmap

## Mid Term

### Get rid of `ilStartupGUI::setForcedCommand` calls in *.php endpoint scripts

In order to address the issues resulting from the application of
[PR 6628](https://github.com/ILIAS-eLearning/ILIAS/pull/6628) (as a successor
of [PR 5100](https://github.com/ILIAS-eLearning/ILIAS/pull/5100)), a new static
method `ilStartupGUI::setForcedCommand` has been introduced as a bugfix
(not supported with any/appropriate funding) to provide a new way to force `ilStartupGUI`
to execute a specific command depending on the context of the requested
PHP endpoint script, e.g. ...

* login.php
* logout.php
* confirmReg.php
* pwassist.php
* register.php
* saml.php
* openidconnect.php
* sso/index.php
* ...

. To solve the routing problems we also considered a different approach by looking a
dozens and dozens of code references to these different PHP endpoints. Due to the sheer amount of
of changes and conflicts with other mechanisms in the "Init/Authentication Process"
(to name it: handling `cmd=force_login`, even if the forced command of these
endpoints differs from "force_login"), we decided to go with
the `ilStartupGUI::setForcedCommand` approach for now.

Of course the endgame should be to get rid of these static calls in the PHP endpoint scripts,
or even better, to get reduce the number of PHP endpoints generally.
But all this will have impacts on other components (e.g. the "Routing") sooner or later and
should be part of a dedicated project.