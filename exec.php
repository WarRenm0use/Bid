<?php

$cmdline = "java -jar LoKieroBid.jar 46 ABCDE dev &";
exec($cmdline, $result, $retint);
echo "<pre>";
print_r($result);
echo "<pre>";

?>
