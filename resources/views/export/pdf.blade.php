@extends('layouts.base')

@section('content')
    <table>
        <thead>
            <tr>
                <th>Tanggal</th>
                <th>Struk</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($dataBill as $bill)
                <tr>
                    <td>{{ Carbon::createFromFormat('Y-m-d H:i:s', $bill['date'])->format('j F Y') }}</td>
                    <td><img src="{{ $bill['base64_img'] }}" alt="" width="100"></td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection