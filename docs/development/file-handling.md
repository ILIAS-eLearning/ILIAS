File Handling in ILIAS
======================

Many components and features in ILIAS work with files uploaded by users. Over the years, more and more services have been developed in ILIAS that support the processing, storage and delivery of files and take over central workflows. This how-to summarises which services are used for which use cases and which services are deprecated and should no longer be used.

The aim of the how-to is to have a view in ILIAs that accepts a file via a form, displays it and makes it available for download.

In general, the following services are used in ILIAS to handle files:
- FileUpload-Service: See [FileUpload](../../src/FileUpload/README.md)
- Filesystem-Service: See [Filesystem](../../src/Filesystem/README.md)
- ResourceStorage-Service: See [ResourceStorage](../../src/ResourceStorage/README.md)

Old implementations like the following should no longer be used:
- ilObjFile (as a Service)
- Services/FileSystem
- Services/FileServices/classes/class.ilFileUtils.php
- Direct use of PHP functions for processing uploads (such as move_uploaded_file) or for reading and writing files (file_get_contents, fputs, ...)
- Other implementations not listed above

# How to handle files in ILIAS (Preferred way)
For the following How-To, the ZIP available [here](code-examples/FileHandlingDemo.zip) can be downloaded, unzipped and placed in `./Services`. Then run a `composer dump-autoload`. Afterwards, the demo service can be reached via http://{YOUR_ILIAS_DEVELOPMENT_INSTANCE}/ilias.php?baseClass=ilfilehandlingdemogui.

The sample code is not exhaustive or does not address guidelines.  For example, there is no permissions check, no UI components are used (except in the form), etc. It is only intended to provide a few approaches on how to work with files yourself.

We implement a method in the GUI that represents a form with a file input. File inputs use a so-called UploadHandler, more about this in a moment.

```php
    protected function buildForm(): Standard
    {
        // Create Upload-Form
        $upload_handler = new ilFileHandlingDemoUploadHandlerGUI();
        return $this->ui->factory()->input()->container()->form()->standard(
            $this->ctrl->getLinkTarget($this, self::CMD_CREATE),
            [
                self::F_FILE => $this->ui->factory()->input()->field()->file(
                    $upload_handler,
                    'File',
                    'Upload a File'
                )
            ]
        );
    }

    private function add(): void
    {
        $form = $this->buildForm();
        $this->ui->mainTemplate()->setContent(
            $this->ui->renderer()->render($form)
        );
    }
```
A detailed description of an UploadHandler can be found here: [Services/FileServices/README.md](../../Services/FileServices/README.md). 

In short, the `UploadHandler` takes care of receiving a users upload and processing it or passing it on to a service for storing the upload. In our example, we want to pass the upload to the Resource Storage Service and thus receive a `ResourceIdentification` that we can use to call up the file later. The storage of uploads in the Resource Storage Service is practically obligatory, as in the future all uploads are to be stored in this service.

We can simply extend an abstract class for this, but two things have to be implemented:

- The class must be accessible via our GUI class with ilCtrl: 
```php
/**
 * @author            Fabian Schmid <fabian@sr.solutions>
 * @ilCtrl_IsCalledBy ilFileHandlingDemoUploadHandlerGUI: ilFileHandlingDemoGUI
 */
```
More information about ilCtrl: [Services/UICore/README.md](../../Services/UICore/README.md)

- We need to announce a `Stakeholder` to identify which component or UseCase the uploaded files "belong" to:
```php
    protected function getStakeholder(): ResourceStakeholder
    {
        return new ilFileHandlingDemoStakeholder();
    }
```
More information about stakeholders: [Services/ResourceStorage/README.md](../../src/ResourceStorage/README.md)

After submitting the form, we receive the processed resource IDs already stored in the IRSS as a string:

```php
private function create(): void
    {
        $form = $this->buildForm();
        $form = $form->withRequest($this->http->request());
        $data = $form->getData()[self::F_FILE] ?? [];

        // At this place we want to store the resourse identification string (RID) in the database and use it later to retrieve the file.
        // Since this is just a demo, we just print the RIS to the screen and make a download button which has the RID as e parameter.

        $resource_id = $data[0] ?? '';
        $this->ctrl->setParameter($this, self::P_RID, $resource_id);

        $download_button = $this->ui->factory()->button()->primary(
            'Show File',
            $this->ctrl->getLinkTarget($this, self::CMD_SHOW)
        );

        $this->ui->mainTemplate()->setContent(
            '<pre>' . print_r($data, true) . '</pre>'
            . $this->ui->renderer()->render($download_button)
        );
    }
```
We can now do various things with this resource, such as downloading:

```php
    private function download(): void
    {
        $rid = $this->ridFromRequest();

        $this->irss->consume()->download($rid)->run();
    }
```

display some information about the file (in the real world, of course, UI components would be used for this):

