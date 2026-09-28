<?php

namespace App\Http\Controllers;

use Inertia\Inertia;

final class HomeController extends Controller
{
    public function index()
    {
        return Inertia::render('Home', [
            'auth' => ['player' => null],
        ]);
    }

    public function play()
    {
        return Inertia::render('Play');
    }
}
