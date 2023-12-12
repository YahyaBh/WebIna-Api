<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;

class SocialAuthController extends Controller
{
    public function redirectToProvider($provider): JsonResponse
    {
        return response()->json([
            'url' => Socialite::driver($provider)
                ->with(["prompt" => "select_account"])
                ->stateless()
                ->redirect()
                ->getTargetUrl(),
            'status' => 'success',
        ], 200);
    }

    public function handleProviderCallback($provider)
    {


        try {
            /** @var SocialiteUser $socialiteUser */
            $socialiteUser = Socialite::driver($provider)->stateless()->user();
        } catch (Exception $e) {
            return response()->json([
                'error' => 'Invalid credentials provided.',
                'message' => $e->getMessage()
            ], 422);
        }

        /** @var User $user */
        $user = User::query()
            ->firstOrCreate(
                [
                    'email' => $socialiteUser->getEmail(),
                ],
                [
                    'email_verified_at' => now(),
                    'name' => $socialiteUser->getName(),
                    'google_id' => $socialiteUser->getId(),
                    'avatar' => $socialiteUser->getAvatar(),
                    'role' => 'client',
                ]
            );


        return response()->json([
            'message' => 'Login successful',
            'user' => $user,
            'token' => $user->createToken('access-token')->plainTextToken,
        ]);
    }


    public function handleUserDeletion(Request $request, $provider)
    {
        // Log the incoming request for debugging purposes
        Log::info($provider . ' Data Deletion Callback:', $request->all());

        // Extract user ID from the request
        $userId = $request->input('id');

        // Perform actions to delete user data based on the user ID
        try {
            // Example: Delete user from the database
            $deletedUser = User::where('id', $userId)->first();

            if ($deletedUser) {
                $deletedUser->delete();

                // Example: Notify administrators or log the deletion event
                Log::info('User data deleted for ' . $provider . ' ID: ' . $userId);

                // Return a success response
                return response()->json(['status' => 'success']);
            } else {
                // User not found
                Log::error('User not found for Facebook ID: ' . $userId);

                // Return a response indicating failure
                return response()->json(['status' => 'failure', 'error' => 'User not found'], 404);
            }
        } catch (\Exception $e) {
            // Handle exceptions or errors during deletion
            Log::error('Error deleting user data: ' . $e->getMessage());

            // Return a response indicating failure
            return response()->json(['status' => 'failure', 'error' => 'Error during deletion'], 500);
        }
    }
}
