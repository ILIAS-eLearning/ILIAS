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

This Java server has been tested with Open JDK Java Runtime 1.8.
To be able to index and search for non-ASCII characters your system should
support UTF-8 encodings.

PHP curl and xmlrpc are required for using the Java server features.

On Debian based systems try:

```
bash$ apt-get install php5-curl curl php5-xmlrpc
```

<a name="installation"></a>
# Installation

<a name="create-a-server-configuration-file"></a>
## Create a Server Configuration File

Open the java server configuration in ```Administration ->  General Setting
Java-Server``` and click the button ```Create Configuration File```.

Fill the form and download the configuration file.
Save the newly created file (ilServer.ini) on your ILIAS server, the file location doesn't matter.

<a name="start-the-server"></a>
## Start the server:

**MySQL backends:**

```
bash$ java -Dfile.encoding=UTF-8 -jar <PATH_TO_SERVER>/ilServer.jar <PATH_TO_SERVER_INI> start &
```

**Oracle Backends:**

The Oracle licence is very restrictive. Thus it is not possible to release an all-in-one package 
including an Oracle-JDBC-Driver.

Download an appropriate JDBC-Thin-Client from:
   
http://www.oracle.com/technology/software/tech/java/sqlj_jdbc/htdocs/jdbc_10201.html

The following packages are required:

```
ojdbc14.jar
orai18n.jar
```

**Start the Java-Server including these packages to your CLASSPATH:**

```
bash$java -Dfile.encoding=UTF-8 -cp "<PATH_TO_ojdbc.jar>:<PATH_TO_orai18n.jar>:ilServer.jar" de.ilias.ilServer <PATH_TO_SERVER_INI> start &
```

**To stop the server simply type:**

```
bash$ java -jar <PATH_TO_SERVER>/ilServer.jar <PATH_TO_SERVER_INI> stop
```

**Show the server status:**

```
bash$ java -jar <PATH_TO_SERVER>/ilServer.jar <PATH_TO_SERVER_INI> status
```

**Possible return values are:**

```
Running
Stopped
Indexing
```

<a name="creating-a-new-lucene-index"></a>
## Creating a new Lucene index:

```
bash$ java -jar <PATH_TO_SERVER>/ilServer.jar <PATH_TO_SERVER_INI> createIndex <CLIENT> &
```

The ```<CLIENT_INFO>``` is a combination of the client id and the installation id.
You find these values in the table "Administration -> Server Data".

**Example:**

```
bash$ java -jar <PATH_TO_SERVER>/ilServer.jar <PATH_TO_SERVER_INI> createIndex ilias40_4000 &
```

or

```
bash$ java -jar <PATH_TO_SERVER>/ilServer.jar <PATH_TO_SERVER_INI> createIndex ilias40_0 &
```
if no installation id is given.

<a name="updating-an-existing-index"></a>
## Updating an existing index:

```
bash$ java -jar ilServer.jar <PATH_TO_SERVER_INI> updateIndex <CLIENT> &
```

<a name="performing-a-query"></a>
## Performing a query

```
bash$ java -jar <PATH_TO_SERVER>/ilServer.jar <PATH_TO_SERVER_INI> search <CLIENT> "ilias"
```

<a name="preparing-ilias"></a>
# Preparing ILIAS

  * Log in to ILIAS
  * Setup up the Lucene Host and Port in ```Administration -> General settings -> Java-Server```
  * Enable Lucene Search
  * Enable the option ```Lucene search``` in ```Administration -> Search -> Settings```.

<a name="starting-lucene-server-at-boot-time"></a>
# Starting Lucene server at boot time

<a name="sysv-init"></a>
## SysV-Init

To start the Lucene RPC server automatically at boottime, follow these instructions:

Change the working directory to ```/etc/init.d/``` and create a file named ```ilserver```

```
bash$ cd /etc/init.d  # Adjust this path according to your distribution
bash$ vi ilserver
```

with this content

```
#!/bin/bash
### BEGIN INIT INFO
# Provides:          ilServer
# Required-Start:    $remote_fs $network
# Required-Stop:     $remote_fs $network
# Default-Start:     2 3 4 5
# Default-Stop:      0 1 6
# Short-Description: Start ilServer instances
# Description:       Debian init script for starting ilServer instances
### END INIT INFO

JAVABIN=/usr/bin/java
ILIASDIR=/var/www/ilias            # Type in the root directory of your ILIAS installation
IL_SERVER_INI=/path_to_server_ini  # Type in the path to your ilserver.ini

case "$1" in
    start)
    echo "Starting ILIAS Java-Server"
        $JAVABIN -Dfile.encoding=UTF-8 -jar $ILIASDIR/Services/WebServices/RPC/lib/ilServer.jar $IL_SERVER_INI start &
    ;;

    stop)
        echo "Shutting down ILIAS Java-Server"
        $JAVABIN -jar $ILIASDIR/Services/WebServices/RPC/lib/ilServer.jar $IL_SERVER_INI stop
        ;;
        
    status)
        $JAVABIN -jar $ILIASDIR/Services/WebServices/RPC/lib/ilServer.jar $IL_SERVER_INI status
        ;;      

    restart)
        $0 stop
        sleep 2
        $0 start
        ;;

    *)
        echo "Usage: $0 {start|stop|status|restart}"
        exit 1
esac

exit 0
```

Change the file permissions by typing

```
bash$ chmod 750 ilserver
```

**You can start the ILIAS Java-Server by typing:**

```
bash$ /etc/init.d/ilserver start
```

**stop it:**

```
bash$ /etc/init.d/ilserver stop
```

**restart it:**

```
bash$ /etc/init.d/ilserver restart
```

**or receive the status:**

```
bash$ /etc/init.d/ilserver status
```

You can start the ILIAS Java-Server automatically at boottime by executing ```update-rc.d ilserver enable``` or linking ```/etc/init.d/ilserver``` to ```/etc/rc.X``` (where ```X``` is the desired runlevel).
