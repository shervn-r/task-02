<?php

namespace App\Http\Controllers;


use App\Jobs\ProcessClick;
use App\Url;
use Illuminate\Http\Request;
use Illuminate\Queue\Queue;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
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
        $this->validate($request, [
            'long_url' => 'required|string',
            'suggested_short_url_identifier' => 'string',
        ]);

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
                $short_url_identifier = generate_short_url(10);
            } while (Url::where('short_url_identifier', $short_url_identifier)->exists());
        }

        $request_fill_url_scheme = parse_url($request->fullUrl())['scheme'];
        $request_fill_url_host = parse_url($request->fullUrl())['host'];
        $request_fill_url_port = parse_url($request->fullUrl())['port'];
        $request_fill_url_path = parse_url($request->fullUrl())['path'];

        $long_url_scheme = parse_url($url->long_url)['scheme'];
        $long_url_host = parse_url($url->long_url)['host'];
        $long_url_host_exploded = explode(".", $long_url_host);
        $long_url_host_part_1 = $long_url_host_exploded[sizeof($long_url_host_exploded)-2];
        $long_url_host_part_2 = $long_url_host_exploded[sizeof($long_url_host_exploded)-1];
        $long_url_path = parse_url($url->long_url)['path'];

        $url->short_url_identifier = $short_url_identifier;

        $user = Auth::user();
        $user->urls()->save($url);

        $url->short_url = $request_fill_url_scheme.'://'
            .$long_url_host_part_1.'.'
            .$request_fill_url_host.':'
            . $request_fill_url_port.'/'.
            'r'.'/'.
            $short_url_identifier;

        return response()->json([
            'meta' => [
                'code' => 200,
                'message' => 'OK'
            ], 'data' => [
                'url' => $url,
                'user' => $user
            ]
        ]);
    }
}

function generate_short_url($length = 10) {
    return substr(str_shuffle(str_repeat($x='0123456789abcdefghijklmnopqrstuvwxyz',
        ceil($length/strlen($x)) )),0, $length);
}


