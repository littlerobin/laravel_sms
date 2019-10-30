<?php namespace App\Services;

use DB;
use Carbon\Carbon;
use App\Models\Tariff;

class CampaignDbService {


	/**
	 * Create a new isntance of CampaignDbService class.
	 *
	 * @return void
	 */
	public function __construct()
	{
	}

	/**
	 * Filter numbers for the campaign
	 * Should remove all not valid numbers from array
	 * And return object with tariffs too.
	 *
	 * @param Array $phonenumbers
	 * @return Collection
	 */
	public function getValidPhonenumbers($phonenumbers, $user)
	{
		$finalNumbers = [];
		$countries = \App\Models\Country::all();
		foreach ($phonenumbers as $phonenumber) {
			$validationResponse = $this->isValidNumber($phonenumber, $user, $countries);
			if(!$validationResponse['finalNumber']){continue;}
			$finalNumbers[] = [
				'phonenumber' => $validationResponse['finalNumber'],
				'tariff' => $validationResponse['detectedTariff']
			];
		}
		return collect($finalNumbers);
	}

	/**
	 * check if the given phonenumber is valid and can be used by asterisk server.
	 * PREFIX - valid
	 * -1 - non numeric
	 * -2 - not supported
	 *
	 * @param string $phonenubmer
	 * @return string
	 */
	public function isValidNumber($phonenumber, $user = null, $countries = null)
	{
		$errorResponseArray = ['finalNumber' => false, 'reason' => 'NONE'];
		$detectedCountry = null;
		$phonenumber = (string)$phonenumber;

		if(!isset($phonenumber[1])) {
            $errorResponseArray['reason'] = 'Phone number not provided.';
			return $errorResponseArray;
		}
        
		if($phonenumber[0] != '+' && $user &&
			!($phonenumber[0] == '0' && $phonenumber[1] == '0'))
		{
			if(!$user->country) {
                $errorResponseArray['reason'] = 'User Country is not exist.';
				return $errorResponseArray;
			}
            $len = strlen($user->country->phonenumber_prefix);
            if(substr($phonenumber, 0, $len) != $user->country->phonenumber_prefix){
                $phonenumber = $user->country->phonenumber_prefix . $phonenumber;
            }
			$detectedCountry = $user->country;
		}

		$strippedNumber = ltrim($phonenumber, '+');
		$strippedNumber = ltrim($strippedNumber, '0');
		$strippedNumber = ltrim($strippedNumber, '0');
		$strippedNumber = str_replace(' ', '', $strippedNumber);
		$strippedNumber = str_replace('-', '', $strippedNumber);
		if(!$countries){
			$countries = \App\Models\Country::all();
		}
		//DETECT MAIN TARIFF
		if(!$detectedCountry) {
			foreach ($countries as $country) {
				$len = strlen($country->phonenumber_prefix);
				if(substr($strippedNumber, 0, $len) == $country->phonenumber_prefix){
					$detectedCountry = $country;
					break;
				}
			}
		}
		if(!$detectedCountry){
            $errorResponseArray['reason'] = 'Country is not detected.';
			return $errorResponseArray;
		}

		$expiresAt = Carbon::now()->addMinutes(20);
		\Cache::put('countryCodeOf_' . $phonenumber, strtolower($detectedCountry), $expiresAt);
	
		$countryCode = strtoupper($detectedCountry->code);
		$phoneUtil = \libphonenumber\PhoneNumberUtil::getInstance();
		try {
		    $numberForGoogle = substr($strippedNumber, strlen($detectedCountry->phonenumber_prefix) );
		    $numberProto = $phoneUtil->parse($numberForGoogle, $countryCode);

		    $nationalNumber = $numberProto->getNationalNumber();

//		    if ($nationalNumber != $numberForGoogle){
//		    	$isValid = false;
//		    } else {
//		    	$isValid = $phoneUtil->isValidNumber($numberProto);
//		    }

            $isValid = $phoneUtil->isValidNumber($numberProto);
		    if(!$isValid){
                $errorResponseArray['reason'] = 'Phone number is not valid.';
		    	return $errorResponseArray;
		    }
		} catch (\libphonenumber\NumberParseException $e) {
            $errorResponseArray['reason'] = 'Nimber Validation is failed.';
			return $errorResponseArray;
		}
		$strippedNumber = $phoneUtil->format($numberProto, \libphonenumber\PhoneNumberFormat::E164);
		$strippedNumber = ltrim($strippedNumber, '+');
		//Once main tariff is detected, we will move on for finding real tariff
		//This way we will have in some cases 40X times less operations
        $realTariffs = \Cache::remember('tariffs-' . $countryCode, 10, function() use($detectedCountry) {
            return Tariff::orderBy(\DB::raw('CHAR_LENGTH(prefix)'), 'DESC')
	            ->where('country_id', $detectedCountry->_id)
	            ->get();
        });

        //Detect real tariff
        $detectedTariff = null;
        foreach ($realTariffs as $tariff) {
        	if($tariff->is_disabled){continue;}
        	$len = strlen($tariff->prefix);
        	if(substr($strippedNumber, 0, $len) == $tariff->prefix){
        		$detectedTariff = $tariff;
        		break;
        	}
        }

        if(!$detectedTariff || $detectedTariff->is_blocked == true){
			$errorResponseArray['reason'] = 'Tariff is blocked OR not detected.';
			return $errorResponseArray;
		}
		$detectedTariff->country = $detectedCountry;

        $type = $this->checkIsPhoneNumberMobile($strippedNumber,$user,$countries);

		return ['finalNumber' => $strippedNumber, 'detectedTariff' => $detectedTariff, 'detectedType' => $type];
	}

