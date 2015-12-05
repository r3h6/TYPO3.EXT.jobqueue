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

/**
 * Simple in-memory queue.
 * There is no benefit using this queue.
 * It can be used as a fallback and for testing.
 */
class RuntimeQueue implements QueueInterface
{
    /**
     * @var array
     */
    protected $messages = array();

    /**
     * @var array
     */
    protected $processing = array();

    /**
     * @var string
     */
    protected $name;

    /**
     * @var array
     */
    protected $options = array();

    /**
     * Constructor
     *
     * @param string $name
     * @param array  $options
     */
    public function __construct($name, $options)
    {
        $this->name = $name;
        $this->options = (array) $options + $this->options;

        // Remember queue name for executing the job with the eofe hook.
        $GLOBALS['TYPO3_CONF_VARS']['EXT']['jobqueue']['TYPO3\\Jobqueue\\Queue\\RuntimeQueue']['queues'][$name] = true;
    }

    /**
     * @param Message $message
     */
    public function finish(Message $message)
    {
        $message->setState(Message::STATE_DONE);
        unset($this->processing[$message->getIdentifier()]);
    }

    /**
     * @param int $limit
     * @return array<\TYPO3\Jobqueue\Queue\Message>
     */
    public function peek($limit = 1)
    {
        return (count($this->messages) > 0) ? array_slice($this->messages, 0, $limit) : array();
    }

    /**
     * @param Message $message
     */
    public function publish(Message $message)
    {
        $message->setIdentifier(count($this->messages));
        $message->setState(Message::STATE_PUBLISHED);
        $this->messages[] = $message;
    }

    /**
     * @param int $timeout
     * @return Message
     */
    public function waitAndReserve($timeout = 60)
    {
        $message = array_shift($this->messages);
        if ($message !== null) {
            $message->setState(Message::STATE_RESERVED);
            $this->processing[$message->getIdentifier()] = $message;
        }

        return $message;
    }

    /**
     * @param int $timeout
     * @return Message
     */
    public function waitAndTake($timeout = null)
    {
        $message = array_shift($this->messages);

        return $message;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @return array
     */
    public function getMessages()
    {
        return $this->messages;
    }

    /**
     * @return array
     */
    public function getProcessing()
    {
        return $this->processing;
    }

    /**
     * @return int
     */
    public function count()
    {
        return count($this->messages);
    }

    /**
     * @param string $identifier
     * @return Message
     */
    public function getMessage($identifier)
    {
        foreach ($this->messages as $message) {
            if ($message->getIdentifier() === $identifier) {
                return $message;
            }
        }

        return null;
    }
}
