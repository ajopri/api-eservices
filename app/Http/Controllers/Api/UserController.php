<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseController;
use App\Models\Group;
use App\Models\User;
use App\Models\UserVerify;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Str;

class UserController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $users = User::all();
        $users->load(['roles', 'group']);

        return $this->sendResponse($users, 'Users retrieved successfully.');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $user = User::with(['roles', 'group'])->where('id', $id)->orWhere('contact_code', $id)->get();

        if (is_null($user)) {
            return $this->sendError('User not found.');
        }

        return $this->sendResponse($user, 'User retrieved successfully.');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $user = User::find($id);

        if (is_null($user)) {
            return $this->sendError('User not found.');
        }

        if (!empty($request->contact_code)) {
            if (!$this->checkContactCode($request)) {
                $user->contact_code = $request->contact_code;
            } else {
                return $this->sendError('Contact code must be unique');
            }
        }

        $user->name = $request->name;
        $user->email = $request->email;
        $user->group_id = $request->group_id;
        $user->default = $request->default;
        $user->active = $request->active;
        $user->role_id = $request->role_id;
        $user->save();

        return $this->sendResponse($user, 'User updated successfully.');
    }

    public function checkContactCode($req)
    {
        $getGroup = Group::findOrFail($req->group_id);

        $check = User::where("contact_code", $req->contact_code)->whereHas('group', function (Builder $query) use ($getGroup) {
            $query->where('rcc', $getGroup->rcc);
        })->first();

        return $check ? false : true;
    }

    /**
     * User Activate
     * @param \Illuminate\Http\Request $request
     */
    public function activateAccount(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed'],
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }
        if (!is_null($request->token)) {
            $verifyUser = UserVerify::where('token', $request->token)->first();
            $user = $verifyUser->user;
        } else {
            $verifyUser = User::with('verify')->where('email', $request->email)->first();
            $user = $verifyUser;
        }

        if (is_null($verifyUser)) {
            return $this->sendError('User not found.', 'Not Found', Response::HTTP_NOT_FOUND);
        }

        if (!$user->active) {
            $user->active = 1;
            $user->activated_at = Carbon::now();
            $user->password = Hash::make($request->password);
            $user->remember_token = Str::random(60);
            $user->save();
            $message = "Your e-mail is active. You can login now.";
            UserVerify::where('user_id', $user->id)->delete();
        } else {
            $message = "Your e-mail is already active. You can login now.";
        }

        return $this->sendResponse($message, 'User is activated.');
    }
}
