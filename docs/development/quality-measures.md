# Code Quality Measures

To address long-term technical debt in our codebase, component maintainers can propose targeted measures to improve the quality of their components. These measures are documented in our component overview, which also tracks key code quality metrics. This transparency enables stakeholders interested in improving overall code quality to fund specific initiatives with clearly defined objectives and measurable outcomes. Component maintainers can provide the relevant information in a `code_maintenance.json` file.

## File location

Place `code_maintenance.json` in the root directory of your component, for example:

```text
components/ILIAS/COPage/code_maintenance.json
```

## JSON format

The top-level `issues` object contains one entry for each issue. Each entry has
these fields:

- `title`: short issue title displayed by the overview.
- `desc`: description of the technical debt.
- `min` and `max`: estimated costs in euros, expressed as integers; the plugin displays and sums these estimates across the component's issues.

Issue object keys serve as stable identifiers for maintainers, but are not shown
in the overview.

For example:

```json
{
  "issues": {
    "reduce_large_classes": {
      "title": "Reduce large classes",
      "desc": "Split 26 classes over 500 lines into smaller units.",
      "min": 5000,
      "max": 10000
    },
    "increase_type_annotations": {
      "title": "Increase type annotations",
      "desc": "All method parameters and return values should be typed.",
      "min": 1000,
      "max": 2000
    }
  }
}
```

## Process

- You may add new entries to the list on the trunk branch at any time.
- Please remove entries as soon as they are fully funded. For partially funded measures, adjust the estimated costs to reflect the remaining funding requirements.
- Once a measure has been implemented, please contact the product manager and provide its title, actual costs, funding party, and a brief summary of the outcome. The product manager will then add it to the list of completed, funded measures.