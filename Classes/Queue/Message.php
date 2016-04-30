<?php

namespace TYPO3\Jobqueue\Queue;

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

use DateTime;

/**
 * Message object.
 */
class Message
{
    // Created locally, not published to queue
    const STATE_NEW = 0;
    // Message published to queue, should not be processed by client
    const STATE_PUBLISHED = 1;
    // Message received from queue, not deleted from queue! (a.k.a. Reserved)
    const STATE_RESERVED = 2;
    // Message processed and deleted from queue
    const STATE_DONE = 3;

    /**
     * Depending on the queue implementation, this identifier will
     * allow for unique messages (e.g. prevent adding jobs twice).
     *
     * @var string Identifier of the message
     */
    protected $identifier = null;

    /**
     * The message payload has to be serializable.
     *
     * @var string The message payload
     */
    protected $payload = null;

    /**
     * @var int State of the message, one of the Message::STATE_* constants
     */
    protected $state = self::STATE_NEW;

    /**
     * Gets increased every time the job is executed.
     *
     * @var int Attemps
     */
    protected $attemps = 0;

    /**
     * @var DateTime
     */
    protected $availableAt = null;

    /**
     * Constructor
     *
     * @param string $payload
     * @param string $identifier
     */
    public function __construct($payload, $identifier = null)
    {
        $this->payload = $payload;
        $this->identifier = $identifier;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            'identifier' => $this->identifier,
            'payload' => $this->payload,
            'state' => $this->state,
            'attemps' => $this->attemps,
        ];
    }

    /**
     * @param string
     */
    public function setIdentifier($identifier)
    {
        $this->identifier = $identifier;
    }

    /**
     * @return string
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * @return mixed
     */
    public function getPayload()
    {
        return $this->payload;
    }

    /**
     * @param int $state
     */
    public function setState($state)
    {
        $this->state = $state;
    }

    /**
     * @return int
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * Get attemps.
     *
     * @return int [description]
     */
    public function getAttemps()
    {
        return $this->attemps;
    }

    /**
     * Set attemps.
     *
     * @param int $attemps [description]
     */
    public function setAttemps($attemps)
    {
        $this->attemps = $attemps;
    }

    /**
     * Get availableAt.
     *
     * @return DateTime Date when job is available.
     */
    public function getAvailableAt()
    {
        return $this->availableAt;
    }

    /**
     * Set availableAt.
     *
     * @param DateTime $availableAt Date when job is available.
     */
    public function setAvailableAt($availableAt)
    {
        $this->availableAt = $availableAt;
    }

    /**
     * Get delay.
     *
     * @return int Delay in seconds
     */
    public function getDelay()
    {
        if ($this->availableAt instanceof DateTime) {
            return max(0, (int) (new DateTime())->diff($this->availableAt)->format('%s'));
        }

        return 0;
    }
}
