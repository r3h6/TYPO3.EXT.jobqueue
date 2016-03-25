<?php

namespace TYPO3\Jobqueue\Command;

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
use TYPO3\Jobqueue\Job\JobInterface;
use TYPO3\Jobqueue\Job\Worker;
use TYPO3\Jobqueue\Registry;
use TYPO3\CMS\Extbase\Mvc\Controller\CommandController;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Job command controller.
 */
class JobCommandController extends CommandController
{
    // Argument 'all' for work command.
    const ARG_ALL_QUEUES = 'all';

    /**
     * @var \TYPO3\Jobqueue\Job\JobManager
     * @inject
     */
    protected $jobManager;

    /**
     * @var \TYPO3\Jobqueue\Registry
     * @inject
     */
    protected $registry;

    /**
     * Experimental command
     *
     * @param  int  $id       Daemon id
     * @param  string  $queueName [description]
     * @param  integer $timeout   [description]
     */
    public function daemonCommand($id, $queueName, $timeout = 0)
    {
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            throw new \BadFunctionCallException("Command 'job:daemon' is not available on windows systems", 1458844709);
        }

        $cliDispatchPath = PATH_site . 'typo3/cli_dispatch.phpsh';

        // Check if daemon is already running.
        $status = $this->registry->get('daemon:' . $id);
        if (is_array($status) && isset($status['pid'])) {
            if ($this->processExist($status['pid'])) {
                $this->outputFormatted('Daemon "%s" is already running in process "%s".', [$id, $status['pid']]);
                return;
            }
        }

        // Test if a process can be started and the system gets the right pids.
        $command = "exec php $cliDispatchPath extbase job:test --id=\"$id\"";
        $test = $this->processOpen($command);
        if ($test['pid'] == getmypid()) {
            throw new \Exception("Daemon not started because pid's are same", 1458897118);
        }
        $i = 0;
        while ($this->registry->get('pid:' . $id) != $test['pid']) {
            if (++$i > 10) {
                throw new \Exception("Daemon not started because the system failded to verify the pid's", 1458894146);
            }
            sleep(1);
        }
        if (!$this->processExist($test['pid'])) {
            throw new \Exception("Daemon not started because the system failded to verify the test process", 1458896762);
        }

        // Open daemon process
        $command = "exec php $cliDispatchPath extbase job:work --queue-name=\"$queueName\" --timeout=\"$timeout\" --daemon";
        $status = $this->processOpen($command);
        $this->registry->set('daemon:' . $id, $status);
        $this->outputFormatted('Daemon "%s" started in a new process "%s".', [$id, $status['pid']]);
    }

    protected function processOpen($command)
    {
        $pipes = [];
        $descriptorspec = [];
        $process = proc_open($command, $descriptorspec, $pipes);
        if ($process === false) {
            throw new \RuntimeException(sprintf('Could not open command "%s"!', $command), 1458849054);
        }
        $status = proc_get_status($process);
        if ($status === false) {
            throw new \RuntimeException('Could not get status!', 1458849124);

        }
        return $status;
    }

    protected function processExist($pid)
    {
        exec(sprintf("ps -p %s", $pid), $output);
        return (count($output) > 1);
    }

    /**
     * Test (Internal command only)
     * @param int $id Daemon id
     * @cli
     */
    public function testCommand($id)
    {
        $pid = getmypid();
        $key = 'pid:' . $id;
        $this->registry->set($key, $pid);
        // $this->outputFormatted('Registred key "%s" with value "%s"', [$key, $pid]);
        sleep(10);
    }

    /**
     * Sends signal for stop all running daemon processes.
     * @cli
     */
    public function killCommand()
    {
        $this->registry->set(Registry::DAEMON_RESTART_KEY, time());
        $this->outputLine('Broadcast signal');
    }

    /**
     * Work on a queue and execute jobs.
     *
     * @param string      $queueName The name of the queue
     * @param int    $timeout Timeout in seconds
     * @param boolean    $daemon
     * @see JobCommandController::ARG_ALL_QUEUES
     * @todo Exception handling
     */
    public function workCommand($queueName, $timeout = 0, $daemon = false)
    {
        $worker = $this->objectManager->get(Worker::class);
        if ($daemon === false) {
            $worker->run($queueName, $timeout);
        } else {
            $worker->daemon($queueName, $timeout);
        }
    }

    /**
     * List jobs in a queue.
     *
     * @param string $queueName The name of the queue
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
     * @param string $queueName The name of the queue
     * @cli
     */
    public function infoCommand($queueName)
    {
        $queue = $this->jobManager->getQueueManager()->getQueue($queueName);
        $options = $queue->getOptions();

        $this->outputFormatted('List infos for queue "%s"...', [$queueName]);
        $this->outputFormatted('<b>Class:</b> %s', [get_class($queue)]);

        if (is_array($options) && !empty($options)) {
            foreach ($options as $key => $value) {
                $this->outputFormatted('<b>%s:</b> %s', [ucfirst($key), ($value === null) ? 'null': $value]);
            }
        }
    }
}
