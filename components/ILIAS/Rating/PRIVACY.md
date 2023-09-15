# Rating Service Privacy

This documentation does not warrant completeness or correctness. Please report any
missing or wrong information using the [ILIAS issue tracker](https://mantis.ilias.de)
or contribute a fix via [Pull Request](../../docs/development/contributing.md#pull-request-to-the-repositories).

## Data being stored

- Each **rating** stores the **user ID** (creator) of the rating, the referenced **object** and a timestamp of the last change in the database.

## Data presentation

- ILIAS presents the **own ratings** of the user on various (sub-)object screens, e.g. learning modules or wiki pages.
- ILIAS also shows an **arithmetic mean and total number of ratings of other users** at the same place.
- **Usually user names are not presented** with single ratings, but some contexts do (e.g. peer reviews in exercises). The privacy information of the consuming components should list these cases.  
- In a context with a small user base, other users may be able to guess the other users ratings (e.g. a small group with similar ratings).

## Data Deletion

- Individual ratings may be removed by the user from objects.

## Data Export

- Rating information cannot be exported.