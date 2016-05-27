<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use Validator;
use ValidatesRequests;

use App\Http\Requests;
use Session;
use Storage;
use Net_SSH2;
use Crypt_RSA;
use Net_SCP;
use App\Job;
use App\Node;
use App\Sshkey;

class NodeController extends Controller
{
    //
    protected function nonauthorized(){
        $nonauth = Session::get('isadmin');
        if (empty($nonauth) || $nonauth == 0) {
            return true;
        }
        return false;
    }
    
    public function index(Request $request) {

        $pagenum = $request->input('page');
        $freshSection = $request->input('fromHome');
        $validator = Validator::make($request->all(),[
            'page' => 'integer',
        ]);
        if ($validator->fails()) {
            return view('error')->withMsg('Page Must be Integer');
        }
        if ( !$this->nonauthorized() ) {

                $nodes = new Node;
                
                /**
                 * If user input filter, filter node result
                 */
                $filter = $request->input('filter');
                $perPage = $request->input('perpage');
                $perPage = empty($perPage) ? 20:$perPage;
                if (empty($filter)) {
                    $nodelist = $nodes->orderBy('created_at','desc')->paginate($perPage);
                } else {
                    $nodelist = $this->filterNode($filter,null,null,$perPage);
                }
                
                $totalpage = ceil($nodelist->total() / $perPage);
                $data = ['totalpage' => $totalpage,'noderes' => $nodelist];
                /**
                 * If request from home page, refresh whole page
                 * else refresh list table section
                 */
                if ($freshSection == 1) {
                    return view('nodes.nodelistMenu',$data);
                } else {
                    return view('nodes.nodelistTable',$data);
                }

        }else{

            return view('error')->withMsg('Permission Denied');
            //echo $request->session()->all();
        }     
    }

    //ping target to check is alive
    //now we use net_ssh2
    //this is not useless
    public function getPing($ip) {
        
        $cmd = "ping -c 3 -W 5 ".$ip;
        $message = array('status'=>0,'text'=>'initial');
        $p = @popen($cmd,'r');
        $read = "";
        if ( $p ) {
            while ( !feof($p) ){
                $read = $read.fread($p,4096);
                //echo $read;
            }
            pclose($p);
            $val = explode('=',$read);
            if ( is_array($val) and (count($val)>0)) {
                //return $message;
                $time = end($val);
                $time = explode('/',$time);
            }
            if ( !empty($time) and count($time)>2 ) {
                $message['status']=1;
                $message['text']=$time[3];
                //return $message;
            } else {
                $message['status']=2;
                $message['text']="Remote Server Timeout in 15s";
                //return $message;
            }
        } else {
            $message['status']=3;
            $message['text']="Execute Command Failed";
            //return $message;
        }
        return $message;
    }
    
    
    //discover single host
    public function discoverd(Request $request) {
        
        $ip = $request->input('ip');
        $port = $request->input('port');
        $error = '';
        $res = '';
        if ( empty($ip) ) {
            $error['message'] = "IP Cannot be Empty";
        } else {
            //default port 22
            if ( empty($port) ){
                $port = 22;
            }
            //validate ip and port
            $validator = Validator::make($request->all(),[
                'ip' => 'required|ip',
                'port' => 'integer'
            ]);
            if ($validator->fails()) {
                $error['message'] = "IP or Port is Illegal";
            } else {
            
                $sshkey = new Sshkey;
                $dbres = $sshkey->select('username','pubkey_path','privekey_path')->where('username','root')->first();
                if ( !empty($dbres['privekey_path'])) {
                    $pubkey = $dbres['pubkey_path'];
                    $prikey = $dbres['privekey_path'];
                    
                        
                        //$con = @ssh2_connect($ip,$port,array('hostkey'=>'ssh-rsa'));
                        //default connect timeout 10s
                        $sshcon = new Net_SSH2($ip,$port);
                        $prikeyString = new Crypt_RSA();
                        $prikeyString->loadKey(file_get_contents($prikey));
                        $rootpass = $request->input('rootpass');
                        
                        //if password given, use password
                        if (!empty($rootpass)) {
                        //echo $rootpass;
                            $authflag = $this->addPubkeyUsePassword($sshcon,$rootpass,$pubkey);
                            if ($authflag !== true) {
                                 $error['message'] = $authflag;
                            }
                        } else {
                            //use private key to login if root password not give
                            if (!@$sshcon->login('root',$prikeyString)) {
                                $error = error_get_last();
                            }
                        }
                        //test if login sucess
                            if ( $sshcon->isAuthenticated() ) {
                                $docroot = base_path();
                                $localf = $docroot."/resources/shell/inner/addsinglenode.sh";
                                $remotef = "/tmp/addsinglenode".time();
                                $scpChan = new Net_SCP($sshcon);
                                
                                if (@$scpChan->put($remotef,$localf,'NET_SCP_LOCAL_FILE')) {
                                    //exectue timeout 10s
                                    $sshcon->setTimeout(30);
                                    $output = $sshcon->exec("sh ".$remotef." 2>&1");
                                    //echo $output;
                                    if (!$sshcon->isTimeout()) {
                                        $res = json_decode($output,true);
                                        if (json_last_error()) {
                                            $error['message'] = "Parse Host Info Error";
                                        }
                                    } else {
                                        $error['message'] = "Retrive Host Info Timeout";
                                    }
                                } else {
                                    $error = error_get_last();
                                }
                                //return view('nodeinfo');   
                            } else {
                                $error['message'] = "Authentication Failed";
                            }
                   
               } else {
                    //echo $dbres['pubkey_path'];
                    $error['message'] = "Retrive Authenticate Key Failed";
               }
           } //validate end

        }
        //return $message;
        if (!empty($sshcon)) {
            $sshcon->disconnect();
            unset($sshcon);
        }
        if (empty($error['message']) and is_array($res)) {
            return view('nodes.nodeinfo',$res);
        } else {
            return view('error')->withMsg($error['message']);
        }
    } //end of function
    
