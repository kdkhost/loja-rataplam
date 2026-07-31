<?php

namespace App\Repositories\Back;

use App\Models\Post;
use App\Helpers\ImageHelper;
use App\Services\BlogSlugService;
use App\Services\BlogHtmlSanitizer;
use App\Services\BlogFileService;

class PostRepository
{

    /**
     * Store post.
     *
     * @param  \App\Http\Requests\ImageStoreRequest  $request
     * @return void
     */

    public function store($request)
    {
        $input = $request->all();
        $input['slug'] = BlogSlugService::generateSlug($request->title);
        $input['details'] = BlogHtmlSanitizer::sanitize($request->details ?? '');
        if ($request->has('tags')) {
            $input['tags'] = str_replace(["value", "{", "}", "[", "]", ":", "\""], '', $request->tags);
        }
        if ($request->photo) {
            $input['photo'] = $this->storeImageData($request);
        }

        Post::create($input);
    }

    /**
     * Update post.
     *
     * @param  \App\Http\Requests\ImageUpdateRequest  $request
     * @return void
     */

    public function update($post, $request)
    {
        $input = $request->all();
        $input['slug'] = BlogSlugService::generateSlug($request->title, $post->id);
        $input['details'] = BlogHtmlSanitizer::sanitize($request->details ?? '');
        if ($request->has('tags')) {
            $input['tags'] = str_replace(["value", "{", "}", "[", "]", ":", "\""], '', $request->tags);
        }
        if ($request->photo) {
            $input['photo'] = $this->UpdateImageData($request, $post);
        }
        $post->update($input);
    }


    public function storeImageData($request)
    {
        $storeData = [];
        if ($photos = $request->file('photo')) {
            foreach ($photos as $key => $photo) {
                $storeData[$key] = ImageHelper::handleUploadedImage($photo, 'images');
            }
        }
        return $storeData;
    }

    public function UpdateImageData($request, $post)
    {
        $storeData = is_array($post->photo) ? $post->photo : [];

        if ($photos = $request->file('photo')) {
            foreach ($photos as $key => $photo) {
                array_push($storeData, ImageHelper::handleUploadedImage($photo, 'images'));
            }
        }

        return $storeData;
    }


    /**
     * Delete post.
     *
     * @param  Post  $post
     * @return void
     */

    public function delete($post)
    {
        $images = is_array($post->photo) ? $post->photo : [];
        foreach ($images as $image) {
            BlogFileService::deleteImage($image);
        }
        $post->delete();
    }

    /**
     * Delete single photo.
     *
     * @param  int  $key
     * @param  int  $id
     * @return void
     */

    public function photoDelete($key, $id)
    {
        $post = Post::findOrFail($id);
        $photos = is_array($post->photo) ? $post->photo : [];
        if (!isset($photos[$key])) {
            return;
        }

        $delete_photo = $photos[$key];
        BlogFileService::deleteImage($delete_photo);

        unset($photos[$key]);
        $post->update(['photo' => array_values($photos)]);
    }
}
