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
     * @param  int  $id       [description]
     * @param  string  $queueName [description]
     * @param  integer $timeout   [description]
     * @return [type]             [description]
     */
    public function daemonCommand($id, $queueName, $timeout = 0)
    {
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            throw new \BadFunctionCallException("Command 'job:daemon' is not available on windows systems", 1458844709);
        }
        if (!is_callable('exec')) {
            throw new \BadFunctionCallException('Function "exec" is not callable', 1458849505);
        }
        if (!is_callable('proc_open')) {
            throw new \BadFunctionCallException('Function "proc_open" is not callable', 1458849578);
        }
        if (!is_callable('proc_get_status')) {
            throw new \BadFunctionCallException('Function "proc_get_status" is not callable', 1458849585);
        }

        $status = $this->registry->get('daemon:' . $id);
        if (is_array($status) && isset($status['pid'])) {
            exec(sprintf("ps -p %s", $status['pid']), $output);
            if (count($output) > 1) {
                $this->outputFormatted('Daemon "%s" is already running in process "%s".', [$id, $status['pid']]);
                return;
            }
        }

        $cliDispatchPath = PATH_site . 'typo3/cli_dispatch.phpsh';
        $command = "exec php $cliDispatchPath extbase job:work --queue-name=\"$queueName\" --timeout=\"$timeout\" --daemon";

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

        $this->registry->set('daemon:' . $id, $status);

        $this->outputFormatted('Daemon "%s" started in a new process "%s".', [$id, $status['pid']]);
    }

    public function killCommand()
    {
        $this->registry->set(Registry::DAEMON_RESTART_KEY, time());
        $this->outputLine('Broadcast kill signal');
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
