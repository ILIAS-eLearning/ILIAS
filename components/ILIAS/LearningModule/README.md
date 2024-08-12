# Learning Modules

## Copy Process

- When pages, chapters or learning modules are copied, the media objects are re-used. This means that all instances will refer to the same shared object. This prevents large media files from to filling up disk space quickly.
- Questions will be not shared when pages are copied. Every page copy will get separate copies of a question.

## Reading Time

- The estimated reading time is language independent and always derived from the master language. (https://docu.ilias.de/goto_docu_wiki_wpage_6586_1357.html)

## Auto-Linked Glossaries

- If glossaries are linked to a learning module, internal links to found terms in the glossary will be created during text editing automatically when text is saved. This can be triggered for the whole content, when linking the glossary to the learning module. Once the links are created they are part of the content like manual created links. Removing a glossary from the auto-link list will not delete the links to the glossary terms in the content.

## Print View

- Print view selection uses a modal, see https://docu.ilias.de/goto_docu_wiki_wpage_5907_1357.html
- The print view opens in a separate tab, see https://mantis.ilias.de/view.php?id=20653

## Multilinguality and Questions

Avoid mixing these two concepts. The outcome will be most probably not what you desire.

The learning module component re-uses question from the T&A component. These questions do not support multilinguality yet. This means that using questions on multi-lingual pages are always completely separated instances. The question on the german page "does not know" that it is a copy of the version on the english page. Additionally there is nothing like a language-dependent learning progress in ILIAS. So activating the learning progress based on questions or navigation restrictions will also not work as expected in a multi-lingual learning module. 