    public function checkIsPhoneNumberMobile($phonenumber,$user = null,$countries = null) {
        $detectedCountry = null;
        if(!$countries){
            $countries = \App\Models\Country::all();
        }
        if($phonenumber[0] != '+' && $user && $user->country &&
            !($phonenumber[0] == '0' && $phonenumber['1'] == '0'))
        {
            $phonenumber = $user->country->phonenumber_prefix . $phonenumber;
            $detectedCountry = $user->country;
        }
        $strippedNumber = ltrim($phonenumber, '+');
        $strippedNumber = ltrim($strippedNumber, '0');
        $strippedNumber = str_replace(' ', '', $strippedNumber);
        $strippedNumber = str_replace('-', '', $strippedNumber);

        //DETECT MAIN TARIFF
        if (!$detectedCountry) {
            foreach ($countries as $country) {
                $len = strlen($country->phonenumber_prefix);
                if(substr($strippedNumber, 0, $len) == $country->phonenumber_prefix){
                    $detectedCountry = $country;
                    break;
                }
            }
        }
        $countryCode = strtoupper($detectedCountry->code);
        $phoneUtil = \libphonenumber\PhoneNumberUtil::getInstance();
        $numberForGoogle = substr($strippedNumber, strlen($detectedCountry->phonenumber_prefix) );
        $numberProto = $phoneUtil->parse($numberForGoogle, $countryCode);

        return $this->setPhoneNumberType($phoneUtil->getNumberType($numberProto));
    }

    private function setPhoneNumberType($typeInt) {
        switch ($typeInt) {
            case \libphonenumber\PhoneNumberType::FIXED_LINE:
                return "FIXED_LINE";
                break;
            case \libphonenumber\PhoneNumberType::MOBILE:
                return "MOBILE";
                break;
            case \libphonenumber\PhoneNumberType::FIXED_LINE_OR_MOBILE:
                return "FIXED_LINE_OR_MOBILE";
                break;
            case \libphonenumber\PhoneNumberType::TOLL_FREE:
                return "TOLL_FREE";
                break;
            case \libphonenumber\PhoneNumberType::PREMIUM_RATE:
                return "PREMIUM_RATE";
                break;
            case \libphonenumber\PhoneNumberType::SHARED_COST:
                return "SHARED_COST";
                break;
            case \libphonenumber\PhoneNumberType::VOIP:
                return "VOIP";
                break;
            case \libphonenumber\PhoneNumberType::PERSONAL_NUMBER:
                return "PERSONAL_NUMBER";
                break;
            case \libphonenumber\PhoneNumberType::PAGER:
                return "PAGER";
                break;
            case \libphonenumber\PhoneNumberType::UAN:
                return "UAN";
                break;
            case \libphonenumber\PhoneNumberType::EMERGENCY:
                return "EMERGENCY";
                break;
            case \libphonenumber\PhoneNumberType::VOICEMAIL:
                return "VOICEMAIL";
                break;
            case \libphonenumber\PhoneNumberType::SHORT_CODE:
                return "SHORT_CODE";
                break;
            case \libphonenumber\PhoneNumberType::STANDARD_RATE:
                return "STANDARD_RATE";
                break;
        }
        return "UNKNOWN";
    }
}