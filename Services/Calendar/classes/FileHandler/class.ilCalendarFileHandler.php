<?php
/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Calendar file handler
 * @author  Alex Killing <killing@leifos.de>
 * @version $Id$
 * @ingroup ServicesCalendar
 */
class ilCalendarFileHandler
{
    
    
    /**
     * Constructor
     */
    public function __construct()
    {
    }

    /**
     * Download files for events
     *
     * @param
     */
    public function downloadFilesForEvents($a_events)
    {
        include_once("./Services/Calendar/classes/FileHandler/class.ilAppointmentFileHandlerFactory.php");
        foreach ($a_events as $event) {
            $fh = ilAppointmentFileHandlerFactory::getInstance($event);
            foreach ($fh->getFiles() as $file) {
                // just for demonstration we provide only the last file
                $last_file = $file;

                // @todo: collect all files
            }

            // @todo: copy zip all files using background

            /*
                        global $DIC;

                        $factory = $DIC->backgroundTasks()->taskFactory();
                        $taskManager = $DIC->backgroundTasks()->taskManager();

                        // We create a bucket that will be scheduled and set the user that should observe the bucket.
                        $bucket = new BasicBucket();
                        $bucket->setUserId($DIC->user()->getId());

                        // Combine the tasks. This will create a task that looks like this: (1 + 1) + (1 + 2).
                        include_once("./Services/Calendar/FileHandler/classes/class.ilCalFileZipJob.php");
                        $a = $factory->createTask(ilCalFileZipJob::class, [$last_file]);
                        // Note the integer 1 will automatically be wrapped in a IntegerValue class. All scalars can be wrapped automatically.
                        // The above is the same as:
                        // $a = $factory->createTask(PlusJob::class, [new IntegerValue(1), new IntegerValue(1)]);
                        //$b = $factory->createTask(PlusJob::class, [1, 2]);
                        //$c = $factory->createTask(PlusJob::class, [$a, $b]);

                        // The last task is a user interaction that allows the user to download the result calculated above.
                        $userInteraction = $factory->createTask(DownloadInteger::class, [$c]);

                        // We put the combined task into the bucket and add some description
                        $bucket->setTask($userInteraction);
                        $bucket->setTitle("Some calculation.");
                        $bucket->setDescription("We calculate 5!");

            // We schedule the task.
                        $taskManager->run($bucket);

            // We redirect somewhere.
                        $this->ctrl->redirect($this, "showContent");
            */




            // just for demonstration: send last file
            if (is_file($last_file)) {
                require_once('./Services/FileDelivery/classes/class.ilFileDelivery.php');
                global $DIC;
                $ilClientIniFile = $DIC['ilClientIniFile'];

                $ilFileDelivery = new ilFileDelivery($last_file);
                $ilFileDelivery->setDisposition(ilFileDelivery::DISP_ATTACHMENT);
                //$ilFileDelivery->setMimeType($this->guessFileType($file));
                $ilFileDelivery->setConvertFileNameToAsci((bool) !$ilClientIniFile->readVariable('file_access', 'disable_ascii'));
                //$ilFileDelivery->setDownloadFileName();
                $ilFileDelivery->deliver();
                exit;
            }
        }
    }
}