    public function getNodeById(Request $request) {
        if (Validator::make($request->all(),['nodeid' => 'required|integer'])->fails()) {
            return view('error')->withMsg('Bad Parameter Given');
        }
        $res = $this->getNode('id',$request->input('nodeid'));
        $tagarray = array();
        foreach ($res as $node) {
            $tagarray = explode(',',$node->tag);
        }
        return view('nodes.nodepropertypanel',['nodeidres' => $res,'nodetags' => $tagarray]);
    }
    /**
     * Just save a single node
     */
    public function storenode(Request $request){
        
        $action = $request->input('act');
        $ip = $request->input('ip');
        $host = $request->input('host');
        $plat = $request->input('plat');
        $os = $request->input('os');
        $ker = $request->input('ker');
        $tag = $request->input('tag');
        $vendor = $request->input('ven');
        $portok = $request->input('port');
        $user = $request->input('user');
        
        if (empty($portok)) {
            $portok = 22;
        }
        if (empty($user)) {
            $user = 'root';
        }
        
        $nodeData = [
            'hostname' => $host,
            'platform' => $plat,
            'osversion' => $os,
            'kernel' => $ker,
            'vendor' => $vendor,
            'tag' => $tag,
            'ipaddr' => $ip,
            'portok' => $portok,
            'user' => $user,
            'created_at' => date("Y-m-d H:i:s"),
            'updated_at' => date("Y-m-d H:i:s")
        ];
        switch ($action) {
            case "add":
                $ipExists = $this->getNode('ipaddr',$ip);
                if (!empty($ipExists)) {
                    return view('error')->withMsg('This IP Address Already Exists');
                }
                $flag = $this->insertNodeData($nodeData);
                if ($flag){
                    return view('messageok')->withMsg("Add Successfully");
                } else {
                    return view('error')->withMsg('Save Database Error');
                }
                break;
            default:
                return view('error')->withMsg('Unrecognized Method');
                break;
                
        }
    }
    
    public function insertNodeData($dataArr) {
        $node = new Node;
        $inBool = $node->insert($dataArr);
        return $inBool;
    }
    
    public function getNodeName(Request $request){
        $col = $request->input('column');
        $filter = $request->input('filter');
        $sel = $request->input('selected');
        $filterall = $request->input('filterall');
        $validator = Validator::make($request->all(),[
                'column' => 'required',
                'filter' => 'required'
        ]);
        if ($validator->fails()) {
            return view('error')->withMsg('Filter Text cannot be blank');
        }

        $noderes = $this->filterNode($filter,$sel,$filterall);
        //echo $noderes;
        return view('nodes.nodeselect',['noderes' => $noderes,'sum' => count($noderes)]);
    }
    
