# ILIAS Installation

# Table of Contents

<!-- MarkdownTOC depth=0 autolink="true" bracket="round" autoanchor="true" style="ordered" indent="   " -->

1. [System Requirements](#system-requirements)
   1. [Hardware](#hardware)
   1. [Recommended Setup for Running ILIAS](#recommended-setup-for-running-ilias)
   1. [Database Recommendations](#database-recommendations)
   1. [Reference System](#reference-system)
   1. [Other Platforms or Configurations](#other-platforms)
1. [Installation on Linux](#manual-installation-on-linux)
   1. [Install Dependencies](#install-dependencies)
      1. [Apache Installation and Configuration](#apache-installation)
      1. [PHP Installation and Configuration](#php-installation)
      1. [Database Installation/Configuration](#database-installation)
         1. [MySQL Strict Mode \(5.6+\)](#mysql-strict-mode-56)
         1. [MySQL Performance Tuning \(optional\)](#mysql-performance-tuning-optional)
      1. [Install other Depedencies](#install-other-depedencies)
         1. [Optional Dependencies](#optional-dependencies)
   1. [Get the code](#get-code)
   1. [Install ILIAS](#install-ilias)
   1. [Hardening and Security](#hardening-and-security)
   1. [Configure ILIAS Java RPC server \(optional\)](#java-rpc-configuration)
   1. [Configure E-Mail \(optional\)](#e-mail-configuration)
   1. [Configure WebDAV \(optional\)](#webdav-configuration)
      1. [WebDAV with Windows Explorer \)](#webdav-windows-explorer)
      1. [WebDAV with Mac Finder\)](#webdav-mac-finder)
   1. [Install Plugins (optional)](#install-plugins-and-styles)
1. [Customizing ILIAS](#customizing-ilias)
   1. [Plugin Repository](#plugin-repository)
1. [Upgrading ILIAS](#upgrading-ilias)
   1. [Minor Upgrade](#minor-upgrade)
   1. [Major Upgrade](#major-upgrade)
   1. [Database Update](#database-update)
   1. [Information on Updates](#information-on-updates)
1. [Upgrading Dependencies](#upgrading-dependencies)
   1. [PHP](#php)
   1. [DBMS](#dbms)
   1. [ImageMagick](#imagemagick)
1. [Connect and Contribute](#connect-and-contribute)

<!-- /MarkdownTOC -->

<a name="system-requirements"></a>
# System Requirements

The necessary hardware to run an ILIAS installation is always dependent from the number of users and the kind of usage.

<a name="hardware"></a>
## Hardware

The hardware requirements for ILIAS vary widely, depending on the number of concurrent
users you expect and the features you want to enable. Please be aware that ILIAS
is not a webpage, but a highly interactive application, thus requirements will be
higher than in case of the former. Snappiness of the system will highly depend on
deploying enough resources and tailoring the system to your needs. In any case we
recommend an absolute minimum of a common dual core server CPU, **4GB of RAM**,
and a 100 Mbit/s internet connection. Roughly estimated the operating system and
ILIAS itself will use around **25GB**. From there you can calculate your storage
needs based on the amount of files and media content you expect to upload plus a
few GBs for the database.


<a name="recommended-setup-for-running-ilias"></a>
## Recommended Setup for Running ILIAS

For best results we recommend:

  * a current version of Debian GNU Linux, Ubuntu or RHEL
  * MySQL 5.7.x or MariaDB 10.2
  * PHP 7.4
  * Apache 2.4.x with `mod_php`
  * ImageMagick 6.8+
  * php-gd, php-xml, php-mysql, php-mbstring
  * OpenJDK 11
  * zip, unzip
  * Node.js: 12 (LTS)
  * git
  * composer v2
  * a contemporary browser supporting ES6, CSS3 and HTML 5

Package names may vary depending on the Linux distribution.


<a name="database-recommendations"></a>
## Database Recommendations

> Please note that installing ILIAS in utf8mb4-collations is currently not supported!
> ILIAS supports utf8-collations with 3 bytes per character, such as `utf8_general_ci`,
> only.

We RECOMMEND to use MySQL/MariaDB with the following settings:

  * InnoDB storage engine
  * Character Set: `utf8`
  * Collation: `utf8_general_ci`
  * `query_cache_size` > 16M
  * `join_buffer_size` > 128.0K
  * `table_open_cache` > 400
  * `innodb_buffer_pool_size` > 2G (depending on DB size)

On MySQL 5.8+ and Galera the `Strict SQL Mode` must be disabled. See [MySQL Strict Mode](#mysql-strict-mode-56) for details.

On MySQL/MariaDB `innodb_large_prefix` must be set to `OFF` if the `ROW_FORMAT`
is set to `COMPACT`.

<a name="reference-system"></a>
## Reference System

The ILIAS Testserver (https://test7.ilias.de) is currently configured as follows:

| Package        | Version                     |
|----------------|-----------------------------|
| Distribution   | Ubuntu 20.04.2 LTS          |
| MariaDB        | 10.3                        |
| PHP            | 7.2.34                      |
| Apache         | 2.4.41                      |
| zip            | 3.0                         |
| unzip          | 6.00                        |
| JDK            | 1.8.0_292                   |
| NodeJS         | v10.24.1                    |
| wkhtmltopdf    | 0.12.6                      |
| Ghostscript    | 9.50                        |
| Imagemagick    | 6.9.10-23 Q16               |
| MathJax        | 2.7.9                       |


<a name="other-platforms"></a>
## Other Platforms or Configurations

Please note that other platforms and configurations should be possible, but it
might be harder to find someone who can help when things go south. You should not
use a different configuration unless you are an experienced system administrator.


<a name="installation-on-linux"></a>
# Installation on Linux

<a name="install-dependencies"></a>
## Install Dependencies

Depending on your Linux Distribution you have several ways to install the required
dependencies. We recommend to always use your distributions package manager to
keep your packages up to date in an easy manner avoiding security issues.

<a name="apache-installation"></a>
### Apache Installation and Configuration

On Debian/Ubuntu execute:
```
apt-get install apache2
```

On RHEL/CentOS execute:
```
yum install httpd
```

Usually Apache ships with a default configuration (e.g. `/etc/apache2/sites-enabled/000-default.conf`
on Debian). A minimal configuration may look as follows:

```
<VirtualHost *:80>
    ServerAdmin webmaster@example.com

    DocumentRoot /var/www/html/
    <Directory /var/www/html/>
        Options FollowSymLinks -Indexes
        AllowOverride All
        Require all granted
    </Directory>

    # Possible values include: debug, info, notice, warn, error, crit,
    # alert, emerg.
    LogLevel warn

    ErrorLog /var/log/apache2/error.log
    CustomLog /var/log/apache2/access.log combined
</VirtualHost>
```

In order to secure access to the files in your `data` directory, you should
enable `mod_rewrite` on Debian/Ubuntu (should be enabled by default on
RHEL/CentOS):

```
a2enmod rewrite
```

Please take care to [restrict access to the setup-folder](#secure-installation-files)
Normal users should not be able to access the setup at all. Also see
[Hardening and Security Guidance](#hardening-and-security-guidance) for further
security enhancing configuration.

After changing the configuration remember to reload the web server daemon:

On Debian/Ubuntu:
```
systemctl restart apache2.service
```

On RHEL/CentOS:
```
systemctl restart httpd.service
```

<a name="php-installation"></a>
### PHP Installation and Configuration

Refer to the to documentation of your installation to install either PHP 7.3 to
PHP 7.4 including packages for gd, mysql, mbstring, curl, dom, zip and xml.

To check if the installation was successfull create the file `/var/www/html/phpinfo.php`
with the following contents:

```
<?php
phpinfo();
```

Then point your browser to ```http://yourservername.org/phpinfo.php```. If you see
the content of the file as shown above your configuration is **not** working. If
you can see details of your PHP Configuration everything works fine. Search for
the entry ```Loaded configuration file``` as we now made some changes to it (e.g.
`/etc/php5/apache2/php.ini`). Delete the file `phpinfo.php` afterwards.

We recommend the following settings for your php.ini:

```
; you may choose higher values for max_execution_time and memory_limit
max_execution_time = 600
memory_limit = 512M

error_reporting = E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT ; PHP 5.4.0 and higher
display_errors = Off

; or any higher values for post_max_size and upload_max_filesize
post_max_size = 256M
upload_max_filesize = 256M

; choose a non-zero value for session.gc_probability, otherwise old session data will not be deleted
session.gc_probability = 1
session.gc_divisor = 100
session.gc_maxlifetime = 14400
session.hash_function = 0
session.cookie_httponly = On
session.save_handler = files ; for ILIAS setup, ILIAS installations override this
; If you installation is served via HTTPS also use:
session.cookie_secure = On

; for chat server since ILIAS 4.2
allow_url_fopen = 1

; How many GET/POST/COOKIE input variables may be accepted
max_input_vars = 10000
```

Restart the apache webserver after you installed and configured your dependencies.

Please see [Hardening and Security Guidance](#hardening-and-security-guidance)
for [HTTPS configuration](#enable-http-strict-transport-security) and further
security relevant configuration.

Remember to reload your web server configuration to apply those changes.

<a name="database-installationconfiguration"></a>
### Database Installation/Configuration

On Debian/Ubuntu execute:
```
apt-get install mysql-server
```

On RHEL/CentOS execute:
```
yum install mariadb
```

We recommend to create a dedicated database user for ILIAS:

```
mysql -u root -p
CREATE DATABASE ilias CHARACTER SET utf8 COLLATE utf8_general_ci;
CREATE USER 'ilias'@'localhost' IDENTIFIED BY 'password';
GRANT LOCK TABLES on *.* TO 'ilias'@'localhost';
GRANT ALL PRIVILEGES ON ilias.* TO 'ilias'@'localhost';
FLUSH PRIVILEGES;
```

<a name="mysql-strict-mode-57"></a>
#### MySQL Strict Mode (5.7+)

With MySQL 5.7+ you might see SQL errors like:

```
SQLSTATE[42000]: Syntax error or access violation: 1055 Expression #1 of
SELECT list is not in GROUP BY clause and contains nonaggregated column
'yourdbname.tblname.foobar' which is not functionally dependent on
columns in GROUP BY clause; this is incompatible with sql_mode=only_full_group_by
```

As a workaround `STRICT_TRANS_TABLES`, `STRICT_ALL_TABLES` and `ONLY_FULL_GROUP_BY`
must be disabled. To do so create the file `/etc/mysql/conf.d/disable_strict_mode.cnf`
and enter the following (or add it to `/etc/mysql/my.cnf`):

```
[mysqld]
sql_mode=IGNORE_SPACE,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION
```

After restarting the MySQL-Server use the following command to confirm the changes:

```
mysql -i -BN -e 'SELECT @@sql_mode' | grep -E 'ONLY_FULL_GROUP_BY|STRICT_TRANS_TABLES|STRICT_ALL_TABLES'
```

If strict mode is disabled, there will be no output.

<a name="mysql-optimization"></a>
#### MySQL Performance Tuning (Optional)

You may want to to use [MySQLTuner-perl](https://github.com/major/MySQLTuner-perl)
to optimize your MySQL configuration (e.g. `/etc/mysql/my.cnf`). Run `mysqltuner.pl`
after several days of using ILIAS in production.

<a name="install-other-depedencies"></a>
### Install other Dependencies

```
apt-get install zip unzip imagemagick openjdk-7-jdk
```

On Debian/Ubuntu execute:
```
apt-get install zip unzip imagemagick openjdk-8-jdk
```

On RHEL/CentOS execute:
```
yum install zip unzip libxslt ImageMagick java-1.8.0-openjdk
```

Restart the apache webserver after you installed dependencies!

<a name="optional-dependencies"></a>
### Optional Dependencies

Depending on your use case, you MAY want to install further dependencies (exact package names vary by distribution and PHP version you are using):

* php7.3-curl
* php7.3-xmlrpc
* php7.3-soap
* php7.3-ldap
* ffmpeg
* mimetex

Restart the apache webserver after you installed dependencies!

<a name="get-code"></a>
## Get the Code

You can [download](http://www.ilias.de/docu/goto.php?target=st_229) the latest
ILIAS release or clone it from [GitHub](https://github.com/ILIAS-eLearning/ILIAS).
For production use make sure to checkout the latest stable release, not the trunk,
which is the development branch of the repository.

We recommend to clone from GitHub and use git to update the code, since this simplifies
the update to future releases and versions.

Clone the code to the web servers docroot (e.g. `/var/www/html`) with the following
commands:

```
cd /var/www/html/
git clone https://github.com/ILIAS-eLearning/ILIAS.git . --single-branch
git checkout release_X
```

or unpack the downloaded archieve to the docroot. Replace `release_X` with the
branch or tag you actually want to install.

The repository of ILIAS doesn't contain all code that is required to run. To
download the required PHP-dependencies and to create static artifacts from
the source, run the following in your ILIAS folder:

```
composer install --no-dev
```

This requires that the php dependency manager [composer](https://getcomposer.org/)
is available in your $PATH.

Create a directory outside the web servers docroot (e.g. `/var/www/files`). Make
sure that the web server is the owner of the files and directories that were created
by changing the group and owner to www-data (on Debian/Ubuntu) or apache (on RHEL).

```
chown www-data:www-data `/var/www/html
chown www-data:www-data `/var/www/files
```

The commands above will directly serve ILIAS from the docroot.

<a name="install-ilias"></a>
## Install ILIAS

After having all dependencies installed and configured you should be able to run
the [ILIAS Setup on the command-line](../../setup/README.md).

To do so, create a configuration file for the setup by copying the [minimal-config.json](../../setup/minimal-config.json)
to a location outside your docroot. Fill in the configuration fields that are already
contained in the minimal config. Have a look into the [list of available config options](../../setup/README.md#about-the-config-file)
and add the fields that your environment and installation requires. A typical
configuration might look like this afterwards:

```
{
	"common" : {
		"client_id" : "myilias"
	},
	"database" : {
		"user" : "ilias_user",
		"password" : "my_password"
	},
	"filesystem" : {
		"data_dir" : "/var/www/files"
	},
	"http" : {
		"path" : "http://demo1.cat06.de"
	},
    "language" : {
        "default_language" : "de",
        "install_languages" : ["de"]
    },
    "logging" : {
        "enable" : true,
        "path_to_logfile" : "/var/www/logs/ilias.log",
        "errorlog_dir" : "/var/www/logs/"
    },
	"systemfolder" : {
		"contact" : {
			"firstname" : "Richard",
			"lastname" : "Klees",
			"email" : "richard.klees@concepts-and-training.de"
		}
	},
    "utilities" : {
        "path_to_convert" : "/usr/bin/convert",
        "path_to_zip" : "/usr/bin/zip",
        "path_to_unzip" : "/usr/bin/unzip"
    }
}
```

Run the ILIAS command line setup from within your ILIAS folder with your configuration
file (located outside your doc-root!) as a parameter:

```
php setup/setup.php install /foo/bar/my-configuration.json
```

The installation will display what currently happens and might prompt you with
questions. You might want to have a look into the [documenation of the command line setup](../../setup/README.md)
or into the help of the program itself `php setup/setup.php help`. It is the tool
to manage and monitor your ILIAS installation.


<a name="hardening-and-security-guidance"></a>
## Hardening and Security Guidance

We recommend to perform a threat analysis for your ILIAS installation, as every
prudent admin should do for his resources. In our [security guide](./secure.md)
we show techniques and strategies to be used to secure your ILIAS installation
according to your needs.

<a name="java-rpc-configuration"></a>
## Configure ILIAS Java RPC server (optional)

The ILIAS Java RPC server is used for certain optional functions as Lucene Search
or generating PDF Certificates. To enable the RPC server you need to place a
configuration file in `<YOUR_ILIAS_DIR>/Services/WebServices/RPC/lib/ilServer.properties`:

```
[Server]
IpAddress = localhost
Port = 11111
IndexPath = /var/www/html/ilias/data/
LogFile = /var/www/files/ilServer.log
LogLevel = WARN
NumThreads = 1
RamBufferSize = 256
IndexMaxFileSizeMB = 500

[Client1]
ClientId = ACMECorp
NicId = 0
IliasIniPath = /var/www/html/ilias/ilias.ini.php
```

ILIAS can generate a proper configuration file via the Administration menu
("Administration -> General Settings -> Server -> Java-Server -> Create
Configuration File"). Please note that the configuration file is not directly
written to the file system, you MUST copy the displayed content and create the
file manually.

You may use the following systemd service description to start the RPC server.
If you still use SysV-Initscripts you can find one in the
[Lucene RPC-Server](../../Services/WebServices/RPC/lib/README.md) documentation.

```
[Unit]
Description=ILIAS RPC Server
After=network.target

[Service]
Environment=JAVA_OPTS="-Dfile.encoding=UTF-8"
Environment=ILSERVER_JAR="/var/www/html/ilias/Services/WebServices/RPC/lib/ilServer.jar"
Environment=ILSERVER_INI="/var/www/html/ilias/Services/WebServices/RPC/lib/ilServer.properties"

ExecStart=-/usr/bin/java $JAVA_OPTS -jar $ILSERVER_JAR $ILSERVER_INI start
ExecStop=/usr/bin/java $JAVA_OPTS -jar $ILSERVER_JAR $ILSERVER_INI stop

[Install]
WantedBy=multi-user.target
```

At this point the RPC server will generate PDF certificates, but to use Lucence
search further step are needed. See
[Lucene RPC-Server](../../Services/WebServices/RPC/lib/README.md) for details.

<a name="e-mail-configuration"></a>
## Configure E-Mail (optional)

You may use whatever MTA you like to send e-mail generated by ILIAS. We recommend
to use an already existing smarthost (mailhub). A very simple way to do so is
using ```ssmtp```:

On Debian/Ubuntu execute:
```
apt-get install ssmtp
```

On RHEL/CentOS execute:
```
yum install ssmtp
```

The configuration file for SSMTP (e.g. ```/etc/ssmtp/ssmtp.conf ```) may look as
follows:

```
#
# Config file for sSMTP sendmail
#
# The person who gets all mail for userids < 1000
# Make this empty to disable rewriting.
root=yourmail@mail.com

# The place where the mail goes. The actual machine name is required no
# MX records are consulted. Commonly mailhosts are named mail.domain.com
mailhub=smtp.yourmail.com

# Where will the mail seem to come from?
rewriteDomain=yourservername.org

# The full hostname
hostname=yourserver.example.com

# Are users allowed to set their own From: address?
# YES - Allow the user to specify their own From: address
# NO - Use the system generated From: address
FromLineOverride=YES
```

<a name="webdav-configuration"></a>
## Configure WebDAV (optional)

The recommended webserver configuration is either **Apache with mod_php** or
**Nginx with PHP-FPM (> 1.3.8)**. Do NOT use **Apache with PHP-FPM** if you
want to use WebDAV. Find more information about the configuration of WebDAV
in the [WebDAV Readme](../../Services/WebDAV/README.md).


<a name="install-plugins-and-styles"></a>
### Install Plugins and Styles

Plugins are the way to add new functionality to your ILIAS installation. Do not
change the core files, or you will not be able to update your installation easily.
To develop plugins, you can start in our [development guide](http://www.ilias.de/docu/goto.php?target=st_27029).
A variety of free plugins is provided from our community via the [ILIAS Plugin Repository](http://www.ilias.de/docu/goto.php?target=cat_1442&client_id=docu).

Custom styles are the way to modify the look of your ILIAS installation. Have
a look in the [documentation of the System Styles and Custom Styles](../../templates/Readme.md)
to learn how to build and install them.


<a name="upgrading-ilias"></a>
# Upgrading ILIAS

The easiest way to update ILIAS is using git, please note that this is only possible
if you installed ILIAS via git as advised in this document. If git wasn't used you
can also [download](http://www.ilias.de/docu/goto.php?target=st_229) new releases.

Before you start you should consider to...

  * backup your database
  * backup your docroot
  * change your system style to the `Delos` default

We also recommend to use a decent staging environment to make a pre-flight check,
to see if all plugins, skins, etc. still working as expected with the new version.


<a name="minor-upgrade"></a>
## Minor Upgrade

To apply a minor update (e.g. v7.1 to v7.2) execute the following command in
your ILIAS basepath (e.g. `/var/www/html/`):

```
git pull
composer install --no-dev
```

if you follow a branch or

```
git fetch
git checkout v7.1
composer install --no-dev
```

if you use tags to pin a specific ILIAS version.

In case of merge conflicts refer to [the ILIAS Developement Guide](http://www.ilias.de/docu/goto.php?target=pg_15604). 
You should only encounter these if you changed the code of your installation
locally.

Then complete the update by [updating the database](#database-update).

<a name="major-upgrade"></a>
## Major Upgrade

To apply a major update (e.g. v6.13 to 7.0) please check that your OS has the
[proper dependency versions](#upgrading-dependencies) installed. If everything
is fine change your default skin to Delos and apply this change at least to
your root user. Otherwise ILIAS might become unusable due to changes in the
layout templates. Then execute the following commands in your ILIAS basepath
(e.g. `/var/www/html`).

```
git fetch
git checkout release_X
composer install --no-dev
```

Replace `release_X` with the branch or tag you actually want to upgrade to. You can
get a list of available branches by executing `git branch -a` and a list of
all available tags by executing `git tag`. Never use `trunk` or ```*beta``` for
production.

In case of merge conflicts refer to [the ILIAS Developement Guide](http://www.ilias.de/docu/goto.php?target=pg_15604). 
You should only encounter these if you changed the code of your installation
locally.

In case of merge conflicts refer to [Resolving Conflicts - ILIAS Development Guide](http://www.ilias.de/docu/goto.php?target=pg_15604).

Complete the update of the base system by [updating the database](#database-update).

As a last step you should log in with a User using your custom skin. If everything
works fine change back from Delos to your custom system style. If not, you probably
will need to update your style to match the new release.

<a name="database-update"></a>
## Update the Database

Database updates must be done for both minor and major updates, the schema and content
of the database probably won't match the code otherwise. Database updates are performed
via the [command line setup program](../../setup/README.md). The required updates
are split into two groups. **Updates** are tasks that need to be run immediately to
make your installation work properly. **Migrations** are tasks, that potentially take
some time, but which can also be executed while the installation is in productive use.

Run the `status` command on the command line to check if there are any updates
available. Look into the section `database` of the output and check the fields
`update_required`, `hotfix_required` and `custom_update_required`. If any of these
fields is `true`, run in your ILIAS folder:

```
php setup/setup.php update
```

To check if there are migrations, run in your ILIAS folder:

```
php setup/setup.php migrate
```

The command will show you if there are migrations that need to be run for you
installation. Run them by using the `--run` parameter and have a look into
the help of the command for more details: `php setup/setup.php migrate --help`.

Both commands will display what currently happens and might prompt you with
questions. You might want to have a look into the [documenation of the command line setup](../../setup/README.md)
or into the help of the program itself `php setup/setup.php help`. It is the tool
to manage and monitor your ILIAS installation.

To check which update step gets currently executed run the following SQL-Statement
on your ILIAS database: `SELECT * FROM `settings` WHERE keyword = "db_update_running"`.


## Information on Updates

To keep your ILIAS Installation secure and healthy it is important that you keep
it up to date. To get informations about updates and security fixes you should
consider to subscribe to the [ILIAS Admin Mailing-List](http://lists.ilias.de/cgi-bin/mailman/listinfo/ilias-admins)

<a name="upgrading-dependencies"></a>
# Upgrading Dependencies

When you upgrade from rather old versions please make sure that the dependencies,
like MySQL and PHP, are up to date. Below you will find the supported versions for
each ILIAS release.

<a name="php"></a>
## PHP

| ILIAS Version   | PHP Version                           |
|-----------------|---------------------------------------|
| 7.x             | 7.3.x, 7.4.x                          |
| 6.x             | 7.2.x, 7.3.x, 7.4.x                   |
| 5.4.x           | 7.2.x, 7.3.x, 7.4.x                   |
| 5.3.x           | 5.6.x, 7.0.x, 7.1.x                   |
| 5.2.x           | 5.5.x - 5.6.x, 7.0.x, 7.1.x           |
| 5.0.x - 5.1.x   | 5.3.x - 5.5.x                         |
| 4.4.x           | 5.3.x - 5.5.x                         |
| 4.3.x           | 5.2.6 - 5.4.x                         |
| 4.2.x           | 5.2.6 - 5.3.x                         |
| 4.0.x - 4.1.x   | 5.1.4 - 5.3.x                         |
| 3.8.x - 3.10.x  | 5.1.4 - 5.2.x                         |

<a name="dbms"></a>
## DBMS

| ILIAS Version   | MySQL Version                       | MariaDB Version         | Postgres (experimental)  |
|-----------------|-------------------------------------|-------------------------|--------------------------|
| 7.0 - 7.x       | 5.7.x, 8.0.x                        | 10.1, 10.2, 10.3        |                          |
| 6.0 - 6.x       | 5.6.x, 5.7.x, 8.0.x                 | 10.0, 10.1, 10.2, 10.3  | 9.x                      |
| 5.4.x - x.x.x   | 5.6.x, 5.7.x                        |                         |                          |
| 5.3.x - 5.4.x   | 5.5.x, 5.6.x, 5.7.x                 |                         |                          |
| 4.4.x - 5.2.x   | 5.0.x, 5.1.32 - 5.1.x, 5.5.x, 5.6.x |                         |                          |
| 4.2.x - 4.3.x   | 5.0.x, 5.1.32 - 5.1.x, 5.5.x        |                         |                          |
| 4.0.x - 4.1.x   | 5.0.x, 5.1.32 - 5.1.x               |                         |                          |
| 3.10.x          | 4.1.x, 5.0.x, 5.1.32 - 5.1.x        |                         |                          |
| 3.7.3 - 3.9.x   | 4.0.x - 5.0.x                       |                         |                          |

<a name="imagemagick"></a>
## ImageMagick

| ILIAS Version   | ImageMagick Version                   |
|-----------------|---------------------------------------|
| 4.2.x - 5.2.x   | 6.8.9-9 or higher                     |
| < 4.2.x         | No specific version requirements      |


<a name="connect-and-contribute"></a>
# Connect and Contribute

ILIAS is backed by a [huge community](http://www.ilias.de/docu/goto.php?target=cat_1444&client_id=docu).
We will be happy to welcome you  as a member of the [ILIAS Society](http://www.ilias.de/docu/goto.php?target=cat_1669&client_id=docu)
or at one of our regular [ILIAS Conferences](http://www.ilias.de/docu/goto.php?target=cat_2255&client_id=docu)
or [ILIAS Development Conferences](https://docu.ilias.de/goto_docu_grp_3721.html).

We are also looking for [contributions of code](../development/contributing.md),
[reports of issues](http://mantis.ilias.de) or [requests in our Feature Wiki](https://docu.ilias.de/goto.php?target=wiki_5307&client_id=docu#ilPageTocA119).
