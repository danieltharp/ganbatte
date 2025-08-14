<?php

namespace App\Http\Controllers;

use App\Models\Article;
use Illuminate\Http\Request;

class ArticleController extends Controller
{
    /**
     * Display the specified article.
     */
    public function show(Article $article)
    {
        return view('articles.show', compact('article'));
    }
}
