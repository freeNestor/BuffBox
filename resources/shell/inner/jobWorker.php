<?php
define("APP_LIB_PATH",dirname(__FILE__)."/../../../app/Libs");
include_once(APP_LIB_PATH."/Net/SSH2.php");
include_once(APP_LIB_PATH."/Net/SCP.php");
include_once(APP_LIB_PATH."/Net/SFTP.php");
include_once(APP_LIB_PATH."/Crypt/RSA.php");
include_once(APP_LIB_PATH."/functions.php");

class jobWorker extends Thread {
	protected $id = '';
	protected $ip = '';
    protected $hostname = '';
    protected $port = 22;
    protected $remoteUser = 'root';
    //protected $pubkey = '';
    protected $prikey = '';
	//protected $timeElapse = 0 ;
    protected $timeOut = 0;
    protected $taskid = '';
    protected $jobid = '';
    protected $joblog = '';
    protected $steplog = '';
    protected $syslog = '';
    protected $workerTmpLog = '';
    protected $stepRela = 1;
    protected $comm = '';
	//protected $count = 3;

	protected $runnig = false;
    protected $log = '';


	public function __construct($id) {
		$this->id = $id;
		$this->running = true;
	}
    /**
     * Object convert to Array
     */
    public function obj2arr($obj) {
        if (is_object($obj)) {
            foreach ($obj as $key => $value) {
                $array[$key] = $value;
            }
        } else {
            $array = $obj;
        }
        return $array;
    }
    
    public function write2joblog($log,$content) {
        //$f = fopen($log,'a');
        $writeOK = file_put_contents($log,$content,FILE_APPEND);
        if (!$writeOK) {
            return false;
        } else {
            return true;
        }
    }
    /**
     * Merge source file to target file
     */
    public function file2file($target,$source) {
        //$t = fopen($target,'a');
        //$s = fopen($source,'r');
        $writeOK = file_put_contents($target,file_get_contents($source),FILE_APPEND);
        if (!$writeOK) {
            return false;
        } else {
            return true;
        }
    }
    
    public function microtime_float()
    {
        list($usec, $sec) = explode(" ", microtime());
        return ((float)$usec + (float)$sec);
    }
    
    /**
     * start ssh channel
     * if success return ssh channel object
     * ip: ip address
     * port: default 22
     * user: default root
     * key: private key for login
     */
    public function startSSH($ip,$port=22,$user='root',$key=''){
        $error = '';
        $sshCon = new Net_SSH2($ip,$port);
        $prikey = new Crypt_RSA();
        
        if (!empty($key)) {
            if(file_exists($key)) {

                $prikey->loadKey(file_get_contents($key));
                
                if(@$sshCon->login($user,$prikey)){
                    
                    return $sshCon;
                } else {
                    $error['message'] = "Authenticated Failed, user: ".$user.", port: ".$port.",private key: ".$key;
                }
            } else {
                $error['message'] = "Private Key File not Found";
            }
        } else {
            $error['message'] = "Private Key not Given";
        }
        return $error['message'];
    }
    
    public function closeSSH($sshCon){
        if (is_object($sshCon)) {
            $sshCon->disconnect();
        }
        unset($sshCon);
    }
    /**
     * Use ssh channel to execute remote command
     */
    public function runRemoteCmd($sshCon,$cmd,$timeout){
        $output = ['out' => '','timeElapse' => 0];
        
        //set command timeout
        $sshCon->setTimeout($timeout);
        $timeStart = $this->microtime_float();
        $output['out'] = $sshCon->exec($cmd);
        $output['timeElapse'] = round($this->microtime_float() - $timeStart,3);
        if ($sshCon->isTimeout()) {
            $output['out'] = "timeout";
        }
        
        return $output;
    }
    
