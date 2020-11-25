<?php

function ngegrab($url){
ini_set("user_agent","Opera/9.80 (J2ME/MIDP; Opera Mini/4.2 19.42.55/19.892; U; en) Presto/2.5.25");
$grab = @fopen($url, 'r');
$contents = "";
if ($grab) {
while (!feof($grab)) {
$contents.= fread($grab, 8192);
}
fclose($grab);
}
return $contents;
}

function potong($content,$start,$end){
if($content && $start && $end) {
$r = explode($start, $content);
if (isset($r[1])){
$r = explode($end, $r[1]);
return $r[0];}
return '';}}
function arzGrab($url){
$ua = 'Mozilla/5.0 (Windows NT 6.3; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2226.0 US Safari/537.36';
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_USERAGENT, $ua);
$c = curl_exec($ch);
return $c;
}

function arzLink($txt) {
$txt = preg_replace("/[^a-zA-Z0-9]/", " ", strtolower($txt));
$txt = implode('-',array_unique(explode(' ', trim($txt))));
$txt = str_replace('”', '', $txt);
$txt = str_replace('“', '', $txt);
$txt = str_replace(',', '', $txt);
$txt = str_replace('"', '', $txt);
$txt = str_replace("'", '', $txt);
$txt = str_replace('--', '-', $txt);
return $txt;
}

function arzClear($value){
$value = ucwords(str_replace('-', ' ', $value));
$value = ucwords(str_replace('_', ' ', $value));
$value = ucwords(str_replace('#', ' ', $value));
$value = ucwords(str_replace('/', ' ', $value));
$value = ucwords(str_replace('%', ' ', $value));
$value = str_replace('  ', ' ', $value);
return $value;
}

class YouTube
{
    var $error = FALSE;
    var $errorMsg;

