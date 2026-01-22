# Supported Versions

Every ILIAS version will be **fully supported** until the end of the year after
the year it was released in. **Fully supported** means that every kind of issue
that is reported for the release according to our bugfixing process is eligible
for a fix. *E.g.: A usability issue, reported for ILIAS 10 (released 2025), can
be reported in August 2026 and is eligible for a fix then.*

Every ILIAS version will then gain **security support** for an additional year
after that. **Security support** means that we are fixing security issues only.
*E.g.: A security issue, reported for ILIAS 10 (release 2025), can be reported
in August 2027 and is eligible for a fix then. A malfunction that does make the
program crash, reported at the same moment, won't be eligible for a fix.*


## Timeline per Version

With that support schedule, every version will have (roughly) the following timeline:

| Date      | ILIAS X                 | ILIAS (X+1)             | ILIAS (X+2)             |
|-----------|-------------------------|-------------------------|-------------------------|
| 20X4, Nov | Project Jour Fixe       |                         |                         |
| 20X5, Oct | Coding Completed        |                         |                         |
| 20X5, Nov | Start of Beta Phase     | Project Jour Fixe       |                         |
| 20X6, Mar | Release                 |                         |                         |
| 20X6, Oct |                         | Coding Completed        |                         |
| 20X6, Nov |                         | Start of Beta Phase     | Project Jour Fixe       |
| 20X7, Mar |                         | Release                 |                         |
| 20X7, Oct |                         |                         | Coding Completed        |
| 20X7, Dec | End of Full Support     |                         | Start of Beta Phase     |
| 20X8, Mar |                         |                         | Release                 |
| 20X8, Dec | End of Security Support | End of Full Support     |                         |
| 20X9, Dec |                         | End of Security Support | End of Full Support     |
| 20Y0, Dec |                         |                         | End of Security Support |


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
