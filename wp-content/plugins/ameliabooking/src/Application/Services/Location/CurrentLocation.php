<?php

/**
 * @copyright © Melograno Ventures. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace AmeliaBooking\Application\Services\Location;

/**
 * Class CurrentLocation
 *
 * @package AmeliaBooking\Application\Services\Location
 */
class CurrentLocation extends AbstractCurrentLocation
{
    /**
     * Get country ISO code by public IP address
     *
     * @param string $ipLocateApyKey
     *
     * @return string
     */
    public function getCurrentLocationCountryIso($ipLocateApyKey)
    {
        try {
            $remoteAddr = $_SERVER['REMOTE_ADDR'] ?? '';

            if ($remoteAddr === '') {
                return '';
            }

            $curlHandle = curl_init();

            curl_setopt(
                $curlHandle,
                CURLOPT_URL,
                'https://www.iplocate.io/api/lookup/' . rawurlencode($remoteAddr) . ($ipLocateApyKey ? ('?apikey=' . rawurlencode($ipLocateApyKey)) : '')
            );

            curl_setopt($curlHandle, CURLOPT_CONNECTTIMEOUT, 2);
            curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($curlHandle, CURLOPT_USERAGENT, 'Amelia');
            $response = curl_exec($curlHandle);

            if ($response === false) {
                return '';
            }

            $result = json_decode($response);

            return !isset($result->country_code) ? '' : strtolower($result->country_code);
        } catch (\Exception $e) {
            return '';
        }
    }
}
