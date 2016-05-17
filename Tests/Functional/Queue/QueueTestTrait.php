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
 * Functional test for BeanstalkdQueue
 */
trait QueueTestTrait
{
    /**
     * @test
     */
    public function publishAndWaitWithMessageWorks()
    {
        $message = new Message('Yeah, tell someone it works!');
        $this->queue->publish($message);

        $result = $this->queue->waitAndTake(1);
        $this->assertNotNull($result, 'wait should receive message');
        $this->assertEquals($message->getPayload(), $result->getPayload(), 'message should have payload as before');
    }

    /**
     * @test
     */
    public function waitForMessageTimesOut()
    {
        $result = $this->queue->waitAndTake(1);
        $this->assertNull($result, 'wait should return NULL after timeout');
    }

    /**
     * @test
     */
    public function peekReturnsNextMessagesIfQueueHasMessages()
    {
        $message = new Message('First message');
        $this->queue->publish($message);
        $message = new Message('Another message');
        $this->queue->publish($message);

        $results = $this->queue->peek(1);
        $this->assertEquals(1, count($results), 'peek should return a message');
        /** @var Message $result */
        $result = $results[0];
        $this->assertEquals('First message', $result->getPayload());
        $this->assertEquals(Message::STATE_PUBLISHED, $result->getState(), 'Message state should be published');

        $results = $this->queue->peek(1);
        $this->assertEquals(1, count($results), 'peek should return a message again');
        $result = $results[0];
        $this->assertEquals('First message', $result->getPayload(), 'second peek should return the same message again');
    }

    /**
     * @test
     */
    public function peekReturnsNullIfQueueHasNoMessage()
    {
        $result = $this->queue->peek();
        $this->assertEquals(array(), $result, 'peek should not return a message');
    }

    /**
     * @test
     */
    public function waitAndReserveWithFinishRemovesMessage()
    {
        $message = new Message('First message');
        $this->queue->publish($message);


        $result = $this->queue->waitAndReserve(1);
        $this->assertNotNull($result, 'waitAndReserve should receive message');
        $this->assertEquals($message->getPayload(), $result->getPayload(), 'message should have payload as before');

        $result = $this->queue->peek();
        $this->assertEquals(array(), $result, 'no message should be present in queue');

        $finishResult = $this->queue->finish($message);
        $this->assertTrue($finishResult, 'message should be finished');
    }

    /**
     * @test
     */
    public function countReturnsZeroByDefault()
    {
        $this->assertSame(0, $this->queue->count());
    }

    /**
     * @test
     */
    public function countReturnsNumberOfReadyJobs()
    {
        $message1 = new Message('First message');
        $this->queue->publish($message1);

        $message2 = new Message('Second message');
        $this->queue->publish($message2);

        $this->assertSame(2, $this->queue->count());
    }
}
