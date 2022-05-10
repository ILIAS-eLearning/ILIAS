# Proposal: Use frameworkless SASS for the UI-Framework

We hereby propose to the JF to move the style code of the UI-Framework from LESS
to SASS. We propose to get rid of the framework dependency to Bootstrap completely
during that move. We furthermore propose to introduce a certain structure-model
for the SASS-code based on a customized version of Inverted Triangle CSS (ITCSS).
This new guideline will superseed the current LESS Guideline. We ask the JF to
express its general support for that move so we can be confident to go on to
make more detailed plans to get us there. We furthermore request the JF to ask
questions or state constraints for our endeavor so we can look into them for a
next iteration.

We try to lay out the plan in more detail in the following.


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


## Why only move the UI-Framework?

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
an extensive research in proposals for style code architecture . During our
discussion of the various models, we found that a customized version of ITCSS
should allow us to build a structured code base for our style code that neatly
fits the requirements of different groups of its prospective users and the idea
and implementation of the UI framework.


## Which guidelines do you propose?

TL/DR: The [complete guideline](#guidelines-for-an-itcss-oriented-scss-structure-for-ilias)
including some examples can be found beneath this text. We propose to structure
our style code according to ITCSS using eight layers:
1. settings
2. dependencies
3. tools
4. normalize
5. layout
6. elements
7. components
8. hacks and tweaks


## Why do we need such a complex style code structure for SASS?

The proposed structure tries to fit the actual complexity and requirements that
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


## Why can't we reuse the current LESS-Guidelines for SASS?

The current guidelines were in fact intended to be a first version of guidelines
for style code and were meant to be extended step by step (see JF decision
regarding that guideline). We in fact should recognize that these guidelines only
target a very small portion of the actual problems that arise when building and
maintaining style code for a complex and manifold UI. The proposal we make here
targets the general structure of our style code, while the LESS-guidelines were
mostly concerned with the way that actual less code is written and layed out. We
plan to add corresponding guidelines to our current proposal as well and will
revisit the current LESS-guidelines when doing so. But we think that there are
more important questions to ponder before doing so. 


## Can you outline how you would want to proceed?

If the JF supports our general idea and direction, as of this proposal here, we
plan to actually apply the model we layed out to (some of) the existing components
in the UI framework. We expect that this will spawn questions and insights that
will lead to refinements and clarifications of the guidelines as proposed here,
as well as induce a discussion about issues and rules on a more detailed level,
such as naming conventions, documentation requirements and layout of code. This
will also allow us to investigate how and where we would want to use the syntactic
and functional possibilities of SASS. This hopefully should lead to a refined
guideline and a PR for the UI framework that we will take to the JF again, for
discussions and/or approval.


# Guidelines for an ITCSS-oriented SCSS structure for ILIAS

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

ILIAS uses a custom set of ITCSS-layers, which is structured as such (from general to specific):


## Settings

The Settings-layer only contains global variables that will be used throughout the
whole system. People that only need minimal changes to the ILIAS skin to create
their custom skin should be able to do this easily by changing values here. It
should contain all global variables related to colors, fonts, font-sizes and
spacings. Local variables that belong to lower layers are defined based on values
from these global variables.

Examples:
* Choose fonts for different purposes.
* Choose a color palette.
* Choose general sizing and spacing for the skin.


## Dependencies

The Dependencies-layer contains files pulled in from other projects. Dependencies
should only be added after careful consideration whether the benefits outweigh the
risks. The dependencies are added here as complete packages to make updating them
easy. Instead of adding complete packages to our css as a framework, we use
dependencies as libraries in the lower layers.

Examples:
* Bootstrap...
* ...


## Tools

The Tools-layer defines mixins, extensible classes, media queries and animations
that are used in lower layers of the SCSS. The tools are used in various other
sections in the SCSS, provide uniform definitions for common concepts and problems,
and thus foster visual homogenity in the system. They substantiate variables from
the Settings into more concrete concepts.

Examples:
* Use colors from the Settings to define a common look for errors.
* Turn screen sizes from the Settings to concrete media queries.
* Provide parametrized mixins for common styling problems.


## Normalize

The Normalize-layer contains styles that normalize browser behaviour by resetting
browser defaults for page and element rendering.

Examples:
* Set general line-heights for texts.
* Remove paddings and margins.
* Remove browser specific stylings.


## Layout

The Layout-layer contains classes and mixins that define the positioning and spacing
of components relative to each other. Base classes for extensions or mixins from
this layer are used in the components. That means: Whenever positioning and spacing
needs to be coordinated between various components the code should be contained
here. If a positioning or spacing can be defined internally in a component or solely
be based on global variables, it should go into the component layer.

Examples:
* The layout of the complete ILIAS-page is implemented via a CSS-grid/flexbox and
  coordinates different individual components.
* The spacing of Bulky Buttons and Bulky Links in Menues needs to be coordinated.
* Close Button in Tool Slate and the Collapse Button in Slates should be on the
  same vertical line.


## Elements

The Elements-layer contains the basic styling of all unclassed HTML-elements. It
provides a visual baseline for more specific components. This is the first layer
that contains actual styling that is visual for end user. This layer will use
variables from the Settings extensively. It captures commonalities among components
that allows them to use unclassed HTML-elements and to only add classes and specific
styling if strictly required.

Examples:
* Define the bullets that are used in lists.
* Define a general look for links.
* Define how headline-elements use variables for fonts and sizes.


## Components

The Component-layer contains css-classes for the single components in the UI-framework
and determines the individual look of each UI-component. It is expected that this
mostly mixes and combines stuff from the upper layers to classes to be slapped
onto html from UI-Components. Colors, sizes and fonts may not be set on this layer
but only be referenced via global variables or indirectly via base classes or mixins
from other layers. This layer should only define its own CSS-properties or overwrite
styles from the Elements-Layer if strictly required.

Examples:
* Build a class to assign color and positions of label to Primary Button by using
  some mixins from Tools and global variables.
* Build a class for the panel to set background color, border and font-style based
  on mixins from Tools and global variables.

Non-Examples:
* Define font-families or sizes for some component. This is supposed to be done in
  the Settings-layer.


## Hacks and Tweaks

This layer should be empty. But as every developer knows: sometimes we cannot solve
a problem right away and we need a hack or tweak that temporarily fixes the issue.
This is the layer where this code goes. Styles that affect very specific locations
in the DOM or override some specific CSS inherited from somewhere go here.
`!important` is only permitted here. Every bit of code that is contained here
should be considered a smell that is worth fixing.
