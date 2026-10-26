# UIComponent Privacy

> **Disclaimer: This documentation does not guarantee completeness or accuracy. Please report any missing or incorrect information by submitting a [Pull Request](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/docs/development/contributing.md#pull-request-to-the-repositories) or, if you prefer, via the [ILIAS bug tracker](https://mantis.ilias.de). When using the bug tracker, please select the corresponding component in the **Category** field.**


## General Information

The UIComponent component provides a collection of legacy, reusable UI building blocks for the ILIAS frontend. It includes widgets such as tabbed navigation (`ilTabsGUI`), toolbars (`ilToolbarGUI`), tree explorers (`ilExplorerBaseGUI`, `ilTreeExplorerGUI`), confirmation dialogs (`ilConfirmationGUI`), progress bars (`ilProgressBar`), buttons (`ilLinkButton`, `ilSubmitButton`), text highlighters, checkbox overlays, and nested lists. Additionally, it provides a plugin hook mechanism (`ilUIHookProcessor`, `ilUserInterfaceHookPlugin`) that allows UI plugins to prepend, append, or replace rendered HTML output of any ILIAS page section. The component itself acts purely as a rendering infrastructure layer; it does not perform any database reads or writes of its own and does not manage personal data directly.

## Integrated Components

The UIComponent component does not directly integrate with other ILIAS components that handle personal data. It provides generic UI infrastructure consumed by virtually all other ILIAS components, but the UIComponent itself does not reference or depend on user, course, or any other personal-data-bearing component.

## Data being stored

The UIComponent component does not store any personal data. No database insert, update, or manipulate calls were found in any of its PHP source files. All widgets are stateless renderers that receive their data from the calling component at runtime.

## Data being presented

The UIComponent component does not present personal data on its own. The individual widgets (tabs, toolbars, explorers, confirmation dialogs, progress bars, buttons) render structural or navigational HTML whose content is supplied entirely by the calling ILIAS component. Any personal data that may appear within those widgets originates from and is the responsibility of the consuming component, not UIComponent.

## Data being deleted

The UIComponent component does not store any data and therefore has no deletion behaviour of its own.

## Data being exported

The UIComponent component does not export any data.
