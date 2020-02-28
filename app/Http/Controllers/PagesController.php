<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\TestInter;

class PagesController extends Controller
{
    public function root()
    {
        return view('pages.root');
    }
}
