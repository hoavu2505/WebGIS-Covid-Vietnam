<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <title>OpenStreetMap &amp; OpenLayers - Marker Example</title>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/openlayers/openlayers.github.io@master/en/v6.6.0/css/ol.css" type="text/css" />
        <script src="https://cdn.jsdelivr.net/gh/openlayers/openlayers.github.io@master/en/v6.6.0/build/ol.js" type="text/javascript"></script>
        
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.0/jquery.min.js" type="text/javascript"></script>

        <style>
            /*
            .map, .righ-panel {
                height: 500px;
                width: 80%;
                float: left;
            }
            */
            .map, .righ-panel {
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
            -webkit-filter: drop-shadow(0 1px 4px rgba(0,0,0,0.2));
            filter: drop-shadow(0 1px 4px rgba(0,0,0,0.2));
            padding: 15px;
            border-radius: 10px;
            border: 1px solid #cccccc;
            bottom: 12px;
            left: -50px;
            min-width: 180px;
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
            
            /* .ol-popup-closer:after {
            content: "✖";
            } */
        </style>

    </head>
    <body onload="initialize_map();">

        <div id="popup" class="ol-popup">
            <a href="#" id="popup-closer" class="ol-popup-closer"></a>            
            <div id="popup-content"></div>
        
        </div>

        <table>
            <tr>
                <td>
                    <div id="map" class="map"></div>
                    <!--<div id="map" style="width: 80vw; height: 100vh;"></div>-->
                </td>
                <td>
                    <input type="checkbox" id="cbVietNam" name="covid" checked><label for="cbVietNam">Việt Nam</label><br>
                    <input type="checkbox" id="cbHaNoi" name="covid" ><label for="cbHaNoi">Hà Nội</label><br>
                    <input type="checkbox" id="cbHCM" name="covid" ><label for="cbHCM">Tp.Hồ Chí Minh</label>
                </td>
            </tr>
        </table>

        <?php include 'Vietnam_pgsqlAPI.php' ?>
        <script>
            var format = 'image/png';
            var map;
            var minX = 102.14458465576172;
            var minY = 8.381355285644531;
            var maxX = 109.46917724609375;
            var maxY = 23.3926944732666;
            var cenX = (minX + maxX) / 2;
            var cenY = (minY + maxY) / 2;
            var mapLat = cenY;
            var mapLng = cenX;
            var mapDefaultZoom = 6;
            function initialize_map() {
                //*
                layerBG = new ol.layer.Tile({
                    source: new ol.source.OSM({})
                });
                //*/
                var layerCMR_adm1 = new ol.layer.Image({
                    source: new ol.source.ImageWMS({
                        ratio: 1,
                        url: 'http://localhost:8080/geoserver/example/wms?',
                        params: {
                            'FORMAT': format,
                            'VERSION': '1.1.1',
                            STYLES: '',
                            LAYERS: 'gadm36_vnm_1',
                        }
                    })
                });

                var layerCovid_HaNoi = new ol.layer.Image({
                    source: new ol.source.ImageWMS({
                        ratio: 1,
                        url: 'http://localhost:8080/geoserver/example/wms?',
                        params: {
                            'FORMAT': format,
                            'VERSION': '1.1.1',
                            STYLES: '',
                            LAYERS: 'covid_hanoi',
                        }
                    })
                })

                var layerCovid_HCM = new ol.layer.Image({
                    source: new ol.source.ImageWMS({
                        ratio: 1,
                        url: 'http://localhost:8080/geoserver/example/wms?',
                        params: {
                            'FORMAT': format,
                            'VERSION': '1.1.1',
                            STYLES: '',
                            LAYERS: 'covid_hcm',
                        }
                    })
                })

                var viewMap = new ol.View({
                    center: ol.proj.fromLonLat([mapLng, mapLat]),
                    zoom: mapDefaultZoom
                    //projection: projection
                });
                map = new ol.Map({
                    target: "map",
                    // layers: [layerBG, layerCMR_adm1],
                    layers: [layerCMR_adm1, layerCovid_HaNoi, layerCovid_HCM],
                    overlays: [overlay],//them khai bao overlays
                    view: viewMap
                });


                const highlightStyle = new ol.style.Style({
                stroke: new ol.style.Stroke({
                    color: '#f00',
                    width: 2,
                }),
                fill: new ol.style.Fill({
                    color: 'rgba(255,0,0,0.1)',
                }),
                text: new ol.style.Text({
                    font: '12px Calibri,sans-serif',
                    fill: new ol.style.Fill({
                    color: '#000',
                    }),
                    stroke: new ol.style.Stroke({
                    color: '#f00',
                    width: 3,
                    }),
                }),
                });

                var styles = {
                    'MultiPolygon': new ol.style.Style({
                        fill: new ol.style.Fill({
                            color: '#DDDDDD'
                        }),
                        stroke: new ol.style.Stroke({
                            color: 'black', 
                            width: 0.5
                        })
                    })
                }; 

                var styles1 = {
                    'MultiPolygon': new ol.style.Style({
                        fill: new ol.style.Fill({
                            color: '#e6ff99'
                        }),
                        stroke: new ol.style.Stroke({
                            color: 'black', 
                            width: 0.5
                        })
                    })
                };

                var styles2 = {
                    'MultiPolygon': new ol.style.Style({
                        fill: new ol.style.Fill({
                            color: 'yellow'
                        }),
                        stroke: new ol.style.Stroke({
                            color: 'black', 
                            width: 0.5
                        })
                    })
                };
				
                var styles3 = {
                    'MultiPolygon': new ol.style.Style({
                        fill: new ol.style.Fill({
                            color: 'orange'
                        }),
                        stroke: new ol.style.Stroke({
                            color: 'black', 
                            width: 0.5
                        })
                    })
                };

                var styles4 = {
                    'MultiPolygon': new ol.style.Style({
                        fill: new ol.style.Fill({
                            color: 'red'
                        }),
                        stroke: new ol.style.Stroke({
                            color: 'black', 
                            width: 0.5
                        })
                    })
                };
               
                var styleFunction = function (feature) {
                    return styles[feature.getGeometry().getType()];
                };
                var styleFunction1 = function (feature) {
                    return styles1[feature.getGeometry().getType()];
                };
                var styleFunction2 = function (feature) {
                    return styles2[feature.getGeometry().getType()];
                };
                var styleFunction3 = function (feature) {
                    return styles3[feature.getGeometry().getType()];
                };
                var styleFunction4 = function (feature) {
                    return styles4[feature.getGeometry().getType()];
                };

                var vectorLayer = new ol.layer.Vector({
                    //source: vectorSource,
                    style: styleFunction
                });
                var vectorLayer1 = new ol.layer.Vector({
                    //source: vectorSource,
                    style: styleFunction1
                });
                var vectorLayer2 = new ol.layer.Vector({
                    //source: vectorSource,
                    style: styleFunction2
                });
                var vectorLayer3 = new ol.layer.Vector({
                    //source: vectorSource,
                    style: styleFunction3
                });
                var vectorLayer4 = new ol.layer.Vector({
                    //source: vectorSource,
                    style: styleFunction4
                });

                // map.addLayer(vectorLayer);
                // map.addLayer(vectorLayer1);
                // map.addLayer(vectorLayer2);
                // map.addLayer(vectorLayer3);
                // map.addLayer(vectorLayer4);

                $("#cbVietNam").change(function () {
                    if($("#cbVietNam").is(":checked"))
                    {
                        layerCMR_adm1.setVisible(true);
                    }
                    else{
                        layerCMR_adm1.setVisible(false);
                    }
                }
                );

                $("#cbHaNoi").change(function () {
                    if($("#cbHaNoi").is(":checked"))
                    {
                        layerCovid_HaNoi.setVisible(true);
                    }
                    else{
                        layerCovid_HaNoi.setVisible(false);
                    }
                }
                );

                $("#cbHCM").change(function () {
                    if($("#cbHCM").is(":checked"))
                    {
                        layerCovid_HCM.setVisible(true);
                    }
                    else{
                        layerCovid_HCM.setVisible(false);
                    }
                }
                );

                function createJsonObj(result) {                    
                    var geojsonObject = '{'
                            + '"type": "FeatureCollection",'
                            + '"crs": {'
                                + '"type": "name",'
                                + '"properties": {'
                                    + '"name": "EPSG:4326"'
                                + '}'
                            + '},'
                            + '"features": [';
                    if(Array.isArray(result)){
                        for(data of result){
                        geojsonObject+='{'
                                + '"type": "Feature",'
                                + '"properties": {"name_1": "'+data['name_1']
                                // CHINH O DAY LA 2
                                +'", "canhiem":"'+data['canhiem']
                                +'", "dangdieutri":"'+data['dangdieutri']
                                +'", "binhphuc":"'+data['binhphuc']
                                +'", "tuvong":"'+data['tuvong']
                                +'"},'
                                + '"geometry": ' + JSON.stringify(data['geo']) +
                            '},'
                        }
                        geojsonObject=geojsonObject.slice(0,-1);      
                        geojsonObject+=']}';                  
                    }  
                    
                    else {
                        geojsonObject+='{'
                                + '"type": "Feature",'
                                + '"geometry": ' + result
                            + '}]'
                        + '}';
                    }
                    
                    return geojsonObject;
                }

                function displayObjInfo(result, coordinate)
                {
					// $("#info").html(result);
                    $("#popup-content").html(result);
                }

                function highLightGeoJsonObj(paObjJson,type) {
                    var vectorSource = new ol.source.Vector({
                        features: (new ol.format.GeoJSON()).readFeatures(paObjJson, {
                            dataProjection: 'EPSG:4326',
                            featureProjection: 'EPSG:3857'
                        })
                    });
                    console.log(vectorSource);
                    if(type==0)
                        vectorLayer.setSource(vectorSource);
                    if(type==1)					
					    vectorLayer1.setSource(vectorSource);
                    if(type==2)
                        vectorLayer2.setSource(vectorSource);
                    if(type==3)
                        vectorLayer3.setSource(vectorSource);
                    if(type==4)
                        vectorLayer4.setSource(vectorSource);
                }

                function highLightObj(result,type) {
                    var strObjJson = createJsonObj(result);
                    // console.log(strObjJson);
                    var objJson = JSON.parse(strObjJson);
                    // console.log(JSON.stringify(objJson));
                    highLightGeoJsonObj(objJson,type);
                }
                let selected = null;

                map.once('postrender', function(event) {
                    // console.log("test")
                    $.ajax({
                            type: "POST",
                            url: "Vietnam_pgsqlAPI.php",
                            //dataType: 'json',
                            data: {functionname: 'getLayermap'},
                            success : function (response) {
                               
                               var objJson = JSON.parse(response);
                               console.log(objJson);
                            // console.log(objJson)
                                highLightObj(objJson,0);
                            },
                            error: function (req, status, error) {
                                alert(req + " " + status + " " + error);
                            }
                        });
                    for(let i=1;i<5;i++){
                    let min1;
                    let max1;
                    let type=i;
                    if(i==1) min1=1,max1=5;
                    if(i==2) min1=6,max1=20;
                    if(i==3) min1=21,max1=50;
                    if(i==4) min1=51,max1=9999999;
                        $.ajax({
                            type: "POST",
                            url: "Vietnam_pgsqlAPI.php",
                            //dataType: 'json',
                            data: {functionname: 'getGeoCovidToAjax', min: min1, max:max1},
                            success : function (response) {
                               
                               var objJson = JSON.parse(response);
                            //    console.log(response);

                                highLightObj(objJson,type);
                            },
                            error: function (req, status, error) {
                                alert(req + " " + status + " " + error);
                            }
                        });
                    }
                });
                map.on('pointermove', function (e) {
                    if (selected !== null) {
                        selected.setStyle(undefined);
                        selected = null;
                    }

                    map.forEachFeatureAtPixel(e.pixel, function (f) {
                        selected = f;
                        f.setStyle(highlightStyle);
                        // console.log(f['j']['varname_1']+"\n"+f['j']['canhiem']);
                        displayObjInfo("<strong><center>" + f.getProperties()['name_1'] + "</center></strong>"
                                    +"<br>Ca nhiễm: <label style= 'color: white; background-color: #CD113B; border-radius: 10px; padding: 3px' >" + f.getProperties()['canhiem'] + "</label><br>"
                                    +"<br>Đang điều trị: <label style= 'color: white; background-color: #FFA900; border-radius: 25px; padding: 3px' >" + f.getProperties()['dangdieutri'] + "</label><br>"
                                    +"<br>Bình phục: <label style= 'color: white; background-color: #50CB93; border-radius: 25px; padding: 3px' >" +f.getProperties()['binhphuc'] + "</label><br>"
                                    +"<br>Tử vong: <label style= 'color: white; background-color: #2C2E43; border-radius: 25px; padding: 3px' >" +f.getProperties()['tuvong'] + "</label><br>"
                                    , overlay.setPosition(e.coordinate));
                        
                        return true;
                    });
                   
                });
            
            };
            /**
            * Elements that make up the popup.
            */
            var container = document.getElementById('popup');
            var content = document.getElementById('popup-content');
            var closer = document.getElementById('popup-closer');
            
            /**
            * Create an overlay to anchor the popup to the map.
            */
            var overlay = new ol.Overlay(/** @type {olx.OverlayOptions} */({
            element: container,
            }));
            /**
            * Add a click handler to hide the popup.
            * @return {boolean} Don't follow the href.
            */
            // closer.onclick = function () {
            // overlay.setPosition(undefined);
            // closer.blur();
            // return false;
            // };

            container.onmouseover = function() {
                overlay.setPosition(undefined);
                return false;
            };

        </script>

        <!-- Update data -->
        <script>
            var xmlHttp = new XMLHttpRequest();
            xmlHttp.open("GET", "https://data.opendevelopmentmekong.net/vi/api/3/action/datastore_search?resource_id=b15e8f4b-c905-48fb-973e-d412e2759f55", false ); // false for synchronous request
            xmlHttp.send( null );
            var data = xmlHttp.responseText;
            var arrayData = JSON.parse(data)
            // console.log(arrayData['result']['records']);
            $.ajax({
                type: "POST",
                url: "Vietnam_pgsqlAPI.php",
                data: {functionname: 'updateData', data: arrayData['result']['records']},
                success : function (result, status, erro) {
                    // console.log(result);
                }
            });
        </script>

    </body>
</html>