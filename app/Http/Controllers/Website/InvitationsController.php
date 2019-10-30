<?php

namespace App\Http\Controllers\Website;

use App;
use App\Models\ApiToken;
use App\Models\InvitationParam;
use App\Models\Language;
use App\Services\ActivityLogService;
use App\Services\CookieService;
use App\Services\InfoService;
use App\Services\SlackNotificationService;
use App\User;
use Auth;
use Carbon\Carbon;
use Cookie;
use DB;
use Exception;
use Illuminate\Http\Request;
use JWTAuth;
use Session;
use Validator;

class InvitationsController extends WebsiteController
{
    /**
     * Create a new instance of InvitationsController class
     *
     * @return void
     */
    public function __construct(Request $request)
    {
        $isEUMember = CookieService::checkEU($request);
        view()->share('tab', '');
        view()->share('isEUMember', $isEUMember);
        view()->share('languages', Language::whereIsActive(1)->where('code', '<>', 'db')->get());
    }

    /**
     * Check if email es seen.
     *
     * @param string $token
     * @return \Illuminate\Http\Response
     */
    public function seen($token)
    {

        $invitation = InvitationParam::whereToken($token)->whereStatus('SENT')->first();
        if ($invitation) {
            if ($invitation->lead_id) {
                $invitation->load(['lead']);
                $invitation->lead->views += 1;
                $invitation->lead->save();
            }
            $invitation->status = 'SEEN';
            $invitation->save();
        }
        $file = file_get_contents(public_path() . "/pixel.png");
        return response($file, 200)->header('Content-Type', 'image/png');
    }

    /**
     * Unsubscribe user.
     *
     * @param string $lang
     * @param string $token
     * @return void
     */
    public function unsubscribe($lang, $token)
    {
        $invitation = InvitationParam::whereToken($token)
            ->whereNotIn('status', ['BONUS', 'COMPLETED'])
            ->with(['lead', 'customer'])
            ->first();
        if (!is_null($invitation)) {
            if ($invitation->lead) {
                $lead = $invitation->lead;
                if ($invitation->status == 'SENT') {
                    $lead->views += 1;
                }
                $lead->status = "UNSUBSCRIBED";
                $lead->save();
            } elseif ($invitation->customer) {
                $customer = $invitation->customer;
                $customer->send_newsletter = 0;
                $customer->save();
            }
            $invitation->status = "UNSUBSCRIBED";
            $invitation->save();
            return view('invitations.unsubscribe', compact('token'));
        }
        return redirect('/');
    }

    /**
     * Subscribe user.
     *
     * @param string $lang
     * @param string $token
     * @return void
     */
    public function subscribe($lang, $token)
    {
        $invitation = InvitationParam::whereToken($token)
            ->whereStatus('UNSUBSCRIBED')
            ->with(['lead', 'customer'])
            ->first();
        if (!is_null($invitation)) {
            if ($invitation->lead) {
                $lead = $invitation->lead;
                $lead->status = "ACTIVE";
                $lead->save();
            } elseif ($invitation->customer) {
                $customer = $invitation->customer;
                $customer->send_newsletter = 1;
                $customer->save();
            }
            $invitation->status = "SEEN";
            $invitation->save();
            return view('invitations.subscribe');
        }
        return redirect('/');
    }

