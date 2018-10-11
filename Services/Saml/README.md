# SAML Authentication

The key words "MUST", "MUST NOT", "REQUIRED", "SHALL", "SHALL NOT", "SHOULD",
"SHOULD NOT", "RECOMMENDED", "MAY", and "OPTIONAL"
in this document are to be interpreted as described in
[RFC 2119](https://www.ietf.org/rfc/rfc2119.txt).

**Table of Contents**

* [Server Configuration](#web-server-configuration)

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