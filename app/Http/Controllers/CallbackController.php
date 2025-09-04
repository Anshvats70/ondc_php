<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CallbackController extends Controller
{
    /**
     * Handle the on_search callback
     */
    public function on_search(Request $request)
    {
        // Log the incoming request
        $logData = [
            'timestamp' => date('Y-m-d H:i:s'),
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'headers' => $request->headers->all(),
            'body' => $request->all(),
            'raw_body' => $request->getContent()
        ];
        
        // Write to Laravel log
        Log::info('ONDC on_search callback received', $logData);
        
        // Also write to custom log file
        $logMessage = "[" . date('Y-m-d H:i:s') . "] on_search callback\n";
        $logMessage .= "Method: " . $request->method() . "\n";
        $logMessage .= "IP: " . $request->ip() . "\n";
        $logMessage .= "Data: " . json_encode($request->all()) . "\n";
        $logMessage .= "Raw Body: " . $request->getContent() . "\n";
        $logMessage .= str_repeat('-', 80) . "\n";
        
        file_put_contents(storage_path('logs/on_search.log'), $logMessage, FILE_APPEND | LOCK_EX);
        
        // Your business logic goes here
        // Add any processing logic here
        
        // Prepare response data
        $responseData = [
            'message' => 'on_search callback received successfully',
            'timestamp' => date('Y-m-d H:i:s'),
            'data' => $request->all(),
            'headers' => $request->headers->all()
        ];
        
        // Log the response as well
        Log::info('ONDC on_search response sent', ['response' => $responseData]);
        
        // Return proper Laravel JSON response
        return new \Illuminate\Http\JsonResponse($responseData);
    }

    /**
     * Handle the on_select callback
     */
    public function on_select(Request $request)
    {
        // Log the incoming request
        $logData = [
            'timestamp' => date('Y-m-d H:i:s'),
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'headers' => $request->headers->all(),
            'body' => $request->all(),
            'raw_body' => $request->getContent()
        ];
        
        // Write to Laravel log
        Log::info('ONDC on_select callback received', $logData);
        
        // Also write to custom log file
        $logMessage = "[" . date('Y-m-d H:i:s') . "] on_select callback\n";
        $logMessage .= "Method: " . $request->method() . "\n";
        $logMessage .= "IP: " . $request->ip() . "\n";
        $logMessage .= "Data: " . json_encode($request->all()) . "\n";
        $logMessage .= "Raw Body: " . $request->getContent() . "\n";
        $logMessage .= str_repeat('-', 80) . "\n";
        
        file_put_contents(storage_path('logs/on_select.log'), $logMessage, FILE_APPEND | LOCK_EX);
        
        // Your business logic goes here
        // Add any processing logic here
        
        // Prepare response data
        $responseData = [
            'message' => 'on_select callback received successfully',
            'timestamp' => date('Y-m-d H:i:s'),
            'method' => $request->method(),
            'data' => $request->all(),
            'headers' => $request->headers->all()
        ];
        
        // Log the response as well
        Log::info('ONDC on_select response sent', ['response' => $responseData]);
        
        // Return proper Laravel JSON response
        return new \Illuminate\Http\JsonResponse($responseData);
    }

    /**
     * Handle the on_init callback
     */
    public function on_init(Request $request)
    {
        // Log the incoming request
        $logData = [
            'timestamp' => date('Y-m-d H:i:s'),
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'headers' => $request->headers->all(),
            'body' => $request->all(),
            'raw_body' => $request->getContent()
        ];
        
        // Write to Laravel log
        Log::info('ONDC on_init callback received', $logData);
        
        // Also write to custom log file
        $logMessage = "[" . date('Y-m-d H:i:s') . "] on_init callback\n";
        $logMessage .= "Method: " . $request->method() . "\n";
        $logMessage .= "IP: " . $request->ip() . "\n";
        $logMessage .= "Data: " . json_encode($request->all()) . "\n";
        $logMessage .= "Raw Body: " . $request->getContent() . "\n";
        $logMessage .= str_repeat('-', 80) . "\n";
        
        file_put_contents(storage_path('logs/on_init.log'), $logMessage, FILE_APPEND | LOCK_EX);
        
        // Your business logic goes here
        // Add any processing logic here
        
        // Prepare response data
        $responseData = [
            'message' => 'on_init callback received successfully',
            'timestamp' => date('Y-m-d H:i:s'),
            'method' => $request->method(),
            'data' => $request->all(),
            'headers' => $request->headers->all()
        ];
        
        // Log the response as well
        Log::info('ONDC on_init response sent', ['response' => $responseData]);
        
        // Return proper Laravel JSON response
        return new \Illuminate\Http\JsonResponse($responseData);
    }

    /**
     * Handle the on_update callback
     */
    public function on_update(Request $request)
    {
        // Log the incoming request
        $logData = [
            'timestamp' => date('Y-m-d H:i:s'),
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'headers' => $request->headers->all(),
            'body' => $request->all(),
            'raw_body' => $request->getContent()
        ];
        
        // Write to Laravel log
        Log::info('ONDC on_update callback received', $logData);
        
        // Also write to custom log file
        $logMessage = "[" . date('Y-m-d H:i:s') . "] on_update callback\n";
        $logMessage .= "Method: " . $request->method() . "\n";
        $logMessage .= "IP: " . $request->ip() . "\n";
        $logMessage .= "Data: " . json_encode($request->all()) . "\n";
        $logMessage .= "Raw Body: " . $request->getContent() . "\n";
        $logMessage .= str_repeat('-', 80) . "\n";
        
        file_put_contents(storage_path('logs/on_update.log'), $logMessage, FILE_APPEND | LOCK_EX);
        
        // Your business logic goes here
        // Add any processing logic here
        
        // Prepare response data
        $responseData = [
            'message' => 'on_update callback received successfully',
            'timestamp' => date('Y-m-d H:i:s'),
            'method' => $request->method(),
            'data' => $request->all(),
            'headers' => $request->headers->all()
        ];
        
        // Log the response as well
        Log::info('ONDC on_update response sent', ['response' => $responseData]);
        
        // Return proper Laravel JSON response
        return new \Illuminate\Http\JsonResponse($responseData);
    }

    /**
     * Handle the on_cancel callback
     */
    public function on_cancel(Request $request)
    {
        // Log the incoming request
        $logData = [
            'timestamp' => date('Y-m-d H:i:s'),
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'headers' => $request->headers->all(),
            'body' => $request->all(),
            'raw_body' => $request->getContent()
        ];
        
        // Write to Laravel log
        Log::info('ONDC on_cancel callback received', $logData);
        
        // Also write to custom log file
        $logMessage = "[" . date('Y-m-d H:i:s') . "] on_cancel callback\n";
        $logMessage .= "Method: " . $request->method() . "\n";
        $logMessage .= "IP: " . $request->ip() . "\n";
        $logMessage .= "Data: " . json_encode($request->all()) . "\n";
        $logMessage .= "Raw Body: " . $request->getContent() . "\n";
        $logMessage .= str_repeat('-', 80) . "\n";
        
        file_put_contents(storage_path('logs/on_cancel.log'), $logMessage, FILE_APPEND | LOCK_EX);
        
        // Your business logic goes here
        // Add any processing logic here
        
        // Prepare response data
        $responseData = [
            'message' => 'on_cancel callback received successfully',
            'timestamp' => date('Y-m-d H:i:s'),
            'method' => $request->method(),
            'data' => $request->all(),
            'headers' => $request->headers->all()
        ];
        
        // Log the response as well
        Log::info('ONDC on_cancel response sent', ['response' => $responseData]);
        
        // Return proper Laravel JSON response
        return new \Illuminate\Http\JsonResponse($responseData);
    }

    /**
     * Handle the on_track callback
     */
    public function on_track(Request $request)
    {
        // Log the incoming request
        $logData = [
            'timestamp' => date('Y-m-d H:i:s'),
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'headers' => $request->headers->all(),
            'body' => $request->all(),
            'raw_body' => $request->getContent()
        ];
        
        // Write to Laravel log
        Log::info('ONDC on_track callback received', $logData);
        
        // Also write to custom log file
        $logMessage = "[" . date('Y-m-d H:i:s') . "] on_track callback\n";
        $logMessage .= "Method: " . $request->method() . "\n";
        $logMessage .= "IP: " . $request->ip() . "\n";
        $logMessage .= "Data: " . json_encode($request->all()) . "\n";
        $logMessage .= "Raw Body: " . $request->getContent() . "\n";
        $logMessage .= str_repeat('-', 80) . "\n";
        
        file_put_contents(storage_path('logs/on_track.log'), $logMessage, FILE_APPEND | LOCK_EX);
        
        // Your business logic goes here
        // Add any processing logic here
        
        // Prepare response data
        $responseData = [
            'message' => 'on_track callback received successfully',
            'timestamp' => date('Y-m-d H:i:s'),
            'method' => $request->method(),
            'data' => $request->all(),
            'headers' => $request->headers->all()
        ];
        
        // Log the response as well
        Log::info('ONDC on_track response sent', ['response' => $responseData]);
        
        // Return proper Laravel JSON response
        return new \Illuminate\Http\JsonResponse($responseData);
    }

    /**
     * Handle the on_status callback
     */
    public function on_status(Request $request)
    {
        // Log the incoming request
        $logData = [
            'timestamp' => date('Y-m-d H:i:s'),
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'headers' => $request->headers->all(),
            'body' => $request->all(),
            'raw_body' => $request->getContent()
        ];
        
        // Write to Laravel log
        Log::info('ONDC on_status callback received', $logData);
        
        // Also write to custom log file
        $logMessage = "[" . date('Y-m-d H:i:s') . "] on_status callback\n";
        $logMessage .= "Method: " . $request->method() . "\n";
        $logMessage .= "IP: " . $request->ip() . "\n";
        $logMessage .= "Data: " . json_encode($request->all()) . "\n";
        $logMessage .= "Raw Body: " . $request->getContent() . "\n";
        $logMessage .= str_repeat('-', 80) . "\n";
        
        file_put_contents(storage_path('logs/on_status.log'), $logMessage, FILE_APPEND | LOCK_EX);
        
        // Your business logic goes here
        // Add any processing logic here
        
        // Prepare response data
        $responseData = [
            'message' => 'on_status callback received successfully',
            'timestamp' => date('Y-m-d H:i:s'),
            'method' => $request->method(),
            'data' => $request->all(),
            'headers' => $request->headers->all()
        ];
        
        // Log the response as well
        Log::info('ONDC on_status response sent', ['response' => $responseData]);
        
        // Return proper Laravel JSON response
        return new \Illuminate\Http\JsonResponse($responseData);
    }
}
