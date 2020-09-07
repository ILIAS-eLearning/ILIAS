<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Cron job for definition for oer harvesting
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 *
 */
class ilOerHarvester
{
    /**
     * @var \ilLogger
     */
    private $logger = null;

    /**
     * @var \ilCronJobResult
     */
    private $cronresult = null;


    /**
     * @var ilOerHarvesterSettings
     */
    private $settings = null;

    /**
     * ilOerHarvester constructor.
     * @param \ilCronJobResult $result
     */
    public function __construct(ilCronJobResult $result)
    {
        global $DIC;

        $this->logger = $DIC->logger()->meta();
        $this->cronresult = $result;
        $this->settings = ilOerHarvesterSettings::getInstance();
    }

    /**
     * run harvester
     * @return ilCronJobResult
     */
    public function run()
    {
        try {
            $obj_ids = $this->collect();
            $obj_ids = $this->filter($obj_ids);
            $num = $this->harvest($obj_ids);

            $message = 'Created ' . $num . ' new objects. <br />';

            $deleted = $this->deleteDeprecated();

            $message .= 'Deleted ' . $deleted . ' deprecated objects.';

            if (!$deleted && !$num) {
                $this->cronresult->setStatus(ilCronJobResult::STATUS_NO_ACTION);
            } else {
                $this->cronresult->setStatus(ilCronJobResult::STATUS_OK);
            }
            $this->cronresult->setMessage($message);
            return $this->cronresult;
        } catch (Exception $e) {
            $this->cronresult->setStatus(ilCronJobResult::STATUS_FAIL);
            $this->cronresult->setMessage($e->getMessage());
            return $this->cronresult;
        }
    }

    /**
     * Collect all obj_ids with copyright settings which are collectable.
     * @return int[]
     */
    protected function collect()
    {
        $collectable_types = $this->settings->getHarvestingTypes();
        $copyright_ids = $this->settings->getCopyRightTemplatesInLomFormat();

        $collectable_obj_ids = ilMDRights::lookupRightsByTypeAndCopyright(
            $collectable_types,
            $copyright_ids
        );

        $this->logger->debug('Found ' . count($collectable_types) . ' collectable objects.');
        $this->logger->dump($collectable_obj_ids, ilLogLevel::DEBUG);

        return $collectable_obj_ids;
    }

    /**
     * @param int[] $a_collectable_obj_ids
     */
    protected function filter($a_collectable_obj_ids)
    {
        global $DIC;

        $tree = $DIC->repositoryTree();

        $filtered = [];
        foreach ($a_collectable_obj_ids as $obj_id) {
            $status = new ilOerHarvesterObjectStatus($obj_id);
            if ($status->isCreated()) {
                $this->logger->debug('Object already created: ' . $obj_id);
                continue;
            }
            if ($status->isBlocked()) {
                $this->logger->debug('Object creation is blocked: ' . $obj_id);
                continue;
            }

            $exists = false;
            foreach (ilObject::_getAllReferences($obj_id) as $ref_id => $tmp) {
                if (!$tree->isDeleted($ref_id)) {
                    $exists = true;
                }
            }
            if (!$exists) {
                $this->logger->notice('Ignoring deleted object: ' . $obj_id);
                continue;
            }
            $filtered[] = $obj_id;
        }

        $this->logger->debug('Result after filtering.');
        $this->logger->dump($filtered, ilLogLevel::DEBUG);

        return $filtered;
    }

    /**
     * @param int[] $a_collectable_obj_ids
     * @return bool
     */
    protected function harvest($a_collectable_obj_ids)
    {
        $num = 0;
        foreach ($a_collectable_obj_ids as $obj_id) {
            $ref_ids = ilObject::_getAllReferences($obj_id);
            $ref_id = end($ref_ids);

            $object = ilObjectFactory::getInstanceByRefId($ref_id, false);

            if (!$object instanceof ilObject) {
                $this->logger->warning('Found invalid reference: ' . $ref_id);
            }
            $this->logger->debug('Creating new reference for object: ' . $obj_id);
            $this->harvestObject($object);
            $num++;
        }
        return $num;
    }

    /**
     * Harvest object
     * @param $ref_id
     */
    protected function harvestObject(ilObject $object)
    {
        $this->logger->debug('Create new reference');
        $new_ref_id = $object->createReference();
        $this->logger->debug('Put in tree');
        $object->putInTree($this->settings->getTarget());
        $this->logger->debug('Set pernissions');
        $object->setPermissions($this->settings->getTarget());

        $this->logger->debug('Set status');
        $status = new ilOerHarvesterObjectStatus($object->getId());
        $status->setHarvestRefId($new_ref_id);
        $status->setBlocked(false);
        $status->save();

        return true;
    }

    /**
     * Delete object
     */
    protected function deleteObject($a_ref_id)
    {
        $object = ilObjectFactory::getInstanceByRefId($a_ref_id, false);

        if (!$object instanceof ilObject) {
            $this->logger->warning('Found invalid reference: ' . $a_ref_id);
            return false;
        }
        $this->logger->debug('Deleting reference...');
        $object->delete();


        $status = new ilOerHarvesterObjectStatus(
            ilOerHarvesterObjectStatus::lookupObjIdByHarvestingId($a_ref_id)
        );
        $status->delete();
    }

    /**
     * Delete deprecated
     */
    protected function deleteDeprecated()
    {
        $num_deleted = 0;
        foreach (ilOerHarvesterObjectStatus::lookupHarvested() as $ref_id) {
            $obj_id = ilObject::_lookupObjId($ref_id);

            // blocked items are always deleted
            $status = new ilOerHarvesterObjectStatus($obj_id);
            if ($status->isBlocked()) {
                $this->logger->debug('Deleting blocked object ressource.');
                $this->deleteObject($ref_id);
                $num_deleted++;
                continue;
            }

            $copyright = ilMDRights::_lookupDescription($obj_id, $obj_id);
            $is_valid = false;
            foreach ($this->settings->getCopyRightTemplatesInLomFormat() as $cp) {
                if (strcmp($copyright, $cp) === 0) {
                    $is_valid = true;
                }
            }

            if (!$is_valid) {
                $this->logger->debug('Deleting deprecated object with ref_id: ' . $ref_id);
                $this->deleteObject($ref_id);
                $num_deleted++;
            }
        }
        return $num_deleted;
    }
}
