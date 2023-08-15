# Roadmap

## Short Term

## Mid Term

- Add a decent internal service structure
- Add documentation for external service usage
- Improve DI handling to get more code under unit tests
- Use namespaces

### Filter / Taxonomy Side Block

The interaction of the taxonomy selection block in the repository is strange especially if an additional filter is used, e.g. the taxonomies do not react on filter collapsing or deactivation. During a workshop [1] we discussed the use of both features and gained some insights and found multiple alternative ways to proceed.

General Insights [1]:
- The taxonomy side block act as a filter. This should be made clearer in naming of the option (settings) and the presentation.
- We checked a scenario, that made use of taxonomies without using filters, which showed some advantages:
  - Users get an immediate overview on the classification.
  - No need to activate a filter, activate single filter elements or select options in previously hidden dropdowns/overlays.
- Unfortunatley, no participant could provide a "filter only" use case.

Alternatives
- A Keeping the filter element
  - A.1 Prevent the simultaneous activation of filter and taxonomy side block in the settings (short term). We are seeing this as a good short term solution to prevent the issues when activating both. Other short term changes should include the improvement of the option and info texts and the visual presentation of the taxomonies in the side block.
  - A.2 Full integration of the taxonomies in the filter input component, remove of the taxonomy side blocks (long term). Unfortunately this would disimprove the only well working scenario we checked (taxonomy only use), see "General Insights".
  - A.3 Integration of the taxonomies into the filter, but only, if the both are activated (long term). If only the taxonomy is used, the presentation is done as side block. This might be a solution, but has some disadvantages in understandability for users when managing the settings (why do side blocks dissappear when the filter is activated?). Also the technical effort to support both visualisations would be at least of medium level.
  - A.4 Subordination of the taxonomy side block to the filter. I.E. we need to conceptualise the behaviour and visualisation of the taxonomy side block depending on the filter state. E.g. what happens if the filter is deactivated, how is this visualised in the side block, and so on. We discarded this alternative due to complexity in specification, visualisation and technical interface.
- B Replacing the filter element
  - B.1 Replace the filter by a search (simple text field) input on top and provide optional metadata / taxonomy side blocks for additional filtering (long term). This would allow to have an "amazon"-like user experience. This might meet user expectations in many scenarios when looking for specific things in a large set of items.

Current plan is to implement A.1 and further discuss the long term alternatives in subsequent workshops.

[1] Workshop held on 11 Aug 2023 on repository taxonomy and filter.