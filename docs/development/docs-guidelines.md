# Docs Guidelines

This guidelines direct authors in creating, naming and writing files providing
information such as guidelines, how-tos, tutorials, examples or abouts. If you
want to propose changes to any of the doc files, please make a PR agains trunk
and label it with 'JourFixe'. Further, please consult the
[contributing](contributing.md) guideline for more information about
collaborating in this project.

## Location

When such new information is to be added, the following points need to be
considered concerning the location of this information:

* If information that applies to exactly one directory of the ILIAS repo is
provided then a file named `README.md` MUST be created in this exact directory
and the new information MUST be added there.
* Each folder except docs and it's descendants MUST only contain at most one md
file, named `README.md`
* Information of general nature or not concerning strictly one directory MUST be
placed in the docs folder or even better in one of the following sub directories
of docs if applicable:
  * documentation: For examples, tutorials, explanations and guidelines covering
  cross-sectional matters. Mostly for contributors.
  * configuration: For How-Tos, FAQs installation instructions or similar.
  Mostly for administrators.
* Any information essential for either running ILIAS or contributing to ILIAS
SHOULD at least be linked in the global `README.md` file placed in the main
directory of the ILIAS repo.

## Naming

* Files providing information for one exact folder MUST be named README.md.
* Only the following files MUST be written in uppercase:
  * `README.md`
  * `LICENSE.md`
* All other files MUST be written in lowercase.
* Names containing more than one word MUST use hyphens to separate the words
(such as docs-guidelines).
* The file extension for all markdown files MUST be `.md`

## Content

* Markdown SHOULD be used for providing information such as described above.
  * A Table of Content (TOC) with links to all headlines SHOULD be included.
* [RFC2119](https://www.ietf.org/rfc/rfc2119.txt) SHOULD be used when writing
guidelines to signify their level of requirement.
* In Guidelines the audience SHOULD NOT be addressed directly (e.g. do not start
with "You SHOULD").
* In how-to, tutorials or similar, the audience MAY be addressed directly.
* If content from other files is referenced an internal link to this document
SHOULD be provided.
* Line wrap after around 80 chars per line to improve readability SHOULD be used.
* Screenshots and Images SHOULD be avoided in the documentation if possible. Use
abstractions instead. If there is no way around using an image, it MUST be included
by referencing a resource in the [screenshots folder](https://ilias-elearning.github.io/screenshots)
on https://ilias-elearning.github.io.


### Table of Contents (Markdown)

To maintain a TOC the *Sublime Edit* plugin
[MarkdownTOC](https://packagecontrol.io/packages/MarkdownTOC) MAY be used, other
Tools are welcome as well. If *MarkdownTOC* is used, the TOC SHOULD be initiated
with the following attributes:

```
<!-- MarkdownTOC depth=0 autolink="true" bracket="round" autoanchor="true" style="ordered" indent="   " -->
```

# PRs and tickets for the Technical Documentation

* PRs for documentation and according tickets in Mantis will be assigned to Ann-Christin
  Gruber (@acgruber), as she is the staff member of the ILIAS society that is tasked
  to take care of the technical documentation.
* Ann-Christin can handle PRs and Mantis-tickets about documentation however she sees
  fit. In particular, she may merge PRs and close tickets.
* We kindly ask Ann-Christin to consider if and when to ask other community members
  for help when working on the documentation.
* We kindly ask every community member to support Ann-Christin with her work on the
  technical documentation, especially when asked for feedback regarding changes in
  PRs.
* Issues in the technical documentation are treated like any issue in our code:
  People may open tickets in mantis or propose changes via PR to solve issues or make
  improvements. As for the ILIAS code, there exists no warranty for the correctness
  of our documentation.
