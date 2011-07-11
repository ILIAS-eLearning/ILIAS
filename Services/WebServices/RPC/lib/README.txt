Lucene RPC-Server
-------------------------------------------------------------------------------

Apache Lucene

Apache Lucene is a high-performance, full-featured text search engine library 
written entirely in Java. It is a technology suitable for nearly any application 
that requires full-text search, especially cross-platform.

Requirements
-------------------------------------------------------------------------------

This Java server has been tested with Sun Java Runtime Environment 1.6.
To be able to index and search for non-ASCII characters your system should
support UTF-8 encodings.

PHP curl is required for using the Java server features.
On Debian based systems try:

bash$ apt-get install php5-curl curl


A Installation
-------------------------------------------------------------------------------

1) Create a Server Configuration File

Open the java server configuration in "Administration ->  General Setting
Java-Server" and click the button "Create Configuration File".

Fill the form and download the configuration file.
Save the newly created file (ilServer.ini) on your ILIAS server.


2) Change directory to:

bash$ cd <YOUR_ILIAS_DIR>/Services/WebServices/RPC/lib

3) Start the server:

MySQL backends:
bash$ java -Dfile.encoding=UTF-8 -jar ilServer.jar <PATH_TO_SERVER_INI> start &

Oracle Backends:
The Oracle licence is very restrictive. Thus it is not possible to release an all-in-one package 
including an Oracle-JDBC-Driver.

Download an appropriate JDBC-Thin-Client from:
   
http://www.oracle.com/technology/software/tech/java/sqlj_jdbc/htdocs/jdbc_10201.html

The following packages are required:
ojdbc14.jar
orai18n.jar

Start the Java-Server including these packages to your CLASSPATH

bash$java -Dfile.encoding=UTF-8 -cp "<PATH_TO_ojdbc.jar>:<PATH_TO_orai18n.jar>:ilServer.jar" de.ilias.ilServer <PATH_TO_SERVER_INI> start &

To stop the server simply type:

bash$ java -jar ilServer.jar <PATH_TO_SERVER_INI> stop


Show the server status:

bash$ java -jar ilServer.jar <PATH_TO_SERVER_INI> status

Possible return values are:

Running
Stopped
Indexing

4) Creating a new Lucene index:

bash$ java -jar ilServer.jar <PATH_TO_SERVER_INI> createIndex <CLIENT> &

The <CLIENT_INFO> is a combination of the client id and the installation id.
You find these values in the table "Administration -> Server Data".

E.g
bash$ java -jar ilServer.jar <PATH_TO_SERVER_INI> createIndex ilias40_4000 &

or

bash$ java -jar ilServer.jar <PATH_TO_SERVER_INI> createIndex ilias40_0 &

if no installation id is given.

5) Updating an existing index:

bash$ java -jar ilServer.jar <PATH_TO_SERVER_INI> updateIndex <CLIENT> &

6) Performing a query

bash$ java -jar ilServer.jar <PATH_TO_SERVER_INI> search <CLIENT> "ilias"


B Preparing ILIAS
--------------------------------------------------------------------------------

1) Log in to ILIAS

2) Setup up the Lucene Host and Port in 
   'Administration -> General settings -> Java-Server'

3) Enable Lucene Search

Enable the option "Lucene search" in "Administration -> Search -> Settings".



C Starting Lucene server at boot time
--------------------------------------------------------------------------------

To start the Lucene RPC server automatically at boottime, follow these instructions:


1) Change to the super user root

bash$ su

Type in your root password

2) Change the working directory and create a file named rpcserver

bash$ cd /etc/init.d	# Adjust this path according to your distribution
bash$ vi ilserver

with this content

#!/bin/bash

JAVABIN=/usr/bin/java
ILIASDIR=/var/www/ilias    # Type in the root directory of your ILIAS installation
IL_SERVER_INI=/path_to_server_ini

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

3) Change the file permissions by typing

bash$ chmod 750 ilserver

4) You can start the ILIAS Java-Server by typing:

bash$ /etc/init.d/ilserver start

stop it:

bash$ /etc/init.d/ilserver stop

restart it:

bash$ /etc/init.d/ilserver restart

or receive the status:

bash$ /etc/init.d/ilserver status

5) You can start the ILIAS Java-Server automatically at boottime by linking 'ilserver'
to one of the /etc/rcX.d ( X indicates the specific runlevel).
