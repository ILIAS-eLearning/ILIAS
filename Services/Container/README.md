# Container

This part of the documentation deals with concepts and business rules, for technical documentation see [README-technical.md](./README-technical.md).

## Presentation of Resource Lists / Item Groups

- Resource lists and item groups are currently presented underneath the content page, if they are not included in active(!) elements of the page (see bug report #9080, #26011). If resources should be hidden from users, rbac or activation settings need to be used.
- Items that are assigned to an item group or to a session are not presented in the "Content" block or in any "By Type" blocks again. They are not hidden from other sessions or item groups if assigned multiple times.
- The page editor will add automatically all existing blocks to the end of the page, if they are not embedded in the page content yet. This happens when the editor is entered and when page content is changed. (ILIAS 9, [1])
- This means, that blocks may reappear at the end of the page, if they are deleted from the content. (ILIAS 9, [1]).
- Non-existing / empty blocks will not appear when editing the page. Only exception are manually created empty item groups which will be shown with a message "This object is empty and contains no items." (ILIAS 9, [1])
- Blocks containing more than 5 items will be shortened in the editor. (ILIAS 9, [1])
- It is possible to add existing blocks multiple times to the page content.

[1] https://docu.ilias.de/goto_docu_wiki_wpage_6012_1357.html

## Presentation of Tiles

- All properties in the tile view are hidden, except alerts, https://mantis.ilias.de/view.php?id=25903#c63314
- If READ permission is given but access restricted due to timings or preconditions, users still can click on object title but are re-directed to the Info screen where related restrictions of availability are presented, https://mantis.ilias.de/view.php?id=25903#c63314 (see also Services/InfoScreen)

## Order

- In Session View the session block always lists its items by their starting date, beginning from the oldest. Alphabetial, "By Creation Date" or manual order does not affect the session block. In all other views sessions are ordered by the selected order type specified in the settings.