#User Service

Currently the user service is only represented by an ilObjUser object of the current user in the DIC via `$DIC->user()`. However ilObjUser is not a real interface for other components, since it reveals lots of internals that should be hidden, see ROADMAP.

## [WIP] Favourites

Before ILIAS 6 consumers used to call `ilDesktopItemGUI::addToDesktop();` or `ilObjUser::_addDesktopItem(...)` to add repository objects to the desktop.

These calls are now replaced by using a  `ilFavouritesManager` class.
```
$favourites = new ilFavouritesManager();
$favourites->add(int $user_id, int $ref_id);
$favourites->remove(int $user_id, int $ref_id);
```

However this is regarded as be being an intermediate solution, since actions on repository objects should get a common concept, similar to actions on users, see ROADMAP.