```php
private function show(): void
    {
        // Retrieve the RID from the URL
        $rid = $this->ridFromRequest();

        $some_infos_of_the_resource_to_show = $this->ridToArray($rid);

        // Buttons
        $this->ctrl->setParameter($this, self::P_RID, $rid->serialize());

        $buttons = [];

        $buttons['add_to_collection'] = $this->ui->factory()->button()->primary(
            'Add File to Collection',
            $this->ctrl->getLinkTarget($this, self::CMD_ADD_TO_COLLECTION)
        );

        $buttons['delete'] = $this->ui->factory()->button()->standard(
            'Delete File',
            $this->ctrl->getLinkTarget($this, self::CMD_DELETE)
        );

        $buttons['download'] = $this->ui->factory()->button()->standard(
            'Download File',
            $this->ctrl->getLinkTarget($this, self::CMD_DOWNLOAD)
        );

        // If file is a ZIP, we can show the content of the ZIP with the following button
        $current_revision = $this->ridToCurrentRevision($rid);
        if ($current_revision->getInformation()->getMimeType() === 'application/zip'
            || $current_revision->getInformation()->getMimeType() === 'application/x-zip-compressed'
        ) {
            $buttons['show_zip'] = $this->ui->factory()->button()->standard(
                'Show ZIP Content',
                $this->ctrl->getLinkTarget($this, self::CMD_SHOW_ZIP)
            );
        }

        $this->ui->mainTemplate()->setContent(
            '<pre>' . print_r($some_infos_of_the_resource_to_show, true) . '</pre>'
            . $this->ui->renderer()->render($buttons)
        );
    }
```

or (if the file is a ZIP) view the contents of the ZIP:

```php
    private function showZip(): void
    {
        $rid = $this->ridFromRequest();
        $stream = $this->irss->consume()->stream($rid)->getStream();
        $unzip = $this->archives->unzip($stream);
        $content = iterator_to_array($unzip->getPaths());

        $back = $this->ui->factory()->button()->standard(
            'Back',
            $this->ctrl->getLinkTarget($this, self::CMD_ADD)
        );

        $this->ui->mainTemplate()->setContent(
            '<pre>' . print_r($content, true) . '</pre>'
            . $this->ui->renderer()->render($back)
        );
    }
```

or delete the resource again:

```php
    private function delete(): void
    {
        $rid = $this->ridFromRequest();

        // Delete the Resource
        $this->irss->manage()->remove($rid, new ilFileHandlingDemoStakeholder());
        $this->ui->mainTemplate()->setOnScreenMessage('success', 'File deleted', true);
        $this->ctrl->redirect($this, self::CMD_ADD);
    }
```

or add it to a so-called collection (more about collections can be found here):

```php
    private function addToCollection(): void
    {
        $rid = $this->ridFromRequest();

        $collection = $this->getCollection();
        $collection->add($rid);
        $return = $this->irss->collection()->store($collection);

        $this->ui->mainTemplate()->setOnScreenMessage('success', 'File added to Collection');

        $show = $this->ui->factory()->button()->standard(
            'Show Collection',
            $this->ctrl->getLinkTarget($this, self::CMD_INDEX)
        );

        $this->ui->mainTemplate()->setContent(
            $this->ui->renderer()->render([$show])
        );
    }
```
If the file is a PDF, we can use the so-called flavours to create a preview of it, for example:
```php
    private function showPDF(): void
    {
        $rid = $this->ridFromRequest();

        $flavour = $this->irss->flavours()->get(
            $rid,
            new PagesToExtract(false, 400, 10, false)
        );

        $images = array_map(function (string $url): string {
            return '<img src="' . $url . '" />';
        }, $this->irss->consume()->flavourUrls($flavour)->getURLsAsArray());

        $this->ctrl->saveParameter($this, self::P_RID);
        $back = $this->ui->factory()->button()->standard(
            'Back',
            $this->ctrl->getLinkTarget($this, self::CMD_SHOW)
        );

        $this->ui->mainTemplate()->setContent(
            $this->ui->renderer()->render($back) . '<br>'
            . implode(' ', $images)
        );
    }
```

# Zip and Unzip

The following example shows how to create a ZIP file from a list of files and how to unzip a ZIP file.

The legacy varaints should no longer be used because they always presuppose that one works directly with directories and files.


```php

    private function zip(): void
    {
        global $DIC;
        // Collect Streams to the files you want to ZIP
        $streams = [];
        foreach ($this->getCollection()->getResourceIdentifications() as $resource_identification) {
            $revision = $this->irss->manage()->getCurrentRevision($resource_identification);
            $streams[$revision->getInformation()->getTitle()] = $this->irss->consume()->stream(
                $resource_identification
            )->getStream();
        }

        $zip_stream = $DIC->archives()->zip($streams)->get();

        $delivery = new Delivery($zip_stream, $this->http);
        $delivery->deliver();
    }

    private function legacyZip(): void
    {
        global $DIC;
        $directory_to_zip = CLIENT_DATA_DIR . '/ilExercise/3/exc_322/feedb_1/0';
        $output_zip = CLIENT_DATA_DIR . '/temp/MyZip.zip';

        $DIC->legacyArchives()->zip($directory_to_zip, $output_zip);

        $delivery = new Delivery($output_zip, $this->http);
        $delivery->deliver();
    }

    public function unzip(): void
    {
        global $DIC;
        $zip_stream = Streams::ofResource(fopen(CLIENT_DATA_DIR . '/temp/MyZip.zip', 'r'));
        $unzip = $DIC->archives()->unzip($zip_stream);

        $amount_of_files = $unzip->getAmountOfFiles();
        $amout_of_directories = $unzip->getAmountOfDirectories();

        foreach ($unzip->getStreams() as $stream) {
            // Do something with the stream
        }
    }

    public function legacyUnzip(): void
    {
        global $DIC;
        $DIC->legacyArchives()->unzip('unzip_test_file.zip', CLIENT_DATA_DIR . "/temp/unzip_test");
    }

```


# Contribute
If there is anything wrong or missing here, or if there are any uncertainties, please open a ticket in Mantis (https://mantis.ilias.de). Errors or missing content in the documentation are treated as bugs.
If someone wants to write this example in "pretty", i.e. use UI components etc., I'm happy about a pull request on Github!

