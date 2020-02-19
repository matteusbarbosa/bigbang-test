<?php

require "functions.php";

if(isset($_GET['city_name'])){
   echo getPlaylistNames('q='.$_GET['city_name']);
}

if(isset($_GET['lat']) && isset($_GET['lon'])){
   echo getPlaylistNames('lat='.urlencode($_GET['lat']).'&lon='.urlencode($_GET['lon']));
}
