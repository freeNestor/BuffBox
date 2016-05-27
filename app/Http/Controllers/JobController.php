<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use Session;
use Cache;
use Validator;
use Storage;
use Net_SSH2;
use App\Node;
use App\Sshkey;
use App\Job;
use App\JobTask;
use App\ViwJobExec;
use App\ViwJobState;
use App\Config;

class JobController extends Controller
{
    //
    public $cacheExpire = 60;
    protected function nonauthorized(){
        $nonauth = Session::get('isadmin');
        if (empty($nonauth) || $nonauth == 0) {
            return true;
        }
        return false;
    }
    
    public function index(Request $request) {

        if ( !$this->nonauthorized() ) {
                $jobs = new Job;
                $dbres = $jobs->orderby('updated_at')->paginate(10);
                $totalPage = ceil($dbres->total() / 10);
                $data = ['jobres' => $dbres,'totalpage' => $totalPage];
                return view('jobs.joblist',$data);
        }else{
            return view('error')->withMsg('Permission Denied');
            //echo $request->session()->all();
        }     
    }
    
    public function indexTask(Request $request) {

        if ( !$this->nonauthorized() ) {
                $task = new JobTask;
                $taskres = $task->orderBy('updated_at','desc')->orderby('state','desc')->paginate(20);
                $totalPage = ceil($taskres->total() / 20);
                $data = ['taskres' => $taskres,'totalpage' => $totalPage];
                return view('jobs.tasklist',$data);
        }else{
            return view('error')->withMsg('Permission Denied');
            //echo $request->session()->all();
        }     
    }
    /**
     * New a job and insert into database
     */
    public function myjobSave(Request $request){
        if ( !$this->nonauthorized() ) {
            $validator = Validator::make($request->all(),[
                'steptimeout' => 'integer',
                'jobtimeout' => 'integer'
            ]);
            if ($validator->fails()) {
                return view('error')->withMsg('Step Timeout or Job Timeout is not integer');
            }
            $validator = Validator::make($request->all(),[
                'parallel' => 'integer|max:50'
            ]);
            if ($validator->fails()) {
                return view('error')->withMsg('Threads must be integer and less than 50');
            }
            $validator = Validator::make($request->all(),[
                'jobname' => 'required|alpha_dash',
                'stepstring' => 'required'
            ]);
            if ($validator->fails()) {
                return view('error')->withMsg('Job Name or Step is Empty');
            }
            
            $jobName = $request->input('jobname');
            $jobDes = $request->input('jobdes');
            $stepRel = $request->input('steprela');
            $parallel = $request->input('parallel');
            $stepTimeout = $request->input('steptimeout');
            $jobTimeout = $request->input('jobtimeout');
            $filter = $request->input('filter');
            $stepString = $request->input('stepstring');
            $nodeSel = $request->input('nodeselect');
            $allselect = $request->input('allselect');
            
            $opFlag = $request->input('update');
            
            if (empty($parallel)) {
                $parallel = 5;
            } else {
                if ($parallel > 50) {
                    $parallel = 50;
                }
            }
            
            if (empty($stepTimeout)) {
                $stepTimeout = 0;
            }
            if (empty($jobTimeout)) {
                $jobTimeout = 0;
            }
            if (!empty($filter)) {
                $filter = trim($filter);
            }
            if (!empty($nodeSel)) {
                $nodeSel = preg_replace('/,$/','',$nodeSel);
            }
            $stepString = "{".preg_replace('/,$/','}',$stepString);
            $date = date("Y-m-d H:i:s");
            $conf = new Config;
            $defLogPath = $conf->where('id',1)->get()->first()->def_log_dir;
            $defLogPath = empty($defLogPath) ? base_path()."/storage/logs" : $defLogPath;
            $data = [
                'name' => $jobName,
                'description' => $jobDes,
                'jobsteps' => $stepString,
                'stepRelation' => $stepRel,
                'parallel' => $parallel,
                'stepTimeout' => $stepTimeout,
                'jobTimeout' => $jobTimeout,
                'nodeFilter' => $filter,
                'nodeSelected' => $nodeSel,
                'filterall' => $allselect,
                'logpath' => $defLogPath,
                'updated_at' => $date
            ];
            //empty: new job;
            //true: update job;
            if (empty($opFlag)) {
                array_add($data,'created_at',$date);
                $jobExists = $this->getJobByName($jobName);
                if ($jobExists != false) {
                    return view('error')->withMsg('Job Name Already Exists');
                }
                $this->saveJob($data);
                return view('messageok')->withMsg('Saved Job Sucessfully');
            }
            $jobid = $request->input('jobid');
            if ($opFlag == true and !empty($jobid)) {
                if($this->updateJob($jobid,$data)) {
                    return view('messageok')->with('msg','Update Job Successfully');
                } else {
                    return view('error')->with('msg','Update Job Failed');
                }
            }
        } else {
            return view('error')->withMsg('Permission Denied');
        }
    }
    
