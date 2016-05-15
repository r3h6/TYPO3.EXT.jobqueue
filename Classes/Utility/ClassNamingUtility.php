<?php

namespace R3H6\Jobqueue\Utility;

/*                                                                        *
 * This script is part of the TYPO3 project - inspiring people to share!  *
 *                                                                        *
 * TYPO3 is free software; you can redistribute it and/or modify it under *
 * the terms of the GNU General Public License version 3 as published by  *
 * the Free Software Foundation.                                          *
 *                                                                        *
 * This script is distributed in the hope that it will be useful, but     *
 * WITHOUT ANY WARRANTY; without even the implied warranty of MERCHAN-    *
 * TABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General      *
 * Public License for more details.                                       *
 *                                                                        */
/**
 * ClassNamingUtility
 */
class ClassNamingUtility
{
    /**
     * Explodes a standard TYPO3 class name.
     *
     * @param  string $className Fully qualified class name
     * @return array             Class name parts
     */
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
