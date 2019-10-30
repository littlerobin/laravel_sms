<?php

namespace App\Http\Controllers\Website;

use App\Models\File;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Auth;


class AudioFilesController extends WebsiteController
{
    /**
     * Create a new instance of CampaignsController class.
     *
     * @return void
     */
    public function __construct()
    {

    }

    /**
     * Send request for getting template audio files.
     * GET /audio-files/audio-templates
     *
     * @return JSON
     */
    public function getAudioTemplates(Request $request)
    {

        $types = json_decode( $request->get('checkbox', "[]"), 1 );
        $section = $request->get('section');
        $page = $request->get('page', 0);
        $searchKey = $request->get('search_key');
        $orderField = $request->get('order_field');
        $order = $request->get('order');
        $savedFrom = $request->get('saved_from');
        $user = Auth::user();
        $files = $user->files()->where('is_template', 1);
        $totalTemplatesCount = $user->files()->where('is_template', 1)->count();

        if (count($types) > 0){
            $files = $files->whereIn('type', $types);
        } elseif ($section == 'templates_page'){
            $files = $files->where('type', 'NOT_EXISTING');
        }
        if ($searchKey) {
            $files = $files->where(function($query) use ($searchKey){
                $query->where('orig_filename', 'LIKE', '%' . $searchKey . '%')
                    ->orWhere('tts_text', 'LIKE', '%' . $searchKey . '%');
            });
        }
        if($savedFrom) {
            if($savedFrom == 'SMS') {
                $files = $files->where('type','SMS');
            } elseif($savedFrom == 'CALL_MESSAGE') {
                $files = $files->whereIn('saved_from',['COMPOSE','NOT_SPECIFIED','TEMPLATE'])->where('type','!=','SMS');
            } else {
                $files = $files->where('saved_from',$savedFrom);
            }
        }
        $count = $files->count();
        if($page >= 0){
            $files = $files->skip($page * 30)->take(30);
        }
        if($orderField){
            $order = in_array($order, ['ASC', 'DESC']) ? $order : 'ASC';
            $files = $files->orderBy($orderField, $order);
        }
        $files = $files->get();

        $response = [
            'error' => [
                'no' => 0,
                'text' => 'template_files'
            ],
            'files' => $files,
            'page' => $page + 1,
            'count' => $count,
            'total_templates_count' => $totalTemplatesCount,
        ];
        return response()->json(['resource' => $response]);
    }

    /**
     * Send request for making audio file as template.
     * POST /audio-files/make-audio-template
     *
     * @param Request $request
     * @return JSON
     */
    public function postMakeAudioTemplate(Request $request)
    {
        $user = Auth::user();
        $id = $request->get('id');
        $file = $user->files()->where('_id', $id)->update(['is_template' => 1]);
        $cacheService = new \App\Services\Cache\UserDataRedisCacheService();
        $cacheService->incrementMessages($user->_id, count($id));
        $response = $this->createBasicResponse(0, 'Updated');
        return response()->json(['resource' => $response]);
    }


    /**
     * Send request for making audio file as template.
     * POST /audio-files/make-audio-template
     *
     * @param Request $request
     * @return JSON
     */
    public function postMakeSmsTemplate(Request $request)
    {
        $user = Auth::user();
        $smsText = $request->get('sms_text');
        $savedFrom = $request->get('saved_from');
        $length = ceil(strlen($smsText)/160);
        $templateData = [
            'user_id' => $user->_id,
            'length' => $length,
            'type' => 'SMS',
            'is_template' => 1,
            'saved_from' => $savedFrom,
            'tts_text' => $smsText,
        ];
        $template = File::create($templateData);
        $response = $this->createBasicResponse(0, 'Created');
        return response()->json(['resource' => $response]);
    }

    /**
     * Send request for removing audio file from template.
     * POST /audio-files/remove-audio-templates
     *
     * @param Request $request
     * @return JSON
     */
    public function postRemoveAudioTemplates(Request $request)
    {
        $user = Auth::user();
        $ids = $request->get('ids');
        $file = $user->files()->where('_id', $ids)->update(['is_template' => 0]);
        $cacheService = new \App\Services\Cache\UserDataRedisCacheService();
        $cacheService->incrementMessages($user->_id, - count($ids));
        $response = $this->createBasicResponse(0, 'Updated');
        return response()->json(['resource' => $response]);
    }

    /**
     * Update file name.
     * PUT /audio-files/update-file-name
     *
     * @param integer $id
     * @param Request $request
     * @return JSON
     */
    public function putUpdateFileName($id, Request $request)
    {
        $user = Auth::user();
        $fileName = $request->get('name');
        $file = $user->files()->find($id);
        if(!$file){
            $response = $this->createBasicResponse(-1, 'fIle_does_not_exists_or_not_belongs_to_you_1');
            return response()->json(['resource' => $response]);
        }
        $file->orig_filename = $fileName;
        $file->save();
        $response = [
            'error' => [
                'no' => 0,
                'text' => 'updated_2'
            ],
            'file' => $file
        ];
        return response()->json(['resource' => $response]);
    }




    /**
     * Send request for getting template audio files.
     * GET /audio-files/audio-templates
     *
     * @return JSON
     */


    public function getAmazonUrlOfAudio($id)
    {
        $user = Auth::user();

        $file = $user->files()->find($id);
        if(!$file){
            $response = $this->createBasicResponse(-1, 'fIle_does_not_exists_or_not_belongs_to_you_1');
            return response()->json(['resource' => $response]);
        }

        $amazonS3Url = $this->getAmazonS3Url($file->map_filename);
        $response = [
            'error' => [
                'no' => 0,
                'text' => ''
            ],
            'amazon_s3_url' => $amazonS3Url
        ];
        return response()->json(['resource' => $response]);
    }

}