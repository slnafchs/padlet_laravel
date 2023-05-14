<?php

namespace App\Http\Controllers;

use App\Models\Padlet;
use Illuminate\Http\Request;

class PadletController extends Controller
{
    public function index()
    {
        $padlets = Padlet::all();
        return view('padlets.index', compact('padlets'));
    }

    public function show($padlet)
    {
        $padlet = Padlet::find($padlet);
        return view('padlets.show', compact('padlet'));
    }
}
