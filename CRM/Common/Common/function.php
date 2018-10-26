<?php
function msubstr($str, $start=0, $length, $charset="utf-8", $suffix=true){
    if(mb_strlen($str,$charset)>$length)
    {
        if(function_exists("mb_substr")){
            if($suffix)
                return mb_substr($str, $start, $length, $charset)."...";
            else
                return mb_substr($str, $start, $length, $charset);
        }elseif(function_exists('iconv_substr')) {
            if($suffix)
                return iconv_substr($str,$start,$length,$charset)."...";
            else
                return iconv_substr($str,$start,$length,$charset);
        }
        $re['utf-8'] = "/[x01-x7f]|[xc2-xdf][x80-xbf]|[xe0-xef][x80-xbf]{2}|[xf0-xff][x80-xbf]{3}/";
        $re['gb2312'] = "/[x01-x7f]|[xb0-xf7][xa0-xfe]/";
        $re['gbk'] = "/[x01-x7f]|[x81-xfe][x40-xfe]/";
        $re['big5'] = "/[x01-x7f]|[x81-xfe]([x40-x7e]|xa1-xfe])/";
        preg_match_all($re[$charset], $str, $match);
        $slice = join("",array_slice($match[0], $start, $length));
        if($suffix) return $slice."…";
        return $slice;
    }
    else
    {
        return $str;
    }
}
function subtext($text, $length)
{
    if(mb_strlen($text, 'utf8') > $length)
        return mb_substr($text, 0, $length, 'utf8').'...';
    return $text;
}
function getStringId($string)
{
    return substr($string,0,strpos($string, "_"));
}
function getStringChar($string)
{
    return substr($string, strpos($string, "_") + 1);
}
function getIdWithZero($number)
{
    $temp = 1000000;
    $newNum = $number + $temp;
    return substr($newNum, 1, 6); //即截取掉最前面的“1”
}
function arraySort($arrays, $sort_key, $sort_order = SORT_ASC, $sort_type = SORT_NUMERIC ){
    if(is_array($arrays)){
        foreach ($arrays as $array){
            if(is_array($array)){
                $key_arrays[] = $array[$sort_key];
            }else{
                return false;
            }
        }
    }else{
        return false;
    }
    array_multisort($key_arrays,$sort_order,$sort_type,$arrays);
    return $arrays;
}

function dataReturn($msg, $status, $data = [])
{
    $msg = [
        'msg'    => $msg,
        'status' => $status,
        'data'   => $data
    ];
    return $msg;
}


/**
 * @name get_amount
 * @abstract 数字金额转换成中文大写金额的函数
 * @param mixed  $num  要转换的小写数字或小写字符串
 * @return array 大写的金额
 * 小数位为两位
 **/
function get_amount($num)
{
    $c1 = "零壹贰叁肆伍陆柒捌玖";
    $c2 = "分角圆拾佰仟万拾佰仟亿";
    $num = round($num, 2);
    $num = $num * 100;
    if (strlen($num) > 10) {
        return [400, "数据太长，没有这么大的钱吧，检查下", ''];
    }
    $i = 0;
    $c = "";
    while (1) {
        if ($i == 0) {
            $n = substr($num, strlen($num)-1, 1);
        } else {
            $n = $num % 10;
        }
        $p1 = substr($c1, 3 * $n, 3);
        $p2 = substr($c2, 3 * $i, 3);
        if ($n != '0' || ($n == '0' && ($p2 == '亿' || $p2 == '万' || $p2 == '圆'))) {
            $c = $p1 . $p2 . $c;
        } else {
            $c = $p1 . $c;
        }
        $i = $i + 1;
        $num = $num / 10;
        $num = (int)$num;
        if ($num == 0) {
            break;
        }
    }
    $j = 0;
    $slen = strlen($c);
    while ($j < $slen) {
        $m = substr($c, $j, 6);
        if ($m == '零圆' || $m == '零万' || $m == '零亿' || $m == '零零') {
            $left = substr($c, 0, $j);
            $right = substr($c, $j + 3);
            $c = $left . $right;
            $j = $j-3;
            $slen = $slen-3;
        }
        $j = $j + 3;
    }

    if (substr($c, strlen($c)-3, 3) == '零') {
        $c = substr($c, 0, strlen($c)-3);
    }
    if (empty($c)) {
        return [200, "数据返回成功", "零圆整"];
    }else{
        return [200, "数据返回成功", $c . "整"];
    }
}