<?php

namespace TYPO3\Jobqueue\Job;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "TYPO3.Jobqueue.Common". *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU General Public License, either version 3 of the   *
 * License, or (at your option) any later version.                        *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use Exception;
use DateTime;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\Jobqueue\Exception as JobQueueException;
use TYPO3\Jobqueue\Queue\Message;
use TYPO3\Jobqueue\Queue\QueueManager;

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
     * [$maxAttemps description].
     *
     * @var int
     */
    protected $maxAttemps;

    /**
     * @var TYPO3\Jobqueue\Configuration\ExtConf
     * @inject
     */
    protected $extConf;

    public function initializeObject()
    {
        $this->maxAttemps = (int) $this->extConf->getMaxAttemps();
    }

    /**
     * Put a job in the queue.
     *
     * @param string       $queueName
     * @param JobInterface $job
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
     * [delay description].
     *
     * @param string       $queueName [description]
     * @param int          $delay     [description]
     * @param JobInterface $job       [description]
     */
    public function delay($queueName, $delay, JobInterface $job)
    {
        $this->queue($queueName, $job, new DateTime('@'.time().(int) $delay));
    }

    /**
     * Wait for a job in the given queue and execute it
     * A worker using this method should catch exceptions.
     *
     * @param string $queueName
     * @param int    $timeout
     *
     * @return JobInterface The job that was executed or NULL if no job was executed and a timeout occured
     *
     * @throws JobQueueException
     */
    public function waitAndExecute($queueName, $timeout = null)
    {
        $queue = $this->queueManager->getQueue($queueName);
        $message = $queue->waitAndReserve($timeout);
        if ($message !== null) {
            try {
                $job = unserialize($message->getPayload());
                if ($job->execute($queue, $message)) {
                    $queue->finish($message);
                    return $job;
                } else {
                    throw new JobQueueException('Job execution for message "'.$message->getIdentifier().'" failed', 1334056583);
                }
            } catch (Exception $exception) {
                $attemps = $message->getAttemps() + 1;
                if ($attemps < $this->maxAttemps) {
                    $message->setAttemps($attemps);
                    $queue->finish($message);
                    $queue->publish($message);
                }
                throw $exception;
            }
        }
        return null;
    }

    /**
     * @param string $queueName
     * @param int    $limit
     *
     * @return array
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
}
