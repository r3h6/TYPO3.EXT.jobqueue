<?php
namespace TYPO3\Jobqueue\Tests\Functional\Job;

require_once __DIR__ . '/../BasicFrontendEnvironmentTrait.php';

use Exception;
use TYPO3\Jobqueue\Exception as JobQueueException;
use TYPO3\Jobqueue\Job\JobManager;
use TYPO3\Jobqueue\Domain\Repository\JobRepository;
use TYPO3\Jobqueue\Tests\Functional\Fixtures\TestAttempsJob;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\Jobqueue\Queue\Message;

class ItemRepositoryTest extends \TYPO3\CMS\Core\Tests\FunctionalTestCase {

	use \TYPO3\Jobqueue\Tests\Functional\BasicFrontendEnvironmentTrait;

	protected $coreExtensionsToLoad = array('extbase');
	protected $testExtensionsToLoad = array('typo3conf/ext/jobqueue');

	protected $jobManager = NULL;

	protected $jobRepository = NULL;

	public function setUp (){
		parent::setUp();
		$this->objectManager = GeneralUtility::makeInstance(ObjectManager::class);
		$this->jobManager = $this->objectManager->get(JobManager::class);
		$this->jobRepository = $this->objectManager->get(JobRepository::class);

		$this->setUpBasicFrontendEnvironment();
	}

	public function tearDown (){
		parent::tearDown();
		unset($this->jobManager, $this->jobRepository);
	}

	/**
	 * @test
	 */
	public function waitAndExecuteGetsAndExecutesJobFromQueue (){
		$queueName = 'TestQueue';
		$job = new TestAttempsJob();
		$this->jobManager->queue($queueName, $job);

		$this->assertEquals(1, $this->jobRepository->countByQueueName($queueName));

		// 1st attemp
		$exception = NULL;
		try {
			$queuedJob = $this->jobManager->waitAndExecute($this->queueName);
		} catch (Exception $exception){}
		$this->assertInstanceOf(JobQueueException::class, $exception);
		$this->assertEquals(1, $this->jobRepository->countByAttemps(1));

		// 2nd attemp
		$exception = NULL;
		try {
			$queuedJob = $this->jobManager->waitAndExecute($this->queueName);
		} catch (Exception $exception){}
		$this->assertInstanceOf(Exception::class, $exception);
		$this->assertEquals(1, $this->jobRepository->countByAttemps(2));

		// 3rd attemp
		$queuedJob = $this->jobManager->waitAndExecute($this->queueName);
		$this->assertInstanceOf(TestAttempsJob::class, $queuedJob);
		$this->assertTrue($queuedJob->getProcessed());

		$this->assertEquals(0, $this->jobRepository->countByQueueName($queueName));
	}

}