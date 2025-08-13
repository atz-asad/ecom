<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Helpers\Uploader;
use App\Models\Bcategory;
use App\Models\Language;
use App\Models\Blog;
use Purifier;
use Validator;
use Session;

class BlogController extends Controller
{
    public function index(Request $request)
    {
        $lang = Language::where('code', $request->language)->firstOrFail();

        $lang_id = $lang->id;
        $data['lang_id'] = $lang_id;
        $data['blogs'] = Blog::where('language_id', $lang_id)->orderBy('id', 'DESC')->get();
        $data['bcats'] = Bcategory::where('language_id', $lang_id)->where('status', 1)->get();
        return view('admin.blog.blog.index', $data);
    }

    public function edit($id)
    {
        $data['blog'] = Blog::findOrFail($id);
        $data['bcats'] = Bcategory::where('language_id', $data['blog']->language_id)->where('status', 1)->get();
        return view('admin.blog.blog.edit', $data);
    }

    public function store(Request $request)
    {
        $img = $request->file('image');
        $allowedExts = array('jpg', 'png', 'jpeg');

        $slug = make_slug($request->title);

        $rules = [
            'language_id' => 'required',
            'title' => 'required|max:255',
            'category' => 'required',
            'content' => 'required',
            'serial_number' => 'required|integer',
            'image' => 'required|mimes:jpg,jpeg,png',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            $validator->getMessageBag()->add('error', 'true');
            return response()->json($validator->errors());
        }

        $input = $request->all();
        $input['bcategory_id'] = $request->category;
        $input['slug'] = $slug;

        if ($request->hasFile('image')) {
            $dir = public_path('assets/front/img/blogs/');
            $filename =  Uploader::upload_picture($dir, $request->file('image'));
            Session::put('blog_image', $filename);
            $input['main_image'] = $filename;
        }
        $input['content'] = Purifier::clean($request->content);
        $blog = new Blog;
        $blog->create($input);
        Session::flash('success', __('Created Successfully'));
        return "success";
    }

    public function update(Request $request)
    {
        $slug = make_slug($request->title);
        $blog = Blog::findOrFail($request->blog_id);

        $rules = [
            'title' => 'required|max:255',
            'category' => 'required',
            'content' => 'required',
            'serial_number' => 'required|integer',
            'image' => 'mimes:jpg,jpeg,png',
        ];
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            $validator->getMessageBag()->add('error', 'true');
            return response()->json($validator->errors());
        }

        $input = $request->all();
        $blog = Blog::findOrFail($request->blog_id);
        $input['bcategory'] = $request->category;
        $input['slug'] = $slug;
        if ($request->hasFile('image')) {
            $dir = public_path('assets/front/img/blogs/');
            @unlink($dir . $blog->main_image);
            $input['main_image'] = Uploader::upload_picture($dir, $request->file('image'));
        }

        $input['content'] = Purifier::clean($request->content);
        $blog->update($input);
        Session::flash('success', __('Updated Successfully'));
        return "success";
    }

    public function delete(Request $request)
    {
        $blog = Blog::findOrFail($request->blog_id);
        @unlink(public_path('assets/front/img/blogs/') . $blog->main_image);
        $blog->delete();
        Session::flash('success', __('Deleted Successfully'));
        return back();
    }

    public function bulkDelete(Request $request)
    {
        $ids = $request->ids;
        foreach ($ids as $id) {
            $blog = Blog::findOrFail($id);
            @unlink(public_path('assets/front/img/blogs/') . $blog->main_image);
            $blog->delete();
        }
        Session::flash('success', __('Deleted Successfully'));
        return "success";
    }

    public function getcats($langid)
    {
        $bcategories = Bcategory::where('language_id', $langid)->where('status', 1)->get();
        return $bcategories;
    }
}
