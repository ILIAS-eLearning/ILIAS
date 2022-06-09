# Missing  migration of File-Objects

With ILIAS 7, a migration of all files uploaded as file objects was introduced. Migrations require that they are always executed in the release in which they were introduced.

You found this README because the update to ILIAS 7 was cancelled because not all files were migrated. This can have different causes:

- The migration was performed out with ILIAS 7
- Not all files could be migrated in ILIAS 7

The easiest way to find file objects that were not migrated is via the database:

```sql 
SELECT file_id FROM file_data WHERE rid IS NULL OR rid = '';
```
If there are some which failed to migrate in ILIAS 7 you could set the `rid` for those entries to something like 'unknown'.

## The recommended procedure

Rollback your backup of the ILIAS installation. This means that the database as well as the file system must have the status before the attempted ILIAS 8 update. Then - if not already done - update the installation to the latest version of ILIAS 7. Then carry out all migrations, these can be listed with 

```bash 
sudo -uwww-data php setup/cli.php migrate
```

Detailed information on the procedure for the migrations can be found at the [README of the Setup](../../../../src/Setup#on-migration).

## Optional procedure

The optional procedure can be carried out at your own risk (as all operations in ILIAS, but at this case I'd highly recommend the recommended procedure). The migration for the file object is also available in ILIAS 8, but has not been tested. You can run it with

```bash 
sudo -uwww-data php setup/cli.php migrate --run ilFileObjectMigrationAgent.ilFileObjectToStorageMigration --steps=-1
```
