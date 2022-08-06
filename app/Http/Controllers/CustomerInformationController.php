<?php

namespace App\Http\Controllers;

use App\Http\Resources\CustomerInformationResource;
use App\Models\CustomerInformation;
use App\Traits\ApiResponder;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class CustomerInformationController extends Controller
{
    //
    use ApiResponder;

    public function __construct()
    {
        $this->middleware('auth:api');
    }
    
    public function set(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string',
            'middle_name' => 'required|string',
            'last_name' => 'required|string',
            'gender' => 'required|string',
            'phone' => 'required|numeric|digits_between:10,10',
            'birth_day' => 'required|date',
        ]);

        if ($validator->fails()) {
            return $this->badRequestResponse(null, $validator->errors()->toJson());
        }

        $customer_info = new CustomerInformation();
        $customer_info->user_id = Auth::id();
        $customer_info->first_name = $request->first_name;
        $customer_info->middle_name = $request->middle_name;
        $customer_info->last_name = $request->last_name;
        $customer_info->gender = $request->gender;
        $customer_info->phone = $request->phone;
        $customer_info->birth_day = Carbon::parse($request->birth_day)->format('Y-m-d');
        $customer_info->save();

        return $this->okResponse(
            new CustomerInformationResource(
                CustomerInformation::where('user_id', Auth::id())->first()
            ),
            'Information has been added successfully'
        );
    }

    public function update(Request $request, CustomerInformation $customer)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'string',
            'middle_name' => 'string',
            'last_name' => 'string',
            'gender' => 'string',
            'phone' => 'numeric|digits_between:10,10',
            'birth_day' => 'date',
        ]);

        if ($validator->fails()) {
            return $this->badRequestResponse(null, $validator->errors()->toJson());
        }

        if ($request->has('first_name')) {
            $customer->first_name = $request->first_name;
        }

        if ($request->has('middle_name')) {
            $customer->middle_name = $request->middle_name;
        }

        if ($request->has('last_name')) {
            $customer->last_name = $request->last_name;
        }

        if ($request->has('gender')) {
            $customer->gender = $request->gender;
        }

        if ($request->has('phone')) {
            $customer->phone = $request->phone;
        }

        if ($request->has('birth_day')) {
            $customer->birth_day = $request->birth_day;
        }

        if (!$customer->isDirty()) {
            return $this->errorResponse(null, 'You need to specify a different value to update', 422);
        }

        $customer->save();

        return $this->okResponse(
            new CustomerInformationResource(
                $customer
            ),
            'Information has been updated successfully'
        );
    }
}
