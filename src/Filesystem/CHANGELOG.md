# Change Log
All notable changes to this service will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/)
and this service adheres to [Semantic Versioning](http://semver.org/).

## [0.0.5]
## Added
* MetadataType interface
* FileStream interface
* hasDir method introduced within the DirectoryAccess interfaces
* DataSize string conversion

## Changed
* DirectoryAccess split into read and write interfaces
* FileAccess split into read and write interfaces
* FileStreamAccess split into read and write interfaces
* FileStreamAccess now uses the new FileStream
* FileSize renamed to DataSize and moved to the Data namespace.

## Removed
* DataSize getSuffix method removed

## Fixed
* Metadata documentation fixed.
* Various interfaces were wrongly described as class within the doc comment.
* first part of the README adjusted

## [0.0.4]
## Added
* Filesystem factories

## Changed
* IllegalArgumentException changed to the native InvalidArgumentException

## [0.0.3]
## Changed
* README updated
* Filesystem interface split to make the code more versatile

## [0.0.2]
## Added
* copyDir method added to filesystem interface

### Changed
* Code is now PHP 5.6 compatible

## [0.0.1]
### Added
* Filesystem interface created
* Visibility interface created to make the usage of the filesystem rights more readable.
* Exception created
  * DirectoryNotFoundException
  * FileAlreadyFoundException
  * FileNotFoundException
  * IllegalArgumentException
  * IOException
* Metadata class added
* FileSize class added
* README added
* CHANGELOG added
