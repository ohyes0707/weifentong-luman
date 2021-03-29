<?php
ini_set('max_execution_time', '0');
$filename='http://localhost/wft/public/index.php/user/getDbUserInfo/v1.0';
$hhd=file_get_contents($filename);
sleep(3);
echo '<script type="text/javascript">
    location.href="";
</script>';