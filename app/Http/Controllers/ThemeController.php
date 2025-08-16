<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ThemeController extends Controller
{
    public function update(Request $request)
    {
        // dd($request->theme);
        $request->validate([
            'theme' => 'required|string|in:blue-theme,light,dark,semi-dark,bordered-theme',
        ]);

        $user = Auth::user();
        $user->theme = $request->theme;
        $user->save();

        return back()->with('status', 'Theme updated successfully!');
    }
}
