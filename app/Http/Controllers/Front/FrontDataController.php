<?php

namespace App\Http\Controllers\Front;

use App\Http\Middleware\Snippet;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\Services\YoutubeService;

class FrontDataController extends Controller
{
    protected $youtubeService;

    public function __construct(YoutubeService $youtubeService)
    {
        $this->youtubeService = $youtubeService;
    }

    public function postLanguages()
    {
        $languages = \App\Models\Language::where('code','<>','db')->get();

        $response = [
            'error' => [
                'no' => 0,
                'text' => 'all__timezones'
            ],
            'languages' => $languages
        ];
        return response()->json(['resource' => $response]);

    }


    public function getTrans(Request $request)
    {
        $part = $request->get('part');
        $keys = json_decode($request->get('keys'));
        $translations = [];

        foreach ($keys as $key) {
            $translations[$key] = trans($part . '.' . $key);
        }

        $response = [
            'error' => [
                'no' => 0,
                'text' => ''
            ],
            'translations' => $translations
        ];
        return response()->json(['resource' => $response]);


    }

    public function getYoutubeTitles(Request $request) {

        $ids = array_values($request->all());

        $titles = \App\Services\YoutubeService::getTitles($ids);

        if($titles) {
            $response = [
                'error' => [
                    'no' => 0,
                    'text' => ''
                ],
                'titles' => $titles
            ];

            return response()->json(['resource' => $response]);
        }

        $response = [
            'error' => [
                'no' => -10,
                'text' => ''
            ],

        ];

        return response()->json(['resource' => $response]);

    }


    public function postLanguage(Request $request) {

        $referer = parse_url ($request->header('referer') , PHP_URL_PATH);

        $segments = array_values(array_filter(explode("/",$referer)));

        $locale = $request->get('locale');

        $segments[0] = $locale;
        $to = implode('/', $segments);


        $response = [
            'error' => [
                'no' => 0,
                'text' => '',
                'to' => $to,
            ],

        ];
        return response()->json(['resource' => $response])
            ->withCookie(cookie()->forever('callburn-locale', $locale));


    }


    public function postCheckSnippetDomain(Request $request) {

        $subDomain = $request->get('subdomain');

        $snippet = \App\Models\Snippet::isPublished(1)->isActive(1)->where('subdomain',$subDomain)->first();

        if(!$snippet) {

            $response = [
                'error' => [
                    'no' => -10,
                    'text' => ''
                ],

            ];
        } else {

            $response = [
                'error' => [
                    'no' => 0,
                    'text' => '',
                ],

                'subdomain' => $snippet->subdomain,

            ];


        }


        return response()->json(['resource' => $response]);
    }

    public function getPlaylists(Request $request)
    {
        $final = $this->youtubeService->getPlaylists();

        $response = [
            'error' => [
                'no' => 0,
                'text' => 'success',
            ],
            'playlists' => $final
        ];

        return response()->json(['resource' => $response]);
    }

}
