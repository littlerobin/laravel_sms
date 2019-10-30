<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;

class FrontController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function __construct(Request $request)
    {
        $isEUMember = \App\Services\CookieService::checkEU($request);
        View::share('tab', '');
        View::share('isEUMember', $isEUMember);
        View::share('languages', \App\Models\Language::where('is_active', 1)->where('code', '<>', 'db')->get());
    }

    public function getIndex()
    {
        return view('front.index');
    }

    public function getLogin()
    {
        return view('front.authentication', ['authPart' => 'login']);
    }

    public function getRegister()
    {
        return view('front.authentication', ['authPart' => 'registration']);
    }

    public function getClickToCall()
    {
        return view('front.clicktocall', ['tab' => 'click-to-call']);
    }

    public function getVoiceMessage()
    {
        return view('front.voicemessage', ['tab' => 'voice-message']);
    }

    public function getSms()
    {
        return view('front.sms', ['tab' => 'sms']);
    }

    public function getDocumentation()
    {
        return view('front.documentation', ['tab' => 'doc']);
    }

    public function getContactUs()
    {
        return view('front.contact_us', ['tab' => 'contact-us']);
    }

    public function getIframeContentClicktocall()
    {
        return view('front.iframes.clicktocall', ['tab' => 'click-to-call']);
    }

    public function getIframeContentVoiceMessages()
    {
        return view('front.iframes.voicemessages', ['tab' => 'voice-message']);
    }

    public function getIframeNavbar()
    {
        return view('front.iframes.navbar');
    }

    public function getPrivacy()
    {
        $locale = \App::getLocale();

        if (!view()->exists('front.partials.privacy.' . $locale . '.terms')) {
            $locale = 'en';
        }

        return view('front.privacy', ['locale' => $locale]);
    }

    public function getFinishRegistration($lang, $token, $email)
    {
        $user = \App\User::where('email', $email)->with('numbers')->first();
        if($token != 'activation') {
            if(!$user->email_confirmed) {
                $user = $user->where('email_confirmation_token',$token)->with('numbers')->first();
                if (!$user) {
                    abort('404');
                }

                \App\User::where('email', $email)->update(['email_confirmed' => 1,'email_confirmation_token' => null]);
            }

            if($user->numbers->count()) {
                $leastOneNumberIsConfirmed = false;
                foreach ($user->numbers as $number) {
                    if($number->is_verified) {
                        $leastOneNumberIsConfirmed = true;
                    }
                }
                if($leastOneNumberIsConfirmed) {
                    return view('front.voicemessage', ['tab' => 'voice-message']);
                }
            }
        }

        return view('front.authentication', ['authPart' => 'phoneNumberVerification'], ['tab' => 'finishRegister']);
    }

    public function getPasswordReset($lang, $token)
    {
        $user = \App\User::where('password_reset', $token)->first();

        if (!$user) {
            return redirect()->back();
        }

        return view('front.authentication', ['authPart' => 'passwordReset']);
    }

    public function getDevelopers()
    {
        return view('front.developers' ,['tab' => 'developers']);
    }

    public function getApi()
    {
        return view('front.api', ['tab' => 'api']);
    }

    public function getAffiliation()
    {
        return view('front.affiliation', ['tab' => 'affiliation']);
    }
}