    public function filterNode($filter,$select=null,$filterall=null,$pagenum=null){
        $filter = trim($filter);
        $colArr = preg_split('/[\s]+/',$filter);
        $whereString = '';
        for ($i=0;$i<count($colArr);$i++) {
            $filterString = explode(':',$colArr[$i]);
            if (count($filterString) == 2) {
                $colname = trim($filterString[0]);
                $searchString = trim($filterString[1]);
                $search = str_replace("*","%",$searchString);
                $whereString .= $colname." like '".$search."' and ";
            }
        }
        $node = new Node;
        /**
         * if pagenum is given and show list by pagenum
         * if user choose node and show list choosed
         * if user use filter and show list by filter
         */
        if ($filterall == 1) {
            $noderes = $node->whereRaw($whereString."1=1")->orderBy('created_at','desc')->get();
            return $noderes;
        }
        if ($filterall != null and empty($select)) {
            return null;
        }
        
        if ($select == null) {
            if ($pagenum != null) {
                $noderes = $node->whereRaw($whereString."1=1")->orderBy('created_at','desc')->paginate($pagenum);
            } else {
                $noderes = $node->whereRaw($whereString."1=1")->orderBy('created_at','desc')->get();
            }
        } else {
            if ($pagenum != null) {
                $noderes = $node->whereRaw($whereString."1=1")->whereIn('id',explode(',',$select))->orderBy('created_at','desc')->paginate($pagenum);
            } else {
                $noderes = $node->whereRaw($whereString."1=1")->whereIn('id',explode(',',$select))->orderBy('created_at','desc')->get();
            }
        }
        return $noderes;
    }
    
    public function getNode($col,$value) {
        $node = new Node;
        $noderes = $node->where($col,$value)->get();
        return $noderes;
    }
    
    public function searchNode($col,$value) {
        $node = new Node;
        if ($value == "*") {
            $noderes = $node->get();
        } else {
            $search = str_replace("*","%",$value);
            $noderes = $node->where($col,'like',$search)->get();
        }
        return $noderes;
    }
    
    /**
     * Function add public key
     */
    public function addPubkeyUsePassword($sshcon,$pass,$pubkey){
        //if root password give, use password and add public key
        $error['message'] = false;
        if (!empty($pass)) {
             //$authflag = @ssh2_auth_password($con,'root',$pass);
             if (!@$sshcon->login('root',$pass)) {
                $error = error_get_last();
                //return 'Authenticate Failed Use Password';
             }
             //upload public key and add to root
             $remotef = "/tmp/pubkey".time();
             $scpChan = new Net_SCP($sshcon);
             
             if (@$scpChan->put($remotef,$pubkey,'NET_SCP_LOCAL_FILE')) {
                  $command = "mkdir ~/.ssh ; chmod 0700 ~/.ssh && cat ".$remotef." >> ~/.ssh/authorized_keys";
                  //ssh2_exec($con,$command);
                  $sshcon->exec($command);
                  return true;
             } else {
                $error = error_get_last();
                //return 'Upload Public Key Failed';
             }
        }
        return $error['message'];
    }
    
