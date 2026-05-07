<?php
/**
 * Connexion au SGA : mot de passe + boutons WebAuthn (empreinte / passkey).
 * Redirige vers accueil.php si déjà connecté ou après succès POST.
 * La biométrie nécessite HTTPS (ou localhost) — voir messages sous les boutons.
 */
require_once __DIR__ . "/auth.php";

if (auth_is_logged_in()) {
    header("Location: accueil.php");
    exit;
}

$error = "";
// Connexion classique (identifiants vérifiés dans data/users.json)
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST["username"] ?? "");
    $password = $_POST["password"] ?? "";

    if ($username === "" || $password === "") {
        $error = "Veuillez saisir le nom d'utilisateur et le mot de passe.";
    } elseif (!auth_attempt_password_login($username, $password)) {
        $error = "Identifiants invalides.";
    } else {
        header("Location: accueil.php");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Connexion - SGA UPC</title>
  <style>
    :root {
      color-scheme: light;
      --bg: #f3f6fb;
      --card: #ffffff;
      --primary: #0f4fa8;
      --primary-dark: #0a3a7a;
      --danger: #c42a2a;
      --muted: #64748b;
    }
    * { box-sizing: border-box; }
    body {
      margin: 0;
      font-family: Arial, sans-serif;
      background: var(--bg);
      display: grid;
      min-height: 100dvh;
      place-items: center;
      padding: 16px;
    }
    .card {
      width: 100%;
      max-width: 420px;
      background: var(--card);
      border-radius: 14px;
      padding: 24px;
      box-shadow: 0 10px 30px rgba(2, 6, 23, 0.12);
    }
    h1 { margin: 0 0 8px; font-size: 1.4rem; color: #0f172a; }
    p.meta { margin: 0 0 20px; color: var(--muted); font-size: 0.95rem; }
    label { display: block; margin-bottom: 6px; font-weight: 700; }
    input {
      width: 100%;
      padding: 12px;
      margin-bottom: 14px;
      border-radius: 10px;
      border: 1px solid #cbd5e1;
      font-size: 1rem;
    }
    .btn {
      width: 100%;
      border: none;
      border-radius: 10px;
      padding: 12px;
      font-size: 1rem;
      cursor: pointer;
      font-weight: 700;
      margin-bottom: 10px;
    }
    .btn-primary { background: var(--primary); color: #fff; }
    .btn-primary:hover { background: var(--primary-dark); }
    .btn-secondary { background: #dbeafe; color: #0b3a86; }
    .btn-outline { background: #fff; border: 1px solid #94a3b8; color: #0f172a; }
    .error {
      background: #fee2e2;
      color: #7f1d1d;
      padding: 10px 12px;
      border-radius: 10px;
      margin-bottom: 12px;
      font-size: 0.92rem;
    }
    .hint {
      font-size: 0.86rem;
      color: var(--muted);
      margin-top: 8px;
      line-height: 1.45;
    }
    .hint-warn {
      font-size: 0.88rem;
      background: #fff7ed;
      border: 1px solid #fdba74;
      color: #9a3412;
      padding: 12px;
      border-radius: 10px;
      margin-top: 12px;
      line-height: 1.45;
    }
    .hint-warn strong { display: block; margin-bottom: 6px; }
    .status {
      min-height: 22px;
      color: #0f172a;
      font-size: 0.9rem;
      margin-bottom: 8px;
    }
    @media (max-width: 420px) {
      .card { padding: 18px; }
    }
  </style>
</head>
<body>
  <main class="card">
    <h1>Connexion SGA</h1>
    <p class="meta">Gestion des auditoires et horaires - UPC</p>

    <?php if ($error !== ""): ?>
      <div class="error"><?php echo htmlspecialchars($error, ENT_QUOTES, "UTF-8"); ?></div>
    <?php endif; ?>

    <form method="post" action="login.php">
      <label for="username">Nom d'utilisateur</label>
      <input type="text" id="username" name="username" required>

      <label for="password">Mot de passe</label>
      <input type="password" id="password" name="password" required>

      <button type="submit" class="btn btn-primary">Se connecter</button>
    </form>

    <div id="bioSecureWarn" class="hint-warn" style="display:none;"></div>
    <div class="status" id="bioStatus"></div>
    <button type="button" class="btn btn-secondary" id="bioLoginBtn">Connexion par empreinte</button>
    <button type="button" class="btn btn-outline" id="bioRegisterBtn">Enregistrer mon empreinte</button>

    <p class="hint">
      Compte initial : <strong>admin</strong> / <strong>admin123</strong>.<br><br>
      <strong>Empreinte</strong> : elle doit être liée à un compte qui existe déjà (même nom que dans data/users.json).<br><br>
      <strong>Empreinte (WebAuthn)</strong> : ce sont les navigateurs qui exigent un contexte sécurisé (
      <strong>HTTPS</strong> ou <strong>http://localhost</strong> sur la machine qui héberge le site).
      En <strong>http://192.168…</strong> depuis le téléphone, l’empreinte ne peut pas fonctionner tant que vous n’avez pas du HTTPS (SSL Laragon, certificat local de confiance, ou tunnel ngrok).
    </p>
  </main>

  <script>
    /* WebAuthn côté navigateur : appels AJAX vers webauthn.php */
    function bioSupportedEnvironment() {
      if (typeof window.isSecureContext !== "undefined" && !window.isSecureContext) {
        return {
          ok: false,
          reason: "insecure",
          message:
            "Connexion non sécurisée (HTTP ou adresse locale type 192.168.x.x). WebAuthn est désactivé par le navigateur. Utilisez HTTPS sur cette URL ou testez avec http://localhost sur le PC."
        };
      }
      if (!window.PublicKeyCredential || typeof navigator.credentials === "undefined") {
        return {
          ok: false,
          reason: "no-api",
          message: "Ce navigateur ne propose pas WebAuthn (API indisponible)."
        };
      }
      return { ok: true };
    }

    (function initBioUi() {
      const check = bioSupportedEnvironment();
      const warn = document.getElementById("bioSecureWarn");
      if (!check.ok && check.reason === "insecure") {
        warn.style.display = "block";
        warn.innerHTML =
          "<strong>Règle du navigateur (pas un blocage du site)</strong>" +
          "Sur cette URL en HTTP (ex. 192.168.x.x), Chrome désactive WebAuthn : l’empreinte ne peut pas tourner tant que vous n’utilisez pas <strong>HTTPS</strong> ou une astuce équivalente. " +
          "Les boutons ci‑dessous restent utilisables pour réessayer après avoir activé SSL (Laragon) ou une URL HTTPS (ex. ngrok).";
      }
      if (!check.ok && check.reason === "no-api") {
        warn.style.display = "block";
        warn.innerHTML =
          "<strong>WebAuthn indisponible</strong>" +
          check.message;
      }
    })();

    function base64urlToArrayBuffer(base64url) {
      const base64 = base64url.replace(/-/g, "+").replace(/_/g, "/");
      const pad = "=".repeat((4 - (base64.length % 4)) % 4);
      const binary = atob(base64 + pad);
      const bytes = new Uint8Array(binary.length);
      for (let i = 0; i < binary.length; i += 1) bytes[i] = binary.charCodeAt(i);
      return bytes.buffer;
    }

    function arrayBufferToBase64url(buffer) {
      const bytes = new Uint8Array(buffer);
      let binary = "";
      bytes.forEach((b) => { binary += String.fromCharCode(b); });
      return btoa(binary).replace(/\+/g, "-").replace(/\//g, "_").replace(/=+$/g, "");
    }

    async function api(action, data) {
      const response = await fetch("webauthn.php?action=" + encodeURIComponent(action), {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(data || {})
      });
      return response.json();
    }

    function status(text, isError) {
      const zone = document.getElementById("bioStatus");
      zone.textContent = text || "";
      zone.style.color = isError ? "#b91c1c" : "#0f172a";
    }

    function getUsername() {
      return document.getElementById("username").value.trim();
    }

    document.getElementById("bioRegisterBtn").addEventListener("click", async () => {
      try {
        const env = bioSupportedEnvironment();
        if (!env.ok) {
          status(env.message, true);
          return;
        }
        const username = getUsername();
        if (!username) {
          status("Saisissez d'abord le nom d'utilisateur.", true);
          return;
        }

        status("Préparation de l'enregistrement biométrique...");
        const start = await api("begin_register", { username });
        if (!start.ok) {
          status(start.message || "Impossible de démarrer l'enregistrement.", true);
          return;
        }

        const publicKey = start.publicKey;
        publicKey.challenge = base64urlToArrayBuffer(publicKey.challenge);
        publicKey.user.id = base64urlToArrayBuffer(publicKey.user.id);

        const cred = await navigator.credentials.create({ publicKey });
        const credentialId = arrayBufferToBase64url(cred.rawId);
        const end = await api("finish_register", { credentialId });
        if (!end.ok) {
          status(end.message || "Echec d'enregistrement.", true);
          return;
        }
        status("Empreinte enregistrée. Vous pouvez maintenant vous connecter par biométrie.");
      } catch (e) {
        status("Enregistrement annulé ou échoué.", true);
      }
    });

    document.getElementById("bioLoginBtn").addEventListener("click", async () => {
      try {
        const env = bioSupportedEnvironment();
        if (!env.ok) {
          status(env.message, true);
          return;
        }
        const username = getUsername();
        if (!username) {
          status("Saisissez d'abord le nom d'utilisateur.", true);
          return;
        }

        status("Vérification de l'empreinte en cours...");
        const start = await api("begin_auth", { username });
        if (!start.ok) {
          status(start.message || "Aucune empreinte disponible pour cet utilisateur.", true);
          return;
        }

        const publicKey = start.publicKey;
        publicKey.challenge = base64urlToArrayBuffer(publicKey.challenge);
        if (publicKey.allowCredentials && publicKey.allowCredentials.length > 0) {
          publicKey.allowCredentials = publicKey.allowCredentials.map((c) => ({
            ...c,
            id: base64urlToArrayBuffer(c.id)
          }));
        }

        const assertion = await navigator.credentials.get({ publicKey });
        const credentialId = arrayBufferToBase64url(assertion.rawId);
        const end = await api("finish_auth", { credentialId });
        if (!end.ok) {
          status(end.message || "Connexion biométrique refusée.", true);
          return;
        }
        window.location.href = "accueil.php";
      } catch (e) {
        status("Vérification biométrique annulée ou échouée.", true);
      }
    });
  </script>
</body>
</html>
