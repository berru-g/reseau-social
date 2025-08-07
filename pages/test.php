<?php
echo '<h2>Diagnostic des chemins</h2>';
echo '__DIR__ : ' . __DIR__ . '<br>';
echo 'Chemin config.php : ' . __DIR__ . '/../includes/config.php' . '<br>';
echo 'Existe ? ' . (file_exists(__DIR__ . '/../includes/config.php') ? 'OUI' : 'NON');
?>