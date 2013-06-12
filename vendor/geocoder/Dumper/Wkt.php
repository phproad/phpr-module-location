<?php

/**
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

/**
 * @author Jan Sorgalla <jsorgalla@googlemail.com>
 */
class Dumper_Wkt implements Dumper_Interface
{
    /**
     * @param  \Geocoder\Result\ResultInterface $result
     * @return string
     */
    public function dump(Result_Interface $result)
    {
        return sprintf('POINT(%F %F)', $result->getLongitude(), $result->getLatitude());
    }
}
