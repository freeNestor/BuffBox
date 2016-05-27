<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;

use Session;

class HomeController extends Controller
{
    //
    
    public function index(Request $request) {

        if ( $request->session()->has('username') ) {
            $user = $request->session()->get('username');
            $admin = $request->session()->get('isadmin');
            $data = ['user' => $user,'title' => 'Home Page'];
            
            if ( $admin == 1 ) {
                //return view('admin.home');
                return view('monitorpage',$data);
            }else{
                return view('monitorpage',$data);
            }

        }else{

            return view('welcome')->withTitle('Pleas Login');
        }
        
    }
}
