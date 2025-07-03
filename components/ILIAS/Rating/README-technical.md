# Rating Service

## Auto Activate Rating

This feature is currently not implemented in a coherent way.

- ilObjectServiceSettingsGUI embeds the settings
- Setting is saved in ilContainer::_lookupContainerSetting with key ilObjectServiceSettingsGUI::AUTO_RATING_NEW_OBJECTS
- ilObject holds a function selfOrParentWithRatingEnabled() which checks the setting
- This method is called in ilObject->handleAutoRating -> ilObject->hasAutoRating
- Finally this method is called in ilObject->putInTree. If the consuming components do not call this method, auto-rating activation will not work.