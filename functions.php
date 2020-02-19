<?php

function getOWMkey(){
    return '0908931a901c2dfabe78865d6b9b8582';
}
function getCelsius($query_string){
    $url = 'https://api.openweathermap.org/data/2.5/weather?'.$query_string.'&appid='.getOWMkey().'&units=metric';
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url );
    //curl_setopt($ch, CURLOPT_HTTPHEADER, $CURL_HEADER_SPOTIFY);
    //curl_setopt($ch, CURLOPT_FILE, $myfile);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);    
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,  2);
    curl_setopt($ch, CURLOPT_DNS_USE_GLOBAL_CACHE, false );
    curl_setopt($ch, CURLOPT_DNS_CACHE_TIMEOUT, 2 );
    $output = curl_exec($ch);
    $data = json_decode($output);
    return $data->main->temp;
}
function getBasicHeader(){
   return [
        'Content-Type: application/x-www-form-urlencoded',
        'Authorization: Basic ZjI3ZTY5NjNiNzk2NDU3ZWI0YjgwMTVlZmZkNmZiMzg6ZWVkMGIxOWZiODFlNDU4M2I2MDEwMzQwM2VkODk0ZDY='
   ];
}
function getBearerHeader(){
    return [
        'Content-Type: application/x-www-form-urlencoded',
        'Authorization:  Bearer '.getToken()
   ];
}
function getToken(){
    $url = 'https://accounts.spotify.com/api/token/';
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url );
    //curl_setopt($ch, CURLOPT_HTTPHEADER, $CURL_HEADER_SPOTIFY);
    //curl_setopt($ch, CURLOPT_FILE, $myfile);
    curl_setopt($ch, CURLOPT_HTTPHEADER, getBasicHeader());
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);    
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,  2);
    curl_setopt($ch, CURLOPT_DNS_USE_GLOBAL_CACHE, false );
    curl_setopt($ch, CURLOPT_DNS_CACHE_TIMEOUT, 2 );
    curl_setopt($ch, CURLOPT_POSTFIELDS, 'grant_type=client_credentials');
    $output = curl_exec($ch);
    $data = json_decode($output);
    return $data->access_token;
}
function getGenresAndDegreesData(){
    $string = file_get_contents("./genres_degrees.json");
    if ($string === false) {
        // deal with error...
    }
    $json_a = json_decode($string);
    return $json_a;
}
function search($genre){
    $url = "https://api.spotify.com/v1/search?q=$genre&type=playlist";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL,            $url );
    //curl_setopt($ch, CURLOPT_HTTPHEADER, $CURL_HEADER_SPOTIFY);
    //curl_setopt($ch, CURLOPT_FILE, $myfile);
    curl_setopt($ch, CURLOPT_HTTPHEADER, getBearerHeader());
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);    
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,  2);
    curl_setopt($ch, CURLOPT_DNS_USE_GLOBAL_CACHE, false );
    curl_setopt($ch, CURLOPT_DNS_CACHE_TIMEOUT, 2 );
    $output = curl_exec($ch);
    $data = json_decode($output, true);
    return $data;
  }
function getPlaylistTrackNames($playlist_id){
    $url = "https://api.spotify.com/v1/playlists/".$playlist_id."/tracks";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL,            $url );
    //curl_setopt($ch, CURLOPT_HTTPHEADER, $CURL_HEADER_SPOTIFY);
    //curl_setopt($ch, CURLOPT_FILE, $myfile);
    curl_setopt($ch, CURLOPT_HTTPHEADER, getBearerHeader());
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);    
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,  2);
    curl_setopt($ch, CURLOPT_DNS_USE_GLOBAL_CACHE, false );
    curl_setopt($ch, CURLOPT_DNS_CACHE_TIMEOUT, 2 );
    $output = curl_exec($ch);
    $data = json_decode($output, true);

    $track_names = array_map(function($t){
        return $t['track']['artists'][0]['name']." - ".$t['track']['name'];
    }, $data['items']);
    return $track_names;
  }
function getPlaylistNames($query_string){
    $data = current(getGenresAndDegreesData())->data;
    $temperature = getCelsius($query_string);
    $genre = '';
    foreach($data as $k => $w){
        $min = isset($w->min) ? $w->min : 0;
        $max = isset($w->max) ? $w->max : 99999999;
        if($temperature > $min && $temperature < $max){
            $genre = $k;
        }
    } 
    $p_list = search($genre);
    /*
    echo '<pre>';
    print_r($p_list); */
    $tracks = new \stdClass();
    $tracks->genre = $genre;
    $tracks->temperature = $temperature.' Celsius';
    foreach($p_list['playlists']['items'] as $k => $p){
       $tracks->items[$k] = new \stdClass();
       $tracks->items[$k]->playlist_name = $p['name'];
       $tracks->items[$k]->track_list = getPlaylistTrackNames($p['id']);
    }
  
    return json_encode($tracks, true);
}