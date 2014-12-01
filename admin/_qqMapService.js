var qqMapBean = {
	map : null,
	geocoder : null,
	address : null,
	markerArray : [],
};
var qqMapService = {
	init : function() {
		
		var center = new qq.maps.LatLng(parent.document.getElementById("h_map_lat").value||39.916527,parent.document.getElementById("h_map_lng").value||116.397128);
		qqMapBean.map = new qq.maps.Map(document.getElementById("container"), {
			center : center,
			zoom : 13
		});
			var marker = new qq.maps.Marker({
				map : qqMapBean.map,
				position : center
			});
			qqMapBean.markerArray.push(marker);
		// 初始化地址解析对象
		qqMapBean.geocoder = new qq.maps.Geocoder({
			complete : function(result) {
				qqMapService.clearOverlays();
				qqMapBean.map.setCenter(result.detail.location);
				// 返回值(结果)
				// document.getElementById("result").innerHTML = result.detail.address + "---" + result.detail.location;
				parent.document.getElementById("h_map_lat").value = result.detail.location.lat;
				parent.document.getElementById("h_map_lng").value = result.detail.location.lng;
				// console.log('lat:' + parent.document.getElementById("h_map_lat").value + 'lng:' + parent.document.getElementById("h_map_lng").value);
				var marker = new qq.maps.Marker({
					map : qqMapBean.map,
					position : result.detail.location
				});
				qqMapBean.markerArray.push(marker);
			}
		});
		// 添加地图点击
		qq.maps.event.addListener(qqMapBean.map, "click", function(e) {
			qqMapService.clearOverlays();
			qqMapService.codeLatLng(e.latLng);
			// qqMapBean.map.setCenter(e.latLng);
			var marker = new qq.maps.Marker({
				map : qqMapBean.map,
				position : e.latLng
			});
			qqMapBean.markerArray.push(marker);
		});
	},
	// 反地址解析
	codeLatLng : function(latLng) {
		var lat = parseFloat(latLng.lat);
		var lng = parseFloat(latLng.lng);
		var latLng = new qq.maps.LatLng(lat, lng);
		qqMapBean.geocoder.getAddress(latLng);
	},
	// 地址解析
	codeAddress : function() {
		var selCities = parent.document.getElementById("selCities");
		var selDistricts = parent.document.getElementById("selDistricts");
		var city = selCities.options[selCities.selectedIndex].text;
		var district = selDistricts.options[selDistricts.selectedIndex].text;
		var address = document.getElementById("address").value;
		// alert('city:' + city + ' district:' + district);
		var marker = "";
		if(selDistricts == ''){
			marker = city + ',' + address;
		}else{
			marker = city + ',' + district +　',' + address;
		}
		if(marker == ''){
			marker = address;	
		}
		qqMapBean.geocoder.getLocation(marker);
	},
	// 清除标记
	clearOverlays : function() {
		if (qqMapBean.markerArray) {
			for (var i = 0; i < qqMapBean.markerArray.length; i++) {
				qqMapBean.markerArray[i].setMap(null);
			}
		}
	}
};
