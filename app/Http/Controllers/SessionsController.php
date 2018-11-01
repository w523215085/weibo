<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use Auth;

class SessionsController extends Controller
{   
    /*
    *显示登录界面
    *
    */
    public function create()
    {
    	return view('sessions.create');
    }


    //登录成功后跳转到个人界面
    public function store(Request $request)
    {
    	$credentials = $this->validate($request, [
    		'email' => 'required|email|max:255',
    		'password' => 'required'
    	]);

    	if (Auth::attempt($credentials, $request->has('remember'))) {
    		session()->flash('success', '欢迎光临');
    		return redirect()->route('users.show', [Auth::user()]);
    	}else{
    		session()->flash('danger', '很抱歉，邮箱和密码不匹配');
    		return redirect()->back();
    	}
    }

    public function destroy()
    {
    	Auth::logout();
    	session()->flash('success', '您已成功退出');
    	return redirect('login');
    }
}
