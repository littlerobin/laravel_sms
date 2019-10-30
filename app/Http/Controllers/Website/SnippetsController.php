<?php

namespace App\Http\Controllers\Website;

use App\Http\Requests\CheckSnippetUrl;
use App\Http\Requests\SnippetRequest;
use App\Models\Phonenumber;
use App\Models\Snippet;
use App\Services\FileService;
use App\Services\SnippetService;
use App\Services\SendEmailService;
use Carbon\Carbon;
use Chumper\Zipper\Zipper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use \App\Services\AddressBookService;

class SnippetsController extends WebsiteController
{
    public function __construct()
    {
        $this->middleware('jwt.headers');
        $this->middleware('jwt.auth', ['except' => [
            'getExportStatistics',
            'getWordPressPlugin',

        ]]);
        $this->middleware('active.user', ['except' => [
            'getExportStatistics',
            'getWordPressPlugin',

        ]]);
    }

    protected $user;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, SnippetService $snippetService)
    {
        $page = $request->get('page', 1);
        $user = \Auth::user();
        $snippets = Snippet::with(['country', 'callerId', 'user', 'totalClicks',
            'callsCost', 'pendingCount', 'inTimeCount'])
            ->where('user_id', $user->_id);
        $tempCount = $snippets->count();
        $totalCount = $snippets->count();
        //Available statuses are: ALL ACTIVE DELETED DRAFT IN_PAUSE DEACTIVATED
        $statuses = json_decode($request->get('statuses', "[]"), 1);
        if (in_array('ALL', $statuses) || count($statuses) === 0) {
            $snippets = $snippets->withTrashed();
        } elseif (count($statuses) > 0) {
            $snippets->where(function ($query) use ($statuses) {
                if (in_array('ACTIVE', $statuses)) {
                    $query->orWhere(function ($newQuery) {
                        $newQuery->where('is_active', 1)
                            ->where('is_published', 1)
                            ->where('is_blocked', 0);
                    });
                }
                if (in_array('DRAFT', $statuses)) {
                    $query->orWhere('is_published', 0);
                }
                if (in_array('IN_PAUSE', $statuses)) {
                    $query->orWhere('is_active', 0);
                }
                if (in_array('DEACTIVATED', $statuses)) {
                    $query->orWhere('is_blocked', 1);
                }
            });
        }
        if (in_array('DELETED', $statuses)) {
            $snippets = $snippets->withTrashed();
        }
        $snippets = $snippets->where('name', 'like', '%' . $request->name . '%');

        $dateRange = $request->date_range;

        if ($dateRange) {
            $dateRangeTime = Carbon::now()->subDays($dateRange);

            $snippets = $snippets->where('created_at', '>', $dateRangeTime);
        }

        $count = $snippets->count();
        $snippets = $snippets->skip(($page - 1) * 30)->take(30);

        if ($request->orderBy && $request->type) {
            $snippets = $snippets->orderBy($request->orderBy, $request->type)->get();
        } else {
            $snippets = $snippets->orderBy('created_at', 'DESC')->get();
        }

        $snippets->each(function ($snippet) use ($snippetService) {
            $snippet->totalClicks = isset($snippet->totalClicks[0]) ? $snippet->totalClicks[0]->count : 0;
            $snippet->inTimeCount = isset($snippet->inTimeCount[0]) ? $snippet->inTimeCount[0]->count : 0;
            $snippet->outOfTimeCount = $snippet->totalClicks - $snippet->inTimeCount;
            $snippet->pendingCount = isset($snippet->pendingCount[0]) ? $snippet->pendingCount[0]->count : 0;
            $snippet->doneCount = $snippet->totalClicks - $snippet->pendingCount;
            $snippet->callsCost = isset($snippet->callsCost[0]) ? $snippet->callsCost[0]->sum : 0;
            if ($snippet->image_name) {
                $snippet->image_url = $this->getAmazonS3Url($snippet->image_name);
            }
            if ($snippet->qr_code_img_name) {
                $snippet->qr_code_img_url = $this->getAmazonS3Url($snippet->qr_code_img_name);
            }
            $customerTimezone = $snippet->user->timezone ? $snippet->user->timezone : "UTC";
        });

        foreach ($snippets as $snippet) {
            $snippet->updated_at = Carbon::createFromFormat('Y-m-d H:i:s', $snippet->updated_at, config('app.timezone'))
                ->setTimezone($user->timezone)
                ->toDateTimeString();
        }

        $response = [
            'error' => [
                'no' => 0,
                'text' => '',
            ],
            'snippets' => $snippets,
            'count' => $count,
            'total_count' => $totalCount,
            'hasAnySnippet' => $tempCount > 0 ? true : false,
            'statuses' => $request->get('statuses'),
        ];

        return response()->json(['resource' => $response]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function store(SnippetRequest $request)
    {
        DB::beginTransaction();
        try {

            $snippet = new Snippet;

            $snippet = $snippet = $this->snippetCRUD($snippet, $request);

            DB::commit();

            $response = [
                'error' => [
                    'no' => 0,
                    'text' => '',
                ],
                'success' => 1,
                'snippets' => $snippet,
            ];

            return response()->json(['resource' => $response]);

        } catch (\Exception $e) {

            DB::rollback();
            \Log::info($e);
            $response = [
                'error' => [
                    'no' => -2,
                    'text' => 'something__went__wrong',
                ],
                'message' => $e->getMessage(),

            ];

            return response()->json(['resource' => $response]);
        }
    }

    /**
     * Export CTC statistics into a csv file
     *
     * @param Request $request
     * @return Response
     */
    public function getExportStatistics(Request $request)
    {
        $token = $request->get('token');
        $fileFormat = $request->get('file_format', 'csv');
        $fileFormat = in_array($fileFormat, config('export_files.allowed_for_export'))
                        ? $fileFormat
                        : 'csv';
        $user = \App\Services\DownloadsService::user($token);

        if (!$user) {
            return 'User missing';
        }

        $snippet = \App\Models\Snippet::isPublished(1)->isActive(1)->find($request->snippet_id);
        if (!$snippet || $snippet->user_id != $user->_id) {
            return 'Snippet missing';
        }

        $phonenumbers = \App\Models\Phonenumber::where('snippet_id', $snippet->_id)->validCTC();
        if ($request->date_range) {
            $dateRangeTime = Carbon::now()->subDays($request->date_range);
            $phonenumbers = $phonenumbers->where('created_at', '>', $dateRangeTime);
        }
        if ($request->phonenumber) {
            $phonenumbers = $phonenumbers->where('phone_no', 'like', '%' . $request->phonenumber . '%');
        }
        if ($request->statuses == 'ANSWERED') {
            $phonenumbers = $phonenumbers->where('status', 'SUCCEED');
        } elseif ($request->statuses
            && $request->statuses != 'ANSWERED'
            && $request->statuses != 'ALL'
        ) {
            $phonenumbers = $phonenumbers->where('status', '<>', 'SUCCEED');
        }

        $locale = $request->get('locale', 'en');
        \App::setLocale($locale);

        $csv = \League\Csv\Writer::createFromFileObject(new \SplTempFileObject());

        $csv->insertOne([trans('csv.phonenumber'), trans('csv.clicked_on'), trans('csv.status'), trans('csv.total_length'), trans('csv.total_cost')]);
        $phonenumbers->with('calls')->chunk(1000, function ($ctcPhonenumbers) use ($csv, $user) {
            foreach ($ctcPhonenumbers as $phonenumber) {
                $totalLength = $this->getCTCTotalLength($phonenumber);
                $totalCost = $this->getCTCTotalCost($phonenumber);
                $finalNumber = $phonenumber->status == 'SUCCEED' ? $phonenumber->phone_no : AddressBookService::addThreeAsterisks($phonenumber->phone_no);
                $csv->insertOne([
                    $finalNumber,
                    $this->applyTimezone($phonenumber->created_at, $user->timezone),
                    $phonenumber->status,
                    $totalLength,
                    $totalCost,
                ]);
            }
        });
        $csv->output('statistics_' . $snippet->name . '_statistics.'. $fileFormat);
        exit;
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function getShowStatistics(Request $request, SnippetService $snippetService, $id)
    {
        $snippet = \App\Models\Snippet::isPublished(1)->currentUser()->with(
            [
                'country',
                'callerId',
                'ctcPhonenumbers' => function ($query) use ($request) {
                    $this->ctcFilter($query, $request);
                    $query->skip(($request->page - 1) * 30)->take(30)->orderBy('created_at', 'DESC');
                },
            ])->where('_id', $id);
        $snippet = $snippet->first();

        foreach ($snippet->ctcPhonenumbers as $phonenumber) {
            $phonenumber->created_at = $this->applyTimezone($phonenumber->created_at, $snippet->user->timezone);
            $phonenumber->first_scheduled_date = $this->applyTimezone($phonenumber->first_scheduled_date, $snippet->user->timezone);
            $ifAnySuccess = false;
            if ($phonenumber->status == 'SUCCEED') {
                $ifAnySuccess = true;
            }
            foreach ($phonenumber->manualRetries as $manualRetry) {
                $manualRetry->created_at = $this->applyTimezone($manualRetry->created_at, $snippet->user->timezone);
                $manualRetry->first_scheduled_date = $this->applyTimezone($manualRetry->first_scheduled_date, $snippet->user->timezone);
                if ($manualRetry->status == 'SUCCEED') {
                    $ifAnySuccess = true;
                }
                $manualRetry->phone_no = null;
            }
            if (!$ifAnySuccess) {
                $phonenumber->phone_no = AddressBookService::addThreeAsterisks($phonenumber->phone_no);
            }
        }

        $count = $snippet->ctcPhonenumbers()->where(function ($query) use ($request) {
            $this->ctcFilter($query, $request);
        })->count();
        if ($snippet) {
            $response = [
                'error' => [
                    'no' => 0,
                    'text' => '',
                ],
                'snippet' => $snippet,
                'count' => $count,
            ];
        } else {
            $response = [
                'error' => [
                    'no' => -2,
                    'text' => 'snippet_not_found',
                ],
                'snippets' => '',
            ];
        }

        return response()->json(['resource' => $response]);
    }

    public function show (Request $request, SnippetService $snippetService, $id)
    {
        $snippet = Snippet::with('files')->currentUser()->with(['country', 'callerId'])->find($id);

        if ($snippet->file_id) {
            $snippet->files->amazon_s3_url = $this->getAmazonS3Url($snippet->files->map_filename);
        }

        if ($snippet->image_name) {
            $snippet->image_url = $this->getAmazonS3Url($snippet->image_name);
        }

        if ($snippet->qr_code_img_name) {
            $snippet->qr_code_img_url = $this->getAmazonS3Url($snippet->qr_code_img_name);
        }

        $count = null;

        $snippet->allowed_url = str_replace("https://callburn.com,", "", $snippet->allowed_url);

        if ($snippet) {
            $response = [
                'error' => [
                    'no' => 0,
                    'text' => '',
                ],
                'snippets' => $snippet,
                'count' => $count,
            ];
        } else {
            $response = [
                'error' => [
                    'no' => -2,
                    'text' => 'snippet_not_found',
                ],
                'snippets' => '',
            ];
        }

        return response()->json(['resource' => $response]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(SnippetRequest $request, $id)
    {
        DB::beginTransaction();
        try {
            $snippet = Snippet::currentUser()->find($id);

            $snippet->country()->detach();
            $snippet->callerId()->detach();
            $snippet = $this->snippetCRUD($snippet, $request, false);

            DB::commit();

            $response = [
                'error' => [
                    'no' => -0,
                    'text' => '',
                ],
                'success' => 1,
                'snippets' => $snippet,
            ];

            return response()->json(['resource' => $response]);

        } catch (\Exception $e) {

            DB::rollback();

            $response = [
                'error' => [
                    'no' => -2,
                    'text' => 'something__went__wrong',
                ],

            ];

            return response()->json(['resource' => $response]);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            Snippet::currentUser()->find($id)->delete();

            $response = [
                'error' => [
                    'no' => 0,
                    'text' => '',
                ],

            ];

            return response()->json(['resource' => $response]);

        } catch (\Exception $e) {
            $response = [
                'error' => [
                    'no' => -2,
                    'text' => 'something__went__wrong',
                ],

            ];

            return response()->json(['resource' => $response]);
        }
    }

    /**
     * Retry the call of the snippet phone number
     *
     * @param Request $request
     * @return JSON
     */
    public function postRetryPhoneNumber(Request $request)
    {
        $user = \Auth::user();
        $ipAddress = $request->ip();
        $phonenumberId = $request->get('phonenumber_id');
        $phonenumber = Phonenumber::find($phonenumberId);
        if (!$phonenumber || $phonenumber->status == 'CANT_CALL_DUE_TO_EU' || $phonenumber->user_id != $user->_id) {
            $response = [
                'error' => [
                    'no' => -1,
                    'text' => 'phonenumber_does_not_exist_or_not_belongs_to_you',
                ],

            ];
            return response()->json(['resource' => $response]);
        }
        try {
            DB::beginTransaction();
            $phonenumber->is_current = false;
            $phonenumber->save();
            $phonenumber->manualRetries()->update(['is_current' => false]);
            $newPhoneNumberData = [
                'ip_address' => $ipAddress,
                'phone_no' => $phonenumber->phone_no,
                'tariff_id' => $phonenumber->tariff_id,
                'user_id' => $phonenumber->user_id,
                'action_type' => 'VOICE_CALL',
                'is_from_not_eu_to_eu' => false,
                'snippet_id' => $phonenumber->snippet_id,
                'retry_of' => $phonenumber->_id,
                'is_current' => true,
            ];
            $newPhoneNumber = Phonenumber::create($newPhoneNumberData);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            \Log::info($e);
            $response = [
                'error' => [
                    'no' => -2,
                    'text' => 'something__went__wrong',
                ],
                'message' => $e->getMessage(),

            ];
        }

        $response = [
            'error' => [
                'no' => 0,
                'text' => 'created',
            ],
            'phonenumber_id' => $newPhoneNumber->_id,
        ];

        return response()->json(['resource' => $response]);
    }

    /**
     * Retry the call of the snippet phone number
     *
     * @param Request $request
     * @return JSON
     */
    public function postRemoveAllFromPending(Request $request)
    {
        $user = \Auth::user();
        $snippetId = $request->get('snippet_id');
        $snippet = Snippet::isPublished(1)->isActive(1)->find($snippetId);
        if (!$snippet || $snippet->user_id != $user->_id) {
            $response = [
                'error' => [
                    'no' => -1,
                    'text' => 'snippet_does_not_exist_or_not_belongs_to_you',
                ],

            ];
            return response()->json(['resource' => $response]);
        }

        $snippet->ctcPhonenumbers()->where('is_pending', 1)
            ->update(['is_pending' => 0]);
        $snippet->ctcPhonenumbers()->where('status', 'TRANSFER_NOT_CONNECTED')
            ->update(['status' => 'TRANSFER_NOT_CONNECTED_FAILED']);

        $response = [
            'error' => [
                'no' => 0,
                'text' => 'created',
            ],
        ];
        return response()->json(['resource' => $response]);
    }

    /**
     * Add or remove phonenumber to pending
     *
     * @param Request $request
     * @return JSON
     */
    public function postAddRemovePending(Request $request)
    {
        $user = \Auth::user();
        $phonenumberId = $request->get('phonenumber_id');
        $action = $request->get('action');
        $phonenumber = Phonenumber::find($phonenumberId);
        if (!$phonenumber || $phonenumber->status == 'CANT_CALL_DUE_TO_EU' || $phonenumber->user_id != $user->_id) {
            $response = [
                'error' => [
                    'no' => -1,
                    'text' => 'phonenumber_does_not_exist_or_not_belongs_to_you',
                ],

            ];
            return response()->json(['resource' => $response]);
        }

        if ($action == 'ADD') {
            $phonenumber->is_pending = 1;
        } elseif ($phonenumber->status == 'TRANSFER_NOT_CONNECTED') {
            $phonenumber->status = 'TRANSFER_NOT_CONNECTED_FAILED';
            $phonenumber->is_pending = 0;
        } else {
            $phonenumber->is_pending = 0;
        }
        $phonenumber->save();

        $response = [
            'error' => [
                'no' => 0,
                'text' => 'updated',
            ],
        ];
        return response()->json(['resource' => $response]);
    }

    private function generateQRCode($subdomain)
    {
        $path = public_path('uploads/img/qr/');
        $extension = '.png';
        $name = str_random(8) . $extension;

        $lang = \App::getLocale();
        $multiLangPrefixes = [
            'en' => 'callme.callburn.com/',
            'es' => 'llamame.callburn.com/',
            'it' => 'chiamami.callburn.com/',
        ];

        $prefix = $multiLangPrefixes[$lang];

        $fullUrl = $prefix . $subdomain;

        $renderer = new \BaconQrCode\Renderer\Image\Png();
        $renderer->setHeight(256);
        $renderer->setWidth(256);
        $writer = new \BaconQrCode\Writer($renderer);
        $writer->writeFile($fullUrl, $path . $name);

        \App\Services\FileService::moveImageToAmazon($name, $path);

        \File::delete($path . $name);

        return $name;
    }

    private function snippetCRUD($snippet, $request, $create = true)
    {
        $user = \Auth::user();
        $snippetService = new SnippetService;
        $snippet->name = $request->snippetName;
        $snippet->default_text = $request->default_text;
        $snippet->file_id = $request->voice_file_id;
        $snippet->user_id = $user->_id;
        $snippet->wait_time = $request->wait_time;
        $snippet->allowed_url = "https://callburn.com," . $request->allowed_url;
        $snippet->subdomain = $request->subdomain ? $request->subdomain : str_random(8);
        $snippet->qr_code_img_name = $this->generateQRCode($snippet->subdomain);
        $snippet->caller_id_id = $request->caller_id_id;

        if ($request->image_name) {
            $snippet->image_name = $request->image_name;
        } else {
            $snippet->image_name = null;
        }

        if ($request->saveType == 'saveAndCreate') {
            $snippet->is_published = 1;
        } elseif ($request->saveType == 'saveAsDraft') {
            $snippet->is_published = 0;
        }

        if ($create) {
            $snippet->api_token = str_random(40);
        } else {
            $snippet->is_blocked = 0;
        }

        if ($request->has_custom_date_times) {
            $snippet->custom_date_times = json_encode($request->custom_date_times);
            $snippet->allowed_date_times = null;
        }

        $snippet->save();

        if ($snippet->qr_code_img_name) {
            $snippet->qr_code_img_url = $this->getAmazonS3Url($snippet->qr_code_img_name);
        }

        if ($create) {
            $snippetId = $snippet->_id;

            $addressBookGroup = new \App\Models\AddressBookGroup;
            $addressBookGroup->name = $snippet->name;
            $addressBookGroup->user_id = $snippet->user->_id;
            $addressBookGroup->snippet_id = $snippetId;
            $addressBookGroup->save();
        }
        $snippet->callerId()->sync($request->callerIds);
        $snippet->country()->sync($request->countries);
        $snippet->country;
        $snippet->callerId;

        return $snippet;
    }

    public function getApiJavascript($token)
    {
        $snippet = Snippet::isPublished(1)->isActive(1)->where('api_token', $token)->first();
        $javascripts = [];
        if ($snippet) {
            for ($i = 1; $i < 4; $i++) {
                $javascript = view('clickToCall.api', [
                    'token' => $token,
                    'type' => $i,
                    'url' => url(''),
                ])->render();
                $javascripts[] = $javascript;
            }
            $invisibleJavascript = view('clickToCall.api-invisible', [
                'token' => $token,
                'type' => 'invisible',
                'url' => url(''),
            ])->render();
            array_push($javascripts, $invisibleJavascript);
            $response = [
                'error' => [
                    'no' => 0,
                    'text' => '',
                ],
                'javascript' => $javascripts,
            ];
        } else {
            $response = [
                'error' => [
                    'no' => -2,
                    'text' => 'something__went__wrong',
                ],

            ];
        }

        return response()->json(['resource' => $response]);
    }

    public function getCallRoutes(Request $request)
    {
        $ids = json_decode($request->ids);
        $routes = \App\Models\Country::select(['_id', 'code', 'name', 'phonenumber_prefix'])
            ->where('customer_price', '!=', 0)
            ->whereNotIn('_id', $ids)
            ->orderByRaw('cast(phonenumber_prefix as unsigned) ASC')
            ->get();
        $response = [
            'error' => [
                'no' => 0,
                'text' => 'list__of_routes',
            ],
            'routes' => $routes,
        ];

        return response()->json(['resource' => $response]);
    }

    public function getCallerIds()
    {
        if (\Auth::user()->numbers) {
            $response = [
                'error' => [
                    'no' => 0,
                    'text' => 'list__of_routes',
                ],
                'callerIds' => \Auth::user()->numbers,
            ];
        } else {
            $response = [
                'error' => [
                    'no' => -2,
                    'text' => 'something__went__wrong',
                ],
            ];
        }

        return response()->json(['resource' => $response]);
    }

    /**
     * Get total length of phone number
     * Total length is the sum of lengths of call
     *
     * @param Phonenumber $phonenumber
     * @return integer
     */
    public function getCTCTotalLength($phonenumber)
    {
        $totalLength = 0;
        foreach ($phonenumber->calls as $call) {
            $totalLength += $call->duration;
        }

        return $totalLength;
    }

    /**
     * Get total cost of phone number
     * Total cost is the sum of costs of call
     *
     * @param Phonenumber $phonenumber
     * @return integer
     */
    public function getCTCTotalCost($phonenumber)
    {
        $totalLength = 0;
        foreach ($phonenumber->calls as $call) {
            $totalLength += $call->cost;
        }

        return $totalLength;
    }

    public function postCheckUrl(CheckSnippetUrl $request)
    {
        $response = [
            'error' => [
                'no' => 0,
                'text' => '',
            ],
        ];

        return response()->json(['resource' => $response]);
    }

    public function postUploadSnippetFile(Request $request, FileService $fileRepo)
    {
        $validator = \Validator::make($request->all(), [
            'file' => 'mimes:audio/mp3,audio/wma|max:6144',
        ]);
        if (!$validator->fails()) {
            $response = [
                'error' => [
                    'no' => -11,
                    'text' => 'max_upload_size_can_be_8_mb',
                ],
            ];

            return response()->json(['resource' => $response]);
        }

        $user = \Auth::user();
        $voiceFile = $request->file('file');
        $originalName = $voiceFile->getClientOriginalName();
        $extension = $voiceFile->getClientOriginalExtension();
        $isTemplate = $request->get('is_template');
        $validExtensions = ['mp3', 'wav'];
        if (!in_array($extension, $validExtensions)) {
            $response = [
                'error' => [
                    'no' => -12,
                    'text' => 'file_format_not_supported',
                ],
            ];

            return response()->json(['resource' => $response]);
        }

        $uploadFolder = public_path() . '/uploads/audio/';
        $tempName = str_random();
        $newName = str_random();
        $originalName = $voiceFile->getClientOriginalName();
        $fileExtension = $voiceFile->getClientOriginalExtension();
        $voiceFile->move($uploadFolder, $tempName . '.' . $fileExtension);

        $cmd = "lame -b 128 " . $uploadFolder . $tempName . "." . $extension . " " . $uploadFolder . $newName . '.mp3';
        $response = shell_exec($cmd);
        $extension = 'mp3';

        $file = $fileRepo->createFile([
            'orig_filename' => preg_replace('/\.[^.\s]{3,4}$/', '', $originalName) . '.' . $extension,
            'map_filename' => $newName . '.' . $extension,
            'extension' => $extension,
            'stripped_name' => $newName,
            'user_id' => $user->_id,
        ]);

        \File::delete($uploadFolder . $tempName . "." . $fileExtension);
        $file->length = 0;
        $file->type = 'UPLOADED';
        $file->is_template = $isTemplate;
        $file->save();

        $fileRepo->moveAudioFileToAmazon($file->map_filename);

        $file['amazon_s3_url'] = $this->getAmazonS3Url($file->map_filename);
        $response = [
            'error' => [
                'no' => 0,
                'message' => 'file_created',
            ],
            'file' => $file,
        ];

        return response()->json(['resource' => $response]);
    }

    /**
     *IMAGE UPLOAD
     *POST /campaigns/upload-image-file
     *
     *@param Request $request
     *@return JSON
     */
    public function postUploadImageFile(Request $request, FileService $fileRepo)
    {
        $user = \Auth::user();
        $image = $request->file('file');
        $extension = $image->getClientOriginalExtension();
        $validExtensions = ['png', 'jpg', 'jpeg'];
        if (!in_array($extension, $validExtensions)) {
            $response = [
                'error' => [
                    'no' => -12,
                    'text' => 'file_format_not_supported',
                ],
            ];
            return response()->json(['resource' => $response]);
        }

        $path = public_path('/uploads/img/');
        $name = str_random(30) . '.' . $extension;
        $image->move($path, $name);

        $fileRepo->moveImageToAmazon($name, $path);

        \File::delete($path . $name);

        $imageUrl = $this->getAmazonS3Url($name);

        $response = [
            'error' => [
                'no' => 0,
                'message' => 'file_created',
            ],
            'url' => $imageUrl,
            'name' => $name,
        ];
        return response()->json(['resource' => $response]);
    }

    private function ctcFilter(&$query, $request)
    {
        $page = $request->get('page', 1);
        $query->whereNull('retry_of')->validCTC();
        $query->where('phone_no', 'like', '%' . $request->phonenumber . '%');
        $availableStatuses = json_decode($request->get('statuses', "[]"), 1);
        if (!in_array('ALL', $availableStatuses) && count($availableStatuses) > 0) {
            $query->where(function ($newQuery) use ($availableStatuses) {
                $newQuery->where(function ($withoutManualRetries) use ($availableStatuses) {
                    $withoutManualRetries->whereHas('manualRetries', function ($withoutManualRetriesTempQuery) {}, '=', 0)
                        ->where(function ($tempQuery) use ($availableStatuses) {
                            $this->applyStatuses($tempQuery, $availableStatuses);
                        });
                })
                    ->orWhere(function ($withManualRetries) use ($availableStatuses) {
                        $withManualRetries->whereHas('manualRetries', function ($withManualRetriesTempQuery) use ($availableStatuses) {
                            $withManualRetriesTempQuery->where('is_current', 1);
                            $this->applyStatuses($withManualRetriesTempQuery, $availableStatuses);
                        }, '>', 0);
                    });
            });
        }

        $dateRange = $request->date_range;
        if ($dateRange) {
            $dateRangeTime = Carbon::now()->subDays($dateRange);

            $query->where('created_at', '>', $dateRangeTime);
        }
    }

    /**
     * Apply statuses for query
     *
     * @param QueryBuilder $query
     * @return void
     */
    private function applyStatuses(&$query, $availableStatuses)
    {
        $query->where(function ($newQuery) use ($availableStatuses) {
            if (in_array('ANSWERED', $availableStatuses)) {
                $newQuery->orWhere('status', 'SUCCEED');
            }
            if (in_array('FAILED', $availableStatuses)) {
                $newQuery->orWhereNotIn('status', ['SUCCEED', 'IN_PROGRESS']);
            }
            if (in_array('SCHEDULED', $availableStatuses)) {
                $newQuery->orWhere(function ($scheduleQuery) {
                    $scheduleQuery->where('status', 'IN_PROGRESS')
                        ->whereNotNull('first_scheduled_date');
                });
            }
            if (in_array('PENDING', $availableStatuses)) {
                $newQuery->orWhere('is_pending', 1)->orWhere('status', 'TRANSFER_NOT_CONNECTED');
            }
            if (in_array('IN_PROGRESS', $availableStatuses)) {
                $newQuery->orWhere('status', 'IN_PROGRESS');
            }
            if (in_array('OUT_OF_DATE', $availableStatuses)) {
                $newQuery->orWhere('status', 'OUT_OF_DATE');
            }
        });

    }

    public function getMergedDate(Request $request, SnippetService $snippetService)
    {
        $date = $request->custom_date_times;

        $offset = -$request->offset;
        $customerTimezone = \Auth::user()->timezone;
        if (!$date) {
            $response = [
                'error' => [
                    'no' => -2,
                    'message' => '',
                ],
            ];

            return response()->json(['resource' => $response]);
        }

        $date = json_encode($date);
        $final = \App\Services\SnippetService::createCustomSnippetDateRange($date, $offset, $customerTimezone, true);

        $response = [
            'error' => [
                'no' => 0,
                'message' => '',
            ],
            'date' => $final,

        ];

        return response()->json(['resource' => $response]);
    }

    public function postEnableOrDisable(Request $request)
    {
        $id = $request->get('_id');
        $type = $request->get('type');
        $snippet = Snippet::find($id);

        DB::beginTransaction();

        try {
            if ($type == 'enable') {
                if ($snippet->callerId()->count() == 0) {
                    $response = [
                        'error' => [
                            'no' => -1,
                            'text' => 'snippet_does_not_have_active_caller_id',
                        ],

                    ];

                    return response()->json(['resource' => $response]);
                }
                $snippet->is_active = 1;

            } elseif ($type == 'disable') {
                $snippet->is_active = 0;
            }

            $snippet->save();

            DB::commit();

            $response = [
                'error' => [
                    'no' => -0,
                    'text' => '',
                ],

                'snippet' => $snippet,
            ];

            return response()->json(['resource' => $response]);
        } catch (\Exception $e) {
            DB::rollback();
            \Log::info($e);
            $response = [
                'error' => [
                    'no' => -2,
                    'text' => 'something__went__wrong',
                ],
                'message' => $e->getMessage(),

            ];

            return response()->json(['resource' => $response]);
        }
    }

    public function getWordPressPlugin(Request $request)
    {
        try {
            $token = $request->get('token');
            $id = $request->get('id');
            $type = $request->get('type');
            $user = \App\Services\DownloadsService::user($token);
            if (!$user) {
                return 'User missing';
            }

            $snippet = Snippet::isActive(1)->where('_id', $id)->first();

            $javascript = view('clickToCall.api', [
                'token' => $snippet->api_token,
                'type' => $type,
                'url' => url(''),
            ])->render();

            $wordPressJs = view('clickToCall.wordpressJs', [
                'apiJs' => trim($javascript),
                'type' => $type,
                'id' => $snippet->_id,

            ])->render();

            $wordPressPhp = \File::get(public_path('wordpress/callburn.php'));
            $wordPressPhp = "<?php \n \n \n define('apiJavascript', '" . $javascript . "');" . $wordPressPhp;

            \File::makeDirectory(public_path('wordpress/plugin/callburn/js'));
            \File::put(public_path('wordpress/plugin/callburn/js/script.js'), $wordPressJs);

            \File::put(public_path('wordpress/plugin/callburn/calburn.php'), $wordPressPhp);

            $zipper = new \Chumper\Zipper\Zipper;
            $files = public_path('/wordpress/plugin');
            $zipper->make('callburn.zip')->add($files)->close();
            $zipFile = public_path('callburn.zip');
            \File::deleteDirectory(public_path('wordpress/plugin/callburn/js'));
            \File::delete(public_path('wordpress/plugin/callburn/calburn.php'));

            return response()->download($zipFile)->deleteFileAfterSend(true);
        } catch (\Exception $e) {
            \Log::info($e->getMessage());

            return redirect()->back();
        }
    }

    public function postCallNow(Request $request)
    {
        $id = $request->get('phonenumber_id');
        $phonenumber = \App\Models\Phonenumber::find($id);

        $response = [
            'error' => [
                'no' => -2,
                'text' => 'something__went__wrong',
            ],

        ];

        if (!$phonenumber or $phonenumber->status != 'IN_PROGRESS') {
            return response()->json(['resource' => $response]);
        }

        $phonenumber->first_scheduled_date = null;

        if (!$phonenumber->save()) {
            return response()->json(['resource' => $response]);
        }

        $response = [
            'error' => [
                'no' => 0,
                'text' => '',
            ],

            'phonenumber' => $phonenumber,
        ];

        return response()->json(['resource' => $response]);
    }

    public function postStatisticsSchedulation(Request $request)
    {
        $id = $request->get('id');
        $cancelSchedulate = $request->get('cancelSchedulation');
        $cancelCancellation = $request->get('cancelCancellation');

        $phonenumber = \App\Models\Phonenumber::find($id);

        $response = $this->createBasicResponse(-2, 'something__went__wrong');

        if (!$phonenumber) {
            return response()->json(['resource' => $response]);
        } elseif ($phonenumber->status == 'CANT_CALL_DUE_TO_EU') {
            $response = $this->createBasicResponse(-30, 'action_not_allowed');
            return response()->json(['resource' => $response]);
        }

        if ($cancelSchedulate) {
            $phonenumber->first_scheduled_date = null;
            $phonenumber->status = 'CANCELLED';
        } elseif ($cancelCancellation) {
            $phonenumber->status = 'IN_PROGRESS';
        }

        if (!$phonenumber->save()) {
            return response()->json(['resource' => $response]);
        }

        $response = [
            'error' => [
                'no' => 0,
                'text' => '',
            ],

            'phonenumber' => $phonenumber,
        ];

        return response()->json(['resource' => $response]);
    }

    public function postSaveHolidayMode (Request $request)
    {
        $snippet = Snippet::isActive(1)->where('_id', $request->get('id'))->first();

        if (!$snippet) {
            $response = $this->createBasicResponse(-30, 'snippet_not_found');
        }

        $isActiveHolidayMode = $request->get('isActiveHolidayMode');

        if (!$isActiveHolidayMode) {
            $snippet->holiday_mode = null;
            $snippet->is_active_holiday_mode = 0;

            if ($snippet->save()) {
                $response = [
                    'error' => [
                        'no' => 0,
                        'text' => '',
                    ],
                    'snippet' => $snippet,
                ];

                return response()->json(['resource' => $response]);
            }
        }

        try {
            $response = $this->createBasicResponse(-20, 'date_range_no_valid');
            $HolidayModeFrom = Carbon::createFromFormat('d.m.Y', $request->get('from'));
            $HolidayModeTo = Carbon::createFromFormat('d.m.Y', $request->get('to'));

            if ($HolidayModeTo->gt($HolidayModeFrom)) {
                $snippet->holiday_mode = $HolidayModeFrom->format('d/m/Y') . ' - ' . $HolidayModeTo->format('d/m/Y');
                $snippet->is_active_holiday_mode = 1;

                $snippet->save();

                $response = [
                    'error' => [
                        'no' => 0,
                        'text' => '',
                    ],
                    'snippet' => $snippet,
                ];

                return response()->json(['resource' => $response]);
            } else {
                return response()->json(['resource' => $response]);
            }
        } catch (\Exception $e) {
            \Log::info($e->getMessage());

            return response()->json(['resource' => $response]);
        }
    }

    public function postSendEmailIntegrationCodes (Request $request)
    {
        try {
            $user = \Auth::user();
            $recipientEmail = $request->get('email');
            $snippetId = $request->get('snippet_id');
            $token = $request->get('token');
            $snippet = \App\Models\Snippet::where('api_token', $token)->first();
            $integrationCodesArr = $this->getApiJavascript($token)->getData()->resource->javascript;
            $integrationCodes = (object) null;
            $integrationCodes->open_version = $integrationCodesArr[0];
            $integrationCodes->semi_open_version = $integrationCodesArr[1];
            $integrationCodes->closed_version = $integrationCodesArr[2];
            $integrationCodes->invisible_version = $integrationCodesArr[3];
            $sendEmailRepo = new SendEmailService();
            $sendEmailRepo->sendCTCIntegrationCodesEmail($user, $recipientEmail, $integrationCodes, $snippet);

            $response = [
                'error' => [
                    'no' => 0,
                    'text' => 'successfully_sent_email',
                ]
            ];

            return response()->json(['resource' => $response]);
        } catch (Exception $e) {
            \Log::info($e->getMessage());
            $response = [
                'error' => [
                    'no' => -1,
                    'text' => 'some_problems_to_sent_email',
                ]
            ];

            return response()->json(['resource' => $response]);
        }
    }
}
