# Roadmap

Note: Some of the rules listed in this roadmap may be superseded by general ILIAS rules in the future.

## Known Issues

In contrast to the behaviour in courses it is possible for group members with permission "manage_members" to administrate groups administrators.   

## Short Term


## Mid Term
- Only group users with permission "edit_permission" or group administrators are allowed to administrate group administrators.
- The datetimes in the columns "registration_start" and "registration_end" in
the db table "grp_settings" are saved in the local timezone of the server. They
should instead be saved in UTC, and existing values corrected accordingly.

## Long Term


[1] https://mantis.ilias.de/view.php?id=20681
