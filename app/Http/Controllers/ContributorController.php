<?php
namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Contributor;
use Illuminate\Http\Request;

class ContributorController extends Controller
{
    // Mendaftarkan user sebagai contributor
    public function registerAsContributor(Request $request)
    {
        $user = $request->user();

        if ($user->contributor) {
            return response()->json(['message' => 'Already a contributor'], 400);
        }

        $contributor = new Contributor();
        $contributor->user_id = $user->id;
        $contributor->bio = $request->input('bio');
        $contributor->save();

        return response()->json(['message' => 'Registered as contributor', 'contributor' => $contributor], 201);
    }
}