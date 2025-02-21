# ILIAS Installation

This is the installation guide for ILIAS 10, providing step-by-step instructions to set up all necessary components, 
including the web server, database, and ILIAS code. Follow these instructions carefully to ensure a successful 
installation of the e-learning software. Each section will guide you through the required configurations and setups 
for a fully functional ILIAS environment.

# Table of Contents

<!-- toc -->

- [System Requirements](#system-requirements)
  * [Hardware](#hardware)
  * [Supported Software Setup and Reference System](#supported-software-setup-and-reference-system)
- [Installation on Ubuntu 24.04](#installation-on-ubuntu-2404)
  * [Install Dependencies](#install-dependencies)
  * [Webserver Installation/Configuration](#webserver-installationconfiguration)
  * [Database Installation/Configuration](#database-installationconfiguration)
  * [Get the Code and Install ILIAS](#get-the-code-and-install-ilias)
  * [Install ILIAS](#install-ilias)
  * [Install Further Components](#install-further-components)
  * [Install Plugins and Styles](#install-plugins-and-styles)
- [Backup ILIAS](#backup-ilias)
- [Upgrading ILIAS](#upgrading-ilias)
  * [Minor Upgrade](#minor-upgrade)
  * [Major Upgrade](#major-upgrade)
  * [Update the Database](#update-the-database)
  * [Information on Updates](#information-on-updates)
- [Connect and Contribute](#connect-and-contribute)
- [Appendix](#appendix)
  * [Upgrading Dependencies](#upgrading-dependencies)
  * [Configure Cron Jobs](#configure-cron-jobs)
  * [Configure WebDAV](#configure-webdav)
  * [Hardening and Security Guidance](#hardening-and-security-guidance)
  * [MySQL Strict Mode (5.7+)](#mysql-strict-mode-57)

<!-- tocstop -->

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

<a name="supported-system"></a>
## Supported Software Setup and Reference System

The following software versions are required/supported for ILIAS 10. The table below lists these versions alongside the 
current configuration of the [ILIAS test server](https://test10.ilias.de), which serves as a reference system.

| Package      | Version                                                | Reference System |
|--------------|--------------------------------------------------------|------------------|
| Distribution | current version of Debian GNU Linux, Ubuntu or RHEL    | Ubuntu 22.04 LTS |
| Database     | MySQL >8.0.21 or MariaDB 10.5 - 10.11                  | MariaDB 10.6.18  |
| PHP          | 8.2, 8.3                                               | 8.3              |
| Webserver    | nginx: 1.12.x – 1.18.x, Apache: ≥ 2.4.x                | Apache 2.4.52    |
| JDK          | Open JDK Runtime 11, 17 or 21 LTS                      | OpenJDK 17       |
| Node.js      | 20 (LTS), 21, 22 (LTS), 23 Recommended: 22             | 16.20            |
| Ghostscript  | 10.x                                                   | 9.55             |
| Imagemagick  | 6.9.x                                                  | 6.9.11           |
| MathJax      | MathJax 3, MathJax 2 with safe mode                    | 2.7.9            |
| Browser      | a contemporary browser supporting ES6, CSS3 and HTML 5 |                  |

Package names may vary depending on the Linux distribution.

> [!NOTE]
> Please note that other platforms and configurations may be possible, but it
> might be difficult to find someone who can help in case of issues. We strongly recommend **not**
> using a different configuration unless you are an experienced system administrator.

<a name="installation-on-linux"></a>
# Installation on Ubuntu 24.04

Depending on your Linux Distribution, you have several ways to install the required
dependencies. We recommend to always use your distributions package manager to
easily keep your packages up to date avoiding security issues.

In this guide we choose Ubuntu 24.04 because it already meets the recommended PHP version 8.3.
For other Ubuntu or Debian-based Linux systems, we recommend using [DEB.SURY.ORG](https://deb.sury.org/) to install the correct 
PHP version later on.

<a name="install-dependencies"></a>
## Install Dependencies

`openjdk-17-jdk` and `maven` are optional and are used for the ILIAS RPC server for search indexing and certificate generation. 
`git` is required if the source code is obtained directly from GitHub.
`nodejs` and `npm` are required as well if you get the source directly to download the javascript dependencies in the installation process.
Alternatively, they can be obtained directly from the distribution package at [Nodesource](https://deb.nodesource.com/) to select appropriate nodejs versions according to the [Recommended Setup for Running ILIAS](#recommended-setup-for-running-ilias).
`ffmpeg` is optionally used to optimise media files, and `ghostscript` is optionally used to create file previews.

```shell
apt update
apt update zip unzip openjdk-17-jdk maven ffmpeg git ghostscript nodejs npm
```

<a name="install-webserver"></a>
## Webserver Installation/Configuration

In this guide, we use Apache2 as the web server, utilizing `libapache2-mod-php` to process PHP files. 
Other web servers capable of processing PHP, such as Nginx or Apache2 with FCGI and PHP-FPM, can also be used.

**Required PHP Extensions:**
```
gd, dom, xsl, pdo, pdo_mysql, curl, json, simplexml, libxml, xml, zip, imagick, mbstring
```

**Optional PHP Extensions:**

* `xmlrpc` for the ILIAS RPC server
* `soap` for SOAP user administration 
* `ldap` for LDAP user authentication


The PHP packet manager Composer is necessary to download all external PHP dependencies and for PHP autoloading. 
Alternatively, it can be obtained directly from [getcomposer.org](https://getcomposer.org/download/). 
Composer may be optional when using the prepacked ILIAS from [Download & Releases](https://docu.ilias.de/go/pg/197851_35), but it is necessary when using plugins to rebuild the PHP autoload classmap.

```shell
apt install apache2 libapache2-mod-php php php-gd php-xsl php-imagick php-curl php-mysql php-xmlrpc php-soap php-ldap composer
```

Create a directory for the html sources (e.g. `/var/www/ilias`) which is referenced in the apache2 vhost and also a directory outside the web servers docroot (e.g. `/var/www/files`) for files stored by ILIAS. 
Make sure that the web server is the owner of the files and directories that were created by changing the group and owner to www-data (on Debian/Ubuntu) or apache (on RHEL).

In addition to the file folder, ILIAS also needs a place to create the log files
(e.g. `/var/www/logs`). The 'ilias.log' can be viewed there later, as well as all
error_log files, which are created in case of errors and are referenced in ILIAS by
an errorcode.

Also, to store the ILIAS configuration, which is later used to configurate ILIAS, we create the folder /var/www/config. To prevent future issues with npm we create /var/www/.npm with webowner rights.

```shell
mkdir /var/www/ilias
mkdir /var/www/files
mkdir /var/www/logs
mkdir /var/www/config
mkdir /var/www/.npm
chown www-data:www-data /var/www/ilias
chown www-data:www-data /var/www/files
chown www-data:www-data /var/www/logs
chown www-data:www-data /var/www/.npm
```

Usually Apache ships with a default configuration (e.g. `/etc/apache2/sites-enabled/000-default.conf` on Debian based
systems). A minimal configuration for ILIAS may look as follows:

```apacheconf
<VirtualHost *:80>
    ServerAdmin webmaster@example.com

    DocumentRoot /var/www/ilias/public/
    <Directory /var/www/ilias/>
        Options +FollowSymLinks -Indexes
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

In order to secure access to the files in your `data` directory, you should enable `mod_rewrite` on Debian/Ubuntu (should be enabled by default on RHEL/CentOS):

```shell
a2enmod rewrite
systemctl restart apache2.service
```

To check if the installation was successfull create the file `/var/www/ilias/phpinfo.php` with the following contents:

```
<?php
phpinfo();
```

Then point your browser to ```http://example.com/phpinfo.php```. 
If you see the content of the file as shown above, your configuration is **not** working. 
If you can see details of your PHP configuration, everything works fine. 
Search for the entry ```Loaded configuration file``` as we now made some changes to it (e.g. `/etc/php5/apache2/php.ini`).
Delete the file `phpinfo.php` afterwards.

We recommend at least the following settings for your php.ini:

```ini
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
session.save_handler = files
; If you installation is served via HTTPS also use:
session.cookie_secure = On

; for chat server
allow_url_fopen = 1

; How many GET/POST/COOKIE input variables may be accepted
max_input_vars = 10000
```

Restart the apache webserver after you installed and configured your dependencies.

```shell
systemctl restart apache2.service
```

Please see [Hardening and Security Guidance](#hardening-and-security-guidance) for further security relevant configuration.

Remember to reload your web server configuration to apply those changes.

Please ensure that PHP is compiled with `libargon2`. 
This is mostly the case for common distributions, but if you compile PHP yourself, it must be build with `--with-password-argon2[=DIR]` (see: https://www.php.net/manual/en/password.installation.php).

<a name="install-database"></a>
## Database Installation/Configuration

On Debian/Ubuntu execute:
```shell
apt install mariadb-server
```

> [!NOTE]
> Please note that installing ILIAS in utf8mb4-collations is currently not supported!
> ILIAS supports utf8-collations with 3 bytes per character, such as `utf8_general_ci`, only.

We **strongly recommend** to use MariaDB with the following settings:

* InnoDB storage engine (default)
* `character-set-server` = `utf8mb3`
* `collation-server` = `utf8mb3_general_ci`
* `join_buffer_size` > `128K`
* `table_open_cache` > `400`
* `innodb_buffer_pool_size` > `2G` (depending on DB size)

On MySQL 5.7+ and Galera the `Strict SQL Mode` must be disabled. See [MySQL Strict Mode](#mysql-strict-mode-57) for details.

On MySQL/MariaDB `innodb_large_prefix` must be set to `OFF` if the `ROW_FORMAT`
is set to `COMPACT`.

```shell
systemctl restart mariadb.service
```

We recommend to create a dedicated database user for ILIAS:

```shell
mysql -e "CREATE DATABASE ilias CHARACTER SET utf8 COLLATE utf8_general_ci;"
mysql -e "CREATE USER 'ilias'@'localhost' IDENTIFIED BY '<db-password>';"
mysql -e "GRANT LOCK TABLES on *.* TO 'ilias'@'localhost';"
mysql -e "GRANT ALL PRIVILEGES ON ilias.* TO 'ilias'@'localhost';"
mysql -e "FLUSH PRIVILEGES;"
```

<a name="get-code"></a>
## Get the Code and Install ILIAS

You can [download](https://docu.ilias.de/go/pg/197851_35) the latest
ILIAS release or clone it from [GitHub](https://github.com/ILIAS-eLearning/ILIAS).
For production use make sure to checkout the latest stable release, not the trunk,
which is the development branch of the repository.

We recommend to clone from GitHub and use git to update the code, since this simplifies
the update to future releases and versions.

Clone the code to the web servers docroot (e.g. `/var/www/html`) with the following
commands:

```shell
cd /var/www/ilias/
sudo -uwww-data git clone https://github.com/ILIAS-eLearning/ILIAS.git . --single-branch -b release_10
```
<details>
<summary>If you use tags to directly reference ILIAS versions</summary>

```shell
cd /var/www/ilias/
sudo -uwww-data git clone https://github.com/ILIAS-eLearning/ILIAS.git . --single-branch -b v10.X
```
</details>

<details>
<summary>If you use the tar.gz provided in the release</summary>

Download the file from the [Download & Releases](https://docu.ilias.de/go/lm/35) page.

```shell
sudo -uwww-data wget https://github.com/ILIAS-eLearning/ILIAS/releases/download/v10.X/ILIAS-10.X.tar.gz
sudo -uwww-data tar -xzf ILIAS-10.X.tar.gz  -C /var/www/ilias --strip-components=1 ILIAS-10.X/
```
</details>

The GitHub repository of ILIAS doesn't contain all code that is required to run. To download the required PHP-dependencies and
to create static artifacts from the source, run the following in your ILIAS folder:
```shell
sudo -uwww-data npm clean-install --omit=dev --ignore-scripts
sudo -uwww-data composer install --no-dev
```

> [!IMPORTANT]
> We recommend restricting the rights of the ILIAS code in the production system so that the web server only has 
> read access to the code. For this and other important security considerations, please refer to the security
> instructions in the [Security Guide](./secure.md).

<a name="install-ilias"></a>
## Install ILIAS

After having all dependencies installed and configured you should be able to run
the [ILIAS Setup on the command-line](../../setup/README.md).

To do so, create a configuration file for the setup by copying the [minimal-config.json](../../setup/minimal-config.json)
to a location outside your docroot. Fill in the configuration fields that are already
contained in the minimal config. Have a look into the [list of available config options](../../setup/README.md#about-the-config-file)
and add the fields that your environment and installation requires.

```shell
cp /var/www/ilias/components/ILIAS/setup_/minimal-config.json /var/www/config/ilias.json
```

A typical configuration might look like this afterwards:

```json
{
        "common" : {
                "client_id" : "myilias"
        },
        "database" : {
                "user" : "ilias",
                "password": "<db-passowrd>>",
                "database": "ilias",
                "create_database": true
        },
        "filesystem" : {
                "data_dir" : "/var/www/files/ilias"
        },
        "logging" : {
                "enable" : true,
                "path_to_logfile" : "/var/www/logs/ilias.log",
                "errorlog_dir" : "/var/www/logs/"
    	},
        "http" : {
                "path" : "http://www.example.com"
        },
        "systemfolder" : {
                "contact" : {
                        "firstname" : "Richard",
                        "lastname" : "Klees",
                        "email" : "richard.klees@concepts-and-training.de"
                }
        },
        "utilities" : {
        	"path_to_convert" : "/usr/bin/convert"
    	},
    	"mediaobject" : {
		"path_to_ffmpeg" : "/usr/bin/ffmpeg"
	},
	"preview" : {
		"path_to_ghostscript" : "/usr/bin/gs"
	},
}
```

Run the ILIAS command line setup from within your ILIAS folder with your configuration
file (located outside your doc-root!) as a parameter:

```shell
sudo -uwww-data php cli/setup.php install /var/www/config/ilias.json
```

The installation will display what currently happens and might prompt you with
questions. You might want to have a look into the [documentation of the command line setup](../../setup/README.md)
or into the help of the program itself `php cli/setup.php help`. It is the tool
to manage and monitor your ILIAS installation.

If you are installing from Git, it is possible that ILIAS will already require a few migrations to the initial
database. Run the setup migration and follow the steps shown. This is also necessary whenever you update your code.

```shell
php cli/setup.php migrate
```

Now that you have ILIAS installed, you can start by logging in as root. Go to your http path and log in with the 
username `root` and password `homer`. ILIAS will ask you for a new password after the first login.

<a name="install-further"></a>
## Install Further Components

Optionally you can continue with the installation of further components to get the full functionality of ILIAS:

1. **ILIAS Cron Job**
A cron job can be automatically executed to perform recurring tasks, such as sending notifications or deleting inactive user accounts. 
For details on how to configure the automatic execution of cron jobs, see [Configure Cron Jobs](#configure-cron-jobs).
2. **ILIAS Java RPC server**
It is used for certain optional functions such as Lucene Search
or generating PDF Certificates. See [Lucene RPC-Server](../../components/ILIAS/WebServices/RPC/lib/README.md) for details
on how to install the RPC server.
3. **ILIAS Chat Server** 
It is used to provide an interactive chat experience between users.
See [Chat Server](../../components/ILIAS/Chatroom/README.md) for details on how to install the chat server.
4. **E-Mail**
You either use a MTA of your liking to send e-mail generated by ILIAS or configure a SMPT Connection in ILIAS 
"Administration > Communication > Settings > Extern" by activating and configuring "Send via SMTP". We recommend
to use a MTA installed to your OS like `postfix`. On Debian/Ubuntu execute and configure it according to their instructions:
```shell
apt-get install postfix
```

<a name="install-plugins-and-styles"></a>
## Install Plugins and Styles

Plugins are the way to add new functionality to your ILIAS installation. Do not
change the core files, or you will not be able to update your installation easily.
A variety of free plugins is provided from our community via the [ILIAS Plugin Repository](https://docu.ilias.de/go/cat/1442).
To develop plugins, you can get started in our [Development Guide](https://docu.ilias.de/go/pg/27030_42).

Custom styles are the way to modify the look of your ILIAS installation. Have
a look in the [documentation of the System Styles and Custom Styles](../../templates/Readme.md)
to learn how to build and install them.

<a name="backup-ilias"></a>
# Backup ILIAS

There are three places where the ILIAS core system stores data that needs to be backed up in order to restore your 
system in case of failure.
* Internal data within your webroot `<ILIAS-root-folder>/public/data` in our case `/var/www/ilias/public/data`.
* External data, configured in `ilias.json` within `filesystem.data_dir`, in our case `/var/www/files/ilias`.
* The database, which can easily be done by running `mysqldump'.
```shell
mysqldump --lock-tables=false -u<database user> -p <database name> > /path/to/your/backup/folder/ilias-backup.sql
# Prompt for password
```

When restoring the ILIAS files to the designated folder, remember to set the correct permissions and ownership of your 
web server. To restore the database, drop the old database with `DROP DATABASE <database-name>;`, create an empty 
database according to [Database Installation/Configuration](#install-database) and write the database dump to this database:
```shell
mysql -u<database-user> -p <database-name> < /path/to/your/backup/folder/ilias-backup.sql
# Prompt for password
```

<a name="upgrading-ilias"></a>
# Upgrading ILIAS

The easiest way to update ILIAS is using Git. Please note that this is only possible
if you installed ILIAS via Git as advised in this document. If Git wasn't used you
can also [download](https://docu.ilias.de/go/lm/35) new releases.

Before you start you should consider to [backup](#backup-ilias).

<a name="minor-upgrade"></a>
## Minor Upgrade

To apply a minor update (e.g. v10.1 to v10.2) execute the following command in
your ILIAS basepath (e.g. `/var/www/ilias/`):

```shell
sudo -uwww-data git pull origin release_10
sudo -uwww-data npm clean-install --omit-dev --ignore-scripts
sudo -uwww-data composer install --no-dev
```

<details>
<summary>If you use tags to directly reference ILIAS versions</summary>

```shell
sudo -uwww-data git fetch origin v10.X:v10.X
sudo -uwww-data git checkout v10.X
sudo -uwww-data npm clean-install --omit-dev --ignore-scripts
sudo -uwww-data composer install --no-dev
```
</details>

<details>
<summary>If you use the tar.gz provided in the release</summary>

Download the archive of the newest ILIAS minor version on the [Download & Releases](https://docu.ilias.de/go/lm/35) page.

```shell
sudo -uwww-data wget https://github.com/ILIAS-eLearning/ILIAS/releases/download/v10.X/ILIAS-10.X.tar.gz
sudo -uwww-data mkdir /tmp/ilias_update
sudo -uwww-data tar -xzf ILIAS-10.X.tar.gz  -C /tmp/ilias_update --strip-components=1 ILIAS-10.X/
sudo -uwww-data rsync -av --exclude='/public/' --exclude='/Customizing/' --exclude='/data/' --exclude='ilias.ini.php' /tmp/ilias_update/ /var/www/ilias/
rm -rf /tmp/ilias_update
```
If you have plugins installed, you will need to rebuild the classmap, including all plugins. You will need to call 
`composer du` to do this.

```shell
sudo -uwww-data composer du
```
</details>

In case of merge conflicts, refer to [the ILIAS Development Guide](http://www.ilias.de/docu/goto.php?target=pg_15604).
You should only encounter these if you changed the code of your installation
locally.

Then complete the update by [updating the database](#update-the-database).

<a name="major-upgrade"></a>
## Major Upgrade

To apply a major upgrade (e.g. v9.13 to v10.1) please check that your OS has the
[proper dependency versions](#upgrading-dependencies) installed. If everything
is fine, change your default skin to Delos and apply this change at least to
your root user. Otherwise ILIAS might become unusable due to changes in the
layout templates. Then execute the following commands in your ILIAS basepath
(e.g. `/var/www/ilias`).

```shell
sudo -uwww-data git fetch origin release_10:release_10
sudo -uwww-data git checkout release_10
```
<details>
<summary>If you use tags to directly reference ILIAS versions</summary>

```shell
sudo -uwww-data git fetch origin v10.X:v10.X
sudo -uwww-data git checkout v10.X
```
</details>

<details>
<summary>If you use the tar.gz provided in the release</summary>

Download the archive of the newest ILIAS minor version from the [Download & Releases](https://docu.ilias.de/go/lm/35) page.

```shell
sudo -uwww-data wget https://github.com/ILIAS-eLearning/ILIAS/releases/download/v10.X/ILIAS-10.X.tar.gz
sudo -uwww-data mkdir /tmp/ilias_update
sudo -uwww-data tar -xzf ILIAS-10.X.tar.gz  -C /tmp/ilias_update --strip-components=1 ILIAS-10.X/
sudo -uwww-data rsync -av --exclude='/public/' --exclude='/Customizing/' --exclude='/data/' --exclude='ilias.ini.php' /tmp/ilias_update/ /var/www/ilias/
rm -rf /tmp/ilias_update
```
</details>
After upgrading the code from ILIAS 9 to ILIAS 10 due to structural changes, you need to move the `/Customizing/global/plugins`
and `/data` folder to its new destination. Both are now located in the newly created `public` folder.

```shell
sudo -uwww-data mkdir -p public/Customizing/plugins
mv data public/
mv Customizing/global/plugins/Services/* public/Customizing/plugins/
mv Customizing/global/plugins/Modules/* public/Customizing/plugins/
```

Then update the code of your plugins according to their documentation to ensure they are compatible with the new ILIAS version.
If you are **not** using the tar.gz archive to upgrade your release, update your javascript and php dependencies. If you
are using a tar.gz archive and are using plugins, reload your php classmap with `composer du`.

```shell
sudo -uwww-data npm clean-install --omit-dev --ignore-scripts
sudo -uwww-data composer install --no-dev
```

Complete the update of the base system by [updating the database](#update-the-database).

As a last step, you should log in with a User using your custom skin. If everything
works fine, change back from Delos to your custom system style. If not, you probably
will need to update your style to match the new release.

<a name="update-the-database"></a>
## Update the Database

Database updates must be done for both minor and major updates, the schema and content
of the database probably won't match the code otherwise. Database updates are performed
via the [command line setup program](../../setup/README.md). The required updates
are split into two groups. **Updates** are tasks that need to be run immediately to
make your installation work properly. **Migrations** are tasks, that potentially take
some time, but which can also be executed while the installation is in productive use.

Run the `status` command on the command line to check if there are any updates
available and if ILIAS is responding. After this you need to perform the update.

```
php cli/setup.php update
```

To check if there are migrations, run in your ILIAS folder.

```
php cli/setup.php migrate
```

The command will show you if there are migrations that need to be run for you
installation. Run them by using the `--run` parameter and have a look into
the help of the command for more details: `php cli/setup.php migrate --help`.

Both commands will display what currently happens and might prompt you with
questions. You might want to have a look into the [documentation of the command line setup](../../setup/README.md)
or into the help of the program itself `php cli/setup.php help`. It is the tool
to manage and monitor your ILIAS installation.

Database updates are performed in steps; it might happen that a step fails, e.g. due
to some edge case or inconsistency in existing data, files, etc.
In this case, a concecutive command `php setup/setup.php update` will error with
a message like
> step 2 was started last, but step 1 was finished last.
> Aborting because of that mismatch.

You may reset the records for those steps by running:
```shell
php cli/setup.php achieve database.resetFailedSteps
```
However, be sure to understand the cause for the failing steps and tend to it before
resetting and running update again.

<a name="information-updates"></a>
## Information on Updates

To keep your ILIAS Installation secure and healthy it is important that you keep
it up to date. To get informations about updates and security fixes you should
consider subscribing to the [ILIAS Admin Mailing-List](http://lists.ilias.de/cgi-bin/mailman/listinfo/ilias-admins). Information on the 
new versions, such as Important Changes, Known Issues, Changed Behaviour and Fixed 
Issues, can be found in the release notes in [Download & Releases](https://docu.ilias.de/go/lm/35).

<a name="connect-and-contribute"></a>
# Connect and Contribute

ILIAS is backed by a [huge community](http://www.ilias.de/docu/goto.php?target=cat_1444&client_id=docu).
We will be happy to welcome you  as a member of the [ILIAS Society](http://www.ilias.de/docu/goto.php?target=cat_1669&client_id=docu)
or at one of our regular [ILIAS Conferences](http://www.ilias.de/docu/goto.php?target=cat_2255&client_id=docu)
or [ILIAS Development Conferences](https://docu.ilias.de/goto_docu_grp_3721.html).

We are also looking for [contributions of code](../development/contributing.md),
[reports of issues](http://mantis.ilias.de) or [requests in our Feature Wiki](https://docu.ilias.de/goto.php?target=wiki_5307&client_id=docu#ilPageTocA119).

If you have any questions about the installation or the community, please visit us on our [Discord server](https://discord.gg/H9v2v2Ar2T) and join 
the discussion!

<a name="appendix"></a>
# Appendix

<a name="upgrading-dependencies"></a>
## Upgrading Dependencies

When you upgrade from rather old versions please make sure that the dependencies,
like MySQL and PHP, are up to date. Below you will find the supported versions for
each ILIAS release.

**PHP:**

| ILIAS Version  | PHP Version                 |
|----------------|-----------------------------|
| 10.x           | 8.2.x, 8.3.x                |
| 9.x            | 8.1.x, 8.2.x                |
| 8.x            | 7.4.x, 8.0.x                |
| 7.x            | 7.3.x, 7.4.x                |
| 6.x            | 7.2.x, 7.3.x, 7.4.x         |

**DBMS:**

We strongly recommend using MariaDB instead of MySQL due to performance, licensing and compatibility in the future.

| ILIAS Version | MySQL Version       | MariaDB Version        |
|---------------|---------------------|------------------------|
| 10.0  - 10.x  | 8.0.x               | 10.4, 10.5, 10.6       |
| 9.0 - 9.x     | 8.0.x               | 10.3, 10.4, 10.5, 10.6 |
| 8.0 - 8.x     | 5.7.x, 8.0.x        | 10.2, 10.3, 10.4       |
| 7.0 - 7.x     | 5.7.x, 8.0.x        | 10.1, 10.2, 10.3       |
| 6.0 - 6.x     | 5.6.x, 5.7.x, 8.0.x | 10.0, 10.1, 10.2, 10.3 |

<a name="configurate-cron"></a>
## Configure Cron Jobs

This step configures the execution of the ILIAS Cron Jobs, which can be set to perform tasks, such as sending notifications.
You can manage these jobs in the ILIAS Administration under "Administration > General Settings > Cron Jobs".

To test the execution of the Cron Jobs Executable `./cli/cron.php`, the following command can be used:

```shell
php /var/www/ilias/cli/cron.php run-jobs <user> <client_id> run-jobs
```

The `<user>` is a valid, arbitrary user account within the ILIAS installation.
The `<client_id>` corresponds to the client ID of the ILIAS installation.

To configure automated Cron Jobs in your system, you need to create an user in ILIAS, for example named `cron`.
Then create a new file in the Linux Cron configuration for ILIAS at `/etc/cron.d/ilias`, 
including a line to execute `./cli/cron.php` every 5 minutes. 
Other methods for executing Linux cron tasks, such as using the user crontab, can also be utilized.

```cron
*/5 * * * * www-data /usr/bin/php /var/www/ilias/cli/cron.php run-jobs cron myilias run-jobs > /dev/null 2>&1
```

You can verify the proper automatic execution in the ILIAS Administration section by checking the timestamp 
displayed at `Last Start of the Cron Job` after some time.


<a name="webdav-configuration"></a>
## Configure WebDAV

The recommended webserver configuration is either **Apache with mod_php** or
**Nginx with PHP-FPM (> 1.3.8)**. Do NOT use **Apache with PHP-FPM** if you
want to use WebDAV. Find more information about the configuration of WebDAV
in the [WebDAV Readme](../../components/ILIAS/WebDAV/README.md).

<a name="hardening-and-security-guidance"></a>
## Hardening and Security Guidance

We recommend to perform a threat analysis for your ILIAS installation, as every
prudent admin should do for his resources. In our [Security Guide](./secure.md)
we show techniques and strategies to be used to secure your ILIAS installation
according to your needs.

<a name="mysql-strict-mode-57"></a>
## MySQL Strict Mode (5.7+)

With MySQL 5.7+ you might see SQL errors like:

```
SQLSTATE[42000]: Syntax error or access violation: 1055 Expression #1 of
SELECT list is not in GROUP BY clause and contains nonaggregated column
'yourdbname.tblname.foobar' which is not functionally dependent on
columns in GROUP BY clause; this is incompatible with sql_mode=only_full_group_by
```

As a workaround `STRICT_TRANS_TABLES`, `STRICT_ALL_TABLES` and `ONLY_FULL_GROUP_BY`
must be disabled. To do so, create the file `/etc/mysql/conf.d/disable_strict_mode.cnf`
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
