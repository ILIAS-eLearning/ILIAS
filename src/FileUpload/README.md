# FileUpload Service

## Introduction
The file upload service is new service which filters the uploaded files before
they actually touch ILIAS. Furthermore the service acts like a tiny abstraction of the http and
filesystem service with an additional security layer which is composed of several
processors.

## Getting started
### File Upload
The file upload process consists of three main parts. The registration of the
file processors, the invocation of the processor chain and movement of the valid files.
The reason of the three step operation are the old ILIAS GUI elements.

#### Example
```php
<?php
use ILIAS\FileUpload\Location;
/**
 * @var \ILIAS\FileUpload\FileUpload $upload 
 */
$upload = $DIC->upload();
$upload->register($additionalProcessor);
$upload->process();
$upload->moveFilesTo("your/destionation", Location::STORAGE);
```
### Processors

The processors are used to filter the uploaded files and enrich the collected metadata.
An important property of the processors is that they never depend on each other. Furthermore
the processor must always behave the same way, regardless of execution sequence or used implementation
of the stream passed to the processor.

The processors are invoked in the sequence they got registered. Each processor returns an
upload status which indicates whether the file got accepted or rejected. If a processor fails
no further processors are invoked and the file gets automatically dropped to protect ILIAS
from potentially dangerous files.

The implementation of the processors must never close the given resource or detach the
underlying stream. If the stream got closed or detached the file gets automatically rejected
due to an internal error.

**ATTENTION: Processors SHOULD be used for uploads only. If there is another service which could profit 
from the concept of the Processors, you MUST discuss this in JF first. We see the risk of a too excessive 
dependence on those Processors in the system.**

#### Example
```php
<?php
use ILIAS\FileUpload\Processor\PreProcessor;
use ILIAS\FileUpload\DTO\Metadata;
use ILIAS\DI\LoggingServices;
use ILIAS\Filesystem\Stream\FileStream;
use ILIAS\FileUpload\DTO\ProcessingStatus;

class UploadLogger implements PreProcessor {
	
	private $logger;
	
	public function __construct(LoggingServices $logger) {
		$this->logger = $logger->root();
	}

	public function process(FileStream $stream, Metadata $metadata) {
		$this->logger->info("File Uploaded: " . $metadata->getFilename());
		return new ProcessingStatus(ProcessingStatus::OK, 'logging successful');
	}
}
```

The processor can be registered with the register method afterwards.
```php
<?php
/**
 * @var \ILIAS\FileUpload\FileUpload $upload 
 */
$upload = $DIC->upload();
$upload->register($additionalProcessor);
```
### Get the Results
The final upload results can be fetch with the getResults method which will return an
UploadResult array. The result it self is immutable because the result at this point of
execution is considered final.

#### Example
```php
<?php
/**
 * @var \ILIAS\FileUpload\FileUpload $upload 
 */
$upload = $DIC->upload();

// ... process and move files

$results = $upload->getResults();
$result = $results[0];
```

## Terminology

### UploadResult
The purpose of the UploadResult object is to transport data, namely the attributes
(name, size and mimeType), metadata and upload-status of a file. The attributes are
fetched from the uploaded file which is handed over by a ServerRequest. The metadata
can be enriched by the processors, which also set the upload status to accepted or
rejected.


## Known issues
The http service has methods which would allow ILIAS to handle the files in a way which bypasses
the file upload service.



