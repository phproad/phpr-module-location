<?php

/**
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

/**
 * @author William Durand <william.durand1@gmail.com>
 */
interface Provider_Interface
{
    /**
     * Returns an associative array with data treated by the provider.
     *
     * @param  string $address An address (IP or street).
     * @return array
     */
    public function getGeocodedData($address);

    /**
     * Returns an associative array with data treated by the provider.
     *
     * @param  array $coordinates Coordinates (latitude, longitude).
     * @return array
     */
    public function getReversedData(array $coordinates);

    /**
     * Returns the provider's name.
     *
     * @return string
     */
    public function getName();
}
