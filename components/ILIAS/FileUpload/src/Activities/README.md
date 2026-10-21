# Files in Activities

How an activity takes a file, and why it takes it the way it does.

## The starting point

An [Activity](../../../Component/src/Activities/README.md) describes its input as a `FormInput` of
the UI framework, so that the same description serves a form, a webservice and a workflow tool. For
a file that promise does not hold today, and the two fields below are the consequence, not a
preference. This chapter says exactly why, because it is the first question anybody asks.

### What `Field\File` is

Forms never carry the bytes of a file. `Field\File` takes an `UploadHandler`, the browser sends the
file to `$handler->getUploadURL()`, and what ends up in the form is an *identifier* of whatever that
endpoint made of the upload - in the ILIAS handlers the serialised `rid` of a resource:

```php
// AbstractCtrlAwareIRSSUploadHandler::getUploadResult()
$identifier = $this->irss->manage()->upload($result, $this->stakeholder)->serialize();
```

Five things follow from how that is built. They are worth naming one by one, because together they
say something different than "the upload happens asynchronously":

1. **The field has no file element at all.** `UI/src/templates/default/Input/tpl.file.html` contains
   no `<input type="file">`, only a dropzone container and a `<template>`. The element is created by
   JavaScript. Without JavaScript the field cannot be used - not "used with less comfort".
2. **Its value is a list of strings in hidden inputs.** `File::createDynamicInputsTemplate()` builds
   `$field_factory->hidden()`, and `File::isClientSideValueOk()` accepts nothing but arrays of
   strings.
3. **The input layer never reads an uploaded file.** There is no `$_FILES` and no
   `getUploadedFiles()` anywhere under `UI/src/Implementation/Component/Input/`.
4. **The declared limits are hints for the browser, not validation.**
   `Renderer::initClientsideFileInput()` passes `getMaxFiles()`, `getMaxFileSize()` and
   `getAcceptedMimeTypes()` into the JavaScript call and into a help block. Server side the field
   checks that the value is an array of strings, and with `withRequired` that it is not empty -
   nothing else. The real checks live in the upload endpoint, where
   `AbstractCtrlAwareIRSSUploadHandler::getUploadResult()` runs `$upload->process()` with its
   preprocessors and its blacklist.
5. **The handler is bound to `ilCtrl`.** All three of its URLs come from
   `ilCtrl::getLinkTargetByClass()`, and the chunking protocol is the one of the JavaScript library
   (`dzuuid`, `dzchunkindex`, `dztotalchunkcount`, read in
   `AbstractCtrlAwareUploadHandler::readChunkedInformation()`).

So `Field\File` describes a *widget*, not an input. Everything that makes an upload an upload -
receiving the bytes, checking them, naming them - sits outside the input, in an endpoint that only a
GUI can address. That single fact explains three symptoms at once: a client without JavaScript
cannot use the field, removing a file again is fragile because it is a second endpoint the form does
not control, and a caller over REST or SOAP cannot satisfy the description at all.

## The second reason: an endpoint of our own would be worse

The section above says an activity *cannot* use that field. This one says that copying its model -
adding an upload endpoint of our own, per transport - would be the wrong answer anyway.

An upload endpoint accepts files without knowing what they are for. Every call creates something,
and nothing forces a second call to ever use it. In a GUI that is bounded by the form the user is
looking at. Over a webservice it is not: anyone with a session can fill the storage, and afterwards
nobody can tell an abandoned upload from one that is still waited for.

Therefore **there is no upload endpoint, and no upload activity**. The file travels inside the call
that uses it. `CreateFile` either creates the object with its content or leaves nothing behind, and
the permission is checked before a single byte is stored.

What that costs: a file has to fit into one request - the limits of `post_max_size` and of the
memory apply - and there is neither chunking nor a resumable upload. For very large files this is
the wrong door; that case would need an endpoint bound to a concrete target, not a free store.

## The wire format

The content is **base64** in a plain parameter:

* JSON has no type for bytes, so a REST client has to encode anyway.
* SOAP encodes binary content the same way (`xsd:base64Binary`).
* Anything that can carry text can carry this, and there is nothing to negotiate.

The REST service adds the convenience an HTTP client expects and encodes a `multipart/form-data`
part before the activity sees it, so this works as well:

```bash
curl -b cookies -F 'content=@notes.pdf' -F 'parent_ref_id=1' http://.../rest/file/create
```

Delivery is the mirror image: base64 in the answer, raw bytes with `?raw=1`.

## How an activity takes a file

`FileParameter` describes the parameters and reads them, `FileContent` is what an activity works
with. Both are provided by the FileUpload component; an activity gets the parameter injected.

| Parameter | Meaning |
| --- | --- |
| `filename` | name of the file including its suffix |
| `content` | the content of the file, base64 encoded |

