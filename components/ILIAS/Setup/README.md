# Setup Machinery

This library contains interfaces, base classes and utilities for the setup.

The setup is build around four concepts:

* [**Config**](./Config.php) - Some options or configuration for the setup process
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

There are special kinds of `Objective`s and supporting classes that are tailored to
match certain use cases.

* A [**Migration**](Migration.php) is a potentially long running operation that can be
broken into discrete steps. Other than database updates, it is supposed to run in the
background, even when the installation is online again.
* [**BuildArtifactObjective**](Objective/BuildArtifactObjective.php) allows to create an
[`Artifact`](./Artifact.php) somewhere. Look into the [according section](#on-artifacts)
to find out how to use them.

For the `status` command of the setup, the kernel of a framework for metrics is
[included](./Metrics/README.md) here. This is kept a little separate from the rest
of the setup, because we might want to detach this some day.


## More Details, Please!

### On Config

This comes first, because it's probably the most simple of the four concepts. A
config is basically a glorified key-value map as a data type. It encapsulates
defaults and checks for the values in the config and acts as an insurance to its
users that the config has the expected shape. Also, it's a config for the setup
process and not for the installed system. For an example, have a look into
[`ilDatabaseSetupConfig`](components/ILIAS/Database/classes/Setup/class.ilDatabaseSetupConfig.php).

A config-file, when used from the CLI, expects keys according to the array keys used
for the construction of the AgentCollection in cli.php ($c["agent"] = ...).
So, e.g., constructing your colllection with "database" => $c["agent.database"] means
an expected config
{
  "database" : {
      "host": "xxx",
      "port": "",
      ...
  }
}


### On Environment

This basically is a key-value map as well, but with resources as values. It acts
as a registry for the services that are required and created during the setup
process, e.g. the database. A complete environment for an ILIAS-installation is
the ultimate goal of the setup process. Since the setup process starts with very
little, the environment is designed as an extensible registry that will get
filled during the setup process. Look into [`ilDatabaseExistsObjective::achieve`](components/ILIAS/Database/classes/Setup/class.ilDatabaseExistsObjective.php)
to see how the environment is used during the setup process.


### On Agent

An `Agent` is what every ILIAS-component needs to implement if it wants to take
part in the setup process. An agent needs to tell how to build a configuration
from an array or by an input from the UI framework. It also needs to provide an
objective for the setup or for an update. As expected, the database-service
provides an agent for the setup: [`ilDatabaseSetupAgent`](components/ILIAS/Database/classes/Setup/class.ilDatabaseSetupAgent.php).


### On Objective

Objectives are the core of the whole matter. An `Objective` describes a state of
the system that an agent wants to achieve. An objective might or might not be
applicable for the current state of the system, which means that it might not
be required to be achieved. Any `Objective` may have preconditions, which are
other objectives. Once the preconditions are achieved, the objective itself may
be achieved. This might use stuff from the environment but also add stuff to
the environment. The [agent from the database service](components/ILIAS/Database/classes/Setup/class.ilDatabaseSetupAgent.php),
for example, has the [objective to create a populated database](components/ILIAS/Database/classes/Setup/class.ilDatabasePopulatedObjective.php).
This has the precondition [that the database exists](components/ILIAS/Database/classes/Setup/class.ilDatabaseExistsObjective.php),
which in turn requires [that the database server is connectable](components/ILIAS/Database/classes/Setup/class.ilDatabaseExistsObjective.php).

This yields a directed graph of objectives, where (hopefully) some objectives do
not have any preconditions. These can be achieved, which prepares the environment
for other objectives to be achievable, until all objectives are achieved and the
setup is completed.

[`DBUpdateSteps`](components/ILIAS/Database/interfaces/Setup/interface.ilDatabaseUpdateSteps.php)
are a special type of `Objective`. Their purpose is to change the database **structure**
to a desired state, i.e. adding fields or changing field types. To change database
**contents**, you can use `Migrations`.


### On Migration

Sometimes an update of an installation requires more work than simply downloading
fresh code and updating the database schema. When, e.g., the certificates where
moved to a new persistant storage model, a lot of data needed to be shuffled around.
This operation would potentially take a lot of time and thus was offloaded to be
triggered by single users. A `Migration` is the right tool for this case, as its
purpose is to change the database **contents** to a desired state.

The setup offers functionality for components to encapsulate these kind of operations
to allow administrators to monitor and also run them in a principled way. `Agent`s
therefore can implement the [`getMigrations`](`src/Setup/Agent.php#L82`) method to
make these [`Migration`s](src/Setup/Migration.php) available in the setup.

The general idea is, that a migration is an operation that can be broken into discrete
steps which can be executed even if the installation is online after update again.
These steps can then be triggered via the CLI and also be monitored there. It is well
possible, that there are also other means to trigger the steps, such as an interaction
by the user. The first user of the migrations is the [`FileObject`](components/ILIAS/File/classes/Setup/class.ilFileObjectToStorageMigration.php).

Please keep in mind that a `Migration` should only have to be executed **once** to
change existing data, which means that in parallel the code should be adapted so
that any new content is already stored in the "new" state. Besides that, the code
should also be able to handle date in the "old" state, as the `Migration` might
still be running while the system is already active. These code parts can then
safely be removed alongside the `Migration` itself with the next major version.


### On Artifact

Sometimes ILIAS needs information from the source code to offer certain services.
E.g.: Which base classes and command classes exist in the control structure?
Which GlobalScreen-providers exist to build the screen? Which instances of
WebAccessChecker are available. Since this information can be derived statically
for any given state of source code, it would be inefficient to derive it dynamically.

The [`BuildArtifactObjective`](Objective/BuildArtifactObjective.php) allows to create source-code
files based on the current state of the code and store them in the ILIAS-filesystem-
structure for later use.

This strategy will be faster then crawling the ILIAS code everytime the information
is required or storing that information in the database. Thanks to op-code-caching,
the information will practically be in-memory. This approach has one major downside:
When adding or changing code that is included in some artifact, the change does
not come in effect immediately, because the corresponding artifact has not been
updated. This is done via `php cli/setup.php build` or when updating
the composer class-map.

You can use your artifact with the following method which resolves to the 
path whre the artifact is stored:

```php
    $array_data = require MyArtifact::PATH();
```

#### Example: Global Screen Provider

The main visuals of ILIAS are pieced together by parts from many different components.
Entries in the main bar may be derived from various components, notifications arise
from many sources and tools are provided by different features. The [GlobalScreen-service](../../components/ILIAS/GlobalScreen_)
collects providers from all components to build the screen from contributions from
all of them. Providers are classes implementing a specific interface. These are
collected in the [`ilGlobalScreenBuildProviderMapObjective`](../../components/ILIAS/GlobalScreen_/classes/Setup/class.ilGlobalScreenBuildProviderMapObjective.php)
and stored in `components/ILIAS/GlobalScreen_/artifacts/global_screen_providers.php` as
serialized array like so:

```php
<?php return array (
  'ILIAS\\GlobalScreen\\Scope\\MainMenu\\Provider\\StaticMainMenuProvider' =>
  array (
    0 => 'ilLearningHistoryGlobalScreenProvider',

	//...

    18 => 'ilPrtfGlobalScreenProvider',
  ),
  'ILIAS\\GlobalScreen\\Scope\\MetaBar\\Provider\\StaticMetaBarProvider' =>
  array (
    0 => 'ilSearchGSMetaBarProvider',
    1 => 'ilMMCustomTopBarProvider',
  ),
  'ILIAS\\GlobalScreen\\Scope\\Tool\\Provider\\DynamicToolProvider' =>
  array (
    0 => 'ilStaffGSToolProvider',
    1 => 'ilMediaPoolGSToolProvider',
  ),
);
```

The GlobalScreen-service than reads that file later and uses the information to
determine which classes to use for which task:

```php
	/**
	 * @inheritDoc
	 */
	public function __construct(Container $dic) {
		// ...
		$this->class_loader = include "vendor/ilias/Artifacts/global_screen_providers.php";
	}
```


---

# Use the Command Line to Manage ILIAS

The ILIAS command line app can be called via `php setup\setup.php`. It contains four
main commands to manage ILIAS installations:

* `install` will [set an installation up](#install-ilias)
* `update` will [update an installation](#update-ilias)
* `status` will [report status of an installation](#report-status-of-ilias)
* `build` [recreates static assets](#build-static-assets) of an installation
* `achieve` [a named objective](#achieve-a-named-objective) of an agent 
* `migrate` will run [needed migrations](#migrations)

`install` and `update` also supply switches and options for a granular control of the inclusion of plugins:

* `--skip-legacy-plugin
* There are also named objectives for **import** and **export**. <plugin name>` will exclude the named legacy plugin from the command
* `--no-legacy-plugins` will exclude all plugins from the command
* `install <legacy plugin name>` (or `update <legacy plugin name>` respectively) will update or install the specified legacy plugin

`install` requires a [configuration file](#about-the-config-file) to do the job.
`update` can be used without this file for updating the installation only, but is
required to transfer any modified setting from this file to the installation.
The app also supports a `help` command that lists arguments and
options of the available commands.


## Install ILIAS

To install ILIAS with all plugins from the command line, call `php cli/setup.php install config.json`
from within the ILIAS folder you checked out from GitHub (or downloaded from elsewhere).
`config.json` can be the path to some [configuration file](#about-the-config-file)
which does not need to reside in the ILIAS folder. Also, `cli/setup.php` could be
the path to the `setup.php` when the command is called from somewhere else.

You most probably want to execute the setup with the user that also executes your
webserver to avoid problems with filesystem permissions. The installation creates
directories and files that the webserver will need to read and sometimes even modify.
If you need to run setup as another user, please make sure that the user that executes
the webserver has the necessary filesystem permissions (e.g. by using chown), to
avoid some errors which may be difficult to troubleshoot.

The setup will ask you to confirm some assumptions during the setup process, where
you will have to type `yes` (or `no`, of course). These checks can be overwritten
with the `--yes` option, which confirm any assumption for you automatically.

There might be cases where the setup aborts for some reasons. These reasons might
require further actions on your side which the setup cannot perform. Make sure you
read messages from the setup carefully and act accordingly. If you do not change the
config file, it is safe to execute the installation process a second time for the
same installation a during the initial setup process.

Do not discard the `config.json` you use for the installation, you will need it later
on to update that installation. If you want to overwrite specific fields in the
configuration file you can use the `--config="<path>=<value>"` option, even several
times. If you e.g. use `--config="database.password=XYZ"` the field `database.password`
from the original config will be overwritten with `XYZ`. This allows to use one
configuration for multiple setups and overwrite it from the CLI or even share
configs without secrets.

The setup will also install plugins of the installation, unless the plugin explicitely
defines that it cannot be installed via CLI setup. If you still want to skip a plugin
for installation, use the skip-option: `php cli/setup.php install --skip-legacy-plugin <plugin name> config.json`.
The option can be repeated to cover multiple plugins. If you want to skip plugins
alltogether, use the `--no-legacy-plugins` option. If you only want to install a specific
plugin, use `php cli/setup.php install config.json <plugin name>`.

The install command also offers the option to import a zip file during setup. The 
zip file must have been previously exported from another instance via export
(see [a name objective](#achieve-method)). 
The command `php cli/setup.php install --import-file <path_to_zip_file> config.json`
will install the data from the export to this instance.

## Update ILIAS

To update ILIAS from the command line, call `php cli/setup.php update`
from within your ILIAS folder. This will update ILIAS as well as update the
database of the installation or do other necessary task for the update.
This does not update the source code.
If there are changes in your config.json file call `php cli/setup.php update config.json`
from within your ILIAS folder.  This will also update the configuration of ILIAS according
to the provided configuration.

Plugins are updated just as the core of ILIAS (if the plugin does not exclude itself),
where the plugins can be controlled with the same options as for `install`.

Sometimes it might happen that the database update steps detect some edge case
or warn about a possible loss of data. In this case the update is aborted with
a message and can be resumed after the messages were read carefully and acted
upon. 
You may use the `--ignore-db-update-messages` at your own risk if you want
to silence the messages.

When an update step failed, you might get a message about inconsistent order 
of already performed steps when resuming the setup:
> step 2 was started last, but step 1 was finished last. 
> Aborting because of that mismatch.

You may reset the records for those steps by running:
```
php setup/setup.php achieve database.resetFailedSteps
```
However, be sure to understand the cause for the failing steps and tend to it before 
resetting and re-running the update.

## Report Status of ILIAS

Via `php cli/setup.php status` you can get a status of your ILIAS installation.
The command uses a best effort approach, so according to the status of your
system the output might contain more or less fields. When calling this for a
system where ILIAS was not installed, for example, the output only contains the
information that ilias is not installed. The command also reports on the configuration
of the installation.

The output of the command is formatted as YAML to be easily readable by people and
machines. So we encourage you to use this command for monitoring your system and
also request status information via our feature process that you are interested in.

Like for `install` and `update`, plugins are included here, but can be controlled
via options.


## Build Static Assets

There are two types of assets that ILIAS needs to function:

* **Artifacts** are source code files that are created based on the ILIAS source tree.
* The **Public Folder** is filled with resources from the ILIAS components to be
  served on the web.

You can refresh them by calling `php cli/setup.php build` from your
installation. Make sure you run the command with the webserver user or adjust
filesystem permissions later on, because the webserver will need to access the
generated files. Please do not invoke this function unless it is explicitly stated
in update or patch instructions or you know what you are doing.

Like for `install` and `update`, plugins are included here, but can be controlled
via options.


## Achieve a Named Objective

Some components of ILIAS will publish named objectives to the setup via their
agent. The most notorious example for this is the component `UICore` which provides
the objective `buildIlCtrlArtifacts` that will generate routing information for the
GUI. To achieve a single objective from an agent, e.g. for control structure reload,
run `php cli/setup.php achieve $AGENT_NAME.$OBJECTIVE_NAME`, e.g. 
`php cli/setup.php achieve uicore.buildIlCtrlArtifacts` to generate the necessary
artifacts for the control structure. The agent might need to a config file to work,
which may be added as last parameter: 
`php cli/setup.php achieve uicore.buildIlCtrlArtifacts config.json`

There is also a named objective for **export**. The command 
`php cli/setup.php achieve common.buildExportZip config.json` creates a zip file 'ILIAS_EXPORT.zip' at the
location of the call. The export also changes the name of the client directory to
'default' so that the import can work with the files. The objective
'ilFileSystemClientDirectoryRenamedObjective.php' takes care of the renaming.  

The ILIAS export mechanism can be extended with ExportHooks. This allows you to influence the exported database during the export.  
The ExportHooks file must be a PHP file and can be placed anywhere. It only has to be ensured that ILIAS has access to this file.  
The path to the file can either be set permanently in config.json under the namespace common.  
```bash
"common" : {
        "client_id" : "ilias",
        "master_password" : "ilias",
        "server_timezone" : "Europe/Berlin",
        "export_hooks_path" : "/var/ilias/export.php"
    }
```
Or you can specify it once when calling up the export command.
```bash
php cli/setup.php achieve common.buildExportZip --config="common.export_hooks_path=/var/ilias/export.php" config.json -y
```
This [mysqldump](https://github.com/ifsnop/mysqldump-php#changing-values-when-exporting) hooks can be used in the export hooks file.
An example file could look like this (the variable $dumper is indirectly available).
```php
<?php

$dumper->setTransformTableRowHook(function ($tableName, array $row) {
    if ($tableName === 'write_event') {
        if ($row['obj_id'] == 100) {
                $row['usr_id'] = -1;
        }
    }

    return $row;
});
```
The zip file can then be imported using the install command.

## List available objectives
Calling `php cli/setup.php achieve` without any arguments and options  
or calling `php cli/setup.php achieve --list` will list all available objectives.


# Migrations

Migrations are major changes in the ILIAS database or file system that are 
necessary after an update. Migrations can take quite a long time, which is 
why they are available separately as a command. The advantage is that you can 
perform migrations after the update when the installation is already online again. 
For more information, see [https://docu.ilias.de/goto_docu_wiki_wpage_6399_1357.html](https://docu.ilias.de/goto_docu_wiki_wpage_6399_1357.html)

The command lists available migrations:

`php cli/setup.php migrate`


```
! [NOTE] There are 1 to run:

ilFileObjectMigrationAgent.ilFileObjectToStorageMigration: Migration of File-Objects to Storage service [remaining steps: 1110]
```

Individual migrations can then be started as follows, e.g.:

`php cli/setup.php migrate --run ilFileObjectMigrationAgent.ilFileObjectToStorageMigration`

A migration must be confirmed in each case, e.g.:

``` 
Do you really want to run the following migration? Make sure you have a backup
of all your data. You will run this migration on your own risk.

Please type 'ilFileObjectToStorageMigration' to confirm and start.:
>
```

With `--yes` migrations can be confirmed automatically.

Migrations are divided into individual steps, of which there can be many depending
on the migration. A default number of steps is executed in each case; the number 
can be increased or set with `--steps=...`.

## About the Config File

The config file is a json file with the following structure. **Mandatory fields
are printed bold**, all other fields might be omitted. A minimal example is
[here](minimal-config.json).

* **common** (type: object) settings relevant for the complete installation, e.g.:
    ``` 
    "common" : {
        "client_id" : "test7",
        "server_timezone" : "Europe/Berlin",
        "register_nic" : true,
        "export_hooks_path" : "/var/ilias/export_hooks.php"
    }
    ```
  * **client_id** (type: string) is the identifier to be used for the installation 
  * *server_timezone* (type: string) where the installation resides, given as `region/city`,
    e.g. `Europe/Berlin`, defaults to `UTC`
  * *register_nic* (boolean) sends the identification number of the installation to a server
    of the ILIAS society together with some information about the installation, defaults to `false`
  * *export_hooks_path* (type: string) The path to the PHP export hooks file, not required and defaults to null if absent. Setting to an empty string results in an error during export.
* *backgroundtasks* (type: object) is a service to run tasks for users in separate processes, e.g.:
    ``` 
    "backgroundtasks" : {
        "type" : "sync",
        "max_number_of_concurrent_tasks" : 3
    },
    ``` 
  * *type* (type: string) might be `async` or `sync`, defaults to `sync`; async requires SOAP (c.f. webservices) to be enabled
  * *max_number_of_concurrent_tasks* (type: number) that all users can run together, defaults to `1`
* **database** (type: object) is required to connect to the database, e.g.:
    ```
    "database" : {
        "type" : "innodb",
        "host" : "192.168.47.11",
        "port" : 3306,
        "database" : "db_test7",
        "user" : "test7_homer",
        "password" : "homers-secret",
        "create_database" : true
    },
    ```
  * *type* (type: string) of the database, `innodb`, defaults
    to `innodb`
  * *host* (type: string) the database server runs on, defaults to `localhost`
  * *port* (type: string or number) the database server uses, defaults to `3306`
  * *database* (type: string) name to be used, defaults to `ilias`
  * **user** (type: string) to be used to connect to the database
  * *password*  (type: string) to be used to connect to the database
  * *create_database* (type: boolean) if a database with the given name does not exist? Defaults to `true`.
* **filesystem** (type: object) configuration, e.g.:
    ```
    "filesystem" : {
        "data_dir" : "/var/ilias_external_data/test7"
    },
    ```
  * **data_dir** (type: string) outside the web directory where ILIAS puts some data
* *globalcache* (type: object) is a service for caching various information, e.g.:
    ```
    "globalcache" : {
        "service" : "static",
        "components" : "all"
    },
    ```
    or
    ```
    "globalcache" : {
        "service" : "apc",
        "components" : {
            "clng" : true,
            "comp" : true,
            "events" : true,
            "global_screen" : true,
            "obj_def" : true,
            "ilctrl" : true,
            "tpl" : true,
            "tpl_blocks" : true,
            "tpl_variables" : true
        }
    },
    ```
    or
    ```
    "globalcache" : {
        "service" : "memcached",
        "components" : "all",
        "memcached_nodes" : [
            {
                "active" : true,
                "host" : "example1.com",
                "port" : 4711,
                "weight" : 10
            },
            {
                "active" : false,
                "host" : "example2.com",
                "port" : 4712,
                "weight" : 90
            }
        ]
    },
    ```
  * *service* (type: string) to be used for caching. Either `none`, `static`, `memcached`
    or `apc`, defaults to  `static`.
  * *components* (type: string or object) that should use caching. Can be `all` or any list of components that
    support caching,  (must be set too, if *service* is set)
  * *memcached_nodes* (type: array of objects) if *service* equals `memcached` place your nodes here
* **http** (type: object) configuration, e.g.:
    ```
    "http" : {
        "path" : "https://test7.ilias.de/",
		"https_autodetection" : {
			"header_name" : "my-header-name",
			"header_value" : "my-header-value"
		},
		"proxy" : {
			"host" : "webproxy.ilias.de",
			"port" : "8088"
		},
		"allowed_hosts" : [
			"red.ilias.de",
			"blue.ilias.de",
			"www.ilias.de"
		]
    },
    ```
  * **path** (type: string) to your installation on the internet
  * *https_autodetection* (type: object) allows ILIAS to be run behind a proxy that terminates ssl
    connections
    * *header_name* (type: string) that the proxy sets to indicate ssl connections
    * *header_value* (type: string) that the proxy sets for said header
  * *proxy* (type: object) for outgoing http connections
    * *host* (type: string) the proxy runs on
    * *port* (type: string or number) the proxy listens on
  * *allowed_hosts* (type: an `array`/list of strings, or `null`) A list of valid hosts which is used to
    validate the `HTTP_HOST` header of incoming web requests. If the host header does not match any of
    the allowed hosts, the request is rejected. If `null` is set or an empty list is provided, the host
    header is only validated against the host of the `path` setting
    (stored in the "ilias.ini.php" as `http_path`), which is always considered allowed.
    This also applies for the optionally configurable host used for the WSDL path definition
    in the SOAP web service configuration and for "localhost".
* *logging* (type: object) configuration if logging should be used
    ```
	"logging" : {
		"enable" : true,
		"path_to_logfile" : "/var/log/ilias_test7.log",
        "default_level" : "INFO"
		"errorlog_dir" : "/var/log/ilias_errorlogs/"
	},
    ```
  * *enable* (type: boolean) the logging, defaults to `false`
  * *path_to_logfile* (type: string) to be used for logging
  * *default_level* (type: string) default log level, possible values: `DEBUG`, `INFO`, `NOTICE`, `WARNING`, `ERROR`, `CRITICAL`, `ALERT`, `EMERGENCY`
  * *errorlog_dir* (type: string) to put error logs in
* *preview* (type: object) contains settings for ILIAS/Preview
    ```
	"preview" : {
		"path_to_ghostscript" : "/usr/bin/gs"
	},
    ```
  * *path_to_ghostscript* (type: string) executable
* *mediaobject* (type: object) contains settings for ILIAS/MediaObjects
    ```
	"mediaobject" : {
		"path_to_ffmpeg" : "/usr/bin/ffmpeg"
	},
    ```
  * *path_to_ffmpeg* (type: string) executable
* *style* (type: obejct) configuration to change the ILIAS look
    ```
	"style" : {
		"manage_system_styles" : true,
		"path_to_scss" : "/usr/bin/scss"
	},
    ```
  * *manage_system_styles* (type: boolean) via a GUI in the installation, defaults to `false`
  * *path_to_scss* (type: string) to compile scss to css
* **systemfolder** (type: object) settings for ILIAS/SystemFolder
    ```
	"systemfolder" : {
		"client" : {
			"name" : "test7",
			"description" : "Test Installation for ILIAS 7",
			"institution" : "Atomic Powerplant Springfield"
		},
		"contact" : {
			"firstname" : "Homer",
			"lastname" : "Simpson",
			"title" : "Sir",
			"position" : "Security Inspector Sector 7G",
			"institution" : "Atomic Powerplant Springfield",
			"street" : "742 Evergreen Terrace",
			"zipcode" : "12345",
			"city" : "Springfield",
			"country" : "USA",
			"phone" : "(939) 555-0113",
			"email" : "Chunkylover53@aol.com"
		}
	},
    ```
  * *client* (type: string) information
    * *name* (type: string) of the ILIAS installation
    * *description* (type: string) of the installation
    * *institution* (type: string) that provides the installation
  * **contact** (type: string) to a person behind the installation
    * **firstname** (type: string) of said person
    * **lastname** (type: string) of said person
    * *title* (type: string) of said person
    * *position* (type: string) of said person
    * *institution* (type: string) of said person
    * *street* (type: string) of said person
    * *zipcode* (type: string) of said person
    * *city* (type: string) of said person
    * *country* (type: string) of said person
    * *phone* (type: string) of said person
    * **email** (type: string) of said person
* *utilities* (type: object) contains settings for ILIAS/Utilities
    ```
	"utilities" : {
		"path_to_convert" : "/usr/bin/convert",
		"path_to_zip" : "/usr/bin/zip",
		"path_to_unzip" : "/usr/bin/unzip"
	},
    ```
  * *path_to_convert* (type: string) from ImageMagick, to resize images
  * *path_to_zip*" (type: string) to zip files
  * *path_to_unzip*" (type: string) to unzip files
* *virusscanner* (type: object) configuration
    ```
	"virusscanner" : {
		"virusscanner" : "clamav",
		"path_to_scan" : "/usr/bin/clamdscan",
		"path_to_clean" : "/usr/bin/clamdscan --remove=yes",
	},
    ```
    or
    ```
	"virusscanner" : {
		"virusscanner" : "icap",
		"icap_host" : "192.168.47.12",
		"icap_port" : 4712,
		"icap_service_name" : "icap-name",
		"icap_client_path" : "icap-client-path"
	},
    ```
  * *virusscanner* (type: string) to be used. Either `none`, `sophos`, `antivir`, `clamav` or `icap`
  * *path_to_scan* (type: string) command of the scanner
  * *path_to_clean* (type: string) command of the scanner
  * *icap_host* (type: string) host address of the icap scanner
  * *icap_port* (type: string or number) port if the icap scanner
  * *icap_service_name* (type: string) service name of the icap scanner
  * *icap_client_path* (type: string) path to the `c-icap-client`, if this is left empty, a php client will be used
* *privacysecurity* (type: object)
    ```
	"privacysecurity" : {
		"https_enabled" : true,
		"auth_duration" : 3000,
		"account_assistance_duration" : 3000,
		"registration_duration" : 3000,
	},
    ```
  * *https_enabled* (type: boolean) forces https on login page, defaults to `false`
  * *auth_duration* (type: integer) stretches the auth-duration on logins to the given amount in ms, defaults to `null`
  * *account_assistance_duration* (type: integer) stretches the password- and username-assistance duration to the given amount in ms, defaults to `null`
  * *registration_duration* (type: integer) stretches registration duration to the given amount in ms, defaults to `null`
* *webservices* (type: object)
    ```
	"webservices" : {
		"soap_user_administration" : true,
		"soap_wsdl_path" : "https://test7.ilias.de/public/soap/server.php?wsdl",
		"soap_connect_timeout" : 30,
		"rpc_server_host" : "192.168.47.13",
		"rpc_server_port" : "11112",
		"soap_internal_wsdl_path": "https://localhost/public/soap/server.php?wsdl",
		"soap_internal_wsdl_verify_peer": false,
		"soap_internal_wsdl_verify_peer_name": false,
		"soap_internal_wsdl_allow_self_signed": false
	},
    ```
  * *soap_user_administration* (type: boolean) enable administration per soap, defaults to `false`
  * *soap_wsdl_path* (type: string) path to the ilias wsdl file, default is `http:///public/soap/server.php?wsdl`
  * *soap_connect_timeout* (type: number) maximum time in seconds until a connection attempt to the SOAP-Webservice is interrupted, defaults to `10`
  * *rpc_server_host* (type: string) Java-Server host (must be set too, if *rpc_server_port* is set)
  * *rpc_server_port* (type: string or number) Java-Server port (must be set too, if *rpc_server_host* is set)
  * *soap_internal_wsdl_path* (type: string) path to the ilias wsdl file for internal usage (for calls from ilias to ilias itself), default is *soap_wsdl_path*
  * *soap_internal_wsdl_verify_peer* (type: bool) verify peer for calls from ilias to ilias itself (see https://www.php.net/manual/en/context.ssl.php for more information)
  * *soap_internal_wsdl_verify_peer_name* (type: bool) verify peer name for calls from ilias to ilias itself (see https://www.php.net/manual/en/context.ssl.php)
  * *soap_internal_wsdl_allow_self_signed* (type: bool) allow self signed certificates for calls from ilias to ilias itself (see https://www.php.net/manual/en/context.ssl.php)
* *chatroom* (type: object) see also [Chat Server Setup](/components/ILIAS/Chatroom/README.md), eg.:
    ```
	"chatroom" : {
		"address" : "192.168.47.14",
		"port" : 8081,
		"sub_directory" : "/chat",
		"https" : {
			"cert" : "/etc/ssl/certs/server.pem",
			"key" : "/etc/ssl/private/server.key",
			"dhparam" : "/etc/ssl/private/dhparam.pem"
		},
		"log" : "/var/log/ilias_onscreenchat/access.log",
		"log_level" : "info",
		"error_log" : "/var/log/ilias_onscreenchat/error.log",
		"ilias_proxy" : {
			"ilias_url" : "https://chat-ilias-proxy.ilias.de"
		},
		"client_proxy" : {
			"client_url" : "https://chat-client-proxy.ilias.de"
		},
		"deletion_interval" : {
			"deletion_unit" : "months",
			"deletion_value" : "6",
			"deletion_time" : "23:45"
		}
	}
    ```
  * *address* (type: string) IP-Address/FQN of Chat Server
  * *port* (type: string or number) of the chat server, possible value from `1` to `65535` 
  * *sub_directory* (type: string) http(s)://[IP/Domain]/[SUB_DIRECTORY]
  * *https* (type: object) adding this enables https
    * *cert* (type: string) absolute server path to the SSL certificate file e.g. `/etc/ssl/certs/server.pem`
    * *key* (type: string) absolute server path to the private key file e.g. `/etc/ssl/private/server.key`
    * *dhparam* (type: string) absolute server path to a file e.g. `/etc/ssl/private/dhparam.pem`
  * *log* (type: string) absolute server path to the chat server's log file e.g. `/var/www/ilias/data/chat.log`
  * *log_level* (type: string) possible values are `emerg`, `alert`, `crit` `error`, `warning`, `notice`, `info`, `debug`, `silly`, defaults to `warning`
  * *error_log* (type: string) absolute server path to the chat server's error log file e.g. `/var/www/ilias/data/chat_error.log`
  * *ilias_proxy* (type: object) ILIAS to Server Connection
    * *ilias_url* (type: string) URL for the Server connection
  * *client_proxy* (type: object) Client to Server Connection
    * *client_url* URL for the Server connection
  * *deletion_interval* (type: object)
    * *deletion_unit* (type: string) possible values are `days`, `weeks`, `months`, `years`
    * *deletion_value* (type: string or number) depending on `deletion_unit` possible values are `days max 31`, `weeks max 52`, `months max 12`, `years no max`
    * *deletion_time* (type: string) with format `HH:MM e.g. 23:30`
* *authentication* (type: object)
  ```
  "authentication" : {
    "session_max_idle": 1800
  }
  ```
  * *session_max_idle* (type: number) maximum session idle (in seconds)
