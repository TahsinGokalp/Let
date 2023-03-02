<?php

namespace Lett\Http\Controllers;

use Illuminate\Http\Request;

class LettReportController
{
    /**
     * @return \Illuminate\Http\Response|\Illuminate\Contracts\Routing\ResponseFactory
     */
    public function report(Request $request)
    {
        $lett = app('lett');

        $lett->handle(
            new \ErrorException($request->input('message')),
            'javascript',
            [
                'file' => $request->input('file'),
                'line' => $request->input('line'),
                'message' => $request->input('message'),
                'stack' => $request->input('stack'),
                'url' => $request->input('url'),
            ]
        );

        return response('ok', 200);
    }
}
