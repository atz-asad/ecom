<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\User\AdditionalSection;
use App\Models\User\AdditionalSectionContent;
use App\Models\User\BasicSetting;
use App\Models\User\Language;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Purifier;
use Session;

class AdditionalSectionController extends Controller
{
    public function index(Request $request)
    {
        $lang = Language::where([['code', $request->language], ['user_id', Auth::guard('web')->user()->id]])->first();
        $information['langs'] = Language::where('user_id', Auth::guard('web')->user()->id)->get();

        $information['sections'] = AdditionalSection::join('user_additional_section_contents', 'user_additional_section_contents.addition_section_id', '=', 'user_additional_sections.id')
            ->where('user_additional_section_contents.language_id', $lang->id)
            ->where('user_additional_sections.page_type', 'home')
            ->select('user_additional_sections.*', 'user_additional_section_contents.section_name')
            ->get();

        return view('user.home.additional-section.index', $information);
    }

    public function create(Request $request)
    {
        $information['language'] = Language::where([['is_default', 1], ['user_id', Auth::guard('web')->user()->id]])->first();
        $information['languages'] = Language::where('user_id', Auth::guard('web')->user()->id)->get();
        $information['page_type'] = 'home';

        $data['ubs'] = BasicSetting::where('user_id', Auth::guard('web')->user()->id)->select('theme')->firstOrFail();
        $themeConfig = config('additional-section');
        $currentTheme = $data['ubs']->theme;
        $information['sections'] = $themeConfig[$currentTheme]['sections'] ?? [];


        return view('user.home.additional-section.create', $information);
    }

    public function store(Request $request)
    {
        $rules = [
            'position' => 'required',
            'page_type' => 'required',
            'serial_number' => 'required',
        ];
        $languages = Language::where('user_id', Auth::guard('web')->user()->id)->get();
        $messages = [];
        foreach ($languages as $language) {
            $code = $language->code;
            $langaugeName = ' ' . $language->name . ' ' . __('language');

            if (
                $language->is_default == 1 ||
                $request->input($code . '_section_name') ||
                $request->input($code . '_content')
            ) {
                $rules[$code . '_section_name'] = 'required|max:255';
                $rules[$code . '_content'] = 'required';
            }

            //messsge array
            $messages[$code . '_section_name.required'] = __('The title field is required for') . $langaugeName;
            $messages[$code . '_section_name.max'] = __('The title field cannot contain more than 255 characters for') . $langaugeName;
            $messages[$code . '_content.required'] = __('The content field is required for') . $langaugeName;
        }

        $validator = Validator::make($request->all(), $rules, $messages);
        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->getMessageBag()
            ], 400);
        }

        $languages = Language::where('user_id', Auth::guard('web')->user()->id)->get();
        $in = $request->all();
        $in['user_id'] = Auth::guard('web')->user()->id;
        $in['possition'] = $request->position;
        $section = AdditionalSection::create($in);


        foreach ($languages as $language) {
            $code = $language->code;
            if ($language->is_default == 1 || $request->filled($code . '_section_name') || $request->filled($code . '_content')) {
                $content = new AdditionalSectionContent();
                $content->language_id = $language->id;
                $content->addition_section_id = $section->id;
                $content->section_name = $request[$code . '_section_name'];
                $content->content = Purifier::clean($request[$code . '_content'], 'youtube');
                $content->save();
            }
        }

        $bs = BasicSetting::where('user_id', Auth::guard('web')->user()->id)->first();

        $arr = json_decode($bs->additional_section_status, true);
        $arr["$section->id"] = "1";

        $bs->additional_section_status = json_encode($arr);
        $bs->save();

        Session::flash('success', __('Created successfully'));
        return response()->json(['status' => 'success'], 200);
    }

    public function edit($id, Request $request)
    {
        $information['languages'] = Language::where('user_id', Auth::guard('web')->user()->id)->get();
        $information['language'] = Language::where('is_default', 1)->where('user_id', Auth::guard('web')->user()->id)->first();
        $information['selectedSection'] = AdditionalSection::where([['page_type', 'home'], ['user_id', Auth::guard('web')->user()->id], ['id', $id]])->firstOrFail();

        $data['ubs'] = BasicSetting::where('user_id', Auth::guard('web')->user()->id)->select('theme')->firstOrFail();
        $themeConfig = config('additional-section');
        $currentTheme = $data['ubs']->theme;
        $information['sections'] = $themeConfig[$currentTheme]['sections'] ?? [];

        return view('user.home.additional-section.edit', $information);
    }

    public function update(Request $request, $id)
    {
        $rules = [
            'position' => 'required',
            'page_type' => 'required',
            'serial_number' => 'required',
        ];
        $languages = Language::where('user_id', Auth::guard('web')->user()->id)->get();
        $messages = [];
        foreach ($languages as $language) {
            $code = $language->code;
            $langaugeName = ' ' . $language->name . ' ' . __('language');

            if (
                $language->is_default == 1 ||
                $request->input($code . '_section_name') ||
                $request->input($code . '_content')
            ) {
                $rules[$code . '_section_name'] = 'required|max:255';
                $rules[$code . '_content'] = 'required';
            }

            //messsge array
            $messages[$code . '_section_name.required'] = __('The title field is required for') . $langaugeName;
            $messages[$code . '_section_name.max'] = __('The title field cannot contain more than 255 characters for') . $langaugeName;
            $messages[$code . '_content.required'] = __('The content field is required for') . $langaugeName;
        }

        $validator = Validator::make($request->all(), $rules, $messages);
        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->getMessageBag()
            ], 400);
        }

        $section = AdditionalSection::where([
            ['id', $id],
            ['user_id', Auth::guard('web')->user()->id]
        ])->first();
        $section->possition = $request->position;
        $section->page_type = $request->page_type;
        $section->serial_number = $request->serial_number;
        $section->save();

        foreach ($languages as $language) {
            $content = AdditionalSectionContent::where('addition_section_id', $id)->where('language_id', $language->id)->first();
            if (empty($content)) {
                $content = new AdditionalSectionContent();
            }
            $code = $language->code;
            if ($language->is_default == 1 || $request->filled($code . '_section_name') || $request->filled($code . '_content')) {
                // Retrieve the content for the given section and language, or create a new one if it doesn't exist
                $content = AdditionalSectionContent::firstOrNew([
                    'addition_section_id' => $section->id,
                    'language_id' => $language->id
                ]);
                $content->section_name = $request[$code . '_section_name'];
                $content->content = Purifier::clean($request[$code . '_content'], 'youtube');
                $content->save();
            }
        }

        Session::flash('success', __('Updated Successfully'));

        return response()->json(['status' => 'success'], 200);
    }

    public function delete($id)
    {
        $section = AdditionalSection::where([['id', $id], ['user_id', Auth::guard('web')->user()->id]])->first();
        $contents = AdditionalSectionContent::where('addition_section_id', $id)->get();
        foreach ($contents as $content) {
            $content->delete();
        }
        $section->delete();
        return redirect()->back()->with('success', __('Deleted successfully'));
    }

    public function bulkdelete(Request $request)
    {
        $ids = $request->ids;

        foreach ($ids as $id) {
            $section = AdditionalSection::where([['id', $id], ['user_id', Auth::guard('web')->user()->id]])->first();

            $contents = AdditionalSectionContent::where('addition_section_id', $id)->get();

            foreach ($contents as $pageContent) {
                $pageContent->delete();
            }

            $section->delete();
        }
        Session::flash('success', __('Deleted successfully'));
        return "success";
    }
}
