# Setup Machinery

This library contains interfaces, base classes and utilities for the setup.

The setup is build around four main concepts:

** [**Config**](./Config.php) - Some options or configuration for the setup process
that a user can or must set.
* [**Objective**](./Objective.php) - Some desired state of the system that should
be achieved via the setup process, maybe depending on other objectives as preconditions.
* [**Agent**](./Agent.php) - Some component performing parts of in the setup process
is refered to as agent.
* [**Environment**](./Environment.php) - Some surrounding of the setup process which
the objectives build and depend upon.

Any implementation of a setup process, on the command line or in the web, then
basically needs to ask an agent for an objective for a fresh installation (or the
update of an installation) and then successively achieve all the preconditions
and finally the objective itself.

## More Details, Please!

### On Config

This comes first, because it' probably the most simple of the four concepts. A
config is basically a glorified key-value map as a data type. It encapsulates
defaults and checks for the values in the config and acts as an insurance to its
users that the config has the expected shape. Also, it's a config for the setup
process and not for the installed system. For an example, have a look into
[`ilDatabaseSetupConfig`](Services/Database/classes/Setup/class.ilDatabaseSetupConfig.php).

### On Environment

This basically is a key-value map as well, but with resources as values. It acts
as a registry for the services that are required and created during the setup
process, e.g. the database. A complete environment for an ILIAS-installation is
the ultimate goal of the setup process. Since the setup process starts with very
little, the environment is designed as an extensible registry that will get
filled during the setup process. Look into [`ilDatabaseExistsObjective::achieve`](Services/Database/classes/Setup/class.ilDatabaseExistsObjective.php)
to see how the environment is used during the setup process.

### On Agent

An `Agent` is what every ILIAS-component needs to implement if it wants to take
part in the setup process. An agent needs to tell how to build a configuration
from an array or by an input from the UI framework. It also needs to provide an
objective for the setup or for an update. As expected, the database-service
provides an agent for the setup: [`ilDatabaseSetupAgent`](Services/Database/classes/Setup/class.ilDatabaseSetupAgent.php).

### On Objective

Objectives are the core of the whole matter. An `Objective` describes a state of
the system that an agent wants to achieve. Any `Objective` may have preconditions,
which are other objectives. Once the preconditions are achieved, the objective
itself may be achieved. This might use stuff from the environment but also add
stuff to the environment. The [agent from the database service](Services/Database/classes/Setup/class.ilDatabaseSetupAgent.php),
for example, has the [objective to create a populated database](Services/Database/classes/Setup/class.ilDatabasePopulatedObjective.php).
This has the precondition [that the database exists](Services/Database/classes/Setup/class.ilDatabaseExistsObjective.php),
which in turn requires [that the database server is connectable](Services/Database/classes/Setup/class.ilDatabaseExistsObjective.php).

This yields a directed graph of objectives, where (hopefully) some objectives do
not have any preconditions. These can be achieved, which prepares the environment
for other objectives to be achieable, until all objectives are achieved and the
setup is completed.
