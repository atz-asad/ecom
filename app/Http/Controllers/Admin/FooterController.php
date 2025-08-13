<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Helpers\Uploader;
use App\Models\BasicSetting as BS;
use App\Models\BasicExtended;
use App\Models\Language;
use Validator;
use Session;
use Purifier;

class FooterController extends Controller
{
    public function index(Request $request)
    {
        $lang = Language::where('code', $request->language)->firstOrFail();
        $data['lang_id'] = $lang->id;
        $data['abs'] = $lang->basic_setting;
        $data['abe'] = $lang->basic_extended;
        return view('admin.footer.logo-text', $data);
    }

    public function update(Request $request, $langid)
    {
        $img = $request->file('file');
        $allowedExts = array('jpg', 'png', 'jpeg');

        $rules = [
            'footer_text' => 'nullable|max:255',
            'copyright_text' => 'nullable',
            'file' => [
                function ($attribute, $value, $fail) use ($img, $allowedExts) {
                    if (!empty($img)) {
                        $ext = $img->getClientOriginalExtension();
                        if (!in_array($ext, $allowedExts)) {
                            return $fail(__('Only png, jpg, jpeg image is allowed'));
                        }
                    }
                },
            ],
            'useful_links_title' => 'nullable|max:50',
            'newsletter_title' => 'nullable|max:50'
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            $validator->getMessageBag()->add('error', 'true');
            return response()->json($validator->errors());
        }

        $bs = BS::where('language_id', $langid)->firstOrFail();

        if ($request->hasFile('file')) {
            $dir = public_path('assets/front/img/');
            @unlink($dir . $bs->footer_logo);
            $bs->footer_logo = Uploader::upload_picture($dir, $img);
        }

        $bs->footer_text = $request->footer_text;
        $bs->useful_links_title = $request->useful_links_title;
        $bs->contact_info_title = $request->contact_info_title;
        $bs->newsletter_title = $request->newsletter_title;
        $bs->newsletter_subtitle = $request->newsletter_subtitle;
        $bs->copyright_text = Purifier::clean($request->copyright_text);
        $bs->save();

        Session::flash('success', __('Updated Successfully'));
        return "success";
    }

    public function removeImage($language_id, Request $request)
    {
        $be = BS::where('language_id', $language_id)->firstOrFail();
        @unlink(public_path("assets/front/img/") . $be->footer_logo);
        $be->footer_logo = NULL;
        $be->save();

        Session::flash('success', __('Removed successfully'));
        return response()->json(['status' => 'success']);
    }
}
