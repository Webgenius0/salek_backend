@extends('backend.app', ['title' => 'Update Info'])

@section('content')

<!--app-content open-->
<div class="app-content main-content mt-0">
    <div class="side-app">

        <!-- CONTAINER -->
        <div class="main-container container-fluid">

            <div class="page-header">
                <div>
                    <h1 class="page-title">Info</h1>
                </div>
                <div class="ms-auto pageheader-btn">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="javascript:void(0);">Info</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Update</li>
                    </ol>
                </div>
            </div>

            <div class="row" id="user-profile">
                <div class="col-lg-12">

                    <div class="tab-content">
                        <div class="tab-pane active show" id="editProfile">
                            <div class="card">
                                <div class="card-body border-0">
                                    <form class="form-horizontal" method="post" action="{{ route('info.update', $infos->id) }}">
                                        @csrf
                                        <div class="row mb-4">

                                            <div class="form-group">
                                                <label for="title" class="form-label">Title:</label>
                                                <input type="title" class="form-control @error('title') is-invalid @enderror" name="title" placeholder="Title" id="" value="{{ $infos->title }}">
                                                @error('title')
                                                <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>

                                            <div class="form-group">
                                                <label for="type" class="form-label">Select Type:</label>
                                                <select class="form-control @error('type') is-invalid @enderror" name="type" id="type">
                                                    <option value="">Choose a Type</option>
                                                    <option value="terms & conditions" {{ old('type', $infos->type) == 'terms & conditions' ? 'selected' : '' }}>Terms & Conditions</option>
                                                    <option value="privacy policy" {{ old('type', $infos->type) == 'privacy policy' ? 'selected' : '' }}>Privacy Policy</option>
                                                    <option value="about us" {{ old('type', $infos->type) == 'about us' ? 'selected' : '' }}>About Us</option>
                                                </select>
                                                @error('type')
                                                <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>

                                            <div class="form-group">
                                                <label for="description" class="form-label">Description:</label>
                                                <textarea class="form-control @error('description') is-invalid @enderror" name="description" placeholder="Description" id="" rows="3">{{ old('description', $infos->description) }}</textarea>
                                                @error('description')
                                                <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>

                                            <div class="form-group">
                                                <button class="btn btn-primary" type="submit">Submit</button>
                                            </div>

                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
<!-- CONTAINER CLOSED -->
@endsection
@push('scripts')
    
@endpush