# Lucene RPC-Server

**Table of Contents**
* [Apache Lucene](#apache-lucene)
    * [Requirements](#requirements)
* [Installation](#installation)
    * [Build the Java RPC server](#build-the-java-rpc-server)
    * [Confgure the server](#configure-the-java-rpc-server)
    * [Manage Startup](#manage-startup-using-systemd)
    * [Start the Server](#start-the-server)
    * [Show Additional Status Info](#show-additional-status-info)
    * [Creating a new Index](#creating-a-new-lucene-index)
    * [Updating an existing index](#manage-startup-using-systemd)
    * [Performing a query](#performing-a-query)

<a name="apache-lucene"></a>
# Apache Lucene

Apache Lucene is a high-performance, full-featured text search engine library
written entirely in Java. It is a technology suitable for nearly any application
that requires full-text search, especially cross-platform.

<a name="requirements"></a>
## Requirements

This Java server has been tested with Open JDK Java Runtime 11.
To be able to index and search for non-ASCII characters your system should
support UTF-8 encodings.

PHP curl and xmlrpc are required for using the Java server features.

On Debian-based systems try:

```shell
> apt-get install php-curl php-xmlrpc openjdk-11-jdk-headless
```

<a name="installation"></a>
# Installation
Copy or link the "ilServer" jar file to any location readable by the webserver
```shell
> cp -a {ILIAS_ROOT}/Services/WebServices/RPC/lib/ilServer.jar /foo/bar
or
> ln -s {ILIAS_ROOT}/Services/WebServices/RPC/lib/ilServer.jar /foo/bar/ilServer.jar
```
## Configure the Java RPC Server
Create a config file readable by the webserver user/group with following contents. E.g in the external data directory
```shell
> vi /foo/bar/ilServer.ini
[Server]
IpAddress = 127.0.0.1
Port = 11111
IndexPath = /var/www/files/lucene
LogFile = /var/www/logs/ilServer.log
LogLevel = INFO
NumThreads = 2
RamBufferSize = 256
IndexMaxFileSizeMB = 500

[Client1]
ClientId = ACMECorp
NicId = 0
IliasIniPath = /var/www/html/ilias/ilias.ini.php
```

- IpAddress: normally localhost is sufficient
- Port: any free non pivileged port
- IndexPath: any directory with read/write access for the webserver user
- LogFile: Directory must exist. Read/write access for the webserver is required
- LogLevel: one of INFO, DEBUG, WARN, ERROR, FATAL
- NumThreads: The larger the number of NumThreads, the shorter the indexing time, at the expense of the overall CPU load.
- RamBufferSize: The maximum amount of memory in MB before index data is written to the file system
- IndexMaxFileSize: The maximum file size of ILIAS files that can be included in the index.

- ClientId: ClientId of ILIAS installation
- NicId: NicID of ILIAS installation
- IliasIniPath: absolute path to ilias.ini.php

### Configure the ILIAS RPC connection settings
Adapt the ILIAS setup config file variables (rpc_server_host and rpc_server_port) according
to [documentation of the command line setup](../../../../setup/README.md)

## Manage Startup using systemd
```shell
> vi /etc/systemd/system/ilserver.service
[Unit]
Description=ILIAS Java-Server
After=network.target

[Service]
User=www-data
Group=www-data
ExecStart=/usr/bin/java -jar /foo/bar/ilServer.jar /foo/bar/ilServer.ini start
ExecStop=/usr/bin/java -jar /foo/bar/ilServer.jar /foo/bar/ilServer.ini stop
TimeoutStopSec=10

[Install]
WantedBy=multi-user.target
> systemctl enable ilserver.service
```

<a name="start-the-server"></a>
## Start the Server
```shell
> systemctl start ilserver.service
```
## Show Additional Status Info
```shell
> java -jar {PATH_TO_EXTERNAL_DATA}/ilServer.jar {PATH_TO_EXTERNAL_DATA}/ilServer.ini status
```

Possible return values are:
```
Running
Stopped
Indexing
```

<a name="creating-a-new-lucene-index"></a>
## Creating a new Lucene index:

```shell
> java -jar /foo/bar/ilServer.jar /foo/bar/ilServer.ini createIndex <CLIENT_INFO>
```

The ```<CLIENT_INFO>``` is a combination of the client id and the installation id.
You find these values with the setup status command:
```shell
> cd <ILIAS_ROOT_DIRECTORY>
> php setup/cli.php status
...
config:
    common:
        client_id: default
        inst_id: 12345678
...
```

Example:
```shell
> java -jar /foo/bar/ilServer.jar /foo/bar/ilServer.ini createIndex default_12345678
```
or
```shell
> java -jar /foo/bar/ilServer.jar /foo/bar/ilServer.ini createIndex default_0
```
if no installation id is given.

<a name="updating-an-existing-index"></a>
## Updating an existing index:

```shell
> java -jar /foo/bar/ilServer.jar /foo/bar/ilServer.ini updateIndex <CLIENT_INFO>
```

<a name="performing-a-query"></a>
## Performing a query

```shell
> java -jar /foo/bar/ilServer.jar /foo/bar/ilServer.ini search <CLIENT_INFO> "ilias"
```