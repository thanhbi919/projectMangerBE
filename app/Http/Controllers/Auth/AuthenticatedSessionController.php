<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use mysql_xdevapi\Exception;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class AuthenticatedSessionController extends Controller
{
    /**
     * Handle an incoming authentication request.
     *
     * @throws ValidationException
     */
    public function store(LoginRequest $request): \Illuminate\Http\JsonResponse
    {

        try {
            $request->authenticate();
            $request->session()->regenerate();

            $user = Auth::user();

            return response()->json(['message' => 'Successfully logged in.', 'data' => $user], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], ResponseAlias::HTTP_BAD_REQUEST);
        }

    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): Response
    {
        //        Auth::guard('web')->logout();

        Auth::logout();
        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return response()->noContent();
    }

    public function currentUser(Request $request)
    {
        $user = auth()->user();
        if ($user) {
            return response()->json($user, 200);
        }
        return response()->json(['message' => 'Not authenticated'], 401);
    }

}
