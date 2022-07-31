<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

trait ApiResponder
{
    public function successResponse(mixed $data, $message, int $statusCode = Response::HTTP_OK): JsonResponse
    {
        if(!$message){
            $message = Response::$statusTexts[$statusCode];
        }

        return response()->json([
            'status code' => $statusCode,
            'message' => $message,
            'data' => $data
        ]);
    }

    public function errorResponse(mixed $data, $message, int $statusCode = Response::HTTP_INTERNAL_SERVER_ERROR): JsonResponse
    {
        if(!$message){
            $message = Response::$statusTexts[$statusCode];
        }

        return response()->json([
            'status code' => $statusCode,
            'error' => $message,
            'data' => $data
        ]);
    }
    public function okResponse(mixed $data, $message=''): JsonResponse
    {
        return $this->successResponse($data,$message,Response::HTTP_OK);
    }

    public function createdResponse(mixed $data, $message=''): JsonResponse
    {
        return $this->successResponse($data,$message,Response::HTTP_CREATED);
    }

    public function badRequestResponse(mixed $data, $message=''): JsonResponse
    {
        return $this->errorResponse($data,$message,Response::HTTP_BAD_REQUEST);
    }

    public function unauthorizedResponse(mixed $data, $message=''): JsonResponse
    {
        return $this->errorResponse($data,$message,Response::HTTP_UNAUTHORIZED);
    }

}
