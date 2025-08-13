@extends('admin.layout')

@if (!empty($process->language) && $process->language->rtl == 1)
  @section('styles')
    <link rel="stylesheet" href="{{ asset('assets/admin/css/rtl.css') }}">
  @endsection
@endif

@section('content')
  <div class="page-header">
    <h4 class="page-title">{{ __('Edit Work Process') }}</h4>
    <ul class="breadcrumbs">
      <li class="nav-home">
        <a href="{{ route('admin.dashboard') }}">
          <i class="flaticon-home"></i>
        </a>
      </li>

      <li class="separator">
        <i class="flaticon-right-arrow"></i>
      </li>
      <li class="nav-item">
        <a href="#">{{ __('Pages') }}</a>
      </li>
      <li class="separator">
        <i class="flaticon-right-arrow"></i>
      </li>
      <li class="nav-item">
        <a href="#">{{ __('Home Page') }}</a>
      </li>
      <li class="separator">
        <i class="flaticon-right-arrow"></i>
      </li>
      <li class="nav-item">
        <a
          href="{{ route('admin.process.index') . '?language=' . request()->input('language') }}">{{ __('Work Process') }}</a>
      </li>
      <li class="separator">
        <i class="flaticon-right-arrow"></i>
      </li>
      <li class="nav-item">
        <a href="#">{{ __('Edit Work Process') }}</a>
      </li>
    </ul>
  </div>
  <div class="row">
    <div class="col-md-12">
      <div class="card">
        <form id="ajaxForm" action="{{ route('admin.process.update') }}" method="post" enctype="multipart/form-data">
          <div class="card-header">
            <div class="card-title d-inline-block">{{ __('Edit Work Process') }}</div>
            <a class="btn btn-info btn-sm float-right d-inline-block"
              href="{{ route('admin.process.index') . '?language=' . request()->input('language') }}">
              <span class="btn-label">
                <i class="fas fa-backward"></i>
              </span>
              {{ __('Back') }}
            </a>
          </div>
          <div class="card-body pt-5 pb-5">
            <div class="row">
              <div class="col-lg-6 m-auto">
                @csrf
                <input type="hidden" name="process_id" value="{{ $process->id }}">
                <div class="row">
                  <div class="col-lg-12">
                    <div class="form-group">
                      <label for="">{{ __('Icon') }}<span class="text-danger">**</span></label>
                      <div class="btn-group d-block">
                        <button type="button" class="btn btn-primary iconpicker-component"><i
                            class="{{ $process->icon }}"></i></button>
                        <button type="button" class="icp icp-dd btn btn-primary dropdown-toggle" data-selected="fa-car"
                          data-toggle="dropdown">
                        </button>
                        <div class="dropdown-menu"></div>
                      </div>
                      <input id="inputIcon" type="hidden" name="icon" value="{{ $process->icon }}">
                      @if ($errors->has('icon'))
                        <p class="mb-0 text-danger">{{ $errors->first('icon') }}</p>
                      @endif
                      <div class="mt-2">
                        <small>{{ __('NB: click on the dropdown sign to select an icon.') }}</small>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="form-group">
                  <label for="">{{ __('Title') }}<span class="text-danger">**</span></label>
                  <input class="form-control" name="title" placeholder="{{ __('Enter title') }}"
                    value="{{ $process->title }}">
                  <p id="errtitle" class="mb-0 text-danger em"></p>
                </div>
                <div class="form-group">
                  <label for="">{{ __('Color') }}<span class="text-danger">**</span></label>
                  <input type="text" class="form-control jscolor" name="color" value="{{ $process->color }}">
                  <p id="errcolor" class="mb-0 text-danger em"></p>
                </div>
                <div class="form-group">
                  <label for="">{{ __('Text') }}<span class="text-danger">**</span></label>
                  <textarea name="text" id="" class="form-control">{{ $process->text }}</textarea>
                  <p id="errtext" class="mb-0 text-danger em"></p>
                </div>

                <div class="form-group">
                  <label for="">{{ __('Serial Number') }}<span class="text-danger">**</span></label>
                  <input type="number" class="form-control ltr" name="serial_number"
                    value="{{ $process->serial_number }}" placeholder="{{ __('Enter Serial Number') }}">
                  <p id="errserial_number" class="mb-0 text-danger em"></p>
                  <p class="text-warning">
                    <small>{{ __('The higher the serial number is, the later the process will be shown.') }}</small>
                  </p>

                </div>
              </div>
            </div>
          </div>
          <div class="card-footer pt-3">
            <div class="form">
              <div class="form-group from-show-notify row">
                <div class="col-12 text-center">
                  <button type="submit" id="submitBtn" class="btn btn-success">{{ __('Update') }}</button>
                </div>
              </div>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
@endsection
