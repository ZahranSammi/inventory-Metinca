<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    //
    public function dashboard()
    {
        //load data disini jika perlu

        return view('dashboard');
    }
}