    public function saveJob($data){
        $job = new Job;
        $jobid = $job->insertGetId($data);
        return $jobid;
    }
    
    public function updateJob($id,$data) {
        $job = new Job;
        $opnum = $job->where('id',$id)->update($data);
        if ($opnum > 0) {
            return true;
        }
        return false;
    }
    
    public function removeJob($id){
        $job = new Job;
        $opnum = $job->where('id',$id)->delete();
        if ($opnum > 0) {
            return true;
        }
        return false;
    }
    public function getJobByName($jobname) {
        $job = new Job;
        $jobres = $job->where('name',$jobname)->get()->first();
        if (!empty($jobres)) {
            return $jobres;
        }
        return false;
    }
    public function getJobById($id){
        $job = new Job;
        $jobres = $job->where('id',$id)->get()->first();
        if (!empty($jobres)) {
            return $jobres;
        }
        return false;
    }
    
    public function getTaskById($id,$jobid=null) {
        $task = new JobTask;
        if ($jobid != null) {
            $taskres = $task->where('jobid',$jobid)->orderBy('updated_at','desc')->orderby('state','desc')->get()->first();
        } else {
            $taskres = $task->where('id',$id)->get()->first();
        }
        if (!empty($taskres)) {
            return $taskres;
        }
        return null;
    }
    /**
     * Update Task Table
     */
    public function updateTaskById($id,$dataArr) {
        $task = new JobTask;
        $tasknum = $task->where('id',$id)->update($dataArr);
    }
    
