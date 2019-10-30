<?php

namespace App\Http\Controllers\Website;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Auth;

class SupportTicketsController extends WebsiteController
{

    /**
     *
     * Create a new instance of SupportTicketsController class
     *
     * @return void
     */
    public function __construct()
    {

    }

    /**
     * Send request for creating new support ticket.
     * POST /tickets/create-ticket
     *
     * @param Request $request
     * @return JSON
     */
    public function postCreateTicket(Request $request)
    {
        $user = Auth::user();
        $type = $request->get('type');
        $subject = $request->get('subject');
        $context = $request->get('context');
        $data = [
            'user_id' => $user->_id,
            'type' => $type,
            'subject' => $subject,
            'context' => $context,
            'country_code' => $user->country_code
        ];

        $supportTicket = \App\Models\SupportTicket::create($data);
        $this->notifyAdmins('User ' . $user->email . ' created the ticket');
        $response = [
            'error' => [
                'no' => 0,
                'text' => 'ticket_successfully_submited'
            ],
            'support_ticket' => $supportTicket 
        ];
        return response()->json(['resource' => $response]);
    }

    /**
     * Send request for getting all tickets.
     * GET /tickets/tickets
     *
     * @return JSON
     */
    public function getTickets(Request $request)
    {
        $user = Auth::user();

        $status = $request->get('status');
        $keyword = $request->get('keyword');
        $page = $request->get('page');
        $tickets = $user->tickets();
        if($status){
            if($status == "OPEN") {
                $tickets = $tickets->whereIn('status', [$status,'IN_PROGRESS']);
            } else {
                $tickets = $tickets->where('status', $status);
            }

        }
        if($keyword){
            $tickets = $tickets->where('subject', 'LIKE', '%' . $keyword . '%');
        }
        $count = $tickets->count();
        $tickets = $tickets->skip($page * 5)->take(5)->with('replies')->orderBy('_id','desc')->get();
        $response = [
            'error' => [
                'no' => 0,
                'text' => 'tickets_of_the__user_1'
            ],
            'count' => $count,
            'tickets' => $tickets
        ];
        return response()->json(['resource' => $response]);
    }


    /**
     * Send request for replying to ticket
     * POST /tickets/reply-to-ticket
     *
     * @param Request $request
     * @return JSON
     */
    public function postReplyToTicket(Request $request)
    {
        $user = Auth::user();
        $ticketId = $request->get('ticket_id');
        $message = $request->get('message');
        $ticket = $user->tickets()->find($ticketId);
        if(!$ticket){
            $response = $this->createBasicResponse(-1, 'ticket_does__not_exist_1');
            return response()->json(['resource' => $response]);
        }

        $data = [
            'user_id' => $user->_id,
            'ticket_id' => $ticketId,
            'message' => $message
        ];

        $ticketReply = \App\Models\SupportTicketReply::create($data);
        $this->notifyAdmins('User ' . $user->email . ' replied to the ticket');
        $response = [
            'error' => [
                'no' => 0,
                'text' => 'reply__was_submitted_1'
            ],
            'ticket_reply' => $ticketReply 
        ];
        return response()->json(['resource' => $response]);
    }

    /**
     * Send request for replying to ticket
     * POST /tickets/close-ticket
     *
     * @param Request $request
     * @return JSON
     */
    public function postCloseTicket(Request $request)
    {
        $user = Auth::user();
        $key = isset($_COOKIE['api_key']) ? $_COOKIE['api_key']: '';
        $ticketId = $request->get('ticket_id');
        $ticket = $user->tickets()->find($ticketId);
        if(!$ticket){
            $response = $this->createBasicResponse(-1, 'ticket_does__not_exist_1');
            return response()->json(['resource' => $response]);
        }
        $ticket->status = 'CLOSED';
        $ticket->save();
        $this->notifyAdmins('User ' . $user->email . ' closed the ticket');
        $response = $this->createBasicResponse(0, 'ticket_was__closed_1');
        return response()->json(['resource' => $response]);
    }

    /**
     * Send request for reopening to ticket
     * POST /tickets/close-ticket
     *
     * @param Request $request
     * @return JSON
     */
    public function postReopenTicket(Request $request)
    {
        $user = Auth::user();
        $ticketId = $request->get('ticket_id');
        $ticket = $user->tickets()->find($ticketId);
        if(!$ticket){
            $response = $this->createBasicResponse(-1, 'ticket_does__not_exist_1');
            return response()->json(['resource' => $response]);
        }
        $ticket->status = 'REOPENED';
        $ticket->save();

        $this->notifyAdmins('User ' . $user->email . ' reopened the ticket');

        $response = $this->createBasicResponse(0, 'ticket_was_reopened_1');
        return response()->json(['resource' => $response]);
    }

    /**
     * Put notifications for all admins
     *
     * @param string $text
     * @return bool
     */
    private function notifyAdmins($text)
    {
        $admins = \App\User::where('role', 'administrator')->get();
        $notificationsData = [];
        foreach ($admins as $admin) {
            $notificationsData[] = [
                'text' => $text,
                'route' => 'tickets',
                'user_id' => $admin->_id,
                'created_at' => date('Y-m-d H:i:s')
            ];
        }
        return \App\Models\Notification::insert($notificationsData);
    }

}