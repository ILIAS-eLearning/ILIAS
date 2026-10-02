# Roadmap

## Short Term

- Move the adapter serving `ILIAS\UI\Storage` from `Authentication` into `UI`,
  so that the UI owns the namespace and the keys it stores under.

## Long Term

- Reconsider whether the persistent scope should stay a single global table once
  more consumers exist.
