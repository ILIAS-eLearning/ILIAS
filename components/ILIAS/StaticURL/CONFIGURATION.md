To use really short URL, we need to configure the web server to redirect requests to ILIAS's `goto.php` file.

As soon as your Server is configured correctly, you can activate the Setting 
`Readable .html URLs` in the ILIAS Administration under `General Settings`.

### Apache Web Server

Make sure the following configuration is present in your Apache 
configuration file. This should be already the case if you used the
standard ILIAS .htaccess File.

```apache
    RewriteEngine on
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule ^([A-Za-z0-9_-]+)/?$ goto.php/shortlink/$1 [L]
    RewriteRule ^go\/(.*)$ goto.php/$1 [L]
```

### NginX Web Server
Make sure the following configuration is present in your NginX 
configuration file.
```nginx
    location ^~ /go/ {
        rewrite ^/go/(.*)$ /goto.php/$1 last;
    }

    location / {
        try_files $uri $uri/ @shortlink;
    }
    location @shortlink {
        rewrite ^/([A-Za-z0-9_-]+)/?$ /goto.php/shortlink/$1 last;
    }
```
