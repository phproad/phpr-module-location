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
class Formatter_Formatter implements Formatter_Interface
{
    /**
     * @var \Geocoder\Result\ResultInterface
     */
    private $result;

    /**
     * @param ResultInterface $result
     */
    public function __construct(ResultInterface $result)
    {
        $this->result = $result;
    }

    /**
     * {@inheritdoc}
     */
    public function format($format)
    {
        return strtr($format, array(
            Formatter_Interface::STREET_NUMBER   => $this->result->getStreetNumber(),
            Formatter_Interface::STREET_NAME     => $this->result->getStreetName(),
            Formatter_Interface::CITY            => $this->result->getCity(),
            Formatter_Interface::ZIPCODE         => $this->result->getZipcode(),
            Formatter_Interface::CITY_DISTRICT   => $this->result->getCityDistrict(),
            Formatter_Interface::COUNTY          => $this->result->getCounty(),
            Formatter_Interface::REGION          => $this->result->getRegion(),
            Formatter_Interface::REGION_CODE     => $this->result->getRegionCode(),
            Formatter_Interface::COUNTRY         => $this->result->getCountry(),
            Formatter_Interface::COUNTRY_CODE    => $this->result->getCountryCode(),
            Formatter_Interface::TIMEZONE        => $this->result->getTimezone(),
        ));
    }
}
