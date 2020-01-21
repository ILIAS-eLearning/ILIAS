# Repository Service

There is currently no main entrance in the DIC for the repository service. The services for **Recommended Content** and **Favourites** are added here but without the appropriate time for discussion due to lack of time. 

## [WIP] Favourites

Before ILIAS 6 consumers used to call

- `ilDesktopItemGUI::addToDesktop();`
- `ilObjUser::_addDesktopItem(...)`
- `ilObjUser::isDesktopItem(...)`

and similar calls.

These calls are now replaced by using a  `ilFavouritesManager` class.
```
$favourites = new ilFavouritesManager();

// add favourite for user
$favourites->add($user_id,$ref_id);

// remove favourite for user
$favourites->remove($user_id, $ref_id);

// check if repository item is favourite of user
$favourites->ifIsFavourite($user_id, $ref_id) {
    ...
}

// load data into cache
$favourites->loadData($user_id, $ref_ids);

```
