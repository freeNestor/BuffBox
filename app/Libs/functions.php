<?php
    if (!function_exists('unescape')) {
        function phpUnescape($escstr) 
        { 
            preg_match_all("/%u[0-9A-Za-z]{4}|%.{2}|[0-9a-zA-Z.+-_]+/", $escstr, $matches); 
            $ar = &$matches[0]; 
            $c = ""; 
            foreach($ar as $val) 
            { 
                if (substr($val, 0, 1) != "%") 
                { 
                    $c .= $val; 
                } elseif (substr($val, 1, 1) != "u") 
                { 
                    $x = hexdec(substr($val, 1, 2)); 
                    $c .= chr($x); 
                } 
                else 
                { 
                    $val = intval(substr($val, 2), 16); 
                    if ($val < 0x7F) // 0000-007F 
                    { 
                        $c .= chr($val); 
                    } elseif ($val < 0x800) // 0080-0800 
                    { 
                        $c .= chr(0xC0 | ($val / 64)); 
                        $c .= chr(0x80 | ($val % 64)); 
                    } 
                    else // 0800-FFFF 
                    { 
                        $c .= chr(0xE0 | (($val / 64) / 64)); 
                        $c .= chr(0x80 | (($val / 64) % 64)); 
                        $c .= chr(0x80 | ($val % 64));
                    } 
                } 
            } 
            return $c; 
        }
    }
    
    if (!function_exists('phpEscape')) {
        function phpEscape($str) {
            preg_match_all("/[\x80-\xff].|[\x01-\x7f]+/",$str,$r);
            $ar = $r[0];
            foreach($ar as $k=>$v) {
                if(ord($v[0]) < 128) {
                    $ar[$k] = rawurlencode($v);
                } else {
                    $ar[$k] = "%u".bin2hex(iconv("GB2312","UCS-2//IGNORE",$v));
                }
            }
            return join("",$ar);
        }
    }
    
    function getCurrentDateTime(){
        return date("Y-m-d H:i:s");
    }
?>