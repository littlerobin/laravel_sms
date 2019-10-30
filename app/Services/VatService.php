<?php namespace App\Services;

use Rokde\VatCalculator\Calculator;
use Rokde\VatCalculator\Rule;
use Rokde\VatCalculator\RuleSet;

class VatService{

	/**
	 * Country code who will receive payments
	 *
	 * @const string
	 */
	CONST OWRCOUNTRY = 'ES';

	/**
	 * Percentage amount of receiver country
	 *
	 * @const float
	 */
	const OWRPERCENTAGE = .21;

	/**
	 * Check if VAT id is valid or not.
	 *
	 * @param string $vatId
	 * @return bool
	 */
	public function checkIfVatIdValid($vatid)
	{
        $vatid = str_replace(array(' ', '.', '-', ',', ', '), '', trim($vatid));
        if($vatid){
            $vatid = str_replace(array(' ', '.', '-', ',', ', '), '', trim($vatid));
            $cc = substr($vatid, 0, 2);
            $vn = substr($vatid, 2);
            $cc = strtolower($cc);
            if( $cc == 'es'){
                return false;
            }
            $client = new \SoapClient("http://ec.europa.eu/taxation_customs/vies/checkVatService.wsdl");

            if($client){
                $params = array('countryCode' => $cc, 'vatNumber' => $vn);
                try{
                    $r = $client->checkVat($params);
                    if($r->valid == true){
                        return true;
                    } else {
                        return false;
                    }
                } catch(\SoapFault $e) {
                    \Log::info($e);
                    return false;
                } catch (\Exception $e) {
                	\Log::error($e);
                	return false;
                }
            } else {
                return false;
            }
        } else{
            return false;
        }
	}


	/**
	 * Calculate vat for the given price and country.
	 *
	 * @param float $price
	 * @param string $country
	 * @param bool $isCompany
	 * @return float
	 */
	public function calculateVat($price, $country, $isCompany = false)
	{
		$rules = new RuleSet();
        $rates = json_decode( file_get_contents( public_path() . '/rates.json' ), 1);
		$rates = $rates['rates'];
		$rules->addRule(new Rule(self::OWRCOUNTRY, '.' . explode('.', $rates[self::OWRCOUNTRY]['standard_rate'])[0]));
		if(isset($rates[$country])){
			$rules->addRule(new Rule($country, '.' . explode('.', $rates[$country]['standard_rate'])[0] ));
		} else{
			$rules->addRule(new Rule($country, .20));
		}

		$calculator = new Calculator($rules, self::OWRCOUNTRY);
		$calculator->setPrice($price);
		$vatCost = $calculator->calculate($country, $isCompany);
		return $vatCost;
	}
}