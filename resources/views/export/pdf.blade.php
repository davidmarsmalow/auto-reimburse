@extends('layouts.base')

@section('content')
    <style>
        table {
            width: 100%;
        }

        th {
            padding: 10px;
            text-align: center;
            border: 1px solid;
        }

        td, th {
            border: 1px solid;
        }

        img {
            margin: 20px 20px 0 0;
        }
    </style>
    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Bill</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($dataBill as $key => $dateGroup)
                <tr>
                    <td>{{ Carbon::createFromFormat('Y-m-d', $key)->format('j F Y') }}</td>
                    <td>
                        @foreach ($dateGroup as $bill)
                            <img src="{{ $bill['base64_img'] }}" alt="" width="100">
                        @endforeach
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection