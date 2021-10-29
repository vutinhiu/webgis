<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>The first webgis: View Background Map</title>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />

    <link rel="stylesheet" href="https://openlayers.org/en/v4.6.5/css/ol.css" type="text/css" />
    <script src="https://openlayers.org/en/v4.6.5/build/ol.js" type="text/javascript"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.0/jquery.min.js" type="text/javascript"></script>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css"
        integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
    <style>

        .map,
        .righ-panel {
            height: 98vh;
            width: 80vw;
            float: left;
        }

        .map {
            border: 1px solid #000;
        }
       
      .ol-popup {
        position: absolute;
        background-color: white;
        box-shadow: 0 1px 4px rgba(0,0,0,0.2);
        padding: 15px;
        border-radius: 10px;
        border: 1px solid #cccccc;
        bottom: 12px;
        left: -50px;
        min-width: 280px;
      }
      .ol-popup:after, .ol-popup:before {
        top: 100%;
        border: solid transparent;
        content: " ";
        height: 0;
        width: 0;
        position: absolute;
        pointer-events: none;
      }
      .ol-popup:after {
        border-top-color: white;
        border-width: 10px;
        left: 48px;
        margin-left: -10px;
      }
      .ol-popup:before {
        border-top-color: #cccccc;
        border-width: 11px;
        left: 48px;
        margin-left: -11px;
      }
      .ol-popup-closer {
        text-decoration: none;
        position: absolute;
        top: 2px;
        right: 8px;
      }
      .ol-popup-closer:after {
        content: "✖";
      }

    </style>
</head>

