<?php

namespace TYPO3\Jobqueue\Job;

/*                                                                        *
 * This script is part of the TYPO3 project - inspiring people to share!  *
 *                                                                        *
 * TYPO3 is free software; you can redistribute it and/or modify it under *
 * the terms of the GNU General Public License version 3 as published by  *
 * the Free Software Foundation.                                          *
 *                                                                        *
 * This script is distributed in the hope that it will be useful, but     *
 * WITHOUT ANY WARRANTY; without even the implied warranty of MERCHAN-    *
 * TABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General      *
 * Public License for more details.                                       *
 *                                                                        */

use Exception;
use DateTime;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\Jobqueue\Exception as JobQueueException;
use TYPO3\Jobqueue\Queue\Message;
use TYPO3\Jobqueue\Queue\QueueManager;
use TYPO3\Jobqueue\Job\JobInterface;
use TYPO3\Jobqueue\Command\JobCommandController;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;

/**
 * Job manager.
 */
class JobManager implements SingletonInterface
{
    /**
     * @var TYPO3\Jobqueue\Queue\QueueManager
     * @inject
     */
    protected $queueManager;

    /**
     * @var TYPO3\Jobqueue\Configuration\ExtConf
     * @inject
     */
    protected $extConf;

    /**
     * @var TYPO3\Jobqueue\Job\Worker
     * @inject
     */
    protected $worker;

    /**
     * @var int
     */
    protected $maxAttemps;

    /**
     * @return void
     */
    public function initializeObject()
    {
        $this->maxAttemps = (int) $this->extConf->getMaxAttemps();
    }

    /**
     * Put a job in the queue.
     *
     * @param string       $queueName   Queue name
     * @param JobInterface $job         The job
     * @param DateTime     $availableAt Time when the job is available for executing
     */
    public function queue($queueName, JobInterface $job, DateTime $availableAt = null)
    {
        $queue = $this->queueManager->getQueue($queueName);

        $payload = serialize($job);
        $message = new Message($payload);
        $message->setAvailableAt($availableAt);

        $queue->publish($message);
    }

    /**
     * Queues a job with a delay when it will be available for executing.
     *
     * @param string       $queueName Queue name
     * @param int          $delay     Delay in seconds
     * @param JobInterface $job       The job
     */
    public function delay($queueName, $delay, JobInterface $job)
    {
        $this->queue($queueName, $job, new DateTime('@' . (time() + (int) $delay)));
    }

    /**
     * Wait for a job in the given queue and execute it.
     * A worker using this method should catch exceptions.
     *
     * @param string $queueName
     * @param int    $timeout
     * @return JobInterface|null The job that was executed or null if no job was executed and a timeout occured
     * @throws JobQueueException
     */
    public function waitAndExecute($queueName, $timeout = null)
    {
        $queue = $this->queueManager->getQueue($queueName);
        $message = $queue->waitAndReserve($timeout);
        if ($message !== null) {
            $success = false;
            try {
                $job = unserialize($message->getPayload());
                $success = $job->execute($queue, $message);
            } catch (Exception $exception) {
                throw new JobQueueException('Job execution for "' . $message->getIdentifier() . '" threw an exception', 1446806185, $exception);
            } finally {
                $queue->finish($message);
                $attemps = $message->getAttemps() + 1;
                if ($attemps < $this->maxAttemps) {
                    $message->setAttemps($attemps);
                    $queue->publish($message);
                }
            }

            if ($success === false) {
                throw new JobQueueException('Job execution for message "' . $message->getIdentifier() . '" failed', 1334056583);
            }
            return $job;
        }
        return null;
    }

    /**
     * Returns the jobs without removing and executing them.
     *
     * @param string $queueName
     * @param int    $limit
     * @return array<TYPO3\Jobqueue\Job\JobInterface>
     */
    public function peek($queueName, $limit = 1)
    {
        $queue = $this->queueManager->getQueue($queueName);
        $messages = $queue->peek($limit);

        return array_map(function (Message $message) {
            $job = unserialize($message->getPayload());

            return $job;
        }, $messages);
    }

    /**
     * @return TYPO3\Jobqueue\Queue\QueueManager
     */
    public function getQueueManager()
    {
        return $this->queueManager;
    }


    public function __destruct()
    {
        $queues = $this->queueManager->getQueues();

        foreach ($queues as $queue) {
            if ($queue instanceof \TYPO3\Jobqueue\Queue\MemoryQueue) {
                $this->worker->work($queue->getName(), 0, Worker::LIMIT_QUEUE);
            }
        }
    }
}
