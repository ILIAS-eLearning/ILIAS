# Supported Versions

Every ILIAS version will be **fully supported** until the end of the year after
the year it was released in. **Fully supported** means that every kind of issue
that is reported for the release according to our bugfixing process is eligible
for a fix. *E.g.: A usability issue, reported for ILIAS 8 (released 2022), can
be reported in August 2023 and is eligible for a fix then.*

Every ILIAS version will then gain **security support** for an additional year
after that. **Security support** means that we are fixing security issues only.
*E.g.: A security issue, reported for ILIAS 8 (release 2022), can be reported
in August 2024 and is eligible for a fix then. A malfunction that does make the
program crash, reported at the same moment, won't be eligible for a fix.*


## Timeline per Version

With that support schedule, every version will have (roughly) the following timeline:

| Date      | ILIAS X                 | ILIAS (X+1)             | ILIAS (X+2)             |
|-----------|-------------------------|-------------------------|-------------------------|
| 20X0, Nov | Project Jour Fixe       |                         |                         |
| 20X1, Apr | Feature Freeze          |                         |                         |
| 20X1, Oct | Start of Beta Phase     |                         |                         |
| 20X1, Nov |                         | Project Jour Fixe       |                         |
| 20X2, Mar | Release                 |                         |                         |
| 20X2, Apr |                         | Feature Freeze          |                         |
| 20X2, Oct |                         | Start of Beta Phase     |                         |
| 20X2, Nov |                         |                         | Project Jour Fixe       |
| 20X3, Mar |                         | Release                 |                         |
| 20X3, Apr |                         |                         | Feature Freeze          |
| 20X3, Oct |                         |                         | Start of Beta Phase     |
| 20X3, Dec | End of Full Support     |                         |                         |
| 20X4, Dec | End of Security Support | End of Full Support     |                         |


## Implications

* If we follow this optimal timeline, users have roughly 3/4 year to update to the
  next fully supported version. This can be expanded to 1 3/4 year if users skip
  every other fully supported version.
* From the project planning jour fixe to the end of security support, every version is
  active for a little more then four years.
* Most of the time, the community will need to keep track of four different version
  in different states of their life cycle.
* Most of the changes that fix issues will need to be included in three branches,
  fixes for security issues will need to be included in four branches.
