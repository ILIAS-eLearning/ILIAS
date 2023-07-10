# How to Handle Text in ILIAS?

As an application with content management functionality, ILIAS handles text in
various locations: on handcrafted pages, in mails, in feedbacks, comments and
forums and in various other locations.

Currently, text in ILIAS is not abstracted in any way: It is passed as string.
This causes various problems, especially when text is passed between components,
that pop up from time to time. Does this text contain formatting in HTML? Can
it be used with or without html escaping, or was HTML escaping already done by
the other component? Or will it be performed later? Can I output my text in this
or that context without introducing security problems, formatting problems or
what not?

This paper looks to propose an idea how this situation can be improved.


## What do we Mean by "Text"?

Certainly, we are not looking to target any `string` in the ILIAS codebase. There
are various locations where `string`s are used for other purposes: as ids, as part
of controlled vocabularies, as input from users that needs to be parsed. So what
do we really target when we say "text" in this paper?

**We understand text to be sequences of words of an unspecified length, possibly
containing information about its structure (like paragraphs, headings or lists).
The information content of these sequences of words won't be of interest for the
application, but the structure is required to derive formatting for various output
contexts.**

Although this might leave some gray areas, this definition could be used to decide
if a given piece of information is or isn't text, e.g.:

* **login names of users**: *no text*, because just one word (no spaces...) and no
further structure possible. Also, the contained information is of interest for the
application since we need it to derive which user just logged in.
* **content of a mail**: *text* because certainly structured words are possible and
common. The actual information content does matter for the receiving person, but not
for the application.
* **titles of objects**: *might or might not be text*, because we support a little
structuring (bold and italics) currently, but it is debatable if we really want to
do so in the future.


## Requirements for Text Handling

To provide ILIAS developers with a tool set to solve the outlined problems with the
current `string`-based text handling approach, a new approach needs to implement
the following requirements:

* For a given piece of text it should be known at any time which markup is used
in the underlying string representation of the text.
* For a given piece of text it should be known at any time which structure elements
could be used in the text.
* On programmatic interfaces it should be possible to specify which structure and
markup is required when passing text.
* The tool set should support building user interfaces to input text with a specified
markup and structure (but actually building said interfaces is out of scope here).
* It should be possible to convert all texts to certain baseline representation.
These are plain text (as this is a baseline that is supported in every interesting
target context) and HTML (since this is the markup for browsers, the main environment
of our users). These conversion can be lossful, though.
* It may be possible to convert some text in a given representation to some other
representation but in general it is only expected that every text can be converted
to HTML and plain text.


## Approach

To solve the requirements we are looking to implement the following approach in
a sub folder of `src\Data`, using the conventions and standards of that library.
Conversions will also be made available via the `Refinery` to integrate them
into an established framework.

### Define Structure Options

To make it possible to programmatically talk about structuring options for text,
a central `class` (and later `enum`, once supported for PHP < 8.1 is cut) defines
the options that we care about:

```php
class Structure
{
    const string BOLD = "bold";
    const string ITALIC = "italic";
    const string UNORDERED_LIST = "unordered_list";
    /* ... */
}
```

### Define Markup

Text will be represented as `string` in memory. Since we do not care about the
information or specific structure of a given text, it seems to be unexpected that
texts need to be represented as abstract syntax trees or something alike. Text
might temporarily be transformed to non-`string` representations during conversions
from one representation to another, but these representations will be local to the
conversion.

The set of available markup will likely be mostly static. The different markup
classes might become carrier for markup specific methods (e.g. escaping...). At
the current state of this proposal it is not clear if a shared `interface` for
`Markup` classes can or should have common methods or are just a tag.

```php
interface Markup
{
}

class HTML implements Markup
{
    public function restrictUsedTags(string $in, array $tags) : string; // for example
}
```

### Define Shapes for Text

These will be the workhorses for the toolset we propose. A shape bundles
information about markup and structuring options. It can produce text data from
raw string input and convert given data to other shapes. We expect that there
will be families of shapes that share most of their code via class hierarchies.
A markdown family, e.g., could contain various markdown shapes with the same
representation but different structuring options.

