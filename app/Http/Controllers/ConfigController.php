<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;

use App\User;
use App\Sshkey;
use App\Config;
use Validator;
use Crypt;
use Session;

class ConfigController extends Controller
{
    //
    
    protected function nonauthorized() {
        $adminFlag = Session::get('isadmin');
        if (empty($adminFlag) || $adminFlag == 0) {
            return true;
        }
        return false;
    }

    public function index(Request $request) {
        return view('configure.welcome');
    }
    
    public function home(Request $request) {
        if ($this->nonauthorized()) {
            return view('error')->withMsg('Permission Denied');
        }
        return view('configure.home');
    }
    
    public function configuser(Request $request) {
        $defaultuser = $request->input('defaultu');
        
        if (empty($defaultuser) || $defaultuser == 1) {
            $user = new User;
            $userList = $user->where('defaultu',1)->get();
            return view('configure.defaultuser',['userList' => $userList]);
        }
            return view('configure.otheruser');
    }
    
    public function resetpass(Request $request) {
        $newpass = $request->input('newpass');
        $confirmpass = $request->input('confirmpass');
        $uid = $request->input('uid');
        
        if (Validator::make($request->all(),['newpass' => 'required','confirmpass' => 'required'])->fails()) {
            return view('error')->withMsg('New Password and Confirm Password must be given');
        } else if ($newpass != $confirmpass) {
            return view('error')->withMsg('Two Given Password not Consistent');
        } else {
        
            $cryptpass = Crypt::encrypt($newpass);
            $user = new User;
            $dbflag = $user->where('id',$uid)->update(['pass' => $cryptpass]);
            //print_r($dbflag);
            if ($dbflag) {
                return view('messageok')->withMsg('Reset Password Finished');
            } else {
                return view('error')->withMsg('Reset Password Failed');
            }
            
        }
    }
    
    public function configSSHKey(Request $request) {
        $sshkey = new Sshkey;
        $keyres = $sshkey->get()->first();
        $data = ['key' => $keyres];
        return view('configure.sshkey',$data);
    }
    
    public function updateSSHKey(Request $request) {
        $val = Validator::make($request->all(),[
            'pubkeypath' => 'required',
            'prikeypath' => 'required'
        ]);
        if ($val->fails()) {
            return view('error')->with('msg','Please input public key and private key path');
        }
        $pubkeypath = $request->input('pubkeypath');
        $prikeypath = $request->input('prikeypath');
        $updated_at = date("Y-m-d H:i:s");
        $dataArr = [
            'pubkey_path' => $pubkeypath,
            'privekey_path' => $prikeypath,
            'updated_at' => $updated_at
        ];
        
        if ($this->updateSSHKeyDb($dataArr)) {
            return view('messageok')->with('msg','Update SSH Key Successfully');
        } else {
            return view('error')->with('msg','Update SSH Key Failed');
        }
    }
    public function newSSHKey($dataArr) {
        $sshkey = new Sshkey;
        $flag = $sshkey->insert($dataArr);
        return $flag;
    }
    public function getSSHKey($id){
        $sshkey = new Sshkey;
        $sshkeyres = $sshkey->where('id',$id)->get()->first();
        if (!empty($sshkeyres)) {
            return $sshkeyres;
        }
        return null;
    }
    public function updateSSHKeyDb($dataArr) {
        $sshkey = new Sshkey;
        $flag = $sshkey->where('id',1)->update($dataArr);
        if ($flag > 0) {
            return true;
        }
        return false;
    }
    /**
     * User Upload Key and Save
     * Update Database if initialization
     */
    public function getUploadKeyFile(Request $request){
        $pubkeyfile = $request->file('pubkeyup');
        $prikeyfile = $request->file('prikeyup');
        
        $val = Validator::make($request->all(),[
            'pubkeyup' => 'required',
            'prikeyup' => 'required'
        ]);
        if ($val->fails()) {
            return view('error')->with('msg','Please Select File');
        }
        
        $sshkeydbres = $this->getSSHKey(1);
        $keypath = base_path()."/resources/.ssh/";
        
        if ($sshkeydbres != null) {
            $keypath = dirname($sshkeydbres->pubkey_path);
        } else {
            $dataArr = [
                'username' => 'root',
                'pubkey_path' => $keypath."id_rsa.pub",
                'prikey_path' => $keypath."id_rsa",
                'key_type' => 'ssh-rsa',
                'ssh_port' => '22',
                'created_at' => date("Y-m-d H:i:s"),
                'updated_at' => date("Y-m-d H:i:s")
            ];
            if (!$this->newSSHKey($dataArr)) {
                return view('error')->with('msg','Database Error');
            }
        }
        $pubkeyfile->move($keypath,'id_rsa.pub');
        $prikeyfile->move($keypath,'id_rsa');
        
        return view('messageok')->with('msg','Update Key File Successfully');
    }
    
    public function getConfigById($id) {
        $conf = new Config;
        $confdb = $conf->where('id',1)->get()->first();
        return $confdb;
    }
    
    public function updateConfig($id,$dataArr) {
        $conf = new Config;
        $flag = $conf->where('id',$id)->update($dataArr);
        if ($flag > 0) {
            return true;
        }
        return false;
    }
    /**
     * Display System Config Form
     */
    public function configSystem(Request $request) {
        $confres = $this->getConfigById(1);
        $data = [
            'timeout' => 0,
            'logdir' => ''
        ];
        if (!empty($confres)) {
            $data = [
                'timeout' => $confres->def_job_timeout,
                'logdir' => $confres->def_log_dir,
                'phpbin' => $confres->php_bin
            ];
        }
        return view('configure.systemconf',$data);
    }
    /**
     * Save User Change into Database
     */
    public function saveSystemConfig(Request $request) {
        $jobtimeout = $request->input('jobtimeout');
        $logidr = $request->input('logdir');
        $phpbin = $request->input('phpbin');
        $val = Validator::make($request->all(),[
            'jobtimeout' => 'required|numeric',
            'logdir' => 'required'
        ]);
        if ($val->fails()) {
            return view('error')->with('msg','Input Cannot be Blank');
        }
        
        $dataArr = [
            'def_job_timeout' => $jobtimeout,
            'def_log_dir' => $logidr,
            'php_bin' => $phpbin
        ];
        if ($this->updateConfig(1,$dataArr)) {
            return view('messageok')->with('msg','Save Change Successfully');
        } else {
            return view('error')->with('msg','Save Change Failed');
        }
    }
}
