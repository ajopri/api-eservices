<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseController as BaseController;
use App\Models\Group;
use App\Models\User;
use App\Models\UserVerify;
use App\Notifications\RegisterNotification;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Str;

class AuthController extends BaseController
{
    public function me(Request $request)
    {
        return $request->user();
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
     * Register
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function register(Request $request)
    {
        // Validate request data
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email|max:255',
            'contact_code' => 'required|string',
        ]);

        // Return errors if validation error occur.
        if ($validator->fails()) {
            $errors = $validator->errors();
            return $this->sendError('Validation error.', $errors, Response::HTTP_BAD_REQUEST);
        }

        if (!$this->checkContactCode($request)) {
            return $this->sendError('Validation error.', 'Contact code already exists', Response::HTTP_BAD_REQUEST);
        }

        // Check if validation pass then create user and auth token. Return the auth token
        // Random password
        $password = Str::random(12);
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'contact_code' => $request->contact_code,
            'password' => Hash::make($password),
            'group_id' => $request->group_id,
            'default' => $request->default
        ]);

        //for verify
        $verify_token = $user->createToken('verify_token')->plainTextToken;
        UserVerify::create([
            'user_id' => $user->id,
            'token' => $verify_token
        ]);

        $user['token'] = $verify_token;

        // For notification
        // $user->notify(new RegisterNotification($user));

        $res = [
            'email' => $user->email,
            'password' => $password,
            'verify_token' => $verify_token,
        ];
        return $this->sendResponse($res, 'Registration successfully.');
    }

    /**
     * Login
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function login(Request $request)
    {
        // Validate request data
        $validator = Validator::make($request->all(), [
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        // Return errors if validation error occur.
        if ($validator->fails()) {
            return $this->sendError(__('auth.failed'), $validator->errors(), 422);
        }

        if (!Auth::attempt($validator->validated())) {
            return $this->sendError('Unauthorized', $validator->errors(), Response::HTTP_UNAUTHORIZED);
        }

        if (Auth::user()->active !== 1) {
            Auth::logout();
            return $this->sendError("Account not active", __('auth.suspend'), Response::HTTP_FORBIDDEN);
        } else {
            $user = User::where('email', $request->email)->firstOrFail();
            $token = $user->createToken('auth_eservices_token')->plainTextToken;
            $res = [
                'access_token' => $token,
                'token_type' => 'Bearer',
                'user' => Auth::user()
            ];
            return $this->sendResponse($res, 'Login successfully.');
        }
    }

    /**
     * Logout
     */
    public function logout()
    {
        Auth::guard('web')->logout();

        return $this->sendResponse(null, 'You have successfully logged out and token was successfull deleted.');
    }

    /**
     * Email verification
     * @param $token
     */
    public function verifyAccount($token)
    {
        $verifyUser = UserVerify::where('token', $token)->first();

        if (!is_null($verifyUser)) {
            $user = $verifyUser->user;
            if (!$user->is_email_verified) {
                $verifyUser->user->is_email_verified = 1;
                $verifyUser->user->email_verified_at = Carbon::now();
                $verifyUser->user->save();

                $activateToken = Str::random(64);
                UserVerify::create([
                    'user_id' => $user->id,
                    'token' => $activateToken
                ]);
                $res = [
                    'email' => $user->email,
                    'activateToken' => $activateToken
                ];
                return $this->sendResponse($res, 'Account verify successfully.');
            } else {
                return $this->sendResponse('', 'Account already verified.');
            }
        } else {
            return $this->sendError('Verify error.', 'Data not found or account is already verified.', Response::HTTP_NOT_FOUND);
        }
    }
}
