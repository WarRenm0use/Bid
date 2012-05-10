<?php
//echo date("U");
$cmdline = "java -jar LoKieroBid.jar 46 ABCDE dev  > /dev/null 2>&1 & echo $!";
exec($cmdline, $result, $retint);
echo "<pre>";
print_r($result);
echo "<pre>";
echo "fin";
?>
