# Table of Contents

- [Hardening and Security Guidance](#hardening-and-security-guidance)
  * [List of placeholder names](#list-of-placeholder-names)
  * [Firewall](#firewall)
  * [File Access Rights](#file-access-rights)
  * [Place data directory outside of the web root](#place-data-directory-outside-of-the-web-root)
  * [Isolate Docroot](#isolate-docroot)
  * [OS user handling security](#os-user-handling-security)
  * [Major security improvement: use HTTPS](#major-security-improvement-use-https)
    + [Redirect all unencrypted traffic to HTTPS](#redirect-all-unencrypted-traffic-to-https)
    + [Proper SSL configuration](#proper-ssl-configuration)
    + [Serve security related Headers](#serve-security-related-headers)
    + [Enable OCSP stapling (TLS Certificate Status Request)](#enable-ocsp-stapling-tls-certificate-status-request)
    + [Enable HTTP Strict Transport Security](#enable-http-strict-transport-security)
    + [Genrate the ILIAS `ilClientId` cookie with `secure` attribute](#generate-the-ilias-ilclientid-cookie-with-secure-attribute)
  * [Suppress server signature and PHP version information](#suppress-server-signature-and-php-version-information)
  * [deny access or restrict to several files or locations](#deny-access-or-restrict-to-several-files-or-locations)
    + [ILIAS setup](#ilias-setup)
    + [Prevent blacklisted files of beeing served by the webserver](#prevent-blacklisted-files-of-beeing-served-by-the-webserver)
    + [Prevent execution of PHP-Code in data-directory](#prevent-execution-of-php-code-in-data-directory)
    + [Deny Access to local Git-Directory](#deny-access-to-local-git-directory)
  * [Integrity check of ILIAS code in docroot](#integrity-check-of-ilias-code-in-docroot)
  * [Use WebAccessChecker](#use-webaccesschecker)
  * [Use secure passwords](#use-secure-passwords)
  * [Report security issues](#report-security-issues)

# Hardening and Security Guidance

This guideline is going to show you some best practice examples how to improve the security of your ILIAS installation, of the webserver and it's associated components.
The ILIAS e.V. requested a documentation for a "Secure ILIAS" in march 2019.
The section "Hardening and Security Guidance" should be removed and the security related instructions have to be mantained in a own document.

## List of placeholder names

In the following text includes some placeholder.
For a better identification, they will describe here:

| placeholder | description |
|-------|-------|
| %USERNAME% | an individual user name or the webserver user based on distribution |
| %GROUP% | a individual group name or the webserver group based on distribution |
| %HOSTNAME% | your specific fully qualified domain name of ILIAS |
| %IPADDRESS% | ip address in CIDR notation |
| %DOCROOT% | directory that forms the main document tree visible from the web |
| %EXTERNALDATA% | ILIAS data directory outside of the web document root |
| %LOGDIR% | path to the directory containing log files |
| %CLIENTID% | the client name of the ILIAS installation  |

## Firewall

Block all traffic by default and explicitly allow only specific traffic to port 443/TCP for HTTPS secured traffic.
For "quality of life", it is recommend to also permit 80/TCP for a [redirect to HTTPS](#redirect-all-unencrypted-traffic-to-https).

## File Access Rights

If you're an experienced admin you MAY want to use more restrictive file access rights that we RECOMMEND in this document. To make it impossible for an attacker to modify PHP files if he gains control over the web server processes, those files SHOULD be owned by `root` wherever possible.

The only files and directories that MUST be owned/writeable by the web user are:

  * ilias.ini.php
  * data/
  * ILIAS data dir outside of the webservers docroot

All the other files and directories should be owned by a specific user (%USERNAME% and %GROUP%, has to be created), but readable by the web user (e.g. 644/755).

example of the suggested configuration (may you can choose other directories, based on your discretion / distribution standard):

```
git clone --single-branch -b release_5-4 https://github.com/ILIAS-eLearning/ILIAS.git %DOCROOT%/.

mkdir -p %DOCROOT%
mkdir %EXTERNALDATA%
mkdir %LOGDIR%
mkdir %LOGDIR%/errors

chown %USERNAME%:%GROUP% %DOCROOT% %DOCROOT%/data
chown %USERNAME%:%GROUP% %EXTERNALDATA%
chown %USERNAME%:%GROUP% %LOGDIR% %LOGDIR%/errors

chmod 2775 %DOCROOT%
chmod 2775 %EXTERNALDATA%
chmod 2775 %LOGDIR%
chmod 2775 %LOGDIR%/errors
```

After the installation of ILIAS is finished, you SHOULD also revoke write permission for the file `ilias.ini.php` (e.g. `%DOCROOT%/ilias.ini.php`).
note: for changing base setting via ILIAS setup, you need to grant write permission for the file `ilias.ini.php` again.

## Place data directory outside of the web root

It is highly RECOMMENDED to place your data directory outside of the web server docroot, as pointed out by the ILIAS Installation Wizard.

## Isolate Docroot

You MAY use openbasedir-restriction to avoid malicious software to directory-traverse out of your docroot-directory. This is very important if there are other websites on the same host.

Apache2:

```
php_admin_value open_basedir ./:%DOCROOT%:%EXTERNALDATA%:%LOGDIR%:/usr/share/php/:/var/www/lib/:/tmp
```

Nginx:

```
    fastcgi_param PHP_VALUE open_basedir="./:%DOCROOT%:%EXTERNALDATA%:%LOGDIR%:/usr/share/php/:/var/www/lib/:/tmp";
```

**Hint:** This option will be applied to the PHP-FPM process. If there are multiple websites on your webserver you have to define a single PHP-FPM-pool for each website. Otherwise these other homepages won' t be accessible anymore.

## OS user handling security

If you use PHP-FPM (FastCGI Process Manager), you can increase security by running the PHP-FPM processes as a specific unique user instead of `www-data` / `wwwrun` (depends on linux distribution).

Snippet from a PHP-FPM pool definition:

```
; Unix user/group of processes
; Note: The user is mandatory. If the group is not set, the default user's group
;       will be used.
user = %USERNAME%
group = %GROUP%
```

**note:**
NGINX and also apache2 can only run with one user (no multi user multi process model).
So it is necessary to put all the "PHP-FPM"-users in the primary group of the webserver user.

## Major security improvement: use HTTPS

You can get a trusted, free SSL certificate at <https://letsencrypt.org>.
Or use a SSL certificate from a commercial certificate authority.

### Redirect all unencrypted traffic to HTTPS

To redirect all HTTP traffic to HTTPS you SHOULD issue a permanent redirect using the 301 status code:

#### Apache2

```
<VirtualHost *:80>
    ServerName %HOSTNAME%
    Redirect permanent / https://%HOSTNAME%/
</VirtualHost>
```

#### NGINX

```
server {
    listen   *:80;
    listen   [::]:80;
    server_name %HOSTNAME%;

    location / {
        return 301 https://%HOSTNAME%$request_uri;
    }
```

### Proper SSL configuration

The default SSL settings (e.g. ciphers & ssl protocol version ) used by web servers are often not state-of-the-art, so you SHOULD consider to choose your own settings.

**Suggestion of modern settings for SSL configuration:**

The following suggestion can only be a recommendation and depends completely on your specific environment (webserver software and version, used OpenSSL version, etc.).

If you use, the following suggestion, please note that the oldest compatible clients will be:

* Firefox 27
* Chrome 30
* IE 11 on Windows 7
* Edge
* Opera 17
* Safari 9
* Android 5.0
* Java 8

Older browser clients will not be able to reach the ILIAS installation!

#### Apache2

*(Version: 2.4.34,  OpenSSL 1.1.1c)*

Add the following line INSIDE the `<VirtualHost></VirtualHost>` block:

```
<VirtualHost *:443>
    ...
    ServerName %HOSTNAME%
    ...
    SSLEngine on
    SSLCertificateFile       /path/to/signed_cert_plus_intermediates
    SSLCertificateKeyFile   /path/to/private/key
```

Add the following line OUTSIDE  the `<VirtualHost></VirtualHost>` block:

```
SSLProtocol             all -SSLv3 -TLSv1 -TLSv1.1
SSLCipherSuite          ECDHE-ECDSA-AES256-GCM-SHA384:ECDHE-RSA-AES256-GCM-SHA384:ECDHE-ECDSA-CHACHA20-POLY1305:ECDHE-RSA-CHACHA20-POLY1305:ECDHE-ECDSA-AES128-GCM-SHA256:ECDHE-RSA-AES128-GCM-SHA256:ECDHE-ECDSA-AES256-SHA384:ECDHE-RSA-AES256-SHA384:ECDHE-ECDSA-AES128-SHA256:ECDHE-RSA-AES128-SHA256
SSLHonorCipherOrder     on
SSLCompression          off
SSLSessionTickets       off
```

#### NGINX

*(Version: 1.14.0,  OpenSSL 1.1.1c)*

Add the following line INSIDE the server block:

```
server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    ...
    server_name %HOSTNAME%;
    ...
    # certs sent to the client in SERVER HELLO are concatenated in ssl_certificate
    ssl_certificate /path/to/signed_cert_plus_intermediates;
    ssl_certificate_key /path/to/private_key;
    ssl_session_timeout 5m;
    ssl_session_cache shared:SSL:16m;
    ssl_session_tickets off;

    ssl_protocols TLSv1.2;
    ssl_ciphers 'ECDHE-ECDSA-AES256-GCM-SHA384:ECDHE-RSA-AES256-GCM-SHA384:ECDHE-ECDSA-CHACHA20-POLY1305:ECDHE-RSA-CHACHA20-POLY1305:ECDHE-ECDSA-AES128-GCM-SHA256:ECDHE-RSA-AES128-GCM-SHA256:ECDHE-ECDSA-AES256-SHA384:ECDHE-RSA-AES256-SHA384:ECDHE-ECDSA-AES128-SHA256:ECDHE-RSA-AES128-SHA256';
    ssl_prefer_server_ciphers on;

    ssl_dhparam /etc/ssl/private/dhparam.pem;
```

`ssl_dhparam /etc/ssl/private/dhparam.pem;`:

This specifies a file with DH parameters for EDH (Ephemeral Diffie-Hellman) ciphers.
By default, NGINX will use the default DHE paramaters provided by openssl. This uses a weak key that gets lower scores.
Run `openssl dhparam -out /etc/ssl/private/dhparam.pem 4096` in terminal to generate it.

We RECOMMEND to use the [Mozilla SSL Configuration Generator](https://ssl-config.mozilla.org/) to generate a suitable configuration and the [Qualys SSL Labs Tests](https://www.ssllabs.com/ssltest/) or the [High-Tech Bridge SSL Server Test](https://www.htbridge.com/ssl/) to check your settings. It is recommended to reach a "A" rating as minimum.

It is necessary to often revise these configuration.
In best case, you always use the latest "Modern" configuration.

### Serve security related Headers

To improve the security of your ILIAS users you SHOULD set the following Headers:
(this is an experience based configuration set of headers, this may differ from your configuration/usage scenario)

```
    X-Content-Type-Options: nosniff;
    X-XSS-Protection: 1; mode=block;
    X-Frame-Options: SAMEORIGIN;
    Referrer-Policy: strict-origin;
    Feature-Policy sync-xhr 'self';
    add_header Content-Security-Policy "default-src 'self'; connect-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval' blob:; style-src 'self' 'unsafe-inline' data:; img-src 'self' 'unsafe-inline' data:; font-src 'self' 'unsafe-inline' data:; media-src 'self' 'unsafe-inline' data:";
```

*Backward compatibility to  Microsoft Internet Explorer 10*:

```
add_header X-Content-Security-Policy "default-src 'self'; connect-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval' blob:; style-src 'self' 'unsafe-inline' data:; img-src 'self' 'unsafe-inline' data:; font-src 'self' 'unsafe-inline' data:; media-src 'self' 'unsafe-inline' data:";
```

see also: [Browser compatibility of HTTP headers](https://developer.mozilla.org/en-US/docs/Web/HTTP/CSP#Browser_compatibility)

The proposed CSP is to be understood as a starting point. ILIAS is a generic software to support many different LMS scenarios and thus can't
provide a suggestion which fits all circumstances and guarantees the best security.
Depending on the content your users can provide (embedded media, SCORM packages, etc.) you should try to
initially define a CSP which is as strict as possible.

You could use the [Reporting Feature](https://developer.mozilla.org/en-US/docs/Web/HTTP/CSP#enabling_reporting) to
log and analyse CSP violations to loosen the CSP gradually and carefully, if necessary.

A minimum endpoint to log the CSP violations could be:

```php
<?php

declare(strict_types=1);

file_put_contents('csp.log', file_get_contents('php://input') . "\n", FILE_APPEND);
```

This is not a production ready solution, but a starting point to get an idea how to log the CSP violations.

##### Apache

Add the following line INSIDE the `<VirtualHost></VirtualHost>` block:

```
    <IfModule mod_headers.c>
        Header set X-Content-Type-Options "nosniff"
        Header set X-XSS-Protection "1; mode=block"
        Header set X-Frame-Options "SAMEORIGIN;"
        Header set Referrer-Policy "strict-origin"
        Header set Feature-Policy "sync-xhr 'self'"

        # Working with ILIAS in most use cases:
        Header set Content-Security-Policy "default-src 'self'; connect-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval' blob:; style-src 'self' 'unsafe-inline' data:; img-src 'self' 'unsafe-inline' data:; font-src 'self' 'unsafe-inline' data:; media-src 'self' 'unsafe-inline' data:"

        # Working with ILIAS in most use cases:
        Header set X-Content-Security-Policy "default-src 'self'; connect-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval' blob:; style-src 'self' 'unsafe-inline' data:; img-src 'self' 'unsafe-inline' data:; font-src 'self' 'unsafe-inline' data:; media-src 'self' 'unsafe-inline' data:"
    </IfModule>
```

##### NGINX

 You can simply add this in your `server` configuration:

 ```
  # Content-Security
         add_header X-Frame-Options "SAMEORIGIN";
         add_header X-Content-Type-Options nosniff;
         add_header X-XSS-Protection "1; mode=block";
         add_header Referrer-Policy "strict-origin";
         add_header Feature-Policy "sync-xhr 'self';

         # Working with ILIAS in most use cases:
         add_header Content-Security-Policy "default-src 'self'; connect-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval' blob:; style-src 'self' 'unsafe-inline' data:; img-src 'self' 'unsafe-inline' data:; font-src 'self' 'unsafe-inline' data:; media-src 'self' 'unsafe-inline' data:";
         #compatibility to  Microsoft Internet Explorer 10:
         add_header X-Content-Security-Policy "default-src 'self'; connect-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval' blob:; style-src 'self' 'unsafe-inline' data:; img-src 'self' 'unsafe-inline' data:; font-src 'self' 'unsafe-inline' data:; media-src 'self' 'unsafe-inline' data:";
 ```

note:
If you use a proxied [Chat Server](https://github.com/ILIAS-eLearning/ILIAS/blob/release_5-4/Modules/Chatroom/README.md), you MUST add the url to the CSP definition:  

```
connect-src 'self' wss://onscreenchat.%HOSTNAME% https://onscreenchat.%HOSTNAME%;
```

If you use other external content (e.g. for a SCORM module or external video content) you have to add a `default-src` (The default-src is the default policy for loading content such as JavaScript, Images, CSS, Fonts, AJAX requests, Frames, HTML5 Media from other sites):

e.g. for <https://www.youtube.com> as source:

```
default-src 'self' www.youtube.com; ...
```

#### Validate the header configuration

It is recommended to validate your configuration with the services from <https://securityheaders.com>.
Try to reach **A** grade.

### Enable OCSP stapling (TLS Certificate Status Request)

The  TLS/SSL extension [OCSP stapling](https://en.wikipedia.org/wiki/OCSP_stapling) provides a way to improve the performance (as the webserver will fetch the OCS instead of the browser client) of SSL negotiation while maintaining visitor privacy.
The [Online Certificate Status Protocol (OCSP)](https://en.wikipedia.org/wiki/Online_Certificate_Status_Protocol) is used for checking the revocation status of a SSL certificate (X.509 certificate). -> https://tools.ietf.org/html/rfc6066

By adding the following to your SSL VirtualHost configuration your webserver will do the check for you and send signatured information regarding to browser client

#### Apache2

Add the following line INSIDE the `<VirtualHost></VirtualHost>` block:

```
SSLUseStapling on
SSLStaplingResponderTimeout 5
SSLStaplingReturnResponderErrors off
```

Add the following line OUTSIDE  the `<VirtualHost></VirtualHost>` block:

```
SSLStaplingCache shmcb:/tmp/stapling_cache(128000)
```

#### NGINX

```
    ssl_stapling on;
    ssl_stapling_verify on;
    ssl_stapling_file ocsp_response;
    ssl_trusted_certificate /path/to/cert_chain.pem;

    resolver <IP DNS resolver>;
```

#### Check the OCSP configuration

```
openssl s_client -connect %HOSTNAME%:443 -servername %HOSTNAME% -status < /dev/null 2>&1 | grep -i "OCSP response"
```

The expected output of a working OCSP configuration:

```
OCSP response:
OCSP Response Data:
    OCSP Response Status: successful (0x0)
    Response Type: Basic OCSP Response
```

The [Qualys SSL Labs Tests](https://www.ssllabs.com/ssltest/) will also show if your OCSP configuration is working:

```
OCSP stapling Yes
```

### Enable HTTP Strict Transport Security

**A word of warning here** *: This is a very effective, but also complex thing to do. It is recommended you do that - but you should totally know what you're doing. If not, please be aware that you may mess up your installation. If you have the slightest doubt you can pull this off, do yourself (and your users) a huge favor by trying this on an unimportant subdomain first. Really.*

The [HTTP Strict Transport Security (HSTS)](https://en.wikipedia.org/wiki/HTTP_Strict_Transport_Security) header allows a host to enforce the use of HTTPS on the client side. The browser will be informed to use HTTPS.
This protects e.g. from protocol downgrade attacks and cookie hijacking.

By adding the following to your SSL VirtualHost configuration you instruct browsers not to allow any connection to your ILIAS instance using HTTP and prevent visitors from bypassing invalid certificate warnings.

#### Apache2

Ensure you have `mod_headers.so` enabled in Apache2:

```
<IfModule mod_headers.c>
    Header always set Strict-Transport-Security "max-age=15552000; includeSubDomains; preload"
</IfModule>
```

#### NGINX

```
    add_header Strict-Transport-Security "max-age=15552000; includeSubDomains" preload;
```

**Warning:** Before activating the configuration above you MUST make sure that you have a good workflow for maintaining your SSL settings (including certificate renewals) as you will not be able to disable HTTPS access to your site for up to 6 months.

**Tip:** When you test HSTS, use a very short max-age timeout

```
includeSubDomains:       Restrictions also apply to all subdomains of the current domain.
max-age:                 Duration of cached information (180 days)
```

### Generate the ILIAS `ilClientId` cookie with `secure` attribute

(The JF <https://docu.ilias.de/goto.php?target=wiki_1357_JourFixe-2018-04-23> suggested to do this in webserver configuration.)

The browser will include the cookie in an HTTP request only if the request is transmitted over a secure channel (HTTPS).
<https://en.wikipedia.org/wiki/Secure_cookie>

##### Apache

You have to add this to the apache2 configuration.

```
Header edit Set-Cookie ^(.*)$ $1;HttpOnly;Secure
```

note:
Apache2 will use this for all cookie, who are beeing created.

##### NGINX

You can simply add for example `add_header Set-Cookie` in your `server` configuration.

```
    add_header Set-Cookie "ilClientId=%CLIENTID%; Path=/; Secure; HttpOnly";
```

note:
For nginx, you have to generate a specific cookie for your ILIAS client.
This will overide the cookie deliverd by ILIAS, so it is necessary to generate the whole cookie.

## Suppress server signature and PHP version information

By default, web servers include sensitive server information in http headers and detailed PHP version information.
It is recommended to suppress these header informations to prevent detection and exploit of version specific security issues of web server and PHP version.

### Remove “server” information from http header

#### Apache2

* Module mod\_headers is needed

```
<IfModule mod_headers.c>
    Header unset Server
    Header always unset Server
</IfModule>
```

#### NGINX

* Module ngx\_http\_headers\_module is needed

```
    more_set_headers 'Server: ';
```

or better

```
    more_clear_headers  'Server:*';
```

### Remove “X-Powered-By” information from http header

This can also be done by unsetting the header in webserver.
* `Header unset X-Powered-By` for Apache2 or rather
* `more_clear_headers 'X-Powered-By'` for NGINX.

The suggested solution is to set 'expose\_php' to 'off' in your php.ini:

```
    expose_php = Off
```

## deny access or restrict to several files or locations

### ILIAS setup

The access to the ILIAS Installation Wizard `(/setup/setup.php)` MAY be restricted:

Apache2:

```
    <Location /setup>
        <IfVersion < 2.3>
         Order Deny,Allow
         Deny From All
         Allow from %IPADDRESS%
        </IfVersion>

        <IfVersion > 2.3>
            Require all denied
            Require ip %IPADDRESS%
        </IfVersion>
    </Location>
```

Nginx:

```
    location /setup {
        allow %IPADDRESS%;
        deny all;

        # add location for PHP processing here
    }
    location /setup/setup.php {
        allow %IPADDRESS%;
        deny all;
    }

```

Please add the whitelisted ip address (%IPADDRESS%) to grant access to ILIAS setup here.

### Prevent blacklisted files of beeing served by the webserver

If somebody tries to upload a file with a filetype blacklisted by the upload settings, the upload will take place, but the file will be renamed to `filename.sec`. The webserver should not serve this file anymore to it's visitors as the file may consists of malicious software.

Apache2:

```
    <FilesMatch "\.(sec)$">
        Order Deny,Allow
        Deny from All
    </FilesMatch>
```

Nginx:

```
    location ~ [^/]\.sec {
        deny all;
    }
```

### Prevent execution of PHP-Code in data-directory

There may be situations where there is no oppurtunity to disallow uploading php-files e.g. in Computer Science courses. In this case you SHOULD disallow these uploaded code to be executed by the webserver.

Apache2:

```
    <LocationMatch "/data/.*(?i)\.(php)$">
        Order Deny,Allow
        Deny from All
    </LocationMatch>
```

Nginx:

```
    location ~* /data/.*\.php {
        deny all;
    }
```

### Deny Access to local Git-Directory

If you installed ILIAS via git, access the local Git-Directory (.git) SHOULD be denied for visitors via web.

Apache2:

```
    <Directorymatch "^/.*/\.git/">
        Order deny,allow
        Deny from all
    </Directorymatch>
```

Nginx:

```
    location /.git {
        deny all;
    }
```

## Integrity check of ILIAS code in docroot

Local changes of the code of ILIAS can indicate a potential intrusion.

To determine local changes of the code of ILIAS use `git status` / `git diff`.
This will show you the uncommited local changes.
(Beware: Committed local changes remain undetected using this method.)
(If you have conscious code local changes, this can lead to a false positive.)

## Use WebAccessChecker

In previous versions of ILIAS, it might have been possible to access SCORM, Media Files and User Profile Images without beeing logged in by guessing the proper URL and no measures were taken by the admin to deny such access. Since ILIAS 5.1, a new WebAccessChecker (WAC) is activated by default. To make use of WAC you MUST enable `mod_rewrite` in your Apache configuration.

Please note that this will not work with Nginx as `.htaccess-files` are not supported. Instead you MUST add the following to your Nginx configuration file (please note that running ILIAS with Nginx isn't officially supported and certain features like Shibboleth won't work):

This is a NGINX recommended configuration. (note: inside the `%DOCROOT%/data` no PHP will be proceed)

```
    server {
        [...]
        set $root $document_root;
        location ~ /data/ {
            rewrite ^/data/(.*)/(.*)/(.*)$ /Services/WebAccessChecker/wac.php last;

            location ~ [^/]\.php(/|$) { access_log off; log_not_found off; deny all; }
        }

        location ^~ /secured-data {
            # Protected, only working for subrequests (X-Accel-Redirect)
            alias $root/data;
            internal;

            location ~ [^/]\.php(/|$) { access_log off; log_not_found off; deny all; }
        }
         [...]
    }
```

**[ilFileDelivery](https://github.com/ILIAS-eLearning/ILIAS/blob/release_5-4/Services/FileDelivery/classes/override.php.template)** (concerns NGINX/PHP-FPM):
> This is needed if you want to use the ilFileDelivery::DELIVERY_METHOD_XACCEL or the ilFileDelivery::DELIVERY_METHOD_XSENDFILE Method since PHP can't figure out whether X-Accel ist installed or not.""

rename the file:

```
%DOCROOT%/Services/FileDelivery/classes/override.php.template
```

to

```
%DOCROOT%/Services/FileDelivery/classes/override.php
```

## Use secure passwords

Please keep in mind, that your plattform might me be accessible to the world wide web. To avoid unauthorized access to your Ilias-Installation, it his highly recommended to use secure passwords. Especially the root password and the Ilias-Master-Password are potentially endangered.

Your passwords should fullfil the following criterias:

* at least 8 characters in length
* lowercase and uppercase alphabetic characters [possible:  `a-z`, `A-Z`]
* numbers [possible:  `0-9`]
* and symbols [possible:  `_ . + ? # - * @ ! $ % ~ / : ;`]
* do not consist of information that can easily be associated with the user

You MAY generate a password by using the pwgen-command on your webserver's cli

* `-B`: don't include ambiguous characters in the password;
* `-y`: include at least one special symbol in the password

```
    pwgen -By
```

## Report security issues

If you think you found an security related issue in ILIAS please refer to <http://www.ilias.de/docu/goto_docu_wiki_5307.html#ilPageTocA213> for reporting it.
