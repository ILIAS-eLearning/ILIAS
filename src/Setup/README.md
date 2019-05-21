# Setup Machinery

This library contains interfaces, base classes and utilities for the setup.

The setup is build around four main concepts:

** [**Config**](./Config.php) - Some options or configuration for the setup process that a user can
or must set.
* [**Objective**](./Objective.php) - Some desired state of the system that should
be achieved via the setup process, maybe depending on other objectives as preconditions.
* [**Agent**](./Agent.php) - Some component performing parts of in the setup process is refered to
as agent.
* [**Environment**](./Environment.php) - Some surrounding of the setup process which the objectives
build and depend upon.

Any implementation of a setup process, on the command line or in the web, then
basically needs to ask an agent for an objective for a fresh installation (or the
update of an installation) and then successively achieve all the preconditions
and finally the objective itself.