    public function grab($url)
    {
        $ip = $_SERVER['REMOTE_ADDR'];
        $setUA = 'Opera/9.80 (BlackBerry; Opera Mini/4.5.33868/37.8993; HD; en_US) Presto/2.12.423 Version/12.16';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_REFERER, $url);
        curl_setopt($ch, CURLOPT_USERAGENT, $setUA); // Set UA curl_setopt($ch,CURLOPT_HTTPHEADER,array("REMOTE_ADDR:$ip","HTTP_X_FORWARDED_FOR:$ip"));
        $ret = curl_exec($ch);
        curl_close($ch);
        return $ret;
    }

    public function trending()
    {
        $response = $this->grab('https://m.youtube.com/channel/UC-9-kyTW8ZkZNDHQJ6FgpwQ');
            $initial = $this->getStr($response,'<div id="initial-data">','</div>');
            $int_data = $this->getStr($initial,'<!-- ','-->');
        $json = json_decode($int_data,1);
        $k = 0;
        $raw = [];
        $data = [];
        foreach ($json['contents']['singleColumnBrowseResultsRenderer']['tabs'][0]['tabRenderer']['content']['sectionListRenderer']['contents'] as $jason) {
        $listing = $jason['shelfRenderer']['content']['verticalListRenderer']['items'];
        $raw[$k] = $listing;
        foreach ($listing as $dataz) {
            if(isset($dataz['compactVideoRenderer']['videoId'])){
                $duration = $this->covertime($dataz['compactVideoRenderer']['lengthText']['runs'][0]['text']);
                $parsed = date_parse($duration);
            @$data[$k]['id'] .= $dataz['compactVideoRenderer']['videoId'];
            @$data[$k]['title'] .= $dataz['compactVideoRenderer']['title']['runs'][0]['text']; 
            @$data[$k]['durationformat'] .= $duration;
            @$data[$k]['duration'] .= $dataz['compactVideoRenderer']['lengthText']['accessibility']['accessibilityData']['label'];
            @$data[$k]['size'] .=  $this->formatSizeUnits(($parsed['hour'] * 3600 + $parsed['minute'] * 60 + $parsed['second']) * (24 * 1000));
            @$data[$k]['channel'] .= $dataz['compactVideoRenderer']['shortBylineText']['runs'][0]['text'];
            @$data[$k]['viewer'] .= $dataz['compactVideoRenderer']['viewCountText']['runs'][0]['text'];
            $k++;
            }

        }
        }
        return json_encode($data);
    }

    /**
     * @param $q
     * @param null $token
     * @return false|string
     */

    function covertime($data){
        $totaldot = substr_count($data,'.');
        if($totaldot == 1){

            return '00:'.str_replace('.', ':', $data);
        }else{
            return $data;
        }
    }
        function formatSizeUnits($bytes)
    {
        if ($bytes >= 1073741824)
        {
            $bytes = number_format($bytes / 1073741824, 2) . ' GB';
        }
        elseif ($bytes >= 1048576)
        {
            $bytes = number_format($bytes / 1048576, 2) . ' MB';
        }
        elseif ($bytes >= 1024)
        {
            $bytes = number_format($bytes / 1024, 2) . ' KB';
        }
        elseif ($bytes > 1)
        {
            $bytes = $bytes . ' bytes';
        }
        elseif ($bytes == 1)
        {
            $bytes = $bytes . ' byte';
        }
        else
        {
            $bytes = '0 bytes';
        }

        return $bytes;
}
    function scrape($url){    
    $ch = curl_init();
    curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt ($ch, CURLOPT_URL, $url);
    curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, 20);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, 20);
    curl_setopt ($ch, CURLOPT_USERAGENT, 'Opera/9.80 (BlackBerry; Opera Mini/4.5.33868/37.8993; HD; en_US) Presto/2.12.423 Version/12.16');
    curl_setopt ($ch, CURLOPT_FOLLOWLOCATION, true);
    $content = curl_exec ($ch);
    curl_close ($ch);
    return $this->remline($content);
    }
    public function search($q, $token = NULL)
    {
        $b = urlencode($q);
        $q = str_replace("+", "-", $b);
        if (empty($q)) {
            $this->error = TRUE;
            $this->errorMsg = "Parameter Queri Kosong";
        }

        if($token){
        $response = $this->grab('https://m.youtube.com/results?client=mv-google&gl=EN&hl=en&search_sort=relevance&q='.rawurlencode($q).'&search_type=search_video&uploaded=&action_continuation=1&ctoken='.$token);
        }else{
        $response = $this->grab('https://m.youtube.com/results?client=mv-google&gl=EN&hl=en&q='.rawurlencode($q).'&submit=Telusuri');
        }
            $initial = $this->getStr($response,'<div id="initial-data">','</div>');
            $int_data = $this->getStr($initial,'<!-- ','-->');
        $json = json_decode($int_data,1);
        if(strpos($int_data,'universalWatchCardRenderer')){
        $listing = $json['contents']['sectionListRenderer']['contents'][count($json['contents']['sectionListRenderer']['contents']) - 1]['itemSectionRenderer']['contents'];
        }else{
        $listing = $json['contents']['sectionListRenderer']['contents'][0]['itemSectionRenderer']['contents'];
        }
        $k = 0;
        $data = [];
        // print_r($listing);
        foreach ($listing as $dataz) {
            if(isset($dataz['compactVideoRenderer']['videoId'])){
                $duration = $this->covertime(@$dataz['compactVideoRenderer']['lengthText']['runs'][0]['text']);
                $parsed = date_parse($duration);
            @$data[$k]['id'] .= $dataz['compactVideoRenderer']['videoId'];
            @$data[$k]['title'] .= $dataz['compactVideoRenderer']['title']['runs'][0]['text']; 
            @$data[$k]['duration'] .= $dataz['compactVideoRenderer']['lengthText']['runs'][0]['text'];
            @$data[$k]['size'] .=  $this->formatSizeUnits(($parsed['hour'] * 3600 + $parsed['minute'] * 60 + $parsed['second']) * (24 * 1000));
            @$data[$k]['channel'] .= $dataz['compactVideoRenderer']['shortBylineText']['runs'][0]['text'];
            @$data[$k]['view'] .= $dataz['compactVideoRenderer']['viewCountText']['runs'][0]['text'];
            @$data[$k]['nonapi'] .= 'yes';
            @$data[$k]['type'] .= 'video';
            $k++;
            }
        }
        return json_encode($data);
    }

    function getStr($string,$start,$end){
        $str = explode($start,$string,2);
        $str = @explode($end,$str[1],2);
        return $str[0];
    }

    function onlyStr($data){
        $data = rtrim(ltrim(strip_tags($data)));
        return trim($data);
    }
    function getAll($x,$content,$start,$end){
        $r = explode($start, $content);
            if (isset($r[$x])){
                $r = explode($end, $r[$x]);
                return $r[0];
            }
        return '';
    }
    
    function getData($id)
    {
        if (!empty($id)) {
            $grab = $this->grab('https://m.youtube.com/watch?v=' . $id . '&hl=id&client=mv-google&gl=ID&fulldescription=1');
            $initial = $this->getStr($grab,'<div id="initial-data">','</div>');
            $int_data = $this->getStr($initial,'<!-- ','-->');
            // print_r($int_data);
            $js_data = '{'.$this->getStr($grab,'ytInitialPlayerConfig = {','};').'}';
            $jason = json_decode($js_data,1);
            $videoinfo = json_decode($jason['args']['player_response'],1)['videoDetails'];
            // print_r($videoinfo);
            $title = $videoinfo['title'];
            $durasi = $videoinfo['lengthSeconds'];
            $suka = $this->getStr($int_data,'{"iconType":"LIKE"},"defaultText":{"runs":[{"text":"','"');
            $tidak = $this->getStr($int_data,'{"iconType":"DISLIKE"},"defaultText":{"runs":[{"text":"','"');
            $publish = $this->getStr($int_data,'Dipublikasikan tanggal','"}');
            $view = $videoinfo['viewCount'];
            $chid = $videoinfo['channelId'];
            $chtitle = $videoinfo['author'];
            $desc = $videoinfo['shortDescription'];
            $stream = explode('rtsp://', $hasil);
            $stream = explode('"', array_sum($stream));
            $stream = 'rtsp://' . $stream[0];
            $array = array(
                'title' => $title,
                'id' => $id,
                'stream' => $stream,
                'chid' => $chid,
                'duration' => gmdate("H:i:s", $durasi),
                'chtitle' => $chtitle,
                'view' => $view,
                'upload' => $publish,
                'like' => $suka,
                'notlike' => $tidak,
                'description' => $desc
            );
            return json_encode($array);
        } else {
            $this->error = TRUE;
            $this->errorMsg = "Parameter ID Kosong";
        }
    }

    function remline($string){
        $string= str_replace(PHP_EOL, ' ', $string);
        $string= str_replace('&nbsp;', ' ', $string);
        $string= str_replace(array("\r","\n"), "", $string);
        $string= trim(preg_replace('/\s\s+/', ' ', $string));
        return $string;
    }
    function relatedVideo($id)
    {
        if (!empty($id)) {
            $grab = $this->grab('https://m.youtube.com/watch?v=' . $id . '&hl=id&client=mv-google&gl=ID');
            $hasil = array_pad(explode('Video Terkait', $grab), 2, null);
            $hasil = array_pad(explode('<img src="', $hasil[1]), 2, null);
            $array = array();
            for ($i = 1, $iMax = count($hasil); $i < $iMax; $i++) {
                $link = array_pad(explode('<a href="', $hasil[$i]), 2, null);
                $link = array_pad(explode('">', $link[1]), 2, null);
                $link = $link[0];
                $id = array_pad(explode('i.ytimg.com/vi/', $hasil[$i]), 2, null);;
                $id = array_pad(explode('/', $id[1]), 2, null);;
                $id = $id[0];
                $title = array_pad(explode('<a href="' . $link . '">', $hasil[$i]), 2, null);;
                $title = array_pad(explode('</a>', $title[1]), 2, null);;
                $title = str_replace(PHP_EOL, '', $title[0]);
                $anu = array_pad(explode('<div style="', $hasil[$i]), 2, null);;
                if (isset($anu[2])) {
                    $durasi = array_pad(explode('</div>', $anu[2]), 2, null);;
                    $durasi = array_pad(explode('">', $durasi[0]), 2, null);;
                    $durasi = str_replace(PHP_EOL, '', $durasi[1]);
                }else{
                    $durasi = '';
                }
                    $channel = @array_pad(explode('</div>', $anu[3]), 2, null);;
                    $channel = array_pad(explode('">', $channel[0]), 2, null);;
                    $channel = str_replace(PHP_EOL, '', $channel[1]);
                if (isset($anu[4])) {
                    $view = array_pad(explode('</div>', $anu[4]), 3, null);
                    $view = array_pad(explode('">', $view[0]), 2, null);;
                    $view = str_replace(array('x ditonton', PHP_EOL), '', $view[1]);
                }

                $array[] = array(
                    'id' => $id,
                    'title' => $title,
                    'duration' => $durasi,
                    'channel' => $channel,
                );
            }
            return json_encode($array);
        } else {
            $this->error = TRUE;
            $this->errorMsg = "ID belum ada";
        }
    }

    function playlist($play, $token)
    {
        $grab = $this->grab('https://m.youtube.com/playlist?list=' . $play . '&ctoken=' . $token . '&client=mv-google&gl=ID&hl=id');
        $exp = explode('</form>', $grab);
        $hasil = explode('Playlist:', $grab);
        $page = explode('<span style="padding:0px 3px">', $exp[1]);
        $page = explode('</div>', $page[1]);
        $page = explode('<a', $page[0]);
        if (count($page) > 2) {
            $prevPage = explode('&amp;ctoken=', $page[1]);
            $prevPage = explode('"', $prevPage[1]);
            $prevPage = explode('&', $prevPage[0]);
            $this->prevToken = $prevPage[0];
            $nextPage = explode('&amp;ctoken=', $page[2]);
            $nextPage = explode('"', $nextPage[1]);
            $nextPage = explode('&', $nextPage[0]);
            $this->nextToken = $nextPage[0];
        } else {
            $nextPage = explode('&amp;ctoken=', $page[1]);
            $nextPage = explode('"', $nextPage[1]);
            $nextPage = explode('&', $nextPage[0]);
            $this->nextToken = $nextPage[0];
        }
        $playlist = explode(PHP_EOL, $hasil[1]);
        $playlist = $playlist[0];
        $hasil = explode('<img src="', $exp[1]);
        for ($i = 1; $i < count($hasil); $i++) {
            $link = explode('<a href="', $hasil[$i]);
            $link = explode('">', $link[1]);
            $link = $link[0];
            $id = explode('i.ytimg.com/vi/', $hasil[$i]);
            $id = explode('/', $id[1]);
            $id = $id[0];
            $title = explode('<a href="' . $link . '">', $hasil[$i]);
            $title = explode('</a>', $title[1]);
            $title = str_replace(PHP_EOL, '', $title[0]);
            $anu = explode('<div style="', $hasil[$i]);
            $durasi = explode('</div>', $anu[2]);
            $durasi = explode('">', $durasi[0]);
            $durasi = str_replace(PHP_EOL, '', $durasi[1]);
            $channel = explode('</div>', $anu[3]);
            $channel = explode('">', $channel[0]);
            $channel = str_replace(PHP_EOL, '', $channel[1]);
            $view = explode('</div>', $anu[4]);
            $view = explode('">', $view[0]);
            $view = str_replace(PHP_EOL, '', str_replace('x ditonton', '', $view[1]));
            $array[] = array(
                'type' => 'video',
                'id' => $id,
                'title' => $title,
                'duration' => $durasi,
                'channel' => $channel
            );
        }
        return json_encode($array);
    }
}
?>