# Rector for Services/Utilities

The Services/Utilities component is an accumulation of many methods and
classes that have accumulated over the years and unfortunately are often
widely used across the entire code base. As part of the PHP8 project
2021/22, large parts of it have been moved to other components as they
are exclusively or mainly used.

The `ilUtil` class, however, partly contains static methods that cannot
easily be moved or assigned to another component. partly it also contains
methods for which alternatives have existed for a long time.

with the help of rector we try to get rid of such places.

# Replace ilUtil:.sendInfo (and 3 more)
There is since at least ILIAS 6 on `ilGlobalTemplateInterface` the method `setOnScreenMessage`, which takes over the functionality of the `ilUtil` methods. The rector `ReplaceUtilSendMessageRector` replaces the places that use the static ilUtil methods.

Thereby - if not existing - the class is supplemented with an `ilGlobalTemplateInterface` dependency.

Make sure you have a updated composer classmap before running rector!

How to run for a component such as Modules/File:
```bash
./libs/composer/vendor/bin/rector process --clear-cache --no-diffs --config ./CI/Rector/ilUtils/ilutil_rector.php Modules/File
```
