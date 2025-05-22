# Roadmap

## Short Term

### GET/POST handling

- replace GET/POST access by using request objects
  - ILIAS 6
    - Presentation centralises most of request access in `ilLMPresentationRequest`
    - Editing missing (and view remaining cases in presentation)

### Remove Public Access Exports From DB

In the table `content_object`, the following columns can be removed:

- `downloads_public_active`
- `downloads_active`
- `public_xml_file`
- `public_html_file`

## Mid Term

### Legacy Templates > KS elements

The presentation uses some legacy templates, e.g. ilLMContentRendererGUI. This should be moved to KS elements where possible.

### Refactor Editing Clipboard

- The editing cliboard uses static calls to `ilEditClipboard` and features in `ilUser`. This should be moved to one decent repo class in `ILIAS/LearningModule`.

### Improve Architecture

- Introduce repository pattern
- Improve DI handling
- Factor business logic out of UI classes

## Long Term
