<?php
fwrite(SITE,'请输入域名：');
$site=fgets(SITE);
history($site);
function history($site){
    $date=geturl('https://web.archive.org/__wb/sparkline?url='.urlencode($site).'&collection=web&output=json');
    
    // var_dump($date["years"]);
    foreach ($date["years"] as $year => $month) {
        $d=geturl('https://web.archive.org/__wb/calendarcaptures/2?url='.$site.'&date='.$year.'&groupby=day');
        foreach ($d["items"] as $day) {
            // echo $year.$day[0];
            $hour=geturl('https://web.archive.org/__wb/calendarcaptures/2?url='.$site.'&date='.$year.cdate($day[0]));
            // var_dump($hour);
            foreach ($hour["items"] as $m) {
                // echo 'https://web.archive.org/web/'.$year.cdate($day[0]).cdate($m[0]).'/'.$site;
                $html=file_get_contents('https://web.archive.org/web/'.$year.cdate($day[0]).cdate($m[0]).'/'.$site);
                $html=array_iconv($html,'utf-8','gb2312');
                preg_match('|<title>(.*?)</title>|',$html,$title);
                preg_match('|<meta name="description" content="(.*?)"/>|',$html,$description);
                preg_match('|<meta name="keywords" content="(.*?)"/>|',$html,$keywords);
                echo $year.' '.cdate($day[0]).' '.cdate($m[0]).'|'.$title[1].'|'.$description[1].'|'.$keywords[1]."\n";
            }
        }
        sleep(1);
    }
}

function cdate($date){
    if(strlen($date)<4){
        return '0'.$date;
    }else{
        return $date;
    }
}
function geturl($url){
        $headerArray =array(
            "cookie: donation-identifier=55cac01a8f126400466a2ef0217ce984; donation=x",
            'referer: https://web.archive.org/',
            'sec-fetch-dest: empty',
            'sec-fetch-mode: cors',
            'sec-fetch-site: same-origin',
            'user-agent: Mozilla/5.0 (Windows NT 6.3; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/87.0.4280.88 Safari/537.36'    
        );
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE); 
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE); 
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch,CURLOPT_HTTPHEADER,$headerArray);
        $output = curl_exec($ch);
        curl_close($ch);
        $output = json_decode($output,true);
        return $output;
}
/**
 * 对数据进行编码转换
 * @param array/string $data 数组
 * @param string $output 转换后的编码
 * Created on 2016-7-13
 */
function array_iconv($data, $output = 'utf-8') {
  $encode_arr = array('UTF-8','ASCII','GBK','GB2312','BIG5','JIS','eucjp-win','sjis-win','EUC-JP');
  $encoded = mb_detect_encoding($data, $encode_arr);
  if (!is_array($data)) {
    return mb_convert_encoding($data, $output, $encoded);
  }
  else {
    foreach ($data as $key=>$val) {
      $key = array_iconv($key, $output);
      if(is_array($val)) {
        $data[$key] = array_iconv($val, $output);
      } else {
      $data[$key] = mb_convert_encoding($data, $output, $encoded);
      }
    }
  return $data;
  }
}