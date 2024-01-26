# Survey Privacy

This documentation does not warrant completeness or correctness. Please report any
missing or wrong information using the [ILIAS issue tracker](https://mantis.ilias.de)
or contribute a fix via [Pull Request](../../../docs/development/contributing.md#pull-request-to-the-repositories).

## Data being stored

When a user votes in a Poll, the IDs of the user, the Poll, and the answer
they voted for are stored as a triple. When the Poll allows for multiple
answers per participant, multiple such triples are stored per user and Poll.

## Data being presented

Users with the 'Edit Settings' permission in a Poll can access its Results
screen and see how many votes in total were cast per answer. Additionally,
when the Poll is in the mode 'With Names', they can also access an overview
over all participants in the poll, and which answers they voted for. In this
overview, users are identified by their login, as well as first and last name.

## Data being deleted

When a Poll is deleted from the system, all of the stored data is deleted
along with it, including how users voted in it. Additionally, in the 'Votes' subtab of the Results, all
votes can be deleted on their own.

## Data being exported

- XML exports of Polls do not contain any personal data.
- Results screens provide spreadsheet exports of the presented data.