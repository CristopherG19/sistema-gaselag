<?php
function validarNombre($nombre) {
    return !empty(trim($nombre)) && preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/', $nombre);
}

function validarApellidos($apellidos) {
    return !empty(trim($apellidos)) && preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/', $apellidos);
}

function validarCorreo($correo) {
    return filter_var($correo, FILTER_VALIDATE_EMAIL);
}

function validarPassword($password) {
    return strlen($password) >= 10;
}

function sanitizarInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}
?>