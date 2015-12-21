********
Jobqueue
********

This extension provides a simple in-memory queue and a cli/scheduler command to execute jobs.



Configuration
-------------

In the extension settings you can set the default queue class.

You can define for each queue different settings over TYPO3_CONF_VARS.

.. code-block:: php
    $GLOBALS['TYPO3_CONF_VARS']['EXT']['jobqueue']['queueXyz'] = [
        'className' => 'VendorName\\ExtensionName\\Queue\\MyQueue',
        'options' => [
            // Options are passed into the constructor of the queue
        ],
    ];



Creating a job
--------------

Jobs must implement the ``TYPO3\Jobqueue\Job\JobInterface`` interface.

Jobs are getting serialized. It is recommended to use ``__sleep`` to exclude dependencies from serialization and ``__wakeup`` for inject them.



Queue a job
-----------

When you created a job you can add the job to a queue over the ``JobManager``.