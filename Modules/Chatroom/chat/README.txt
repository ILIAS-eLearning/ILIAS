= Chatserver setup =

== Requirements ==

  NodeJs
    It is required that NodeJs is installed on the host machine

  Https
    For https using highly secured TLS it is required to have NodeJS >= 0.12.04 installed on the host machine.
    You can get the latest version for your distribution from following source:
    `https://github.com/nodesource/distributions#debinstall`.

  If you want to copy the Chatserver to another location, make sure to copy
  the whole chat folder.

== Usage ==

Single ILIAS client:
  node chat path/to/server.cfg path/to/client.cfg

Multiple ILIAS clients:
  node chat path/to/server.cfg path/to/first/client.cfg path/to/second/client.cfg ....

== Introduction ==

The basic configuration is done in the ILIAS Chatroom configuration in the ILIAS
Administration. After saving the "Server settings" and "Client Settings" two
config files (server.cfg and client.cfg) are created in your ILIAS
external data directory in the subfolder chatroom. These files must be addressed
when starting the NodeJs Chatserver.

If you want to handle multiple ILIAS clients with one chatserver multiple
"client.cfg" can be passed as start parameters.

After you made changes, the chatserver must be restarted.

== ILIAS Configuration == 

=== Server Settings ===

==== IP-Address/FQN ====
  Example: 192.168.1.1
  Example: my.domain.com

  The IPv4 address/FQN , the chatserver will be listening on

==== Port ====
  Example: 8080

  The port to be bound to the chatserver

==== Protocol ====
  HTTP: chatserver opens an unencrypted http socket

  HTTPS: chatserver opens an ssl encrypted http socket
    For an HTTPS setup you must specify the following settings.

    Certificate: Path to the ssl certificate file
        Example: /srv/ssl/server.cert

    Key: Path to the private key file of ssl
        Example: /srv/private/server.key

    Diffie-Hellman Parameter: Path to Diffie-Hellman parameter file
        Example: /srv/private/dhparam.pem

==== Logging ====
    You can configure optional a path for log and error_log. By default the Chatserver writes logs to his root folder.

    Example: Chatserver Log = /srv/www/logs/ilias_chat.log
    Example: Chatserver Error-Log = /srv/www/logs/ilias_error_chat.log

==== ILIAS to Server Connection ====
    By default the ilias uses the IP-Address/FQN on which the chatserver is listening. There may be some configurations
    where the chatserver is only accessible through a proxy server. For this you can enable the proxy use and deliver
    another URL which is used to connect ILIAS with the Chatserver.
    Note: Please do not insert the protocol to url definition

    Example: proxy.domain.com:8080

==== Client to Server Connection ====
    By default the client uses the IP-Address/FQN on which the chatserver is listening. There may be some configurations
    where the chatserver is only accessible through a proxy server. For this you can enable the proxy use and deliver
    another URL which is used to connect the client with the Chatserver.
    Note: Please do not insert the protocol to url definition

	Example: proxy.domain.com:8081

=== Client Settings ===

==== Enable Chat ====
  Enable/Disable the Chat

==== Enable On-Screen notifications ====
  If enabled, users are notified by a popup about new Invitations

  Refreshinterval:
    Polling interval for checking of new notifications. A lower number will
    notify the user more quickly but increases the number of requests the 
    webserver must handle.

==== Authentication ====
   The Chatserver uses BasicAuth for Api authentication. ILIAS is able to generate a key pair of RFC 4122 based string
   which is used to authenticate the ILIAS instance in the chatserver.

   Example: Key = 801c4a44-739a-45d8-93df-880e7a2ac8e7
         Secret = 04e60a6e-e9e7-4eb7-bd01-96e65b9a767c
