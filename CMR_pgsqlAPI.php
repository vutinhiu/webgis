<?php
$paPDO = initDB();
$paSRID = '4326';
if(isset($_POST['functionname']))
{
    $paPoint = $_POST['paPoint'];
    $functionname = $_POST['functionname'];
        
    $aResult = "null";
    if ($functionname == 'getGeoCMRToAjax')
        $aResult = getGeoCMRToAjax($paPDO, $paSRID, $paPoint);
    else if ($functionname == 'getInfoCMRToAjax')
        $aResult = getInfoCMRToAjax($paPDO, $paSRID, $paPoint);
    else if ($functionname == 'getGeoCMR2ToAjax')
        $aResult = getGeoCMR2ToAjax($paPDO, $paSRID, $paPoint);
    else if ($functionname == 'getInfoCMR2ToAjax')
        $aResult = getInfoCMR2ToAjax($paPDO, $paSRID, $paPoint);
    else if ($functionname == 'getInfoData2ToAjax')
        $aResult = getInfoData2ToAjax($paPDO, $paSRID, $paPoint);
    else if ($functionname == 'getData2ToAjax')
        $aResult = getData2ToAjax($paPDO, $paSRID, $paPoint);
    else if ($functionname == 'getInfoData3ToAjax')
        $aResult = getInfoData3ToAjax($paPDO, $paSRID, $paPoint);
    else if ($functionname == 'getData3ToAjax')
        $aResult = getData3ToAjax($paPDO, $paSRID, $paPoint);
        
    echo $aResult;
    
    closeDB($paPDO);
}

if (isset($_POST['name'])) {
    $name = $_POST['name'];
    $aResult = searchCity($paPDO, $paSRID, $name);
    echo $aResult;
}
    
