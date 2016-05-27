<?php

include_once("jobWorker.php");

/**
 * $argv[0]: script name
 * $argv[1]: concurrency
 * $argv[2]: timeout
 * $argv[3]: info file
 * $argv[4]: debug
 */

/**
 * Function remove temporary file if debug not true
 */
function removeTempFile($debug,$file) {    
    if (empty($debug) or $debug == 0) {
        unlink($file);
    }
}

if ($argc < 2) {
    echo "Not Enough Argument\nUsage: php jobDispatcher.php infoFile\n";
} else {
   
    $hostlist = '';
    $infoFile = $argv[1];
    $sshinfo = '';
    $jobinfo = '';
    //$f = @fopen($infoFile,'r');
    if (!file_exists($infoFile)) {
        echo "File Not Exists\n";
    } else {

        /**
         * Parse information from given file
         */
        $info = [];
        $fContents = file_get_contents($infoFile);
        $lineArr = explode("\n",$fContents);
        /*
        while (!feof($f)) {
            $line = fgets($f,8192);
            $tmp = json_decode($line,true);
            if (!json_last_error()) {
                $info[$seek] = $tmp;
            } else {
                echo $line."\n";
                echo "Parse Info Error ".json_last_error().": ".json_last_error_msg()."\n".$line;
                //break;
            }
            $seek++;
        }
        fclose($f);
        */
        if (count($lineArr) < 3) {
            echo "Parse Information Error,Not Enough Information\n";
            removeTempFile($debug,$infoFile);
            exit('');
        }
        for($i=0;$i<=2;$i++) {
            if (!empty($lineArr[$i])) {
                $info[$i] = json_decode($lineArr[$i],true);
                if (json_last_error()) {
                    echo "Parse Information Error ".json_last_error().": ".json_last_error_msg()."\n".$line;
                    removeTempFile($debug,$infoFile);
                    exit('');
                }
            } else {
                echo "Parse Information Error,Not Enough Information\n";
                removeTempFile($debug,$infoFile);
            }
        }
        $hostlist = $info[0];
        $sshinfo = $info[1];
        $jobinfo = $info[2];

        $debug = $jobinfo['debug'];
        $concurrency = 5;
        if (!empty($jobinfo['parallel'])) {
            $concurrency = $jobinfo['parallel'];
        }
        
        /**
         * New jobWorker Thread Pool
         */
        for ($i=1;$i<=$concurrency;$i++) {
        	$pool[] = new jobWorker($i);
        }               
        foreach ($pool as $worker) {
        	//echo "memory: ".$worker->id." ".memory_get_usage()."\n";
            $worker->start();
            usleep(random_int(100,200));
        }
        
        for ($i=1;$i<=count($hostlist);$i++){
        	while (true) {
        		foreach ($pool as $w) {
        			if ($w->ip == ''){
        				$w->ip = $hostlist[$i]['ipaddr'];
                        $w->port = $hostlist[$i]['portok'];
                        $w->hostname = $hostlist[$i]['hostname'];
                        $w->remoteUser = $hostlist[$i]['execuser'];
                        $w->pubkey = $sshinfo['pubkey'];
                        $w->prikey = $sshinfo['prikey'];
                        $w->taskid = $jobinfo['taskid'];
                        $w->jobid = $jobinfo['jobid'];
                        $w->joblog = $jobinfo['joblog'];
                        $w->stepRela = $jobinfo['steprela'];
                        $w->syslog = $jobinfo['syslog'];
                        $w->steplog = $jobinfo['steplog'];
                        $w->timeOut = $jobinfo['steptimeout'];
                        $w->comm = json_encode($jobinfo['steps']);
        				break 2;
        			}
        		}
        	}
            usleep(random_int(100,200));
        }
        
        while (count($pool)){
        	foreach ($pool as $key => $th ){
        	               
        		if ($th->ip == ''){
        			$th->running = false;
                    $th->join();
        			unset($pool[$key]);
        		}
        	}
        	usleep(100000);
        }
        
        removeTempFile($debug,$infoFile);
   }
   //exit('Failed'); 
} // if end
?>