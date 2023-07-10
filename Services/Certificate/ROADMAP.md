# Roadmap

Certificates are used by different Services/Modules to
symbolize the successful completion of the Service/Module.

This file aims to serve a basic overview of the planned and
already implemented Features.
This file should be updated regulary to avoid collision with
other development processes and give anybody a fair overview
of the planned features of this service.

## Versions

### v1.0.0 (ILIAS < 5.4)

* Certificate templates can be created via file
  in the filesystem
* Certificates can be downloaded by users(based on
  the certificate template)

### v2.0.0 (ILIAS 5.4)

* Persist templates and certificates in the database
* Visioning of templates and certificates
* Refactoring and extracting of classes
* Add Unittests
* Eliminate the need for Certificate adapters
* User Interface with list of achieved certificates
* Elimination of unused methods and code
* Alternative concept to queuing and process via
  cron job to avoid idle times

### v2.1.0 (ILIAS 6)

* Abandon certificate migration according to https://docu.ilias.de/goto.php?target=wiki_1357_Persisting_Certificates

### v2.2.0 (ILIAS 7)

* Increase peformance when learners complete a learning object and course certificates are evaluated

### v2.3.0 (ILIAS 8)

* Increase peformance even more when learners complete a learning object and course certificates are evaluated

## Mid Term
* Use a local dependency injection container instead of the `Huge Constructor with Defaults` pattern where applicable
* Invert dependencies to support (new) object types and even repository object plugins without changing
  certificate related code: Use interfaces and ILIAS artifacts instead
* Add an administration user interface to manage user certificates (issue, delete) or even certificate templates globally
* Increase performance in `\ilCertificateAppEventListener::handleLPUpdate` if course certificate trigger objects are
  referenced
```php
// Instead of iterating over references (like shown below), we should try to achieve O(1) by evaluating a collection of ref_ids
foreach (ilObject::_getAllReferences($objectId) as $refId) {
    $templatesOfCompletedCourses = $progressEvaluation->evaluate($refId, $userId);
}
// ...
```
