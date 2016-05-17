<?php

namespace R3H6\Jobqueue\Queue;

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
class MemoryQueue implements QueueInterface
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
     * {@inheritdoc}
     */
    public function __construct($name, array $options = array())
    {
        $this->name = $name;
        $this->options = (array) $options + $this->options;
    }

    /**
     * {@inheritdoc}
     */
    public function finish(Message $message)
    {
        $message->setState(Message::STATE_DONE);
        unset($this->processing[$message->getIdentifier()]);
    }

    /**
     * {@inheritdoc}
     */
    public function peek($limit = 1)
    {
        return (count($this->messages) > 0) ? array_slice($this->messages, 0, $limit) : array();
    }

    /**
     * {@inheritdoc}
     */
    public function publish(Message $message)
    {
        $message->setIdentifier(count($this->messages));
        $message->setState(Message::STATE_PUBLISHED);
        $this->messages[] = $message;
    }

    /**
     * {@inheritdoc}
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
     * {@inheritdoc}
     */
    public function waitAndTake($timeout = null)
    {
        $message = array_shift($this->messages);

        return $message;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * {@inheritdoc}
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     *{@inheritdoc}
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
     * {@inheritdoc}
     */
    public function count()
    {
        return count($this->messages);
    }

    /**
     * {@inheritdoc}
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
