# Identifying Objects

> This documentation does not warrant completeness or correctness. Please report any
missing or wrong information using the [ILIAS issue tracker](https://mantis.ilias.de)
or contribute a fix via [Pull Request](../../../docs/development/contributing.md#pull-request-to-the-repositories).

In `Services/MetaData`, objects in ILIAS are generally identified by
three parameters:

1. The `obj_id` of the object if it is a repository object, else the
   `obj_id` of its parent repository object. If the object does not have
   a fixed parent  (e.g. MediaObject), then this parameter is 0.
2. The `obj_id` of the object. If the object is a repository object by
   itself and not a sub-object, then you can set this parameter to 0, but
   we recommend passing the `obj_id` again.
3. The type of the object (and not its parent's), e.g. `'crs'` or `'lm'`.

For example, consider three different objects:

- a Group with `obj_id` 123,
- a Page with `obj_id` 54 in an ILIAS Learning Module with `obj_id` 456,
- and a MediaObject with `obj_id` 789.

The corresponding ID-triples would then be

|             | 1   | 2   | 3
|-------------|-----|-----|-----
| Group       | 123 | 123 | grp
| Page in LM  | 456 | 54  | pg
| MediaObject | 0   | 789 | mob
