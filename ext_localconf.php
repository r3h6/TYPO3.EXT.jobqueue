<?php

if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}

if (!is_array($GLOBALS['TYPO3_CONF_VARS']['EXT']['jobqueue'])) {
    $GLOBALS['TYPO3_CONF_VARS']['EXT']['jobqueue'] = [];
}
if (!is_array($GLOBALS['TYPO3_CONF_VARS']['EXT']['jobqueue']['TYPO3\\Jobqueue\\Queue\\MemoryQueue']['queues'])) {
    $GLOBALS['TYPO3_CONF_VARS']['EXT']['jobqueue']['TYPO3\\Jobqueue\\Queue\\MemoryQueue']['queues'] = [];
}

if (TYPO3_MODE === 'FE') {
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tslib/class.tslib_fe.php']['hook_eofe'][] = 'TYPO3\\Jobqueue\\Hooks\\Jobs->spool';
}

if (TYPO3_MODE === 'BE') {
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['extbase']['commandControllers'][] = 'TYPO3\\Jobqueue\\Command\\JobCommandController';
}
