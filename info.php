<?php
echo "<pre>";
print_r($_SERVER);
echo "</pre>";

if($_SERVER["REMOTE_ADDR"] != "50.56.80.62"){
    echo "visitante";
} else echo "servidor";
?>
