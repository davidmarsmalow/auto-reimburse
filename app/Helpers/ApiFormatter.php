<?php
namespace App\Helpers;

class ApiFormatter{
	
	public static function responseData($response_code = null, $response_message = null, $data = null, $uuid = null, $status_code = null){

        $response = [
            'response_code'     => $response_code ?? '0000',
            'response_message'  => $response_message ?? 'Success',
            'trace_uid'         => $uuid ?? 'uuid',
            'data'              => $data,
        ];

        return response()->json($response, $status_code);
	}

}
