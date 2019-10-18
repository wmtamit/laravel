<?php

namespace App\Http\Middleware;

use App\Utils\AppConstant;
use App\Traits\ApiResponse;
use Closure;

class CheckApiHeader
{
    use ApiResponse;
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $auth = $request->header('Authorization');
        $content = $request->header('Content-Type');
        if (isset($auth) && isset($content)) {
            return $next($request);
        }
        $this->setData('message', __('Please check your header data.'));
        return response()->json($this->setResponse(), AppConstant::OK);

    }
}
