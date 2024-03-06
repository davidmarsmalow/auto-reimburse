<?php

namespace App\Http\Controllers;

use App\Helpers\ApiFormatter;
use App\Models\Bill;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use thiagoalessio\TesseractOCR\TesseractOCR;

class BillController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
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

        $bills = Bill::where('date', '>=', $input['start_date'])
            ->where('date', '<=', Carbon::createFromFormat('Y-m-d', $input['end_date'])->addDay()) // jam 00:00 maka +1 supaya ambil data hari ini
            ->orderBy('date')
            ->get();

        $error_code = '0000';
        $message = 'Success';
        $status_code = 200;
        $data = $bills;

        return ApiFormatter::responseData($error_code, $message, $data, $uuid, $status_code);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validate the incoming request
        $input = $request->all();
        $validator = Validator::make($input, [
            'image' => 'required|image', // Validate that the uploaded file is an image
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

        // Perform OCR processing to extract date and amount from the image
        $extractedData = self::performOCR($input);

        if ($extractedData['error_code'] != '0000') {
            $status_code = 200;
            $error_code = $extractedData['error_code'];
            $message = $extractedData['message'];
            $data = $extractedData['data'];
            return ApiFormatter::responseData($error_code, $message, $data, $uuid, $status_code);
        }

        // Save the extracted data to the database
        try {
            DB::beginTransaction();

            $bill = new Bill([
                'date' => $extractedData['datetime'],
                'amount' => $extractedData['amount'],
                'image_path' => $input['image']->store('bills'), // Store the image in a storage path
            ]);
            $bill->save();

            $error_code = '0000';
            $message = 'Success';
            $status_code = 200;
            $data = $extractedData;

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();

            $error_code = '0002';
            $message = $th->getMessage();
            $status_code = 200;
            $data = [];
        }

        return ApiFormatter::responseData($error_code, $message, $data, $uuid, $status_code);
    }

    /**
     * Display the specified resource.
     */
    public function show(Bill $bill)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Bill $bill)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Bill $bill)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Bill $bill)
    {
        //
    }

    private function performOCR($input)
    {
        $return = [];
        $text = (new TesseractOCR($input['image']))->run();

        $parts = explode("\n", $text);
        $parts = array_values(array_filter($parts));

        if ($parts[1] == 'QR a' || $parts[1] == 'QR 0') { // QR BCA Mobile
            $rawDate = Carbon::createFromFormat('d/m H:i:s', $parts[3]); // 03/11 19:43:01
            $amount = (float) preg_replace('/[^\d]/', '', substr($parts[5], 3)); // Rp 15.000
            $error_code = '0000';
            $message = 'Success';
        } elseif ($parts[0] == 'xs') { // QR Gopay
            $rawDateOnly = substr($parts[7], -10); // 3 Nov 2023
            $rawTime = substr($parts[6], -7); // 9:24 PM
            $rawDate = Carbon::createFromFormat('j M Y g:i A', $rawDateOnly . $rawTime);
            $amount = (float) preg_replace('/[^\d]/', '', substr($parts[1], 2)); // Rp33.500
            $error_code = '0000';
            $message = 'Success';
        } elseif ($parts[0] == 'Pembayaran QRIS Berhasil') { // QR myBca
            $rawDate = Carbon::createFromFormat('j M Y H:i:s', $parts[4]); // 21 Feb 2024 13:35:33
            $amount = (float) preg_replace('/[^\d]/', '', substr($parts[1], 4, -3)); // IDR 20,000.00
            $error_code = '0000';
            $message = 'Success';
        } elseif ($parts[1] == 'Anda baru saja melakukan transaksi dengan menggunakan fasilitas myBCA.') { // QR myBCA Email
            $rawDate = Carbon::createFromFormat('d M Y H:i:s', Str::substr($parts[4], -20)); // Tanggal Transaksi : 05 Mar 2024 18:00:16
            $amount = (float) preg_replace('/[^\d]/', '', Str::between($parts[13], 'Total Bayar : IDR ', '.00')); // : IDR 20,000.00
            $error_code = '0000';
            $message = 'Success';
        } else {
            $rawDate = Carbon::now();
            $amount = 0;
            $error_code = '0003';
            $message = 'Invalid: Unknown Format';
            $return['data'] = $parts;
        }

        $date = $rawDate->format('Y-m-d H:i:s');

        $return['error_code']   = $error_code;
        $return['message']      = $message;
        $return['datetime']     = $date;
        $return['amount']       = $amount;

        return $return;
    }
}
