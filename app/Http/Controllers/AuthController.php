<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum')->only(['logout']);
    }

    /**
     * @OA\Post(
     * path="/login",
     * description="Login by email and password",
     * operationId="authLogin",
     * tags={"User - Auth"},
     *   @OA\RequestBody(
     *       required=true,
     *       @OA\MediaType(
     *           mediaType="multipart/form-data",
     *           @OA\Schema(
     *              required={"email","password"},
     *              @OA\Property(property="email", format="email" ,type="string"),
     *              @OA\Property(property="password", type="password"),
     *           )
     *       )
     *   ),
     * @OA\Response(
     *     response=200,
     *     description="successful operation",
     *  ),
     *  )
    */

    public function login(Request $request)
    {
        $request->validate( [
            'email'    => ['required'],
            'password' => ['required','min:6'],
        ]);

        if (Auth::attempt(['email' => $request->email, 'password' => $request->password], $request->remember??false)) {
            $user = to_user(Auth::user());
            $token = $user->createToken('Sanctum', [])->plainTextToken;
            return response()->json([
                'user'           => new UserResource($user),
                'token'          => $token,
            ], 200);
        }

        return response()->json([
            'message' => 'email or password is incorrect.',
            'errors' => [
                'email' => ['email or password is incorrect.']
            ]
        ], 422);
    }

    /**
     * @OA\Post(
     * path="/logout",
     * description="Logout authorized user",
     * operationId="authLogout",
     * tags={"User - Auth"},
     * security={{"bearer_token":{}}},
     * @OA\Response(
     *    response=200,
     *    description="successful operation"
     *     ),
     * )
    */

    public function logout()
    {
        $user =to_user(Auth::user());
        to_token($user->currentAccessToken())->delete();
    }
}
