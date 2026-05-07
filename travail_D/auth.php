<?php
/**
 * Authentification par session et fichiers JSON locaux (sans base de données).
 *
 * - Utilisateurs : data/users.json (mot de passe hashé avec password_hash).
 * - Empreintes WebAuthn : data/webauthn_credentials.json (géré depuis webauthn.php).
 * - Compte admin par défaut créé si absent (voir auth_ensure_default_user).
 */
session_start();

define("AUTH_USERS_FILE", __DIR__ . "/data/users.json");
define("AUTH_WEBAUTHN_FILE", __DIR__ . "/data/webauthn_credentials.json");

function auth_load_json_file($path) {
    if (!file_exists($path)) {
        return [];
    }

    $raw = file_get_contents($path);
    if ($raw === false || trim($raw) === "") {
        return [];
    }

    $decoded = json_decode($raw, true);
    return is_array($decoded) ? $decoded : [];
}

function auth_save_json_file($path, $data) {
    file_put_contents($path, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

function auth_ensure_default_user() {
    $users = auth_load_json_file(AUTH_USERS_FILE);
    if (!isset($users["admin"])) {
        $users["admin"] = [
            "username" => "admin",
            "password_hash" => password_hash("admin123", PASSWORD_DEFAULT),
            "role" => "apparitorat"
        ];
        auth_save_json_file(AUTH_USERS_FILE, $users);
    }
}

function auth_is_logged_in() {
    return isset($_SESSION["auth_user"]) && is_array($_SESSION["auth_user"]);
}

function auth_require_login() {
    if (!auth_is_logged_in()) {
        header("Location: login.php");
        exit;
    }
}

function auth_normalize_username($username) {
    return strtolower(trim((string)$username));
}

function auth_resolve_user_key($users, $username) {
    $username = trim((string)$username);
    if ($username === "") {
        return null;
    }
    if (isset($users[$username])) {
        return $username;
    }
    $target = auth_normalize_username($username);
    foreach ($users as $key => $user) {
        if (!is_array($user)) {
            continue;
        }
        if (auth_normalize_username((string)$key) === $target) {
            return $key;
        }
        if (isset($user["username"]) && auth_normalize_username((string)$user["username"]) === $target) {
            return $key;
        }
    }
    return null;
}

function auth_user_exists($username) {
    $users = auth_load_json_file(AUTH_USERS_FILE);
    return auth_resolve_user_key($users, $username) !== null;
}

function auth_attempt_password_login($username, $password) {
    $users = auth_load_json_file(AUTH_USERS_FILE);
    $key = auth_resolve_user_key($users, $username);
    if ($key === null) {
        return false;
    }
    $user = $users[$key];
    if (!isset($user["password_hash"])) {
        return false;
    }
    if (!password_verify($password, $user["password_hash"])) {
        return false;
    }

    $_SESSION["auth_user"] = [
        "username" => $user["username"],
        "role" => $user["role"]
    ];

    return true;
}

function auth_login_user($username) {
    $users = auth_load_json_file(AUTH_USERS_FILE);
    $key = auth_resolve_user_key($users, $username);
    if ($key === null) {
        return false;
    }
    $user = $users[$key];
    $_SESSION["auth_user"] = [
        "username" => $user["username"] ?? $key,
        "role" => $user["role"] ?? "apparitorat"
    ];
    return true;
}

function auth_logout() {
    $_SESSION = [];
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), "", time() - 42000, $params["path"], $params["domain"], $params["secure"], $params["httponly"]);
    }
    session_destroy();
}

auth_ensure_default_user();
