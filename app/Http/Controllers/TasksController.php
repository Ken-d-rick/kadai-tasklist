<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Task;    // 追加

class TasksController extends Controller
{
    // getでtasks/にアクセスされた場合の「一覧表示処理」
    public function index()
    {
        $data = [];
        if (\Auth::check()) { // 認証済みの場合
            // 認証済みユーザを取得
            $user = \Auth::user();
            
            $tasks = $user->tasks()->orderBy('created_at', 'desc')->paginate(10);

            // タスク一覧ビューでそれを表示
            return view('tasks.index', [
                'tasks' => $tasks,
            ]);
            
            $data = [
                'user' => $user,
                'tasks' => $tasks,
            ];
        }
        
        // Welcomeビューでそれらを表示
        return view('welcome', $data);
    } 

    // getでmessages/createにアクセスされた場合の「新規登録画面表示処理」
    public function create()
    {
        $task = new Task;

        // タスク作成ビューを表示
        return view('tasks.create', [
            'task' => $task,
        ]);
    }

    // postでmessages/にアクセスされた場合の「新規登録処理」
    public function store(Request $request)
    {
        // バリデーション
        $request->validate([
            'status' => 'required|max:10',   // 追加
            'content' => 'required|max:255',
        ],
        [
            'status.required' => '空文字を許さない',
            'status.max' => '10文字を超える文字数を許さない',
            'content.required'=> '空文字を許さない',
        ]);
        
        $request->user()->tasks()->create([
            'status' => $request->status,
            'content' => $request->content,
        ]);

        // トップページへリダイレクトさせる
        return redirect('/');
    }

    // getでmessages/（任意のid）にアクセスされた場合の「取得表示処理」
    public function show($id)
    {
        // idの値でタスクを検索して取得
        $task = Task::findOrFail($id);
        
        // 認証済みユーザ（閲覧者）がその投稿の所有者でない場合はトップページにリダイレクト
        if (\Auth::id() != $task->user_id) {
            // トップページへリダイレクトさせる
            return redirect('/');
        }

        
        // タスク詳細ビューでそれを表示
        return view('tasks.show', [
            'task' => $task,
        ]);
    }
    // getでmessages/（任意のid）/editにアクセスされた場合の「更新画面表示処理」
    public function edit($id)
    {
        // idの値でタスクを検索して取得
        $task = Task::findOrFail($id);
        
        // 認証済みユーザ（閲覧者）がその投稿の所有者でない場合はトップページにリダイレクト
        if (\Auth::id() != $task->user_id) {
            // トップページへリダイレクトさせる
            return redirect('/');
        }


        // タスク編集ビューでそれを表示
        return view('tasks.edit', [
            'task' => $task,
        ]);
    }

    // putまたはpatchでmessages/（任意のid）にアクセスされた場合の「更新処理」
    public function update(Request $request, $id)
    {
        // バリデーション
        $request->validate([
            'status' => 'required|max:10',   // 追加
            'content' => 'required|max:255',
        ],
        [
            'status.required' => '空文字を許さない',
            'status.max' => '10文字を超える文字数を許さない',
            'content.required'=> '空文字を許さない',
        ]);
        
        // idの値でタスクを検索して取得
        $task = Task::findOrFail($id);
        
        // 認証済みユーザ（閲覧者）がその投稿の所有者である場合は、
        if (\Auth::id() === $task->user_id) {
            //タスクを編集
            $task->status = $request->status;    // 追加
            $task->content = $request->content;
            $task->save(); 
        }

        // トップページへリダイレクトさせる
        return redirect('/');
    }

    // deleteで（任意のid）にアクセスされた場合の「削除処理」
    public function destroy($id)
    {
        // idの値でタスクを検索して取得
        $task = Task::findOrFail($id);
        
        // 認証済みユーザ（閲覧者）がその投稿の所有者である場合は、
        if (\Auth::id() === $task->user_id) {
            //タスクを削除
            $task->delete();
        }

        // トップページへリダイレクトさせる
        return redirect('/');
        
    }
}