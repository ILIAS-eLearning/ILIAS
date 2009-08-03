Lucene RPC-Server
-------------------------------------------------------------------------------

Apache Lucene

Apache Lucene is a high-performance, full-featured text search engine library 
written entirely in Java. It is a technology suitable for nearly any application 
that requires full-text search, especially cross-platform.

Requirements
-------------------------------------------------------------------------------

This Java server has been tested with Sun Java Runtime Environment 1.5.
To be able to index and search for non-ASCII characters your system should
support UTF-8 encodings.

ILIAS needs the pear XML_RPC library.


A Installation
-------------------------------------------------------------------------------

1) Install pear XML_RPC:

bash$ pear install XML_RPC

Please check if your XML_RPC package is newer than Release 1.4 due to security bugs in older libraries.
If not upgrade the package XML_RPC:

bash$ pear upgrade XML_RPC

2) Change directory to:

bash$ cd <YOUR_ILIAS_DIR>/Services/WebServices/RPC/lib


3) Edit the file ilServer.properties

ilServer.properties:
IpAddress = 127.0.0.1 # Configure the Ip the RPC server is bound to. 
					  # If the loopback device is configured on your system, you
					  # can use the default value (127.0.0.1)

Port = 11111		  # Configure the port number the RPC server is bound to.
					  # You can use any port.

IndexPath = /tmp	  # Choose an existing directory where lucene will store the
					  # index files. Write permission is required for this dierectory.

LogFile = /var/log/ilServer.log # Choose a filename. The LogFile will be created.

4) Start the server:

Start the server:

bash$ java -Dfile.encoding=UTF-8 -jar ilServer.jar ilServer.properties &

You will receive the PROCESS_ID of the started RPC-Server

To stop the server simply type:

bash$ kill <PROCESS_ID>

B Preparing ILIAS
--------------------------------------------------------------------------------

1) Log in to ILIAS

2) Setup up the Lucene IpAddress and Port in 
   'Administration -> Administration -> Search -> 'Search settings'

3) Tick the checkbox activate lucene

4) Save these settings

5) Enable the Lucene indexing for cron jobs by ticking the checkbox 'Administration -> Cron-jobs -> Update Lucene index'.

6) Enter the IP and the port of the Server in 'Administration -> WebServices':

E.g:
Host: 127.0.0.1
Port: 11111

7) Finally save these settings.


C Indexing ILIAS HTML learning modules
-------------------------------------------------------------------------------

1) Change to the directory of your ILIAS installation.

bash$ cd <YOUR_ILIAS_DIR>

2) Start indexing:

bash$ php cron/cron.php <ADMIN_LOGIN> <ADMIN_PASSWORD> <ILIAS_CLIENT_NAME>

3) you will find informations about the new created index in the Lucene LogFile

D Finally, start searching
-------------------------------------------------------------------------------

1) Log in to ILIAS

2) Click on Search in the Main-Menu

3) Enter a search string and tick the checkboxes 'Detail search', 
   'Learning materials' and 'Files'.


E Starting Lucene server at boot time
--------------------------------------------------------------------------------

To start the Lucene RPC server automatically at boottime, follow these instructions:


1) Change to the super user root

bash$ su

Type in your root password

2) Change the working directory and create a file named rpcserver

bash$ cd /etc/init.d	# Adjust this path according to your distribution
bash$ vi rpcserver

with this content

#!/bin/bash

JAVABIN=/usr/bin/java
ILIASDIR=/var/www/ilias		# Type in the root directory of your ILIAS installation

case "$1" in
	start)
		if [ -f /tmp/rpcserver.pid ]
		then
			echo "The RPC Server seems to be running. Type 'rpcserver stop' or remove the file '/tmp/rpcserver.pid' manually"
			exit 1
		fi
		echo "Starting RPC server"
		$JAVABIN -Dfile.encoding=UTF-8 -jar $ILIASDIR/Services/WebServices/RPC/lib/ilServer.jar $ILIASDIR/Services/WebServices/RPC/lib/ilServer.properties $USER &
		echo $! > /tmp/rpcserver.pid
		;;

	stop)
		echo "Shutting down RPC server"
		{
			kill `cat /tmp/rpcserver.pid`
			unlink /tmp/rpcserver.pid
		} 2> /dev/null
		;;

	restart)
		$0 stop
		sleep 2
		$0 start
		;;

	*)
		echo "Usage: $0 {start|stop|restart}"
		exit 1
esac

exit 0

3) Change the file permissions by typing

bash$ chmod 750 rpcserver

4) You can start the RPC server by typing:

bash$ /etc/init.d/rpcserver start

stop it:

bash$ /etc/init.d/rpcserver stop

or restart it:

bash$ /etc/init.d/rpcserver restart

5) You can start the rpcserver automatically at boottime by linking 'rpcserver'
to one of the /etc/rcX.d ( X indicates the specific runlevel).

