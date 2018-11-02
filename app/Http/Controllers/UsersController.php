<?php

namespace App\Http\Controllers;
use App\Models\User;
use Illuminate\Http\Request;
use Auth;
use Mail;

class UsersController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth', [
            'except' => ['show', 'create', 'store', 'index', 'confirmEmail']
        ]);

        $this->middleware('guest', [
            'only' => ['create']
        ]);
    }

    //显示用户列表
    public function index()
    {
        $users = User::paginate(10);
        return view('users.index', compact('users'));
    }

    //显示注册页面
    public function create()
    {
    	return view('users.create');
    }

    //个人信息界面
    public function show(User $user)
    {
    	return view('users.show', compact('user'));
    }

    //显示编辑页面
    public function edit(User $user)
    {   
        $this->authorize('update', $user);
        return view('users.edit', compact('user'));
    }

    //更新用户信息
    public function update(User $user, Request $request)
    {
        $this->validate($request, [
            'name' => 'required|max:50',
            'password' => 'nullable|confirmed|min:6'
        ]);

        $this->authorize('update', $user);

        $data['name'] = $request->name;
        if ($request->password) {
            $data['password'] = bcrypt($request->password);
        }
        $user->update($data);

        session()->flash('success', '个人资料更新成功！');
        return redirect()->route('users.show', $user->id);
    }

    //提交注册的信息
    public function store(Request $request)
    {
    	$this->validate($request, [
    		'name' => 'required|max:50',
    		'email' => 'required|email|unique:users|max:255',
            'password' => 'required|confirmed|min:6'
    	]);

    	$user = User::create([
    		'name' => $request->name,
    		'email' => $request->email,
    		'password' => bcrypt($request->password),
    	]);

        $this->sendEmailConfirmationTo($user);
    	session()->flash('success', '验证邮件已发送到你的注册邮箱上，请注意查收。');
    	return redirect('/');
    }

    //删除用户
    public function destroy(User $user)
    {   
        $this->authorize('destroy', $user);
        $user->delete();
        session()->flash('success', '删除成功');
        return back();
    }

    //用户的激活操作
    public function confirmEmail($token)
    {
        $user = User::where('activation_token', $token)->firstOrFail();

        $user->activated = true;
        $user->activation_token = null;
        $user->save();

        Auth::login($user);
        session()->flash('success', '恭喜你，激活成功！');
        return redirect()->route('users.show', [$user]);
    }

    //注册成功后发送激活邮件,use意思是连接闭包和外界变量。
    public function sendEmailConfirmationTo($user)
    {

        $view = 'emails.confirm';
        $data = compact('user');
        $from = 'aufree@yousails.com';                      //发送者
        $name = 'Aufree';                                   //发送者名称
        $to = $user->email;                                 //接受者
        $subject = "感谢注册 Sample 应用！请确认你的邮箱。";   //主题

        // 第一个参数是包含邮件消息的视图名称。
        // 第二个参数是要传递给该视图的数据数组。
        // 最后是一个用来接收邮件消息实例的闭包回调，我们可以在该回调中自定义邮件消息的发送者、接收者、邮件主题等信息。
        Mail::send($view, $data, function($message) use ($from, $name, $to, $subject)
        {
            $message->from($from, $name)->to($to)->subject($subject);
        });
    }
}
