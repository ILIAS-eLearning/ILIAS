# Text Handling in ILIAS

For a detailed explanation look into the [according paper](../../../../../docs/development/text-handling.md).

This implementation currently focusses on the Markdown classes to make the proposal
in the paper accessible as code. This is missing implementations for HTML and PlainText
(and, possibly, other formats).

This simplified class diagram, which omits associations, aggregations, and compositions
for clarity, might help to understand the code.

```mermaid
classDiagram
direction BT

class Text {
    <<interface>>
    + getShape(): Shape
    + getMarkup(): Markup
    + getSupportedStructure(): Structure[]
    + toHTML(): Text\HTML
    + toPlainText(): Text\PlainText
    + getRawRepresentation(): string
}

class Shape {
    <<interface>>
    + fromString(text: string): Text
    + isRawStringCompliant(text: string): bool
    + toHTML(text: Text): Text\HTML
    + toPlainText(text: Text): Text\PlainText
    + getMarkup(): Markup
}

class Markup {
    <<interface>>
}

class Structure {
    <<enum>>
    + HEADING_1
    + HEADING_2
    + HEADING_3
    + HEADING_4
    + HEADING_5
    + HEADING_6
    + BOLD
    + ITALIC
    + UNORDERED_LIST
    + ORDERED_LIST
    + LINK
    + PARAGRAPH
    + BLOCKQUOTE
    + CODE
}

%% Classes start here
class Base {
    <<abstract>>
    + __construct(shape: Shape, raw: string)
    + getShape(): Shape
    + getMarkup(): Markup
    + getSupportedStructure(): Structure[]
    + toHTML(): Text\HTML
    + toPlainText(): Text\PlainText
    + getRawRepresentation(): string
}

class HTML {
    + __construct(html_text: string)
}

class PlainText {
    + __construct(plain_text: string)
}

class Markdown {
    + __construct(markdown_shape: Shape\Markdown, raw: string)
}

class SimpleDocumentMarkdown {
    + __construct(simple_document_markdown_shape: Shape\SimpleDocumentMarkdown, raw: string)
}

class WordOnlyMarkdown {
    + __construct(word_only_markdown_shape: Shape\WordOnlyMarkdown, raw: string)
}

class ShapeMarkdown {
    + __construct(markdown_to_html_transformation: Refinery\Transformation)
    + toHTML(text: Text): Text\HTML
    + toPlainText(text: Text): Text\PlainText
    + getMarkup(): Markup\Markdown
    + fromString(text: string): Text\Markdown
    + isRawStringCompliant(text: string): bool
}

class ShapeSimpleDocumentMarkdown {
    + getSupportedStructure(): Structure[]
    + fromString(text: string): Text\SimpleDocumentMarkdown
    + isRawStringCompliant(text: string): bool
}

class ShapeWordOnlyMarkdown {
    + getSupportedStructure(): Structure[]
    + fromString(text: string): Text\WordOnlyMarkdown
    + isRawStringCompliant(text: string): bool
}

class MarkupMarkdown

%% Relationships
Base <|.. Text
Markdown <|-- Base
SimpleDocumentMarkdown <|-- Markdown
WordOnlyMarkdown <|-- SimpleDocumentMarkdown
ShapeMarkdown <|.. Shape
ShapeSimpleDocumentMarkdown <|-- ShapeMarkdown
ShapeWordOnlyMarkdown <|-- ShapeSimpleDocumentMarkdown
MarkupMarkdown <|.. Markup
```

