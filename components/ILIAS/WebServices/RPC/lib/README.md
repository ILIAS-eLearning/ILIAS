# Lucene RPC-Server

<!-- MarkdownTOC depth=0 autolink="true" bracket="round" autoanchor="true" style="ordered" indent="   " -->

1. [Apache Lucene](#apache-lucene)
   1. [Requirements](#requirements)
1. [Installation](#installation)
   1. [Create a Server Configuration File](#create-a-server-configuration-file)
   1. [Start the server:](#start-the-server)
   1. [Creating a new Lucene index:](#creating-a-new-lucene-index)
   1. [Updating an existing index:](#updating-an-existing-index)
   1. [Performing a query](#performing-a-query)
1. [Preparing ILIAS](#preparing-ilias)
1. [Starting Lucene server at boot time](#starting-lucene-server-at-boot-time)
   1. [SysV-Init](#sysv-init)

<!-- /MarkdownTOC -->

<a name="apache-lucene"></a>
# Apache Lucene

Apache Lucene is a high-performance, full-featured text search engine library
written entirely in Java. It is a technology suitable for nearly any application
that requires full-text search, especially cross-platform.

<a name="requirements"></a>
## Requirements

This Java server has been tested with Open JDK Java Runtime 17.
To be able to index and search for non-ASCII characters your system should
support UTF-8 encodings.

PHP curl and xmlrpc are required for using the Java server features.

On Debian based systems try:

````shell
> apt-get install php-curl php-xmlrpc openjdk-17-jdk-headless
````
Dependencies and the build process is managed via maven
```shell
> apt-get install maven
```

<a name="installation"></a>
# Installation

<a name="build-java-server"></a>
## Build the Java RPC Server
```shell
> cd Services/WebServervices/RPC/lib
> mvn clean install
```
To build/compile the jar file for older LTS release than v17, start the maven build process with the following parameters:
```shell
# java 11
> mvn clean install -Dmaven.compiler.release=11
# java 8
> mvn clean install -Dmaven.compiler.source=8 -Dmaven.compiler.target=8
```

The newly generated ilServer.jar has been created in the target-directory.
Now move the target directory to the external data directory or any other location.
```shell
> mv target {PATH_TO_EXTERNAL_DATA}
```

## Configure the Java RPC Server
Create a config file readable by the webserver user/group with following contents. E.g in the external data directory
```shell
> vi {PATH_TO_EXTERNAL_DATA}/ilServer.ini
[Server]
IpAddress = localhost
Port = 11111
IndexPath = /var/www/html/ilias/data/
LogFile = /var/www/files/ilServer.log
LogLevel = INFO 
NumThreads = 1
RamBufferSize = 256
IndexMaxFileSizeMB = 500

[Client1]
ClientId = ACMECorp
NicId = 0
IliasIniPath = /var/www/html/ilias/ilias.ini.php
```
## Manage Startup using systemd
```shell
> vi /etc/systemd/system/ilserver.service
[Unit]
Description=ILIAS Java-Server
After=network.target

[Service]
User=www-data
Group=www-data
ExecStart=/usr/bin/java -jar {PATH_TO_EXTERNAL_DATA}/target/ilServer.jar {PATH_TO_EXTERNAL_DATA}/ilServer.ini start
ExecStop=/usr/bin/java -jar {PATH_TO_EXTERNAL_DATA}/target/ilServer.jar {PATH_TO_EXTERNAL_DATA}/ilServer.ini stop
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
> java -jar {PATH_TO_EXTERNAL_DATA}/target/ilServer.jar {PATH_TO_EXTERNAL_DATA}/ilServer.ini status
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
> java -jar {PATH_TO_EXTERNAL_DATA}/target/ilServer.jar {PATH_TO_EXTERNAL_DATA}/ilServer.ini createIndex <CLIENT_INFO>
```

The ```<CLIENT_INFO>``` is a combination of the client id and the installation id.
You find these values with the setup status command:
```shell
> cd <ILIAS_ROOT_DIRECTORY>
> php cli/setup.php status
...
config:
    common:
        client_id: default
        inst_id: 12345678
...
```

Example:
```shell
> java -jar {PATH_TO_EXTERNAL_DATA}/target/ilServer.jar {PATH_TO_EXTERNAL_DATA}/ilServer.ini createIndex default_12345678
```
or
```shell
> java -jar {PATH_TO_EXTERNAL_DATA}/target/ilServer.jar {PATH_TO_EXTERNAL_DATA}/ilServer.ini createIndex default_0
```
if no installation id is given.

<a name="updating-an-existing-index"></a>
## Updating an existing index:

```shell
> java -jar {PATH_TO_EXTERNAL_DATA}/target/ilServer.jar {PATH_TO_EXTERNAL_DATA}/ilServer.ini updateIndex <CLIENT_INFO>
```

<a name="performing-a-query"></a>
## Performing a query

```shell
> java -jar {PATH_TO_EXTERNAL_DATA}/target/ilServer.jar {PATH_TO_EXTERNAL_DATA}/ilServer.ini search <CLIENT_INFO> "ilias"
```