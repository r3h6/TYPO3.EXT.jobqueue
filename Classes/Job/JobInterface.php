<?php

namespace R3H6\Jobqueue\Job;

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

use R3H6\Jobqueue\Queue\Message;
use R3H6\Jobqueue\Queue\QueueInterface;

/**
 * Job interface.
 */
interface JobInterface extends \Serializable
{
    /**
     * Execute the job.
     *
     * @param QueueInterface $queue
     * @param Message        $message The original message
     * @return bool TRUE If the job was executed successfully and the message should be finished
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
