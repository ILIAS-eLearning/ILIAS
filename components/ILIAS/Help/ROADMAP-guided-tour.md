# Roadmap Guided Tour

The feature depends on several parts of the general infrastructure provide its features. Some of them are already integrated in last generation components like global screen or ui framework, others rely on legacy behavior. But also the collaboration with ui elements and the global screen has sometimes a workaround-ish state.

## Obtaining IDs for UI Elements

These are abstract IDs that are used to be stored with the different steps of a guided tour. If these IDs change in the code, the steps in the guided tour may break.

- Screen ID: The screen ID is obtained from an ilCtrl observer implemented by the Help component. The IDs are derived from the class names being involved in the ilCtrl flow. These class names may be subject to technical refactorings in the components, so they may change with major releases. It is possible to decouple this via attributes in the class code, but the JF decided not to introduce this decoupling via rector. So developers would need to take care of the stability of screen IDs individually.
- Metabar and Mainbar button IDs: The help component implements the AbstractModificationProvider provided by the global screen service. This allows to collect all items (buttons) and their internal identifiers. These should be quite stable, since they are also used, e.g. for the main menu configuration in the ILIAS administration.
- Tab IDs: Tabs are a legacy UI component in ILIAS. They are not collected by the global screen service, thus providing their IDs to the help component is part of the legacy tab code. Components using tabs need to provide these IDs in general, since they are used, e.g. for activation of tabs, too. The current approach results in a high coupling of the help and tab component, even if these are only a few lines.

## Presenting UI Element IDs

Authors of guided tours need to know the IDs of screens and ui elements, since they need to enter these IDs when creating steps for these elements.

- Screen ID: The screen ID is displayed by implementing the AbstractNotificationProvider interface of the global screen service.
- Metabar and Mainbar button IDs: These IDs are displayed by attaching KS Help\Topic ui element to the items in the AbstractModificationProvider implementations using withTopics() methods.
- Tabs IDs: The tabs are using KS link elements and attach KS Help\Topic similarly. However the coupling is again higher, since this code is part of the legacy tab implementation.

The presentation of the KS Help\Topic ui elements is suffering from a longtime bug which is already part of ILIAS 10, https://mantis.ilias.de/view.php?id=42421

## Mapping Element IDs to HTML DOM IDs

The content of guided tour steps is displayed in a KS popover element. The popover is placed via Javascript closed to the target element. The are not attached to the usual click event of these elements, since this is not the desired behaviour. The target elements (e.g. mainbar entries) should still do their normal action „on click“.

The DOM IDs of KS ui elements are generated during runtime for each request. The get different IDs for each request, thus the „identification part“ of an ID concept is not well supported through requests.

The guided tour stores the mapping by attaching JS code for these elements. This is not necessary for the Screen IDs or for elements that are not identified by their ID (first form or first table of a view).

-  Metabar and Mainbar ID mapping: As part of a AbstractModificationProvider implementation, the guided tour components attaches Javascript calls like `il.guidedTour.addMapping('$name', '$id');` to the items.
- Tab ID mapping: The tabs call `$ilHelp->registerTabLink` which adds a `il.guidedTour.addMapping('$name', '$id');` call to the links.

## Attaching the Popover to DOM elements

As written above this cannot be done using the usual mechanism „on click“ of the target elements, since the popover is shown and attached to the target elements via Javascript.

Like many KS elements the popover provides a method to obtain a signal ID. This ID can be used in an event to show the popup. The event can contain a triggerer parameter to attach the popover to a DOM element. If this is considered an official supported part if the interface is questionable. Additionally attaching the element this way, also binds the presentation of the popover to the click event of the DOM element which is not desired. It required a workaround to inject a „trigger: manual“ behavior here which does not seem to be sustainable.

Some elements like the first form, table or primary button are not identified by any ID provided by global screen or KS elements. These elements are retrieved via Javascript/CSS selectors during runtime. Even if the main HTML structure of the ILIAS view is pretty stable, this part of the code might need adaptions with every major release.

## Recommendations for Improvements

- The popover element should get an official supported Javascript interface to allow presentation and attaching to other KS elements. It should also provide a method to hide all popovers.
- The global screen component should organize more elements similar to the mainbar and metabar, at least tabs, maybe also forms and tables.
- Internal IDs from the global screen should be rendered as data attributes with the KS elements. This would make it possible to remove the ID mapping in the guided tour.
- The KS page element should provide a stable structure that enables other components to address elements like forms or tables in the main content and other areas reliably. This is also important for skins or plugins trying to modify these elements.
- The KS tooltip element needs to be fixed. It should be possible to use it on all major interactive elements.
