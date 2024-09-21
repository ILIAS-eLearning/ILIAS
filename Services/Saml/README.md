# SAML Authentication

The key words "MUST", "MUST NOT", "REQUIRED", "SHALL", "SHALL NOT", "SHOULD",
"SHOULD NOT", "RECOMMENDED", "MAY", and "OPTIONAL"
in this document are to be interpreted as described in
[RFC 2119](https://www.ietf.org/rfc/rfc2119.txt).

**Table of Contents**

* [Server Configuration](#web-server-configuration)
* [Performance](#performance)
* [ILIAS Configuration](#ilias-configuration)

## Web Server Configuration

The `PATH_INFO` directive for your web server MUST be enabled
and properly configured. Otherwise HTTP requests
targeting *SimpleSAMLphp* will not be routed to the
corresponding PHP script.

You SHOULD verifiy this by creating an example/a temporary
PHP script in the ILIAS root directory.

```php
<?php
phpinfo();
```

Cal this script via your browser and append a trailing /,
followed by an arbitrary string.

	https://your.ilias.de/phpinfo.php/saml/sp/saml2-acs.php/default-sp

When now searching `PATH_INFO` within the delivered
HTML document, the contents PHP variable `$_SERVER['PATH_INFO']`
MUST be `/saml/sp/saml2-acs.php/default-sp`.

### Apache

See: [AcceptPathInfo Directive](https://httpd.apache.org/docs/2.4/mod/core.html#AcceptPathInfo)

### Nginx

See: [SimpleSAMLphp: Configuring Nginx](https://simplesamlphp.org/docs/development/simplesamlphp-install#section_7)

#### Example
```
[...]

location ~ \.php$ {
	fastcgi_split_path_info ^(.+?\.php)(/.*)$; # SimpleSAMLphp

	# Bypass the fact that try_files resets $fastcgi_path_info
	# see: http://trac.nginx.org/nginx/ticket/321
	set $path_info $fastcgi_path_info;
	fastcgi_param PATH_INFO $path_info;

	fastcgi_pass unix:/run/php/php7.0-fpm.sock;
	fastcgi_keep_conn on;
	fastcgi_index  index.php;
	fastcgi_param  SCRIPT_FILENAME  $document_root/$fastcgi_script_name;
	include /etc/nginx/fastcgi_params;
}

[...]
```

## Performance

For best performance with a huge number of concurrent login requests we recommend
the usage of `memcached` as session storage for the *SimpleSAMLphp* sessions.

See:
- https://simplesamlphp.org/docs/stable/simplesamlphp-maintenance.html#session-management
- https://mantis.ilias.de/view.php?id=37725

## ILIAS Configuration

Please change your ILIAS configuration according to the `Superglobal` behaviour described in
the [`HTTP README`](../../src/HTTP/README.md#dropinreplacements)

## Config Changes in ILIAS 9

This chapter list the differences to ILIAS versions prior to ILIAS 9 for the SAML configuration files.
Please read this chapter if you are migrating to ILIAS 9.

ILIAS 9 uses simplesamlphp v2.0 (prior v1.9). For a complete list of all changes please see https://simplesamlphp.org/docs/stable/simplesamlphp-upgrade-notes-2.0.html

### config.php

The following changes must be made in the `$ILI_DATA/auth/saml/config/config.php`:

The key `debug` is now not a boolean but an array value instead. New installations will have this value as default.
```diff
-    'debug' => true,
+    'debug' => [
+        'saml' => false,
+        'backtraces' => true,
+        'validatexml' => false,
+    ],
```

The key `baseurlpath` must be changed to the following. New installations will have this value as default.
```diff
- 'baseurlpath' => 'simplesaml/',
+ 'baseurlpath' => 'Services/Saml/lib/',
```

If it does not already exist the key `module.enable` must be added with the following content. New installations will have this value as default.
Please note that `exampleauth` and `admin` must always be `false`, as ILIAS doesn't use the admin GUI of the simplesamlphp project.
```diff
+    'module.enable' => [
+        'exampleauth' => false,
+        'core' => true,
+        'admin' => false,
+        'saml' => true,
+    ],
```

### authsources.php

The following changes must be made in the `$ILI_DATA/auth/saml/config/authsources.php`:

The certificate keys `privatekey` and `certificate` are not considered *disabled* when they are set to an empty string. If they are not used, they must not be present.
Prior to ILIAS 9 these keys where added by default as an empty string, for new installations they are now removed.
Please also note, that one should not use SAML unencrypted in production.
```diff
- 'privatekey'  => '',
- 'certificate' => '',
```

The key `NameIDPolicy` cannot be a boolean option. If it should be disabled, an empty array must be used instead. This key is not present in new installations.
```diff
- 'NameIDPolicy' => false,
+ 'NameIDPolicy' => [],
```

The key `NameIDPolicy` cannot be a string. It must be an array of the following format:
```diff
- 'NameIDPolicy' => 'urn:oasis:names:tc:SAML:1.1:nameid-format:unspecified',
+ 'NameIDPolicy' => ['Format' => 'urn:oasis:names:tc:SAML:1.1:nameid-format:unspecified'],
```
