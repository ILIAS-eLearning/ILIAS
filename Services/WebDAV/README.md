# WebDAV Service
WebDAV or Web Distributed Authoring and Versioning is an extension to HTTP. This service implements a WebDAV interface to the ILIAS-Repository. Since ILIAS version 5.4, the sabreDAV library from sabre-io is used to handle the HTTP-Requests. This services implements functionality behind this requests.

## Table of Contents
* [WebDAV Service](#webdav-service)
    * [Table of Contents](#table-of-contents)
    * [Introduction](#introduction)
        * [A short overview to WebDAV](#a-short-overview-to-webdav)
        * [How to read this documentation?](#how-to-read-this-documentation)
    * [SabreDAV and its interfaces](#sabredav-and-its-interfaces)
        * [SabreDAV server](#sabredav-server)
        * [Virtual filesystem](#virtual-filesystem)
        * [SabreDAV locks vs. ILIAS locks](#sabredav-locks-vs-ilias-locks)
    * [classes/](#classes)
        * [dav/](#dav)
            * [ilMountPointDAV](#ilmountpointdav)
            * [ilClientDAV](#ilclientdav)
            * [ilObjectDAV](#ilobjectdav)
        * [auth/](#auth)
        * [db/](#db)
        * [lock/](#lock)
    * [HTTP-Methods](#http-methods)
        * [propfind](#propfind)
        * [get](#get)
        * [put (create file)](#put-create-file)
        * [mkcol](#mkcol)
        * [remove](#remove)
        * [move and move](#move-and-move)
        * [lock / unlock](#lock--unlock)
    * [Terminology](#terminology)
        * [WebDAV Service](#webdav-service)
        * [WebDAV request](#webdav-request)
        * [SabreDAV](#sabredav)


## Introduction
**TL;DR** The WebDAV Service is used to add the ILIAS-Repository to your explorer like an external drive. Instead of opening a Webbrowser to browse through the ILIAS-Repositry, you can do it with any WebDAV client. For example:

* Explorer on Windows
* Finder on Mac
* WinSCP

But everything has its price. Since WebDAV ist just an abstraction to the ILIAS-Repositry, there are some limitations. That means, WebDAV does not replace the use of the ILIAS-Website. It is just meant for simple interaction with the repository like:

* _Browse_ through the files and folder strcutures
* _Download_ files and folder structures
* _Upload_ files and folder structures
* _Rename_ files and folder structures
* _Cope_ files and folder structures
* _Move_ files and folder structures
* _Lock_ files and edit them

For more about the limitations, see chapter xyz

### A short overview to WebDAV

**WebDAV** (short for **Web**-based **D**istributed **A**uthoring and **V**ersioning) is an extension of HTTP that allows clients to perform remote web content authoring operations. It is defined in [RFC 4918](https://tools.ietf.org/html/rfc4918). Following additional requests are defined by WebDAV (normal context = defined by wikipedias):

* **COPY**
    * Normal context: Copy resource from one URI to another
    * ILIAS context: Copy object / contaienr from one container (ref_id) to another
* **LOCK**
    * Normal context: Put a lock on a resource. WebDAV supports both shared and exclusive locks
    * ILIAS context: Put a lock on an ILIAS object
* **MKCOL**
    * Normal context: Create collections (a.k.a. a directory)
    * ILIAS context: Create a container (category inside a category, folder inside other containers)
* **MOVE**
    * Normal context: Move a resource from one URI to another
    * ILIAS context: Move object / container from one container (ref_id) to another
* **PROPFIND**
    * Normal context: Get properties, stored as XML, from a web resource. It is also overloaded to allow one to retrieve the collection structure (also known as directory hierarchy) of a remote system
    * ILIAS context: Get information about objects and the structure in a container
* **PROPPATCH**
    *  Normal context: Change and delete multiple properties on a resource
    *  ILIAS context: Not used
* **UNLOCK**
    *  Normal context: Remove a lock from a resource
    *  ILIAS context: Remove a lock from an object


### How to read this documentation?

There are different ways to read this documentation. Depending on which information you are looking for, there are different chapters that could be interessting to you. 

Are you interested in...

* knowing how ILIAS interacts with sabreDAV? Then [SabreDAV and its interfaces](#sabredav-and-its-interfaces) is the right chapter for you
* the file structure of the WebDAV-Service? Then [classes/](#classes/) is the right chapter for you
* the different HTTP-Methods and how they are implemented for the WebDAV-Service in ILIAS? Then [HTTP-Methods](#http-methods) is the right chapter for you

## SabreDAV and its interfaces

### SabreDAV server

### Virtual filesystem

### SabreDAV locks vs. ILIAS locks

## classes/

### dav/
The dav-folder contains all the classes that are needed for the virtual filesystem.

#### ilMountPointDAV

#### ilClientDAV

#### ilObjectDAV




### auth/
The auth-folder contains all classes that are needed to authenticate the user in a webdav request.

### db/

### lock/

## HTTP-Methods

### propfind

### get

### put (create file)

### mkcol

### remove

### move and move

### lock / unlock


## Terminology

The keywords in this documentation are defined like in [RFC 4918](https://tools.ietf.org/html/rfc4918).

### WebDAV Service
This means the ILIAS implementation of the WebDAV interface

### WebDAV request
All requests on webdav.php in the ILIAS-Root are handled as WebDAV requests

### SabreDAV
Library used to handle WebDAV requests