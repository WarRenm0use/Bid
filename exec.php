<?php

$cmdline = "/home/dev/www/cgi-bin/tbk_check_mac.cgi lala.txt";
exec($cmdline, $result, $retint);
echo "<pre>";
print_r($result);
echo "<pre>";

?>