    /**
     * Execute command at localhost
     * Use proc_open() function to control
     */
    public function runLocalCmd($cmd,$timeout){
        $output = ['out' => '','timeElapse' => 0];
        $descriptorspec = array(
            0 => array('pipe', 'r'),
            1 => array('pipe', 'w'),
            2 => array('pipe', 'w')
        );
        //Open Proc
        $process = proc_open($cmd, $descriptorspec, $pipes);
        //Record Start time
        $start = $this->microtime_float();
        if(!is_resource($process)) {
            //Open Proc Failed
            $output['out'] = "error";
        } else {
            //Get Proc Status and Timeout if set
            $status = proc_get_status($process);
            while($status['running'] == true) {
                $curr = $this->microtime_float();
                $offset = round($curr - $start,3);
                //timeout = 0 is not timeout
                if ($offset > $timeout and $timeout != 0) {
                    $ppid = $status['pid'];
                    posix_kill($ppid, 9);
                    $output['out'] = "timeout";
                    break;
                }
                $status = proc_get_status($process);
                //Proc Finished
                if ($status['running'] == false) {
                    $output['out'] = stream_get_contents($pipes[1]);
                    $output['timeElapse'] = $offset;
                    fclose($pipes[1]);
                    fclose($pipes[2]);
                }
            }
        }
        proc_close($process);
        return $output;
    }
    
    public function startSFTP($host,$port,$user,$key) {
        $sftp = new Net_SFTP($host,$port);
        $error = '';
        $prikey = new Crypt_RSA();
        
        if (!empty($key)) {
            if(file_exists($key)) {
                $prikey->loadKey(file_get_contents($key));
                if(@$sftp->login($user,$prikey)){
                    return $sftp;
                } else {
                    $error['message'] = "Authenticated Failed, user: ".$user.", port: ".$port.",private key: ".$key;
                }
            } else {
                $error['message'] = "Private Key File not Found";
            }
        } else {
            $error['message'] = "Private Key not Given";
        }
        return $error['message'];
    }
    
