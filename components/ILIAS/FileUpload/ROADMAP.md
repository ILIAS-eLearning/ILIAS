# Roadmap

## Short Term

### Inject the dependencies of `AbstractCtrlAwareIRSSUploadHandler`
The abstract upload handler still reads IRSS, the temp filesystem, the language and the file service settings
from `$DIC` in its constructor. It is extended by GUI classes of many components that are constructed by
`ilCtrl`, so this can only change once those subclasses are constructed with their dependencies, or the
handler receives them through a dedicated factory.

## Mid Term

...

## Long Term

...
