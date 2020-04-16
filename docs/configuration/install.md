# ILIAS Installation

ILIAS is a powerful Open Source Learning Management System for developing and realising web-based e-learning. The software was developed to reduce the costs of using new media in education and further training and to ensure the maximum level of customer influence in the implementation of the software. ILIAS is published by ILIAS open source e-Learning e.V. under the General Public Licence and free of charge.

**Please note:** The key words "MUST", "MUST NOT", "REQUIRED", "SHALL", "SHALL NOT", "SHOULD", "SHOULD NOT", "RECOMMENDED",  "MAY", and  "OPTIONAL" in this document are to be interpreted as described in [RFC 2119](https://www.ietf.org/rfc/rfc2119.txt).

# Table of Contents

<!-- MarkdownTOC depth=0 autolink="true" bracket="round" autoanchor="true" style="ordered" indent="   " -->

1. [System Requirements](#system-requirements)
   1. [CPU](#cpu)
   1. [Memory](#memory)
   1. [Harddrive](#harddrive)
   1. [Bandwidth](#bandwidth)
   1. [Recommended Setup for Running ILIAS](#recommended-setup-for-running-ilias)
   1. [Supported Platforms](#supported-platforms)
      1. [Server](#server)
      1. [Client](#client)
   1. [Database Recommendations](#database-recommendations)
1. [Manual Installation on Linux](#manual-installation-on-linux)
   1. [Git Clone/Checkout](#git-clonecheckout)
1. [Dependency Installation](#dependency-installation)
   1. [Apache Installation/Configuration](#apache-installationconfiguration)
   1. [PHP Installation/Configuration](#php-installationconfiguration)
   1. [Database Installation/Configuration](#database-installationconfiguration)
      1. [MySQL Strict Mode \(5.6+\)](#mysql-strict-mode-56)
      1. [MySQL Perfomance tuning \(OPTIONAL\)](#mysql-perfomance-tuning-optional)
   1. [E-Mail Configuration \(OPTIONAL\)](#e-mail-configuration-optional)
   1. [WebDAV Configuration \(OPTIONAL\)](#webdav-configuration-optional)
   1. [Install other Depedencies](#install-other-depedencies)
      1. [Optional Dependencies](#optional-dependencies)
   1. [Installation Wizard](#installation-wizard)
   1. [Configure ILIAS Java RPC server \(OPTIONAL\)](#configure-ilias-java-rpc-server-optional)
1. [Hardening and Security Guidance](#hardening-and-security-guidance)
   1. [Secure Files](#secure-files)
      1. [File Access Rights](#file-access-rights)
      1. [Place data directory outside of the web root](#place-data-directory-outside-of-the-web-root)
      1. [Secure Installation Files](#secure-installation-files)
   1. [Use HTTPS](#use-https)
      1. [Redirect all unencrypted traffic to HTTPS](#redirect-all-unencrypted-traffic-to-https)
      1. [Enable HTTP Strict Transport Security](#enable-http-strict-transport-security)
      1. [Proper SSL configuration](#proper-ssl-configuration)
   1. [Serve security related Headers](#serve-security-related-headers)
   1. [Report security issues](#report-security-issues)
1. [Customizing ILIAS](#customizing-ilias)
   1. [Plugin Repository](#plugin-repository)
1. [Upgrading ILIAS](#upgrading-ilias)
   1. [Minor Upgrade](#minor-upgrade)
   1. [Major Upgrade](#major-upgrade)
   1. [Database Update](#database-update)
   1. [Information on Updates](#information-on-updates)
1. [Upgrading Dependencies](#upgrading-dependencies)
   1. [PHP](#php)
   1. [MySQL](#mysql)
   1. [ImageMagick](#imagemagick)
1. [Contribute](#contribute)
   1. [Pull Requests](#pull-requests)
   1. [Reference System](#reference-system)

<!-- /MarkdownTOC -->

<a name="system-requirements"></a>
# System Requirements

The necessary hardware to run an ILIAS installation is always dependent from the number of users and the kind of usage.

<a name="cpu"></a>
## CPU

We RECOMMEND a common dual core server CPU.

<a name="memory"></a>
## Memory

Memory requirements are greatly variable, depending on the number of users and server activity. We RECOMMEND a minimum of 4096 MB.

<a name="harddrive"></a>
## Harddrive

We RECOMMEND 250 GB for usual ILIAS deployments. 25 GB would be used by the operating system and ILIAS itself. 225 GB would remain for the database and files.

<a name="bandwidth"></a>
## Bandwidth

We RECOMMEND at least 100 Mbit/sec. for the web server WAN connection.

<a name="recommended-setup-for-running-ilias"></a>
## Recommended Setup for Running ILIAS

For best results we RECOMMEND:

  * Debian GNU Linux 9 / Red Hat Enterprise Linux 7 / Ubuntu 16.04 LTS
  * MySQL 5.6+
  * MariaDB 10.2
  * PHP 7.0
  * Apache 2.4.18 with mod_php
  * ImageMagick 6.8+
  * php-gd, php-xml, php-mysql, php-mbstring
  * OpenJDK 7+
  * zip, unzip
  * git

Package names may vary depending on the Linux distribution.

<a name="supported-platforms"></a>
## Supported Platforms

Please note that different configurations SHOULD be possible, but it might be harder to find someone who can help when things go south. You SHALL NOT use a different configuration unless you are an experienced system administrator.

<a name="server"></a>
### Server

  * Server OS: Linux
  * Web Server: Apache 2.4 (mod_php, php-fpm)
  * Databases: MySQL/MariaDB 5.6 and 5.7 and Galera (experimental), PostgreSQL 9.x
  * PHP: Version 7.0, 7.1, 7.2 and 7.3 are supported
  * zip: 3.0+
  * unzip: 6.0+
  * Imagemagick: 6.8.9-9+
  * PhantomJS: 2.0.0+
  * NodeJS: 8.9.4 (TLS) - 9.7.1
  * Java: Version 7 and 8 are suported
  
<a name="client"></a>
### Client

  * Desktop: Windows 7+, MacOS X 10.7+, Linux
  * Web Browser: IE11+, Microsoft Edge, Firefox 14+, Chrome 18+, Safari 7+

<a name="database-recommendations"></a>
## Database Recommendations

> Please note that installing ILIAS in utf8mb4-collations is currently not supported! ILIAS supports utf8mb3 only. 

We RECOMMEND to use MySQL/MariaDB with the following settings:

  * InnoDB storage engine
  * utf8_general_ci
  * query_cache_size (> 16M)
  * join_buffer_size (> 128.0K, or always use indexes with joins)
  * table_open_cache (> 400)
  * innodb_buffer_pool_size (>= 2G, depending on DB size)

On MySQL 5.6+ and Galera the ```Strict SQL Mode``` MUST be disabled. See [MySQL Strict Mode](#mysql-strict-mode-56) for details.

On MySQL/MariaDB `innodb_large_prefix` must be set to `OFF` if the `ROW_FORMAT`
is set to `COMPACT`.

<a name="manual-installation-on-linux"></a>
# Manual Installation on Linux

You can download the latest ILIAS release at http://www.ilias.de/docu/goto.php?target=st_229 or clone it from GitHub at https://github.com/ILIAS-eLearning/ILIAS (for production make sure to checkout the latest stable release, not trunk).

We RECOMMEND to clone from GitHub as this will offer some kind of autoupdate for future releases and versions.

  * Install dependencies (see [Recommended Setup for Running ILIAS](#recommended-setup-for-running-ilias))
  * Untar/Clone ILIAS into the web servers docroot (e.g. /var/www/html/)
  * Create directory outside the web servers docroot (e.g. /var/www/files/)
  * Change owner/group to www-data (on Debian) or apache (on RHEL) for the files and directories created above

<a name="git-clonecheckout"></a>
## Git Clone/Checkout

To checkout the ILIAS release 5.4 in ```/var/www/html/ilias/``` use the following commands:

```
cd /var/www/html/
git clone https://github.com/ILIAS-eLearning/ILIAS.git ilias
cd ilias
git checkout release_5-4
chown www-data:www-data /var/www/html/ilias -R
```
The files SHOULD be owned by your webserver user/group (e.g. ```www-data``` or ```apache```) the mode SHOULD be 644 for files and 755 for directories. 

For more details on file access rights see [File Access Rights](#file-access-rights) in the Security section of this document.

<a name="dependency-installation"></a>
# Dependency Installation

Depending on your Linux Distribution you have several ways to install the required dependencies. We RECOMMEND to always use your distributions package manager to keep your packages up to date in an easy manner avoiding security issues. 

<a name="apache-installationconfiguration"></a>
## Apache Installation/Configuration

On Debian/Ubuntu execute: 
```
apt-get install apache2 
```

On RHEL/CentOS execute: 
```
yum install httpd
```

Usually Apache ships with a default configuration (e.g. ```/etc/apache2/sites-enabled/000-default.conf``` on Debian). A minimal configuration MAY look as follows:

```
<VirtualHost *:80>
    ServerAdmin webmaster@example.com

    DocumentRoot /var/www/html/ilias/
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

In order to secure access to the files in your `data` directory, you SHOULD
enable `mod\_rewrite` on Debian/Ubuntu (should be enabled by default on
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

<a name="php-installationconfiguration"></a>
## PHP Installation/Configuration

On Debian/Ubuntu 14.04 or 16.04 execute:
```
apt-get install libapache2-mod-php7.0 php7.0-gd php7.0-mysql php7.0-mbstring php-xml
```

On RHEL/CentOS execute: 
```
yum install php
systemctl restart httpd.service
```

To check if the installation was successfull create the file ```/var/www/html/ilias/phpinfo.php``` with the following contents:

```
<?php
phpinfo();
?>
```

Then point your browser to ```http://yourservername.org/phpinfo.php```. If you see the content of the file as shown above your configuration is **not** working. If you can see details of your PHP Configuration everything works fine. Search for the entry ```Loaded configuration file``` as we now made some changes to it (e.g. ```/etc/php5/apache2/php.ini```).

We RECOMMEND the following settings for your php.ini:

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

Please see [Hardening and Security Guidance](#hardening-and-security-guidance)
for [HTTPS configuration](#enable-http-strict-transport-security) and further
security relevant configuration.

Remember to reload your web server configuration to apply those changes.

<a name="database-installationconfiguration"></a>
## Database Installation/Configuration

On Debian/Ubuntu execute: 
```
apt-get install mysql-server
```

On RHEL/CentOS execute: 
```
yum install mariadb
```

We RECOMMEND to create a dedicated database user for ILIAS:

```
mysql -u root -p
CREATE DATABASE ilias CHARACTER SET utf8 COLLATE utf8_general_ci;
CREATE USER 'ilias'@'localhost' IDENTIFIED BY 'password';
GRANT LOCK TABLES on *.* TO 'ilias@localhost';
GRANT ALL PRIVILEGES ON ilias.* TO 'ilias'@'localhost';
FLUSH PRIVILEGES;
```

<a name="mysql-strict-mode-56"></a>
### MySQL Strict Mode (5.6+)

With MySQL 5.6+ and Galera you might see SQL errors like:

```
SQLSTATE[42000]: Syntax error or access violation: 1055 Expression #1 of
SELECT list is not in GROUP BY clause and contains nonaggregated column
'yourdbname.tblname.foobar' which is not functionally dependent on
columns in GROUP BY clause; this is incompatible with sql_mode=only_full_group_by
```

As a workaround ```STRICT_TRANS_TABLES```, ```STRICT_ALL_TABLES``` and ```ONLY_FULL_GROUP_BY``` MUST be disabled. To do so create the file ```/etc/mysql/conf.d/disable_strict_mode.cnf``` and enter the following (or add it to ```/etc/mysql/my.cnf```):

```
[mysqld]
sql_mode=IGNORE_SPACE,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION
```

After restarting the MySQL-Server use the following command to confirm the changes:

```
mysql -i -BN -e 'SELECT @@sql_mode' | grep -E 'ONLY_FULL_GROUP_BY|STRICT_TRANS_TABLES|STRICT_ALL_TABLES'
```

If strict mode is disabled, there will be no output.

<a name="mysql-perfomance-tuning-optional"></a>
### MySQL Perfomance tuning (OPTIONAL)

We RECOMMEND to use https://github.com/major/MySQLTuner-perl to optimize your MySQL configuration (e.g. ```/etc/mysql/my.cnf```). Execute ```mysqltuner.pl``` after several days of using ILIAS in production.

<a name="e-mail-configuration-optional"></a>
## E-Mail Configuration (OPTIONAL)

You MAY use whatever MTA you like to send E-Mail generated by ILIAS. We RECOMMEND to use an already existing smarthost (mailhub). A very simple way to do so is using ```ssmtp```:

On Debian/Ubuntu execute: 
```
apt-get install ssmtp
```

On RHEL/CentOS execute: 
```
yum install ssmtp
```

The configuration file for SSMTP (e.g. ```/etc/ssmtp/ssmtp.conf ```) MAY look as follows:

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

<a name="webdav-configuration-optional"></a>
## WebDAV Configuration (OPTIONAL)

Because of a special behaviour in the Windows Explorer, it sometimes fails to add a WebDAV connection with the error code "0x80070043 The Network Name Cannot Be Found".

To prevent this behaviour, add the following rewrite rules to a .htaccess file in your webroot or to the corresponding section of the configuration of your webserver:
```
RewriteCond %{HTTP_USER_AGENT} ^(DavClnt)$
RewriteCond %{REQUEST_METHOD} ^(OPTIONS)$
RewriteRule .* "-" [R=401,L]
```

<a name="install-other-depedencies"></a>
## Install other Depedencies

On Debian/Ubuntu 14.04 execute:
```
apt-get install zip unzip imagemagick openjdk-7-jdk
```

On Ubuntu 16.04 execute:
```
apt-get install zip unzip imagemagick openjdk-8-jdk
```

On RHEL/CentOS execute: 
```
yum install zip unzip libxslt ImageMagick java-1.8.0-openjdk
```
### Optional Dependencies

Depending on your use case, you MAY want to install further dependencies (exact package names vary by distribution):

* php-curl
* php-xmlrpc
* php-soap
* php-ldap
* ffmpeg
* mimetex
* phantomjs

Please ensure that the phantomjs version you use is at least 2.0.0. Please note that phantomjs development has been suspended until further notice. See https://github.com/ariya/phantomjs/issues/15344 for details.

<a name="installation-wizard"></a>
## Installation Wizard

After having all dependencies installed and configured you should be able to run the ILIAS Installation Wizard using http://yourservername.org/setup/setup.php

Make sure to reload your Apache configuration before entering the Wizard. Otherwise there are unmet dependencies in the setup (like XLS and GD are both installed but ILIAS does not see them, yet).

<a name="configure-ilias-java-rpc-server-optional"></a>
## Configure ILIAS Java RPC server (OPTIONAL)

The ILIAS Java RPC server is used for certain OPTIONAL functions as Lucene Search or generating PDF Certificates. To enable the RPC server you need to place a configuration file in ```<YOUR_ILIAS_DIR>/Services/WebServices/RPC/lib/ilServer.properties```:

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

ILIAS can generate a proper configuration file via the Administration menu ("Administration -> General Settings -> Server -> Java-Server -> Create Configuration File"). Please note that the configuration file is not directly written to the file system, you MUST copy the displayed content and create the file manually.

You MAY use the following systemd service description to start the RPC server. If you still use SysV-Initscripts you can find one in the [Lucene RPC-Server](../../Services/WebServices/RPC/lib/README.md) documentation.

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

At this point the RPC server will generate PDF certificates, but to use Lucence search further step are needed. See [Lucene RPC-Server](../../Services/WebServices/RPC/lib/README.md) for details.

<a name="hardening-and-security-guidance"></a>
# Hardening and Security Guidance

<a name="secure-files"></a>
## Secure Files

In previous versions of ILIAS it might have been possible to access SCORM, Media Files and User Profile Images without beeing logged in by guessing the proper URL and no measures were taken by the admin to deny such access.

Since ILIAS 5.1 a new WebAccessChecker (WAC) is implemented by default. To make use of WAC you MUST enable ```mod_rewrite``` in your Apache configuration.

Please note that this will not work with Nginx as ```.htaccess```-files are not supported. Instead you MAY add the following to your Nginx configuration file (please note that running ILIAS with Nginx isn't officially supported and certain features like Shibboleth won't work):

```
server {
    [...]
    root /var/www/trunk;
    set $root $document_root;
    rewrite ^/data/(.*)/(.*)/(.*)$ /Services/WebAccessChecker/wac.php last;
    location /secured-data {
        alias $root/data;
        internal;
    }
    [...]
}
```

<a name="file-access-rights"></a>
### File Access Rights

If you're an experienced admin you MAY want to use more strict file access rights that we RECOMMENDED earlier in this document. To make it impossible for an attacker to modify PHP files if he gains control over the web server processes those files SHOULD be owned by ```root``` wherever possible.

The only files and directories that must be owned/writeable by the web user are:

  * ilias.ini.php
  * data/
  * ILIAS data dir outside of the webservers docroot

All the other files and directories should be owned by ```root```, but readable by the web user (e.g. 644/755).

<a name="place-data-directory-outside-of-the-web-root"></a>
### Place data directory outside of the web root

It is highly RECOMMENDED to place your data directory outside of the web server docroot, as pointed out by the ILIAS Installation Wizard.

<a name="secure-installation-files"></a>
### Secure Installation Files

The access to the ILIAS Installation Wizard (```/setup/setup.php```) MAY be restricted:

```
<Location /setup>
  <IfVersion < 2.3>
    Order Deny,Allow
    Deny From All
    Allow from 127.0.0.1
  </IfVersion>

  <IfVersion > 2.3>
    Require all denied
    Require ip 127.0.0.1
  </IfVersion>
</Location>
```

<a name="use-https"></a>
## Use HTTPS

You can get a trusted, free SSL certificate at https://letsencrypt.org

<a name="redirect-all-unencrypted-traffic-to-https"></a>
### Redirect all unencrypted traffic to HTTPS

To redirect all HTTP traffic to HTTPS you MAY issue a permanent redirect using the 301 status code:

```
<VirtualHost *:80>
   ServerName yourservername.org
   Redirect permanent / https://yourservername.org/
</VirtualHost>
```

<a name="enable-http-strict-transport-security"></a>
### Enable HTTP Strict Transport Security

By adding the following to your Apache SSL VirtualHost configuration you instruct browsers not to allow any connection to your ILIAS instance using HTTP and prevent visitors from bypassing invalid certificate warnings.

```
<IfModule mod_headers.c>
    Header always set Strict-Transport-Security "max-age=15768000; includeSubDomains; preload"
</IfModule>
```

**Warning:** Before activating the configuration above you MUST make sure that you have a good workflow for maintaining your SSL settings (including certificate renewals) as you will not be able to disable HTTPS access to your site for up to 6 months.

<a name="proper-ssl-configuration"></a>
### Proper SSL configuration

The default SSL ciphers used by web servers are often not state-of-the-art, so you SHOULD consider to choose your own settings. Which settings should be used depends completely on your environment. Therefore giving a generic recommendation is not really possible.

We RECOMMEND to use the [Mozilla SSL Configuration Generator](https://mozilla.github.io/server-side-tls/ssl-config-generator/) to generate a suitable configuration and the [Qualys SSL Labs Tests](https://www.ssllabs.com/ssltest/) or the [High-Tech Bridge SSL Server Test](https://www.htbridge.com/ssl/) to check your settings.

<a name="serve-security-related-headers"></a>
## Serve security related Headers

To improve the security of your ILIAS users you SHOULD set the following Headers:

  * X-Content-Type-Options: nosniff
  * X-XSS-Protection: 1; mode=block
  * X-Frame-Options: SAMEORIGIN

For Apache on Debian systems you can turn those headers on by editing ```/etc/apache2/conf-enabled/security.conf```. You MUST enable  ```mod_headers``` and ```mod_env``` for this.

For Nginx you can simply add for example ```add_header X-Frame-Options "SAMEORIGIN";``` in your ```server``` configuration.

<a name="report-security-issues"></a>
## Report security issues

If you think you found an security related issue in ILIAS please refer to http://www.ilias.de/docu/goto_docu_wiki_5307.html#ilPageTocA213 for reporting it.

<a name="customizing-ilias"></a>
# Customizing ILIAS

If you need to customize your ILIAS installation you MUST NOT edit the core files, otherwise you will not be able to update your installation in a timely manner (e.g. due to security fixes). 

You can find proper ways to customize ILIAS in the [ILIAS Development Guide](http://www.ilias.de/docu/goto_docu_pg_29964_42.html):

  * [Plugins and Plugin Slots - ILIAS Development Guide](http://www.ilias.de/docu/goto.php?target=st_27029)
  * [Custom Styles](/templates/Readme.md#custom-styles)
  
<a name="plugin-repository"></a>
## Plugin Repository

ILIAS can be extended with a lot of Plugins. You find the complete list in the [Plugin Repository](http://www.ilias.de/docu/goto.php?target=cat_1442&client_id=docu)

<a name="upgrading-ilias"></a>
# Upgrading ILIAS

The easiest way to update ILIAS is using Git, please note that this is only possible if you installed ILIAS via git as advised in this document. If Git wasn't used you can always download the ZIP or Tarball in the [release section of the ILIAS GitHub pages](https://github.com/ILIAS-eLearning/ILIAS/releases). 

Before you start you SHOULD consider to:

  * Backup your database
  * Backup your docroot
  * Change your skin to delos (default skin)

We also RECOMMEND to use a decent test instance to make a pre-flight check, to see if all plugins, skins, etc. still working as expected with the new version.

<a name="minor-upgrade"></a>
## Minor Upgrade

To apply a minor update (e.g. v5.2.0 to v5.2.1) execute the following command in your ILIAS basepath (e.g. ```/var/www/html/ilias/```):

```
git pull
```

In case of merge conflicts refer to [Resolving Conflicts - ILIAS Development Guide](http://www.ilias.de/docu/goto.php?target=pg_15604).

See [Database Update](#database-update) for details on how to complete the Upgrade by updating your database.

<a name="major-upgrade"></a>
## Major Upgrade

To apply a major update (e.g. v5.1.0 to 5.2.0 or v4.x.x to 5.x.x) please check that your OS has the [proper dependency versions](#upgrading-dependencies) installed. If everything is fine change your default skin to Delos and apply this at least to your root user, otherwise ILIAS might become unusable due to changes in the layout templates. Then execute the following commands in your ILIAS basepath (e.g. ```/var/www/html/ilias/```):

```
git fetch
git checkout release_5-2
```

Replace ```release_5-2``` with the branch or tag you actually want to upgrade to. You can get a list of available branches by executing ```git branch -a``` and a list of all available tags by executing ```git tag```. Never use ```trunk``` or ```*beta``` for production.

In case of merge conflicts refer to [Resolving Conflicts - ILIAS Development Guide](http://www.ilias.de/docu/goto.php?target=pg_15604).
  
See [Database Update](#database-update) for details on how to complete the Upgrade by updating your database.

As a last step you should log in with a User using your custom skin. If everything works fine change back from Delos to your skin. If not refer to [Customizing ILIAS](#customizing-ilias) to modify your skin to match the new requirements.

<a name="database-update"></a>
## Database Update

A Database Updates MUST be done for both minor and major updates. Open the ILIAS Installation Wizard (e.g. http://yourservername.org/setup/setup.php) to check and apply the needed updates and/or hotfixes.

The update process usually will be splitted into several runs to avoid timeouts. Each update step can take quite some time without huge load peaks on your PHP/Database processes. To check which update step gets currently executed run the following SQL-Statement on your ILIAS database: ```SELECT * FROM `settings` WHERE keyword = "db_update_running"```

<a name="information-on-updates"></a>
## Information on Updates

To keep your ILIAS Installation secure and healthy it is important that you keep it up to date. To get informations about updates and security fixes you SHOULD consider to subscribe to the ILIAS Admin Mailing-List: http://lists.ilias.de/cgi-bin/mailman/listinfo/ilias-admins

<a name="upgrading-dependencies"></a>
# Upgrading Dependencies

When you upgrade from rather old versions please make sure that the dependencies, like MySQL and PHP, are up to date. Below you will find the supported versions for each ILIAS release.

<a name="php"></a>
## PHP

| ILIAS Version   | PHP Version                           |
|-----------------|---------------------------------------|
| 5.4.x           | 7.0.x, 7.1.x, 7.2.x, 7.3.x            |
| 5.3.x           | 5.6.x, 7.0.x, 7.1.x                   |
| 5.2.x           | 5.5.x - 5.6.x, 7.0.x, 7.1.x           |
| 5.0.x - 5.1.x   | 5.3.x - 5.5.x                         |
| 4.4.x           | 5.3.x - 5.5.x                         |
| 4.3.x           | 5.2.6 - 5.4.x                         |
| 4.2.x           | 5.2.6 - 5.3.x                         |
| 4.0.x - 4.1.x   | 5.1.4 - 5.3.x                         |
| 3.8.x - 3.10.x  | 5.1.4 - 5.2.x                         |

<a name="mysql"></a>
## MySQL

| ILIAS Version   | MySQL Version                         |
|-----------------|---------------------------------------|
| 5.4.x - x.x.x   | 5.6.x, 5.7.x                          |
| 5.3.x - 5.4.x   | 5.5.x, 5.6.x, 5.7.x                   |
| 4.4.x - 5.2.x   | 5.0.x, 5.1.32 - 5.1.x, 5.5.x, 5.6.x   |
| 4.2.x - 4.3.x   | 5.0.x, 5.1.32 - 5.1.x, 5.5.x          |
| 4.0.x - 4.1.x   | 5.0.x, 5.1.32 - 5.1.x                 |
| 3.10.x          | 4.1.x, 5.0.x, 5.1.32 - 5.1.x          |
| 3.7.3 - 3.9.x   | 4.0.x - 5.0.x                         |

<a name="imagemagick"></a>
## ImageMagick

| ILIAS Version   | ImageMagick Version                   |
|-----------------|---------------------------------------|
| 4.2.x - 5.2.x   | 6.8.9-9 or higher                     |
| < 4.2.x         | No specific version requirements      |

<a name="contribute"></a>
# Contribute

We have a big [community](http://www.ilias.de/docu/goto.php?target=cat_1444&client_id=docu) and you can get a member of [ILIAS Society](http://www.ilias.de/docu/goto.php?target=cat_1669&client_id=docu).
You may even join us at one of our regular [ILIAS Conferences](http://www.ilias.de/docu/goto.php?target=cat_2255&client_id=docu).

<a name="pull-requests"></a>
## Pull Requests

We highly appreciate Pull-Request from external developers. Due to some regulations in the developments process of ILIAS, some kinds of Pull-Request need further steps. Additionally Pull-Request SHOULD target the correct branch for easy merging.

   - Language-Fixes or additions to language-files don't need further steps.
   - Bugfixes need an entry in the [ILIAS-Bugtracker](http://mantis.ilias.de). Pull-Request for Bugfixes target always to the branch where the bug occurs. The developer which merges it will cherry-pick the fix to all branches needed
   - Features/Refactorings need an entry in [Feature-Wiki](http://feature.ilias.de) and has to get through the existing procedure for Feature-Requests. Pull-Request target to trunk.

Pull-Request will be assigned to the responsible maintainer(s). See further information on how contributions are handled in [/docs/CONTRIBUTING.md](/docs/CONTRIBUTING.md)

<a name="reference-system"></a>
## Reference System

The ILIAS Testserver (https://test54.ilias.de) is currently configured as follows:

| Package        | Version                     |
|----------------|-----------------------------|
| Distribution   | Ubuntu 16.04.1 LTS          |
| MySQL          | MySQL 5.5.58                |
| MariaDB        | 10.1                        |
| PHP            | 7.0.33                      |
| Apache         | 2.4.7                       |
| Nginx          | 1.4.6                       |
| zip            | 3.0                         |
| unzip          | 6.00                        |
| JDK            | 1.7.0_121 (IcedTea 2.6.8)   |
| NodeJS         | 8.9.4 LTS                   |

Please note: Shibboleth won't work with Nginx.