    public function sftpFile($ch,$local,$path,$fileName) {
        $ch->mkdir($path,-1,true);
        $error = $ch->getLastSFTPError();
        if (!empty($error)) {
            return $error;
        }
        $ch->put($path."/".$fileName,$local,'NET_SFTP_LOCAL_FILE');
        $error = $ch->getLastSFTPError();
        if (empty($error)) {
            return true;
        }
        return $error;
    }
    //put file to remote use scp
    public function uploadFile($sshCon,$remote,$local) {
        $scp = new Net_SCP($sshCon);
        if (@$scp->put($remote,$local,'NET_SCP_LOCAL_FILE')) {
            return true;
        } else {
            return false;
        }
    }
    /**
     * Thread main function
     */
	public function run(){
		while ($this->running) {
			if ($this->ip != '' and $this->comm != '') {
			     //read job steps and decode it
                $steparr = json_decode($this->comm,true);
                //echo count($steparr)."\n";
                $date = date("Y-m-d H:i:s");
                //taskid,jobid,jobstate,stepnum,stepSuccessRate,host,jobsumElapse
                $jobstatis = [
                    'date' => $date,
                    'host' => $this->hostname,
                    'taskid' => $this->taskid,
                    'jobid' => $this->jobid,
                    'jobstate' => 'success',
                    'stepnum' => count($steparr),
                    'stepfail' => 0,
                    'stepSuccessRate' => 0,
                    'jobsumElapse' => 0
                ];
                $msg = [
                    'host' => $this->hostname,
                    'state' => "sucess",
                    'date' => $date,
                    'msg' => "",
                    'out' => "",
                    'elapse' => 0
                ];
                /**
                 * Set worker temporary log
                 */
                $this->workerTmpLog = $this->joblog.".".$this->id.".tmp";
                
                $ssh = $this->startSSH($this->ip,$this->port,$this->remoteUser,$this->prikey);
                /**
                 * If ssh connect failed will obtain string
                 * Success if Object return
                 */
                if (is_string($ssh)) {
                    $msg["state"] = "error";
                    $msg["msg"] = $ssh;
                    //now job fail on this ip
                    $date = date("Y-m-d H:i:s");
                    $jobstatis['date'] = $date;
                    $jobstatis['jobstate'] = 'fail';
                    $jobstatis['stepfail'] = $jobstatis['stepnum'];
                    $jobstatis['jobsumElapse'] = 0;

                    //write output to step temporary log
                    $this->log = "{\"date\":\"".$msg["date"]."\",\"host\":\"".$msg["host"]."\",\"stepstate\":\"".$msg["state"]."\",\"msg\":\"".$msg["msg"]."\",\"out\":\"".$msg["out"]."\",\"Elapse\":\"".$msg['elapse']."\"}\n";
                    if (!$this->write2joblog($this->workerTmpLog,$this->log)) {
                        $date = date("Y-m-d H:i:s");
                        $syserr = $msg['date']." sysErr: write ".$this->workerTmpLog." file failed\n";
                        $this->write2joblog($this->syslog,$syserr);
                    }
                } else {
                        //task state flag, if one step fail, task marked portion on this ip
                        $taskState = 'success';
                        //step fail count
                        $stepFailCount = 0;
                        //step time elapse summary
                        $stepTimeSum = 0;
                        //read steps loop
                        while (!empty($steparr)) {
                            //read one step
                            
                            $step = array_shift($steparr);
                            //$step = $this->obj2arr($stepTmp);
                            $mycomm = trim($step['text']);
                            $type = $step['type'];
                            //use to retrive return state
                            $hashval = "retval".time();
                            /**
                             * Step is distributing file to node
                             */
                            if ($type == "LocalFile" or $type == "RemoteFile") {
                                $sftp = $this->startSFTP($this->ip,$this->port,$this->remoteUser,$this->prikey);
                                if (is_string($sftp)) {
                                    $msg["state"] = "error";
                                    $msg["msg"] = $sftp;
                                    //now job fail on this ip
                                    $date = date("Y-m-d H:i:s");
                                    $jobstatis['date'] = $date;
                                    $jobstatis['jobstate'] = 'fail';
                                    $jobstatis['stepfail'] = $jobstatis['stepnum'];
                                    $jobstatis['jobsumElapse'] = 0;
                
                                    //write output to step temporary log
                                    $this->log = "{\"date\":\"".$msg["date"]."\",\"host\":\"".$msg["host"]."\",\"stepstate\":\"".$msg["state"]."\",\"msg\":\"".$msg["msg"]."\",\"out\":\"".$msg["out"]."\",\"Elapse\":\"".$msg['elapse']."\"}\n";
                                    if (!$this->write2joblog($this->workerTmpLog,$this->log)) {
                                        $date = date("Y-m-d H:i:s");
                                        $syserr = $msg['date']." sysErr: write ".$this->workerTmpLog." file failed\n";
                                        $this->write2joblog($this->syslog,$syserr);
                                    }
                                } else {
                                    $localf = $mycomm;
                                    $remotePath = $step['targetPath'];
                                    //$remotef = $remotePath."/".basename($mycomm);
                                    if ($this->sftpFile($sftp,$localf,$remotePath,basename($mycomm)) != true) {
                                        $date = date("Y-m-d H:i:s");
                                        $msg["date"] = $date;
                                        $msg["state"] = "error";
                                        $msg["msg"] = "Upload Script Failed";
                                        
                                        $stepFailCount++;
                                        $stepTimeSum += 0;
                                    } else {
                                        $date = date("Y-m-d H:i:s");
                                        $msg["date"] = $date;
                                        $msg["state"] = "success";
                                        $msg["msg"] = "Upload Script Successfully";
                                    }
                                }
                            }
                            /**
                             * Step is local script
                             * first should upload shell to remote node
                             * remote path is /tmp
                             */
                            if ($type == "LocalScript") {
                                $mycommRaw = phpUnescape($mycomm);
                                $localf = $mycommRaw;
                                $remotef = "/tmp/".basename($mycommRaw).time();
                                if (!$this->uploadFile($ssh,$remotef,$localf)) {
                                    $date = date("Y-m-d H:i:s");
                                    $msg["date"] = $date;
                                    $msg["state"] = "error";
                                    $msg["msg"] = "Upload Script Failed";
                                    
                                    $stepFailCount++;
                                    $stepTimeSum += 0;
                                } else {
                                    
                                    $cmd = "sh ".$remotef." ; echo ".$hashval."$?";
                                    //run command remote
                                    $output = $this->runRemoteCmd($ssh,$cmd,$this->timeOut);
                                    $date = date("Y-m-d H:i:s");
                                    if ($output['out'] == "error") {
                                        $msg["date"] = $date;
                                        $msg["state"] = "error";
                                        $msg["msg"] = "Encounter System Error";
                                        $msg['out'] = "";
                                        $msg["elapse"] = $output['timeElapse'];
                                        $stepFailCount++;
                                    } else if ($output['out'] == "timeout") {
                                        $msg["date"] = $date;
                                        $msg["state"] = "fail";
                                        $msg["msg"] = "Step ".$remotef." Execute Timeout";
                                        $msg['out'] = "";
                                        $msg["elapse"] = $output['timeElapse'];
                                        $stepFailCount++;
                                    } else {
                                    
                                        $resArray = explode($hashval,$output['out']);
                                        //if remote work ok?
                                        $retval = trim($resArray[1]);
                                        
                                        if ($retval != "0") {
                                            $msg["date"] = $date;
                                            $msg["state"] = "fail";
                                            $msg["msg"] = "[".$remotef."] Return Non-zero[state=".$retval."]";
                                            $msg["out"] = phpEscape(trim($resArray[0]));
                                            $msg["elapse"] = $output['timeElapse'];
                                            $stepFailCount++;
                                        } else {
                                            $msg["date"] = $date;
                                            $msg["state"] = "success";
                                            $msg["msg"] = "[".$remotef."] Execute Sucessfully[state=".$retval."]";
                                            $msg["out"] = phpEscape(trim($resArray[0]));
                                            $msg["elapse"] = $output['timeElapse'];
                                        }
                                        
                                    }//output process end
                                    $stepTimeSum += $output['timeElapse'];
                                }//upload file end
                            }
                            if ($type == "RemoteScript") {
                                    $mycommRaw = phpUnescape($mycomm);
                                    $cmd = $mycommRaw." ; echo ".$hashval."$?";
                                    $output = $this->runRemoteCmd($ssh,$cmd,$this->timeOut);
                                    $date = date("Y-m-d H:i:s");
                                    if ($output['out'] == "error") {
                                        $msg["date"] = $date;
                                        $msg["state"] = "error";
                                        $msg["msg"] = "Encounter System Error";
                                        $msg['out'] = "";
                                        $msg["elapse"] = $output['timeElapse'];
                                        $stepFailCount++;
                                    } else if ($output['out'] == "timeout") {
                                        $msg["date"] = $date;
                                        $msg["state"] = "fail";
                                        $msg["msg"] = "Step ".$remotef." Execute Timeout";
                                        $msg['out'] = "";
                                        $msg["elapse"] = $output['timeElapse'];
                                        $stepFailCount++;
                                    } else {

                                        $resArray = explode($hashval,$output['out']);
                                        //if work ok?
                                        $retval = trim($resArray[1]);
                                        
                                        if ($retval != "0") {
                                            $msg["date"] = $date;
                                            $msg["state"] = "fail";
                                            $msg["msg"] = "[".$mycomm."] Return Non-zero[state=".$retval."]";
                                            $msg["out"] = phpEscape(trim($resArray[0]));
                                            $msg["elapse"] = $output['timeElapse'];
                                            $stepFailCount++;
                                        } else {
                                            $msg["date"] = $date;
                                            $msg["state"] = "success";
                                            $msg["msg"] = "[".$mycomm."] Execute Sucessfully[state=".$retval."]";
                                            $msg["out"] = phpEscape(trim($resArray[0]));
                                            $msg["elapse"] = $output['timeElapse'];
                                        }
                                    }//output process end
                                    $stepTimeSum += $output['timeElapse'];
                            }
                            //step type shell
                            if ($type == "Command") {
                                    $mycommRaw = phpUnescape($mycomm);
                                    $cmd = $mycommRaw." ; echo ".$hashval."$?";
                                    $output = $this->runRemoteCmd($ssh,$cmd,$this->timeOut);
                                    //print_r($output);
                                    $date = date("Y-m-d H:i:s");
                                    if ($output['out'] == "error") {
                                        $msg["date"] = $date;
                                        $msg["state"] = "error";
                                        $msg["msg"] = "Encounter System Error";
                                        $msg['out'] = "";
                                        $msg["elapse"] = $output['timeElapse'];
                                        $stepFailCount++;
                                    } else if ($output['out'] == "timeout") {
                                        $msg["date"] = $date;
                                        $msg["state"] = "fail";
                                        $msg["msg"] = "Step ".$remotef." Execute Timeout";
                                        $msg['out'] = "";
                                        $msg["elapse"] = $output['timeElapse'];
                                        $stepFailCount++;
                                    } else {

                                        $resArray = explode($hashval,$output['out']);
                                        //if work ok?
                                        
                                        $retval = trim($resArray[1]);
                                        //print_r($resArray);
                                        if ($retval != "0") {
                                            $msg["date"] = $date;
                                            $msg["state"] = "fail";
                                            $msg["msg"] = "[".$mycomm."] Return Non-zero[state=".$retval."]";
                                            $msg["out"] = phpEscape(trim($resArray[0]));
                                            $msg["elapse"] = $output['timeElapse'];
                                            $stepFailCount++;
                                        } else {
                                            $msg["date"] = $date;
                                            $msg["state"] = "success";
                                            $msg["msg"] = "[".$mycomm."] Execute Sucessfully[state=".$retval."]";
                                            $msg["out"] = phpEscape(trim($resArray[0]));
                                            $msg["elapse"] = $output['timeElapse'];
                                        }
                                    }//output process end
                                    $stepTimeSum += $output['timeElapse'];
                            } //shell if end
                            //write step log to file
                            //message format
                            
                            $this->log = "{\"date\":\"".$msg["date"]."\",\"host\":\"".$msg["host"]."\",\"stepstate\":\"".$msg["state"]."\",\"msg\":\"".$msg["msg"]."\",\"out\":\"".$msg["out"]."\",\"Elapse\":\"".$msg['elapse']."\"}\n";
                            if (!$this->write2joblog($this->workerTmpLog,$this->log)) {
                                $date = date("Y-m-d H:i:s");
                                $syserr = $msg['date']." sysErr: write ".$this->workerTmpLog." file failed\n";
                                $this->write2joblog($this->syslog,$syserr);
                            }
                            //if step fail and stop next step
                            if ($msg['state'] != "success" and $this->stepRela == 1) {
                                break;
                            }
                            //echo $this->log."\n";
                        } //while end / finish all steps
                        //task statistics
                        $stepSuccessRate = round((($jobstatis['stepnum'] - $stepFailCount) / $jobstatis['stepnum']) * 100);
                        if ($stepSuccessRate == 0) {
                            $stateFlag = 'fail';
                        } else if ($stepSuccessRate == 100) {
                            $stateFlag = 'success';
                        } else {
                            $stateFlag = 'Portion';
                        }
                        $date = date("Y-m-d H:i:s");
                        $jobstatis['date'] = $date;
                        $jobstatis['jobstate'] = $stateFlag;
                        $jobstatis['stepfail'] = $stepFailCount;
                        $jobstatis['stepSuccessRate'] = $stepSuccessRate;
                        $jobstatis['jobsumElapse'] = $stepTimeSum;
                        
                }//ssh connect end
                //output job state summary
                //taskid,jobid,jobstate,stepnum,stepSucessRate,host,jobsumElapse
                $taskString = json_encode($jobstatis);
                echo $taskString."\n";
                //merge temporary log to step log
                $date = date("Y-m-d H:i:s");
                if (!$this->file2file($this->steplog,$this->workerTmpLog)) {
                    $syserr = $msg['date']." sysErr: write ".$this->steplog." file failed\n";
                    $this->write2joblog($this->syslog,$syserr);
                } else {
                    unlink($this->workerTmpLog);
                }
                //finish work
                $this->closeSSH($ssh);
                $this->ip = '';
                usleep(100000);
           } // ip != ''
        } //while end
	} //run() end
}
?>