<?php
namespace R3H6\Jobqueue\Domain\Model;

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

/**
 * FailedJob
 */
class FailedJob extends \TYPO3\CMS\Extbase\DomainObject\AbstractEntity
{

    /**
     * Queue name
     *
     * @var string
     * @validate NotEmpty
     */
    protected $queueName = '';

    /**
     * Payload
     *
     * @var string
     * @validate NotEmpty
     */
    protected $payload = '';

    /**
     * Create date
     *
     * @var \DateTime
     */
    protected $crdate = null;

    public function __construct($queueName, $payload)
    {
        $this->queueName = $queueName;
        $this->payload = $payload;
        $this->pid = 0;
    }

    /**
     * Returns the queueName
     *
     * @return string $queueName
     */
    public function getQueueName()
    {
        return $this->queueName;
    }

    /**
     * Sets the queueName
     *
     * @param string $queueName
     * @return void
     */
    public function setQueueName($queueName)
    {
        $this->queueName = $queueName;
    }

    /**
     * Returns the payload
     *
     * @return string $payload
     */
    public function getPayload()
    {
        return $this->payload;
    }

    /**
     * Sets the payload
     *
     * @param string $payload
     * @return void
     */
    public function setPayload($payload)
    {
        $this->payload = $payload;
    }

    /**
     * Gets the crdate
     *
     * @return  \DateTime
     */
    public function getCrdate()
    {
        return $this->crdate;
    }
}
