<?php

namespace App\Http\Controllers;

use App\Models\AdminInformation;
use App\Models\Book;
use App\Traits\ApiResponder;
use Illuminate\Http\UploadedFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class LoadImageController extends Controller
{
    //
    use ApiResponder;

    public function getImagePath(UploadedFile $image)
    {
        $image_name =   time() . '.' . $image->getClientOriginalExtension();
        $image->storeAs('public/images', $image_name);
        return 'storage/images/' . $image_name;
    }

    public function __construct()
    {
        $this->middleware('auth:api')->except('getImagePath');
    }

    public function libraryImage(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'image' => 'required|file',
        ]);

        if ($validator->fails()) {
            return $this->badRequestResponse(null, $validator->errors()->toJson());
        }

        $path = $this->getImagePath($request->file('image'));

        $admin_info = AdminInformation::where('user_id', Auth::id())->first();
        $admin_info->update([
            $admin_info->image = $path
        ]);
        return $this->okResponse(null, 'image loaded successfully');
    }

    public function bookImage(Request $request, $book)
    {
        $validator = Validator::make($request->all(), [
            'image' => 'required|file',
        ]);
        if ($validator->fails()) {
            return $this->badRequestResponse(null, $validator->errors()->toJson());
        }
        $path = $this->getImagePath($request->file('image'));

        $book_info = Book::find($book);
        $book_info->update([
            $book_info->image = $path
        ]);

        return $this->okResponse(null, 'image loaded successfully');

    }
}