```php
public function __construct(
    private readonly FileParameter $file,
    private readonly RepositoryProvider $repository,
) {
}

public function getInputDescription(FieldFactory $f): FormInput
{
    return $f->section([
        'parent_ref_id' => $f->numeric('Target', '...')->withRequired(true)->withDedicatedName('parent_ref_id'),
        ...$this->file->describe($f, 'File', 'The content of the new file'),
    ], 'Create a file');
}

public function maybePerformAs(InputFactory $input_factory, int $usr_id, array $raw_parameters): Result
{
    // ... convert the other parameters, then:
    if (!$this->isAllowedToPerform($usr_id, $parameters)) {
        return $this->data->error(new \DomainException('You are not allowed to create files here.', 403));
    }

    // only now, and never earlier
    $file = $this->file->read($raw_parameters);

    return $this->data->ok($this->perform([...$parameters, 'file' => $file]));
}

public function perform(mixed $parameters): FileEntry
{
    $file = $parameters['file'];

    try {
        return $this->repository->createFile(..., $file);
    } finally {
        $file->release();
    }
}
```

Three rules, and they are the whole contract:

1. **Read the file after the permission check.** A caller who may not put something there does not
   get to put bytes into the installation either.
2. **Release it, always.** `perform()` wraps its work in `try`/`finally`; the content is scratch,
   not a result.
3. **Never hand out a handle.** Nothing that addresses the content leaves the activity, so nothing
   outside can refer to it later.

### Limits

An activity can say what it accepts, the way the file field of the UI framework does. A
`FileParameter` is immutable, so the narrowed one has to be kept - usually right where it is
injected:

```php
public function __construct(FileParameter $file, private readonly RepositoryProvider $repository)
{
    $this->file = $file
        ->withMaxFileSize(10 * 1024 * 1024)
        ->withAcceptedMimeTypes(['application/pdf']);
}
```

Both end up in the description of the parameter - and therefore in the generated documentation - and
are enforced when it is read. A file that is too large is refused before it is decoded, a file of an
unwanted type is released again.

On top of what an activity declares, a file read this way gets what every upload gets:

- the **upload limit of the installation** (`GlobalUploadLimit`, which may depend on the user) - the
  smaller of both limits applies;
- the **preprocessors** other components contribute to FileUpload (`$seek[PreProcessor::class]`) -
  the virus scanner, the checks for insecure file names and for SVGs carrying script among them. A
  file they reject or deny is released again and the call fails with 400. The reason stays inside,
  as in the GUI: a processor that failed reports the message of its exception, which may name paths
  of the server.

Without them a webservice would be the door around every check an upload through the GUI passes.
The blacklist of suffixes needs nothing of ours: the ResourceStorage applies it when the content is
stored.

### Two files in one activity

`withNames()` gives the pair of parameters different names, so an activity can take more than one
file:

```php
$attachment = $this->file->withNames('attachment', 'attachment_name');
```

## Why the content still ends up in a file

A stream over a string has no path, and both the ResourceStorage and `ilObjFile` take the name of
what they store from the file behind the stream. So `FileParameter::read()` writes the content into
the temp filesystem (`TempFileStore`) and `FileContent` reads its stream from there.

That store is scratch space for the duration of the request. Its handle stays inside the activity,
and `release()` removes the file and its folder again.

Only a request that dies on the way - the memory limit, a timeout - leaves its folder behind under
`activities/` in the temp filesystem, since no `finally` runs then. Those leftovers are removed by
the cron job *Clean temp directory* (`ilFileSystemCleanTempDirCron`, everything older than ten
days), which is not active by default and should be on an installation that offers activities over
a webservice.

## What this is deliberately not

* **Not a `Field\File`.** That field presupposes the separate endpoint this design does without, and
  its handler would put a `ilCtrl` link into a domain object. Using it here would also *weaken* the
  activity rather than strengthen it: as shown above the field validates neither size nor type
  server side, so an activity would have to check both itself anyway - which is what
  `FileParameter` does. The price of not using it is that the input description here is two ordinary
  fields.
* **Not a `rid` parameter.** A resource identification is not a capability, and a client that could
  name one would be naming something it does not own. See
  `ILIAS\ResourceStorage\Activities\DeliverResource` for the two doors that follow from this.

## What would have to change upstream

`FileParameter` is not a second file field. It plays the role of the **handler** - take the bytes,
check them, name them - and borrows two plain fields to describe itself, because the framework has
no field for content that arrives *with* the call.

The fix is not "build the asynchronous upload differently". It is to give `Field\File` a defined
server-side value, in two shapes:

* **Base**: the bytes come with the request as `multipart/form-data`. The input takes them from the
  PSR-7 request, checks amount, size and type itself, and its value is a file object.
* **Enhanced**: JavaScript uploads them beforehand, the value is a list of identifiers, and the
  input resolves them through the handler into the same file object.

Then a form works without JavaScript, a webservice works at all - the base shape is exactly the case
described here - chunking stays what it is, an enhancement on top of the base, and the limits are
enforced in one place instead of three. Two things have to move for that: `InputData` and
`PostDataFromServerRequest` must carry uploaded files, and `UploadHandler` has to lose its `ilCtrl`
coupling so that the endpoint can be named per channel.

That is a change to the UI framework with a migration for every user of `$f->file()`, so it is not
done on the side. Until it happens, `FileParameter` is the transport-side counterpart of an
`UploadHandler`, and these two fields are what an activity can honestly promise.
