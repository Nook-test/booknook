<?php

namespace App\Http\Controllers;

use App\Http\Resources\AdminInformationResource;
use App\Models\AdminInformation;
use App\Models\User;
use App\Traits\ApiResponder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AdminInformationController extends Controller
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
            'library_name' => 'required|string',
            'phone' => 'required|numeric|digits_between:10,10',
            'open_time' => 'required|date_format:H:i',
            'close_time' => 'required|date_format:H:i'
        ]);

        if ($validator->fails()) {
            return $this->badRequestResponse(null, $validator->errors()->toJson());
        }
        $admin_info = new AdminInformation();
        $admin_info->user_id = Auth::id();
        $admin_info->first_name = $request->first_name;
        $admin_info->middle_name = $request->middle_name;
        $admin_info->last_name = $request->last_name;
        $admin_info->library_name = $request->library_name;
        $admin_info->phone = $request->phone;
        $admin_info->open_time = $request->open_time;
        $admin_info->close_time = $request->close_time;
        $admin_info->save();

        return $this->okResponse(
            new AdminInformationResource(
                AdminInformation::where('user_id', Auth::id())->first()
            ),
            'Information has been added successfully'
        );
    }

    public function update(Request $request,AdminInformation $admin)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'string',
            'middle_name' => 'string',
            'last_name' => 'string',
            'library_name' => 'string',
            'phone' => 'numeric|digits_between:10,10',
            'open_time' => 'date_format:H:i',
            'close_time' => 'date_format:H:i'
        ]);

        if ($validator->fails()) {
            return $this->badRequestResponse(null, $validator->errors()->toJson());
        }
        if ($request->has('first_name')) {
            $admin->first_name = $request->first_name;
        }

        if ($request->has('middle_name')) {
            $admin->middle_name = $request->middle_name;
        }

        if ($request->has('last_name')) {
            $admin->last_name = $request->last_name;
        }

        if ($request->has('library_name')) {
            $admin->library_name = $request->library_name;
        }

        if ($request->has('phone')) {
            $admin->phone = $request->phone;
        }

        if ($request->has('open_time')) {
            $admin->open_time = $request->open_time;
        }

        if ($request->has('close_time')) {
            $admin->close_time = $request->close_time;
        }

        if(!$admin->isDirty()){
            return $this->errorResponse(null,'You need to specify a different value to update',422);
        }

        $admin->save();

        return $this->okResponse(
            new AdminInformationResource(
                $admin
            ),
            'Information has been updated successfully'
        );
    }
}
