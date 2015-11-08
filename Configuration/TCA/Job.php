<?php
if (!defined ('TYPO3_MODE')) {
	die ('Access denied.');
}

$GLOBALS['TCA']['tx_jobqueue_domain_model_job'] = array(
	'ctrl' => $GLOBALS['TCA']['tx_jobqueue_domain_model_job']['ctrl'],
	'interface' => array(
		'showRecordFieldList' => 'queue_name, payload, state, attemps, starttime, tstamp',
	),
	'types' => array(
		'1' => array('showitem' => 'queue_name, payload, state, attemps, starttime, tstamp, '),
	),
	'palettes' => array(
		'1' => array('showitem' => ''),
	),
	'columns' => array(

		'queue_name' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:jobqueue/Resources/Private/Language/locallang_db.xlf:tx_jobqueue_domain_model_job.queue_name',
			'config' => array(
				'type' => 'input',
				'size' => 30,
				'eval' => 'trim'
			),
		),
		'payload' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:jobqueue/Resources/Private/Language/locallang_db.xlf:tx_jobqueue_domain_model_job.payload',
			'config' => array(
				'type' => 'text',
				'cols' => 40,
				'rows' => 15,
				'eval' => 'trim'
			)
		),
		'state' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:jobqueue/Resources/Private/Language/locallang_db.xlf:tx_jobqueue_domain_model_job.state',
			'config' => array(
				'type' => 'input',
				'size' => 4,
				'eval' => 'int'
			)
		),
		'attemps' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:jobqueue/Resources/Private/Language/locallang_db.xlf:tx_jobqueue_domain_model_job.attemps',
			'config' => array(
				'type' => 'input',
				'size' => 4,
				'eval' => 'int'
			)
		),
		'starttime' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:jobqueue/Resources/Private/Language/locallang_db.xlf:tx_jobqueue_domain_model_job.starttime',
			'config' => array(
				'type' => 'input',
				'size' => 10,
				'eval' => 'datetime',
				'checkbox' => 1,
				'default' => time()
			),
		),
		'tstamp' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:jobqueue/Resources/Private/Language/locallang_db.xlf:tx_jobqueue_domain_model_job.tstamp',
			'config' => array(
				'type' => 'passthrough',
			),
		),

	),
);
