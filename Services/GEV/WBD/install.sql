ALTER TABLE `hist_user` ADD `last_wbd_report` DATE NULL DEFAULT NULL AFTER `begin_of_certification` ;
ALTER TABLE `hist_usercoursestatus` ADD `last_wbd_report` DATE NULL DEFAULT NULL ;