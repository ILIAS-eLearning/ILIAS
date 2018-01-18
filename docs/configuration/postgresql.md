# Configure PostgreSQL for ILIAS
## Installation
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

##Memory
You may to need increase PHP memory limit?

##Create database

We RECOMMEND to create a dedicated database user for ILIAS:

```
sudo -u postgres psql
CREATE DATABASE ilias WITH ENCODING 'UTF8';
CREATE USER ilias ENCRYPTED|UNENCRYPTED PASSWORD 'password' LOGIN NOSUPERUSER NOCREATEDB NOCREATEROLE;
GRANT ALL PRIVILEGES ON DATABASE ilias TO ilias;
\q
```

##Restart apache server
After changing the configuration remember to reload the web server daemon:

On Debian/Ubuntu: 
```
systemctl restart apache2.service
```

On RHEL/CentOS: 
```
systemctl restart httpd.service
```

##ILIAS setup
In the ILIAS setup select `Postgres (experimental)` as database type.
In `Database Host` enter `localhost` and in `Database Name` your created PostgreSQL database name.
In `Database user` and `Database Password` enter your PostgreSQL user and password.

