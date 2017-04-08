.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.


.. _start:

.. image:: https://travis-ci.org/r3h6/TYPO3.EXT.jobqueue.svg?branch=master
    :target: https://travis-ci.org/r3h6/TYPO3.EXT.jobqueue

=============
Documentation
=============

Job queues for TYPO3 CMS. This extension provides a simple in-memory queue and a cli or scheduler command to execute jobs.

This extension is a backport of the flow package `Flowpack/jobqueue-common <https://github.com/Flowpack/jobqueue-common/>`_.

.. note::

   You should install also one of the following extensions **jobqueue_database**, **jobqueue_beanstalkd** or **jobqueue_redis**.


Configuration
-------------

In the extension settings you can set the default queue class.

You can define for each queue different settings over TYPO3_CONF_VARS.

**Example:**

.. code-block:: php

   $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['jobqueue']['myQueueName'] = [
      'className' => 'R3H6\\JobqueueBeanstalkd\\Queue\\BeanstalkdQueue',
      'options' => [
         // Options are passed into the constructor of the queue
      ],
   ];


Creating a job
--------------

Jobs must implement the ``R3H6\Jobqueue\Job\JobInterface`` interface which extends ``Serializable`` itself.

.. note::

   Jobs are getting serialized. It is recommended to serialize only data and no dependencies because queues could have a data limit.

**Example:**

.. code-block:: php

   <?php
   namespace Vendor\ExtName\Job;
   class MyJob implements \R3H6\Jobqueue\Job\JobInterface
   {
      protected $identifier;
      protected $label = 'My job';
      public function __construct($identifier)
      {
         $this->identifier = $identifier;
      }
      public function execute(\R3H6\Jobqueue\Queue\QueueInterface $queue, \R3H6\Jobqueue\Queue\Message $message)
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

**Example:**

.. code-block:: php

   $myJob = GeneralUtility::makeInstance(\Vendor\ExtName\Job\MyJob::class, 'test');
   $jobManager = GeneralUtility::makeInstance(ObjectManager::class)->get(JobManager::class);
   $jobManager->queue('myQueueName', $myJob);


Executing a job
---------------

You can execute jobs over the ExtBase scheduler task "Jobqueue Job: work" or the cli command "extbase job:work".

Daemon
^^^^^^

You can try to use the experimental scheduler task "Jobqueue Job: daemon".
If you are using something like "upstart" you should call the cli command "extbase job:work" with "--limit=0".


Commands
--------

``typo3/cli_dispatch.phpsh extbase jow:work --queue-name --timeout --limit``

:$queueName:
   The name of the queue to work on.

:$timeout:
   Seconds to wait for a job in the queue.

:$limit:
   Number of jobs to be done, -1 for all jobs in queue, 0 for work infinite


Signal and Slots
----------------

.. t3-field-list-table::

 - :Class:
      Signal Class Name
   :Name:
      Signal Name
   :Method:
      Located in Method
   :Arguments:
      Passed arguments
   :Description:
      Description

 - :Class:
      R3H6\\Jobqueue\\Job\\JobManager
   :Name:
      jobFailed
   :Method:
      waitAndExecute()
   :Arguments:
      $queueName, R3H6\Jobqueue\Queue\Message $message
   :Description:
      Dispatched when a job fails and reached the max attemps.


Differences to the flow package
-------------------------------

* Namespace
* Jobs must satisfy also the ``Serializable`` interface.


Contributing
------------

Bug reports and pull request are welcome through `GitHub <https://github.com/r3h6/TYPO3.EXT.jobqueue/>`_.