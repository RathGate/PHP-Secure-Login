<?php

spl_autoload_register( 'autoloader' );
function autoloader($class) {
    // Remplace les \ du namespace pour convertir en chemin relatif
    $class_path = str_replace('\\', '/', $class);

    // Ajoute l'extension pour charger un potentiel fichier php.
    $file =  __DIR__ . '/' . $class_path . '.php';

    // Si le fichier existe, le require:
    if (file_exists($file)) {
        require_once $file;
    }
}