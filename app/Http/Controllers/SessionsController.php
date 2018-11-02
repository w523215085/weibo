<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use Auth;

class SessionsController extends Controller
{   

    public function __construct()
    {
        $this->middleware('guest', [
            'only' => ['create']
        ]);
    }
    
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

        /*
        Auth::attempt() 方法可接收两个参数，第一个参数为需要进行用户身份认证的数组，第二个参数为是否为用户开启『记住我』功能的布尔值。接下来让我们修改会话控制器中的 store 方法，为 Auth::attempt() 添加『记住我』参数。
        */
    	if (Auth::attempt($credentials, $request->has('remember'))) {
            if (Auth::user()->activated) {
                session()->flash('success', '欢迎光临');
                return redirect()->intended(route('users.show', [Auth::user()]));
            } else {
                Auth::logout();
               session()->flash('warning', '你的账号未激活，请检查邮箱中的注册邮件进行激活。');
               return redirect('/');
            }

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
