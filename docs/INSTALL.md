# ILIAS Installation

ILIAS is a powerful Open Source Learning Management System for developing and realising web-based e-learning. The software was developed to reduce the costs of using new media in education and further training and to ensure the maximum level of customer influence in the implementation of the software. ILIAS is published under the General Public Licence and free of charge.

1. [System Requirements](#system-requirements)
   1. [CPU](#cpu)
   2. [Memory](#memory)
   3. [Harddrive](#harddrive)
   4. [Bandwidth](#bandwidth)
   5. [Recommended Setup for Running ILIAS](#recommended-setup-for-running-ilias)
   6. [Supported Platforms](#supported-platforms)
      1. [Server](#server)
      2. [Client](#client)
   7. [Database Recommendations](#database-recommendations)
2. [Manual Installation on Linux](#manual-installation-on-linux)
   1. [Git Clone/Checkout](#git-clonecheckout)
   2. [Dependency Installation](#dependency-installation)
      1. [Apache Installation/Configuration](#apache-installationconfiguration)
      2. [PHP Installation/Configuration](#php-installationconfiguration)
      3. [Database Installation/Configuration](#database-installationconfiguration)
         1. [MySQL Performance tuning](#mysql-perfomance-tuning)
   3. [E-Mail Configuration](#e-mail-configuration)
   4. [Install other Dependencies](#install-other-depedencies)
   5. [Installation Wizard](#installation-wizard)
   6. [Configure ILIAS Java RPC server](#configure-ilias-java-rpc-server)
3. [Hardening and Security Guidance](#hardening-and-security-guidance)
   1. [Secure Files](#secure-files)
      1. [Place data directory outside of the web root](#place-data-directory-outside-of-the-web-root)
      2. [Secure Installation Files](#secure-installation-files)
   2. [Use HTTPS](#use-https)
      1. [Redirect all unencrypted traffic to HTTPS](#redirect-all-unencrypted-traffic-to-https)
      2. [Enable HTTP Strict Transport Security (HSTS)](#enable-http-strict-transport-security)
      3. [Proper SSL configuration](#proper-ssl-configuration)
   3. [Servce security related Headers](#serve-security-related-headers)
4. [Customizing ILIAS](#customizing-ilias)
   1. [Plugin Repository](#plugin-repository)
5. [Upgrading ILIAS](#upgrading-ilias)
6. [Contribute](#contribute)
   1. [Pull Requests](#pull-requests)

# System Requirements

The necessary hardware to run an ILIAS installation is always dependent from the number of users and the kind of usage.

## CPU

We recommend a common dual core server CPU.

## Memory

Memory requirements are greatly variable, depending on the number of users and server activity. We recommend a minimum of 4096 MB.

## Harddrive

Usually 250 GB are sufficient. 25 GB would be used by the operating system and ILIAS itself. 225 GB would remain for the database and files.

## Bandwidth

We recommend at least 100 Mbit/sec. for the web server WAN connection.

## Recommended Setup for Running ILIAS

For best results we recommend:

  * Debian GNU Linux 8 / Red Hat Enterprise Linux 7 / Ubuntu 16.04 LTS
  * MySQL 5.5 / MariaDB
  * PHP 5.6+
  * Apache 2.4+ with mod_php
  * ImageMagick 6.x+
  * php5-gd, php5-xsl, php5-mysql
  * OpenJDK 7+
  * zip, unzip

## Supported Platforms

### Server

  * Server OS: Linux
  * Web Server: Apache 2 (mod_php, php-fpm), Nginx (php-fpm)
  * Databases: MySQL/MariaDB 5.0+ and Galera, Oracle 10g+, PostgreSQL
  * PHP: Version 5.5+ and 7.0+ are supported
  
  See http://www.ilias.de/docu/goto.php?target=lm_367&client_id=docu for details.
  
### Client

  * Desktop: Windows 7+, MacOS X 10.7+, Linux
  * Web Browser: IE11+, Microsoft Edge, Firefox 14+, Chrome 18+, Safari 7+

## Database Recommendations (MySQL/MariaDB)

  * InnoDB storage engine
  * utf8_general_ci
  * STRICT_TRANS_TABLES or STRICT_ALL_TABLES disabled (on MySQL 5.6.x)

# Manual Installation on Linux

You can download the latest ILIAS release at http://www.ilias.de/docu/goto.php?target=st_229 or clone it from GitHub at https://github.com/ILIAS-eLearning/ILIAS (for production make sure to checkout the latest stable release, not trunk).

We recommend to clone from GitHub as this will offer some kind of autoupdate for future releases and versions.

  * Install dependencies (see Recommended Setup for Running ILIAS)
  * Untar/Clone ILIAS into the web servers docroot (e.g. /var/www/html/)
  * Create directory outside the web servers docroot (e.g. /var/www/files/)
  * Change owner/group to www-data (on Debian) or apache (on RHEL) for the files and directories created above

## Git Clone/Checkout

To checkout the ILIAS release 5.2 in ```/var/www/html/ilias/``` use the following commands:

```
cd /var/www/html/
git clone https://github.com/ILIAS-eLearning/ILIAS.git ilias
git checkout release_5-2
chown www-data:www-data /var/www/html/ilias -R
```
The files should be owned by your webserver user/group (e.g. ```www-data``` or ```apache```) the mode should be 644 for files and 755 for directories.

# Dependency Installation

Depending on your Linux Distribution you have several ways to install the required dependencies. We recommend to always use your distributions package manager to keep your packages up to date in an easy manner avoiding security issues. 

## Apache Installation/Configuration

On Debian/Ubuntu execute: 
```
apt-get install apache2 
```

On RHEL/CentOS execute: 
```
yum install httpd
```

Usually Apache ships with a default configuration (e.g. ```/etc/apache2/sites-enabled/000-default.conf``` on Debian). A minimal configuration should look as follows:

```
<VirtualHost *:80>
    ServerAdmin webmaster@example.com

    DocumentRoot /var/www/html/ilias/
    <Directory /var/drbd/www/html/>
        Options FollowSymLinks
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

After changing the configuration remember to reload the web server daemon:

On Debian/Ubuntu: 
```
systemctl restart apache2.service
```

On RHEL/CentOS: 
```
systemctl restart httpd.service
```

## PHP Installation/Configuration

On Debian/Ubuntu execute: 
```
apt-get install libapache2-mod-php5
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

We recommend the following settings for your php.ini:

```
; you may choose higher values for max_execution_time and memory_limit
max_execution_time = 600
memory_limit = 512M
 
error_reporting = E_ALL & ~E_NOTICE ; up to PHP 5.2.x
error_reporting = E_ALL & ~E_NOTICE & ~E_DEPRECATED ; PHP 5.3.0 and higher
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
 
; for chat server since ILIAS 4.2
allow_url_fopen = 1

; How many GET/POST/COOKIE input variables may be accepted
max_input_vars = 10000
```

Remember to reload your web server configuration to apply those changes.

## Database Installation/Configuration

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
GRANT LOCK TABLES on *.* TO 'ilias@localhost';
GRANT ALL PRIVILEGES ON ilias.* TO 'ilias'@'localhost';
FLUSH PRIVILEGES;
```

### MySQL Perfomance tuning

We recommend to use https://github.com/major/MySQLTuner-perl to optimize your MySQL configuration (e.g. ```/etc/mysql/my.cnf```). Execute ```mysqltuner.pl``` after several days of using ILIAS in production.

## E-Mail Configuration

We recommend to use a already existing smarthost (mailhub) to send E-Mails generated by ILIAS. A very simple way to do so is using ```ssmtp```:

On Debian/Ubuntu execute: 
```
apt-get install ssmtp
```

On RHEL/CentOS execute: 
```
yum install ssmtp
```

The configuration file for SSMTP (e.g. ```/etc/ssmtp/ssmtp.conf ```) should look as follows:

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

## Install other Depedencies

On Debian/Ubuntu execute: 
```
apt-get install zip unzip php5-gd php5-mysql php5-xslt imagemagick openjdk-7-jdk
```

On RHEL/CentOS execute: 
```
yum install zip unzip php-gd libxslt ImageMagick java-1.7.0-openjdk
```

# Installation Wizard

After having all dependencies installed an configured you should be able to run the ILIAS Installation Wizard using http://yourservername.org/setup/setup.php

# Configure ILIAS Java RPC server

The ILIAS Java RPC server is used for certain functions as Lucene Search or generating PDF Certificates. To enable the RPC server you need to place a configuration file in ```<YOUR_ILIAS_DIR>/Services/WebServices/RPC/lib/ilServer.properties```:

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

ILIAS can generate a proper configuration file via the Administration menu ("Administration &rarr; General Settings &rarr; Server &rarr; Java-Server &rarr; Create Configuration File"). Please note that the configuration file is not directly written to the file system, you have to copy the displayed content and create the file manually.

You can use the following SysV-Initscript to start the RPC server:

```
#!/bin/bash

JAVABIN=/usr/bin/java    # Type in the path to your java binary
ILIASDIR=/var/www/html/ilias    # Type in the root directory of your ILIAS installation
IL_SERVER_INI=/var/www/html/ilias/Services/WebServices/RPC/lib/ilServer.properties    # Type in the location of the RPC config file

case "$1" in
    start)
        echo "Starting ILIAS Java-Server"
        $JAVABIN -Dfile.encoding=UTF-8 -jar $ILIASDIR/Services/WebServices/RPC/lib/ilServer.jar $IL_SERVER_INI start &
        exit 0
        ;;

    stop)
        echo "Shutting down ILIAS Java-Server"
        $JAVABIN -jar $ILIASDIR/Services/WebServices/RPC/lib/ilServer.jar $IL_SERVER_INI stop
        exit 0
        ;;

    status)
        $JAVABIN -jar $ILIASDIR/Services/WebServices/RPC/lib/ilServer.jar $IL_SERVER_INI status
        ;;      

    restart)
        $0 stop
        sleep 2
        $0 start
        ;;

    *)
        echo "Usage: $0 {start|stop|status|restart}"
        exit 1
esac

exit 0
```

# Hardening and Security Guidance

## Secure Files

In previous versions of ILIAS it might have been possible to access SCORM, Media Files and User Profile Images without beeing logged in by guessing the proper URL and no measures were taken by the admin to deny such access.

Since ILIAS 5.1 a new WebAccessChecker (WAC) is implemented by default. To make use of WAC you need to enable ```mod_rewrite``` in you Apache configuration.

Please note that this will not work with Nginx as ```.htaccess```-files are not supported. Instead you can add the following to your Nginx configuration file:

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

### Place data directory outside of the web root

It is highly recommended to place your data directory outside of the web server docroot, as recommended by the ILIAS Installation Wizard.

### Secure Installation Files

The access to the ILIAS Installation Wizard (```/setup/setup.php```) should be restricted:

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

## Use HTTPS

You can get a trusted, free SSL certificate at https://letsencrypt.org

### Redirect all unencrypted traffic to HTTPS

To redirect all HTTP traffic to HTTPS you should issue a permanent redirect using the 301 status code:

```
<VirtualHost *:80>
   ServerName yourservername.org
   Redirect permanent / https://yourservername.org/
</VirtualHost>
```

### Enable HTTP Strict Transport Security

By adding the following to your Apache SSL VirtualHost configuration you instruct browsers not to allow any connection to your ILIAS instance using HTTP and prevent visitors from bypassing invalid certificate warnings.

```
<IfModule mod_headers.c>
    Header always set Strict-Transport-Security "max-age=15768000; includeSubDomains; preload"
</IfModule>
```

**Warning:** Before activating the configuration above make sure that you are absolutely sure that you have a good workflow for maintaining your SSL settings (including certificate renewals) as you will not be able to disable HTTPS access to your site for up to 6 months.

### Proper SSL configuration

The default SSL ciphers used by web servers are often not state-of-the-art, so you should consider to choose your own settings. Which settings should be used depends completely on your environment. Therefore giving a generic recommendation is not really possible.

We recommend to use the [Mozilla SSL Configuration Generator](https://mozilla.github.io/server-side-tls/ssl-config-generator/) to generate a suitable configuration and the [Qualys SSL Labs Tests](https://www.ssllabs.com/ssltest/) or the [High-Tech Bridge SSL Server Test](https://www.htbridge.com/ssl/) to check your settings.

## Serve security related Headers

To improve the security of your ILIAS users you should set at least the following Headers:

  * X-Content-Type-Options: nosniff
  * X-XSS-Protection: 1; mode=block
  * X-Frame-Options: SAMEORIGIN

For Apache on Debian systems you can turn those headers on by editing ```/etc/apache2/conf-enabled/security.conf```, make sure ```mod_headers``` and ```mod_env``` are enabled.

For Nginx you can simply add for example ```add_header X-Frame-Options "SAMEORIGIN";``` in your ```server``` configuration.

# Customizing ILIAS

If you need to customize your ILIAS installation avoid under all circumstances to edit the core files, otherwise you will not be able to update your installation in a timely manner (e.g. due to security fixes). 

You can find proper ways to customize ILIAS in the [ILIAS Development Guide](http://www.ilias.de/docu/goto_docu_pg_29964_42.html):

  * [Plugins and Plugin Slots - ILIAS Development Guide](http://www.ilias.de/docu/goto.php?target=st_27029)
  * [Styles and Templates - ILIAS Development Guide](http://www.ilias.de/docu/goto_docu_pg_210_42.html)
  
## Plugin Repository

ILIAS can be extended with a lot of Plugins. You find the complete list in the [Plugin Repository](http://www.ilias.de/docu/goto.php?target=cat_1442&client_id=docu)

# Upgrading ILIAS

The easiest way to update ILIAS is using Git. Before you start you should consider to:

  * Backup your database
  * Backup your docroot
  * Change your skin to delos (default skin)
  
Then go to your ILIAS directory (e.g. ```cd /var/www/html/ilias/```) and excute: ```git pull```

Afterwards open the ILIAS Installation Wizard in your browser (e.g. http://yourservername.org/setup/setup.php) and check if your database needs updates or hotfixes.

In case of merge conflicts refer to [Resolving Conflicts - ILIAS Development Guide](http://www.ilias.de/docu/goto.php?target=pg_15604).

# Contribute

We have a big [community](http://www.ilias.de/docu/goto.php?target=cat_1444&client_id=docu) and you can get a member of [ILIAS Society](http://www.ilias.de/docu/goto.php?target=cat_1669&client_id=docu).
You may even join us at one of our regular [ILIAS Conferences](http://www.ilias.de/docu/goto.php?target=cat_2255&client_id=docu).

## Pull Requests

We highly appreciate Pull-Request from external developers. Due to some regulations in the developments process of ILIAS, some kinds of Pull-Request need further steps. Additionally Pull-Request should target the correct branch for easy merging.

- Language-Fixes or additions to language-files don't need further steps.
- Bugfixes need an entry in the Bugtracker: http://mantis.ilias.de . Pull-Request for Bugfixes target always to the branch where the bug occurs. The developer which merges it will cherry-pick the fix to all branches needed
- Features/Refactorings need an entry in Feature-Wiki and has to get through the existing procedure for Feature-Requests: http://feature.ilias.de . Pull-Request target to trunk.

Pull-Request will be assigned to the responsible maintainer(s). See further information on how contributions are handled in [/docs/CONTRIBUTING.md](/docs/CONTRIBUTING.md)
