# Former Cloud Module

This component only exists to provide a corresponding agent and DB updates to remove cloud data from ILIAS.

## TODOs

Remove more Data from Database, see https://github.com/ILIAS-eLearning/ILIAS/pull/7605

----

1. Remove objects from the repository

Build a sub-query:

```sql
SELECT ref_id
FROM object_data od
INNER JOIN object_reference objr ON objr.obj_id = od.obj_id
WHERE od.type = 'cld';
```

Use this query as sub-select for the following queries:

```sql
DELETE FROM tree WHERE child IN (?);
DELETE FROM object_reference WHERE ref_id IN (?);
```

**TODO: Afterwards, delete object type specific tables ...**

2. Remove object-type-based data

Determine the object type id and use it for ?:

```sql
SELECT obj_id FROM object_data WHERE type = 'typ' AND title = 'cld';
```

```sql
DELETE FROM rbac_ta WHERE typ_id = ?;
```

3. Clean up RBAC

Determine the operation id and use it for ?:

```sql
SELECT ops_id FROM rbac_operations WHERE class = 'create' AND operation = 'create_cld';
```

```sql
DELETE FROM rbac_operations WHERE ops_id = ?;
DELETE FROM rbac_templates WHERE ops_id = ?;
DELETE FROM rbac_ta WHERE ops_id = ?;
```

4. Remove object related settings

```sql
DELETE FROM settings WHERE keyword = 'obj_dis_creation_cld';
DELETE FROM settings WHERE keyword = 'obj_add_new_pos_cld';
DELETE FROM settings WHERE keyword = 'obj_add_new_pos_grp_cld';
```

5. Finally, delete the type

```sql
DELETE FROM object_data WHERE type = 'typ' AND title = 'cld';
```
