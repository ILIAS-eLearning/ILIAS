# ILIAS with nginx and Shibboleth

## Intro

This guide describes the installation of ILIAS with nginx and Shibboleth on
Ubuntu 16.04 (Xenial).

## Overview

```

                                    +------------+       4.       +---------+
                                    |   client   |--------------->|   IdP   |
                                    +------------+                +---------+
                                          |
                                       1. |
                                          |
                                          V
                                    +-----------+
                                    |   nginx   |
                                    +-----------+
                                          |
           +------------------------------+----------------------------+
        2. |                           5. |                         7. |
           V                              V                            V
+--------------------+          +-------------------+           +-------------+
|   shibauthorizer   |          |   shibresponder   |           |   php-fpm   |
+--------------------+          +-------------------+           +-------------+
           |                              |
           +------------+     +-----------+
                     3. |     | 6.
                        V     V
                     +-----------+
                     |   shibd   |
                     +-----------+
```

Legend:

1. client requests url
2. shibauthorizer checks if url is protected by shibboleth
3. shibd provides the list of protected urls
4. if the url is protected by shibboleth, the client requests authentication
   from the identity provider (IdP)
5. client forwards the authentication metadata to the shibresponder
6. shibd checks if the authentication is valid
7. php-fpm renders the application

## Installation

### Dependencies

