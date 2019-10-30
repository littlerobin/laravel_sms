<?php

namespace App\Http\Controllers\Website;

use App\Http\Controllers\Controller;
use App\Models\Service;

class ServicesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function callburn()
    {
        $response = [
            'error' => [
                'no' => 0,
                'text' => 'get_all_services',
            ],
            'services' => Service::whereSource('callburn')->get(),
        ];
        return response()->json(['resource' => $response]);
    }
}
