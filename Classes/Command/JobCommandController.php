<?php

namespace R3H6\Jobqueue\Command;

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

use R3H6\Jobqueue\Job\JobInterface;
use R3H6\Jobqueue\Job\Worker;
use R3H6\Jobqueue\Registry;
use TYPO3\CMS\Extbase\Mvc\Controller\CommandController;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Job command controller.
 */
class JobCommandController extends CommandController
{
    /**
     * @var \R3H6\Jobqueue\Job\JobManager
     * @inject
     */
    protected $jobManager = null;

    /**
     * @var \R3H6\Jobqueue\Registry
     * @inject
     */
    protected $registry = null;

    /**
     * @var \R3H6\Jobqueue\Domain\Repository\FailedJobRepository
     * @inject
     */
    protected $failedJobRepository = null;

    /**
     * Tries to (re)start a worker in a new process (EXPERIMENTAL!).
     *
     * @param  string  $id        daemon id [alphanumeric]
     * @param  string  $queueName the name of the queue to work on
     * @param  integer $timeout   time a queue waits for a job in seconds
     * @experimental
     */
    public function daemonCommand($id, $queueName, $timeout = 1)
    {
        $this->outputLine('<bg=yellow;options=bold>THIS IS AN EXPERIMENTAL FEATURE!</>');

        // Check if daemon is already running.
        $status = $this->registry->get('daemon:' . $id);
        if (is_array($status) && isset($status['pid'])) {
            if ($this->processExist($status['pid'])) {
                $this->outputFormatted('Daemon "%s" is already running in process "%s".', [$id, $status['pid']]);
                return;
            }
        }

        // Path to dispatcher.
        $cliDispatchPath = PATH_site . 'typo3/cli_dispatch.phpsh';

        // Test if a process can be started and the system gets the right pid.
        $command = 'exec php ' . $cliDispatchPath .' extbase job:sleep --id="' . $id . '"';
        $test = $this->processOpen($command);
        if ($test['pid'] == getmypid()) {
            throw new \Exception("Method getmypid fails", 1458897118);
        }
        $i = 0;
        while ($this->registry->get('daemon:' . $id) != $test['pid']) {
            if (++$i > 10) {
                throw new \Exception("Failed to verify the pid", 1458894146);
            }
            sleep(1);
        }
        if (!$this->processExist($test['pid'])) {
            throw new \Exception("Failed to verify that the test process is running", 1458896762);
        }

        // Open daemon process
        $command = 'exec php ' . $cliDispatchPath . ' extbase job:work --queue-name="' . $queueName . '" --timeout="' . $timeout . '" --limit="' . Worker::LIMIT_INFINITE . '"';
        if ($sleep !== null) {
            $command .= ' --sleep="' . $sleep. '"';
        }
        if ($memoryLimit !== null) {
            $command .= ' --memory-limit="' . $memoryLimit. '"';
        }
        $status = $this->processOpen($command);
        $this->registry->set('daemon:' . $id, $status);
        $this->outputFormatted('Daemon "%s" started in a new process "%s".', [$id, $status['pid']]);
    }

    /**
     * Opens a new process for a given command.
     * @param  string $command to open
     * @return array           process status
     * @throws \RuntimeException
     * @experimental
     */
    protected function processOpen($command)
    {
        $pipes = [];
        $descriptorspec = [];
        $process = proc_open($command, $descriptorspec, $pipes);
        if ($process === false) {
            throw new \RuntimeException(sprintf('Could not open process "%s"!', $command), 1458849054);
        }
        $status = proc_get_status($process);
        if ($status === false || empty($status['pid'])) {
            throw new \RuntimeException('Could not get process status!', 1458849124);
        }
        $status['crdate'] = time();
        return $status;
    }

    /**
     * Checks if a process for given pid exists.
     *
     * @param  string $pid process id
     * @return boolean
     * @experimental
     */
    protected function processExist($pid)
    {
        exec(sprintf("ps -p %s", $pid), $output);
        return (count($output) > 1);
    }

    /**
     * Zzz... (INTERNAL!).
     *
     * This command is used by the daemon command for testing purposes.
     *
     * @param string $id
     * @cli
     * @internal
     */
    public function sleepCommand($id)
    {
        $this->registry->set('daemon:' . $id, getmypid());
        sleep(10);
    }

    /**
     * Sends signal for stop all running daemon processes.
     * @cli
     * @experimental
     */
    public function killCommand()
    {
        $this->registry->set(Registry::DAEMON_KILL_KEY, time());
        $this->outputLine('Broadcast kill signal');
    }

    /**
     * Work on a queue and execute jobs.
     *
     * @param  string  $queueName the name of the queue to work on
     * @param  integer $timeout   time a queue waits for a job in seconds
     * @param  integer $limit     number of jobs to be done, -1 for all jobs in queue, 0 for work infinite
     * @see JobCommandController::ARG_ALL_QUEUES
     * @todo Exception handling
     */
    public function workCommand($queueName, $timeout = 0, $limit = Worker::LIMIT_QUEUE)
    {
        $this->outputLine('work...');
        /** @var R3H6\Jobqueue\Job\Worker $worker */
        $worker = $this->objectManager->get(Worker::class);
        $worker->work($queueName, $timeout, $limit);
    }

    /**
     * List jobs in a queue.
     *
     * @param string $queueName The name of the queue to work on
     * @param int    $limit     Number of jobs to list
     * @cli
     */
    public function listCommand($queueName, $limit = 1)
    {
        $jobs = $this->jobManager->peek($queueName, $limit);
        $totalCount = $this->jobManager->getQueueManager()->getQueue($queueName)->count();

        $this->outputFormatted('List jobs for queue "%s"...', [$queueName]);

        foreach ($jobs as $job) {
            $this->outputLine('<u>%s</u>', array($job->getLabel()));
        }

        if ($totalCount > count($jobs)) {
            $this->outputLine('(%d omitted) ...', array($totalCount - count($jobs)));
        }
        $this->outputLine('(<b>%d total</b>)', array($totalCount));
    }

    /**
     * Prints information about a queue.
     *
     * @param string $queueName The name of the queue to work on
     * @cli
     */
    public function infoCommand($queueName)
    {
        $queue = $this->jobManager->getQueueManager()->getQueue($queueName);
        $options = $queue->getOptions();

        $this->outputFormatted('Information for queue "%s"...', [$queueName]);
        $this->outputFormatted('<b>Class:</b> %s', [get_class($queue)]);

        if (is_array($options) && !empty($options)) {
            foreach ($options as $key => $value) {
                $this->outputFormatted('<b>%s:</b> %s', [ucfirst($key), ($value === null) ? 'null': $value]);
            }
        }
    }

    /**
     * List failed jobs.
     *
     * @param  string $queueName only list failures for this queue
     * @cli
     */
    public function failuresCommand($queueName = null)
    {
        if ($queueName === null) {
            $failedJobs = $this->failedJobRepository->findAll();
        } else {
            $failedJobs = $this->failedJobRepository->findByQueueName($queueName);
        }

        foreach ($failedJobs as $failedJob) {
            $this->outputFormatted('Queue "%s" at %s', [$failedJob->getQueueName(), $failedJob->getCrdate()->format('d.m.Y H:i:s')]);
        }
    }
}
