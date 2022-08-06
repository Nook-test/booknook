<?php

namespace App\Http\Controllers;

use App\Models\AdminInformation;
use App\Models\Comment;
use App\Models\CustomerInformation;
use App\Models\LibraryBook;
use App\Models\User;
use App\Traits\ApiResponder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class CommentController extends Controller
{
    //
    use ApiResponder;

    public function __construct()
    {
        $this->middleware('auth:api')->except('index');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(LibraryBook $book)
    {
        $comments = Comment::where('book_id', $book->id)->get();
        return $this->okResponse($comments, 'All comments');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, LibraryBook $book)
    {
        $validator = Validator::make($request->all(), [
            'value' => 'required|string'
        ]);

        if ($validator->fails()) {
            return $this->badRequestResponse(null, $validator->errors()->toJson());
        }

        $comment = new Comment();
        $comment->value = $request->value;
        $comment->book_id = $book->id;
        $comment->user_id = Auth::id();
        $comment->save();

        return $this->okResponse(null, 'comment added successfully');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Comment  $comment
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Comment $comment)
    {
        $com = Comment::findOrFail($comment->id);

        $validator = Validator::make($request->all(), [
            'value' => 'required|string'
        ]);

        if ($validator->fails()) {
            return $this->badRequestResponse(null, $validator->errors()->toJson());
        }

        $com->update([
            'value' => $request->value
        ]);

        return $this->okResponse(null, 'comment updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Comment  $comment
     * @return \Illuminate\Http\Response
     */
    public function destroy(Comment $comment)
    {
        $comment->delete();
        return $this->okResponse(null, 'deleted is done');
    }
}