    public function createviw(Request $request) {

        if ( !$this->nonauthorized() ) {
                $runview = $request->input('runview');
                //1:preview run job page
                if ($runview == 1) {
                    $jobid = $request->input('jobid');
                    if ($jobres = $this->getJobById($jobid)) {
                        $stepArr = json_decode($jobres->jobsteps,true);
                        //we use phpUnescape() to decode commandline and show on page
                        for ($i=0;$i<count($stepArr);$i++) {
                            $stepArr['step'.$i]['text'] = phpUnescape($stepArr['step'.$i]['text']);
                        }
                        //use node filter to show selected node
                        //if taskid not empty present that will view task info
                        $taskid = $request->input('taskid');
                        $finishedat = $request->input('finishedat');
                        $data = [
                            'job' => $jobres,
                            'jobstep' => $stepArr,
                            'taskid' => $taskid,
                            'finishedat' => $finishedat
                        ];
                        //print_r(phpUnescape($stepArr['step0']['text']));
                        return view('jobs.runjob',$data);
                    } else {
                        return view('error')->withMsg('Job Not Found');
                    }
                    
                }
                //2: remove job
                if ($runview == 2) {
                    $jobid = $request->input('jobid');
                    if ($this->removeJob($jobid)) {
                        return view('messageok')->withMsg('Delete Job OK');
                    } else {
                        return view('error')->withMsg('No Jobs Deleted');
                    }
                    
                }
                //3: edit job
                if ($runview == 3) {
                    $jobid = $request->input('jobid');
                    $jobdbh = new Job;
                    $jobres = $jobdbh->where('id',$jobid)->get()->first();
                    if (empty($jobres)) {
                        return view('error')->with('msg','Illegel Job ID,Job Not Found');
                    }
                    //$stepArr = json_decode($jobres->jobsteps,true);
                    $dataArr = [
                        'jobid' => $jobid,
                        'jobname' => $jobres->name,
                        'jobdes' => $jobres->description,
                        'parallel' => $jobres->parallel,
                        'steprla' => $jobres->stepRelation,
                        'steptimeout' => $jobres->stepTimeout,
                        'jobtimeout' => $jobres->jobTimeout,
                        'filter' => $jobres->nodeFilter,
                        'steps' => $jobres->jobsteps,
                        'select' => $jobres->nodeSelected,
                        'filterall' => $jobres->filterall
                    ];
                    return view('jobs.editjob',$dataArr);
                }
                return view('jobs.crtjob');
        }else{
            return view('error')->withMsg('Permission Denied');
            //echo $request->session()->all();
        }     
    }
    /**
     * Step Log Stored on Web Server
     * Get Step Log and Parse, Display on Page
     */
    public function readStepLog(Request $request) {
        $taskid = $request->input('taskid');
        $logType = $request->input('log');
        $taskres = $this->getTaskById($taskid);
        $logFile = $taskres->exec_log;
        
        switch ($logType) {
        case 'step':
            if (file_exists($logFile)) {
                //$f = @fopen($logpath,'r');
                $fileContent = file_get_contents($logFile);
                $contentArr = explode("\n",$fileContent);
                $logArr = [];
                for ($i=0;$i<count($contentArr);$i++) {
                    if (!empty($contentArr[$i])) {
                        $jsonStr = json_decode($contentArr[$i],true);
                        $jsonStr['out'] = preg_replace('/[\r\n]/','<br />',phpUnescape($jsonStr['out']));
                        $jsonStr['out'] = phpEscape($jsonStr['out']);
                        array_push($logArr,$jsonStr);
                    }
                }
            } else {
                return view('error')->withMsg('File Not Exists');
            }
            return view('jobs.steplog')->withData($logArr);
            break;
        case 'task':
            $tasklog = $taskres->tasklog;
            if (empty($tasklog)) {
                return view('messageok')->withMsg('Task Error Log Empty');
            }
            return view('error')->withMsg($tasklog);
        }
    }
    /**
     * Delete Upload File
     */
    public function removeFile(Request $request){
        $file = $request->input('filepath');
        $basename = basename($file);
        Storage::disk('local')->delete('upload/'.$basename);
        echo $basename;
    }
    /**
     * Get User Uploaded File and Store at 'storage' path
     */
    public function getUploadShell(Request $request){
        $file = $request->file('upshell');
        //var_dump($file);
        if ($file->isValid()) {
            $clientName = $file->getClientOriginalName();
            $maxSize = 15 * 1024 * 1024;
            $clientSize = $file->getClientSize();
            if ($clientSize > $maxSize or $clientSize == 0) {
                return response('File is too large, Limit 15MB.',413);
            }
            //$tmpName = $file->getFileName();
            //$realPath = $file->path();
            //$entension = $file->getClientOriginalExtension();
            //$mimeTye = $file->getMimeType();
            $newName = $clientName.".".time();
            $path = $file->move(base_path().'/storage/app/upload',$newName);
            return view('jobs.value')->with('value',$path);
        } else {
            return response('File is Invalid',415);
        }
    }
    /**
     * Read Progress from Cache
     * Read Progress from Database if Cache Expired
     */
    public function getProgress(Request $request) {
        $taskid = $request->input('taskid');
        $taskPara = $request->input('para');
        $value = null;
        if ($taskPara == "jobsum") {
            $total = Cache::get('task#'.$taskid.'total');
            //cache expired,get from database
            if (empty($total)) {
                $task = new JobTask;
                $taskres = $task->where('id',$taskid)->get()->first();
                $total = $taskres->totalnodes;
                $finishnum = $taskres->finishednodes;
                $hostfailed = $taskres->failednodes;
                $stepfailed = $taskres->failedsteps;
                $totaltime = $taskres->totaltime;
                $percent = $taskres->percent;
                $state = $taskres->state;
            } else {
                $hostfailed = Cache::get('task#'.$taskid.'hostfailed');
                $stepfailed = Cache::get('task#'.$taskid.'stepfailed');
                $finishnum = Cache::get('task#'.$taskid.'finishnum');
                $totaltime = Cache::get('task#'.$taskid.'jobtimesum');
                $percent = Cache::get('task#'.$taskid.'percent');
                $state = Cache::get('task#'.$taskid.'state');
            }
            $dataArr = [
                'total' => $total,
                'finished' => $finishnum,
                'hostfailed' => $hostfailed,
                'stepfailed' => $stepfailed,
                'totaltime' => $totaltime,
                'percent' => $percent,
                'state' => $state
            ];
            $value = json_encode($dataArr);
        } else {
            $value = Cache::get('task#'.$taskid.$taskPara);
            //if cache expired use database value
            if (empty($value)) {
                $task = new JobTask;
                $taskres = $task->select($taskPara)->where('id',$taskid)->get()->first();
                $value = $taskres->$taskPara;
            }
        }
        return view('jobs.value')->withValue($value);
    }
    
