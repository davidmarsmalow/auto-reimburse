<?php

namespace App\Http\Controllers;

use App\Helpers\ApiFormatter;
use App\Models\Bill;
use App\Models\BillType;
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

        $bills = Bill::with('billType')
            ->where('date', '>=', $input['start_date'])
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
        $type = BillType::get();

        return view('bills.create', [
            'type' => $type,
        ]);
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

        $extractedData['image'] = $input['image'];
        $saveBill = self::save($extractedData);

        if ($saveBill === TRUE) {
            $error_code = '0000';
            $message = 'Success';
            $status_code = 200;
            $data = $extractedData;
        } else {
            $error_code = '0002';
            $message = $saveBill;
            $status_code = 200;
            $data = [];
        }

        return ApiFormatter::responseData($error_code, $message, $data, $uuid, $status_code);
    }

    public function storeBill(Request $request)
    {
        $validatedData = $request->validate([
            'date'      => 'required|date|date_format:Y-m-d',
            'amount'    => 'required|numeric',
            'bill_type' => 'required|integer|exists:App\Models\BillType,id',
            'image'     => 'required|image', // Validate that the uploaded file is an image
        ]);

        $validatedData['datetime'] = $validatedData['date'];
        $validatedData['type'] = $validatedData['bill_type'];

        $saveBill = self::save($validatedData);

        if ($saveBill === TRUE) {
            return redirect('/')->with('success', 'Success');
        } else {
            return redirect('/')->with('failed', $saveBill);
        }
    }

    public function save($param) {
        // Save the extracted data to the database
        try {
            DB::beginTransaction();

            $bill = new Bill([
                'date' => $param['datetime'],
                'amount' => $param['amount'],
                'type' => $param['type'],
                'image_path' => $param['image']->store('bills'), // Store the image in a storage path
            ]);
            $bill->save();

            DB::commit();
            return TRUE;
        } catch (\Throwable $th) {
            DB::rollBack();
            return $th->getMessage();
        }
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
            $type = 1;
            $error_code = '0000';
            $message = 'Success';
        } elseif ($parts[0] == 'xs') { // QR Gopay
            $rawDateOnly = substr($parts[7], -10); // 3 Nov 2023
            $rawTime = substr($parts[6], -7); // 9:24 PM
            $rawDate = Carbon::createFromFormat('j M Y g:i A', $rawDateOnly . $rawTime);
            $amount = (float) preg_replace('/[^\d]/', '', substr($parts[1], 2)); // Rp33.500
            $type = 1;
            $error_code = '0000';
            $message = 'Success';
        } elseif ($parts[0] == 'Pembayaran QRIS Berhasil') { // QR myBca
            $rawDate = Carbon::createFromFormat('j M Y H:i:s', $parts[4]); // 21 Feb 2024 13:35:33
            $amount = (float) preg_replace('/[^\d]/', '', substr($parts[1], 4, -3)); // IDR 20,000.00
            $type = 1;
            $error_code = '0000';
            $message = 'Success';
        } elseif ($parts[1] == 'Anda baru saja melakukan transaksi dengan menggunakan fasilitas myBCA.') { // QR myBCA Email
            $parts[4] = Str::replace('Mei', 'May', $parts[4]);
            $rawDate = Carbon::createFromFormat('d M Y H:i:s', Str::substr($parts[4], -20)); // Tanggal Transaksi : 05 Mar 2024 18:00:16
            $amount = (float) preg_replace('/[^\d]/', '', Str::between($parts[13], 'Total Bayar : IDR ', '.00')); // : IDR 20,000.00
            $type = 1;
            $error_code = '0000';
            $message = 'Success';
        } elseif (Str::startsWith($parts[3], 'Transaction Detail')) { // Gopay History
            $rawDateOnly = Str::after($parts[7], 'Date '); // Date 20 Mar 2024
            $rawTime = Str::after($parts[6], 'Time '); // Time 05:59 PM
            $rawDate = Carbon::createFromFormat('j M Y g:i A', $rawDateOnly . $rawTime);
            $amount = (float) preg_replace('/[^\d]/', '', Str::after($parts[1], 'Rp')); // Rp33.500
            $orderId = Str::after($parts[9], 'Order ID ');
            
            if (Str::startsWith($orderId, 'RB')) { // Order ID RB-172760-0842018 ![) // Order ID RB-146012-0942697 [Tj
                $type = 2; // Transport
                $error_code = '0000';
                $message = 'Success';
            } elseif (Str::startsWith($orderId, 'mbrs')) { // Order ID mbrs--9fd640f6-05f1-4... [Fj
                $type = 4; // Park
                $error_code = '0000';
                $message = 'Success';
            } else {
                $type = 0;
                $error_code = '0003';
                $message = 'Invalid: Unknown Type';
                $return['data'] = $parts;
            }
        } elseif (Str::contains($parts[0], 'Jago')) {
            $rawDate = Carbon::createFromFormat('d M Y H:i T', Str::after($parts[8], 'Transaction Date ')); // Transaction Date 18 May 2024 11:47 WIB
            $amount = (float) preg_replace('/[^\d]/', '', Str::after($parts[7], 'Amount Rp')); // Amount Rp32.250
            $type = 1;
            $error_code = '0000';
            $message = 'Success';
        } elseif (Str::contains($parts[1], 'Jago')) {
            $rawDate = Carbon::createFromFormat('d M Y H:i T', $parts[12]); // 08 August 2024 10:19 WIB
            $amount = (float) preg_replace('/[^\d]/', '', Str::after($parts[11], 'Rp')); // Rp32.250
            $type = 1;
            $error_code = '0000';
            $message = 'Success';
        } else {
            $rawDate = Carbon::now();
            $amount = 0;
            $type = 0;
            $error_code = '0003';
            $message = 'Invalid: Unknown Format';
            $return['data'] = $parts;
        }

        $date = $rawDate->format('Y-m-d H:i:s');

        $return['error_code']   = $error_code;
        $return['message']      = $message;
        $return['datetime']     = $date;
        $return['amount']       = $amount;
        $return['type']         = $type;

        return $return;
    }
}
