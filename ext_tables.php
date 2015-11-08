<?php
if (!defined('TYPO3_MODE')) {
	die('Access denied.');
}

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile($_EXTKEY, 'Configuration/TypoScript', 'Jobqueue');

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addLLrefForTCAdescr('tx_jobqueue_domain_model_job', 'EXT:jobqueue/Resources/Private/Language/locallang_csh_tx_jobqueue_domain_model_job.xlf');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages('tx_jobqueue_domain_model_job');
$GLOBALS['TCA']['tx_jobqueue_domain_model_job'] = array(
	'ctrl' => array(
		'title'	=> 'LLL:EXT:jobqueue/Resources/Private/Language/locallang_db.xlf:tx_jobqueue_domain_model_job',
		'label' => 'queue_name',
		'tstamp' => 'tstamp',
		'crdate' => 'crdate',
		'cruser_id' => 'cruser_id',
		'dividers2tabs' => TRUE,

		'enablecolumns' => array(

		),
		'searchFields' => 'queue_name,payload,state,attemps,starttime,tstamp,',
		'dynamicConfigFile' => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath($_EXTKEY) . 'Configuration/TCA/Job.php',
		'iconfile' => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extRelPath($_EXTKEY) . 'Resources/Public/Icons/tx_jobqueue_domain_model_job.gif'
	),
);
