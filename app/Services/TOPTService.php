<?php namespace App\Services;

use App\Models\TempBilling;

class TOPTService{

	/**
	 * Returns a HMAC-based one time password
	 *
	 * It is up to the user to select the secret and the moving factor (aka "counter"). Possible
	 * solutions are
	 *
	 * - Time based (see {@see generateTimebased()} below)
	 * - Counter (increase value by 1 each time a code is generated)
	 * - Random value displayed to the user too
	 *
	 * _Never_ reuse any value for moving factor. Especially if an authentication fails, change
	 * the moving factor for the next request anyway. Also you should never reuse secrets.
	 *
	 * Before using, think about how you create the secret. A secret like `md5($passwordMD5)` is as
	 * safe as a md5-hashed password itself (hint: it isn't). The best way is to create a completely
	 * random binary secret and show it to the user as readable (for example) Base32-encoded string.
	 *
	 * RFC4226 compliant http://tools.ietf.org/html/rfc4226
	 *
	 * @param string      $secret           Binary string of secret
	 * @param int         $codeDigits
	 * @return string
	 */
	private function generateHMAC($secret, $codeDigits)
	{
	    $hash = hash_hmac('sha1', (int) (time() / 30), $secret);
	    $offset = hexdec(substr($hash, -1));
	    $binary = hexdec(substr($hash, $offset * 2, 8)) & 0x7fffffff;
	    $otp = $binary % pow(10, $codeDigits);
	    return str_pad($otp, $codeDigits, '0', \STR_PAD_LEFT);
	}

	/**
	 * Returns a time-based one time password
	 *
	 * Compatible to Google Authenticator with
	 *
	 * - $secret = Base32\decode($token)
	 * - $digits = 6
	 *
	 * RFC6238 http://tools.ietf.org/html/rfc6238 compliant
	 *
	 * @param string      $secret    Binary string of shared token
	 * @param int         $digits
	 * @return string
	 */
	public function generateTimebased ($secret, $digits)
	{
	    return $this->generateHMAC($secret, $digits);
	}
}