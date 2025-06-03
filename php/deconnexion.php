<?php
session_start();

// Suppression des données de session
session_unset();
session_destroy();

// Forcer la suppression du cookie PHPSESSID si présent
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

header("Location: ../connexion.php");
?>