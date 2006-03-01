LT4eL RPC-Server
-------------------------------------------------------------------------------

LT4eL

Project Info

Requirements
-------------------------------------------------------------------------------

This Java server has been tested with Sun Java Runtime Environment 1.4.2.
To handle non-ASCII characters your system should support UTF-8 encodings.

Try:
bash$ export LC_CTYPE=en_EN.UTF-8

to set the default character encoding for the Java Virtual Machine to utf-8, 
before starting the RPC-Server



A Installation
-------------------------------------------------------------------------------

1) Change directory to:

bash$ cd <YOUR_ILIAS_DIR>/Services/WebServices/RPC/LT4eL


2) Edit the file ltServer.properties

ltServer.properties:
IpAddress = 127.0.0.1 # Configure the Ip the RPC server is bound to. 
					  # If the loopback device is configured on your system, you
					  # can use the default value (127.0.0.1)

Port = 11112		  # Configure the port number the RPC server is bound to.
					  # You can use any port.

IndexPath = /tmp	  # Choose an existing directory where lucene will store the
					  # index files. Write permission is required for this dierectory.

LogFile = /var/log/ltServer.log # Choose a filename. The LogFile will be created.

3) Start the server:

Set the default character encoding:

bash$ export LC_CTYPE=en_EN.UTF-8

Start the server:

bash$ java -jar ltServer.jar ltServer.properties &

You will receive the PROCESS_ID of the started RPC-Server

To stop the server simply type:

bash$ kill <PROCESS_ID>

