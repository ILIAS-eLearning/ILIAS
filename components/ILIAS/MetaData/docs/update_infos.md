# Important Changes

> This documentation does not warrant completeness or correctness. Please report any
missing or wrong information using the [ILIAS issue tracker](https://mantis.ilias.de)
or contribute a fix via [Pull Request](../../../../docs/development/contributing.md#pull-request-to-the-repositories).

## With ILIAS 9

Due to changes in the Metadata component, certain LOM elements will not
be shown in the LOM editor anymore in ILIAS 9, if their value does not
conform to the [LOM standard](lom_structure.md). The following elements
are affected:

- **All elements of type datetime**: In previous ILIAS versions, any string
could be entered for datetimes, but ILIAS 9 expects values in the form
`YYYY-MM-DDThh:mm:ss.sTZD`. Not all parts of this format need to be present,
`YYYY-MM-DD` and `YYYY` for example are also valid. The LOM editor in 
ILIAS 9 will ignore values that do not fit this format.<br/>
Further, for values that fit this format, only the date part is used, 
the time is disregarded.
- **'Mozilla' as browser name**: `Mozilla` as `name` under `technical > requirement > orComposite` where
`type` is `browser` will be ignored in the LOM editor in ILIAS 9.

The affected invalid elements are not deleted. They are still exported and imported,
and in the case of 'Mozilla' also still found via the Advanced Search.

When trying to read out one such element, the LOM editor will write a
corresponding `info` to the ILIAS log, such that the element can be 
corrected manually in the database if necessary.

## With ILIAS 10

In ILIAS 10, `Mozilla` as `name` under `technical > requirement > orComposite` where
`type` is `browser` is not ignored anymore, and instead treated as a
value from an unknown vocabulary (if not otherwise configured).

The other LOM elements mentioned [above](#with-ilias-9) will not be exported and
imported anymore.

Elements with invalid values can still be corrected manually in the database.
