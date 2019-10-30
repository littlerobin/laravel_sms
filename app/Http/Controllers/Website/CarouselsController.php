<?php

namespace App\Http\Controllers\Website;

use App\Http\Controllers\Controller;
use App\Models\Carousel;
use Auth;
use Illuminate\Http\Request;

class CarouselsController extends Controller
{
    /**
     * Display list of resources.
     *
     * @param  Request $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $user->load(['tags', 'country', 'snippets', 'campaigns', 'billings']);
        $carousel = Carousel::has('tags', '=', 0, 'or', function ($query) use ($user) {
            $query->whereIn('tags._id', $user->tags->lists('_id')->toArray());
        })->has('countries', "=", 0, 'or', function ($query) use ($user) {
            $query->where('countries.code', $user->country_code);
        })->whereHas('condition', function ($query) use ($user) {
            $query
            // No condition
            ->where('carousel_conditions._id', 1)
                ->orWhere(function ($query) use ($user) {
                    if ($user->snippets()->count() == 0) {
                        // User hasn't got a ctc
                        $query->where('carousel_conditions._id', 2);
                    } else {
                        return false;
                    }
                })
                ->orWhere(function ($query) use ($user) {
                    if ($user->campaigns()->count() == 0) {
                        // User hasn't got a vm
                        $query->where('carousel_conditions._id', 3);
                    } else {
                        return false;
                    }
                })
                ->orWhere(function ($query) use ($user) {
                    if ($user->first_time_bonus && $user->billings()->count() == 0) {
                        // User received welcome credit and still not spent it
                        $query->where('carousel_conditions._id', 4);
                    } else {
                        return false;
                    }
                });
        })->orderBy('priority', 'DESC')->get();

        $response = [
            'error' => [
                'no' => 0,
                'message' => 'campaign_data_2',
            ],
            'carousel' => $carousel,
        ];

        return response()->json(['resource' => $response]);
    }
}
