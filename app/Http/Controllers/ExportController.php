<?php

namespace App\Http\Controllers;

use App\Helpers\ApiFormatter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ExportController extends Controller
{
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
        
        

        return $responseContent;
    }

    public function docx() {

    }
}
