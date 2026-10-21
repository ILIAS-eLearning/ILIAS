# Activities of the File Component

The Activities in this folder cover the life cycle of a file object in the repository: create it,
find it, read it, change it, move or copy it, delete it, and download a whole container. The general
idea of Activities and the contract every Activity has to keep are described in
`components/ILIAS/Component/src/Activities/README.md`. Read that first.

| Activity | Type | Permission |
|---|---|---|
| `CreateFile` | Command | `create_file` on the target, which must be a container |
| `ReplaceFileContent` | Command | `write` |
| `UpdateFileMetadata` | Command | `write` |
| `DeleteFile` | Command | `delete` |
| `MoveFile` | Command | `delete` at the source and `create_file` at the target |
| `CopyFile` | Command | `copy` and `create_file` at the target |
| `ListFiles` | Query | `read` per object |
| `ListFileVersions` | Query | `write` |
| `DeliverFileObject` | Query | `read` |
| `DeliverFileVersion` | Query | `write` |
| `DownloadContainerAsZip` | Query | `read` per object |

`ListContainerContent` is code and not a contributed Activity: what lives below a node of the
repository is not the domain of this component. `ListFiles` performs it.

How a file travels as the parameter of an Activity is described in
`components/ILIAS/FileUpload/src/Activities/README.md`.

## Where these Activities deviate from the framework, and why

The Activities framework is young. Some of what its contract asks for has no support yet, and some
of the services these Activities need are not offered by any component yet. The Activities here
work around these gaps in a few places. Each workaround is kept in one place, so that it can be
removed when the gap is closed.

### Raw parameters are not read through the input description

The contract says that `maybePerformAs()` grinds the raw parameters through `getInputDescription()`.
The Activities here read them with `ILIAS\FileUpload\Activities\Parameter` instead.

Why:

* The framework has no code for this step. Neither `ActivityImpl` nor `Query` implements
  `maybePerformAs()`, and nothing turns an array of raw parameters into the content of a `FormInput`.
* The fields of the UI framework are built for forms in a browser, not for parameters from a
  webservice. A `Checkbox` is only true for `"checked"`, so a `true` from a JSON body would silently
  become `false`. `Numeric` rounds `1.7` to `2` and turns `true` into `1`, which is the root of the
  repository when it is a reference id. `Field\File` only knows identifiers from an upload endpoint.
* A cast is no alternative. `(int) ['5']` is `1`, and with `filter_var()` a typo in
  `keep_previous_version` would mean "drop the history".

`Parameter` composes the Refinery: it first checks that a value is of a type that can carry the
parameter (`to()`), and only then converts it (`kindlyTo()`). A value that is not what the parameter
takes is refused with a 400 that names the parameter. The input descriptions still describe the
parameters, with dedicated names, so that they can be used as soon as the framework reads them.

Resolved when: `maybePerformAs()` reads the raw parameters through the input description, and there
are fields that read raw values strictly. Then `Parameter` goes away.

### Every Command implements `maybePerformAs()` itself

There is a base class `Query`, but no base class `Command`. The six Commands extend `ActivityImpl`
directly and each one implements the same sequence: read the parameters, check the permission,
perform, wrap the result.

Resolved when: the framework has a `Command` base class, or a default implementation of
`maybePerformAs()` in `ActivityImpl`.

### Some legacy services come from the legacy container

Access and RBAC come from the AccessControl component, the storage from the ResourceStorage
component. The repository tree, the object definition, the settings and the user of the session
are not offered by any component yet. `RepositoryProvider` takes them from `$DIC` on first use,
the same way `AccessControl\Tree\RepositoryTreeAccessProxy` gets the tree. Creating, copying and
deleting a file object also goes through `ilObject`, `ilObjFile` and `ilRepUtil`, which read the
legacy container themselves.

All of this is kept behind `RepositoryProvider`, which also makes the Activities testable:
`ilObject::create()` and `delete()` are final and cannot be mocked.

A consequence for every webservice that performs these Activities: the legacy code needs the user
of the request in the legacy container, as `ilUser` and as `user`. Without it, every call fails.

Resolved when: the tree, the object definition, the settings and the current user are provided by
their components. Then they move to the constructor of `RepositoryProvider`.

### Commands are only performed as the user of the session

`maybePerformAs()` takes the id of the user to perform as. The legacy code the Commands go through
does not: it takes the user of the session as owner of an object, of a revision, as author of a
news item, and for checks of its own. A Command performed for somebody else would be checked for
one user and done as another. So a Command refuses with 403 unless the user is the user of the
session (`RepositoryProvider::isActingUser()`). Queries are not affected; they read through the
access handler with the user they are given.

Resolved when: creating, copying and deleting objects can be done for a given user.

### Deleting uses a deprecated method

`DeleteFile` does what the GUI does: the object goes to the trash, or it is deleted for good if the
installation has no trash. The answer says which of the two happened. The deletion goes through
`ilRepUtil::deleteObjects()`, which is deprecated. The deletion it hands on to belongs to the
internals of the Repository component, so there is nothing better to call yet.

Resolved when: the Repository component offers a public deletion service.

## Rules these Activities keep

* **One call per intention.** `CreateFile` creates the object, puts it into the tree, sets the
  permissions and stores the content. If storing fails, the object and its tree node are removed
  again. `MoveFile` and `CopyFile` exist because of their pair of checks, which a client issuing
  two calls could not guarantee.
* **Two ways in, two levels of trust.** `maybePerformAs()` is the way in for untrusted callers,
  `perform()` the one for other Activities, which have checked already. `DeliverFileObject` checks
  `read` on the object and then performs `ResourceStorage\Activities\DeliverResource`, which itself
  only hands a resource to its owner when it is called through `maybePerformAs()`.
* **No user, no access.** User 0 and the anonymous user are refused before the access handler is
  asked. A caller without a session must not get what the anonymous user may read in a browser.
* **Failures are results.** An exception with a 4xx code is an answer to the request and leaves as
  it is. Everything else is replaced by a generic message (`FileUpload\Activities\Failure`), because
  a legacy message may carry a query or a path in the storage.
* **Limits before work.** `DownloadContainerAsZip` refuses more than 500 files or 100 MB, because the
  archive is built and encoded in memory. `ListContainerContent` refuses more than 5000 objects
  before it checks the access to each of them. A file that cannot be read is left out of an archive
  and listed in `skipped`.
