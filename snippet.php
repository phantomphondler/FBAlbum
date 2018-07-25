<?php
/*
#fbAlbum#

##About##


* By Philipp Schmidt
* fbAlbum
* Version: 0.1
* Creator: http://www.servingpixels.com
* GitHub: https://github.com/phantomphondler/FBAlbum

fbAlbum is a simple snippet for ModX Revolution that let you retrieve FB album pics.

© servingpixels.com 2018

###Thanks###

A big thank you goes to the creators of MODx Revolution!

###Usage###

<code>
[[!fbAlbum]]
</code>

##Configuration##

The variables that are available are listed below with a description.

* tpl - the chunk you want to use
* fb_page_id - Page id you can get it here https://findmyfbid.com or search google
* access_token - app key and secret key seperated by a | e.g. 974662995944937|h9GLXXk3KSeDpUBMS0pdvEiX0lA
* pics_per_page - how many pics you want to load. Note lots of pics will really slow down the page
* show_all - also show cover pic etc
* albumid - FB album URL you can get it from the URL
* debug - turn debug on off

##Chunk##

You can use the following
[[+large_image]]
[[+small_image]]
[[+orientation]]
[[+count]]
[[+album]]
[[+total]]

*/


$tpl = isset($tpl)? $tpl : "fbAlbumTpl";
$fb_page_id = isset($fb_page_id)? $fb_page_id : "10150136759965455";
$access_token = isset($access_token)? $access_token : "236995737075698|012b2f08530b0d0bce2dfe6a84cca8a5";
$pics_per_page = isset($pics_per_page)? $pics_per_page :10;
$show_all = isset($show_all)? $show_all :FALSE;		//Set to TRUE to show Timelime Photos, Cover Photos & Profile Pictures
$albumid = isset($albumid)? $albumid :"10160926076495455";
$debug = isset($debug)? $debug :0;


if($access_token == ''){
	$modx->log(modX::LOG_LEVEL_ERROR, '[FB] missing required properties!');
	return;
}

function curl_get_contents($url)
{
	$ch   = curl_init($url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
	$data = curl_exec($ch);
	curl_close($ch);
	return $data;
}

$fields       = "count,created_time,description,link,name";
$json_link    = "https://graph.facebook.com/".$albumid."/?access_token={$access_token}&fields={$fields}";
$album        = json_decode(curl_get_contents($json_link));
$album_name   = $album->name;
$extra_params = "&limit=" . $pics_per_page;
$json_linkz   = "https://graph.facebook.com/{$albumid}/photos/?access_token={$access_token}". $extra_params;
$json         = json_decode(curl_get_contents($json_linkz));
$count        = 0;
$totalsize    = sizeof($json->data);


for($i = 1; $i <= $totalsize ; $i++):

$photo      = $json->data[$i - 1];
$fields     = "id,height,images,width,link,name,picture";
$album_link = "https://graph.facebook.com/{$photo->id}/?access_token={$access_token}&fields={$fields}";
$album_json = json_decode(curl_get_contents($album_link));

if(isset($album_json->images[4]->source))
{
	$cover_ind = 4;
	$pic       = $album_json->images[$cover_ind]->source;
}
else
{
	$cover_ind = 0;
	$pic       = $album_json->images[0]->source;
}

if($album_json->images[$cover_ind]->height < $album_json->images[$cover_ind]->width)
{
	$orientation = "landscape";
}
else
{
	$orientation = "portrait";
}


if(isset($album_json->images[1]))
{
	$large = $album_json->images[1]->source;
}
else
{
	$large = $album_json->images[0]->source;
}

$count++;

$output .= $modx->getChunk($tpl ,array(
		'large_image'=> $large,
		'small_image'=> $pic,
		'orientation'=> $orientation,
		'count'      => $count,
		'album'      => $album_name,
		'total'      => $totalsize
	));
$results .= $large.$pic.$orientation.$count.$album_name.$totalsize;
endfor;

if($debug == 0){
	return $output;
}
else
if($debug == 1){
	echo '
	<pre>
	';
	print_r($results);
	echo '
	</pre>';
}