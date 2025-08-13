@extends('admin.layout')

@if (!empty($la) && $la->rtl == 1)
  @section('styles')
    <link rel="stylesheet" href="{{ asset('assets/admin/css/rtl.css') }}">
  @endsection
@endif

@if (empty($la) && $be->default_language_direction == 'rtl')
  @section('styles')
    <link rel="stylesheet" href="{{ asset('assets/admin/css/rtl.css') }}">
  @endsection
@endif

@section('content')
  <div class="page-header">
    <h4 class="page-title">{{ __('Edit Admin Dashboard Keywords') }}</h4>
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
        <a href="#">{{ __('Settings') }}</a>
      </li>
      <li class="separator">
        <i class="flaticon-right-arrow"></i>
      </li>
      <li class="nav-item">
        <a href="{{ route('admin.language.index') }}">{{ __('Languages') }}</a>
      </li>
      <li class="separator">
        <i class="flaticon-right-arrow"></i>
      </li>
      <li class="nav-item">
        <a href="#">{{ __('Edit Admin Dashboard Keywords') }}</a>
      </li>
    </ul>
  </div>
  <div class="row">
    <div class="col-md-12">
      <div class="card">
        <div class="card-header">
          <div class="card-title d-inline-block">{{ __('Edit Admin Dashboard Keywords') }}</div>
          <a class="btn btn-info btn-sm float-right d-inline-block" href="{{ route('admin.language.index') }}">
            <span class="btn-label">
              <i class="fas fa-backward"></i>
            </span>
            {{ __('Back') }}
          </a>
        </div>
        <div class="card-body pt-5 pb-5" id="app">
          <div class="row">
            <div class="col-lg-12">
              @if (!is_null($json))
                <form method="post"
                  action="{{ !empty($la) ? route('admin.language.admin_dashboard.updateKeyword', $la->id) : route('admin.language.admin_dashboard.updateKeyword', 0) }}"
                  id="langForm">
                  {{ csrf_field() }}

                  <div class="row">
                    <div class="col-md-4 mt-2" v-for="(value, key) in datas" :key="key">
                      <div class="form-group">
                        <label class="control-label">@{{ key.replace(/_/g, ' ') }}</label>
                        <div class="input-group">
                          <input type="text" :value="value" :name="'keys[' + key + ']'"
                            class="form-control form-control-lg">
                        </div>
                      </div>
                    </div>
                  </div>

                </form>
                @else
                <h4 class="text-center text-danger">{{ __('File does not exist. Please provide a valid language code.') }}</h4>
              @endif
            </div>
          </div>
        </div>
        @if (!is_null($json))
          <div class="card-footer">
            <div class="form">
              <div class="form-group from-show-notify row">
                <div class="col-12 text-center">
                  <button id="langBtn" type="button" class="btn btn-success">{{ __('Update') }}</button>
                </div>
              </div>
            </div>
          </div>
        @endif
      </div>

    </div>
  </div>
@endsection

@section('vuescripts')
  <script>
    "use strict";
    window.app = new Vue({
      el: '#app',
      data: {
        datas: {!! json_encode($json) !!},
      }
    })
  </script>
@endsection
