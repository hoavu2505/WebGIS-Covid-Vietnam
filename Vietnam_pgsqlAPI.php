<?php

    if(isset($_POST['functionname']))
    {
        $paPDO = initDB();
        $paSRID = '4326';
        if(isset($P_POST['paPoint'])) $paPoint = $_POST['paPoint'];
        $functionname = $_POST['functionname'];
        
        $aResult = "null";
        if ($functionname == 'getGeoVNToAjax')
            $aResult = getGeoVNToAjax($paPDO, $paSRID, $paPoint);
        else if ($functionname == 'getInfoVNToAjax')
            $aResult = getInfoVNToAjax($paPDO, $paSRID, $paPoint);
        else if ($functionname == 'updateData')
            $aResult = updateData($paPDO,$paSRID,$_POST['data']);
        else if($functionname=='getGeoCovidToAjax')
        // $aResult = getGeoCovidToAjax($paPDO,$paSRID,$paPoint);
            $aResult = getGeoCovidToAjax($paPDO,$paSRID,$_POST['min'],$_POST['max']);
        else if($functionname=='getLayermap')
            $aResult = getLayermap($paPDO,$paSRID);
        
        echo $aResult;

        closeDB($paPDO);
    }

    function initDB()
    {
        // Kết nối CSDL
        $paPDO = new PDO('pgsql:host=localhost;dbname=TestCSDL;port=5432', 'postgres', 'postgres');
        return $paPDO;
    }

    function query($paPDO, $paSQLStr)
    {
        try
        {
            // Khai báo exception
            $paPDO->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Sử đụng Prepare 
            $stmt = $paPDO->prepare($paSQLStr);
            // Thực thi câu truy vấn
            $stmt->execute();
            
            // Khai báo fetch kiểu mảng kết hợp
            $stmt->setFetchMode(PDO::FETCH_ASSOC);
            
            // Lấy danh sách kết quả
            $paResult = $stmt->fetchAll();   
            return $paResult;                 
        }
        catch(PDOException $e) {
            echo "Thất bại, Lỗi: " . $e->getMessage();
            return null;
        }       
    }
    function closeDB($paPDO)
    {
        // Ngắt kết nối
        $paPDO = null;
    }

    function getResult($paPDO,$paSRID,$paPoint)
    {
        //echo $paPoint;
        //echo "<br>";
        $paPoint = str_replace(',', ' ', $paPoint);
        //echo $paPoint;
        //echo "<br>";
        //$mySQLStr = "SELECT ST_AsGeoJson(geom) as geo from \"CMR_adm1\" where ST_Within('SRID=4326;POINT(12 5)'::geometry,geom)";
        $mySQLStr = "SELECT ST_AsGeoJson(geom) as geo from \"gadm36_vnm_1\" where ST_Within('SRID=".$paSRID.";".$paPoint."'::geometry,geom)";
        //echo $mySQLStr;
        //echo "<br><br>";
        $result = query($paPDO, $mySQLStr);
        
        if ($result != null)
        {
            // Lặp kết quả
            foreach ($result as $item){
                return $item['geo'];
            }
        }
        else
            return "null";
    }

    function getGeoVNToAjax($paPDO,$paSRID,$paPoint)
    {
        //echo $paPoint;
        //echo "<br>";
        $paPoint = str_replace(',', ' ', $paPoint);
        //echo $paPoint;
        //echo "<br>";
        //$mySQLStr = "SELECT ST_AsGeoJson(geom) as geo from \"CMR_adm1\" where ST_Within('SRID=4326;POINT(12 5)'::geometry,geom)";
        $mySQLStr = "SELECT ST_AsGeoJson(geom) as geo from \"gadm36_vnm_1\" where ST_Within('SRID=".$paSRID.";".$paPoint."'::geometry,geom)";
        //echo $mySQLStr;
        //echo "<br><br>";
        $result = query($paPDO, $mySQLStr);
        
        if ($result != null)
        {
            // Lặp kết quả
            foreach ($result as $item){
                return $item['geo'];
            }
        }
        else
            return "null";
    }

    function getInfoVNToAjax($paPDO,$paSRID,$paPoint)
    {
        //echo $paPoint;
        //echo "<br>";
        $paPoint = str_replace(',', ' ', $paPoint);
        //echo $paPoint;
        //echo "<br>";
        //$mySQLStr = "SELECT ST_AsGeoJson(geom) as geo from \"CMR_adm1\" where ST_Within('SRID=4326;POINT(12 5)'::geometry,geom)";
        //$mySQLStr = "SELECT ST_AsGeoJson(geom) as geo from \"CMR_adm1\" where ST_Within('SRID=".$paSRID.";".$paPoint."'::geometry,geom)";
        $mySQLStr = "SELECT name_1, canhiem from \"gadm36_vnm_1\" where ST_Within('SRID=".$paSRID.";".$paPoint."'::geometry,geom)";
        //echo $mySQLStr;
        //echo "<br><br>";
        $result = query($paPDO, $mySQLStr);
        
        if ($result != null)
        {
            $resFin = '<table>';
            // Lặp kết quả
            foreach ($result as $item){
                $resFin = $resFin.'<tr><td>Tỉnh: '.$item['name_1'].'</td></tr>';
                $resFin = $resFin.'<tr><td>Ca nhiễm: '.$item['canhiem'].' ca</td></tr>';
                break;
            }
            $resFin = $resFin.'</table>';
            return $resFin;
        }
        else
            return "null";
    }

    function getGeoCovidToAjax($paPDO,$paSRID,$min,$max)
    {
        // chinh o day la 1
        $mySQLStr = "SELECT ST_AsGeoJson(geom)as geo, name_1, canhiem, dangdieutri, binhphuc, tuvong  from \"gadm36_vnm_1\" where canhiem>=$min and canhiem<=$max";

        $result = query($paPDO, $mySQLStr);
        $arr=[];
        foreach ($result as $item){
        array_push($arr,$item);
        // echo substr(str_replace('\\',null,json_encode($item['geo'])),1,-1) ."\n"; 
        }
        
        return str_replace('}"','}',str_replace('"{','{',str_replace('\\',null,json_encode($arr,JSON_UNESCAPED_UNICODE))));
    }   

    function getLayermap($paPDO,$paSRID)
    {
        // chinh o day la 1
        $mySQLStr = "SELECT ST_AsGeoJson(geom)as geo, name_1, canhiem, dangdieutri, binhphuc, tuvong from \"gadm36_vnm_1\" ";

        $result = query($paPDO, $mySQLStr);
        $arr=[];
        foreach ($result as $item){
        array_push($arr,$item);
        // echo substr(str_replace('\\',null,json_encode($item['geo'])),1,-1) ."\n"; 
        }
        
        return str_replace('}"','}',str_replace('"{','{',str_replace('\\',null,json_encode($arr,JSON_UNESCAPED_UNICODE))));
    }   


    function updateData($paPDO,$paSRID,$dataCovid)
    {
	$fields = array('canhiem', 'dangdieutri', 'binhphuc', 'tuvong');
        for ($i = 0; $i < 4; $i++) {
            $mySQLStr1 = "ALTER TABLE \"gadm36_vnm_1\" ADD IF NOT EXISTS ".$fields[$i]." INT";
            $result1 = query($paPDO, $mySQLStr1);
        }
        for ($i = 0; $i < 63; $i++){
            echo $i . ' - ';
            $mySQLStr = "UPDATE \"gadm36_vnm_1\" SET canhiem = ".$dataCovid[$i]['Số ca nhiễm'].", dangdieutri = ".$dataCovid[$i]['Đang điều trị'].", binhphuc = ".$dataCovid[$i]['Bình phục'].", tuvong = ".$dataCovid[$i]['Tử vong']." WHERE hasc_1 = '".$dataCovid[$i]['HASC']."'";
            print ($mySQLStr);
            $result = query($paPDO, $mySQLStr);
        }
    }

?>