Install ILIAS according to the [official documentation](https://github.com/ILIAS-eLearning/ILIAS/blob/release_5-3/docs/configuration/install.md),
then install shibboleth according to the [Switch AAI guidelines](https://www.switch.ch/aai/guides/sp/).

Install nginx and php-fpm from the official repositories:

```bash
apt-get install -y nginx php-fpm
```

### Building nginx-http-shibboleth

This step is only necessary if no prebuilt module is available. Prebuilt
modules exists as part of this guide for the following versions (x86-64 only):

* nginx-http-shibboleth: 2.0.1
* nginx: 1.10.3, 1.13.10

Install build dependencies:

```bash
apt-get install libpcre3-dev libssl-dev libxml2-dev libxslt1-dev libgd-dev \
    libgeoip-dev
```

Download nginx-http-shibboleth 2.0.1:

```bash
wget -O nginx-http-shibboleth-2.0.1.tar.gz \
    https://github.com/nginx-shib/nginx-http-shibboleth/archive/v2.0.1.tar.gz
tar xzf nginx-http-shibboleth-2.0.1.tar.gz
cd nginx-http-shibboleth-2.0.1
```

Download nginx 1.10.3:

```bash
wget http://nginx.org/download/nginx-1.10.3.tar.gz
tar xzf nginx-1.10.3.tar.gz
cd nginx-1.10.3
```

Determine build flags and build module:

```bash
flags=$(nginx -V 2>&1 \
    | grep "configure arguments" \
    | sed "s/^configure arguments: --with-cc-opt='.*' --with-ld-opt='.*' //")
./configure $flags --add-dynamic-module=..
make modules
```

If all went fine, the built module is located under
`objs/ngx_http_shibboleth_module.so`.

### Enabling Shibboleth FastCGI services

Shibboleth comes with two FastCGI services for authentication:

* shibauthorizer
* shibresponder

These must be enabled in order for the nginx authentication to work. In this
guide we are using SystemD services. Alternatively
[SupervisorD](http://supervisord.org/) works too.

Create the following files:

`/etc/systemd/system/shibauthorizer.socket`:

```ini
[Unit]
Description=Shibboleth FastCGI Authorizer socket

[Socket]
ListenStream=/run/shibboleth/shibauthorizer.sock
SocketUser=_shibd
SocketGroup=www-data
SocketMode=0660

[Install]
WantedBy=sockets.target
```

`/etc/systemd/system/shibresponder.socket`:

```ini
[Unit]
Description=Shibboleth FastCGI Responder socket

[Socket]
ListenStream=/run/shibboleth/shibresponder.sock
SocketUser=_shibd
SocketGroup=www-data
SocketMode=0660

[Install]
WantedBy=sockets.target
```

`/etc/systemd/system/shibauthorizer.service`:

```ini
[Unit]
Description=Shibboleth FastCGI Authorizer
After=network.target shibd.service
Wants=shibd.service
Requires=shibauthorizer.socket

[Service]
User=_shibd
Group=_shibd
WorkingDirectory=/etc/shibboleth
ExecStart=/usr/lib/x86_64-linux-gnu/shibboleth/shibauthorizer
StandardInput=socket

[Install]
WantedBy=multi-user.target
```

`/etc/systemd/system/shibresponder.service`:

```ini
[Unit]
Description=Shibboleth FastCGI Responder
After=network.target shibd.service
Wants=shibd.service
Requires=shibresponder.socket

[Service]
User=_shibd
Group=_shibd
WorkingDirectory=/etc/shibboleth
ExecStart=/usr/lib/x86_64-linux-gnu/shibboleth/shibresponder
StandardInput=socket

[Install]
WantedBy=multi-user.target
```

Enable and start the two services using SystemD:

```bash
systemctl enable shibauthorizer.service shibresponder.service
systemctl start shibauthorizer.service shibresponder.service
```

### Configure nginx virtual host

Download Shibboleth config for nginx:

```bash
wget -O /etc/nginx/shib_fastcgi_params \
    https://raw.githubusercontent.com/nginx-shib/nginx-http-shibboleth/master/includes/shib_fastcgi_params
```

Create virtual host config for ilias (`/etc/nginx/sites-available/ilias`):

```
server {
        listen 80 default_server;
        server_name ilias.example.com;
        return 301 https://$server_name$request_uri;
}
server {
        listen 443 ssl;
        server_name ilias.example.com;

        root /var/www/ilias;
        index index.php;

        ssl_certificate ssl/ilias_example_com.crt;
        ssl_certificate_file ssl/ilias_example_com.key;

        location = /shibauthorizer {
                internal;
                include fastcgi_params;
                fastcgi_pass unix:/run/shibboleth/shibauthorizer.sock;
        }
        location /Shibboleth.sso {
                include fastcgi_params;
                fastcgi_pass unix:/run/shibboleth/shibresponder.sock;
        }
        location = /shib_login.php {
                shib_request /shibauthorizer;
                include shib_fastcgi_params;

                # mapping additional shibboleth headers to fastcgi params
                shib_request_set $shib_uniqueid $upstream_http_variable_uniqueid;
                fastcgi_param uniqueID $shib_uniqueid;
                shib_request_set $shib_givenname $upstream_http_variable_givenname;
                fastcgi_param givenName $shib_givenname;
                shib_request_set $shib_surname $upstream_http_variable_surname;
                fastcgi_param surname $shib_surname;
                shib_request_set $shib_mail $upstream_http_variable_mail;
                fastcgi_param mail $shib_mail;

                include snippets/fastcgi-php.conf;
                fastcgi_pass unix:/run/php/php7.0-fpm.sock;
        }

        location ~ \.php {
                include snippets/fastcgi-php.conf;
                fastcgi_pass unix:/run/php/php7.0-fpm.sock;

        }
}
```

### Configure Shibboleth access control

Add the following to `/etc/shibboleth/shibboleth2.xml`, adapt according to your
authorisation requirements:

```xml
<RequestMapper type="XML">
    <RequestMap>
        <Host name="ilias.example.com">
            <Path name="shib_login.php" authType="shibboleth" requireSession="true">
                <AccessControl>
                    <Rule require="valid-user"/>
                </AccessControl>
            </Path>
        </Host>
    </RequestMap>
</RequestMapper>
```

## Caveats

### Goto links and WebAccessChecker

The default RewriteRules of ILIAS for Apache (.htaccess) do not apply. You have
to manually add them to your nginx virtual host config:

```
rewrite ^/goto_(.*)_(wiki_([0-9]+|wpage)(.*)).html$  /goto.php?client_id=$1&target=$2       last;
rewrite ^/([^\/]*)_user_(.*)$                        /goto.php?client_id=$1&target=usr_n$2  last;
rewrite ^/goto_(.*)_([a-z]+_[0-9]+(.*)).html$        /goto.php?client_id=$1&target=$2       last;
rewrite ^/data/.*/.*/.*$                             /Services/WebAccessChecker/wac.php     last;
```

### File delivery via X-Accel

ILIAS does not support the nginx equivalent to XSendfile, X-Accel out of the
box. In order to activate it, add the following location to your nginx virtual
host config:

```
set $ilias_root $document_root;

location /secured-data {
        internal;
        alias $ilias_root/data;
}
```

Furthermore, you will have to override the delivery type in ILIAS using a
special file in `Services/FileDelivery/classes/override.php`:

```php
$override_delivery_type = ilFileDelivery::DELIVERY_METHOD_XACCEL;
```

## Sources

* Shibboleth Wiki - [Integrating Nginx and a Shibboleth SP with FastCGI](https://wiki.shibboleth.net/confluence/display/SHIB2/Integrating+Nginx+and+a+Shibboleth+SP+with+FastCGI)
* SWITCH - [SWITCHaai SP Guide](https://www.switch.ch/aai/guides/sp/)
* Technology Consulting Group Stanford - [Using Shibboleth with nginx](https://www.tcg.stanford.edu/2017/09/using-shibboleth-with-nginx/)
