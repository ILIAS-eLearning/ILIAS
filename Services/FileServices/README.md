Uploading Files in ILIAS
========================

# Ways to upload files
## \ILIAS\UI\Component\Input\Field\File
In most cases, files are uploaded via a form. This may only be done with the new forms from the UI service. For this purpose a new form field `File` can be added to its form.

General Information for the Inputs you find in [src/UI/Component/Input/README.md](../../src/UI/Component/Input/README.md)

```php
global $DIC;
$f = $DIC->ui()->factory();

$handler = new ilMyOwnUploadHandlerGUI();

$f->input()->field()->file(
    $handler,
    'File Lable'
);

``` 
A file input requires an UploadHandler. The UploadHandler gives the field the information, where e.g. files are sent, where information about existing files can be requested or how files can be removed again:

### UploadHandler
Basically you can implement the interface `UploadHandler` anywhere, for example in the current GUI class where you use the form or dropzone. To keep classes small, it is worthwhile to use a separate class for an `UploadHandler`.

The simplest variant is to address your `UploadHandler` via ilCtrl. For this there is already a base class that takes care of the whole link generation (e.g. upload link): `AbstractCtrlAwareUploadHandler`.

```php
abstract protected function getUploadResult() : HandlerResult;
abstract protected function getRemoveResult(string $identifier) : HandlerResult;
abstract public function getInfoResult(string $identifier) : ?FileInfoResult;
abstract public function getInfoForExistingFiles(array $file_ids) : array;
```
Of course, your UploadHandler must then be accessible via ilCtrl, so it needs corresponding statements in the class (this is only example, you must define a ilCtrl-way which fits your location, see [Services/UICore/ilctrl.md](../../Services/UICore/ilctrl.md)):

```php
/**
 * @ilCtrl_isCalledBy ilMyOwnUploadHandlerGUI: ilRepositoryGUI, ilDashboardGUI
 */
class ilMyOwnUploadHandlerGUI extends AbstractCtrlAwareUploadHandler
{ 
```

#### getUploadResult
This is the main method in which the files uploaded by the File-Input or Dropzone must be processed. The method is called for each file individually. The uploads are stored in the storage service, for example, or processed in some other way. 

General Information for the Upload-Service you find in [src/FileUpload/README.md](../../src/FileUpload/README.md) 

An example would be:

```php
    protected function getUploadResult() : HandlerResult
    {
        $this->upload->process(); 
        $result_array = $this->upload->getResults();
        $result = end($result_array);
        
        if ($result instanceof UploadResult && $result->isOK()) {
            $identifier = (string) $this->file->appendUpload($result, $this->file->getTitle());
            $status = HandlerResult::STATUS_OK;

            return new BasicHandlerResult(
                $this->getFileIdentifierParameterName(),
                HandlerResult::STATUS_OK,
                $identifier,
                "file upload OK"
            );
        } else {
            return new BasicHandlerResult(
                '',
                HandlerResult::STATUS_FAILED,
                $identifier,
                $result->getStatus()->getMessage()
            );
        }
    }
```
The `$identifier` in a BasicHandlerResult is the value that is then returned as a string value in the form processing after the form is submitted (e. g. `$form->getData();`, see documentation for `\ILIAS\UI\Component\Input\Container\Form\Form`).

#### getRemoveResult
To remove files that a user has dropped into a file input (and thus have already been processed), he clicks on the (X) in the form field. An asynchronous request is sent to the delete URL, and the identifier of the desired file is sent as a parameter. This is received as a parameter in the method. The method is responsible for deleting this file. In the case of the ResourceStorageService this can be done very simply as follows:

```php
    protected function getRemoveResult(string $identifier) : HandlerResult
    {
        if (null !== ($id = $this->storage->manage()->find($identifier))) {
            $this->storage->manage()->remove($id, $this->stakeholder);
            $status = HandlerResult::STATUS_OK;
            $message = "file removal OK";
        } else {
            $status = HandlerResult::STATUS_OK;
            $message = "file with identifier '$identifier' doesn't exist, nothing to do.";
        }

        return new BasicHandlerResult(
            $this->getFileIdentifierParameterName(), 
            $status, 
            $identifier, 
            $message
        );
    } 
```
If you do not use the storage service, you are responsible for the deletion.

#### getInfoResult
Here a file input or dropzone can request information about an existing file, e.g. if the form field with `->withValue()` already contains a file. Example with the ResourceStorageService:

```php
    public function getInfoResult(string $identifier) : ?FileInfoResult
    {
        if (null !== ($id = $this->storage->manage()->find($identifier))) {
            $revision = $this->storage->manage()->getCurrentRevision($id)->getInformation();
            $title = $revision->getTitle();
            $size = $revision->getSize();
            $mime = $revision->getMimeType();
        } else {
            $title = $mime = 'unknown';
            $size = 0;
        }

        return new BasicFileInfoResult(
            $this->getFileIdentifierParameterName(), 
            $identifier, 
            $title, 
            $size, 
            $mime
        );
    } 
```

#### getInfoForExistingFiles
This method is analogous to getInfoResult, but for an array of existing identifiers.

```php
    public function getInfoForExistingFiles(array $file_ids) : array
    {
        $info_results = [];
        foreach ($file_ids as $identifier) {
            $info_results[] = $this->getInfoResult($identifier);
        }

        return $info_results;
    }
```

## \ILIAS\UI\Component\Dropzone\File\File
The second variant of how to upload files is via Dropzones. This works analogous to the inputs and a dropzone also uses an `UploadHandler`:

```php
global $DIC;
$f = $DIC->ui()->factory();

$handler = new ilMyOwnUploadHandlerGUI();

$f->dropzone()->file()->standard(
    $handler,
    'url where to send the whole form after'
);
``` 
