<?php
return array(
    'ctrl' => array(
        'title' => 'LLL:EXT:jobqueue/Resources/Private/Language/locallang_db.xlf:tx_jobqueue_domain_model_failedjob',
        'label' => 'crdate',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'dividers2tabs' => true,

        'enablecolumns' => array(

        ),
        'searchFields' => 'crdate,queue_name,payload',
        'iconfile' => 'EXT:jobqueue/Resources/Public/Icons/tx_jobqueue_domain_model_failedjob.gif',
        'readOnly' => true,
        'rootLevel' => 1,
    ),
    'interface' => array(
        'showRecordFieldList' => 'crdate, queue_name, payload',
    ),
    'types' => array(
        '1' => array('showitem' => 'crdate, queue_name, payload'),
    ),
    'palettes' => array(
        '1' => array('showitem' => ''),
    ),
    'columns' => array(

        'crdate' => array(
            'exclude' => 0,
            'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.creationDate',
            'config' => array(
                'readOnly' => 1,
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim,datetime'
            ),
        ),
        'queue_name' => array(
            'exclude' => 0,
            'label' => 'LLL:EXT:jobqueue/Resources/Private/Language/locallang_db.xlf:tx_jobqueue_domain_model_failedjob.queue_name',
            'config' => array(
                'readOnly' => 1,
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim,required'
            ),
        ),
        'payload' => array(
            'exclude' => 0,
            'label' => 'LLL:EXT:jobqueue/Resources/Private/Language/locallang_db.xlf:tx_jobqueue_domain_model_failedjob.payload',
            'config' => array(
                'readOnly' => 1,
                'type' => 'text',
                'cols' => 40,
                'rows' => 15,
                'eval' => 'trim,required'
            )
        ),

    ),
);