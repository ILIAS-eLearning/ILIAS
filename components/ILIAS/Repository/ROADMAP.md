# Roadmap

## Short Term

- Favourites interface is a quick solution for 6.0 and should finally be discussed in Jour Fixe.

## Mid Term

### Refactoring Deletion / Trash Process

The deletion process should be organised in decent service business logic class. Currently the ilRepUtil holds this code in static methods. The process is vulnerable against errors in the object type specific deletion methods. If these methods fail, objects may remain in the trash, even if deactivated, see https://mantis.ilias.de/view.php?id=35943

### Listing Panels

The repository container should use Listing Panels and Deck of Cards consistently, see [Container Roadmap](../Container/ROADMAP.md).

### Favourites and Common Repository Item Actions

With ILIAS 6 the "Add to Desktop" feature is relabeld as "Favourites". It might be better to group this service together with other common actions on repository objects (copy, paste, ...). These actions should get a better common interface in the future.


## Long Term

...