<?php

namespace App\Providers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\ServiceProvider;

class ResponseMacroServiceProvider extends ServiceProvider
{
    /**
     * Register the application's response macros.
     *
     * @return void
     */
    public function boot()
    {
        Response::macro('failure', function (int $code, array $data, array $messages, Request $request) {
            return response()->json([
                'meta' => [
                    'code' => $code,
                    'messages' => $messages,
                    'params' => $request->all(),
                    'path' => $request->path()
                ], 'data' => $data
            ]);
        });

        Response::macro('success', function (int $code, array $data, array $messages, Request $request) {
            return response()->json([
                'meta' => [
                    'code' => $code,
                    'message' => $messages,
                    'params' => $request->all(),
                    'path' => $request->path()
                ], 'data' => $data
            ]);
        });
    }
}
