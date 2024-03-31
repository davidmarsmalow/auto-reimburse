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

        .text-right {
            text-align: right;
        }

        .bold {
            font-weight: bolder;
        }

        .odd {
            background-color: #dedede;
        }
    </style>
    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Label</th>
                <th>Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($dataBill as $key => $bill)
                @php
                    $class = ($key % 2) ? '' : 'odd';
                @endphp
                <tr class="{{ $class }}">
                    <td>{{ $bill['date'] }}</td>
                    <td>{{ $bill['bill_type']['label'] }}</td>
                    <td>{{ number_format($bill['amount'], 2, ',', '.') }}</td>
                </tr>
            @endforeach
            <tr>
                <td colspan="2">Total</td>
                <td class="text-right bold">{{ number_format($total, 2, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    <br>
    
    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Bill</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($groupedBill as $key => $dateGroup)
                <tr>
                    <td>{{ $key }}</td>
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