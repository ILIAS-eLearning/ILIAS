# How to Migrate a ILIAS-Component to the ResourceStorage-Service (IRSS)

## Introduction

With ILIAS 7 the possibility of migrations was introduced in the setup. Among other things, these are used to transfer
files from old structures to the ILIAS Resource Storage Service (IRSS).

Information about migrations in general can be found at [setup/README](../../setup/README.md).

## General approach

Many components store uploaded files from users. These files usually belong to something, let's call this a "thing". A
forum post is a thing to which files can be saved. Or an exercise unit is a "thing" to which instruction files can be
uploaded. A migration is best targeted at such an isolated use case. It is easier to migrate this use case individually
and isolated than all use cases within a component.

To migrate a use case, the following must be considered:

- The migration that moves the files from the slte location to the IRSS.
- The "thing" has to store itself the Resourceidentification or ResourceCollectionIdentification new
- The location where new files can be uploaded for this use case must be rewritten so that these files end up directly
  in the IRSS.
- The location that obtains or wants to access uploaded files (e.g. download function) must reobtain the file from IRSS
  via Consumers.
- If a component or use case has not yet been migrated or has only been partially migrated, it must still be possible to
  access the old files. However, it should not be possible to make any changes to the old files until they have been
  migrated.

## Migration of a use case

Using an example, we will look at the steps required for a refactoring:
We take as "thing" the files that can be honcgeladen in a forum post. The initial situation here looks like this:

The files of a forum post end up in a directory in the ILIAS data directory (e.g. /var/iliasdata/...) in a
subdirectory "forum" so far. The files are created based on the object ID (e.g. 307) and the posting ID (e.g. 8) (in the
file name), e.g. "307_8_My-Filename.png".

Postings are stored in the "frm_posts" table. This table currently has no direct reference to the files, but this is
done according to the scheme described above in the file name.

### Writing the Migration

First, we introduce a new column in the "frm_posts" table, where we will store the `ResourceCollectionIdentification`
during the migration. `ResourceIdentifications` and ResourceCollectionIdentifications can be stored with a 64-character
text field.

Since multiple files can be uploaded per post and not a single file, we use `ResourceCollectionIdentification` and
not `ResourceIdentifications`. Infos to collections see [here](../../src/ResourceStorage/README.md).

