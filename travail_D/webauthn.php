<?php
/**
 * API JSON pour l’empreinte digitale / WebAuthn (appelée en fetch depuis login.php).
 *
 * Actions (GET action=…) : begin_register, finish_register, begin_auth, finish_auth.
 * L’empreinte ne peut être enregistrée que pour un utilisateur déjà présent dans users.json.
 */
require_once __DIR__ . "/auth.php";

header("Content-Type: application/json; charset=utf-8");

function b64url_encode($data) {
    return rtrim(strtr(base64_encode($data), "+/", "-_"), "=");
}

function random_challenge() {
    return b64url_encode(random_bytes(32));
}

function json_response($data, $code = 200) {
    http_response_code($code);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

$action = $_GET["action"] ?? "";
$payload = json_decode(file_get_contents("php://input"), true);
$payload = is_array($payload) ? $payload : [];

function webauthn_load_credentials() {
    $raw = auth_load_json_file(AUTH_WEBAUTHN_FILE);
    $norm = [];
    foreach ($raw as $k => $v) {
        if (is_array($v)) {
            $norm[auth_normalize_username((string)$k)] = $v;
        }
    }
    return $norm;
}

$credentials = webauthn_load_credentials();

if ($action === "begin_register") {
    $username = auth_normalize_username($payload["username"] ?? "");
    if ($username === "") {
        json_response(["ok" => false, "message" => "Utilisateur requis"], 400);
    }

    $challenge = random_challenge();
    $_SESSION["webauthn_reg_challenge"] = $challenge;
    $_SESSION["webauthn_reg_username"] = $username;

    $userId = b64url_encode(hash("sha256", $username, true));
    json_response([
        "ok" => true,
        "publicKey" => [
            "challenge" => $challenge,
            "rp" => ["name" => "SGA UPC", "id" => $_SERVER["HTTP_HOST"] ?? "localhost"],
            "user" => [
                "id" => $userId,
                "name" => $payload["username"] ?? $username,
                "displayName" => $payload["username"] ?? $username
            ],
            "pubKeyCredParams" => [
                ["type" => "public-key", "alg" => -7],
                ["type" => "public-key", "alg" => -257]
            ],
            "timeout" => 60000,
            "authenticatorSelection" => [
                "userVerification" => "required",
                "residentKey" => "preferred"
            ],
            "attestation" => "none"
        ]
    ]);
}

if ($action === "finish_register") {
    $username = $_SESSION["webauthn_reg_username"] ?? "";
    $challenge = $_SESSION["webauthn_reg_challenge"] ?? "";
    $credentialId = $payload["credentialId"] ?? "";

    if ($username === "" || $challenge === "" || $credentialId === "") {
        json_response(["ok" => false, "message" => "Session invalide"], 400);
    }

    if (!auth_user_exists($username)) {
        json_response([
            "ok" => false,
            "message" => "Ce compte n'existe pas. Enregistrez l'empreinte seulement pour un utilisateur déjà présent dans le système (ex. admin), ou créez l'utilisateur dans data/users.json."
        ], 400);
    }

    $credentials[$username] = [
        "credentialId" => $credentialId,
        "registeredAt" => date("c")
    ];
    auth_save_json_file(AUTH_WEBAUTHN_FILE, $credentials);

    unset($_SESSION["webauthn_reg_username"], $_SESSION["webauthn_reg_challenge"]);
    json_response(["ok" => true, "message" => "Empreinte enregistrée"]);
}

if ($action === "begin_auth") {
    $username = auth_normalize_username($payload["username"] ?? "");
    if ($username === "" || !isset($credentials[$username])) {
        json_response(["ok" => false, "message" => "Aucune empreinte enregistrée pour cet utilisateur"], 400);
    }

    $challenge = random_challenge();
    $_SESSION["webauthn_auth_challenge"] = $challenge;
    $_SESSION["webauthn_auth_username"] = $username;

    json_response([
        "ok" => true,
        "publicKey" => [
            "challenge" => $challenge,
            "rpId" => $_SERVER["HTTP_HOST"] ?? "localhost",
            "allowCredentials" => [[
                "type" => "public-key",
                "id" => $credentials[$username]["credentialId"]
            ]],
            "userVerification" => "required",
            "timeout" => 60000
        ]
    ]);
}

if ($action === "finish_auth") {
    $username = $_SESSION["webauthn_auth_username"] ?? "";
    $challenge = $_SESSION["webauthn_auth_challenge"] ?? "";
    $credentialId = $payload["credentialId"] ?? "";

    if ($username === "" || $challenge === "" || $credentialId === "") {
        json_response(["ok" => false, "message" => "Session invalide"], 400);
    }

    if (!isset($credentials[$username]) || $credentials[$username]["credentialId"] !== $credentialId) {
        json_response(["ok" => false, "message" => "Empreinte non reconnue"], 401);
    }

    if (!auth_login_user($username)) {
        json_response(["ok" => false, "message" => "Utilisateur inconnu"], 401);
    }

    unset($_SESSION["webauthn_auth_username"], $_SESSION["webauthn_auth_challenge"]);
    json_response(["ok" => true, "message" => "Connexion biométrique réussie"]);
}

json_response(["ok" => false, "message" => "Action invalide"], 400);
