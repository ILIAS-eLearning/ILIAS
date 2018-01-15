# Configure PostgreSQL for ILIAS
First install PostgreSQL database server and PostgreSQL PHP support on your machine

On Debian/Ubuntu 14.04 execute:
```
`apt-get install postgresql php5-pgsql`
```

On Debian/Ubuntu 16.04 execute:
```
`apt-get install postgresql php7-pgsql`
```

On RHEL/CentOS execute:
```
`yum install postgresql php5-pgsql`
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

You may to need increase PHP memory limit?
```
memory_limit=1GB
```

We RECOMMEND to create a dedicated database user for ILIAS:

```
sudo -u postgres psql
CREATE DATABASE ilias WITH ENCODING 'UTF8';
CREATE USER ilias ENCRYPTED|UNENCRYPTED PASSWORD 'password' LOGIN NOSUPERUSER NOCREATEDB NOCREATEROLE;
GRANT ALL PRIVILEGES ON DATABASE ilias TO ilias;
\q
```
