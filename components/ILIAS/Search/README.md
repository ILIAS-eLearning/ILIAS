# Search

## Media Objects and Content Snippets

Content Snippets and Media Objects are only found when using Lucene
search, and not direct search. They are displayed as sub-objects of
Media Pools. This is also the case in the 'Advanced Search', except
when searching via the 'Content' field: there Content Snippets and
Media Objects are also included in the direct search.

## Filtering behaviour
Handling of the search term input field and the UI Filter must be separated. 
A new search term is only applied when submitting the input field of the term.
When the filter is applied, no new search term will be aplied, but only the adjusted filter.
Vice versa, when a new search term will be submitted, changes in the filter will be left untouched.
