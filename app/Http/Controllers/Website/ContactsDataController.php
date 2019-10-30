<?php

namespace App\Http\Controllers\Website;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Auth;

class ContactsDataController extends Controller
{
    public function getContactsAdded($groupId)
    {
        $group = Auth::user()->addressBookGroups()
                             ->where('_id', $groupId)
                             ->with('contacts')->first();
        $contactsCount = $group->contacts->count();

        $response = [
            'error' => [
                'no' => 0,
                'text' => 'valid_contacts_count_success'
            ],
            'valid_contacts_count' => $contactsCount
        ];

        return response()->json(['resource' => $response]);
    }
}
