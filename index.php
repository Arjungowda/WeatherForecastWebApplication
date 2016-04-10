<?php
$errors = array();
$json   = array();
$data = array();
$noData = "No Data";
$flag = 0;
$Lat = $Lon = $LatLng = $summary = $icon = $temperature = "";
$precipitation = $chanceOfRain = $windSpeed = $dewPoint =  $humidity = "";
$visibility = $sunrise = $sunset = $temperatureUnit = $iconImg = $tempMin = $tempMax = "";
if (empty($_GET['streetAddress']))
	$errors['streetAddress'] = 'StreetAddress is required.';
if (empty($_GET['city']))
    $errors['city'] = 'City is required.';
if (empty($_GET['state']))
	$errors['state'] = 'State is required.';
if (empty($_GET['degree']))
    $errors['degree'] = 'Degree is required.';
if (!empty($errors)){
    $json['success'] = false;
	$json['errors']  = $errors;
} else {
	$streetAddress = $_GET['streetAddress'];
	$city = $_GET['city'];
	$state = $_GET['state'];
	$degree_units = $_GET['degree'];
	$Address = $streetAddress.",".$city.",".$state;
	$Address = urlencode($Address);
    //echo $Address;
	$request_url = "http://maps.googleapis.com/maps/api/geocode/xml?address=".$Address."&sensor=true";
	$xml = simplexml_load_file($request_url) or die("url not loading");
	$status = $xml->status;
	if ($status=="OK") {
	    $Lat = $xml->result->geometry->location->lat;
	    $Lon = $xml->result->geometry->location->lng;
		$LatLng = "$Lat,$Lon";
	}
	if ($LatLng != '' &&  $flag!=1){
		$forecast_api_key="a92c4bba83fdacb0e6fb1ec510977818";
		$forecast_url = "https://api.forecast.io/forecast/".$forecast_api_key."/".$LatLng."?units=".$degree_units."&exclude=flags";
		$json = file_get_contents($forecast_url) or die("url not loading");
		$json = json_decode($json, true);
	}
	if($json != null){
		if(isset($json['currently']['summary'])){
			$summary = $json['currently']['summary'];
		}
		else{
			$summary = $noData;
		}
	    if(isset($json['currently']['icon'])){
			$icon = $json['currently']['icon'];
		}
		else{
			$icon = $noData;
		}
		 
		if(isset($json['currently']['temperature'])){
			$temperature = intval($json['currently']['temperature']);
			$temperature = $temperature.$temperatureUnit;
		}
		else{
			$temperature = $noData;
		}
	
		if(isset($json['currently']['precipIntensity'])){
			$precipitation = $json['currently']['precipIntensity'];
		}
		else{
			$precipitation = $noData;
		}
	
		if($degree_units == 'us'){
			if($precipitation >=0 && $precipitation < 0.002 ){
				$precipitation = "None";
			}
			else if($precipitation >=0.002 && $precipitation < 0.017 ){
				$precipitation = "Very Light";
			}
			else if($precipitation >=0.017 && $precipitation < 0.1 ){
				$precipitation = "Light";
			}
			else if($precipitation >=0.1 && $precipitation < 0.4 ){
				$precipitation = "Moderate";
			}
			else {
				$precipitation = "High";
			}
		}
		else{
			if($precipitation >=0 && $precipitation < 0.0508 ){
				$precipitation = "None";
			}
			else if($precipitation >=0.0508 && $precipitation < 0.4318 ){
				$precipitation = "Very Light";
			}
			else if($precipitation >=0.4318 && $precipitation < 2.54 ){
				$precipitation = "Light";
			}
			else if($precipitation >=2.54 && $precipitation < 10.16 ){
				$precipitation = "Moderate";
			}
			else {
				$precipitation = "High";
			}
	
		}
		 
		if(isset($json['currently']['precipProbability'])){
			$chanceOfRain = $json['currently']['precipProbability'];
			$chanceOfRain = $chanceOfRain * 100;
			$chanceOfRain = $chanceOfRain."%";
		}
		else{
			$chanceOfRain = $noData;
		}
		 
		if(isset($json['currently']['windSpeed'])){
			$windSpeed = $json['currently']['windSpeed'];
	
			if($degree_units == 'us'){
				$windSpeed =intval($windSpeed)." mph";
			}
			else{
				$windSpeed =intval($windSpeed);
				$windSpeed = intval($windSpeed * 3.6)." kmh";
			}
		}
		else{
			$windSpeed = $noData;
		}
	
		if(isset($json['currently']['dewPoint'])){
			$dewPoint = intval($json['currently']['dewPoint']);
			$dewPoint = $dewPoint.$temperatureUnit;
		}
		else{
			$dewPoint = $noData;
		}
	
		if(isset($json['currently']['humidity'])){
			$humidity = $json['currently']['humidity'];
			$humidity = $humidity * 100;
			$humidity = $humidity."%";
		}
		else{
			$humidity = $noData;
		}
	
		if($degree_units == 'us'){
			if(isset($json['currently']['visibility']))
				$visibility = intval($json['currently']['visibility'])." mi";
				else
					$visibility = "No data available";
		}
		else{
			if(isset($json['currently']['visibility']))
				$visibility = intval($json['currently']['visibility'])." kms";
				else
					$visibility = $noData;
		}
		 
		$timezone = $json['timezone'];
		date_default_timezone_set($timezone);
	
		$sunrise =  $json['daily']['data'][0]['sunriseTime'];
		$sunrise = date('h:i A',$sunrise);
		 
		$sunset = $json['daily']['data'][0]['sunsetTime'];
		$sunset = date('h:i A',$sunset);
		 
		$tempMin = $json['daily']['data'][0]['temperatureMin'];
		$tempMin = intval($tempMin);
		
		$tempMax = $json['daily']['data'][0]['temperatureMax'];
		$tempMax = intval($tempMax);
		
		//next 24 hours
		for($x = 1; $x <= 48 ; $x++){
			$time = $json['hourly']['data'][$x]['time'];
			$time = date('h:i A',$time);
			
			$summ = $json['hourly']['data'][$x]['icon'];
			$cloudCover = $json['hourly']['data'][$x]['cloudCover'];
			$cloudCover = intVal($cloudCover)."%";
			
			$temp = $json['hourly']['data'][$x]['temperature'];
			
			$wind = $json['hourly']['data'][$x]['windSpeed'];
			if($degree_units == 'us'){
				$wind =intval($wind)." mph";
			}
			else{
				$wind =intval($wind)." m/s";
			}
			
			$hum = $json['hourly']['data'][$x]['humidity'];
			$hum = intval($hum)."%";
			
		    $vis = $json['hourly']['data'][$x]['visibility'];
		    if($degree_units == 'us')
		    	$vis = $vis."mi";
		    else 
		    	$vis = $vis."km";
		    
		    $pressure = $json['hourly']['data'][$x]['pressure'];
		    if($degree_units == 'us')
		    	$pressure = $pressure."mb";
		    else 
		    	$pressure = $pressure."hPa";
		    
		    $hourlyData = array();
		    $hourlyData[] = $time;
		    $hourlyData[] = $summ;
		    $hourlyData[] = $cloudCover;
		    $hourlyData[] = $temp;
		    $hourlyData[] = $wind;
		    $hourlyData[] = $hum;
		    $hourlyData[] = $vis;
		    $hourlyData[] = $pressure;
		    
		    $data[]=$hourlyData;
		}
		
	    //next 7 days
		for($x = 1; $x <= 7 ; $x++){
			$day = $json['daily']['data'][$x]['time'];
			$day = date('l',$day);
			
			$date = $json['daily']['data'][$x]['time'];
			$date = date('M j',$date);
			
			$ic =  $json['daily']['data'][$x]['icon'];
			
			$minTemp =  $json['daily']['data'][$x]['temperatureMin'];
			$minTemp = intval($minTemp);
			
			$maxTemp =  $json['daily']['data'][$x]['temperatureMax'];
			$maxTemp = intval($maxTemp); 
			
			$summ =  $json['daily']['data'][$x]['summary'];
			
			$srt = $json['daily']['data'][$x]['sunriseTime'];
			$srt = date('h:i A', $srt);
			
			$sst = $json['daily']['data'][$x]['sunsetTime'];
			$sst = date('h:i A', $sst);
			
			$hum = $json['daily']['data'][$x]['humidity'];
			$hum = intval($hum)."%";
			
			$winSp =  $json['daily']['data'][$x]['windSpeed'];
			if($degree_units == 'us'){
				$winSp =intval($winSp);
				$winSp = $winSp."mph";
			}
		    else{
		    	$winSp =intval($winSp);
		    	$winSp = $winSp."m/s";
		    }
		    if(isset($json['daily']['data'][$x]['visibility'])){	
		    	$visib = $json['daily']['data'][$x]['visibility'];
		    	if($degree_units == 'us'){
		    		$visib = $visib."mi";
		    	}
		    	else{
		    		$visib = $visib."km";
		    	}
		    }
		    else{
		    	$visib = "Data currently not available";
		    }
			
			
		    	
		    $pr = $json['daily']['data'][$x]['pressure'];
		    if($degree_units == 'us'){
		    	$pr = $pr."mb";
		    }
		    else {
		    	$pr = $pr."hPa";
		    } 
		    
			$weeklyData = array();
			$weeklyData[] = $day;
			$weeklyData[] = $date;
			$weeklyData[] = $ic;
			$weeklyData[] = $minTemp;
			$weeklyData[] = $maxTemp;
			$weeklyData[] = $summ;
	 		$weeklyData[] = $srt;
			$weeklyData[] = $sst;
			$weeklyData[] = $hum;
			$weeklyData[] = $winSp;
			$weeklyData[] = $visib;
			$weeklyData[] = $pr;
			
			$data[] = $weeklyData;
		} 
	    
		$lat = $json['latitude'];
		$lon = $json['longitude'];
		$data['lat']=$lat;
		$data['lon']=$lon;
		$data['timezone']=$timezone;
		$data['summary'] = $summary;
		$data['icon']=$icon;
		$data['temperature']=$temperature;
		$data['precipitation']=$precipitation;
		$data['chanceOfRain']=$chanceOfRain;
		$data['windSpeed']=$windSpeed;
		$data['dewPoint']=$dewPoint;
		$data['visibility']=$visibility;
		$data['humidity']=$humidity;
		$data['sunrise']=$sunrise;
		$data['sunset']=$sunset;
		$data['tempMin']=$tempMin;
		$data['tempMax']=$tempMax;
		
	}
    echo json_encode($data);
}
?>