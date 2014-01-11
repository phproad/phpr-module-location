<?php
/* Currency Conversion
 *
 * Example Usage:
 *      $from_currency = Location_Currency::create()->find_by_alpha_code(AUD);
 *      $to_currency = Location_Currency::create()->find_by_alpha_code(USD);
 *      $conversion = Location_CurrencyConvert::between_currencies(200, $from_currency,  $to_currency);
 *      echo "200 AUD in USD = $conversion";
 */

class Location_CurrencyConvert
{
    protected static $initialized = false;
    protected static $getrate_method;
    protected static $rate_cache = array();

    protected function init_converter()
    {
        if (self::$initialized)
            return;

        // Determine default currency lookup provider
        $config = Location_Config::create();
        self::$getrate_method  = ($config->currency_lookup_provider)
            ? $config->currency_lookup_provider
            : 'GetRate_YahooYQL';

        self::$initialized = true;
    }

    //rates are cached in db daily.
    protected function set_cached_rate($ident, $rate){
        $sql = "INSERT INTO location_currency_rates
            (ident, rate, cached_date)
            VALUES (:ident,:rate, NOW())
            ON DUPLICATE KEY UPDATE rate=:rate, cached_date=DATE(NOW())";

        echo "Set cache rate ";
        $query = Db_Helper::query($sql, array('ident' => $ident,'rate' => $rate));
        return self::$rate_cache[$ident] = $rate;
    }

    protected function get_cached_rate($ident){
        if (array_key_exists($ident, self::$rate_cache))
            return self::$rate_cache[$ident];

        $rate = Db_Helper::scalar('select rate from location_currency_rates where ident=:ident AND cached_date=DATE(NOW())', array('ident' => $ident));
        echo "cached rate $rate";
        if($rate){
            return $rate;
        }

        return false;
    }


    public static function between_currencies($amount,Location_Currency $from_currency, Location_Currency $to_currency)
    {
        self::init_converter();

        if(!is_numeric($amount)){
            throw new Phpr_ApplicationException("Invalid amount given. Must be numeric value");
        }

        $ident = $from_currency->alpha_code.$to_currency->alpha_code;
        $rate = self::get_cached_rate($ident);

        if(!$rate){
            $getrate_method = self::$getrate_method;
            $rate = self::set_cached_rate($ident ,self::$getrate_method($from_currency, $to_currency));
        }

        return $amount * $rate;
    }

    public static function between_countries($amount, Location_Country $from_country, Location_Country $to_country)
    {
        if(!is_numeric($amount)){
            throw new Phpr_ApplicationException("Invalid amount given. Must be numeric value");
        }

        if(!$from_country->currency || !$to_country->currency){
            throw new Phpr_ApplicationException("Cannot find default currencies for both countries");
        }

        return self::between_currencies($amount, $from_country->currency, $to_country->currency);
    }

    /* Add rate provider methods below.
     * Only accept Location_Currency objects
     * Throws application error if cannot return a valid rate.
     */

    public static function GetRate_YahooYQL(Location_Currency $from_currency, Location_Currency $to_currency){

        $rate = 0;

        $BASE_URL = "http://query.yahooapis.com/v1/public/yql";
        $yql_query = 'select * from yahoo.finance.xchange where pair="'.$from_currency->alpha_code.$to_currency->alpha_code.'"';
        $yql_query_url = $BASE_URL . "?q=" . urlencode($yql_query) . "&format=json&diagnostics=true&env=store%3A%2F%2Fdatatables.org%2Falltableswithkeys";

        // @Todo Update this to make use of PHPR request/service methods.
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $yql_query_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 40);
        $response = curl_exec($ch);
        $resultObj =  json_decode($response);

        if(!is_null($resultObj->query->results)){
            $rate = $resultObj->query->results->rate->Rate;
        }

        if(!is_numeric($rate) || $rate == 0){
            throw new Phpr_ApplicationException($yql_query_url.' Could Not Retrieve Rate '.$from_currency->alpha_code.$to_currency->alpha_code.' from Yahoo YQL '.$rate);
        }

        return $rate;
    }

}

