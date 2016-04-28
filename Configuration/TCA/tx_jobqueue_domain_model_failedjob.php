<?php
return array(
	'ctrl' => array(
		'title'	=> 'LLL:EXT:jobqueue/Resources/Private/Language/locallang_db.xlf:tx_jobqueue_domain_model_failedjob',
		'label' => 'queue_name',
		'tstamp' => 'tstamp',
		'crdate' => 'crdate',
		'cruser_id' => 'cruser_id',
		'dividers2tabs' => TRUE,

		'delete' => 'deleted',
		'enablecolumns' => array(
			'disabled' => 'hidden',
			'starttime' => 'starttime',
			'endtime' => 'endtime',
		),
		'searchFields' => 'queue_name,payload,',
		'iconfile' => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extRelPath('jobqueue') . 'Resources/Public/Icons/tx_jobqueue_domain_model_failedjob.gif'
	),
	'interface' => array(
		'showRecordFieldList' => 'hidden, queue_name, payload',
	),
	'types' => array(
		'1' => array('showitem' => 'hidden;;1, queue_name, payload, --div--;LLL:EXT:cms/locallang_ttc.xlf:tabs.access, starttime, endtime'),
	),
	'palettes' => array(
		'1' => array('showitem' => ''),
	),
	'columns' => array(

		'hidden' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.hidden',
			'config' => array(
				'type' => 'check',
			),
		),
		'starttime' => array(
			'exclude' => 1,
			'l10n_mode' => 'mergeIfNotBlank',
			'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.starttime',
			'config' => array(
				'type' => 'input',
				'size' => 13,
				'max' => 20,
				'eval' => 'datetime',
				'checkbox' => 0,
				'default' => 0,
				'range' => array(
					'lower' => mktime(0, 0, 0, date('m'), date('d'), date('Y'))
				),
			),
		),
		'endtime' => array(
			'exclude' => 1,
			'l10n_mode' => 'mergeIfNotBlank',
			'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.endtime',
			'config' => array(
				'type' => 'input',
				'size' => 13,
				'max' => 20,
				'eval' => 'datetime',
				'checkbox' => 0,
				'default' => 0,
				'range' => array(
					'lower' => mktime(0, 0, 0, date('m'), date('d'), date('Y'))
				),
			),
		),

		'queue_name' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:jobqueue/Resources/Private/Language/locallang_db.xlf:tx_jobqueue_domain_model_failedjob.queue_name',
			'config' => array(
				'type' => 'input',
				'size' => 30,
				'eval' => 'trim,required'
			),
		),
		'payload' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:jobqueue/Resources/Private/Language/locallang_db.xlf:tx_jobqueue_domain_model_failedjob.payload',
			'config' => array(
				'type' => 'text',
				'cols' => 40,
				'rows' => 15,
				'eval' => 'trim,required'
			)
		),
		
	),
);