    public function cacPercent($count,$sum){
        return round(($count / $sum) * 100);
    }
    
    public function microtime_float()
    {
        list($usec, $sec) = explode(" ", microtime());
        return ((float)$usec + (float)$sec);
    }
    /**
     * Use proc_open to exectue php binary
     * According to Task Information,Set Timeout
     * Write Progress Data to Cache
     */
    public function runCliCmd($cmd,$taskInfo){
        $output = ['state' => 'normal','out' => '','elapse' => 0];
        $descriptorspec = array(
            0 => array('pipe', 'r'),
            1 => array('pipe', 'w'),
            2 => array('pipe', 'w')
        );
        
        $process = proc_open($cmd, $descriptorspec, $pipes);
        //Record Start time
        
        $start = $this->microtime_float();
        if(!is_resource($process)) {
            //Open Proc Failed
            $output['state'] = 'error';
            $output['out'] = date("Y-m-d H:i:s")." Localhost Task is not started successfully";
            $this->removeTempFile($taskInfo['debug'],$taskInfo['infoFile']);
        } else {
            $timeoutSecond = $taskInfo['jobtimeout'];

            //set job percent 0%
            $taskid = $taskInfo['taskid'];
            $nodesum = $taskInfo['nodenum'];
            Cache::put('task#'.$taskid.'percent',0,$this->cacheExpire);
            Cache::put('task#'.$taskid.'hostfailed',0,$this->cacheExpire);
            Cache::put('task#'.$taskid.'stepfailed',0,$this->cacheExpire);
            Cache::put('task#'.$taskid.'jobtimesum',0,$this->cacheExpire);
            $count = 0;
            $failCount = 0;
            $stepFailCount = 0;
            $offset = 0;
            $stream = '';
            stream_set_blocking($pipes[1],false);
            $status = proc_get_status($process);
            
            $updateArr = ['state' => 'Preparing'];
            $taskdb = $this->updateTaskById($taskid,$updateArr);
            Cache::put('task#'.$taskid.'state','Preparing',$this->cacheExpire);
            Cache::put('task#'.$taskid.'pid',$status['pid'],$this->cacheExpire);
            Cache::put('task#'.$taskid.'total',$nodesum,$this->cacheExpire);
            /**
             * proc encounter error if status not running
             */
            if ($status['running'] != true) {
                $line = fgets($pipes[1]);
                $line = $line."\n".fgets($pipes[2]);
                $output['state'] = 'error';
                $output['out'] = date("Y-m-d H:i:s").' Localhost Task is Error,output: '.$line;
                $output['elapse'] = $offset;
                $this->removeTempFile($taskInfo['debug'],$taskInfo['infoFile']);
            }
            
            $ppid = $status['pid'];
            while($status['running'] == true) {
                Cache::put('task#'.$taskid.'state','Running',$this->cacheExpire);
                $curr = $this->microtime_float();
                $offset = round($curr - $start,3);
                Cache::put('task#'.$taskid.'jobtimesum',$offset,$this->cacheExpire);
                if ($offset > $timeoutSecond and $timeoutSecond != 0) {
                    posix_kill($ppid, 9);
                    $output['state'] = "timeout";
                    $output['out'] = date("Y-m-d H:i:s").' Localhost Task is Timeout';
                    $output['elapse'] = $offset;
                    $this->removeTempFile($taskInfo['debug'],$taskInfo['infoFile']);
                    break;
                }
                /**
                 * One Line per Host
                 * Summary Information is in Cache
                 */
                $line = fgets($pipes[1]);
                if (!empty($line)) {
                    $count++;
                    $finArr = json_decode($line,true);
                    /**
                     * If Cli Command Execute Error,json_decode will error
                     * So Termiate it when error
                     */
                    if (json_last_error()) {
                        posix_kill($ppid, 9);
                        $output['state'] = 'error';
                        $output['out'] = date("Y-m-d H:i:s").' Localhost Task is Error,output: '.$line;
                        $output['elapse'] = $offset;
                        $this->removeTempFile($taskInfo['debug'],$taskInfo['infoFile']);
                        break;
                    }
                    
                    $percent = $this->cacPercent($count,$nodesum);
                    if ($finArr['jobstate'] != "success") {
                        $failCount++;
                    }
                    $stepFailCount += $finArr['stepfail'];
                    //$jobWholeTime += $finArr['jobsumElapse'];
                    Cache::put('task#'.$taskid.'hostfailed',$failCount,$this->cacheExpire);
                    Cache::put('task#'.$taskid.'stepfailed',$failCount,$this->cacheExpire);
                    Cache::put('task#'.$taskid.'finishnum',$count,$this->cacheExpire);
                    Cache::put('task#'.$taskid.'percent',$percent,$this->cacheExpire);
                    //Cache::put('task#'.$taskid.'jobtimesum',$jobWholeTime,$this->cacheExpire);
                    $stream = $stream.$line;
                    
                }
                /**
                 * Task is running,reset state in database
                 */
                if ($count == 0) {
                    $dataArr = [
                        'state' => 'Running',
                        'updated_at' => date("Y-m-d H:i:s")
                    ];
                    $this->updateTaskById($taskid,$dataArr);
                }
                $status = proc_get_status($process);
            }
            //While Finished,proc finished,proc timeout
            
        }
        
        /**
        * Record Output,Close Pipe
        */
        switch ($output['state']) {
        case 'normal':
            $output['out'] = $stream;
            $output['elapse'] = $offset;
            Cache::put('task#'.$taskid.'state','Finished',$this->cacheExpire);
            break;
        case 'timeout':
            Cache::put('task#'.$taskid.'state','Timeout',$this->cacheExpire);
            Cache::put('task#'.$taskid.'percent',100,$this->cacheExpire);
            break;
        case 'error':
            Cache::put('task#'.$taskid.'state','Error',$this->cacheExpire);
            Cache::put('task#'.$taskid.'percent',100,$this->cacheExpire);
            break;
        }
        fclose($pipes[1]);
        fclose($pipes[2]);
        proc_close($process);
        return $output;
    }
    /**
     * Not Run Job Immediately
     * Get Task Information and Write to Cache
     */
    public function jobExecutor(Request $request){
        $sshkey = new Sshkey;
        $job = new Job;
        $jobTask = new JobTask;
        $config = new Config;
        
        $jobid = $request->input('jobid');
        $docroot = base_path();
        $dispatcher = $docroot."/resources/shell/inner/jobDispatcher.php";
        /**
         * Job parallel,Timeout,Relation,LogPath
         */
        $jobres = $job->where('id',$jobid)->get()->first();
        
        $parallel = $jobres->parallel;
        $timeout = $jobres->stepTimeout;
        $rela = $jobres->stepRelation;
        $logpath = $jobres->logpath;
        $jobtimeout = $jobres->jobTimeout;
        /**
         * Node Check String,Option Debug
         * All Checked,will Use Filter
         * or Use Checked Input Value String
         */
        $nodeString = $request->input('nodestring');
        $byuser = $request->session()->get('username');
        $debug = $request->input('debug');
        $allflag = $request->input('allflag');
        
        $date = date("Y-m-d-H-i-s");
        $joblog = $logpath."/".$jobid.".jobtask.".$date.".out";
        $sysLog = $logpath."/syslog.out";
        $stepLog = $logpath."/".$jobid.".steplog.".$date.".out";
        $sshres = $sshkey->select('username','pubkey_path','privekey_path')->get()->first();        
        
        $date = date("Y-m-d H:i:s");
        $data = [
            'jobid' => $jobid,
            'state' => 'Starting',
            'exec_log' => $stepLog,
            'byuser' => $byuser,
            'created_at' => $date,
            'updated_at' => $date
        ];
        $taskid = $jobTask->insertGetId($data);

        $sshinfo = [
            'user' => $sshres['username'],
            'pubkey' => $sshres['pubkey_path'],
            'prikey' => $sshres['privekey_path']
        ];
        
        $jobstep = json_decode($jobres->jobsteps,true);
        
        $nodeString = phpUnescape($nodeString);
        $nodeString = preg_replace('/,$/','',$nodeString);
        $noderes = explode(',',$nodeString);
        $nodesum = count($noderes);
        if (empty($nodeString)) {
            $nodesum = 0;
        }
        /**
         * Parse Node String if User Checked at Run-time
         * or Use Filter Input
         */
        $h = 1;
        for ($i=0;$i<$nodesum;$i++) {
            $nodeinfo = explode(':',$noderes[$i]);
            $hostArr = [
                'hostname' => $nodeinfo[0],
                'ipaddr' => $nodeinfo[1],
                'portok' => $nodeinfo[2],
                'execuser' => $nodeinfo[3]
            ];
            if ($h>1) {
                $hostlist = array_add($hostlist,$h,$hostArr);
            } else {
                $hostlist = array($h => $hostArr);
            }
            $h++;
        }
        /**
         * Write all information to a file
         * dispatcher will use this file to put job
         */
        $hostFile = $docroot."/storage/app/taskinfo".time();
        $taskInfo = [
            'taskid' => $taskid,
            'jobid' => $jobid,
            'joblog' => $joblog,
            'steps' => $jobstep,
            'steprela' => $rela,
            'syslog' => $sysLog,
            'steplog' => $stepLog,
            'steptimeout' => $timeout,
            'jobtimeout' => $jobtimeout,
            'nodenum' => $nodesum,
            'infoFile' => $hostFile,
            'debug' => $debug,
            'parallel' => $parallel
        ];
        
        $fLine = json_encode($hostlist)."\n".json_encode($sshinfo)."\n".json_encode($taskInfo);
        $flag = file_put_contents($hostFile,$fLine,FILE_APPEND | LOCK_EX);
        if ($flag == false) {
            return view('error')->withMsg('Write File Failed');
        }
        /**
         * We use php cli to run script
         * Use config set in database first if not null
         * or Guess default php binary file in bin/sbin path
         * or return error
         */
        $phpBinPath = [
            '/usr/bin',
            '/usr/sbin',
            '/usr/local/bin',
            '/usr/local/sbin',
            '/usr/local/php/bin',
            '/usr/local/php/sbin'
        ];
        $conf = $config->where('id',1)->get()->first();
        $phpBin = $conf->php_bin;
        if (empty($phpBin)) {
            foreach($phpBinPath as $path) {
                $phpBin = file_exists($path.'/php') ? $path.'/php' : '';
                if (!empty($phpBin)){
                    break;
                }
            }
        }
        if (empty($phpBin)) {
            return view('error')->with('msg','Cannot Find php binary, You can set in configure page');
        }
        $commandline = $phpBin." ".$dispatcher." ".$hostFile;
        Cache::put('taskid'.$taskid.'comm',$commandline,5);
        Cache::put('taskid'.$taskid.'taskinfo',$taskInfo,5);
        return view('jobs.jobprogress')->withTaskid($taskid);

    }
    /**
     * Function:jobGoNow actually execute job
     */
    public function jobGoNow(Request $request) {
        $taskid = $request->input('taskid');
        $commandLine = Cache::get('taskid'.$taskid.'comm');
        $taskInfo = Cache::get('taskid'.$taskid.'taskinfo');
        $output = $this->runCliCmd($commandLine,$taskInfo);
        /**
         * update task table after finished
         */
        $total = Cache::get('task#'.$taskid.'total');
        $hostfailed = Cache::get('task#'.$taskid.'hostfailed');
        $stepfailed = Cache::get('task#'.$taskid.'stepfailed');
        $finishnum = Cache::get('task#'.$taskid.'finishnum');
        $totaltime = Cache::get('task#'.$taskid.'jobtimesum');
        switch ($output['state']) {
        case 'timeout':
            $updateArr = [
                'state' => 'Timeout',
                'percent' => 100,
                'totalnodes' => $total,
                'finishednodes' => 0,
                'failednodes' => 0,
                'failedsteps' => 0,
                'tasklog' => $output['out'],
                'totaltime' => $totaltime
            ];
            break;
        case 'error':
            $updateArr = [
                'state' => 'Error',
                'percent' => 100,
                'totalnodes' => $total,
                'finishednodes' => 0,
                'failednodes' => 0,
                'failedsteps' => 0,
                'tasklog' => $output['out'],
                'totaltime' => $totaltime
            ];
            break;
        case 'normal':
            $updateArr = [
                'state' => 'Finished',
                'percent' => 100,
                'totalnodes' => $total,
                'finishednodes' => $finishnum,
                'failednodes' => $hostfailed,
                'failedsteps' => $stepfailed,
                'totaltime' => $totaltime
            ];
            break;
        }
        $tasknum = $this->updateTaskById($taskid,$updateArr);

        /**
         * Task finished, clear cache data
         */
        Cache::forget('task#'.$taskid.'percent');
        Cache::forget('task#'.$taskid.'total');
        Cache::forget('task#'.$taskid.'hostfailed');
        Cache::forget('task#'.$taskid.'stepfailed');
        Cache::forget('task#'.$taskid.'finishnum');
        Cache::forget('task#'.$taskid.'jobtimesum');
        Cache::forget('task#'.$taskid.'state');
               
    }
    /**
     * User Cancel task
     */
    public function killJobTask(Request $request) {
        $taskid = $request->input('taskid');
        $taskPid = Cache::get('task#'.$taskid.'pid');
        if (!empty($taskPid)) {
            posix_kill($taskPid, 9);
        }
        $total = Cache::get('task#'.$taskid.'total');
        $hostfailed = Cache::get('task#'.$taskid.'hostfailed');
        $stepfailed = Cache::get('task#'.$taskid.'stepfailed');
        $finishnum = Cache::get('task#'.$taskid.'finishnum');
        $totaltime = Cache::get('task#'.$taskid.'jobtimesum');
        $updateArr = [
            'state' => 'Canceled',
            'percent' => 100,
            'totalnodes' => $total,
            'finishednodes' => $finishnum,
            'failednodes' => $hostfailed,
            'failedsteps' => $stepfailed,
            'tasklog' => date("Y-m-d H:i:s").' Localhost User Canceled This Task',
            'totaltime' => $totaltime
        ];
        $tasknum = $this->updateTaskById($taskid,$updateArr);
        /**
         * Task Killed,Clear Cache Data
         */
        Cache::forget('task#'.$taskid.'percent');
        Cache::forget('task#'.$taskid.'total');
        Cache::forget('task#'.$taskid.'hostfailed');
        Cache::forget('task#'.$taskid.'stepfailed');
        Cache::forget('task#'.$taskid.'finishnum');
        Cache::forget('task#'.$taskid.'jobtimesum');
        Cache::forget('task#'.$taskid.'state');
    }
    /**
     * Click 'Last Task' on Job Page
     * Return View Task Page
     */
    public function viewTask(Request $request) {
        $taskid = $request->input('taskid');
        $jobid = $request->input('jobid');
        $taskFinish = $request->input('finishedat');
        
        if (!empty($jobid) and empty($taskid)) {
            $taskres = $this->getTaskById('',$jobid);
            if (empty($taskres)) {
                return view('error')->withMsg('This Job not Executed yet');
            }
            $taskid = $taskres->id;
            $taskFinish = $taskres->updated_at;
        } else {
            $taskres = $this->getTaskById($taskid);
            $taskFinish = $taskres->updated_at;
        }
        $data = [
            'taskid' => $taskid,
            'userop' => 'view',
            'finishedat' => $taskFinish
        ];
        return view('jobs.jobprogress',$data);
    }
    
