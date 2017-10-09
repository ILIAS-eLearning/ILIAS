# Chat Server Setup

<!-- MarkdownTOC depth=0 autolink="true" bracket="round" autoanchor="true" style="ordered" indent="   " -->

1. [Requirements](#requirements)
1. [Usage Examples](#usage-examples)
1. [Configuration](#configuration)
1. [ILIAS Configuration](#ilias-configuration)
 1. [Server Settings](#server-settings)
    1. [IP-Address/FQN](#ip-addressfqn)
    1. [Port](#port)
    1. [Sub-Directory](#sub-directory)
    1. [Protocol](#protocol)
    1. [Logging](#logging)
    1. [ILIAS to Server Connection](#ilias-to-server-connection)
    1. [Client to Server Connection](#client-to-server-connection)
 1. [Client Settings](#client-settings)
    1. [Enable Chat](#enable-chat)
    1. [Enable On-Screen notifications](#enable-on-screen-notifications)
    1. [Authentication](#authentication)

<!-- /MarkdownTOC -->

<a name="requirements"></a>
# Requirements

## NodeJs

It is required that NodeJs is installed on the host machine.
The chat server is tested and runs with the following versions of NodeJs:

  * v0.10.24
  * v0.12.04
  * v0.12.10
  * 4.5.0 (LTS)
  * 6.5.0
  * 6.11.2

## HTTPS

For https using highly secured TLS it is required to have NodeJS >= 0.12.04 installed on the host machine.

You can get the latest version for your distribution from following source:
https://github.com/nodesource/distributions#debinstall

If you want to copy the chat server to another location, make sure to copy
the whole chat folder.

<a name="usage-examples"></a>
# Usage Examples

**Single ILIAS client:**

```
cd [ILIAS_ROOT_DIRECTORY]
node Modules/Chatroom/chat/chat [PATH_TO_EXTERNAL_DATA_DIRECTORY]/[CLIENT_ID]/chatroom/server.cfg [PATH_TO_EXTERNAL_DATA_DIRECTORY]/[CLIENT_ID]/chatroom/client.cfg
```

**Multiple ILIAS clients:**

```
cd [ILIAS_ROOT_DIRECTORY]
node Modules/Chatroom/chat/chat [PATH_TO_EXTERNAL_DATA_DIRECTORY]/[ANY_CLIENT_ID]/chatroom/server.cfg [PATH_TO_EXTERNAL_DATA_DIRECTORY]/[CLIENT_ID_1]/chatroom/client.cfg [PATH_TO_EXTERNAL_DATA_DIRECTORY]/[CLIENT_ID_2]/chatroom/client.cfg ...
```

<a name="configuration"></a>
# Configuration

The basic configuration is done in the ILIAS Chatroom configuration in the ILIAS
Administration. After saving the `Server settings` and `Client Settings` two
config files (`server.cfg` and `client.cfg`) are created in your ILIAS
external data directory in the subfolder chatroom. These files must be addressed
when starting the NodeJs chat server.

If you want to handle multiple ILIAS clients with one chat server multiple `client.cfg` can be passed as start parameters.

After you made changes, the chat server must be restarted.

<a name="ilias-configuration"></a>
## ILIAS Configuration

<a name="server-settings"></a>
### Server Settings

<a name="ip-addressfqn"></a>
#### IP-Address/FQN

The IPv4 address/FQN , the chat server will be listening on

Examples: 
  
  * 192.168.1.1
  * my.domain.com

<a name="port"></a>
#### Port

The port to be bound to the chat server

  * e.g. `8080`

<a name="sub-directory"></a>
#### Sub-Directory

There may be configurations where the chat server is not directly located in the document root and the URL to the chat server looks like: `http(s)://[IP/DOMAIN]/[PATH]/[TO]/[CHAT]`.
Because of some technical requirements, it is important to define the relative path in this case.

Examples:

  * Your configuration: http(s)://myilias.de/servers/chat (e.g `/servers/chat`)
  * Your configuration: http(s)://myilias.de (e.g. `empty string`)

<a name="protocol"></a>
#### Protocol

HTTP: chat server opens an unencrypted http socket

HTTPS: chat server opens an ssl encrypted http socket 

For an HTTPS setup you must specify the following settings:

  * Certificate: Path to the ssl certificate file (e.g. `/etc/ssl/certs/server.pem`)
  * Key: Path to the private key file of ssl (e.g. `/etc/ssl/private/server.key`)
  * Diffie-Hellman Parameter: Path to Diffie-Hellman parameter file (e.g. `/etc/ssl/private/dhparam.pem`)

    To generate `dhparam.pem` use `openssl dhparam -out /etc/ssl/private/dhparam.pem 2048`

<a name="logging"></a>
#### Logging

You can configure optional a path for log and error_log. By default the chat server writes logs to his root folder.

Example:

  * `Chat Server Log = /srv/www/logs/ilias_chat.log`
  * `Chat Server ErrorLog = /srv/www/logs/ilias_error_chat.log`

<a name="ilias-to-server-connection"></a>
#### ILIAS to Server Connection

By default ILIAS uses the IP-Address/FQN on which the chat server is listening. There may be some configurations
where the chat server is only accessible through a proxy server. For this you can enable the proxy use and deliver
another URL which is used to connect ILIAS with the chat server.

**Note:** It is possible to define the URL with or without a protocol definition. If the URL contains a protocol definition, it
will be used as defined. If the URL does not contain any protocol definition, the protocol definition of the protocol setting
will be prepended to the URL

Example: 

  * proxy.domain.com:8080

<a name="client-to-server-connection"></a>
#### Client to Server Connection

By default the client uses the IP-Address/FQN on which the chat server is listening. There may be some configurations
where the chat server is only accessible through a proxy server. For this you can enable the proxy use and deliver
another URL which is used to connect the client with the chat server.

**Note:** It is possible to define the URL with or without a protocol definition. If the URL contains a protocol definition, it
will be used as defined. If the URL does not contain any protocol definition, the protocol definition of the protocol setting
will be prepended to the URL

Example: 

  * proxy.domain.com:8081

<a name="client-settings"></a>
### Client Settings

<a name="enable-chat"></a>
#### Enable Chat

Enable/Disable the Chat

<a name="enable-on-screen-notifications"></a>
#### Enable On-Screen notifications

If enabled, users are notified by a popup about new Invitations

**Refreshinterval:**
Polling interval for checking of new notifications. A lower number will
notify the user more quickly but increases the number of requests the 
webserver must handle.

<a name="authentication"></a>
#### Authentication

The chat server uses BasicAuth for Api authentication. ILIAS is able to generate a key pair of RFC 4122 based string
which is used to authenticate the ILIAS instance in the chat server.

Example: 

  * Key = 801c4a44-739a-45d8-93df-880e7a2ac8e7
  * Secret = 04e60a6e-e9e7-4eb7-bd01-96e65b9a767c
