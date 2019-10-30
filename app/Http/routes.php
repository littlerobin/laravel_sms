<?php

Route::group(['prefix' => 'v1/mobile', 'namespace' => 'Mobile'], function () {
    Route::controller('auth', 'AuthController');
    Route::controller('addressbook', 'AddressBookController');
    Route::post('contacts/{token}', 'AddressBookController@syncMobileContacts');
});


Route::group(['domain' => config('api.apiurl'), 'prefix' => 'v1/api', 'namespace' => 'Api'], function () {
    Route::get('/', function () {
        return \Response::json(['error' => 'You should not be here']);
    });
    Route::post('messages', 'MessagesController@postCreateMessage');
    Route::get('messages', 'MessagesController@getIndex');
    Route::get('messages/{id}', 'MessagesController@getShow');
    Route::get('tts', 'TTSController@getIndex');
});

Route::group(['namespace' => 'ClickToCall', 'middleware' => ['header', 'snippet']], function () {
    Route::post('/main-js/{token}/{offset}', ['uses' => 'ApiController@postMainJavascript']);
    Route::controller('ctc', 'ApiController');
});

Route::group(['namespace' => 'ClickToCall', 'domain' => 'betacallme.callburn.com'], function () {
    Route::get('/{snippetid?}', ['uses' => 'ApiController@getShowCallburnHostedSnippet']);
});

Route::group(['namespace' => 'ClickToCall', 'domain' => 'callme.callburn.com'], function () {
    Route::get('/{snippetid?}', ['uses' => 'ApiController@getShowCallburnHostedSnippet']);
});

Route::group(['namespace' => 'ClickToCall', 'domain' => 'llamame.callburn.com'], function () {
    Route::get('/{snippetid?}', ['uses' => 'ApiController@getShowCallburnHostedSnippet']);
});

Route::group(['namespace' => 'ClickToCall', 'domain' => 'chiamami.callburn.com'], function () {
    Route::get('/{snippetid?}', ['uses' => 'ApiController@getShowCallburnHostedSnippet']);
});

Route::post('metronic/update-translations', 'MetronicController@postUpdateTranslations');

Route::group(['namespace' => 'Website'], function () {
    Route::get('services-callburn', "ServicesController@callburn");
    Route::get('invitations/{token}/seen', "InvitationsController@seen");
    Route::get('invitations/invitation', "InvitationsController@invitation");
    Route::post('invitations/{token}/register', "InvitationsController@postRegister");
    Route::get('invitations/social/{token}/facebook-callback', "InvitationsController@facebookCallback");
    Route::group(['middleware' => 'language'], function () {
        Route::get('{lang}/invitations/{token}/unsubscribe', "InvitationsController@unsubscribe");
        Route::get('{lang}/invitations/{token}/subscribe', "InvitationsController@subscribe");
        Route::get('{lang}/invitations/{token}/register', "InvitationsController@register");
    });
    Route::post('/verification/send-verification-code-front', ['uses' => 'VerificationsController@postSendVerificationCode']);
    Route::post('/verification/check-voice-code-validation-front', ['uses' => 'VerificationsController@postCheckVoiceCodeValidation']);
    Route::get('/snippets/export-statistics', ['uses' => 'SnippetsController@getExportStatistics']);
    Route::get('/snippets/wordpress-plugin', ['uses' => 'SnippetsController@getWordPressPlugin']);
    Route::controller('address-book', 'AddressBookController');
    Route::group(['middleware' => ['jwt.headers', 'header', 'jwt.auth', 'active.user', 'last.seen']], function () {
        Route::controller('stripe', 'StripeController');
        Route::get('carousel', 'CarouselsController@index');
        Route::controller('contacts-data', 'ContactsDataController');
        Route::controller('api-keys', 'ApiKeysController');
        Route::controller('audio-files', 'AudioFilesController');
        Route::controller('verifications', 'VerificationsController');
        Route::controller('campaigns', 'CampaignsController');
        Route::controller('notifications', 'NotificationsController');
        Route::controller('tickets', 'SupportTicketsController');
        Route::controller('users', 'UsersController');
        Route::group(['prefix' => 'snippets'], function () {
            Route::get('/get-api-js/{id}', ['uses' => 'SnippetsController@getApiJavascript']);
            Route::get('/show-statistics/{id}', ['uses' => 'SnippetsController@getShowStatistics']);
            Route::get('/call-routes', ['uses' => 'SnippetsController@getCallRoutes']);
            Route::get('/get-caller-ids', ['uses' => 'SnippetsController@getCallerIds']);
            Route::post('/get-merged-date', ['uses' => 'SnippetsController@getMergedDate']);
            Route::post('/retry', ['uses' => 'SnippetsController@postRetryPhoneNumber']);
            Route::post('/remove-all-pending', ['uses' => 'SnippetsController@postRemoveAllFromPending']);
            Route::post('/add-remove-pending', ['uses' => 'SnippetsController@postAddRemovePending']);
            Route::post('/check-url', ['uses' => 'SnippetsController@postCheckUrl']);
            Route::post('/upload-snippet-file', ['uses' => 'SnippetsController@postUploadSnippetFile']);
            Route::post('/upload-image-file', ['uses' => 'SnippetsController@postUploadImageFile']);
            Route::post('/enable-or-disable', ['uses' => 'SnippetsController@postEnableOrDisable']);
            Route::post('/call-now', ['uses' => 'SnippetsController@postCallNow']);
            Route::post('/cancel-schedulation', ['uses' => 'SnippetsController@postStatisticsSchedulation']);
            Route::post('/cancel-cancellation', ['uses' => 'SnippetsController@postStatisticsSchedulation']);
            Route::post('/holiday-mode', ['uses' => 'SnippetsController@postSaveHolidayMode']);
            Route::post('/enable-or-disable-holiday-mode', ['uses' => 'SnippetsController@postEnableOrDisableHolidayMode']);
            Route::post('/send-email-integration-codes', ['uses' => 'SnippetsController@postSendEmailIntegrationCodes']);
        });
    });

    Route::resource('snippets', 'SnippetsController');
    Route::controller('billings', 'BillingsController');
    Route::get('/admin-login/{token}', 'AdminLoginController@tokenCheck');
    Route::controller('autobillings', 'AutobillingsController');
    Route::controller('phonenumbers', 'PhonenumbersController');
    Route::controller('data', 'DataController');
    Route::group(['middleware' => ['jwt.headers']], function () {
        Route::get('auth/refresh-token', 'AuthController@RefreshToken');
    });
    Route::group(['middleware' => 'language.control'], function () {
        Route::controller('auth', 'AuthController');
    });
    Route::controller('social', 'SocialConnectionsController');
    Route::get('callback', 'SocialConnectionsController@getFacebookCallback');
    Route::get('callback-google', 'SocialConnectionsController@getGoogleCallback');
    Route::get('callback-github', 'SocialConnectionsController@getGitHubCallback');

    Route::get('isp/sms-response', 'SmsController@response');
});



Route::group(['namespace' => 'Front', 'middleware' => 'language.control'], function () {
    Route::controller('/front-data', 'FrontDataController');
});

Route::group(['namespace' => 'Front', 'middleware' => 'language'], function () {
    Route::controller('{lang}', 'FrontController');
    Route::get('{lang}', 'FrontController@getVoiceMessage');
    Route::get('/', 'FrontController@getVoiceMessage');
    
});
