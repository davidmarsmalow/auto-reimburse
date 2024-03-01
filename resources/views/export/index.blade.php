@extends('layouts.base')

@section('content')
    <h1>Export</h1>
    <form action="/export" method="POST" class="row-6 flex-column">
        @csrf
        <div class="col-6 mb-3">
            Date Start
            <input type="text" name="start_date" id="datepickerStart">
        </div>
        <div class="col-6 mb-3">
            Date End
            <input type="text" name="end_date" id="datepickerEnd">
        </div>
        <div class="col-6 mb-3">
            <input type="submit" name="submit" class="btn">
        </div>
    </form>

    <script>
        $("#datepickerStart").flatpickr({
            altInput: true,
            altFormat: "j F Y",
            dateFormat: "Y-m-d",
        });
        $("#datepickerEnd").flatpickr({
            altInput: true,
            altFormat: "j F Y",
            dateFormat: "Y-m-d",
        });
    </script>
@endsection