<?php

namespace App\Http\Controllers;

use App\Helpers\ApiFormatter;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ExportController extends Controller
{
    public function index() {
        return view('export.index', []);
    }

    public function generate(Request $request) {
        $validatedData = $request->validate([
            'start_date'    => 'required|date|date_format:Y-m-d',
            'end_date'      => 'required|date|date_format:Y-m-d|after_or_equal:start_date',
        ]);

        $url = route('bills.index', [
            'start_date'    => $validatedData['start_date'],
            'end_date'      => $validatedData['end_date'],
        ]);
        $getBills = Request::create($url, 'GET');
        $response = Route::dispatch($getBills);
        $responseContent = $response->getContent();

        $dataBill = json_decode($responseContent, true);
        
        foreach ($dataBill['data'] as $key => $bill) {
            $dataBill['data'][$key]['date'] = Carbon::createFromFormat('Y-m-d H:i:s', $bill['date'])->format('j F Y');
            $dataBill['data'][$key]['base64_img'] = 'data:image/png;base64,' . base64_encode(Storage::get($bill['image_path']));
        }
        
        $group = '';
        $result = [];
        foreach ($dataBill['data'] as $key => $value) {
            $group_db = $value['date'];
            if ($group != $group_db) {
                $group = $group_db;
            }
            $result[$group][] = $value;
        }

        $pdf = Pdf::loadView('export.pdf', ['dataBill' => $result]);

        return $pdf->stream(Carbon::now() . '.pdf');
    }

    public function pdf(Request $request) {
        $input = $request->all();

        $validator = Validator::make($input, [
            'start_date'    => 'required|date|date_format:Y-m-d',
            'end_date'      => 'required|date|date_format:Y-m-d|after_or_equal:start_date',
        ]);

        $uuid = Str::uuid()->toString();

        $errors = $validator->errors();
        if ($validator->fails()) {
            foreach ($errors->all() as $val) {
                $status_code = 200;
                $error_code = '0001';
                $message = $val;
                $data = [];
                return ApiFormatter::responseData($error_code, $message, $data, $uuid, $status_code);
            }
        }

        $url = route('bills.index', [
            'start_date'    => $input['start_date'],
            'end_date'      => $input['end_date'],
        ]);
        $getBills = Request::create($url, 'GET');
        $response = Route::dispatch($getBills);
        $responseContent = $response->getContent();

        $dataBill = json_decode($responseContent, true);
        
        $pdf = Pdf::loadView('pdf', ['dataBill' => $dataBill['data']]);
        return $pdf->download('test.pdf');

        // return $responseContent;
    }

    public function docx() {

    }
}
