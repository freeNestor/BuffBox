<?php
namespace App\Http\Controllers;
use Cache;
    $a = [
        "step1" =>["command" => "ls",
        "type" => "shell"],
        "step2" =>["command" => "/bin/script",
        "type" => "script"]
    ];
   while (!empty($a)) {
        $b = array_shift($a);
        print_r($b['command']);
   }
    echo date("Y-m-d H:i:s");
    
    
?>