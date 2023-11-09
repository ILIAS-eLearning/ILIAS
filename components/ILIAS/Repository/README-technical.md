# Repository Service

## Favourites

Favourites are managed by the class `ilFavouritesManager`.

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