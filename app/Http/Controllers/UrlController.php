<?php

namespace App\Http\Controllers;


use App\Jobs\ProcessClick;
use App\Url;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Jenssegers\Agent\Agent;

class UrlController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function index(Request $request)
    {
        $rules = [
            'browser' => [
                Rule::in(['today', 'yesterday', 'last_week', 'last_month']),
                'string'
            ],
            'created_at' => [
                Rule::in(['today', 'yesterday', 'last_week', 'last_month']),
                'string'
            ],
            'device' => [
                Rule::in(['desktop', 'mobile']),
                'string'
            ]
        ];
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->failure(422, [], $validator->errors()->toArray(), $request);
        }

        $user = Auth::user();
        $user_urls_modified = [];

        foreach ($user->urls as $url) {
            $url['short_url'] = generate_short_url($url['long_url'], $request->fullUrl(), $url['short_url_identifier']);
            $url['clicks'] = $url->clicks();
            if ($request->has('browser')) {
                $url['clicks'] = $url['clicks']->where('browser', $request->input('browser'));
            }
            if ($request->has('device')) {
                $url['clicks'] = $url['clicks']->where('device', $request->input('device'));
            }
            if ($request->has('created_at')) {
                if ($request->input('created_at') == 'today') {
                    $url['clicks'] = $url['clicks']->where('created_at', '>', Carbon::today()
                        ->hour('00')->minute('00')->second('00'));
                } else if ($request->input('created_at') == 'yesterday') {
                    $url['clicks'] = $url['clicks']->where('created_at', '>', Carbon::yesterday()
                        ->hour('00')->minute('00')->second('00'));
                } else if ($request->input('created_at') == 'last_week') {
                    $url['clicks'] = $url['clicks']->where('created_at', '>', Carbon::now()->subDays(7)
                        ->hour('00')->minute('00')->second('00'));
                } else if ($request->input('created_at') == 'last_month') {
                    $url['clicks'] = $url['clicks']->where('created_at', '>', Carbon::now()->subDays(30)
                        ->hour('00')->minute('00')->second('00'));
                }
            }
            $url['click_count'] = $url['clicks']->count();
            $url['clicks'] = $url['clicks']->get();
            array_push($user_urls_modified, $url);
        }

        return response()->success(200, ['urls' => $user_urls_modified], ['OK'], $request);
    }

    /**
     * @param Request $request
     * @param $short_url_identifier
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function show(Request $request, $short_url_identifier)
    {
        $this->validate($request, [
            'short_url_identifier' => 'exists:urls'
        ]);
        $url = Url::where('short_url_identifier', $short_url_identifier)->first();
        $agent = new Agent();

        $this->dispatch(new ProcessClick($agent, $url));

        return redirect()->to($url->long_url);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request)
    {
        $rules = [
            'long_url' => 'required|string',
            'suggested_short_url_identifier' => 'string',
        ];
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->failure(422, [], $validator->errors()->toArray(), $request);
        }

        $url = new Url;

        $url->long_url = $request->input('long_url');
        if ($request->has('suggested_short_url_identifier')) {
            if (Url::where('short_url_identifier', $request->input('suggested_short_url_identifier'))->exists()) {
                do {
                    $short_url_identifier = str_shuffle($request->input('suggested_short_url_identifier'));
                } while (Url::where('short_url_identifier', $short_url_identifier)->exists());
            } else {
                $short_url_identifier = $request->input('suggested_short_url_identifier');
            }
        } else {
            do {
                $short_url_identifier = generate_short_url_identifier(10);
            } while (Url::where('short_url_identifier', $short_url_identifier)->exists());
        }

        $url->short_url_identifier = $short_url_identifier;

        $user = Auth::user();
        $user->urls()->save($url);

        $url->short_url = generate_short_url($url->long_url, $request->fullUrl(), $url->short_url_identifier);

        return response()->success(201, ['url' => $url, 'user' => $user], ['Created'], $request);
    }
}

function generate_short_url_identifier($length = 10) {
    return substr(str_shuffle(str_repeat($x='0123456789abcdefghijklmnopqrstuvwxyz',
        ceil($length/strlen($x)) )),0, $length);
}

function generate_short_url($long_url, $request_full_url, $short_url_identifier) {
    $request_fill_url_scheme = parse_url($request_full_url)['scheme'];
    $request_fill_url_host = parse_url($request_full_url)['host'];
    $request_fill_url_port = parse_url($request_full_url)['port'];
    $request_fill_url_path = parse_url($request_full_url)['path'];

    $long_url_scheme = parse_url($long_url)['scheme'];
    $long_url_host = parse_url($long_url)['host'];
    $long_url_host_exploded = explode(".", $long_url_host);
    $long_url_host_part_1 = $long_url_host_exploded[sizeof($long_url_host_exploded)-2];
    $long_url_host_part_2 = $long_url_host_exploded[sizeof($long_url_host_exploded)-1];
    $long_url_path = parse_url($long_url)['path'];

    return $request_fill_url_scheme.'://'
        .$long_url_host_part_1.'.'
        .$request_fill_url_host.':'
        . $request_fill_url_port.'/'.
        'r'.'/'.
        $short_url_identifier;
}


