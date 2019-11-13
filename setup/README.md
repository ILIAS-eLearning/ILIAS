# Use the Command Line to Manage ILIAS

The ILIAS command line app can be called via `php setup.php`. It contains three
commands to manage ILIAS installations:

* `install` will [set an installation up](#install-ilias)
* `update` will [update an installation](#update-ilias)
* `build-artifacts` recreates static assets of an installation(#build-ilias-artifacts)

`install` and `update` both require a [configuration file](#about-the-config-file)
to do their job.


## Install ILIAS

To install ilias from the command line, use `php $PATH_TO_CLI_PHP $PATH_TO_CONFIG_JSON",
where $PATH_TO_CLI_PHP points to the file `setup/cli.php` from the source of the
installation you want to set up and $PATH_TO_CONFIG_JSON points to a [configuration file](#about-the-config-file).

You might want to run the command with the user that also executes the webserver
to avoid problems with filesystem permissions. The installation creates directories
and files that the webserver will need to read and sometimes even modify.

## Update ILIAS

TBD

## Build ILIAS Artifacts

TBD

## About the Config File

The config file is a json file with the following structure. **Mandatory fields
are printed bold**, all other fields might be ommitted. A minimal example is [here](minimal-config.json).

* **common** settings relevant for the complete installation 
  * **client_id** is the identifier to be used for the installation 
  * **master_password** is used to identify at the web version of the setup
  * **server_timezone** where the installation resides, given as `region/city`, e.g. `Europe/Berlin`
* *backgroundtasks* is a service to run tasks for users in separate processes
  * *type* might be `async` or `sync` and defaults to `sync`
  * *max_number_of_concurrent_tasks* that all users can run together
* **database** is required to connect to the database
  * **type** of the database, one of `innodb`, `mysql`, `postgres`, `galera`
  * **host** the database server runs on
  * **database** name to be used
  * **user** to be used to connect to the database
  * **password**  to be used to connect to the database
  * **create_database** if a database with the given name does not exist?
* **filesystem** configuration
  * **data_dir** outside the web directory where ILIAS puts some data
* *globalcache* is a service for caching various information
  * *service* to be used for caching. Either `none`, `static`, `xcache`, `memcached` or `apc`
  * *components* that should use caching. Can be `all` or any list of components that support caching.
* **http** configuration
  * **path** to your installation on the internet
  * *https_autodetection* allows ILIAS to be run behind a proxy that terminates ssl connections
    * *header_name* that the proxy sets to indicate ssl connections
    * *header_value* that the proxy sets for said header
  * *proxy* for outgoing http connections
    * *host* the proxy runs on
    * *port* the proxy listens on
* **language** configuration
  * **default_language** language to be used for users
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
  * **client** information
    * **name** of the ILIASinstallation
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
* **utilities** contains settings for Services/Utilities
  * **path_to_convert** from ImageMagick, to resize images
  * **path_to_zip**" to zip files
  * **path_to_unzip**" to unzip files
* *virusscanner* configuration
  * *virusscanner* to be used. Either `none`, `sophos`, `antivir` or `clamav`
  * *path_to_scan* command of the scanner
  * *path_to_clean* command of the scanner



 
