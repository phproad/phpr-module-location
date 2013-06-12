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
class Dumper_Kml extends Dumper_Gpx
{
    /**
     * @param  \Geocoder\Result\ResultInterface $result
     * @return string
     */
    public function dump(Result_Interface $result)
    {
        $name = $this->formatName($result);

        $kml = <<<KML
<?xml version="1.0" encoding="UTF-8"?>
<kml xmlns="http://www.opengis.net/kml/2.2">
    <Document>
        <Placemark>
            <name><![CDATA[%s]]></name>
            <description><![CDATA[%s]]></description>
            <Point>
                <coordinates>%.7F,%.7F,0</coordinates>
            </Point>
        </Placemark>
    </Document>
</kml>
KML;

        return sprintf($kml, $name, $name, $result->getLongitude(), $result->getLatitude());
    }
}
