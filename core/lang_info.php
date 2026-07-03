<?php
// Read English website JSON
$en = json_decode(file_get_contents('G:\Tudo\MEU-SISTEMA\LOJA VIRTUAL\omnimart\core\resources\lang\1744539358qWpD7nW6.json'), true);
echo "Website keys: " . count($en) . "\n";

// Read English dashboard JSON
$enDash = json_decode(file_get_contents('G:\Tudo\MEU-SISTEMA\LOJA VIRTUAL\omnimart\core\resources\lang\1647794074eEeCbfDD.json'), true);
echo "Dashboard keys: " . count($enDash) . "\n";

// Print first 20 keys as sample
$i = 0;
foreach ($en as $key => $val) {
    if ($i++ < 20) echo "$key => $val\n";
}
