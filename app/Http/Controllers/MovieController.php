<?php

namespace App\Http\Controllers;

use App\Models\Movie;
use App\Models\Category;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;
use App\Http\Requests\StoreMovieRequest;
use App\Http\Requests\UpdateMovieRequest;

class MovieController extends Controller
{
    public function index()
    {
        $query = Movie::latest();

        if (request('search')) {
            $query->where('judul', 'like', '%' . request('search') . '%')
                ->orWhere('sinopsis', 'like', '%' . request('search') . '%');
        }

        $movies = $query->paginate(6)->withQueryString();

        return view('homepage', compact('movies'));
    }

    public function detail(Movie $movie)
    {
        return view('detail', compact('movie'));
    }

    public function create()
    {
        $categories = Category::all();
        return view('input', compact('categories'));
    }

    public function store(StoreMovieRequest $request)
    {
        $fileName = $this->handleImageUpload($request->file('foto_sampul'));

        Movie::create([
            'id' => $request->id,
            'judul' => $request->judul,
            'category_id' => $request->category_id,
            'sinopsis' => $request->sinopsis,
            'tahun' => $request->tahun,
            'pemain' => $request->pemain,
            'foto_sampul' => $fileName,
        ]);

        return redirect('/')->with('success', 'Data berhasil disimpan');
    }

    public function data()
    {
        $movies = Movie::latest()->paginate(10);
        return view('data-movies', compact('movies'));
    }

    public function form_edit(Movie $movie)
    {
        $categories = Category::all();
        return view('form-edit', compact('movie', 'categories'));
    }

    public function update(UpdateMovieRequest $request, Movie $movie)
    {
        $data = $request->only(['judul', 'sinopsis', 'category_id', 'tahun', 'pemain']);

        if ($request->hasFile('foto_sampul')) {
            $fileName = $this->handleImageUpload($request->file('foto_sampul'));

            if (File::exists(public_path('images/' . $movie->foto_sampul))) {
                File::delete(public_path('images/' . $movie->foto_sampul));
            }

            $data['foto_sampul'] = $fileName;
        }

        $movie->update($data);

        return redirect('/movies/data')->with('success', 'Data berhasil diperbarui');
    }

    public function delete(Movie $movie)
    {
        if (File::exists(public_path('images/' . $movie->foto_sampul))) {
            File::delete(public_path('images/' . $movie->foto_sampul));
        }

        $movie->delete();

        return redirect('/movies/data')->with('success', 'Data berhasil dihapus');
    }

    private function handleImageUpload($file)
    {
        $randomName = Str::uuid()->toString();
        $fileExtension = $file->getClientOriginalExtension();
        $fileName = $randomName . '.' . $fileExtension;

        $file->move(public_path('images'), $fileName);

        return $fileName;
    }
}
