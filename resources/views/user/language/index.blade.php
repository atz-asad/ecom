@extends('user.layout')

@section('content')
  <div class="page-header">
    <h4 class="page-title">{{ __('Languages') }}</h4>
    <ul class="breadcrumbs">
      <li class="nav-home">
        <a href="{{ route('user-dashboard') }}">
          <i class="flaticon-home"></i>
        </a>
      </li>
      <li class="separator">
        <i class="flaticon-right-arrow"></i>
      </li>
      <li class="nav-item">
        <a href="#">{{ __('Site Settings') }}</a>
      </li>
      <li class="separator">
        <i class="flaticon-right-arrow"></i>
      </li>
      <li class="nav-item">
        <a href="#">{{ __('Languages') }}</a>
      </li>
    </ul>
  </div>
  <div class="row">
    <div class="col-md-12">
      <div class="card">
        <div class="card-header">
          <div class="row">
            <div class="col-md-6">
              <div class="card-title d-inline-block">{{ __('Languages') }}</div>
            </div>
            <div class="col-md-6">
              <div class="d-flex justify-content-end gap-3">
                <a href="#" class="btn btn-primary float-right btn-sm" data-toggle="modal"
                  data-target="#createModal">
                  <i class="fas fa-plus"></i>
                  {{ __('Add Language') }}
                </a>

                <a href="#" class="btn btn-secondary float-right btn-sm mr-1 editBtn" data-toggle="modal"
                  data-target="#addModal">
                  <span class="btn-label">
                    <i class="fas fa-plus"></i>
                  </span>
                  {{ __('Add New Keyword') }}
                </a>
              </div>
            </div>
          </div>

        </div>

        <div class="card-body">
          <div class="row">
            <div class="col-lg-12">
              @if (count($languages) == 0)
                <h3 class="text-center">{{ __('NO LANGUAGE FOUND') }}</h3>
              @else
                <div class="table-responsive">
                  <table class="table table-striped mt-3">
                    <thead>
                      <tr>
                        <th scope="col">{{ __('Name') }}</th>
                        <th scope="col">{{ __('Code') }}</th>
                        <th scope="col">{{ __('Default in Website') }}</th>
                        <th scope="col">{{ __('Default in Dashboard') }}</th>
                        <th scope="col">{{ __('Actions') }}</th>
                      </tr>
                    </thead>
                    <tbody>
                      @foreach ($languages as $key => $language)
                        <tr>
                          <td>{{ $language->name }}</td>
                          <td>{{ $language->code }}</td>
                          <td>
                            @if ($language->is_default == 1)
                              <strong class="badge badge-success">{{ __('Default') }}</strong>
                            @else
                              <form class="d-inline-block" action="{{ route('user.language.default', $language->id) }}"
                                method="post">
                                @csrf
                                <button class="btn btn-primary btn-sm" type="submit"
                                  name="button">{{ __('Make Default') }}</button>
                              </form>
                            @endif
                          </td>
                          <td>
                            @if ($language->dashboard_default == 1)
                              <strong class="badge badge-success">{{ __('Default') }}</strong>
                            @else
                              <form class="d-inline-block"
                                action="{{ route('user.language.dashboardDefault', $language->id) }}" method="post">
                                @csrf
                                <button class="btn btn-primary btn-sm" type="submit"
                                  name="button">{{ __('Make Default') }}</button>
                              </form>
                            @endif
                          </td>
                          <td>
                            <a class="btn btn-secondary btn-sm"
                              href="{{ route('user.language.editKeyword', $language->id) }}">
                              <span class="btn-label">
                                <i class="fas fa-edit"></i>
                              </span>
                              {{ __('Edit Keyword') }}
                            </a>
                            <a class="btn btn-info btn-sm" href="{{ route('user.language.edit', $language->id) }}">
                              <span class="btn-label">
                                <i class="fas fa-edit"></i>
                              </span>
                            </a>
                            <form class="deleteform d-inline-block"
                              action="{{ route('user.language.delete', $language->id) }}" method="post">
                              @csrf
                              <button type="submit" class="btn btn-danger btn-sm deletebtn">
                                <span class="btn-label">
                                  <i class="fas fa-trash"></i>
                                </span>
                              </button>
                            </form>
                          </td>
                        </tr>
                      @endforeach
                    </tbody>
                  </table>
                </div>
              @endif
            </div>
          </div>
        </div>

      </div>
    </div>
  </div>

  {{-- language keyword modal start --}}
  <div class="modal fade" id="addModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLongTitle">{{ __('Add Keyword') }}</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>

        <div class="modal-body">
          <form id="ajaxForm2" action="{{ route('user.language.add_keyword') }}" method="POST">
            @csrf
            <div class="form-group">
              <label for="">{{ __('Keyword') }} <span class="text-danger">**</span></label>
              <input type="text" class="form-control" name="keyword" placeholder="{{ __('Enter Keyword') }}">
              <p id="errkeyword" class="mt-1 mb-0 text-danger em"></p>
            </div>
          </form>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">
            {{ __('Close') }}
          </button>
          <button id="submitBtn2" type="button" class="btn btn-primary btn-sm">
            {{ __('Submit') }}
          </button>
        </div>
      </div>
    </div>
  </div>
  {{-- language keyword modal start end --}}

  <!-- Create Language Modal -->
  @includeif('user.language.create')
@endsection
