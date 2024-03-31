@extends('layouts.base')

@section('content')
    <h1>Insert Bill</h1>
    @if (session()->has('success'))
        <div class="row">
            <div class="alert alert-success my-3" role="alert">
                {{ session('success') }}
            </div>
        </div>
    @elseif (session()->has('failed'))
        <div class="row">
            <div class="alert alert-danger my-3" role="alert">
                {{ session('failed') }}
            </div>
        </div>
    @endif
    <form action="/" method="POST" class="row-6 flex-column" enctype="multipart/form-data">
        @csrf
        <div class="col-6 mb-3">
            <label for="datepicker" class="form-label">Date</label>
            <input type="text" name="date" id="datepicker">
        </div>
        <div class="col-6 mb-3">
            <label for="amount" class="form-label">Amount</label>
            <input type="number" name="amount" id="amount" class="form-control input">
        </div>
        <div class="col-6 mb-3">
            <label for="bill_type" class="form-label">Type</label>
            <select class="form-select" name="bill_type" id="bill_type">
                @foreach ($type as $value)
                    <option value="{{ $value->id }}">{{ $value->label }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-6 mb-3">
            <label for="formFile" class="form-label">Image</label>
            <input class="form-control" type="file" name="image" id="formFile" accept="image/jpg, image/png, image/jpeg">
          </div>
        <div class="col-6 mb-3">
            <input type="submit" name="submit" class="btn">
        </div>
    </form>

    <script>
        $("#datepicker").flatpickr({
            altInput: true,
            altFormat: "j F Y",
            dateFormat: "Y-m-d",
        });
    </script>
@endsection