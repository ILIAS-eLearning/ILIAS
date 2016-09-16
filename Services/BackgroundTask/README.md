# Service "BackgroundTask"

## In General

Whenever a long-running task has to be processed in ILIAS a background task should be used. This way the user can be informed about the progress and cancel the task at any point in time.
The presentation as a modal is done via JavaScript which polls the server for progress updates in certain intervals.

## Handler

A background task handler implements a specific type of background task, e.g. creating a zip archive. It abstracts the basic steps to process. 
While it can be a non-abstract implementation it might be beneficial to distinguish between type of background task and specific task, e.g. downloading a folder.
The folder download task is responsible for the GUI integration and gathering of files, while the parent zip handler is all about process and progress handling.

## Task

The handler inits a task and updates the progress information. 
This is done by simply calculating the overall steps needed on initialisation and writing the current step to the database whenever possible while processing.
The service will update the GUI progress bar accordingly.

## Json

The communication between client and server is done via Json. Use ilBackgroundTaskJson to generate valid Json messages which can be parsed by the JavaScript frontend.
See ilFolderDownloadBackgroundTaskHandler::init() for examples.