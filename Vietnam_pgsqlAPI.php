<?php

    if(isset($_POST['functionname']))
    {
        $paPDO = initDB();
        $paSRID = '4326';
        if(isset($_POST['paPoint'])) $paPoint = $_POST['paPoint'];
        $functionname = $_POST['functionname'];
        if(isset($_POST['pos'])) $pos=$_POST['pos'];
        $aResult = "null";
        if ($functionname == 'getGeoVNToAjax')
            $aResult = getGeoVNToAjax($paPDO, $paSRID, $paPoint);
        else if ($functionname == 'getInfoVNToAjax')
            $aResult = getInfoVNToAjax($paPDO, $paSRID, $paPoint);
        else if ($functionname == 'updateData')
            $aResult = updateData($paPDO,$paSRID,$_POST['datavn'], $_POST['datahn'], $_POST['datahcm']);
        else if($functionname=='getGeoCovidToAjax')
        // $aResult = getGeoCovidToAjax($paPDO,$paSRID,$paPoint);
            $aResult = getGeoCovidToAjax($paPDO,$paSRID,$pos,$_POST['min'],$_POST['max']);
        
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

    function getGeoCovidToAjax($paPDO,$paSRID,$pos,$min,$max)
    {
        // chinh o day la 1
        $option="";
        $table="";
        if($pos=="VN") {
            $table="gadm36_vnm_1";
            $option = ", name_1, canhiem, dangdieutri, binhphuc, tuvong";
        }
        if($pos=="HN") {
            $table="covid_hanoi";
            $option = ", name_2, canhiem";
        }
        if($pos=="HCM") {
            $table="covid_hcm";
            $option = ", name_2, canhiem";
        }

        if($min<0) $add1=""; 
        else 
            $add1=" where canhiem>=$min";

        if($max<0) $add2="";
        else 
            $add2="  and canhiem<=$max";
            
        $mySQLStr = "SELECT ST_AsGeoJson(geom)as geo".$option." from \"".$table."\"".$add1.$add2;
        // return $mySQLStr;
        $result = query($paPDO, $mySQLStr);
        $arr=[];
        foreach ($result as $item){
            array_push($arr,$item);
        }
        return json_encode($arr,JSON_UNESCAPED_UNICODE);
    }   

    function updateData($paPDO,$paSRID,$dataCovid,$dataCovidHN, $dataCovidHCM)
    {
        $fields = array('canhiem', 'binhphuc', 'tuvong');
        for ($i = 0; $i < 3; $i++) {
            $mySQLStr1 = "ALTER TABLE \"gadm36_vnm_1\" ADD IF NOT EXISTS ".$fields[$i]." INT";
            $result1 = query($paPDO, $mySQLStr1);
        }
        $mySQLStr1 = "ALTER TABLE \"covid_hanoi\" ADD IF NOT EXISTS canhiem INT";
        $result1 = query($paPDO, $mySQLStr1);
        $mySQLStr3 = "ALTER TABLE \"covid_hcm\" ADD IF NOT EXISTS canhiem INT";
        $result3 = query($paPDO, $mySQLStr3);
        for ($i = 0; $i < 63; $i++){
            echo $i . ' - ';
            $mySQLStr2 = "UPDATE \"gadm36_vnm_1\" SET canhiem = ".$dataCovid[$i]['Số ca nhiễm'].", binhphuc = ".$dataCovid[$i]['Bình phục'].", tuvong = ".$dataCovid[$i]['Tử vong']." WHERE hasc_1 = '".$dataCovid[$i]['HASC']."'";
            print ($mySQLStr2);
            $result = query($paPDO, $mySQLStr2);
        }
        for ($i = 0; $i < 24; $i++){
            echo $i . ' - ';
            $mySQLStr2 = "UPDATE \"covid_hanoi\" SET canhiem = ".$dataCovidHN[$i]['Số ca nhiễm']." WHERE name_2 = '".$dataCovidHN[$i]['Quận']."'";
            print ($mySQLStr2);
            $result = query($paPDO, $mySQLStr2);
        }
        for ($i = 0; $i < 24; $i++){
            echo $i . ' - ';
            $mySQLStr2 = "UPDATE \"covid_hcm\" SET canhiem = ".$dataCovidHCM[$i]['Số ca nhiễm']." WHERE name_2 = '".$dataCovidHCM[$i]['Quận']."'";
            print ($mySQLStr2);
            $result = query($paPDO, $mySQLStr2);
        }
    }

?>