<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

/**
 * @group User Management
 * 
 * Apis to manage the user resource.
 */

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $validateUser = Validator::make(
            $request->all(),
            [
                'email' => 'required|email',
                'password' => 'required'
            ]
        );

        if ($validateUser->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation Error',
                'errors' => $validateUser->errors()
            ], 401);
        }

        if (!Auth::attempt($request->only(['email', 'password']))) {
            return response()->json([
                'status' => false,
                'message' => 'Email and password does not match with our record.'
            ], 401);
        }

        $user = User::where('email', $request->email)->first();

        return response()->json([
            'status' => true,
            'message' => ' Login Successful',
            'data' => $user,
            'token' => $user->createToken('API TOKEN')->plainTextToken
        ], 200);
    }



    public function register(Request $request)
    {
        $validatecreate = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|unique:users,email',
            'phone' => 'required',
            'password' => 'required'
        ]);

        if ($validatecreate->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'validation error',
                'errors' => $validatecreate->errors()
            ], 422);
        }

        if ($request->hasFile('image')) {
            $img_dir = $request->file('image')->store('image', 'public');
        } else {
            $img_dir = 'upload photo';
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'address' => $request->address,
            'image' => $img_dir,
            'website' => $request->website,
            'password' => Hash::make($request->password)
        ]);

        if (Auth::attempt($request->only(['email', 'password'])))
            $user = User::where('email', $request->email)->first();

        return response()->json([
            'status' => true,
            'message' => 'Account Created Successfully',
            'data' => $user,
            'token' => $user->createToken('API TOKEN')->plainTextToken,
        ]);
    }


    public function edit(User $user, $id)
    {
        $user = User::findOrFail($id);

        if($user->count()<0){
            return response()->json([
                'status' => true,
                'message' => 'no user in this application',
            ], 200);
        }

        return response()->json([
            'status' => true,
            'users' => $user
        ]);
    }


    public function update(Request $request)
    {
        $input = $request->all();
        $user = User::findOrFail(auth()->user()->id);
        $update = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|unique:users,email',
            'phone' => 'required',
            'address' => 'nullable',
            'image' => 'nullable',
            'website' => 'nullable',
            'gender' => 'nullable',
        ]);

        if ($update->fails()) {
            return response()->json([
                'status' => 'false',
                'message' => 'validation error',
                'errors' => $update->errors()
            ], 404);
        }

        if ($request->hasFile('image')) {
           $user->update(array_merge($input,['image'=> $request->file('image')->store('image', 'public')]));
        } else {
           $user->update($input);
        }

        return response()->json([
            'status' => true,
            'message' => 'Profile Updated Successfully',
            'data' => $user,
        ]);
    }
}
