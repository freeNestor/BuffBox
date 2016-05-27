<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;

use App\User;
use Session;
use Validator;
use Crypt;

class LoginController extends Controller
{
    //
    protected $title = "Pleas Login";
    
    public function login(Request $request){
        $user = new User;
        $inputname = $request->input('inputName');
        $inputpass = $request->input('inputPassword');

        if (Validator::make($request->all(),['inputName' => 'required','inputPassword' => 'required'])->fails()) {
            //return view('error')->withMsg('UserName or Password is Empty');
            $data = ['title' => $this->title,'msg' => 'UserName or Password is Empty'];
            return view('welcome',$data);
        }
        $dbres = $user->select('name','pass','admin')->where('name',$inputname)->first();
        if (!empty($dbres['name'])){
            $decryptpass = Crypt::decrypt($dbres['pass']);
            //$decryptpass = $dbres['pass'];
            
            if ($decryptpass == $inputpass and $dbres['admin'] == 1){
                $request->session()->put('username',$dbres['name']);
                $request->session()->put('isadmin',1);
                $request->session()->save();
                //return view('admin.home');
                $data = [
                    'title'=>'Home Page',
                    'user'=>$inputname
                    ];
                return view('monitorpage',$data);
            }
            if ($decryptpass == $inputpass and $dbres['admin'] <> 1){
                $request->session()->put('username',$dbres['name']);
                $request->session()->save();
                $data = [
                    'title'=>'Home Page',
                    'user'=>$inputname
                    ];
                return view('monitorpage',$data);
                //return redirect('/');
            }
        }
        
        $data = ['title' => $this->title,'msg' => 'User Name or Password Invalid'];
        return view('welcome',$data);
        
    }
    
    public function logout(Request $request){
        $request->session()->forget('username');
        $request->session()->flush();
        return redirect('/');
    }
}
