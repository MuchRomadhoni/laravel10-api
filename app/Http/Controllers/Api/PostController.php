<?php

namespace App\Http\Controllers\Api;

//import model
use App\Models\Post;

//import resource
use Illuminate\Http\Request;

use App\Http\Controllers\Controller;
use App\Http\Resources\PostResource;

//import validator
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class PostController extends Controller
{
    //

    public function index()
    {
        $post = Post::latest()->paginate(5);

        //return collection of posts as a resource
        return new PostResource(true, 'List Data Post', $post);
    }

    //store method
    public function store(Request $request)
    {
        //validation rules
        $validator = Validator::make($request->all(), [
            'title' => 'required',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'content' => 'required'
        ]);

        //check if validation fails
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        //upload image
        $image = $request->file('image');
        $image->storeAs('public/posts', $image->hashName());

        //create post
        $post = Post::create([
            'title' => $request->title,
            'image' => $image->hashName(),
            'content' => $request->content
        ]);

        //return response
        if ($post) {
            return new PostResource(true, 'Data Post Berhasil Ditambahkan', $post);
        } else {
            return new PostResource(false, 'Data Post Gagal Ditambahkan', $post);
        }
    }

    //show method
    public function show($id)
    {
        $post = Post::findOrfail($id);
        return new PostResource(true, 'Detail Data Post', $post);
    }

    //update method
    public function update(Request $request, $id)
    {
        $post = Post::findOrfail($id);

        //validation rules
        $validator = Validator::make($request->all(), [
            'title' => 'required',
            'image' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'content' => 'required'
        ]);

        //check if validation fails
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        //check if image is not empty
        if ($request->hasFile('image')) {

            //upload image
            $image = $request->file('image');
            $image->storeAs('public/posts', $image->hashName());

            //delete old image
            Storage::delete('public/posts/' . basename($post->image));

            //update post with new image
            $post->update([
                'image'     => $image->hashName(),
                'title'     => $request->title,
                'content'   => $request->content,
            ]);
        } else {
            //update post without image
            $post->update([
                'title'     => $request->title,
                'content'   => $request->content,
            ]);
        }

        //return response
        return new PostResource(true, 'Data Post Berhasil Diubah!', $post);
    }

    //destroy method
    public function destroy($id)
    {
        $post = Post::findOrfail($id);

        //delete image
        Storage::delete('public/posts/' . basename($post->image));
        $post->delete();
        return new PostResource(true, 'Data Post Berhasil Dihapus!', null);
    }
}
