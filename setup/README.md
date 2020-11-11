# Use the Command Line to Manage ILIAS

The ILIAS command line app can be called via `php setup\setup.php`. It contains three
commands to manage ILIAS installations:

* `install` will [set an installation up](#install-ilias)
* `update` will [update an installation](#update-ilias)
* `status` will [report status of an installation](#report-status-of-ilias)
* `build-artifacts` [recreates static assets](#build-ilias-artifacts) of an installation
* `reload-control-structure` [rebuilds structure information](#build-ilias-artifacts) of an installation

`install` and `update` both require a [configuration file](#about-the-config-file)
to do their job. The app also supports a `help` command that lists arguments and
options of the available commands.


## Install ILIAS

To install ILIAS from the command line, call `php setup/setup.php install config.json"
from within the ILIAS folder you checked out from GitHub (or downloaded from elsewhere).
`config.json` can be the path to some [configuration file](#about-the-config-file)
which does not need to reside in the ILIAS folder. Also, `setup/setup.php` could be
the path to the `setup.php` when the command is called from somewhere else.

You most probably want to execute the setup with the user that also executes your
webserver to avoid problems with filesystem permissions. The installation creates
directories and files that the webserver will need to read and sometimes even modify.

The setup will ask you to confirm some assumptions during the setup process, where
you will have to type `yes` (or `no`, of course). These checks can be overwritten
with the `--yes` option, which confirm any assumption for you automatically.

There might be cases where the setup aborts for some reasons. These reasons might
require further actions on your side which the setup can not perform. Make sure you
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


## Update ILIAS

To update ILIAS from the command line, call `php setup/setup.php update config.json`
from within your ILIAS folder. This will update the configuration of ILIAS according
to the provided configuration as well as update the database of the installation or
do other necessary task for the update. This does not update the source code.

Sometimes it might happen that the database update steps detect some edge case
or warn about a possible loss of data. In this case the update is aborted with
a message and can be resumed after the messages was read carefully and acted
upon. You may use the `--ignore-db-update-messages` at your own risk if you want
to silence the messages.


## Report Status of ILIAS

Via `php setup/setup.php status` you can get a status of your ILIAS instalaltion.
The command uses a best effort approach, so according to the status of your
system the output might contain more or less fields. When calling this for an
system where ILIAS was not installed, for example, the output only contains the
information that ilias is not installed. The command also reports on the configuration
of the installation.

The output of the command is formatted as YAML to be easily readable by people and
machines. So we encourage you to use this command for monitoring your system and
also request status information via our feature process that you are interested in.


## Build ILIAS Artifacts

Artifacts are source code files that are created based on the ILIAS source tree.
You can refresh them by calling `php setup/setup.php build-artifacts` from your
installation. Make sure you run the command with the webserver user or adjust
filesystem permissions later on, because the webserver will need to access the
generated files. Please do not invoke this function unless it is explicitly stated
in update or patch instructions or you know what you are doing.


## Reload ILIAS Control Structure

The control structure captures information about components and GUIs of ILIAS
in the database. Sometimes it might be necessary to refresh that information.
Please do not invoke this function unless it is explicitly stated in update
or patch instructions or you know what you are doing.


## About the Config File

The config file is a json file with the following structure. **Mandatory fields
are printed bold**, all other fields might be ommitted. A minimal example is
[here](minimal-config.json).

* **common** settings relevant for the complete installation 
  * **client_id** is the identifier to be used for the installation 
  * *server_timezone* where the installation resides, given as `region/city`,
    e.g. `Europe/Berlin`. Defaults to `UTC`.
  * *register_nic* sends the identification number of the installation to a server
    of the ILIAS society together with some information about the installation.
* *backgroundtasks* is a service to run tasks for users in separate processes
  * *type* might be `async` or `sync` and defaults to `sync`
  * *max_number_of_concurrent_tasks* that all users can run together
* **database** is required to connect to the database
  * *type* of the database, one of `innodb`, `mysql`, `postgres`, `galera`, defaults
    to `innodb`
  * *host* the database server runs on, defaults to `localhost`
  * *port* the database server uses, defaults to `3306`
  * *database* name to be used, defaults to `ilias`
  * **user** to be used to connect to the database
  * **password**  to be used to connect to the database
  * **create_database** if a database with the given name does not exist? Defaults
    to `true`
* **filesystem** configuration
  * **data_dir** outside the web directory where ILIAS puts some data
* *globalcache* is a service for caching various information
  * *service* to be used for caching. Either `none`, `static`, `xcache`, `memcached`
    or `apc`
  * *components* that should use caching. Can be `all` or any list of components that
    support caching.
* **http** configuration
  * **path** to your installation on the internet
  * *https_autodetection* allows ILIAS to be run behind a proxy that terminates ssl
    connections
    * *header_name* that the proxy sets to indicate ssl connections
    * *header_value* that the proxy sets for said header
  * *proxy* for outgoing http connections
    * *host* the proxy runs on
    * *port* the proxy listens on
* **language** configuration
  * *default_language* language to be used for users, defaults to `en`
  * *install_languages* defines all languages that should be available in a list
  * *install_local_languages* defines all languages with a local language file
* *logging* configuration if logging should be used
  * *enable* the logging 
  * *path_to_logfile* to be used for logging
  * *errorlog_dir* to put error logs in
* *mathjax* contains settings for Services/MathJax
  * *path_to_latex_cgi* executable
* *pdfgeneration* contains settings for Services/PDFGeneration
  * *path_to_phantom_js* executable
* *preview* contains settings for Services/Preview
  * *path_to_ghostscript* executable
* *mediaobject* contains settings for Services/MediaObjects
  * *path_to_ffmpeg* executable
* *style* configuration to change the ILIAS look
  * *manage_system_styles* via a GUI in the installation
  * *path_to_lessc* to compile less to css
* **systemfolder** settings for Module/SystemFolder
  * *client* information
    * *name* of the ILIASinstallation
    * *description* of the installation
    * *institution* that provides the installation
  * **contact** to a person behind the installation
    * **firstname** of said person
    * **lastname** of said person
    * *title* of said person
    * *position* of said person
    * *institution* of said person
    * *street* of said person
    * *zipcode* of said person
    * *city* of said person
    * *country* of said person
    * *phone* of said person
    * **email** of said person
* *utilities* contains settings for Services/Utilities
  * *path_to_convert* from ImageMagick, to resize images
  * *path_to_zip*" to zip files
  * *path_to_unzip*" to unzip files
* *virusscanner* configuration
  * *virusscanner* to be used. Either `none`, `sophos`, `antivir`, `clamav` or `icap`
  * *path_to_scan* command of the scanner
  * *path_to_clean* command of the scanner
  * *icap_host* host adress of the icap scanner
  * *icap_port* port if the icap scanner
  * *icap_service_name* serivce name of the icap scanner
  * *icap_client_path* path to the `c-icap-client`, if this is left empty, a php client will be used



 
