# Roadmap

## Short Term

- Move the adapter serving `ILIAS\UI\Storage` from `Authentication` into `UI`,
  so that the UI owns the namespace and the keys it stores under.

## Mid Term

- A scope for per-user state, with the subject as a parameter
  (`forUser($user_id, ['my_component', 'view_state'])`) rather than encoded into
  the namespace or the key. It needs a table with `usr_id` in the primary key,
  contributed by `User`, which clears it on the `deleteUser` event.
- Let a store list the keys it holds, so that stored state can be inspected and
  cleaned up.

## Long Term

- Replace the container lookup in `ILIAS\Database\LazyConnection` once the
  Database component offers `ilDBInterface` through the component bootstrap.
- Reconsider whether the persistent scope should stay a single global table once
  more consumers exist.
