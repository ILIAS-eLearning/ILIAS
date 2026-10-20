# Work Item: ashes-dashboard

> `0027-ashes-dashboard` — created 2026-10-20

## Goal

Plan a maintainable, dependency-injected Ashes plugin dashboard that becomes the default configuration view and presents every existing suite tool as a Delos-styled, SVG-backed card.

## Original Input

<!-- Verbatim user message. Do not edit, summarize, paraphrase, or delete anything. -->

Ashes-Plugin: New Feature "Dashboard".

A new "Dashboard"-feature should be planned.

- The dashboard MUST be the default view when entering the configuration of the Ashes plugin
- While it is not a real tool, it could be put in a dedicated GUI-lass
- A main bar item (main item and sub-item) MUST be offered, similar to the other tools.
- The dashboard MUST provide a "Deck of Cards" with a "Card" for each tool in our suite, where a tool is to be understood as all which is summarized under a Main Tab (except the dashboard itself).
- A meaningfull SVG image (they MUST be part of the plugin/tool directories) MUST be created/used, which symbolizes the respective tool and its features (it MUST be a summarized graphical representation of the each tool). Please align the requiredments of such SVGs accoding to our default delos icon style.
- The dashboard feature MUST use the UI framework where possible.
- The dashboard MUST NOT use static methods and MUST use dependency injection instead.
- The dashboard code and paradigms MUST be align to those of other tools.
- Please use S.O.L.I.D principles and avanced OOP techniques and patterns, as well as Domain Driven Design language.
