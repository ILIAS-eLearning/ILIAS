# General Documentation

## Creating new objects in item groups

Item groups support to create repository objects in their "materials" tab. This allows new objects to be created and directly assigned to the item group.

To achieve this, the `ImplementsCreationCallback` interface of the object service is used. This mechanism only works, if the repository object implementation ensures a call to the method `callCreationCallback()` which invokes these callbacks.

The default implementation in `ilObjectGUI` calls this method in `ilObjectGUI->putObjectInTree()` which is being called in the default implementation of `ilObjectGUI->saveObject()` and `ilObjectGUI->importFileObject()`.

If your repository object does not overwrite these methods, everything should be fine. If your object overwrites `saveObject()` or `importFileObject()`, please ensure that they still contain a call to `putObjectInTree()`. If not, ensure that `$this->callCreationCallback($newObj, $this->obj_definition, $this->requested_crtcb);` is called.

If the resulting implementation does not call `callCreationCallback(...)` for the import and save workflows, objects will not be added to the item group.