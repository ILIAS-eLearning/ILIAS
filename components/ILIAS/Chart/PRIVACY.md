# Chart Privacy

> **Disclaimer: This documentation does not guarantee completeness or accuracy. Please report any missing or incorrect information via [Pull Request](docs/development/contributing.md#pull-request-to-the-repositories).**

## General Information

The Chart component is a pure rendering library that generates visual chart representations
(grid charts, pie charts, and spider charts) using the jQuery Flot JavaScript library. It receives
numeric data points from other components and produces HTML and JavaScript output for display in
the browser. The Chart component itself does not interact with the database, does not store any
data, and does not handle user information directly.

Components that use Chart for visualization (e.g., Tracking, Wiki, SurveyQuestionPool,
Authentication) are responsible for their own data handling and access control. Any personal data
that may appear in a chart is managed entirely by the calling component.

## Integrated Components

The Chart component does not depend on any privacy-relevant ILIAS components. Its only
dependencies are core infrastructure utilities (template engine, jQuery initialization, string
utilities) that do not process personal data.

## Data being stored

This component does **not store any personal data**. It has no database tables, no file storage,
and no user preferences. All data processed by Chart exists only transiently in memory during
the rendering of a chart and is not persisted.

## Data being presented

This component does **not present any personal data** on its own. It renders numeric data series
as visual charts. Whether personal data appears in a chart depends entirely on the calling
component, which is responsible for its own permission checks and data access control.

## Data being deleted

This component does **not store any data**, so there is no data to delete. There are no deletion
methods, no trash lifecycle considerations, and no residual data.

## Data being exported

This component does **not provide any export functionality**. It only generates in-browser
chart visualizations. Any export of chart-related data is handled by the component that supplies
the data to Chart.
