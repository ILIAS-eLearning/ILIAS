# Container

## Presentation of Resource Lists / Item Groups

### Business Rules

- Resource lists and item groups are currently presented underneath the content page, if they are not included in active(!) elements of the page (see bug report #9080, #26011). If resource should be hidden from users rbac or activation settings need to be used.

## Presentation of Tiles

### Business Rules

- All properties in the tile view are hidden, except alerts, https://mantis.ilias.de/view.php?id=25903#c63314
- If READ permission is given but access restricted due to timings or preconditions, users still can click on object title but are re-directed to the Info screen where related restrictions of availability are presented, https://mantis.ilias.de/view.php?id=25903#c63314