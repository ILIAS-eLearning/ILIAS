= Chatserver setup =

== Requirements ==

  JRE 1.6 (Java Runtime)
    The sources has been compiled using version OpenJDK 1.6.0_20. If you have
    any problems running the chatserver using your OpenJDK please contact
    jposselt (at) databay.de with detailed RTE information.

  SOAP webservices must be enabled in your ILIAS administration

== Usage ==

Single ILIAS client:
  java -jar Chatserver.jar "path/to/server.properties" "path/to/client.properties"

Multiple ILIAS clients:
  java -jar Chatserver.jar "path/to/server.properties" "path/to/first/client.properties" "path/to/second/client.properties" ....

== Introduction ==

The basic configuration is done in the ILIAS Chatroom configuration in the ILIAS
Administration. After saving the "Server settings" and "Client Settings" two
config files (server.properties and client.properties) are created in your ILIAS
external data directory in the subfolder chatroom. These files must be addressed
when starting the JAR package.

If you want to handle multiple ILIAS clients with one chatserver multiple
"client.properties" can be passed as start parameters.

After you made changes, the chatserver must be restarted.

== ILIAS Configuration == 

=== Server Settings ===

==== Address ====
  Example: 192.168.1.1

  The IPv4 address, the chatserver will be listen on

==== Port ====
  Example: 8080

  The port to be bound to the chatserver

==== Privileged hosts ====
  Example: 192.168.1.1

  Your webserver IP.

  Allowed IPv4 adresses from which the server accepts controlling connections
  (e.g. connecting a user, posting information). All requests are sent from the
  webservers IP address.

==== Protocol ====
  HTTP: chatserver opens an unencrypted http socket

  HTTPS: chatserver opens an ssl encrypted http socket
    For an HTTPS setup you must specify the following settings.

    Keystore: Path to a PKCS12 file containing all needed certificates/keys
        Example: /srv/private/server.p12

    Keypass: Password for the private key
	Example: mySecretKeyPassword

    Storepass: Password for the PKCS12 storage 
	Example: mySecretKeyStoragePassword


=== Client Settings ===

==== Enable Chat ====
  Enable/Disable the Chat

==== Enable On-Screen notifications ====
  If enabled, users are notified by a popup about new Invitations

  Refreshinterval:
    Polling interval for checking of new notifications. A lower number will
    notify the user more quickly but increases the number of requests the 
    webserver must handle.

==== Name ====
   Example: myclient_chat

   A name that is used by ILIAS and the chatserver to identify your ILIAS. If
   using more than one client per chat server, this name must be unique per
   ILIAS instance.

==== URL ====
   Example: http://yourilias.com/ilias4

   Path to your ILIAS installation.

==== User ====
   Example: soap_user

   Username which is used by the chatserver to send information back to ILIAS.
   This must be a valid user with an accepted user aggreement. Please log in to
   ILIAS with this user to ensure that this account is usable.

==== Password ====
   Example: mySecret

   The password for the user.

==== Client ====
   Example: myClient

   The name of the ILIAS client for this configuration.