```php
interface Shape 
{
    /**
     * @throws \InvalidArgumentException if $text does not match format. 
     */
    public function toHTML($text) : HTMLText;

    /**
     * @throws \InvalidArgumentException if $text does not match format. 
     */
    public function toPlainText($text) : PlainText;

    public function getMarkup() : Markup;

    /**
     * @return mixed[] consts from Structure
     */
    public function getSupportedStructure() : array;

    public function fromString(string $text) : Text;
}

class MarkdownShape implements Shape
{
    /* will implement all Shape-methods except for `getSupportedStructure` */
}

class WordOnlyMarkdownShape extends SimpleDocumentMarkdownShape 
{
    /* will support bold and italics only */
}

class SimpleDocumentMarkdownShape extends MarkdownShape 
{
    /* will support paragraphs, headlines and lists on top */
}

/* ... */

```

### Define Classes for Text on top of Shapes

Since Shapes do not contain a concrete content, we currently could not hint on some
desired text and shape on interfaces. The `Shape`s and some concrete content thus
should be bundled to classes for text. These classes will mostly repeat the class
structure from families and wire up methods from there for ease and correctness of
usage.

To provide a future proof base for text handling, we propose to use a multibyte
representation for the texts in the string, hence according `mb_` string methods
should be used to process the raw strings.


```php
interface Text
{
    public function getShape() : Shape;
    public function getMarkup() : Markup;
    /**
     * @return TextStructure[]
     */
    public function getSupportedStructure() : array;
    public function toHTML() : HTMLText;
    public function toPlainText() : PlainText;
    public function getRawRepresentation() : string;
}

class MarkdownText implements Text
{
    /* ... */
}

class WordOnlyMarkdownText extends SimpleDocumentMarkdown
{
    /* ... */
}

class SimpleDocumentMarkdown extends MarkdownText 
{
    /* ... */
}

/* ... */

```


## Usage

Consumers of the tool set outlined above will mostly get in contact with the classes
for text. These can be used to define broad or narrow restrictions on texts that are
passed to certain components. This could look like this:

```php

class ilObject
{
    /* ... */
    public function setTitle(WordOnlyMarkdownText $title) : void;
    public function getTitle() : WordOnlyMarkdownText;
    /* ... */
}

class ilMail
{
    /* ... */
    public function setBody(SimpleDocumentMarkdownText *body) : void;
    /* ... */
}

```

There are some components that will want to work with the toolset more closely:
The UI components, e.g., are expected to make heavy use of the `Shapes` to build
inputs.


## Limitations

This proposal comes with known limitations:

* This is not looking to represent all of HTML. This is about texts (according to
  the definition given above), not HTML.
* This is not looking to represent all possibilities of the Page Editor. Instead
  we expect this to be used in components of the Page Editor.
* This is not looking to provide inputs for the various text shapes. This should
  be tackled in the UI framework.  Instead this proposal is looking to provide a
  toolset to talk about the shapes and their requirements to build said inputs.
* This is not looking to provide text processing capabilities that look into the
  actual content of texts. Things like spell checking are out of scope here.
* This is not looking to allow for arbitrary conversions between text shapes or
  markups. There are tools that are looking to do so, but these are complex projects
  in their own right.
* This is not looking to provide functionality for multilanguage support or
  localisation (as a special case of "looking into the actual content").


## Outlook

We propose the following course of action to implement this proposal:

* The general idea should be approved by the JF to document commitment in the
  community.
* A basic implementation to flash out the structure and check the approach could
  be made in the context of the efforts to create a `Markdown Input Field` for
  the UI framework.
* This documentation can then be updated accordingly.
* After the successfull implementation the approach should be disseminated at
  the ILIAS Dev Conf and possibly in other meetings. The adoption will then be
  up to maintainers.
* Additional shapes can be added according to the requirements arising by the
  components that adopt the approach.
