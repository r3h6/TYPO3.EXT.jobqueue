<?php

namespace TYPO3\Jobqueue\Tests\Functional\Job;

require_once __DIR__.'/../BasicFrontendEnvironmentTrait.php';

use Exception;
use TYPO3\Jobqueue\Job\JobManager;
use TYPO3\Jobqueue\Tests\Functional\Fixtures\TestAttempsJob;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;

class JobManagerTest extends \TYPO3\CMS\Core\Tests\FunctionalTestCase
{
    use \TYPO3\Jobqueue\Tests\Functional\BasicFrontendEnvironmentTrait;

    protected $coreExtensionsToLoad = array('extbase');
    protected $testExtensionsToLoad = array('typo3conf/ext/jobqueue');

    protected $configurationToUseInTestInstance = [
        'EXT' => [
            'extConf' => [
                'jobqueue' => [
                    'defaultQueue' => 'TYPO3\\Jobqueue\\Queue\\DatabaseQueue',
                    'defaultTimeout' => '0',
                    'maxAttemps' => '3',
                ],
            ],
        ],
    ];

    protected $jobManager = null;
    protected $queueName = 'TestQueue';

    const TABLE = 'tx_jobqueue_domain_model_job';

    public function setUp()
    {
        parent::setUp();
        $this->objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        $this->jobManager = $this->objectManager->get(JobManager::class);

        $this->setUpBasicFrontendEnvironment();
    }

    public function tearDown()
    {
        parent::tearDown();
        unset($this->jobManager);
    }

    /**
     * @test
     */
    public function waitAndExecuteGetsAndExecutesJobFromQueue()
    {
        $job = new TestAttempsJob();
        $this->jobManager->queue($this->queueName, $job);

        $this->assertEquals(1, $this->getDatabaseConnection()->exec_SELECTcountRows('*', self::TABLE, ''));

        // 1st attemp
        try {
            $queuedJob = $this->jobManager->waitAndExecute($this->queueName);
        } catch (Exception $exception) {
        }
        $this->assertEquals(1, $this->getDatabaseConnection()->exec_SELECTcountRows('*', self::TABLE, 'attemps=1'));

        // 2nd attemp
        try {
            $queuedJob = $this->jobManager->waitAndExecute($this->queueName);
        } catch (Exception $exception) {
        }
        $this->assertEquals(1, $this->getDatabaseConnection()->exec_SELECTcountRows('*', self::TABLE, 'attemps=2'));

        // 3rd attemp
        $queuedJob = $this->jobManager->waitAndExecute($this->queueName);
        $this->assertInstanceOf(TestAttempsJob::class, $queuedJob);
        $this->assertTrue($queuedJob->getProcessed());

        $this->assertEquals(0, $this->getDatabaseConnection()->exec_SELECTcountRows('*', self::TABLE, ''), 'Job is not deleted from database!');
    }
}