    /**
     * Receive request and remove node
     */
    public function removeNode(Request $request) {
                
        $validator = Validator::make($request->all(),[
                'nodeid' => 'required'
        ]);
        if ($validator->fails()) {
            return view('error')->withMsg('No Node Selected');
        }
        $nodeid = $request->input('nodeid');
        $nodeidStr = preg_replace('/,$/','',$nodeid);
        $nodeidArr = explode(',',$nodeidStr);
        if ($this->removeNodeById($nodeidArr)) {
            return view('messageok')->withMsg('Remove Node Successfully, Please Refresh Page');
        } else {
            return view('error')->withMsg('No Node Removed');
        }
    }
    /**
     * Function delete nodes use ids
     */
    public function removeNodeById($idArr) {
        $node = new Node;
        $nodeNum = $node->whereIn('id',$idArr)->delete();
        if ($nodeNum > 0) {
            return true;
        }
        return false;
    }
    /**
     * Get uploaded yaml file and parse to array
     * return array
     */
    public function getParseUploadYaml($file){
        $yamlFile = $file;
        if ($yamlFile->isValid()) {
            $filePath = $yamlFile->path();
            $dataArr = @yaml_parse_file($filePath);
            if (empty($dataArr)) {
                return 'filenotyaml';
            } else {
                return $dataArr;
            }
        } else {
            return 'fileinvalid';
        }
    }
    /**
     * When we get parsed data,map to database column
     * then insert data
     */
    public function importNodeYaml(Request $request) {
        $validator = Validator::make($request->all(),[
                'yamlfile' => 'required'
        ]);
        if ($validator->fails()) {
            return view('jobs.value')->with('value','No File Selected');
        }
        
        $upfile = $request->file('yamlfile');
        $dataArr = $this->getParseUploadYaml($upfile);
        if ($dataArr === 'filenotyaml') {
            return view('jobs.value')->with('value','Yaml File Format Not Correct');
        } else if ($dataArr === 'fileinvalid') {
            return view('jobs.value')->with('value','Yaml File is Invalid');
        } else {
            /**
             * Iterate array to retrive node info
             */
            $nodeInsertArr = '';
            $tmpArr = '';
            $i = 0;
            //for ($i=0;$i<count($dataArr);$i++) {
                foreach($dataArr as $key1 => $value1) {
                    foreach ($dataArr[$key1] as $key => $value) {
                        $value = trim($value);
                        switch ($key) {
                            case 'description': $tmpArr['description'] = $value;break;
                            case 'hostname': $tmpArr['ipaddr'] = $value;break;
                            case 'nodename': $tmpArr['hostname'] = $value;break;
                            case 'osArch': break;
                            case 'osFamily': $tmpArr['platform'] = $value;break;
                            case 'osName': $tmpArr['osversion'] = $value;break;
                            case 'osVersion': $tmpArr['kernel'] = $value;break;
                            case 'username': $tmpArr['user'] = $value;break;
                            case 'tags': $tmpArr['tag'] = $value;break;
                        }
                    }
                    $i++;
                //}
                
                    $ipAndPort = explode(':',trim($tmpArr['ipaddr']));
                    $tmpArr['ipaddr'] = $ipAndPort[0];
                    $tmpArr['portok'] = $ipAndPort[1];
                    $tmpArr['created_at'] = date("Y-m-d H:i:s");
                    $tmpArr['updated_at'] = date("Y-m-d H:i:s");
                    if ($i == 0) {
                        $nodeInsertArr = [$i => $tmpArr];
                    } else {
                        $nodeInsertArr = array_add($nodeInsertArr,$i,$tmpArr);
                    }
                }
            
            /**
             * all data in $nodeInsertArr
             * insert into database
             */

            if ($this->insertNodeData($nodeInsertArr)) {
                  return view('jobs.value')->with('value','Import Node Successfully');
            } else {
                  return view('jobs.value')->with('value','Import Failed');
            }

        }
    }
    /**
     * Function:exportNodeYaml()
     * export nodes to a yaml file
     */
    public function exportNodeYaml(Request $request){
        $validator = Validator::make($request->all(),[
                'range' => 'required|numeric'
        ]);
        $valFilter = Validator::make($request->all(),[
                'filter' => 'required'
        ]);
        $valNodestr = Validator::make($request->all(),[
                'nodestr' => 'required'
        ]);
        if ($validator->fails()) {
            return view('error')->withMsg('Input Parameter Illegel');
        }
        
        $range = $request->input('range');
        $filter = $request->input('filter');
        $nodesel = $request->input('nodestr');
        $nodesel = preg_replace('/,$/','',phpUnescape($nodesel));
        /**
         * Three export range
         * 1 for all
         * 2 for filter
         * 3 for selected
         */
        $noderes = null;
        switch ($range) {
            case 1: $filter = 'hostname:*';$nodesel=null;break;
            case 2: 
                if ($valFilter->fails()) {
                    return view('error')->withMsg('Please input filter regex');
                }
                $filter = phpUnescape($filter);$nodesel=null;break;
            case 3: 
                if ($valNodestr->fails()) {
                    return view('error')->withMsg('Please select at least one node');
                }
                $filter = '';break;
        }
        $noderes = $this->filterNode($filter,$nodesel);
        /**
         * Map database record to array
         */
        $iterator = 1;
        foreach ($noderes as $node) {
            $dataArr = [
                'nodename' => $node->hostname,
                'description' => $node->description,
                'hostname' => $node->ipaddr.":".$node->portok,
                'osArch' => '',
                'osFamily' => $node->platform,
                'osName' => $node->osversion,
                'osVersion' => $node->kernel,
                'username' => $node->user,
                'tags' => $node->tag
            ];
            if ($iterator == 1) {
                $toYamlArr = [$node->hostname => $dataArr];
            } else {
                $toYamlArr = array_add($toYamlArr,$node->hostname,$dataArr);
            }
            $iterator++;
        }
        /**
         * Encode arrary to yaml string
         * write to local disk
         */
        $fileName = 'resources.yaml.'.time();
        $content = yaml_emit($toYamlArr,YAML_UTF8_ENCODING,YAML_LN_BREAK);
        $content = preg_replace('/---\n/','',$content);
        $content = preg_replace('/\.\.\.\n/','',$content);
        Storage::disk('download')->put($fileName,$content);
        $data = [
            'filename' => 'resources.yaml',
            'realname' => $fileName
        ];
        /**
         * Wait user to click download
         */
        return view('nodes.downloadYaml',$data);
    }
    /**
     * Respone to user download file
     */
    public function downLoadYaml(Request $request) {
        $realName = $request->input('exprealname');
        if (Storage::disk('download')->has($realName)) {
            $realPath = storage_path('app/download/'.$realName);
            $headers = [
                'Content-Type' => 'application/text'
            ];
            return response()->download($realPath,'resources.yaml',$headers);
        } else {
            return view('error')->with('msg','File has Expired');
        }
    }
}
