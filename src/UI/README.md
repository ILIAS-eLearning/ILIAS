# Prototype for an ILIAS-UI-Framework

## To be discussed

* Should we already introduce some versioning for the consumer interface?
* Which fields of the KS entries should be part of the comments in the factories?
* It would be nice to enumerate the rules, we could refer to them in tests than.
* How could we make sure, that documentation and tests match up as much
  as possible? Could we generate tests from comments directly? How would one
  do that?
* Should the UI elements be immutable? (currently the tests say yes)
* It does not seem to make sense to implement to_html_string on Counter, as we
  never render a counter on its own. What to do about that?

## ToDos:

* Create some more meaningful tests on counter and glyph.
