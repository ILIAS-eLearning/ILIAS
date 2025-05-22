Web Access Checker
==================

The WebAccess Checker has been completely revised in ILIAS 5.1 and takes care of 
securing files in the public directory /data. The switch to IRSS (ILIAS Resource 
Storage Service) makes the Web Access Checker obsolete, since the IRSS already 
includes the secure delivery of files.
For the secure delivery of files with the IRSS, use the src consumer, for example.

Further information
- [IRSS](../ResourceStorage/README.md#consumer)
- [FileDelivery](../FileDelivery/README.md#signed-delivery)

# ROADMAP

As soons as no longer Files are stored in the /data Directory, the 
WebAccessChecker gets removed from the ILIAS Core.
