# 5.4

* The passed arguments to the callable $error of `ILIAS\Validation\Factory::custom` 
  (or `ILIAS\Validation\Constraint\Custom` respectively) changed. The callable now
  gets a function to perform i18n and replace placeholder and the faulty value as
  a second parameter.
* `ILIAS\Validation\Factory` got a new dependency on `\ilLanguage` to satisfy that
  dependency of `ILIAS\Validation\Constraint\Custom` and the derived classes.
