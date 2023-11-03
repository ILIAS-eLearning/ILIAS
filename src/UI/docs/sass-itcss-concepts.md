# Frameworkless SASS and ITCSS

This concept outlines how we strive to make ILIAS style code more modern and
easier to maintain using frameworkless SASS and ITCSS. Large parts of this have
been put into practice during a style code refactoring for ILIAS 9.

Read this document to find out
* why big changes were made to the style code and
* what strategic decisions will influence ongoing and future projects.

A more practical guide with rules and recommendations on how to work with the
ILIAS style code when developing or building System Style skins can be found
here: [SCSS Coding Guidelines](../../../templates/Guidelines_SCSS-Coding.md)

## Why use SASS instead of LESS?

LESS and SASS both are style-sheet-languages that compile to CSS, created to solve
certain shortcommings in CSS that mostly revolve around possibilites to abstract
and reuse code. They are comparable in possibilities, where SASS is slightly more
expressive than LESS. The previous choice for LESS was motivated mostly by the
usage of LESS in Twitter's Bootstrap Framework that was introduced to ILIAS.
Currently it seems as if SASS just won the competition of languages that compile
to CSS, with Bootstrap moved to SASS as well in the current version. The less 
compiler (running on node.js) did not recieve any updates since 2017, so it seems
to be a good time to move to SASS as well.


## Why we expect to implement new best practices in the UI Framework first?

The UI-Framework is meant to provide UI components for all other components of
ILIAS, with the ultimate goal that other components do not contain any custom
HTML or CSS-code anymore. We are not there yet, but we are indeed getting closer.
The scope of the effort to rethink how style code could be organized and structured
was the UI-framework, since the effort spent on existing custom UI-code seems to
become obsolete sometime soon anyway. The HTML/CSS-code in individual components
currently is maintained by the component's maintainer and we can indeed observe,
that it is already hard to keep that code up to date, even with the former
development regarding Bootstrap and LESS. We do not expect that situation to
become better and consider it to be in the best interest of maintainers to move
their components to the shared collection of UI components of the UI framework as
fast as possible. We do not forbid anyone to use the mechanisms for (of?) SASS
that we intend to introduce for individual UI code in their component, but we
don't think it is feasible or even worth taking these components into further
consideration when renovating the style code of the UI framework.


## Why would we want to get rid of Bootstrap as a framework?

From our perspective, Bootstrap was introduced to solve three problems:

1. Building grid layouts and responsive layouts was hard with the then-existing
   means of HTML and CSS.
2. The community generally lacked available expertise in building and styling UI
   components so sticking to an existing framework seemed to be a good way to save
   some work and to use some knowledge from elsewhere on our end.
3. Bootstrap implements some concrete UI components, such as modals, that we also
   wanted to use.

As well our hope was that

4. using Bootstrap would allow people with Bootstrap knowledge to easily redesign
   ILIAS as well.

Problem **1** has mostly become obsolete in the meantime due to the introduction
of the CSS-grid and flexbox layouts to standard CSS, which was already incoporated
in ILIAS 6. Regarding **2**, we managed to build some serious expertise regarding
the required fields of knowledge in our community in the meantime, with the added
benefit that these experts also have knowledge of ILIAS as a general application.
Thanks not least to the UI framework, working on UI and UX related questions is
emerging as a field of work in its own right in the ILIAS community. The goal we
had with **4** seem to never have materialized, probably due to the fact that ILIAS
actually just **uses** Bootstrap instead of being completely implemented with it
as a framework. **3** still seems to be a valid reason to use Bootstrap, but we
don't believe that this benefit makes it worth to carry the whole bootstrap framework
along as a dependency and to risk the sideeffects of this in other parts of the UI.


## Do you want to write all SASS-Code by yourself?

No, we do not intend to write each and every bit of CSS or JS we need and still
want to pull in libraries if feasible and required. But we plan to really do just
this: use other peoples code as library and not as framework. When saying "using as
framework", we understand a framework to be a project that provides a structure
for downstream projects, where these projects use the framework by filling in gaps
and adding pieces as required. A "library" on the other hand serves as a repository
for downstream projects, from where we could pull bits and pieces as required,
while maintaining our own ideas on how to structure and organize our project. We
want and expect ILIAS to thrive for 20 more years and, frankly, do not expect any
project we could pull in to be alive then anymore. We thus consider it to be a good
strategic choice to not rely on other projects too much and thus propose to use
libraries but no frameworks. This indeed could be done with projects like Bootstrap
by just pulling in the parts we require, but there are also many other projects
out there that we indeed might want to use to solve specific UI problems for us.


## What is ITCSS?

[Inverted Triangle CSS](https://www.xfive.co/blog/itcss-scalable-maintainable-css-architecture/)
is a proposal to structure complex and big code bases for style sheet code. The
layers are imagined to form a triangle, where the topmost layer has the biggest
reach but least specificity and explicitness. We decided to use that model after
an extensive research in proposals for style code architecture. During our
discussion of the various models, we found that a customized version of ITCSS
should allow us to build a structured code base for our style code that neatly
fits the requirements of different groups of its prospective users and the idea
and implementation of the UI framework.


## Which guidelines do you propose?

The currently binding guidelines for ILIAS version 9 and later can be found here: 
[SCSS Coding Guidelines](../../../templates/Guidelines_SCSS-Coding.md)

They include a detailed description of each ITCSS layer, naming conventions and
best practices for Sass.


## Why do we need such a complex style code structure for SASS?

The chosen structure tries to fit the actual complexity and requirements that
our style code bears. The complexity mostly arrises due to the huge variety of UI
components and combinations thereof, in conjunction with the sizeable group of
developers working on and using them. The guidelines attempts to provide a structure
to get a handle on that complexity. The requirements for one arise from the needs
of skin creators of different proficiency. It should be easy to change simple
things regarding the skin, but it should also be possible to change hard things
about the way ILIAS looks with a skin. The inverted triangle with the growing
specificity tries to cater to these different requirements when skinning ILIAS.
On the other hand, the style code we have should be maintainable, which for one
means that it should be possible to understand the scope and influence of a change,
and on the other hand also should allow to understand where one might look for or
put a certain snippet of code. Alas, we think that building style code for ILIAS
contains a lot complexity and problems in and on itself. The goal of the guidelines
is to make them manageable but won't make them disappear.


# The ITCSS-oriented SCSS structure for ILIAS

SASS in ILIAS is structured according to an adapted version of the [Inverted Triangle CSS (ITCSS) Model](https://www.xfive.co/blog/itcss-scalable-maintainable-css-architecture/).

The general idea is to separate the SCSS codebase in several layers and arrange
it in a way that goes from general to specific, so that specific SCSS can use
code from more general layers. This model serves different purposes:

* It allows to easily locate and thus maintain and change SCSS-snippets of different
  specificity. If someone wants to derive a custom skin from the ILIAS-SCSS, the
  structure should make it easy to find the location where the change is required.
* It serves to understand the scope of a change and thus the need to coordinate
  with other developers. More general SCSS-code needs more coordination, while
  specific code is only used locally.
* It provides a structure to foster decisions on where we want to have specific
  SCSS-snippets to best serve the purposes defined above, rather than providing
  a taxonomy to definitely classify existing snippets.
* It structures the dependency between SCSS-code from top to bottom and prohibits
  circular or chaotic dependencies.
* It uses separate folders for each layer of the triangle respectively ITCSS section.
* It provides namespaces that make it easy to understand the purpose of a SCSS-snippet.

The custom set of layers that we ended up implementing are described in detail as part
of the [SCSS Coding Guidelines](../../../templates/Guidelines_SCSS-Coding.md).