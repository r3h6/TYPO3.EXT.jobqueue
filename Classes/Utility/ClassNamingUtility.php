<?php

namespace TYPO3\Jobqueue\Utility;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "TYPO3.Jobqueue". *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU General Public License, either version 3 of the   *
 * License, or (at your option) any later version.                        *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

/**
 *
 */
class ClassNamingUtility
{
    public static function explode($className)
    {
        $matches = array();
        if (substr($className, 0, 9) === 'TYPO3\\CMS') {
            $extensionName = '^(?P<vendorName>[^\\\\]+\\\[^\\\\]+)\\\(?P<extensionName>[^\\\\]+)';
        } else {
            $extensionName = '^(?P<vendorName>[^\\\\]+)\\\\(?P<extensionName>[^\\\\]+)';
        }

        preg_match(
            '/' . $extensionName . '\\\\(?P<subpackageKey>.+)\\\\(?P<className>.+)$/ix',
            $className,
            $matches
        );

        return $matches;
    }
}
