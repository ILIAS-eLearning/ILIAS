# Implementing an Email Text Template Context

## module.xml/service.xml

A module or service has to "announce" its mail template contexts to the system by adding them to their respective module.xml or service.xml.

- The template context id has to be globally unique.
- An optional path can be added if the module/service directory layout differs from the ILIAS standard.

```php
<?php xml version = "1.0" encoding = "UTF-8"?>
<module xmlns="http://www.w3.org" version="$Id$" id="crs">
    ...
    <mailtemplates>
        <context id="crs_context_manual" class="ilCourseMailTemplateContext" />
    </mailtemplates>
</module>
```

## ilMailTemplateContext

Every mail template context class defined in a module.xml or service.xml has to extend the base class `ilMailTemplateContext`. Please implement all abstract methods to make a template context usable.

- \+ getId : String
- \+ getTitle : String
- \+ getSpecificPlaceholders : Array
- \+ resolveSpecificPlaceholder ( String placeholderId, Array contextParameters, ilObjUser user, Boolean htmlMarkup ) : String

A collection of context specific placeholders can be returned by a simple array definition. The key of each element should be a unique placeholder id. Each placeholder contains  (beside its id) a placeholder string and a label which is used in the user interfaced.

```php
return array(
    'crs_title' => array(
        'placeholder' => 'CRS_TITLE',
        'label' => $lng->txt('crs_title')
    ),
    'crs_link' => array(
        'placeholder' => 'CRS_LINK',
        'label' => $lng->txt('crs_mail_permanent_link')
    )
);
```