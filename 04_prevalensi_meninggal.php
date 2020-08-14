<?php
ini_set('display_errors', 0);
date_default_timezone_set('Asia/Jakarta');
require_once("assets/php/csv2json.php");
?>
<!DOCTYPE html>
<html>
<head>
	 <head>
	<title>Kasus Aktif Covid-19</title>
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.6.0/dist/leaflet.css" integrity="sha512-xwE/Az9zrjBIphAcBb3F6JVqxf46+CDLwfLMHloNu6KEQCAWi6HcDUbeOfBIptF7tcCzusKFjFw2yuvEpDL9wQ==" crossorigin=""/>
  	<link href="https://fonts.googleapis.com/css?family=Roboto&display=swap" rel="stylesheet">
	<link rel="icon" href="assets/icons/icon.ico" type="image/x-icon">
    <style type="text/css">
    	body{
    		font-family: roboto
    	}
		::-webkit-scrollbar {
		  width: 5px;
		}

		::-webkit-scrollbar-track {
		  background: #eee;
		}

		::-webkit-scrollbar-thumb {
		  background: #ccc;
		}

		::-webkit-scrollbar-thumb:hover {
		  background: #bbb;
		}
		#map{
			width: 100%;
			height: 100vh
		}
		.leaflet-container{
			background: transparent;
		}
		.list-covid{
			height: 100vh;
			overflow-x: hidden;
		}
		.list-group-item:hover{
			cursor: pointer;
		}

	.info { padding: 6px 8px; font: 14px/16px Arial, Helvetica, sans-serif; background: white; background: rgba(255,255,255,0.8); box-shadow: 0 0 15px rgba(0,0,0,0.2); border-radius: 5px; } .info h4 { margin: 0 0 5px; color: #777; }
	.legend { text-align: left; line-height: 20px; color: #555; } .legend i { width: 20px; height: 18px; float: left; margin-right: 8px; opacity: 0.7; }
	header{
		position: fixed;
		top: 0;
		right: 0;
		display: flex;
		color: #333;
		padding: 10px;
		align-content: center;
	}
	header img{
		width: 30px;
		margin-right: 5px
	}
	</style>
		
</head>
<body>
	<header>
		<img src="assets/icons/logo.png" height="32">
		<h3>Kasus Aktif Covid-19 di Indonesia</h3>
	</header>
	<div class="row" style="width: 100%">
		<div class="col-md-3">
			<ul class="list-group list-covid">
			  
			</ul>
		</div>
		<div class="col-md-9">
			<div id="map"></div>
		</div>
	</div>

</body>
<script src="https://code.jquery.com/jquery-3.4.1.min.js" integrity="sha256-CSXorXvZcTkaix6Yvo6HppcZGetbYMGWSFlBw8HfCJo=" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
<script src="https://unpkg.com/leaflet@1.6.0/dist/leaflet.js" integrity="sha512-gZwIG9x3wUXg2hdXF6+rVkLF/0Vi9U8D2Ntg4Ga5I5BZpVkVxlJWbSQtXPSiUTtC0TjtGOmxa1AJPuV0CPthew==" crossorigin=""></script>
<script type="text/javascript">
  var map= L.map("map").setView([-3.824181, 117.8191513],5);
  var api ='assets/covid19/covidkab-6.json';
  var dataCovid=[];
  var geojson=[];
  map.attributionControl.addAttribution('Created by &copy; <a href="#">Irland Fardani</a>');
  getData();
  $(document).on("click",".list-covid .list-group-item",function(){
  	var id=$(this).data("id");
  	var set=geojson[id];
  	set.eachLayer(function(feature){
  		feature.openPopup();
  		map.fitBounds(feature.getBounds());
  	});
  });
function getColor(positif){
	color="#0d0";
	if(positif>100){
		color="#222";
	}
	else if(positif>50){
		color="#555";
	}
	else if(positif>30){
		color="#f00";
	}
	else if(positif>20){
		color="#f90";
	}
	else if(positif>10){
		color="#09d";
	}
	else if(positif>5){
		color="#090";
	}
	return color;
}

//basemap
	L.tileLayer('https://api.mapbox.com/styles/v1/{id}/tiles/{z}/{x}/{y}?access_token=pk.eyJ1IjoibWFwYm94IiwiYSI6ImNpejY4NXVycTA2emYycXBndHRqcmZ3N3gifQ.rJcFIG214AriISLbB6B5aw', {
		maxZoom: 18,
		attribution: 'Map data &copy; <a href="https://www.openstreetmap.org/">OpenStreetMap</a> contributors, ' +
			'<a href="https://creativecommons.org/licenses/by-sa/2.0/">CC-BY-SA</a>, ' +
			'Imagery © <a href="https://www.mapbox.com/">Mapbox</a>',
		id: 'mapbox/streets-v11',
		tileSize: 512,
		zoomOffset: -1
	}).addTo(map);
// atur style
function style(f) {
	var KODE=f.properties.Kode;
	data = dataCovid[KODE];
	var prmd = Number(data.pr_pdp_md)+Number(data.pr_md);
	// console.log(data);
	return {
		weight: 1,
		opacity: 1,
		color: 'white',
		dashArray: '3',
		fillOpacity: 0.7,
		fillColor: getColor(prmd)
	};
}
// update jika hover
function highlightFeature(e) {
	var layer = e.target;

	layer.setStyle({
		weight: 1,
		color: '#f00',
		dashArray: '',
		fillOpacity: 0.7
	});

	if (!L.Browser.ie && !L.Browser.opera && !L.Browser.edge) {
		layer.bringToFront();
	}

}
// update info
	function resetHighlight(e) {
		var layer = e.target;
		layer.setStyle({
			weight: 1,
			opacity: 1,
			color: 'white',
			dashArray: '3',
			fillOpacity: 0.7,
		})
	}

  function onEachFeature(f, layer) {
		layer.on({
			mouseover: highlightFeature,
			mouseout: resetHighlight
		});
		var KODE=f.properties.Kode;
		// console.log(f.properties);
		data = dataCovid[KODE];
		var prmd = Number(data.pr_pdp_md)+Number(data.pr_md);
		var popUp='<table>'+
					'<tr>'+
						'<td colspan="4"><h6>'+data.kabupaten+'</h6></td>'+
					'</tr>'+
					'<tr>'+
						'<td class="bg-primary" width="20">&nbsp;</td>'+
						'<td>Positif</td>'+
						'<td>'+data.positif+'</td>'+
					'</tr>'+
					'<tr>'+
						'<td class="bg-success"></td>'+
						'<td>Sembuh</td>'+
						'<td>'+data.positif_sembuh+'</td>'+
					'</tr>'+
					'<tr>'+
						'<td class="bg-danger"></td>'+
						'<td>Meninggal</td>'+
						'<td>'+data.md+'</td>'+
					'</tr>'+
					'<tr>'+
						'<td class="bg-warning"></td>'+
						'<td>Prevalensi Pasien Meninggal Dunia</td>'+
						'<td>'+prmd+'</td>'+
					'</tr>'+
					'</table>';
		layer.bindPopup(popUp);

 }

var legend = L.control({position: 'bottomright'});

	legend.onAdd = function (map) {
		var div = L.DomUtil.create('div', 'info legend'),
			grades = [0, 10, 20, 30, 50, 100],
			labels = [],
			from, to;

		for (var i = 0; i < grades.length; i++) {
			from = grades[i];
			to = grades[i + 1];

			labels.push(
				'<i style="background:' + getColor(from + 1) + '"></i> ' +
				from + (to ? '&ndash;' + to : '+'));
		}

		div.innerHTML = labels.join('<br>');
		return div;
	};

	legend.addTo(map);
  
  function getData(){
  	$.ajax({
  		url:api,
  		dataType:'JSON',
  		success:function(data){
  			var features=data.features;
  			for (i=0;i<features.length;i++) {
  				var attributes=features[i];
  				var Kode_Provi=attributes.id;
  				//var prmd=Number(attributes.positif)+Number(attributes.md);
				var prmd = Number(attributes.pr_pdp_md)+Number(attributes.pr_md);

  				dataCovid[Kode_Provi]=attributes;
  				// console.log(attributes);
				var list='<h5>'+attributes.kabupaten+'</h5>'+
							'<span class="text-primary">Positif : '+attributes.positif+'</span>- '+
							'<span class="text-success">Sembuh : '+attributes.positif_sembuh+'</span> -'+
							'<span class="text-danger">Meninggal : '+attributes.md+'</span> -'+
							'<span class="text-warning">Prevalensi Meninggal Dunia: '+prmd+'</span> - '+
							'<span class="text-primary">Tanggal : '+attributes.tgl+'</span> ';
				$(".list-covid").append('<li class="list-group-item" data-id="'+Kode_Provi+'">'+list+'</li>');
  			}
  			for (i=0;i<features.length;i++) {
  				var attributes=features[i];
  				var Kode_Provi=attributes.id;
  				if(Kode_Provi!=0){
	  				$.getJSON("assets/geojason/"+Kode_Provi+".geojson",function(data){
	  					var KODE=data.features[0].properties.Kode;
	  					geojson[KODE]=L.geoJSON(data,{
	  						onEachFeature:onEachFeature,
							style: style, 
	  					}).addTo(map);

	  				});
	  			}
  			}
  		}
  	});
  }

</script>
</html>