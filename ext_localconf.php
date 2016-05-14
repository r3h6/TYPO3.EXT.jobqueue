<?php

if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}

if (!is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['jobqueue'])) {
    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['jobqueue'] = [];
}

if (TYPO3_MODE === 'BE') {
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['extbase']['commandControllers'][] = 'TYPO3\\Jobqueue\\Command\\JobCommandController';
}


$GLOBALS['TYPO3_CONF_VARS']['LOG']['TYPO3']['Jobqueue']['writerConfiguration'] = array(
    \TYPO3\Jobqueue\Configuration\ExtensionConfiguration::get('logLevel') => array(
        'TYPO3\\CMS\\Core\\Log\\Writer\\FileWriter' => array(
           'logFile' => 'typo3temp/logs/jobs.log'
        )
    )
);