<body onload="initialize_map();">
    <table>
        <tr>
            <td>
                <div id="map" class="map"></div>
                <div id="map" style="width: 50vw; height: 50vh;"></div>
                <div id="popup" class="ol-popup">
                    <a href="#" id="popup-closer" class="ol-popup-closer"></a>
                    <div id="popup-content"></div>
                </div>
                
                <!--<div id="map" style="width: 80vw; height: 100vh;"></div>-->
            </td>
            <td>
                <h3>Tìm tỉnh/thành phố:</h3>
                <input class="form-control" type="textinput" id="city" placeholder="Nhập tên tỉnh/thành phố ">
                <br>
                <button type="button" id="btnSearch" class="btn btn-info">Search</button>
                <br />
                <br />
                <br />
                <div class="checkbox">
                    <h3>Chọn layer:</h3>
                    <label><input onclick="oncheckgadm()" type="checkbox" id="gadm" name="layer" value="gadm"> Covid-19 Việt
                        Nam</label>
                    <br>
                    <label><input onclick="oncheckdata2()" type="checkbox" id="data2" name="layer" value="data2">
                        Trung tâm xét nghiệm, trạm kiểm dịch Covid-19 VN</label>
                    <br>
                    <label><input onclick="oncheckdata3()" type="checkbox" id="data3" name="layer" value="data3">
                        Trạm ATM gạo và siêu thị 0 đồng VN</label>
                    <br>
                    <label><input onclick="oncheckgadm2()" type="checkbox" id="gadm2" name="layer" value="gadm2"> Covid-19 Hà Nội</label>
                </div>

                <button id="btnRefresh" class="btn btn-info">REFRESH</button>
                <br />
                <br />
                <br />
                <h3> Số Ca Nhiễm:</h3>
                <img src="http://localhost:8080/geoserver/wms?REQUEST=GetLegendGraphic&VERSION=1.0.0&FORMAT=image/png&WIDTH=20&HEIGHT=20&LAYER=gadm36_vnm_1" />
            </td> 
        </tr>
    </table>
    <?php include 'CMR_pgsqlAPI.php' ?>
    <script>
        //$("#document").ready(function () {
        var format = 'image/png';
        var map;
        var minX = 102.107963562012;
        var minY = 8.30629825592041;
        var maxX = 109.505798339844;
        var maxY = 23.4677505493164;
        var cenX = (minX + maxX) / 2;
        var cenY = (minY + maxY) / 2;
        var mapLat = cenY;
        var mapLng = cenX;
        var mapDefaultZoom = 6;

        var layergadm36_vnm_1;
        var layergadm36_vnm_2;
        var data2;
        var data3;
        var vectorLayer;
        var styleFunction;
        var styles;
        var container = document.getElementById('popup');
        var content = document.getElementById('popup-content');
        var closer = document.getElementById('popup-closer');
        var chkgadm = document.getElementById("gadm");
        var chkgadm2 = document.getElementById("gadm2");
        var chkdata2 = document.getElementById("data2");
        var chkdata3 = document.getElementById("data3");
        var chkcity = document.getElementById("city");
        var value;

    /**
    * Create an overlay to anchor the popup to the map.Lấy dữ liệu tại địa chỉ đúp chuột 
    */
        var overlay = new ol.Overlay( /** @type {olx.OverlayOptions} */({
            element: container,
            autoPan: true,
            autoPanAnimation: {
                duration: 250
            }
        }));
        closer.onclick = function () {
            overlay.setPosition(undefined);
            closer.blur();
            return false;
        };
        function handleOnCheck(id, layer) {
            if (document.getElementById(id).checked) {
                value = document.getElementById(id).value;
                // map.setLayerGroup(new ol.layer.Group())
                map.addLayer(layer)
                vectorLayer = new ol.layer.Vector({});
                map.addLayer(vectorLayer);
            } else {
                map.removeLayer(layer);
                map.removeLayer(vectorLayer);
            }
        }
        function myFunction() {
            var popup = document.getElementById("popup");
            popup.classList.toggle("show");
        }
        function oncheckgadm() {
            handleOnCheck('gadm', layergadm36_vnm_1);
        }
        function oncheckgadm2() {
            handleOnCheck('gadm2', layergadm36_vnm_2);
        }
        function oncheckdata2() {
            handleOnCheck('data2', data2);
        }
        function oncheckdata3() {
            handleOnCheck('data3', data3);
        }
        function initialize_map() {
            //* Hiển thị cả map
            layerBG = new ol.layer.Tile({
                source: new ol.source.OSM({})
            });
            //*/ Hiển thị từng dữ liệu trong geoserver
            layergadm36_vnm_1 = new ol.layer.Image({
                source: new ol.source.ImageWMS({
                    ratio: 1,
                    url: 'http://localhost:8080/geoserver/BTL/wms',
                    params: {
                        'FORMAT': format,
                        'VERSION': '1.1.0',
                        STYLES: '',
                        LAYERS: 'gadm36_vnm_1',
                    }
                })
            });
            layergadm36_vnm_2 = new ol.layer.Image({
                source: new ol.source.ImageWMS({
                    ratio: 1,
                    url: 'http://localhost:8080/geoserver/BTL/wms',
                    params: {
                        'FORMAT': format,
                        'VERSION': '1.1.0',
                        STYLES: '',
                        LAYERS: 'gadm36_vnm_2',
                    }
                })
            });
            data2 = new ol.layer.Image({
                source: new ol.source.ImageWMS({
                    ratio: 1,
                    url: 'http://localhost:8080/geoserver/BTL/wms',
                    params: {
                        'FORMAT': format,
                        'VERSION': '1.1.0',
                        STYLES: '',
                        LAYERS: 'data2',
                    }
                })
            });
            data3 = new ol.layer.Image({
                source: new ol.source.ImageWMS({
                    ratio: 1,
                    url: 'http://localhost:8080/geoserver/BTL/wms',
                    params: {
                        'FORMAT': format,
                        'VERSION': '1.1.0',
                        STYLES: '',
                        LAYERS: 'data3',
                    }
                })
            });
            var viewMap = new ol.View({
                center: ol.proj.fromLonLat([mapLng, mapLat]),
                zoom: mapDefaultZoom
                //projection: projection
            });
            map = new ol.Map({
                target: "map",
                layers: [layerBG],
                view: viewMap,
                overlays: [overlay], //them khai bao overlays
            });
            //map.getView().fit(bounds, map.getSize());
            //style màu khoảng khi ấn vào điểm cần chọn
            styles = {
                'Point': new ol.style.Style({
                    stroke: new ol.style.Stroke({
                        color: 'yellow',
                        width: 3
                    })
                }),
                'MultiPolygon': new ol.style.Style({
                    fill: new ol.style.Fill({//phần trong
                        color: 'orange'
                    }),
                    stroke: new ol.style.Stroke({// phần rìa
                        color: 'yellow',
                        width: 2
                    })
                })
            };
            styleFunction = function (feature) {
                return styles[feature.getGeometry().getType()];
            };
            vectorLayer = new ol.layer.Vector({
                //source: vectorSource,
                style: styleFunction
            });
            map.addLayer(vectorLayer);

            var button = document.getElementById("btnSearch").addEventListener("click",
                () => {
                    vectorLayer.setStyle(styleFunction);
                    chkcity.value.length ?
                        $.ajax({
                            type: "POST",
                            url: "CMR_pgsqlAPI.php",
                            data: {
                                name: chkcity.value
                            },
                            success: function (result, status, erro) {

                                if (result == 'null')
                                    alert("không tìm thấy đối tượng");
                                else
                                    highLightObj(result);
                            },
                            error: function (req, status, error) {
                                alert(req + " " + status + " " + error);
                            }
                        }) : alert("Nhập dữ liệu tìm kiếm")
                });
            var buttonRefresh = document.getElementById("btnRefresh").addEventListener("click", () => {
                location.reload();
                });
            function createJsonObj(result) {
                var geojsonObject = '{'
                    + '"type": "FeatureCollection",'
                    + '"crs": {'
                    + '"type": "name",'
                    + '"properties": {'
                    + '"name": "EPSG:4326"'
                    + '}'
                    + '},'
                    + '"features": [{'
                    + '"type": "Feature",'
                    + '"geometry": ' + result
                    + '}]'
                    + '}';
                return geojsonObject;
            }
            function highLightGeoJsonObj(paObjJson) {
                var vectorSource = new ol.source.Vector({
                    features: (new ol.format.GeoJSON()).readFeatures(paObjJson, {
                        dataProjection: 'EPSG:4326',
                        featureProjection: 'EPSG:3857'
                    })
                });
                vectorLayer.setSource(vectorSource);
            }

            function highLightObj(result) {
                var strObjJson = createJsonObj(result);
                var objJson = JSON.parse(strObjJson);
                highLightGeoJsonObj(objJson);
            }

            function displayObjInfo(result, coordinate) {
                $("#popup-content").html(result);
                overlay.setPosition(coordinate);

            }
            map.on('singleclick', function (evt) {
                //alert("coordinate org: " + evt.coordinate);
                var myPoint = 'POINT(12,5)';
                var lonlat = ol.proj.transform(evt.coordinate, 'EPSG:3857', 'EPSG:4326');
                var lon = lonlat[0];
                var lat = lonlat[1];
                var myPoint = 'POINT(' + lon + ' ' + lat + ')';
                //alert("myPoint: " + myPoint);
                //*
                if (value == 'gadm') {
                    vectorLayer.setStyle(styleFunction);
                    $.ajax({
                        type: "POST",
                        url: "CMR_pgsqlAPI.php",
                        data: {
                            functionname: 'getInfoCMRToAjax',
                            paPoint: myPoint
                        },
                        success: function (result, status, erro) {
                            displayObjInfo(result, evt.coordinate);
                        },
                        error: function (req, status, error) {
                            alert(req + " " + status + " " + error);
                        }
                    });
                    $.ajax({
                        type: "POST",
                        url: "CMR_pgsqlAPI.php",
                        data: {
                            functionname: 'getGeoCMRToAjax',
                            paPoint: myPoint
                        },
                        success: function (result, status, erro) {
                            highLightObj(result);
                        },
                        error: function (req, status, error) {
                            alert(req + " " + status + " " + error);
                        }
                    });
                }
                if (value == 'gadm2') {
                    vectorLayer.setStyle(styleFunction);
                    $.ajax({
                        type: "POST",
                        url: "CMR_pgsqlAPI.php",
                        data: {
                            functionname: 'getInfoCMR2ToAjax',
                            paPoint: myPoint
                        },
                        success: function (result, status, erro) {
                            displayObjInfo(result, evt.coordinate);
                        },
                        error: function (req, status, error) {
                            alert(req + " " + status + " " + error);
                        }
                    });
                    $.ajax({
                        type: "POST",
                        url: "CMR_pgsqlAPI.php",
                        data: {
                            functionname: 'getGeoCMR2ToAjax',
                            paPoint: myPoint
                        },
                        success: function (result, status, erro) {
                            highLightObj(result);
                        },
                        error: function (req, status, error) {
                            alert(req + " " + status + " " + error);
                        }
                    });
                }
                if (value == 'data2') {
                    vectorLayer.setStyle(styleFunction);

                    $.ajax({
                        type: "POST",
                        url: "CMR_pgsqlAPI.php",
                        data: {
                            functionname: 'getInfoData2ToAjax',
                            paPoint: myPoint
                        },
                        success: function (result, status, erro) {
                            displayObjInfo(result, evt.coordinate);
                        },
                        error: function (req, status, error) {
                            alert(req + " " + status + " " + error);
                        }
                    });
                    $.ajax({
                        type: "POST",
                        url: "CMR_pgsqlAPI.php",
                        data: {
                            functionname: 'getData2ToAjax',
                            paPoint: myPoint
                        },
                        success: function (result, status, erro) {
                            highLightObj(result);
                        },
                        error: function (req, status, error) {
                            alert(req + " " + status + " " + error);
                        }
                    });
                }
                if (value == 'data3') {
                    vectorLayer.setStyle(styleFunction);

                    $.ajax({
                        type: "POST",
                        url: "CMR_pgsqlAPI.php",
                        data: {
                            functionname: 'getInfoData3ToAjax',
                            paPoint: myPoint
                        },
                        success: function (result, status, erro) {
                            displayObjInfo(result, evt.coordinate);
                        },
                        error: function (req, status, error) {
                            alert(req + " " + status + " " + error);
                        }
                    });
                    $.ajax({
                        type: "POST",
                        url: "CMR_pgsqlAPI.php",
                        data: {
                            functionname: 'getData3ToAjax',
                            paPoint: myPoint
                        },
                        success: function (result, status, erro) {
                            highLightObj(result);
                        },
                        error: function (req, status, error) {
                            alert(req + " " + status + " " + error);
                        }
                    });
                }
            });
        };
        //});
    </script>
</body>
</html>