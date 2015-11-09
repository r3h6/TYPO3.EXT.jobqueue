<?php
if (!defined('TYPO3_MODE')) {
	die('Access denied.');
}

$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['extbase']['commandControllers'][] = 'TYPO3\\Jobqueue\\Command\\JobCommandController';


$GLOBALS['TYPO3_CONF_VARS']['EXT']['jobqueue']['TYPO3\\Jobqueue\\Queue\\DatabaseQueue'] = [];