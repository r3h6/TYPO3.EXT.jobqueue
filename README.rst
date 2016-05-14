.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.


.. _start:

=============
Documentation
=============

Job queues for TYPO3 CMS. This extension provides a simple in-memory queue and a cli or scheduler command to execute jobs.

This extension is a backport of the flow package Flowpack/jobqueue-common.

.. note::
    You should install also one of the following extensions ``jobqueue_database`` or ``jobqueue_beanstalkd``.


Configuration
-------------

In the extension settings you can set the default queue class.

You can define for each queue different settings over TYPO3_CONF_VARS.

.. code-block:: php

    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['jobqueue']['myQueueName'] = [
        'className' => 'TYPO3\\JobqueueBeanstalkd\\Queue\\BeanstalkdQueue',
        'options' => [
            // Options are passed into the constructor of the queue
        ],
    ];


Creating a job
--------------

Jobs must implement the ``TYPO3\Jobqueue\Job\JobInterface`` interface which extends ``Serializable`` itself.

.. tip::
    Jobs are getting serialized. It is recommended to serialize only data and no dependencies because queues could have a data limit.

``Example:``

.. code-block:: php

        <?php
        namespace Vendor\ExtName\Job;
        class MyJob implements \TYPO3\Jobqueue\Job\JobInterface
        {
            protected $identifier;
            protected $label = 'My job';
            public function __construct($identifier)
            {
                $this->identifier = $identifier;
            }
            public function execute(\TYPO3\Jobqueue\Queue\QueueInterface $queue, \TYPO3\Jobqueue\Queue\Message $message)
            {
                // Do the job...
                return true;
            }
            public function getIdentifier(){ return $this->identifier; }
            public function getLabel(){ return $this->label; }
            public function serialize()
            {
                // You must take care of serialization by yourself!
                return serialize([$this->identifier]);
            }
            public function unserialize($data)
            {
                // You must take care of unserialization by yourself!
                call_user_func_array([$this, '__construct'], unserialize($data));
            }
        }


Queue a job
-----------

When you created a job you can add the job to a queue over the ``JobManager``.

.. code-block:: php

        $myJob = new \Vendor\ExtName\Job\MyJob();
        $this->jobManager->queue('myQueueName', $myJob);


Executing a job
---------------

You can execute jobs over the ExtBase scheduler task "Jobqueue Job: work" or the cli command "extbase job:work".

Daemon
^^^^^^

You can try to use the experimental scheduler task "Jobqueue Job: daemon".
If you are using something like "upstart" you should call the cli command "extbase job:work" with "--limit=-1".


Commands
--------

``extbase jow:work``

:$queueName:
    The name of the queue to work on.

:$timeout:
    Seconds to wait for a job in the queue.

:$limit:
    Number of jobs to be done, -1 for all jobs in queue, 0 for work infinite


Differences to the flow package
-------------------------------

* Namespace
* Jobs must satisfy also the ``Serializable`` interface.