<?php
function test()
    {
        $array=[];
        $subarray=[];
        $megarray=['value'=>0];
        $string = "pwwkew";
        if (strlen($string)==0){
            return 0;
        }
        if (strlen($string)==1){
            return 1;
        }
        if (count(array_unique(str_split($string)))==1){
            return 1;
        }
        if (count(array_unique(str_split($string)))==strlen($string)){
            return strlen($string);
        }
        for ($x=0;$x<=strlen($string)+2;$x++){
            for ($y=0;-$y<=strlen($string)+2;$y--) {
                $substr=substr($string,$x,$y);
                if (substr($substr,0,1)==substr($substr,-0,1) and strlen($substr)>1) {
                    $array[]=$substr;
                }
            }
        }
        foreach ($array as $item){
            if (array_search(2,array_count_values(str_split($item,1)))){
                $subarray[]=$item;
            }
        }
        foreach (array_diff($array,$subarray) as $key => $value){
            if (strlen($value)>$megarray['value']) {
                $megarray=['value'=>strlen($value)];
            }
        }
        $sub=['value'=>substr($string,0,1)];
        $bus=['value'=>substr($string,-1,1)];
        $query1=[];
        $query2=[];
        for ($x=1;$x<=count(str_split($string,1))-1;$x++){
            if ($sub['value']==str_split($string,1)[$x]){
                break;
            }
            else {
                $query1[]=(str_split($string,1))[$x];
            }
        }
        for ($x=1;$x<=count(array_reverse(str_split($string,1)))-1;$x++){
            if ($bus['value']==array_reverse(str_split($string,1))[$x]){
                break;
            }
            else {
                $query2[]=array_reverse(str_split($string,1))[$x];
            }
        }
        $result1=count($query1);
        $result2=count($query2);
        dump($result1, $result2,$megarray['value']);
        if (count(array_unique($query1)) != count($query1)){
            $result1=count($query1);
        }
        if (count(array_unique($query2)) != count($query2)){
            $result2=count($query2);
        }
        dump($result1, $result2,$megarray['value']);
        return max($result1, $result2,$megarray['value']);
}
