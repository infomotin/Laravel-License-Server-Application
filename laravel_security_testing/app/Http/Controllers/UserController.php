<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class UserController extends Controller
{
    //index
    public function index()
    {
        $users = User::all();
        return view('pages.users.index', compact('users'));

    }
    //search
    public function search(Request $request)
    {
        $query = $request->input('search');
        // dd( $query);
        $users = User::query()
            ->when($query, function ($q) use ($query) {
                $q->where('name', 'LIKE', "%{$query}%")
                  ->orWhere('email', 'LIKE', "%{$query}%");
            })
            ->orderBy('id', 'desc')
            ->get();
        return view('pages.users.search', compact('users'));
    }
}
