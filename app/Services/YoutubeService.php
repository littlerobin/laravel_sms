<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\App;

class YoutubeService {

    private function getYoutubePlaylists ($lang)
    {
        $url = "https://www.googleapis.com/youtube/v3/playlistItems?key=AIzaSyCpi90YALj32dHBy13zsBvP16YhxBtwMr4&part=snippet&playlistId=";
        $urlEnd = "&rel=0&maxResults=50";
        $jsonData = json_decode(file_get_contents($url . config("youtube.$lang") . $urlEnd));
        $currentVideoData = array();
        foreach ($jsonData->items as $item) {
            $itemList = new \StdClass;
            $itemList->title = $item->snippet->title;
            $itemList->description = $item->snippet->description;
            $itemList->videoId = $item->snippet->resourceId->videoId;
            if($item->snippet->title != 'Deleted video' && $item->snippet->description != 'This video is unavailable.') {
                $currentVideoData[] = $itemList;
            }
        }

        return $currentVideoData;
    }

    public function getPlaylists ($language = null)
    {
        if ($language) {
            $lang = $language;
        } else {
            // $lang = App::getLocale();
            $lang = session('currentLang');
        }

        if (\Cache::store('redis')->get('youtubeVideos' . $lang)) {
            $final = \Cache::store('redis')->get('youtubeVideos' . $lang);
        } else {

            $youtubeResp = $this->getYoutubePlaylists($lang);

            $final = [
                'vm' => [
                    'promotionals' => [],
                    'tutorials' => []
                ],
                'ctc' => [
                    'promotionals' => [],
                    'tutorials' => []
                ]
            ];
            $upperLang = strtoupper($lang);
            foreach ($youtubeResp as $video) {
                if (preg_match("/^.*\($upperLang-V\)$/",  $video->title)) {
                    if (preg_match("/^PROMO.*/", $video->title)) {
                        array_push($final['vm']['promotionals'], $video);
                    }
                    elseif (preg_match("/^TUTORIAL.*/", $video->title)) {
                        array_push($final['vm']['tutorials'], $video);
                    }
                }
                elseif (preg_match("/^.*\($upperLang-C\)$/",  $video->title)) {
                    if (preg_match("/^PROMO.*/", $video->title)) {
                        array_push($final['ctc']['promotionals'], $video);
                    }
                    elseif (preg_match("/^TUTORIAL.*/", $video->title)) {
                        array_push($final['ctc']['tutorials'], $video);
                    }
                }
            }
            \Cache::store('redis')->put('youtubeVideos' . $lang, $final, 14400);
        }

        return $final;
    }

}