We will introduce a corresponding field in a DB update (e.g. in the existing `ilForumDatabaseUpdateSteps`:

```php
public function step_3(): void
{
    if (!$this->db->tableColumnExists('frm_posts', 'rcid')) {
        $this->db->addTableColumn(
            'frm_posts',
            'rcid',
            [
                'type' => 'text',
                'notnull' => false,
                'length' => 64,
                'default' => ''
            ]
        );
    }
}
```

The actual migration consists is structured as described in [setup/README](../../setup/README.md). The following two
methods are the most relevant for the migration:

- public function getPreconditions(Environment $environment): array
- public function prepare(Environment $environment): void;
- public function getRemainingAmountOfSteps(): int;
- public function step(Environment $environment): void;

To ensure we have an updated Database with our new column, we add the following precondition (which has all needed
precondations for migrations):

```php
public function getPreconditions(Environment $environment): array
{
    return \ilResourceStorageMigrationHelper::getPreconditions();
}
```

To start, we write a new class and implement the interface `Migration`. In the `prepare` method we can use - for the
IRSS migrations - a helper class that provides us with the necessary dependencies and also methods for support (we see
more of this helper later in this tutorial):

```php
public function prepare(Environment $environment): void
{
    $this->helper = new \ilResourceStorageMigrationHelper(
        new \ilForumPostingFileStakeholder(),
        $environment
    );
}
```

What we need for this is a `ResourceStakeholder` for our use case. For more information about Stakeholders
see [here](../../src/ResourceStorage/README.md).

The simplest way is to use getRemainingAmountOfSteps to query the database to find out how many "things" still need to
be migrated for this use case.

We use our new `rcid` column in the "frm_posts" table for this purpose:

```php
public function getRemainingAmountOfSteps(): int
{
    $r = $this->helper->getDatabase()->query(
        "SELECT count(frm_posts.pos_pk) AS amount FROM frm_posts WHERE frm_posts.rcid IS NULL OR frm_posts.rcid = '';"
    );
    $d = $this->helper->getDatabase()->fetchObject($r);

    return (int)$d->amount;
}
```

The last thing we do is implement the `step()` method, which migrates one "thing" at a time. The helper mentioned above
can be used for this, which currently offers the following three types:

- moveFilesOfPathToCollection: Moves all files in a directory to a collection and returns
  its `ResourceCollectionIdentification`.
- moveFilesOfPatternToCollection: Moves all files that match a pattern to a collection and returns
  its `ResourceCollectionIdentification`.
- movePathToStorage: Moved one single file to the IRSS and returns its `ResourceIdentification`.

In our example, we use the `moveFilesOfPatternToCollection` method to move all files that match the pattern to a
collection and return the `ResourceCollectionIdentification`:

```php
public function step(Environment $environment): void
{
    $db = $this->helper->getDatabase();

    $r = $this->helper->getDatabase()->query(
        "SELECT
frm_posts.pos_pk AS posting_id,
frm_posts.pos_author_id AS owner_id,
frm_data.top_frm_fk AS object_id
FROM frm_posts
JOIN frm_data ON frm_posts.pos_top_fk = frm_data.top_pk
WHERE frm_posts.rcid IS NULL OR frm_posts.rcid = ''
LIMIT 1;"
    );

    $d = $this->helper->getDatabase()->fetchObject($r);
    $posting_id = (int)$d->posting_id;
    $object_id = (int)$d->object_id;
    $resource_owner_id = (int)$d->owner_id;

    $base_path = $this->buildBasePath();
    $pattern = '/.*\/' . $object_id . '\_' . $posting_id . '\_(.*)/m';

    $collection_id = $this->helper->moveFilesOfPatternToCollection(
        $base_path,
        $pattern,
        $resource_owner_id
    );

    $save_colletion_id = $collection_id === null ? '-' : $collection_id->serialize();
    $this->helper->getDatabase()->update(
        'frm_posts',
        ['rcid' => ['text', $save_colletion_id]],
        ['pos_pk' => ['integer', $posting_id],]
    );
}
```

Since each resource should also have an owner, we read the respective object owner of a forum and pass this as the
resource owner. The `ResourceCollectionIdentification` is then saved in the database.

The methods of the helper also have two callbacks in which, for example, the title of a revision or the file name can be
adjusted. These are used in the effective implementation of the migration of the forum files, but are omitted here for
the sake of simplicity.

### Adopting Upload-Locations

These are only examples and do not show the complete implementation. In the case of the forum files, the previously
used `ilFileDataForum` was completely abstracted in order not to have to adapt the using code. Uploading new files
previously worked as follows:

```php 
// PLEASE do not reuse this code, it is only an legacy example
if (isset($files['name']) && is_array($files['name'])) {
    foreach ($files['name'] as $index => $name) {
        $name = rtrim($name, '/');
        $filename = ilFileUtils::_sanitizeFilemame($name);
        $temp_name = $files['tmp_name'][$index];
        $error = $files['error'][$index];

        if ($filename !== '' && $temp_name !== '' && (int) $error === 0) {
            $path = $this->getForumPath() . '/' . $this->obj_id . '_' . $this->pos_id . '_' . $filename;

            $this->rotateFiles($path);
            ilFileUtils::moveUploadedFile($temp_name, $filename, $path);
        }
    }

    return true;
}

if (isset($files['name']) && is_string($files['name'])) {
    $files['name'] = rtrim($files['name'], '/');
    $filename = ilFileUtils::_sanitizeFilemame($files['name']);
    $temp_name = $files['tmp_name'];

    $path = $this->getForumPath() . '/' . $this->obj_id . '_' . $this->pos_id . '_' . $filename;

    $this->rotateFiles($path);
    ilFileUtils::moveUploadedFile($temp_name, $filename, $path);

    return true;
}

return false;
```

This could now be massively simplified through the use of the IRSS:

```php
if (!$this->upload->hasBeenProcessed()) {
    $this->upload->process();
}
$collection = $this->getCurrentCollection();

foreach ($this->upload->getResults() as $result) {
    $rid = $this->irss->manage()->upload(
        $result,
        $this->stakeholder,
        md5($result->getName())
    );
    $collection->add($rid);
}
$this->irss->collection()->store($collection);
$posting = $this->getCurrentPosting();
$posting->setRCID($collection->getIdentification()->serialize());
$posting->update();

return true;
 
```

### Adopting Download-Locations

The same with the accesses to these files, again only snippets and not the full implementation. Until now, for example,
a zip of all files of a post was made as follows:

```php
// PLEASE do not reuse this code, it is only an legacy example
public function deliverZipFile(): bool
{
    global $DIC;

    $zip_file = $this->createZipFile();
    if (!$zip_file) {
        $this->main_tpl->setOnScreenMessage('failure', $DIC->language()->txt('error_reading_file'), true);
        return false;
    }

    $post = new ilForumPost($this->getPosId());
    ilFileDelivery::deliverFileLegacy($zip_file, $post->getSubject() . '.zip', '', false, true, false);
    ilFileUtils::delDir($this->getForumPath() . '/zip/' . $this->getObjId() . '_' . $this->getPosId());
    $DIC->http()->close();
    return true; // never
}

protected function createZipFile(): ?string
{
    $filesOfPost = $this->getFilesOfPost();
    ksort($filesOfPost);

    ilFileUtils::makeDirParents($this->getForumPath() . '/zip/' . $this->getObjId() . '_' . $this->getPosId());
    $tmp_dir = $this->getForumPath() . '/zip/' . $this->getObjId() . '_' . $this->getPosId();
    foreach ($filesOfPost as $file) {
        copy($file['path'], $tmp_dir . '/' . $file['name']);
    }

    $zip_file = null;
    if (ilFileUtils::zip(
        $tmp_dir,
        $this->getForumPath() . '/zip/' . $this->getObjId() . '_' . $this->getPosId() . '.zip'
    )) {
        $zip_file = $this->getForumPath() . '/zip/' . $this->getObjId() . '_' . $this->getPosId() . '.zip';
    }

    return $zip_file;
}
```

A collection can be easily downloaded as a ZIP via the IRSS as follows:

```php
public function deliverZipFile(): bool
{
    $zip_filename = $this->getCurrentPosting()->getSubject() . '.zip';
    $rcid = $this->getCurrentCollection()->getIdentification();

    $this->irss->consume()->downloadCollection($rcid, $zip_filename)->run();
    return true;
}

```
