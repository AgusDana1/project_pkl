<?php

namespace App\Http\Controllers\Api;

// import models Post
use App\Models\Post;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

// import Resource "Post Resource"
use App\Http\Resources\PostResource;

// import Facade Storage
use Illuminate\Support\Facades\Storage;

// import Facade "Validator"
use Illuminate\Support\Facades\Validator;

class PostController extends Controller
{
    /**
     * index
     * 
     * @return void
     */

     public function index() {

         //  tampilkan data
         $posts = Post::latest()->paginate(5);

         // return collection
         return new PostResource(true, "List Data Posts", $posts);
     }

     /**
      * menambahkan data menggunakan method store
      * 
      * @param mixed $request
      * @return void
      */

      public function store(Request $request)
      {
        // definisikan validation rule
        $validator = Validator::make($request->all(), [
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2044',
            'title' => 'required',
            'content' => 'required',
        ]);

        // check if validation 
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // upload image 
        $image = $request->file('image');
        $image->storeAs('public/posts', $image->hashName());

        // create posts
        $post = Post::create([
            'image' => $image->hashName(),
            'title' => $request->title,
            'content' => $request->content,
        ]);

        // return response
        return new PostResource(true, 'Data berhasil ditambah!', $post);
      }

    /**
     * menampilkan data
     * 
     * @param mixed $post
     * @return void
     */

     public function show($id)
     {
        // find post by id
        $post = Post::find($id);

        // return single post as a resource
        return new PostResource(true, 'Detail Data dari post', $post);
     }

     /**
      * update data 
      * 
      * @param mixed $request
      * @param mixed $post
      * @return void
      */

      public function update(Request $request, $id)
      {
        // definisi validator rules
        $validator = Validator::make($request->all(), [
            'title' => 'required',
            'content' => 'required',
        ]);

        // check jika validasi gagal
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // find post by id
        $post = Post::find($id);

        // check jika image tidak kosong
        if ($request->hasFile('image')) {
            
            // upload image
            $image = $request->file('image');
            $image->storeAs('public/posts'.basename($post->image));

            // update post dengan image baru
            $post->update([
                'image' => $image->hashName(),
                'title' => $request->title,
                'content' => $request->content,
            ]);

        } else {
            // update tanpa image baru
            $post->update([
                'title' => $request->title,
                'content' => $request->content,
            ]);
        }

        // return response
        return new PostResource(true, 'Data post berhasil dirubah!', $post);
      }

      /**
       * destroy
       * 
       * @param mixed $post
       * @return void
       */

       public function destroy($id)
       {
        // find post by id
        $post = Post::find($id);

        // delete image
        Storage::delete('public/posts/'.basename($post->image));

        // delete post
        $post->delete();

        // return response
        return new PostResource(true, 'Data post berhasil didelete!', null);
       }
}