    /**
     * Register from invitation.
     *
     * @param string $lang
     * @param string $token
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function register($lang, $token, Request $request)
    {
        $request->session()->put('invitation_token', $token);
        $invitation = InvitationParam::whereToken($token)
            ->whereNotIn('status', ['BONUS', 'COMPLETED'])
            ->with(['lead', 'customer'])
            ->first();

        if (!is_null($invitation)) {
            $status = $invitation->status;
            $invitation->status = "REGISTER";
            $invitation->save();

            if ($invitation->lead) {
                if ($status == 'SENT') {
                    $invitation->lead->views += 1;
                    $invitation->lead->save();
                }
                $customer = User::whereEmail($invitation->lead->email)->first();
                if ($customer) {
                    if ($customer->is_active) {
                        $invitation->user_id = $customer->_id;
                        $invitation->status = "COMPLETED";
                        $invitation->save();
                        $invitation->lead->status = "ALREADY REGISTERED";
                        $invitation->lead->save();
                        return redirect('/login');
                    } else {
                        $customer->delete();
                    }
                }
                return view('front.invitation', compact('token'));
            } elseif ($invitation->customer) {
                $this->checkInvitation($invitation->customer);
                return redirect('/login');
            }
        }
        return redirect('/');
    }

    /**
     * Sign up user.
     *
     * @param string $token
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function postRegister($token, Request $request)
    {
        $locale = $request->cookie('callburn-locale', 'en');
        app()->setLocale($locale);
        $invitation = InvitationParam::whereToken($token)->with(['lead', 'customer'])->first();
        $user = User::whereEmail($invitation->email)->first();
        if (is_null($invitation) || is_null($invitation->email)) {
            return response()->json([
                'status' => 'error',
                'message' => trans('crud.something_went_wrong'),
            ], 404);
        } elseif ($user) {
            return response()->json([
                'status' => 'error',
                'message' => trans('validation.unique', ['attribute' => 'email']),
            ], 409);
        } else {
            $validator = Validator::make([
                'password' => $request->password,
                'password_confirmation' => $request->password_confirmation,
            ], [
                'password' => 'confirmed|required|min:4|max:20',
            ]);
            if ($validator->fails()) {
                $message = $validator->errors()->first();

                return response()->json([
                    'status' => 'error',
                    'message' => $message,
                ], 400);
            }
        }

        DB::beginTransaction();
        try {
            $language = Language::where('code', app()->getLocale())->first();
            $userData = [
                'email' => $invitation->email,
                'password' => bcrypt($request->password),
                'is_active' => true,
                'last_ip' => $request->ip(),
                'language_id' => $language->_id,
                'local_date_format' => $request->localDateFormat,
            ];
            $timezone = InfoService::getTimezoneName($request);
            $countryCode = InfoService::getCountryCode($request);

            if ($countryCode) {
                $userData['country_code'] = $timezone;
            }

            if ($timezone) {
                $userData['timezone'] = $timezone;
            }
            $user = User::create($userData);

            SlackNotificationService::notify('User registered with email from invitation - ' . $invitation->email);

            $logData = [
                'user_id' => $user->_id,
                'device' => 'WEBSITE',
                'action' => 'REGISTRATION-LOGIN',
                'description' => 'User has been registered from invitation',
            ];
            $activityLogRepo = new ActivityLogService;
            $activityLogRepo->createActivityLog($logData);
            $credentials = ['email' => $user->email, 'password' => $request->password];
            $jwtToken = JWTAuth::attempt($credentials);

            Auth::login($user);

            $token = new ApiToken();
            $token->user_id = $user->_id;
            $token->api_token = str_random(10);
            $token->ip_address = $request->ip();
            $token->agent = '';
            $token->device = 'WEBSITE';
            $token->api_token_validity = Carbon::now()->addMinutes(60)->toDateTimeString();
            $token->session_id = Session::getId();
            $token->save();
            $response = [
                'user' => $user,
                'jwtToken' => $jwtToken,
            ];
            $this->checkInvitation($user);
            DB::commit();
            return response()->json(['resource' => $response]);
        } catch (Exception $e) {
            \Log::error($e);
            DB::rollback();
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(), //trans('crud.something_went_wrong'),
            ], 409);
        }
    }

    /**
     * Get invitation if it's exists in session.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function invitation(Request $request)
    {
        $token = $request->session()->get('invitation_token');
        $invitation = InvitationParam::whereToken($token)->with(['lead', 'customer'])->first();
        return response()->json([
            'status' => 'success',
            'resource' => $invitation,
        ]);
    }

    public function checkInvitation($user)
    {
        if (session()->get('invitation_token')) {
            $invitation = InvitationParam::whereToken(session()->get('invitation_token'))->first();
            if (!is_null($invitation)) {
                $expirationDate = $invitation->bonus_expiration_date;
                if (is_null($expirationDate) || $expirationDate->diffInDays(Carbon::today()) >= 0) {
                    if ($invitation->bonus_criteria && $invitation->bonus) {
                        $user->bonus += $invitation->bonus;
                        $user->bonus_criteria = $invitation->bonus_criteria;
                        $user->balance += $invitation->bonus;
                        $user->save();
                        $invitation->status = "BONUS";
                        $invitation->save();
                    } elseif ($invitation->bonus) {
                        $user->balance += $invitation->bonus;
                        $user->save();
                        $invitation->status = "BONUS";
                        $invitation->save();
                    }
                }
                $invitation->makeAsAccepted($user);
            }
        }
    }
}
