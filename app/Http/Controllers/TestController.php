<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use Cache;

class TestController extends Controller
{
    //
    public function test(Request $request){
        //print_r($request->server('REMOTE_ADDR'));
        $data = array(['ok' => 1]);
        Cache::put('test',$data,1);
        
        $tmp = Cache::get('test');
        print_r($tmp);
    }
}
