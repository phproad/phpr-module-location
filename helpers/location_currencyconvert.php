<?php
/* Currency Conversion
 *
 * Example Usage:
 *
 *      $currency_converter = new Location_CurrencyConvert;
 *      $from_currency = Location_Currency::create()->find_by_alpha_code(AUD);
 *      $to_currency = Location_Currency::create()->find_by_alpha_code(USD);
 *
 *      echo "200 AUD in USD = ."$currency_converter->between_currencies(200, $from_currency,  $to_currency);
 */

class Location_CurrencyConvert
{

    protected $getrate_method;
    protected $rate_cache = array();

    function __construct(){
        // Determine default currency lookup provider
        $config = Location_Config::create();
        $this->getrate_method  = ($config->currency_lookup_provider)
            ? $config->currency_lookup_provider
            : 'GetRate_YahooYQL';
    }

    //rates are cached in db daily.
    protected function set_cached_rate($ident, $rate){
        $sql = "INSERT INTO location_currency_rates
            (ident, rate, cached_date)
            VALUES (:ident,:rate, NOW())
            ON DUPLICATE KEY UPDATE rate=:rate, cached_date=DATE(NOW())";

        $query = Db_Helper::query($sql, array('ident' => $ident,'rate' => $rate));
        return $this->rate_cache[$ident] = $rate;
    }

    protected function get_cached_rate($ident){
        if (array_key_exists($ident, $this->rate_cache)){
            return $this->rate_cache[$ident];
        }

        $rate = Db_Helper::scalar('select rate from location_currency_rates where ident=:ident AND cached_date=DATE(NOW())', array('ident' => $ident));

        if($rate){
            return $this->rate_cache[$ident] = $rate;
        }

        return false;
    }


    public function between_currencies($amount,Location_Currency $from_currency, Location_Currency $to_currency)
    {

        if(!is_numeric($amount)){
            throw new Phpr_ApplicationException("Invalid amount given. Must be numeric value");
        }

        $ident = $from_currency->alpha_code.$to_currency->alpha_code;
        $rate = $this->get_cached_rate($ident);

        if(!$rate){
            $rate = $this->set_cached_rate($ident ,self::$this->getrate_method($from_currency, $to_currency));
        }

        return $amount * $rate;
    }

    public function between_countries($amount, Location_Country $from_country, Location_Country $to_country)
    {
        if(!is_numeric($amount)){
            throw new Phpr_ApplicationException("Invalid amount given. Must be numeric value");
        }

        if(!$from_country->currency || !$to_country->currency){
            throw new Phpr_ApplicationException("Cannot find default currencies for both countries");
        }

        return $this->between_currencies($amount, $from_country->currency, $to_country->currency);
    }




    /* Add rate provider methods below.
     * Only accept Location_Currency objects
     * Throw notification if cannot return a valid rate.
     */

    public static function GetRate_YahooYQL(Location_Currency $from_currency, Location_Currency $to_currency){

        $rate = 0;

        $BASE_URL = "http://query.yahooapis.com/v1/public/yql";
        $yql_query = 'select * from yahoo.finance.xchange where pair="'.$from_currency->alpha_code.$to_currency->alpha_code.'"';
        $yql_query_url = $BASE_URL . "?q=" . urlencode($yql_query) . "&format=json&diagnostics=true&env=store%3A%2F%2Fdatatables.org%2Falltableswithkeys";

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
            throw new Phpr_ApplicationException('Could Not Retrieve Rate '.$from_currency->alpha_code.$to_currency->alpha_code.' from Yahoo YQL ');
        }

        return $rate;
    }

}

