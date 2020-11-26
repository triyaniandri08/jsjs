<?php
include 'func.php';
header('Content-Type: application/json');
$yt = new YouTube;
$response = $yt->grab('https://m.youtube.com/results?client=mv-google&gl=EN&hl=en&q='.rawurlencode($_GET['q']).'&submit=Telusuri');
$initial = $yt->getStr($response,'<div id="initial-data">','</div>');
$int_data = $yt->getStr($initial,'<!-- ','-->');
$json = json_decode($int_data,1);
       if(!empty(strpos($int_data,'universalWatchCardRenderer'))){
        $listing = $json['contents']['sectionListRenderer']['contents'][count($json['contents']['sectionListRenderer']['contents']) - 2]['itemSectionRenderer']['contents'];
        }
        else{
        $listing = $json['contents']['sectionListRenderer']['contents'][0]['itemSectionRenderer']['contents'];
        }
        //print_r($listing);

        $k = 0;
        $data = [];
        // print_r($listing);
        foreach ($listing as $dataz) {
            if(isset($dataz['compactVideoRenderer']['videoId'])){
                $duration = $yt->covertime(@$dataz['compactVideoRenderer']['lengthText']['runs'][0]['text']);
                $parsed = date_parse($duration);
            @$data[$k]['id'] .= $dataz['compactVideoRenderer']['videoId'];
            @$data[$k]['title'] .= $dataz['compactVideoRenderer']['title']['runs'][0]['text'];
            @$data[$k]['duration'] .= $dataz['compactVideoRenderer']['lengthText']['runs'][0]['text'];
            @$data[$k]['size'] .=  $yt->formatSizeUnits(($parsed['hour'] * 3600 + $parsed['minute'] * 60 + $parsed['second']) * (24 * 1000));
            @$data[$k]['channel'] .= $dataz['compactVideoRenderer']['shortBylineText']['runs'][0]['text'];
            @$data[$k]['view'] .= $dataz['compactVideoRenderer']['viewCountText']['runs'][0]['text'];
            @$data[$k]['nonapi'] .= 'yes';
            @$data[$k]['type'] .= 'video';
            $k++;
            }
        }
echo '{ "status" : "success", "items" : ';
echo json_encode($data);
echo '] }';


?>
