<?php

namespace App\Http\Controllers;

use App\Todo;
use Illuminate\Http\Request;

class TodoController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return Todo::all();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $todo = $request->validate([
            'title' => 'required|string',
            'completed' => 'required|boolean'
        ]);

        $result = Todo::create($todo);
        return response($result, 201);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Todo  $todo
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Todo $todo)
    {
        $data = $request->validate([
            'title' => 'required|string',
            'completed' => 'required|boolean'
        ]);

        $todo->update($data);
        return response($todo, 200);
    }

    public function updateAllChecked(Request $request){
        $data = $request->validate([
            'completed' => 'required|boolean'
        ]);

        Todo::query()->update($data);
        return response('Updated All', 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Todo  $todo
     * @return \Illuminate\Http\Response
     */
    public function destroy(Todo $todo)
    {
        $todo->delete();
        return response('Deleted Successfully', 200);
    }

    public function destroyAllCompleted(Request $request){
        $request->validate([
            'todos' => 'required|array'
        ]);

        Todo::destroy($request->todos);
        return response('Deleted All', 200);
    }
}