    public function importJobYaml() {
        
    }
    
    public function exportJobYaml(Request $request) {
        $validator = Validator::make($request->all(),[
                'jobid' => 'required|numeric'
        ]);
        if ($validator->fails()) {
            return view('error')->with('msg','Illegel Job ID');
        }
        $jobID = $request->input('jobid');
        $jobRes = $this->getJobById($jobID);
        $expFileName = $jobRes->name.".yaml.".time();
        $jobStepArr = json_decode($jobRes->jobsteps,true);
        foreach ($jobStepArr as $key => $value) {
            $jobStepArr[$key]['text'] = phpUnescape($jobStepArr[$key]['text']);
        }
        $toYamlArr = [
            'name' => $jobRes->name,
            'description' => $jobRes->description,
            'steps' => $jobStepArr,
            'parallel' => $jobRes->parallel,
            'steprla' => $jobRes->stepRelation,
            'stepTimeout' => $jobRes->stepTimeout,
            'jobTimeout' => $jobRes->jobTimeout,
            'filter' => $jobRes->nodeFilter,
            'nodeSelected' => $jobRes->nodeSelected,
            'nodeAll' => $jobRes->filterall,
            'logpath' => $jobRes->logpath
        ];
        $content = yaml_emit($toYamlArr,YAML_UTF8_ENCODING,YAML_LN_BREAK);
        $content = preg_replace('/---\n/','',$content);
        $content = preg_replace('/\.\.\.\n/','',$content);
        Storage::disk('download')->put($expFileName,$content);
        $data = [
            'filename' => $jobRes->name.'.yaml',
            'realname' => $expFileName
        ];
        /**
         * Wait user to click download
         */
        return view('jobs.downloadYaml',$data);
    }
    
