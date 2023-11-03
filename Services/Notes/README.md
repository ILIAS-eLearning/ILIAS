# Notes Service

This component implements the ILIAS page editor as being used e.g. in learning modules, wikis, content pages in courses and other containers. This part of the documentation deals with concepts and business rules, for technical documentation see [README-technical.md](./README-technical.md).

## Business Rules
- The notes/comments overviews accessible via the dashboard list all repository objects that
  - are untrashed
  - the user has read permission to
  - have notes/comments of the user OR are favourites of the user and have at least one comment of any user
