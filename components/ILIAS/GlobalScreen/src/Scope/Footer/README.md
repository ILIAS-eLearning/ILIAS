Scope Footer
=============
This scope takes care of the MetaBar elements in the upper right corner of ILIAS. The Machnismen from Providern and Collector is identical like in all other Scopes.

# General Information

- Implement a provider: [components/ILIAS/GlobalScreen/README.md](../../README.md) How to implement your provider
- Available elements: [components/ILIAS/GlobalScreen/Scope/Footer/Factory/FooterItemFactory.php](./Factory/FooterItemFactory.php)

# Notes on Types and Possibilities
## Group
Groups are displayed as link groups in the footer. In most cases, the existing groups from `ilFooterStandardGroupsProvider` are sufficient; new entries should be added to one of the existing core groups. To do this, the parent identification can be obtained as follows:

```php 
$parent = (new \ilFooterStandardGroupsProvider($DIC))->getIdentificationFor(\ilFooterStandardGroups::LEGAL_INFORMATION);
```

see the Enum `ilFooterStandardGroups`for available Standard-Groups.


## Link
Links can be used in two places:
- As part of a group (in this case, a parent is given)
- as independent links in the footer (own section)


## Text
You can also add text to the footer; the texts required for the ILIAS core are already implemented. This function should only be used by plug-ins.


## Permanent
The footer currently only accepts one permanent link, which is already provided in the core and no other component should provide one. Plugins could use this to replace the permanent link with a different implementation.