    public function downloadJobYaml(Request $request) {
        $realName = $request->input('exprealname');
        $fileName = $request->input('filename');
        if (Storage::disk('download')->has($realName)) {
            $realPath = storage_path('app/download/'.$realName);
            $headers = [
                'Content-Type' => 'application/text'
            ];
            return response()->download($realPath,$fileName,$headers);
        } else {
            return view('error')->with('msg','File has Expired');
        }
    }
    
    /**
     * return job execute summary charts data
     */
    public function JobExecPieData(Request $request) {
        $jobview = new ViwJobExec;
        $jobviewres = $jobview->orderby('times','desc')->paginate(10);
        $catag = [];
        $data = [];
        foreach($jobviewres as $jobv) {
            array_push($catag,$jobv->name);
            $dataTmp = [
                'value' => $jobv->times,
                'name' => $jobv->name
            ];
            array_push($data,$dataTmp);
        }
        $returnData = [
            'names' => $catag,
            'datas' => $data
        ];
        return json_encode($returnData);
    }
    /**
     * return job state summary charts data
     */
    public function JobStatePieData(Request $request) {
        $jobview = new ViwJobState;
        $jobviewres = $jobview->orderby('num','desc')->get();
        $catag = [];
        $data = [];
        $color = [];
        foreach($jobviewres as $jobv) {
            array_push($catag,$jobv->state);
            $dataTmp = [
                'value' => $jobv->num,
                'name' => $jobv->state
            ];
            array_push($data,$dataTmp);
            switch ($jobv->state) {
                case 'Finished':
                    array_push($color,'#66CD00');break;
                case 'Canceled':
                    array_push($color,'#5CACEE');break;
                case 'Error':
                    array_push($color,'#FF0000');break;
                case 'Timeout':
                    array_push($color,'#FFFF00');break;
            }
        }
        $returnData = [
            'names' => $catag,
            'datas' => $data,
            'color' => $color
        ];
        return json_encode($returnData);
    }
    
    public function removeTempFile($debug,$file) {    
        if (empty($debug) or $debug == false or $debug == 0) {
            unlink($file);
        }
    }
}
