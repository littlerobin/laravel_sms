<?php

namespace App;

use Illuminate\Auth\Authenticatable;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Model implements AuthenticatableContract, CanResetPasswordContract
{

    use Authenticatable, CanResetPassword, SoftDeletes;

    /**
     * Change timestamp field name.
     */
    //const CREATED_AT = 'post_date';
    //const UPDATED_AT = 'post_modified';

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'users';

    /**
     * The primary key name used by mongo database .
     *
     * @var string
     */
    protected $primaryKey = '_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'email', 'password', 'email_confirmation_token', 'new_email', 'first_name',
        'personal_name', 'company_name', 'role', 'city', 'country_code', 'last_ip', 'is_deleted',
        'language_id', 'password_reset', 'api_token', 'api_token_validity', 'deleted_at',
        'mobile_api_token', 'registered_from', 'vat', 'address', 'timezone', 'postal_code',
        'send_newsletter', 'send_low_balance_notifications', 'notify_when_balance_is_low',
        'facebook_id', 'facebook_access_token', 'google_id', 'google_access_token', 'github_id',
        'is_tts_free', 'birthday', 'is_autobilling_made', 'is_low_balance_notification_send',
        'can_access_beta', 'is_active', 'facebook_email', 'gmail_email', 'github_email',
        'newsletter_email', 'last_seen', 'crisp_history_token','mobile_contacts_upload_token','mobile_contacts_upload_token_expiration','email_confirmed'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token', 'totp_token', 'firebase_id',
        'user_importation_id', 'today_created_tts_count', 'role', 'registered_from',
        'paypal_preapproval_key', 'password_reset', 'mobile_api_token', 'last_used_free_credit_at',
        'last_tts_created_at', 'last_ip', 'is_deleted', 'google_id',
        'google_access_token', 'github_id', 'facebook_id', 'facebook_access_token',
        'email_confirmation_token', 'deleted_at', 'welcome_credit',
        'is_active', 'is_caller_id_added', 'admin_token', 'admin_token_expiration_date',
        'stripe_customer_id',
    ];

    protected $dates = [
        'created_at',
        'updated_at',
        'birthday',
        'last_seen'
    ];

    /**
     * Get users language.
     */
    public function language()
    {
        return $this->belongsTo('App\Models\Language');
    }

    /**
     * Get users campaigns.
     */
    public function campaigns()
    {
        return $this->hasMany('App\Models\Campaign');
    }

    /**
     * Get users billings.
     */
    public function billings()
    {
        return $this->hasMany('App\Models\Billing');
    }

    /**
     * Get users numbers.
     */
    public function numbers()
    {
        return $this->hasMany('App\Models\CallerId')->with('tariff');
    }

    /**
     * Get importation details of the user.
     */
    public function importation()
    {
        return $this->belongsTo('App\Models\UserImportation', 'user_importation_id', '_id');
    }

    /**
     * Get temporary billings of the user.
     */
    public function tempBillings()
    {
        return $this->hasMany('App\Models\TempBilling');
    }

    /**
     * Get all notifications for the user.
     */
    public function notifications()
    {
        return $this->hasMany('App\Models\Notification');
    }

    /**
     * Get all number files of the user.
     */
    public function number_files()
    {
        return $this->hasMany('App\Models\NumberFile');
    }

    /**
     * Get all voice files of the user.
     */
    public function files()
    {
        return $this->hasMany('App\Models\File');
    }

    /**
     * Get users contacts from addressbook
     */
    public function addressBookContacts()
    {
        return $this->hasMany('App\Models\AddressBookContact');
    }

    /**
     * Get users groups in addressbook
     */
    public function addressBookGroups()
    {
        return $this->hasMany('App\Models\AddressBookGroup');
    }

    /**
     * Get users groups in addressbook with contact
     */
    public function addressBookGroupsWithContactsCount()
    {
        return $this->hasMany('App\Models\AddressBookGroup')->with('contactCount');
    }

    /**
     * Get api keys of the user.
     */
    public function apiKeys()
    {
        return $this->hasMany('App\Models\ApiKey');
    }

    /**
     * Get api tokens of the user.
     */
    public function apiTokens()
    {
        return $this->hasMany('App\Models\ApiToken');
    }

    /**
     * Get all phonenumbers of the user
     */
    public function phonenumbers()
    {
        return $this->hasMany('App\Models\Phonenumber');

    }

    /**
     * Get all archived phonenumbers of the user
     */
    public function archivedPhonenumbers()
    {
        return $this->hasMany('App\Models\ArchivedPhonenumber');
    }

    /**
     * Get all calls of the user
     */
    public function calls()
    {
        return $this->hasMany('App\Models\Call');
    }

    /**
     * Get all archived calls of the user
     */
    public function archivedCalls()
    {
        return $this->hasMany('App\Models\ArchivedCall');
    }

    /**
     * Get country of the user
     */
    public function country()
    {
        return $this->belongsTo('App\Models\Country', 'country_code', 'code');
    }

    /**
     * Get country of the user from caller id
     */
    public function callerIdCountry()
    {
        return $this->belongsTo('App\Models\Country', 'caller_id_country_code', 'code');
    }

    /**
     * Get coupons of the user
     */
    public function coupons()
    {
        return $this->hasMany('App\Models\Coupon');
    }

    /**
     * Get all invoices of the user
     */
    public function invoices()
    {
        return $this->hasMany('App\Models\Invoice');
    }

    /**
     * Get all submitted tickets fo the user
     */
    public function tickets()
    {
        return $this->hasMany('App\Models\SupportTicket');
    }

    /**
     * Get all queues jobs of the user
     */
    public function jobs()
    {
        return $this->hasMany('App\Models\BackgroundJob');
    }

    /**
     * Get mobile groups of the user
     */
    public function mobileGroups()
    {
        return $this->hasMany('App\Models\MobileMessageGroup')->with('campaigns');
    }

    public function snippets()
    {
        return $this->hasMany('App\Models\Snippet');
    }

    public function localCards()
    {
        return $this->hasMany('App\Models\StripeCard');
    }

    /*
     * Get tags of the customer
     */
    public function tags()
    {
        return $this->belongsToMany(Models\Tag::class, 'user_tags');
    }

    /**
     * Get sms custom cost of the user
     */
    public function smsTariffs()
    {
        return $this->hasMany('App\Models\UserSmsCost', 'user_id','_id');
    }
}
