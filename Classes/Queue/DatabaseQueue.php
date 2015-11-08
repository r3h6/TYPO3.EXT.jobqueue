<?php
namespace TYPO3\Jobqueue\Queue;

use TYPO3\Jobqueue\Domain\Model\Job as DatabaseJob;
use TYPO3\Jobqueue\Queue\Message;

class DatabaseQueue implements QueueInterface {

	/**
	 * [$jobRepository description]
	 * @var TYPO3\Jobqueue\Domain\Repository\JobRepository
	 * @inject
	 */
	protected $jobRepository = NULL;

	/**
	 * PersistenceManager
	 * @var TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager
	 * @inject
	 */
	protected $persistenceManager = NULL;

	protected $queueName;

	public function __construct($queueName, $options = NULL){
		$this->queueName = $queueName;
	}

	public function publish(Message $message){
		$job = $this->encodeJob($message);
		$job->setState(Message::STATE_PUBLISHED);
		$this->jobRepository->add($job);
		$this->persistenceManager->persistAll();
		$message->setState($job->getState());
	}

	public function waitAndTake($timeout = NULL){

	}

	public function waitAndReserve($timeout = NULL){
		$job = $this->jobRepository->findNextByQueueName($this->queueName);
		if ($job !== NULL){
			$job->setState(Message::STATE_RESERVED);
			$this->jobRepository->update($job);
			$this->persistenceManager->persistAll();
			return $this->decodeJob($job);
		}
		return NULL;
	}

	public function finish(Message $message){
		$job = $this->jobRepository->findByUid($message->getIdentifier());
		$job->setState(Message::STATE_DONE);
		$this->jobRepository->remove($job);
		$this->persistenceManager->persistAll();
		$message->setState($job->getState());
		return TRUE;
	}

	public function peek($limit = 1){

	}

	public function getMessage($identifier){

	}

	public function count(){

	}

	private function encodeJob (Message $message){
		$job = new DatabaseJob();
		$job->setQueueName($this->queueName);
		$job->setPayload($message->getPayload());
		$job->setAttemps($message->getAttemps());
		$job->setState($message->getState());
		return $job;
	}

	private function decodeJob (DatabaseJob $job){
		$message = new Message(
			$job->getPayload(),
			$job->getUid()
		);
		$message->setState($job->getState());
		$message->setAttemps($job->getAttemps());
		return $message;
	}
}