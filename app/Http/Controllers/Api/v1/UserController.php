<?php

namespace App\Http\Controllers\Api\v1;

use App\Models\Users;
use App\Rules\AntiXssFinder;
use App\Traits\ApiResponse;
use App\Utils\AppConstant;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    use ApiResponse;

    /**
     * Create a UserController Change Profile instance.
     *
     * @return object
     */
    public function changeProfile(Request $request)
    {
        $userId = $request->user->id;
        $email = $request->email;
        $username = $request->username;

        $validator = Validator::make($request->all(), [
            'first_name' => ['required', 'min:3', 'max:150', new AntiXssFinder()],
            'last_name' => ['required', 'min:3', 'max:150', new AntiXssFinder()],
            'username' => ['required', 'min:3', 'max:30', new AntiXssFinder(), Rule::unique('users', 'username')
                ->ignore($userId)->where(function ($query) use ($username) {
                    $query->where(array(
                        'username' => $username
                    ));
                })],
            'address' => ['required', 'min:6', new AntiXssFinder()],
            'city' => ['required', 'min:3', 'max:150', new AntiXssFinder()],
            'state' => ['required', 'min:3', 'max:150', new AntiXssFinder()],
            'country' => ['nullable', 'min:3', 'max:150', new AntiXssFinder()],
            'zipcode' => ['required', 'alpha_num', 'min:4', 'max:7', new AntiXssFinder()],
            'latitude' => ['nullable', new AntiXssFinder()],
            'longitude' => ['nullable', new AntiXssFinder()],
            'email' => ['required', 'min:3', 'max:150', new AntiXssFinder(), Rule::unique('users', 'email')
                ->ignore($userId)->where(function ($query) use ($email) {
                    $query->where(array(
                        'email' => $email
                    ));
                })],
            'birthdate' => ['required', 'date_format:Y-m-d', 'before:today', new AntiXssFinder()],
            'gender' => ['nullable', 'numeric', new AntiXssFinder()],
            'profile_picture' => ['nullable', 'image', 'mimes:jpg,png,jpeg', 'max:' . AppConstant::IMAGE_SIZE, new AntiXssFinder()],
        ]);

        if ($validator->fails()) {
            $this->setMeta('status', AppConstant::STATUS_FAIL);
            $this->setMeta('message', $validator->messages()->first());
            return response()->json($this->setResponse(), AppConstant::UNPROCESSABLE_REQUEST);
        }

        try {
            DB::beginTransaction();

            $user = Users::find($userId);
            $user->username = $request->username;
            $user->first_name = $request->first_name;
            $user->last_name = $request->last_name;
            $user->address = $request->address;
            $user->city = $request->city;
            $user->state = $request->state;
            $user->country = $request->country;
            $user->zipcode = $request->zipcode;
            $user->latitude = $request->latitude;
            $user->longitude = $request->longitude;
            $user->email = $request->email;
            $user->birthdate = $request->birthdate;
            $user->gender = $request->gender;

            if ($request->hasFile('profile_picture')) {
                $profile_picture = $request->profile_picture;

                $oldImage = $user->profile_picture;

                if ($oldImage != "") {
                    $path_parts = pathinfo($oldImage);
                    Storage::delete('/public' . '/profile_picture/users/' . $path_parts['basename']);
                }

                $filename = mt_rand(111111, 999999) . '_' . $userId;
                $path = URL('profile_picture/users');
                File::makeDirectory($path, $mode = 0777, true, true);
                $profilePicPath = $profile_picture->storeAs(
                    'profile_picture/users', $filename . ".png", 'public'
                );

                $user->profile_picture = $profilePicPath;
                $user->avatar = null;
            }

            if ($request->filled('avatar')) {

                $oldImage = $user->profile_picture;

                if ($oldImage != "") {
                    $path_parts = pathinfo($oldImage);
                    Storage::delete('/public' . '/profile_picture/users/' . $path_parts['basename']);
                }

                $user->profile_picture = null;
                $user->avatar = $request->avatar;
            }

            $user->save();

            DB::commit();
        } catch (QueryException $e) {
            DB::rollBack();
            $this->setMeta('status', AppConstant::STATUS_FAIL);
            $this->setMeta('message', __('auth.server_error'));
            return response()->json($this->setResponse(), AppConstant::INTERNAL_SERVER_ERROR);
        }

        /*  Return user  */
        $users = Users::with('hometown')->where('id', $user->id)->first();
        $this->setMeta('status', AppConstant::STATUS_OK);
        $this->setMeta('message', __('auth.change_profile'));
        $this->setData("user", $users);
        return response()->json($this->setResponse(), AppConstant::OK);
    }

    /**
     * Create a UserController Users latest profile.
     *
     * @api  Request $request
     * @return object
     */
    public function getProfile(Request $request)
    {
        //$user = $request->user;
        $users = Users::with('hometown')->where('id', $request->user->id)->first();
        $this->setMeta('status', AppConstant::STATUS_OK);
        $this->setMeta('message', __('auth.get_profile'));
        $this->setData('user', $users);
        return response()->json($this->setResponse(), AppConstant::OK);
    }
}