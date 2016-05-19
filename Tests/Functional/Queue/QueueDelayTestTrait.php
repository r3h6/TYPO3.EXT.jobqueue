<?php
namespace R3H6\Jobqueue\Tests\Functional\Queue;

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

/**
 * Functional test for delay message feature.
 */
trait QueueDelayTestTrait
{
    /**
     * @test
     */
    public function publishMessageWithDelayAndWaitAndReserveUsingTimeoutAndFinishMessage()
    {
        // Make sure the queue is empty.
        $this->assertSame(null, $this->queue->waitAndReserve(), 'Queue should be empty!');

        // Publish new message.
        $newMessage = new Message('TYPO3');
        $newMessage->setAvailableAt(new \DateTime('now + 2sec'));
        $this->assertSame(2, $newMessage->getDelay(), 'Delay does not  match!');

        $this->queue->publish($newMessage);

        // Do the tests.
        $this->assertSame(null, $this->queue->waitAndReserve(), 'There should be no job available at this moment!');

        $message = $this->queue->waitAndReserve(3);
        $this->assertInstanceOf(Message::class, $message);
        $this->assertSame(Message::STATE_RESERVED, $message->getState(), 'Message has not the state reserved!');
        $this->assertNotEmpty($message->getIdentifier(), 'Message identifier should be set!');

        $this->assertTrue($this->queue->finish($message), 'Message could not be finished!');

        $this->assertSame(Message::STATE_DONE, $message->getState(), 'Message has not the state done!');
    }
}
