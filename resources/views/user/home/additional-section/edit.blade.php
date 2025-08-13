@extends('user.layout')

@section('content')
  <div class="page-header">
    <h4 class="page-title">{{ __('Edit Section') }}</h4>
    <ul class="breadcrumbs">
      <li class="nav-home">
        <a href="{{ route('admin.dashboard') }}">
          <i class="flaticon-home"></i>
        </a>
      </li>
      <li class="separator"><i class="flaticon-right-arrow"></i></li>
      <li class="nav-item"><a href="#">{{ __('Pages') }}</a></li>
      <li class="separator"><i class="flaticon-right-arrow"></i></li>
      <li class="nav-item"><a href="#">{{ __('Home Page') }}</a></li>
      <li class="separator"><i class="flaticon-right-arrow"></i></li>
      <li class="nav-item"><a href="#">{{ __('Additional Sections') }}</a></li>
      <li class="separator"><i class="flaticon-right-arrow"></i></li>
      <li class="nav-item"><a href="#">{{ __('Edit Section') }}</a></li>
    </ul>
  </div>

  <div class="row">
    <div class="col-md-12">
      <div class="card">
        <div class="card-header">
          <div class="card-title d-inline-block">{{ __('Edit Section') }}</div>
          <a class="btn btn-info btn-sm float-right d-inline-block"
            href="{{ route('admin.additional_sections', ['language' => $language->code]) }}">
            <span class="btn-label"><i class="fas fa-backward"></i></span>
            {{ __('Back') }}
          </a>
        </div>

        <div class="card-body">
          <div class="row">
            <div class="col-lg-8 m-auto">
              <div class="alert alert-danger pb-1 dis-none" id="pageErrors">
                <button type="button" class="close" data-dismiss="alert">×</button>
                <ul></ul>
              </div>

              <form id="pageForm" action="{{ route('user.additional_section.update', $selectedSection->id) }}" method="POST">
                @csrf
                <input type="hidden" name="page_type" value="home">

                <div class="row">
                  <div class="col-lg-6">
                    <div class="form-group p-0">
                      <label>{{ __('Position') }} <span class="text-danger">**</span></label>
                      <select name="position" class="form-control select2">
                        <option selected disabled>{{ __('Select a Section') }}</option>
                        <option @selected($selectedSection->possition == 'hero_section') value="hero_section">
                          {{ __('After Hero Section') }}</option>

                        @foreach ($sections as $section)
                          @php
                            //label for the section
                            if ($section == 'tab_section') {
                                $label = __('Tabs Section');
                            } elseif ($section == 'cta_section_status') {
                                $label = __('Call To Action Section');
                            } elseif ($section == 'categoryProduct_section') {
                                $label = __('Category Product Section');
                            } else {
                                $label = __(ucwords(str_replace('_', ' ', $section)));
                            }
                          @endphp
                          <option value="{{ $section }}" @selected($selectedSection->possition == $section)>
                            {{ __('After') }}
                            {{ $label }}
                          </option>
                        @endforeach
                        {{-- <option selected disabled>{{ __('Select a Section') }}</option>
                        @php
                          $positions = [
                            'hero_section' => 'After Hero Section',
                            'partner_section' => 'After Partner Section',
                            'work_process_section' => 'After Work Process Section',
                            'template_section' => 'After Featured Template Section',
                            'features_section' => 'After Features Section',
                            'pricing_section' => 'After Pricing Section',
                            'featured_shop_section' => 'After Featured Shop Section',
                            'testimonial_section' => 'After Testimonial Section',
                            'blog_section' => 'After Blog Section',
                          ];
                        @endphp
                        @foreach ($positions as $key => $label)
                          <option value="{{ $key }}" @selected($section->position == $key)>{{ __($label) }}</option>
                        @endforeach --}}
                      </select>
                    </div>
                  </div>

                  <div class="col-lg-6">
                    <div class="form-group p-0">
                      <label>{{ __('Serial Number') }} <span class="text-danger">**</span></label>
                      <input type="number" name="serial_number" class="form-control" value="{{ $selectedSection->serial_number }}">
                      <p class="text-warning">
                        {{ __('The higher the serial number is, the later the section will be shown.') }}
                      </p>
                    </div>
                  </div>
                </div>

                <div id="accordion" class="mt-3">
                  @foreach ($languages as $language)
                    <div class="version">
                      <div class="version-header" id="heading{{ $language->id }}">
                        <h5 class="mb-0">
                          <button type="button"
                            class="btn btn-link {{ $language->direction == 1 ? 'rtl text-right' : '' }}"
                            data-toggle="collapse"
                            data-target="#collapse{{ $language->id }}"
                            aria-expanded="{{ $language->is_default == 1 ? 'true' : 'false' }}"
                            aria-controls="collapse{{ $language->id }}">
                            {{ $language->name . ' ' . __('Language') }} {{ $language->is_default ? '(Default)' : '' }}
                          </button>
                        </h5>
                      </div>

                      @php
                        $content = \App\Models\User\AdditionalSectionContent::where('addition_section_id', $selectedSection->id)
                          ->where('language_id', $language->id)
                          ->first();
                      @endphp

                      <div id="collapse{{ $language->id }}"
                        class="collapse {{ $language->is_default ? 'show' : '' }}"
                        aria-labelledby="heading{{ $language->id }}"
                        data-parent="#accordion">
                        <div class="version-body">
                          <div class="form-group {{ $language->direction == 1 ? 'rtl text-right' : '' }}">
                            <label>{{ __('Name') }} <span class="text-danger">**</span></label>
                            <input type="text" class="form-control"
                              name="{{ $language->code }}_section_name"
                              placeholder="{{ __('Enter section name') }}"
                              value="{{ @$content->section_name }}">
                          </div>

                          <div class="form-group {{ $language->direction == 1 ? 'rtl text-right' : '' }}">
                            <label>{{ __('Content') }} <span class="text-danger">**</span></label>
                            <textarea class="form-control summernote"
                              name="{{ $language->code }}_content"
                              id="content-{{ $language->id }}"
                              data-height="300">{{ @$content->content }}</textarea>
                          </div>

                          <div class="form-group">
                            @php $currLang = $language; @endphp
                            @foreach ($languages as $lang)
                              @continue($lang->id == $currLang->id)
                              <div class="form-check py-0">
                                <label class="form-check-label">
                                  <input class="form-check-input" type="checkbox"
                                    onchange="cloneInput('collapse{{ $currLang->id }}', 'collapse{{ $lang->id }}', event)">
                                  <span class="form-check-sign">{{ __('Clone for') }}
                                    <strong class="text-capitalize text-secondary">{{ $lang->name }}</strong>
                                    {{ __('language') }}
                                  </span>
                                </label>
                              </div>
                            @endforeach
                          </div>
                        </div>
                      </div>
                    </div>
                  @endforeach
                </div>
              </form>
            </div>
          </div>
        </div>

        <div class="card-footer">
          <div class="text-center">
            <button type="submit" form="pageForm" class="btn btn-success">
              {{ __('Save') }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
@endsection

@section('scripts')
  <script src="{{ asset('assets/admin/js/addition-page.js') }}"></script>
@endsection
