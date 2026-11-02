# Html Privacy

> **Disclaimer: This documentation does not guarantee completeness or accuracy. Please report any missing or incorrect information by submitting a [Pull Request](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/docs/development/contributing.md#pull-request-to-the-repositories) or, if you prefer, via the [ILIAS bug tracker](https://mantis.ilias.de). When using the bug tracker, please select the corresponding component in the **Category** field.**

## General Information

The Html component is a pure utility library that provides HTML sanitization and purification services to other ILIAS components. It wraps the external HTMLPurifier library and exposes it through `ilHtmlPurifierInterface`, an abstract wrapper (`ilHtmlPurifierAbstractLibWrapper`), a composite chainer (`ilHtmlPurifierComposite`), a factory (`ilHtmlPurifierFactory`), and a DOM node iterator (`ilHtmlDomNodeIterator`). The factory creates concrete purifier instances for forum posts (`frm_post` → `ilHtmlForumPostPurifier`) and quiz/test user solutions (`qpl_usersolution` → `ilAssHtmlUserSolutionPurifier`). HTMLPurifier writes internal cache files to a directory derived from `ilFileUtils::getDataDir()/HTMLPurifier`; this cache contains only compiled HTML definition data, no personal data.

The component does not implement any user-facing features on its own and contains no database access code.

## Integrated Components

The Html component does not employ any other ILIAS components that handle personal data. It can be and is used as a dependency by other components for sanitization purposes, but personal data handling resides entirely within those consumer components.

## Data being stored

The Html component does not store any personal data.

## Data being presented

The Html component does not present any personal data.

## Data being deleted

The Html component does not delete any personal data.

## Data being exported

The Html component does not export any personal data.
