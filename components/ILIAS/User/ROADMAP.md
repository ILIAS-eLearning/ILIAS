# Roadmap
- All the steps in this roadmap depend on the availability of funding for
refactoring.
- There are currently no new features on the roadmap.

## Short Term

### Remove Temporary UDF Field ID Mapping Table

With ILIAS 11, custom user field identifiers were migrated from integer IDs
to UUIDs. The temporary table `udf_field_id_map` stores the mapping from
legacy integer IDs to the new UUIDs so plugins can update their own stored
references after the update.

Once plugins have had sufficient time to migrate (target: ILIAS 12), this
table MUST be dropped via a database update step and this roadmap entry
can be removed.

## Mid Term
- Refactor User Actions
- Apply Sustainability Package from PHP8-Refactoring

## Long Term
In the Long Term Services\User will be moved to a more collaborative
maintenance model. A more encompasing refactoring aims to facilitate this and
provide an easy to understand Service.