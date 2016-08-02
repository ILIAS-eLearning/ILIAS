* Create a Style Guide for code using UI components.
* Create a wrapper type for string, that indicates if and what markup is used for
  the contained text. This might not be an issue to solve in the UI framework, but
  a more general issue that should be put in a single library.
* When implementing images or icons, there most propably should be two types of
  them:
	 - one 'image' type, that just points to some image somewhere by path
	 - one 'icon' type, that enumerates the different icons known by ILIAS
  The 'image' would be used for images uploaded by users, which therefore can not
  be exchanged by a skin. The 'icon' on the other hand is skinnable, as it is known
  in advance.
* Create an abstraction for Actions that could be used instead of stringy links.
  It would be strongly related to ilCtrl and probably should be an ILIAS library.
* Create a mechanism to wire less-files to delos.less that is more automatic than
  'do it manually'. There could at least be some kind of UI.less in the src/UI/templates/default.
