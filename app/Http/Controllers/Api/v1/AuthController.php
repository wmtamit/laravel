<?php

namespace App\Http\Controllers\Api\v1;

use App\User;
use App\Rules\AntiXssFinder;
use App\Traits\ApiResponse;
use App\Utils\AppConstant;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    use ApiResponse;

    /**
     * Create a Authcontroller SignUp instance.
     *
     * @return object
     */
    public function signup(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => ['required', 'email', 'unique:users', 'max:150', new AntiXssFinder()],
            'name' => ['required', 'min:3', 'max:30', new AntiXssFinder()],
            'password' => ['required', 'min:8', 'max:30', new AntiXssFinder()],
            'confirm_password' => ['required_with:password', 'same:password', new AntiXssFinder()]
        ]);

        if ($validator->fails()) {
            $this->setData('status', AppConstant::STATUS_FAIL);
            $this->setData('message', $validator->messages()->first());
            return response()->json($this->setResponse(), AppConstant::UNPROCESSABLE_REQUEST);
        }

        try {
            DB::beginTransaction();

            /*  User Table */
            $user = new User();
            $user->email = strtolower($request->email);
            $user->name = strtolower($request->name);
            $user->password = Hash::make($request->password);
            $user->save();

            DB::commit();
        } catch (QueryException $e) {
            DB::rollBack();
            $this->setData('status', AppConstant::STATUS_FAIL);
            $this->setData('message', __('auth.server_error'));
            return response()->json($this->setResponse(), AppConstant::INTERNAL_SERVER_ERROR);
        }

        /*  Return user  */
        $this->setData('status', AppConstant::STATUS_OK);
        $this->setData('token', $user->createToken(config('app.name'))->accessToken);
        $this->setData('message', __('auth.register_success'));
        $this->setData("user", $user);
        return response()->json($this->setResponse(), AppConstant::CREATED);
    }

    /**
     * Create a Authcontroller Login instance.
     *
     * @return object
     */
    public function signin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => ['required', new AntiXssFinder()],
            'password' => ['required', new AntiXssFinder()]
        ]);

        if ($validator->fails()) {
            $this->setData('status', AppConstant::STATUS_FAIL);
            $this->setData('message', $validator->messages()->first());
            return response()->json($this->setResponse(), AppConstant::UNPROCESSABLE_REQUEST);
        }

        try {
            DB::beginTransaction();

            /*  Check user login's  */
            $password = $request->password;
            $user = User::where('email', $request->email)->first();

            if (!$user || !Hash::check($password, $user->password)) {
                $this->setData('status', AppConstant::STATUS_FAIL);
                $this->setData('message', __('auth.login_failed'));
                return response()->json($this->setResponse(), AppConstant::UNAUTHORIZED);
            }

//            if ($user->status == AppConstant::STATUS_INACTIVE) {
//                $this->setData('status', AppConstant::STATUS_FAIL);
//                $this->setData('message', __('auth.unverified_user'));
//                return response()->json($this->setResponse(), AppConstant::UNAUTHORIZED);
//            }
            DB::commit();
        } catch (QueryException $exception) {
            DB::rollBack();
            $this->setData('status', AppConstant::STATUS_FAIL);
            $this->setData('message', __('auth.server_error'));
            return response()->json($this->setResponse(), AppConstant::INTERNAL_SERVER_ERROR);
        }

        /*  Return user  */
        $this->setData('status', AppConstant::STATUS_OK);
        $this->setData('token', $user->createToken(config('app.name'))->accessToken);
        $this->setData('message', __('auth.login_success'));
        $this->setData("user", $user);
        return response()->json($this->setResponse(), AppConstant::OK);
    }

    /**
     * Create a Authcontroller Logout instance.
     *
     * @return null
     */
    public function logout()
    {
        try {
            DB::beginTransaction();
            if (Auth::user()) {
                Auth::user()->token()->revoke();
            } else {
                $this->setData('status', AppConstant::STATUS_FAIL);
                $this->setData('message', __('Please try to login.'));
                return response()->json($this->setResponse(), AppConstant::INTERNAL_SERVER_ERROR);
            }
//            DB::table('oauth_access_tokens')->where('user_id', $request->user()->id)->delete();
            DB::commit();
        } catch (QueryException $e) {
            DB::rollBack();
            $this->setData('status', AppConstant::STATUS_FAIL);
            $this->setData('message', __('auth.server_error'));
            return response()->json($this->setResponse(), AppConstant::INTERNAL_SERVER_ERROR);
        }
        $this->setData('status', AppConstant::STATUS_OK);
        $this->setData('message', __('auth.logout_success'));
        return response()->json($this->setResponse(), AppConstant::OK);
    }
}
