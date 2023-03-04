<?php

namespace TahsinGokalp\Lett\Http\Controllers;

use ErrorException;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use TahsinGokalp\Lett\Lett;

class LettReportController
{
    /**
     * @return Response|ResponseFactory
     */
    public function report(Request $request)
    {
        if (!(bool) config('lett.javascript_reporting')) {
            return response('Javascript reporting disabled', 500);
        }

        /* @var Lett $lett*/
        $lett = app('lett');

        $lett->handle(
            new ErrorException($request->input('message')),
            'javascript',
            [
                'file'    => $request->input('file'),
                'line'    => $request->input('line'),
                'message' => $request->input('message'),
                'stack'   => $request->input('stack'),
                'url'     => $request->input('url'),
            ]
        );

        return response('ok', 200);
    }
}
