# Roadmap

## Streamline implementations for auto-linking

There are currently two autolinking implementations, the client side via linkifyjs and the server side ilUtil::makeClickable. This should be streamlined. A server side solution could use the refinery service.

## Revise Concepts and API

The service mixes internal link, permanent link and auto-linking feature.

The implementation is tightly coupled to COPage (page editor) and learning module or glossary concepts and around 20 years old.

A more generalised and decoupled concept should replace the current one in the future.
