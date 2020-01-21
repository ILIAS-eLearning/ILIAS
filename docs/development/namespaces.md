# Namespaces

ILIAS is using PSR-4 namespaces in its [src directory](../../src/README.md).

You MAY also use namespaces in the `Modules` or `Services` directories. You SHOULD use `ILIAS\ModuleName` or `ILIAS\ServiceName`, e.g. `ILIAS\Course` or `ILIAS\ActiveRecord` in these cases.

* Please be informed that we might introduce a PSR-4 based autoloading for these components in the future, too, and revise our directory structure accordingly. **You MAY only make use of these namespaces if you are willing to support these necessary future refactorings**.
* If you are using namespaces, you SHOULD omit the il-prefix from class names.
