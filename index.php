<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <title>OpenStreetMap &amp; OpenLayers - Marker Example</title>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/openlayers/openlayers.github.io@master/en/v6.6.0/css/ol.css" type="text/css" />
        <script src="https://cdn.jsdelivr.net/gh/openlayers/openlayers.github.io@master/en/v6.6.0/build/ol.js" type="text/javascript"></script>
        
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.0/jquery.min.js" type="text/javascript"></script>
        <script src="js/csv_to_object.js"></script>    

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
            background-color: rgb(242, 242, 242);
            background-color: rgba(242, 242, 242, 0.7);
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
            
            .info{
                font-family:'Poppins',sans-serif;
                text-align:left;
            }
         
            .info div label{
                cursor: pointer;
            }
            .info div label input[type="checkbox"]{
                display:none;
            }
            .info div label span{
               position: relative;
               display:inline-block;
               background: #fff;
                padding:5px 5px;
                margin-block-end:10px;
                color: #000;
                text-shadow:0 1px 4px rgba(0,0,0,.5);
                border-radius:0px;
                font-size:13px;
                transition:0.5s;
                user-select:none;
                width:100px;
                height:100%;
            }
            .info div label span:before{
                content:'';
                width: 100%;
                height:50%;
                background: rgba(255,255,255,.1);
            }
            .info div:nth-child(1) label input[type="checkbox"]:checked ~ span{
                background:#e6ff99;
                color: #000;
                box-shadow:0 2px 10px #e6ff99;
            }
            .info div:nth-child(2) label input[type="checkbox"]:checked ~ span{
                background:yellow;
                color:#000;
                box-shadow:0 2px 20px yellow;
            }
            .info div:nth-child(3) label input[type="checkbox"]:checked ~ span{
                background:orange;
                color:#000;
                box-shadow:0 2px 20px orange;
            }
            .info div:nth-child(4) label input[type="checkbox"]:checked ~ span{
                background:red;
                color: #000;;
                box-shadow:0 2px 20px red;
            }

            /* .ol-popup-closer:after {
            content: "✖";
            } */

            body {
                background-color:#FDF6F0
            }
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
                    <input type="checkbox" id="cbHCM" name="covid" ><label for="cbHCM">Tp.Hồ Chí Minh</label><br>
                    <br>
                    <div class="info">
                        <div>
                            <label name="covid" for="cbV1"><input id="cbV1" type="checkbox" checked><span>Vùng 1 (1-5)</span></label><br>
                        </div> 
                        <div>
                            <label name="covid" for="cbV2"><input id="cbV2"  type="checkbox" checked><span>Vùng 2 (6-20)</span></label><br>
                        </div>
                        <div>
                            <label name="covid" for="cbV3"><input type="checkbox" id="cbV3" checked><span>Vùng 3 (21-50)</span></label><br>
                        </div>
                        <div>
                            <label name="covid" for="cbV4"><input type="checkbox" id="cbV4" checked><span>Vùng 4 (>50)</span></label><br>
                        </div>
                    </div>                    
                </td>
         
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


                layerCovid_HaNoi.setVisible(false);
                layerCovid_HCM.setVisible(false);

                const highlightStyle = new ol.style.Style({
                    stroke: new ol.style.Stroke({
                        color: 'black',
                        width: 2,
                    }),
                    fill: new ol.style.Fill({
                        color: '#6699ff',
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

                var getstyle = function(type){
                    let fillcl,strokecl='black';
                    switch(type){
                        case 0:
                        {
                            fillcl='#DDDDDD';
                            break;
                        }
                        case 1:
                        {
                            fillcl='#e6ff99';
                            break;
                        }
                        case 2:{
                            fillcl='yellow';
                            break;
                        }
                        case 3:{
                            fillcl='orange';
                            break;
                        }
                        case 4:{
                            fillcl='red';
                            break;
                        }
                    }
                    return {
                        'MultiPolygon': new ol.style.Style({
                            fill: new ol.style.Fill({
                                color: fillcl
                            }),
                            stroke: new ol.style.Stroke({
                                color: strokecl, 
                                width: 0.5
                            })
                        })
                    }          
                }
                
                var vector_vn =[],vector_hn=[],vector_hcm = [];
                // var vectorLayer = new ol.layer.Vector({});
                

                function view(check,position){
                    if(check){
                        // map.addLayer(vectorLayer);
                        if(position=="VN")
                        layerCMR_adm1.setVisible(true);
                        if(position=="HN"){
                            layerCovid_HaNoi.setVisible(true);
                        }
                        
                        
                        if(position=="HCM")
                        layerCovid_HCM.setVisible(true);
                        


                        for(let i=0;i<5;i++){
                            // ADD ARR LAYER
                        if(position=="VN"){
                            vector_vn.push(new ol.layer.Vector({}));
                            if(!$("#cbV"+i).is(":checked")&&i!=0){
                                console.log("bo qua vung 1");
                            }
                            else
                            map.addLayer(vector_vn[i]);
                        }
                        if(position=="HN"){
                            vector_hn.push(new ol.layer.Vector({}));
                            if(!$("#cbV"+i).is(":checked")&&i!=0){
                                console.log("bo qua vung 1");
                            }
                            else
                                map.addLayer(vector_hn[i]);
                                vector_hn[i].setZIndex(99);
                        }
                        if(position=="HCM"){
                            vector_hcm.push(new ol.layer.Vector({}));
                            if(!$("#cbV"+i).is(":checked")&&i!=0){
                                console.log("bo qua vung 1");
                            }
                            else
                                map.addLayer(vector_hcm[i]);
                                vector_hcm[i].setZIndex(99);
                            
                            
                        }

                        let min1,max1,type=i;
                        if(i==0) min1=-1,max1=-1;
                        if(i==1) min1=1,max1=5;
                        if(i==2) min1=6,max1=20;
                        if(i==3) min1=21,max1=50;
                        if(i==4) min1=51,max1=-1;
                        
                            $.ajax({
                                type: "POST",
                                url: "Vietnam_pgsqlAPI.php",
                                //dataType: 'json',
                                data: {functionname: 'getGeoCovidToAjax', pos: position, min: min1, max:max1},
                                success : function (response) {
                                
                                response = response.replaceAll("\\",'').replaceAll('"{','{').replaceAll('}"','}');
                                // console.log(response);
                                var objJson = JSON.parse(response);
                                if(objJson.length>0)
                                highLightGeoJsonObj(createJsonObj(objJson),position,type);
                                
                                },
                                error: function (req, status, error) {
                                    alert(req + " " + status + " " + error);
                                }
                            });
                        }

                        
                        
                    }
                    else{
                        // console.log("chua check")
                        if(position=="VN")
                        layerCMR_adm1.setVisible(false);
                        if(position=="HN")
                        layerCovid_HaNoi.setVisible(false);
                        if(position=="HCM")
                        layerCovid_HCM.setVisible(false);
                        // map.removeLayer(vectorLayer)
                        for(let i=0;i<=5;i++){
                            if(position=="VN")
                                map.removeLayer(vector_vn[i]);
                            if(position=="HN")
                                map.removeLayer(vector_hn[i]);
                            if(position=="HCM")
                                map.removeLayer(vector_hcm[i]);
                        }    
                        // DOAN NAY K CAN THIET
                        if(position=="VN") vector_vn=[];
                        if(position=="HN") vector_hn=[];
                        if(position=="HCM") vector_hcm=[];

                        // console.log(vector_vn.length);
                    }      
                }
                
                view($("#cbVietNam").is(":checked"),"VN");
                $("#cbVietNam").change(function(){
                    view($("#cbVietNam").is(":checked"),"VN");
                });
            
                $("#cbHaNoi").change(function () {
                    view($("#cbHaNoi").is(":checked"),"HN");                    
                }
                );

                $("#cbHCM").change(function () {
                    view($("#cbHCM").is(":checked"),"HCM");
                }
                );

                for(let i=1;i<5;i++)
                $("#cbV"+i).change(function(){
                    if(!$("#cbV"+i).is(":checked")){
                        if($("#cbVietNam").is(":checked"))
                            try{
                                map.removeLayer(vector_vn[i]);
                            }
                            catch(E){}
                        if($("#cbHaNoi").is(":checked"))
                            try{
                                map.removeLayer(vector_hn[i]);
                            }
                            catch(E){}
                        if($("#cbHCM").is(":checked"))
                            try{
                                map.removeLayer(vector_hcm[i]);
                            }
                            catch(E){}
                    }
                    else{
                        if($("#cbVietNam").is(":checked"))
                        try{
                            map.addLayer(vector_vn[i]);
                        }
                        catch(E){}
                        if($("#cbHaNoi").is(":checked"))
                        try{
                            map.addLayer(vector_hn[i]);
                        }
                        catch(E){}
                        if($("#cbHCM").is(":checked"))
                        try{
                            map.addLayer(vector_hcm[i]);
                        }
                        catch(E){}
                    }

                })

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
                                +'", "name_2":"'+data['name_2']
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

                function highLightGeoJsonObj(paObjJson,pos,type) {
                    var vectorSource = new ol.source.Vector({
                        features: (new ol.format.GeoJSON()).readFeatures(paObjJson, {
                            dataProjection: 'EPSG:4326',
                            featureProjection: 'EPSG:3857'
                        })
                    });
                    
                    // console.log(vectorLayer)
                    if(pos=="VN"){
                        vectorSource.forEachFeature(function(feature){ vector_vn[type].setStyle(getstyle(type)[feature.getGeometry().getType()])})
                        vector_vn[type].setSource(vectorSource);
                        if(vector_vn[0].getSource()!==null){
                                var layerExtent = vector_vn[0].getSource().getExtent();
                                map.getView().fit(layerExtent);
                        }
                    }
                    if(pos=="HN"){
                        vectorSource.forEachFeature(function(feature){ vector_hn[type].setStyle(getstyle(type)[feature.getGeometry().getType()])})
                        vector_hn[type].setSource(vectorSource);
                        // if(position=="HN"){
                            if(vector_hn[0].getSource()!==null){
                                var layerExtent = vector_hn[0].getSource().getExtent();
                                map.getView().fit(layerExtent);
                            }
                            
                        // }
                    }
                    if(pos=="HCM"){
                        vectorSource.forEachFeature(function(feature){ vector_hcm[type].setStyle(getstyle(type)[feature.getGeometry().getType()])})
                        vector_hcm[type].setSource(vectorSource);
                        if(vector_hcm[0].getSource()!==null){
                                var layerExtent = vector_hcm[0].getSource().getExtent();
                                map.getView().fit(layerExtent);
                            }
                    }
                    
                }

                let selected = null;
                map.on('pointermove', function (e) {
                    if (selected !== null) {
                        selected.setStyle(undefined);
                        selected = null;
                    }

                    map.forEachFeatureAtPixel(e.pixel, function (f) {
                        selected = f;
                        f.setStyle(highlightStyle);
                        // console.log(f['j']['varname_1']+"\n"+f['j']['canhiem']);
                        let content="";
                        if(f.getProperties()['name_2']!=="undefined") 
                            content +="<strong><center>" + f.getProperties()['name_2'] + "</center></strong>"
                        else 
                            content +="<strong><center>" + f.getProperties()['name_1'] + "</center></strong>";

                        content += "<br>Ca nhiễm: <label style= 'color: white; background-color: #CD113B; border-radius: 10px; padding: 3px' >" + f.getProperties()['canhiem'] + "</label><br>"
                        if(f.getProperties()['dangdieutri']!=="undefined")            
                            content +="<br>Đang điều trị: <label style= 'color: white; background-color: #FFA900; border-radius: 25px; padding: 3px' >" + f.getProperties()['dangdieutri'] + "</label><br>"
                        if(f.getProperties()['binhphuc']!=="undefined")
                            content +="<br>Bình phục: <label style= 'color: white; background-color: #50CB93; border-radius: 25px; padding: 3px' >" +f.getProperties()['binhphuc'] + "</label><br>"
                        if(f.getProperties()['tuvong']!=="undefined")
                            content +="<br>Tử vong: <label style= 'color: white; background-color: #2C2E43; border-radius: 25px; padding: 3px' >" +f.getProperties()['tuvong'] + "</label><br>"
                                    
                        displayObjInfo(content, overlay.setPosition(e.coordinate));
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

            container.onmouseover = function() {
                overlay.setPosition(undefined);
                return false;
            };

        </script>

        <!-- Update data -->
        <script>
            // var xmlHttp = new XMLHttpRequest();
            // xmlHttp.open("GET", "https://data.opendevelopmentmekong.net/vi/api/3/action/datastore_search?resource_id=b15e8f4b-c905-48fb-973e-d412e2759f55", false ); // false for synchronous request
            // xmlHttp.send( null );
            // var data = xmlHttp.responseText;

            var rawFilevn = new XMLHttpRequest();
            rawFilevn.open("GET", "cv19.csv", false); // false for synchronous request
            rawFilevn.send(null);
            var datacsvvn = rawFilevn.responseText;
            var datajsonvn = $.csv.toObjects(datacsvvn);

            var rawFilehn = new XMLHttpRequest();
            rawFilehn.open("GET", "cv19hn.csv", false); // false for synchronous request
            rawFilehn.send(null);
            var datacsvhn = rawFilehn.responseText;
            var datajsonhn = $.csv.toObjects(datacsvhn);

            var rawFilehcm = new XMLHttpRequest();
            rawFilehcm.open("GET", "cv19hcm.csv", false); // false for synchronous request
            rawFilehcm.send(null);
            var datacsvhcm = rawFilehcm.responseText;
            var datajsonhcm = $.csv.toObjects(datacsvhcm);
            // var datajson = Papa.parse(datacsv);
            // console.log(datajson[0]['Số ca nhiễm']);

            // var arrayData = JSON.parse(data);
            // console.log(arrayData['result']['records']);
            $.ajax({
                type: "POST",
                url: "Vietnam_pgsqlAPI.php",
                // data: {functionname: 'updateData', data: arrayData['result']['records']},
                data: {functionname: 'updateData', datavn: datajsonvn, datahn: datajsonhn, datahcm: datajsonhcm},
                success : function (result, status, erro) {
                    console.log(result);
                }
            });
        </script>

    </body>
</html>
