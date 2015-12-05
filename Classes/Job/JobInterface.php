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

use TYPO3\Jobqueue\Queue\Message;
use TYPO3\Jobqueue\Queue\QueueInterface;

/**
 * Job interface.
 */
interface JobInterface
{
    /**
     * Execute the job.
     *
     * A job should finish itself after successful execution using the queue methods.
     *
     * @param QueueInterface $queue
     * @param Message        $message The original message
     * @return bool TRUE if the job was executed successfully and the message should be finished
     */
    public function execute(QueueInterface $queue, Message $message);

    /**
     * Get an optional identifier for the job.
     *
     * @return string A job identifier
     */
    public function getIdentifier();

    /**
     * Get a readable label for the job.
     *
     * @return string A label for the job
     */
    public function getLabel();
}
