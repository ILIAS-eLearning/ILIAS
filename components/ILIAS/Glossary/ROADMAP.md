# Roadmap

## Short Term

### Remove Public Access Exports From DB

In the table `glossary`, the following columns can be removed:

- `downloads_active`
- `public_xml_file`
- `public_html_file`

## Mid Term

### Use central Online/Offline code

Glossary still has its own online field in table "glossary". The object service should be used instead.

### Improve Architecture

- Introduce repository pattern
- Improve DI handling
- Factor business logic out of UI classes

## Long Term