function initDB()
    {
        // Kết nối CSDL
        $paPDO = new PDO('pgsql:host=localhost;dbname=BTL;port=5432','postgres','123');
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
function searchCity($paPDO, $paSRID, $name)
    {

        $mySQLStr = "SELECT ST_AsGeoJson(geom) as geo from \"gadm36_vnm_1\" where name_1 like '%$name%'";
        $result = query($paPDO, $mySQLStr);

        if ($result != null) {
            // Lặp kết quả
            foreach ($result as $item) {
                return $item['geo'];
            }
        } else
            return "null";
    }
function getGeoCMRToAjax($paPDO,$paSRID,$paPoint)
    {
        $paPoint = str_replace(',', ' ', $paPoint);
        $mySQLStr = "SELECT ST_AsGeoJson(geom) as geo from \"gadm36_vnm_1\" where ST_Within('SRID=".$paSRID.";".$paPoint."'::geometry,geom)"; 
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
function getInfoCMRToAjax($paPDO,$paSRID,$paPoint)
    {
        $paPoint = str_replace(',', ' ', $paPoint);
        $mySQLStr = "SELECT type_1, name_1, infected, active, recovered, death from \"gadm36_vnm_1\" where ST_Within('SRID=".$paSRID.";".$paPoint."'::geometry,geom)";
        $result = query($paPDO, $mySQLStr);
        
        if ($result != null)
        {
            $resFin = '<table>';
            // Lặp kết quả
            foreach ($result as $item){
                $resFin = $resFin.'<tr><td>'.$item['type_1'].': '.$item['name_1'].'</td></tr>';
                $resFin = $resFin.'<tr><td>Số ca nhiễm: '.$item['infected'].'</td></tr>';
                $resFin = $resFin.'<tr><td>Đang điều trị: '.$item['active'].'</td></tr>';
                $resFin = $resFin.'<tr><td>Hồi phục: '.$item['recovered'].'</td></tr>';
                $resFin = $resFin.'<tr><td>Tử vong: '.$item['death'].'</td></tr>';
                break;
            }
            $resFin = $resFin.'</table>';
            return $resFin;
        }
        else
            return "null";
    }
function getGeoCMR2ToAjax($paPDO,$paSRID,$paPoint)
    {
        $paPoint = str_replace(',', ' ', $paPoint);
        $mySQLStr = "SELECT ST_AsGeoJson(geom) as geo from \"gadm36_vnm_2\" where ST_Within('SRID=".$paSRID.";".$paPoint."'::geometry,geom)";
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
function getInfoCMR2ToAjax($paPDO,$paSRID,$paPoint)
    {
        $paPoint = str_replace(',', ' ', $paPoint);
        $mySQLStr = "SELECT type_2, name_2, infected from \"gadm36_vnm_2\" where ST_Within('SRID=".$paSRID.";".$paPoint."'::geometry,geom)";
        $result = query($paPDO, $mySQLStr);
        
        if ($result != null)
        {
            $resFin = '<table>';
            // Lặp kết quả
            foreach ($result as $item){
                $resFin = $resFin.'<tr><td>'.$item['type_2'].': '.$item['name_2'].'</td></tr>';
                $resFin = $resFin.'<tr><td>Số ca nhiễm: '.$item['infected'].'</td></tr>';
                break;
            }
            $resFin = $resFin.'</table>';
            return $resFin;
        }
        else
            return "null";
    }
function getData2ToAjax($paPDO,$paSRID,$paPoint)
    {

        $paPoint = str_replace(',', ' ', $paPoint);
        $mySQLStr = "SELECT ST_AsGeoJson(geom) as geo from \"data2\" where ST_Within('SRID=".$paSRID.";".$paPoint."'::geometry,geom)";
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
function getInfoData2ToAjax($paPDO,$paSRID,$paPoint)
    {
        
        $paPoint = str_replace(',', ' ', $paPoint);
        $mySQLStr = "SELECT gid, sta_name, location, type, trajectory, contigious, province  from  \"data2\" 
        where ST_Distance('" . $paPoint . "',ST_AsText(geom)) = (SELECT min(ST_Distance('" . $paPoint . "',ST_AsText(geom))) from \"data2\") and ST_Distance('" . $paPoint . "',ST_AsText(geom)) < 0.05";
    
        $result = query($paPDO, $mySQLStr);
        
        if ($result != null)
        {
            $resFin = '<table>';
            // Lặp kết quả
            foreach ($result as $item){
                $resFin = $resFin.'<tr><td>Gid: '.$item['gid'].'</td></tr>';
                $resFin = $resFin.'<tr><td>Tên trạm: '.$item['sta_name'].'</td></tr>';
                $resFin = $resFin.'<tr><td>Vị trí: '.$item['location'].'</td></tr>';
                $resFin = $resFin.'<tr><td>Kiểu trạm: '.$item['type'].'</td></tr>';
                $resFin = $resFin.'<tr><td>Tuyến đường: '.$item['trajectory'].'</td></tr>';
                $resFin = $resFin.'<tr><td>Vùng tiếp giáp: '.$item['contigious'].'</td></tr>';
                $resFin = $resFin.'<tr><td>Tỉnh/Thành phố: '.$item['province'].'</td></tr>';
                break;
            }
            $resFin = $resFin.'</table>';
            return $resFin;
        }
        else
            return "null";
    }
function getData3ToAjax($paPDO,$paSRID,$paPoint)
    {

        $paPoint = str_replace(',', ' ', $paPoint);
        $mySQLStr = "SELECT ST_AsGeoJson(geom) as geo from \"data3\" where ST_Within('SRID=".$paSRID.";".$paPoint."'::geometry,geom)";
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
function getInfoData3ToAjax($paPDO,$paSRID,$paPoint)
    {
        
        $paPoint = str_replace(',', ' ', $paPoint);
        $mySQLStr = "SELECT gid, name_vi, type_vi, loc_vn, time, pro_vi  from  \"data3\" 
        where ST_Distance('" . $paPoint . "',ST_AsText(geom)) = (SELECT min(ST_Distance('" . $paPoint . "',ST_AsText(geom))) from \"data3\") 
        and ST_Distance('" . $paPoint . "',ST_AsText(geom)) < 0.05";
    
        $result = query($paPDO, $mySQLStr);
        
        if ($result != null)
        {
            $resFin = '<table>';
            // Lặp kết quả
            foreach ($result as $item){
                $resFin = $resFin.'<tr><td>Gid: '.$item['gid'].'</td></tr>';
                $resFin = $resFin.'<tr><td>Tên điểm: '.$item['name_vi'].'</td></tr>';
                $resFin = $resFin.'<tr><td>Loại hỗ trợ: '.$item['type_vi'].'</td></tr>';
                $resFin = $resFin.'<tr><td>Vị trí: '.$item['loc_vn'].'</td></tr>';
                $resFin = $resFin.'<tr><td>Thời gian: '.$item['time'].'</td></tr>';
                $resFin = $resFin.'<tr><td>Tỉnh/Thành phố: '.$item['pro_vi'].'</td></tr>';
                break;
            }
            $resFin = $resFin.'</table>';
            return $resFin;
        }
        else
            return "null";
    }

?>