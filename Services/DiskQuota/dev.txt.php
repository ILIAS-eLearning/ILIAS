<?php exit; ?>

Table:
il_disk_quota:
- owner_id (pk)
- src_type (pk)
- src_obj_id (pk)
- src_size

DB Update Initialization:
#3903 - #3905

Update/Insert entry for an object:
ilDiskQuotaHandler::handleUpdatedSourceObject($a_src_obj_type, $a_src_obj_id, $a_src_filesize, $a_owner_obj_ids = null, $a_is_prtf = false)
- calls ilDiskQuotaHandler::handleEntry($a_owner_id, $a_src_obj_type, $a_src_obj_id, $a_src_filesize);

Delete entry for an object
ilDiskQuotaHandler::deleteEntry($a_owner_id, $a_src_obj_type, $a_src_obj_id);

Get total file size for a user:
ilDiskQuotaHandler::getFilesizeByOwner($user_id);
