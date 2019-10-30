<?php

namespace App\Http\Controllers\Website;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Auth;

class NotificationsController extends WebsiteController
{
    /**
     * Create a new instance of CampaignsController class.
     *
     * @return void
     */
    public function __construct()
    {
        $this->notification = new \App\Models\Notification();
    }

    /**
     * Get notifications of the user
     * GET /notifications/notifications
     *
     * @param Request $request
     * @return JSON
     */
    public function getNotifications (Request $request)
    {
        $user = Auth::user();
        $page = $request->get('page', 1);
        $take = 10;
        $skip = ($page - 1) * $take;

    	$notifications = $user->notifications()->skip($skip)->take($take)->get();
        $notificationsCount = $notifications->count();
        $moreNotifications = $notificationsCount % 10 === 0 ? true : false;

    	$response = [
    		'error' => [
    			'no' => 0,
    			'text' => 'Notifications__1'
    		],
    		'notifications' => $notifications,
            'more_notifications' => $moreNotifications
    	];
    	return response()->json(['resource' => $response]);
    }

    /**
     * Mark notifications as seen
     * POST /notifications/mark-as-seen
     * 
     * @param Request $request
     * @return JSON
     */
    public function postMarkAsSeen(Request $request)
    {
    	$user = Auth::user();
    	$user->notifications()->update(['is_seen' => 1]);
    	$response = $this->createBasicResponse(0, 'Marked__1');
    	return response()->json(['resource' => $response]);
    }


    /**
     * Remove notification
     * POST /notifications/remove-notification
     * 
     * @param Request $request
     * @return JSON
     */
    public function postRemoveNotification(Request $request)
    {
        $notificationId = $request->get('notification_id');
        $user = Auth::user();

        $notification = $user->notifications()->where('_id', $notificationId)->first();

        if($notification->route == "tickets") {
            $user->notifications()->where('route', "tickets")->where('text',$notification->text)->delete();

        } else {
            $user->notifications()->where('_id', $notificationId)->delete();
        }

        event(new \App\Events\UserDataUpdated( [
            'user_id' => $user->_id] ));

        $response = $this->createBasicResponse(0, 'Removed__1');
        return response()->json(['resource' => $response]);
    }

    /**
     * Remove notifications
     * POST /notifications/remove-notifications
     *
     * @param Request $request
     * @return JSON
     */
    public function postRemoveNotifications()
    {
        $user = Auth::user();
        $user->notifications()->delete();

        event(new \App\Events\UserDataUpdated( [
            'user_id' => $user->_id] ));

        $response = $this->createBasicResponse(0, 'All_Contacts_Removed');
        return response()->json(['resource' => $response]);
    }
}