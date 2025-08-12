<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\Article\ArticlesListApiResource;
use App\Models\Article;

class ArticleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $articles = Article::with([
            'user' => function ($query) {
                $query->select('id', 'first_name', 'last_name');
            }
        ])->get();
        return ArticlesListApiResource::collection($articles);
    }
}
