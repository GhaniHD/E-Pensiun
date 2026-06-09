<?php

namespace App\Http\Controllers;

use App\Models\Article;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ArticleController extends Controller
{
    public function index(Request $request)
    {
        $query = Article::with('author')->published();

        if ($request->filled('category')) {
            $query->byCategory($request->category);
        }

        if ($request->filled('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        $articles   = $query->latest('published_at')->paginate(12)->withQueryString();
        $categories = Article::published()
            ->select('category')
            ->distinct()
            ->pluck('category');

        return view('articles.index', compact('articles', 'categories'));
    }

    public function show(Article $article)
    {
        if (!$article->is_published) {
            abort(404);
        }

        $related = Article::published()
            ->where('id', '!=', $article->id)
            ->where('category', $article->category)
            ->latest('published_at')
            ->take(3)
            ->get();

        return view('articles.show', compact('article', 'related'));
    }

    // ── CRUD (TIK / SDM Kanwil) ───────────────────────────

    public function create()
    {
        return view('articles.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title'        => ['required', 'string', 'max:255'],
            'content'      => ['required', 'string'],
            'category'     => ['required', 'string', 'max:100'],
            'thumbnail'    => ['nullable', 'image', 'max:2048'],
            'is_published' => ['boolean'],
        ]);

        if ($request->hasFile('thumbnail')) {
            $data['thumbnail'] = $request->file('thumbnail')->store('articles/thumbnails', 'public');
        }

        $data['author_id']     = Auth::id();
        $data['slug']          = Str::slug($data['title']);
        $data['published_at']  = $data['is_published'] ? now() : null;

        Article::create($data);

        return redirect()->route('articles.index')
                         ->with('success', 'Artikel berhasil dipublikasikan.');
    }

    public function edit(Article $article)
    {
        return view('articles.edit', compact('article'));
    }

    public function update(Request $request, Article $article)
    {
        $data = $request->validate([
            'title'        => ['required', 'string', 'max:255'],
            'content'      => ['required', 'string'],
            'category'     => ['required', 'string', 'max:100'],
            'thumbnail'    => ['nullable', 'image', 'max:2048'],
            'is_published' => ['boolean'],
        ]);

        if ($request->hasFile('thumbnail')) {
            if ($article->thumbnail) {
                Storage::disk('public')->delete($article->thumbnail);
            }
            $data['thumbnail'] = $request->file('thumbnail')->store('articles/thumbnails', 'public');
        }

        if ($data['is_published'] && !$article->published_at) {
            $data['published_at'] = now();
        }

        $article->update($data);

        return redirect()->route('articles.index')
                         ->with('success', 'Artikel berhasil diperbarui.');
    }

    public function destroy(Article $article)
    {
        if ($article->thumbnail) {
            Storage::disk('public')->delete($article->thumbnail);
        }

        $article->delete();

        return redirect()->route('articles.index')
                         ->with('success', 'Artikel berhasil dihapus.');
    }
}
