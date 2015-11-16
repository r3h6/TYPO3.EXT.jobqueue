<?php
namespace TYPO3\Jobqueue\Tests\Functional\Queue;

require_once __DIR__ . '/../BasicFrontendEnvironmentTrait.php';

use DateTime;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\Jobqueue\Queue\DatabaseQueue;
use TYPO3\Jobqueue\Queue\Message;

class DatabaseQueueTest extends \TYPO3\CMS\Core\Tests\FunctionalTestCase {

	use \TYPO3\Jobqueue\Tests\Functional\BasicFrontendEnvironmentTrait;

	protected $coreExtensionsToLoad = array('extbase');
	protected $testExtensionsToLoad = array('typo3conf/ext/jobqueue');

	protected $queue = NULL;

	const TABLE = 'tx_jobqueue_domain_model_job';
	const JOBS_FIXTURES = 'typo3conf/ext/jobqueue/Tests/Functional/Fixtures/Database/jobs.xml';

	public function setUp (){
		parent::setUp();
		$this->objectManager = GeneralUtility::makeInstance(ObjectManager::class);
		$this->queue = $this->objectManager->get(DatabaseQueue::class, 'TestQueue', NULL);

		$this->setUpBasicFrontendEnvironment();
	}

	public function tearDown (){
		parent::tearDown();
		unset($this->queue);
	}

	/**
	 * @test
	 */
	public function publishMessageAndCheckDatabaseRecordAndMessageState (){
		$newMessage = new Message('TYPO3');
		$this->queue->publish($newMessage);
		$record = $this->getDatabaseConnection()->exec_SELECTgetSingleRow('queue_name, payload, state, attemps, starttime', self::TABLE, '');
		$this->assertSame([
			'queue_name' => 'TestQueue',
			'payload' => 'TYPO3',
			'state' => '' . Message::STATE_PUBLISHED,
			'attemps' => '0',
			'starttime' => '0',
		], $record, 'Invalid database record');
		$this->assertSame(Message::STATE_PUBLISHED, $newMessage->getState());
	}

	/**
	 * @test
	 * @depends publishMessageAndCheckDatabaseRecordAndMessageState
	 */
	public function waitAndReserve (){
		$this->importDataSet(ORIGINAL_ROOT . self::JOBS_FIXTURES);

		$message = $this->queue->waitAndReserve();
		$this->assertSame(4, $message->getIdentifier(), 'Wrong job found in queue!');

		$message = $this->queue->waitAndReserve();
		$this->assertSame(5, $message->getIdentifier(), 'Wrong job found in queue!');

		$message = $this->queue->waitAndReserve();
		$this->assertSame(null, $message, 'There should be no jobs at this moment!');
	}

	/**
	 * @test
	 * @depends publishMessageAndCheckDatabaseRecordAndMessageState
	 */
	public function publishMessageAndWaitAndReserveWithoutAndWithTimeout (){
		$newMessage = new Message('TYPO3');
		$newMessage->setAvailableAt(new DateTime('now + 1sec'));
		$this->queue->publish($newMessage);

		$this->assertSame(NULL, $this->queue->waitAndReserve(), 'There should be no job available at this moment!');

		$message = $this->queue->waitAndReserve(1.1);
		$this->assertInstanceOf(Message::class, $message);
		$this->assertSame(Message::STATE_RESERVED, $message->getState(), 'Message has not the state reserved!');
		$this->assertNotEmpty($message->getIdentifier(), 'Message identifier should be set!');

		$record = $this->getDatabaseConnection()->exec_SELECTgetSingleRow('state', self::TABLE, '');
		$this->assertSame(Message::STATE_RESERVED, (int) $record['state'], 'Job has not the state reserved!');
	}

	/**
	 * @test
	 */
	public function finishMessage (){
		$this->importDataSet(ORIGINAL_ROOT . self::JOBS_FIXTURES);
		$message = new Message('', 1);
		$this->queue->finish($message);
		$this->assertSame(Message::STATE_DONE, $message->getState(), 'Message is not of state done!');
		$this->assertSame(0, $this->getDatabaseConnection()->exec_SELECTcountRows('*', self::TABLE, 'uid=1'), 'Job was not deleted in database!');
	}

}