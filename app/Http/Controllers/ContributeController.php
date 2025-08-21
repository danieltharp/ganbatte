<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ContributeController extends Controller
{
    /**
     * Show the contribute tools index page
     */
    public function index()
    {
        return view('contribute.index');
    }

    /**
     * Show the vocabulary JSON generator
     */
    public function vocabularyGenerator()
    {
        return view('contribute.vocabulary.generator');
    }
}
