<?php

function numberTowords($number) {
    // $no = floor($number);
    // $point = round($number - $no, 2) * 100;
    // $hundred = null;
    // $digits_1 = strlen($no);
    // $i = 0;
    // $str = array();
    // $words = array(
    //     '0' => '', '1' => 'one', '2' => 'two',
    //     '3' => 'three', '4' => 'four', '5' => 'five', '6' => 'six',
    //     '7' => 'seven', '8' => 'eight', '9' => 'nine',
    //     '10' => 'ten', '11' => 'eleven', '12' => 'twelve',
    //     '13' => 'thirteen', '14' => 'fourteen',
    //     '15' => 'fifteen', '16' => 'sixteen', '17' => 'seventeen',
    //     '18' => 'eighteen', '19' =>'nineteen', '20' => 'twenty',
    //     '30' => 'thirty', '40' => 'forty', '50' => 'fifty',
    //     '60' => 'sixty', '70' => 'seventy',
    //     '80' => 'eighty', '90' => 'ninety');
    // $digits = array('', 'hundred', 'thousand', 'lakh', 'crore');
    // while ($i < $digits_1) {
    //     $divider = ($i == 2) ? 10 : 100;
    //     $number = floor($no % $divider);
    //     $no = floor($no / $divider);
    //     $i += ($divider == 10) ? 1 : 2;
    //     if ($number) {
    //         $plural = (($counter = count($str)) && $number > 9) ? 's' : null;
    //         $hundred = ($counter == 1 && $str[0]) ? ' and ' : null;
    //         $str [] = ($number < 21) ? $words[$number] .
    //             " " . $digits[$counter] . $plural . " " . $hundred
    //             :
    //             $words[floor($number / 10) * 10]
    //             . " " . $words[$number % 10] . " "
    //             . $digits[$counter] . $plural . " " . $hundred;
    //     } else {
    //         $str[] = null;
    //     }
    // }
    // $str = array_reverse($str);
    // $result = implode('', $str);
    // $points = ($point) ?
    //     "" . $words[$point / 10] . " " .
    //     $words[$point = $point % 10] : '';
    // // $rettxt = $result . "Rupees Only";// . $points . " Paise";
    // $rettxt = $result . "Rupees  " . $points . " Paisa";
    // return ucwords($rettxt);

    $no = floor($number);
    $point = round($number - $no, 2) * 100;
    $words = array('0' => '', '1' => 'one', '2' => 'two',
        '3' => 'three', '4' => 'four', '5' => 'five', '6' => 'six',
        '7' => 'seven', '8' => 'eight', '9' => 'nine',
        '10' => 'ten', '11' => 'eleven', '12' => 'twelve',
        '13' => 'thirteen', '14' => 'fourteen',
        '15' => 'fifteen', '16' => 'sixteen', '17' => 'seventeen',
        '18' => 'eighteen', '19' =>'nineteen', '20' => 'twenty',
        '30' => 'thirty', '40' => 'forty', '50' => 'fifty',
        '60' => 'sixty', '70' => 'seventy',
        '80' => 'eighty', '90' => 'ninety');
    $digits = array('', '', 'hundred', 'thousand', 'lakh', 'crore');
    
    $number = explode(".", $number);
    $result = array("","");
    $j =0;
    foreach($number as $val){
        // loop each part of number, right and left of dot
        for($i=0;$i<strlen($val);$i++){
            // look at each part of the number separately  [1] [5] [4] [2]  and  [5] [8]
            
            $numberpart = str_pad($val[$i], strlen($val)-$i, "0", STR_PAD_RIGHT); // make 1 => 1000, 5 => 500, 4 => 40 etc.
            if($numberpart <= 20){
                $numberpart = 1*substr($val, $i,2);
                $i++;
                $result[$j] .= $words[$numberpart] ." ";
            }else{
                //echo $numberpart . "<br>\n"; //debug
                if($numberpart > 90){  // more than 90 and it needs a $digit.
                    $result[$j] .= $words[$val[$i]] . " " . $digits[strlen($numberpart)-1] . " "; 
                }else if($numberpart != 0){ // don't print zero
                    $result[$j] .= $words[str_pad($val[$i], strlen($val)-$i, "0", STR_PAD_RIGHT)] ." ";
                }
            }
        }
        $j++;
    }
    if(trim($result[0]) != "") {
        $amountWord = $result[0] . "Rupees ";
    }
    if($result[1] != "" && $point){
        $amountWord .= $result[1] . "Paisa";
    } 
    $amountWord .= " Only";

    return $amountWord;
}

function IND_money_format($number){
    return $number;
    $decimal = (string)($number - floor($number));
    $money = floor($number);
    $length = strlen($money);
    $delimiter = '';
    $money = strrev($money);

    for($i=0;$i<$length;$i++){
        if(( $i==3 || ($i>3 && ($i-1)%2==0) )&& $i!=$length){
            $delimiter .=',';
        }
        $delimiter .=$money[$i];
    }

    return $result = strrev($delimiter);
    $decimal = preg_replace("/0\./i", ".", $decimal);
    $decimal = substr($decimal, 0, 3);

    if( $decimal != '0'){
        $result = $result.$decimal;
    }

    return $result;
}
