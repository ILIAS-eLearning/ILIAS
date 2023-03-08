# WebDAV Service

WebDAV or Web Distributed Authoring and Versioning is an extension to HTTP.
This service implements a WebDAV interface to the ILIAS-Repository. Since ILIAS
version 5.4, the sabreDAV library from sabre-io is used to handle the HTTP-Requests.
This services implements functionality behind this requests.

## Problems with Windows Explorer

Because of a special behaviour in the Windows Explorer, it sometimes fails to
add a WebDAV connection with the error code "0x80070043 The Network Name Cannot
Be Found".

To prevent this behaviour, add the following rewrite rules to a .htaccess file
in your webroot or to the corresponding section of the configuration of your
webserver:
```
RewriteCond %{HTTP_USER_AGENT} ^(DavClnt)$
RewriteCond %{REQUEST_METHOD} ^(OPTIONS)$
RewriteRule .* "-" [R=401,L]
```

## Problems with Mac Finder

To upload files, the WebDAV Client *Finder* on Mac uses chunked transfer encoding.
Some webservers can't handle this way of uploading files and are serving ILIAS
an empty files, which results in an empty file object on ILIAS.  Due to a bug in
apache, the configuration of **Apache with PHP-FPM** does not work with the
*Mac Finder*. If you use WebDAV on your ILIAS installation, we recommend to
either use **Apache with mod_php** or **Nginx with PHP-FPM (> 1.3.8)**.

Sabre (the creator of the used WebDAV library) has following things to say to this issue:

> Finder uses Transfer-Encoding: Chunked in PUT request bodies. This is a little
used HTTP feature, and therefore not implemented in a bunch of webservers. The
only server I've seen so far that handles this reasonably well is Apache + mod_php.
Nginx and Lighttpd respond with 411 Length Required, which is completely ignored
by Finder. This was seen on nginx 0.7.63. It was recently reported that a
development release (1.3.8) no longer had this issue.
>
> When using this with Apache + FastCGI PHP completely drops the request body,
so it will seem as if the PUT request was succesful, but the file will end up empty.
>
> Source: https://sabre.io/dav/clients/finder/