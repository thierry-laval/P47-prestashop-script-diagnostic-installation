<?php

/**
 * Script de diagnostic Prestashop (Ultimate Final)
 * 
 * Ce script permet de réaliser un diagnostic complet d'une installation Prestashop.
 * Il facilite l'analyse du serveur, de la base de données, des modules, thèmes et du core Prestashop.
 * 
 * Étapes principales :
 * 1. Vérification de la version PHP et des extensions nécessaires
 * 2. Vérification de la connexion à la base de données
 * 3. Vérification des modules activés et de leur présence physique
 * 4. Vérification des dossiers critiques et des permissions
 * 5. Vérification des fichiers essentiels
 * 6. Vérification des thèmes installés et de leur configuration
 * 7. Vérification du core Prestashop (dossier /vendor)
 * 8. Vider le cache si nécessaire
 * 9. Génération d’un rapport HTML téléchargeable
 * 
 * Fonctions principales :
 * - check($condition, $message_ok, $message_fail, $class_ok, $class_fail)
 *      Vérifie une condition et affiche un message OK ou FAIL avec un style spécifique.
 * - rmdir_recursive($dir)
 *      Supprime récursivement le contenu d’un dossier (utile pour vider le cache).
 * 
 * Auteur : Thierry Laval
 * Version : 1.0.0
 * Date : 25/10/2025
 * Lien : https://thierrylaval.dev
 * Licence : Libre pour usage personnel et professionnel
 * 
 * Note :
 * - Ce script ne modifie aucune configuration de Prestashop sans consentement explicite.
 * - Compatible PHP >= 8.2 et Prestashop >= 1.7 (à adapter selon les besoins)
 */
ob_start(); // commence la capture de sortie
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Diagnostic complet Prestashop : vérification modules, thèmes, PHP, cache, Core, base de données.">
    <meta name="theme-color" content="#3366cc">

    <?php
    // Canonical dynamique
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https" : "http";
    $host = $_SERVER['HTTP_HOST'];
    $uri = $_SERVER['REQUEST_URI'];
    $canonical = $protocol . "://" . $host . $uri;
    echo "<link rel='canonical' href='$canonical'>\n";
    ?>

    <title>Diagnostic Prestashop <?php echo $psVersion; ?> - thierrylaval.dev</title>

    <style>
        /* Reset et styles globaux */
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 14px;
            line-height: 1.6;
            background: #41A1E8;
            color: #333;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            justify-content: center;
            /* Centrage vertical */
            align-items: center;
            padding: 20px;
        }

        header,
        main,
        footer {
            width: 50%;
            /* Plus responsive sur mobile */
            max-width: 960px;
            background: #fff;
            padding: 30px;
            margin-bottom: 20px;
            border-radius: 12px;
            /* Coins plus arrondis */
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.1);
            /* Ombre plus prononcée */
            text-align: left;
        }

        header h1 {
            font-size: 32px;
            /* Plus grand */
            font-weight: 700;
            /* Plus gras */
            text-align: center;
            margin-bottom: 15px;
            color: #2C3E50;
            /* Couleur plus foncée */
        }

        main h2,
        main h3 {
            color: #2C3E50;
            margin-top: 10px;
            margin-bottom: 10px;
            font-weight: 600;
        }

        /* États colorés */
        span.ok {
            color: #2ecc71;
        }

        /* Vert plus vif */
        span.fail {
            color: #e74c3c;
        }

        /* Rouge plus vif */
        span.info {
            color: #3498db;
        }

        /* Bleu plus vif */
        span.warn {
            color: #f39c12;
        }

        /* Orange plus vif */

        pre {
            background: #f8f9fa;
            padding: 15px;
            border: 1px solid #e9ecef;
            overflow: auto;
            border-radius: 8px;
            margin: 10px 0;
        }

        button {
            padding: 12px 25px;
            margin: 20px 0;
            font-size: 15px;
            cursor: pointer;
            border: none;
            border-radius: 8px;
            background-color: #3498db;
            color: #fff;
            transition: all 0.3s ease;
        }

        button:hover {
            background-color: #2980b9;
            transform: translateY(-2px);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
        }

        footer {
            border-top: 1px solid #eee;
            text-align: center;
            color: #666;
            font-size: 14px;
            padding: 25px;
        }

        footer a {
            color: #3498db;
            text-decoration: none;
            transition: color 0.3s ease;
        }

        footer a:hover {
            color: #ffffffff;
            text-decoration: underline;
        }

        .donate-button {
            display: inline-block;
            padding: 8px 20px;
            margin-top: 10px;
            background-color: #27ae60;
            color: white;
            border-radius: 25px;
            text-decoration: none;
            transition: all 0.3s ease;
            font-weight: 500;
        }

        .donate-button:hover {
            background-color: #219a52;
            transform: scale(1.05);
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .validation {
            margin-top: 20px;
        }

        .validation img {
            vertical-align: middle;
            opacity: 0.9;
            transition: all 0.3s ease;
        }

        .validation img:hover {
            transform: scale(1.1);
            opacity: 1;
        }

        /* Media queries pour responsive */
        @media (max-width: 768px) {

            header,
            main,
            footer {
                width: 95%;
                padding: 20px;
            }

            header h1 {
                font-size: 24px;
            }
        }
    </style>

</head>

<body>

    <header style="margin-bottom:30px; padding:15px 0; border-bottom:1px solid #ccc; text-align:center; color:#333;">
        <div class="centered-text">
            <a href="https://thierrylaval.dev" target="_blank">
                <img src="https://raw.githubusercontent.com/thierry-laval/archives/master/images/logo-portfolio.png"
                    alt="Logo Développeur Web de Thierry Laval"
                    width="300" height="100"
                    style="max-width: 100%; height: auto; display: block; margin: 0 auto;"
                    loading="lazy"
                    class="zoom-effect">
            </a>
            <h1>🔍 Diagnostic Prestashop <?php echo $psVersion; ?></h1>
            <h2>Vérification complète de votre boutique</h2>
            <p>Ce script analyse votre installation : modules, thèmes, PHP, cache, Core et base de données.</p>

            <!-- AVERTISSEMENT IMPORTANT -->
            <div style="background:#fff3cd;border-left:4px solid #ffeeba;color:#856404;padding:12px 20px;margin:15px 20px 0;border-radius:4px;text-align:left;">
                <strong>⚠️ Avertissement :</strong>
                <ul style="margin:8px 0 0 18px;padding:0;">
                    <li>Ne laissez pas vos identifiants de base de données visibles dans ce script sur un serveur public.</li>
                    <li>Après usage, supprimez ce fichier du serveur pour des raisons de sécurité.</li>
                    <li>Si vous partagez le rapport, veillez à masquer ou supprimer toute information sensible (credentials, chemins exacts si nécessaire).</li>
                </ul>
            </div>
            <!-- FIN AVERTISSEMENT -->
        </div>
    </header>

    <main>
        <?php
        // Ici continue le script original (toutes les vérifications, modules, thèmes, cache, Core, DB…)

        // Fonction de vérification
        function check($condition, $message_ok, $message_fail, $class_ok = 'ok', $class_fail = 'fail')
        {
            echo $condition ? "<span class='$class_ok'>$message_ok</span><br>" : "<span class='$class_fail'>$message_fail</span><br>";
        }

        $dirRoot = __DIR__;

        // Paramètres DB : lecture sécurisée (env > settings.inc.php > app/config/parameters.php / parameters.yml)
        function mask_secret($s) {
            if ($s === null || $s === '') return '—';
            $len = strlen($s);
            if ($len <= 4) return str_repeat('*', $len);
            return substr($s, 0, 3) . str_repeat('*', max(3, $len - 5)) . substr($s, -2);
        }

        // utilitaire basique pour extraire valeurs depuis un fichier PHP/YAML de paramètres
        function parse_params_file(string $file): array {
            $result = [];
            $txt = @file_get_contents($file);
            if ($txt === false) return $result;

            // cherches les constantes _DB_* (settings.inc.php)
            if (preg_match("/_DB_HOST_\s*'\s*,\s*'([^']+)'/i", $txt, $m) || preg_match("/define\(\s*['_\"]_DB_HOST_['_\"]\s*,\s*['_\"]([^'\"]+)['_\"]\s*\)/i", $txt, $m)) {
                $result['host'] = $m[1];
            }
            // patterns courants (PHP array style ou YAML simple)
            $keys = [
                'database_name' => ['database_name', 'db_name', 'dbname', '_DB_NAME_', 'database_name'],
                'database_user' => ['database_user', 'db_user', 'db_user', '_DB_USER_'],
                'database_password' => ['database_password', 'db_password', 'db_passwd', '_DB_PASSWD_'],
                'database_prefix' => ['database_prefix', 'db_prefix', '_DB_PREFIX_'],
                'database_port' => ['database_port', 'db_port']
            ];
            foreach ($keys as $target => $variants) {
                foreach ($variants as $v) {
                    // PHP array style : 'key' => 'value'
                    if (preg_match("/['\"]" . preg_quote($v, '/') . "['\"]\s*=>\s*['\"]([^'\"]+)['\"]/i", $txt, $m)) {
                        $result[$target] = $m[1];
                        break;
                    }
                    // YAML style : key: value
                    if (preg_match("/^\s*" . preg_quote($v, '/') . "\s*:\s*([^\r\n#]+)/im", $txt, $m)) {
                        $result[$target] = trim(trim($m[1]), "\"' ");
                        break;
                    }
                    // define constants style
                    if (preg_match("/define\(\s*['\"]" . preg_quote($v, '/') . "['\"]\s*,\s*['\"]([^'\"]+)['\"]\s*\)/i", $txt, $m)) {
                        $result[$target] = $m[1];
                        break;
                    }
                }
            }
            return $result;
        }

        // 1) prioriser variables d'environnement (préférez définir PRESTASHOP_DB_*)
        $dbHost = getenv('PRESTASHOP_DB_HOST') ?: '';
        $dbPort = getenv('PRESTASHOP_DB_PORT') ?: '';
        $dbName = getenv('PRESTASHOP_DB_NAME') ?: '';
        $dbUser = getenv('PRESTASHOP_DB_USER') ?: '';
        $dbPass = getenv('PRESTASHOP_DB_PASS') ?: '';
        $dbPrefix = getenv('PRESTASHOP_DB_PREFIX') ?: '';

        // 2) fallback : settings.inc.php (constantes PrestaShop)
        $settingsFile = $dirRoot . '/config/settings.inc.php';
        if (file_exists($settingsFile)) {
            $content = @file_get_contents($settingsFile);
            if ($content !== false) {
                if (preg_match("/define\(\s*['_\"]_DB_NAME_['_\"]\s*,\s*['_\"]([^'\"]+)['_\"]\s*\)/i", $content, $m)) $dbName = $dbName ?: $m[1];
                if (preg_match("/define\(\s*['_\"]_DB_USER_['_\"]\s*,\s*['_\"]([^'\"]+)['_\"]\s*\)/i", $content, $m)) $dbUser = $dbUser ?: $m[1];
                if (preg_match("/define\(\s*['_\"]_DB_PASSWD_['_\"]\s*,\s*['_\"]([^'\"]+)['_\"]\s*\)/i", $content, $m)) $dbPass = $dbPass ?: $m[1];
                if (preg_match("/define\(\s*['_\"]_DB_PREFIX_['_\"]\s*,\s*['_\"]([^'\"]+)['_\"]\s*\)/i", $content, $m)) $dbPrefix = $dbPrefix ?: $m[1];
                if (preg_match("/define\(\s*['_\"]_DB_SERVER_['_\"]\s*,\s*['_\"]([^'\"]+)['_\"]\s*\)/i", $content, $m)) $dbHost = $dbHost ?: $m[1];
            }
        }

        // 3) fallback : app/config/parameters.php ou app/config/parameters.yml (différentes versions)
        $appParamsPhp = $dirRoot . '/app/config/parameters.php';
        $appParamsYml = $dirRoot . '/app/config/parameters.yml';
        if (file_exists($appParamsPhp)) {
            $parsed = parse_params_file($appParamsPhp);
            $dbName = $dbName ?: ($parsed['database_name'] ?? $parsed['database_name']);
            $dbUser = $dbUser ?: ($parsed['database_user'] ?? $parsed['database_user']);
            $dbPass = $dbPass ?: ($parsed['database_password'] ?? $parsed['database_password']);
            $dbPrefix = $dbPrefix ?: ($parsed['database_prefix'] ?? $parsed['database_prefix'] ?? '');
            $dbHost = $dbHost ?: ($parsed['host'] ?? $parsed['database_host'] ?? '');
            $dbPort = $dbPort ?: ($parsed['database_port'] ?? '');
        } elseif (file_exists($appParamsYml)) {
            $parsed = parse_params_file($appParamsYml);
            $dbName = $dbName ?: ($parsed['database_name'] ?? '');
            $dbUser = $dbUser ?: ($parsed['database_user'] ?? '');
            $dbPass = $dbPass ?: ($parsed['database_password'] ?? '');
            $dbPrefix = $dbPrefix ?: ($parsed['database_prefix'] ?? '');
            $dbHost = $dbHost ?: ($parsed['host'] ?? $parsed['database_host'] ?? '');
            $dbPort = $dbPort ?: ($parsed['database_port'] ?? '');
        }

        // 4) valeurs par défaut si manquantes
        $dbHost = $dbHost ?: 'localhost';
        $dbPort = $dbPort ?: '';
        $dbPrefix = $dbPrefix ?: 'ps_';

        // 5) message d'avertissement si identifiants manquants
        if (empty($dbName) || empty($dbUser)) {
            echo "<div style='background:#fff3cd;border-left:4px solid #ffeeba;color:#856404;padding:12px 20px;margin:15px 0;border-radius:4px;'>
                    <strong>⚠️ Avertissement :</strong> Identifiants de base de données non fournis. 
                    Définissez les variables d'environnement <code>PRESTASHOP_DB_NAME</code>, <code>PRESTASHOP_DB_USER</code>, <code>PRESTASHOP_DB_PASS</code> 
                    ou placez ce script à la racine d'une installation PrestaShop pour récupérer automatiquement <code>config/settings.inc.php</code> ou <code>app/config/parameters.php</code>.
                  </div>";
        }

        // 6) ne jamais afficher le mot de passe en clair — affichage masqué pour information
        $displayDbInfo = "<span class='info'>DB host: " . htmlspecialchars($dbHost) . ($dbPort ? ':' . htmlspecialchars($dbPort) : '') . " — DB name: " . htmlspecialchars(mask_secret($dbName)) . " — DB user: " . htmlspecialchars(mask_secret($dbUser)) . " — DB prefix: " . htmlspecialchars($dbPrefix) . "</span><br>";
        echo $displayDbInfo;

        // Informations système
        echo "<h2>Informations système :</h2>";
        $phpVersion = phpversion();
        check(version_compare($phpVersion, '8.2', '>='), "PHP $phpVersion : OK", "PHP $phpVersion : < 8.2 risque incompatibilités");
        echo "<span class='info'>Serveur OS : " . php_uname() . "</span><br>";
        echo "<span class='info'>Serveur web : " . $_SERVER['SERVER_SOFTWARE'] . "</span><br>";
        echo "<span class='info'>Nom du serveur : " . gethostname() . "</span><br>";

        // ----- VÉRIFICATIONS RAPIDES (déplacées ici) -----
        // Ces contrôles apparaissent directement sous "Informations système"
        echo "<h3>Contrôles supplémentaires :</h3>";

        // 1) Espace disque
        $df_free = @disk_free_space($dirRoot);
        $df_total = @disk_total_space($dirRoot);
        if ($df_free !== false && $df_total !== false) {
            $freeMB = round($df_free / 1024 / 1024);
            $totalMB = round($df_total / 1024 / 1024);
            echo "Espace disque sur la partition de la racine : $freeMB MB libre / $totalMB MB total<br>";
            check($freeMB > 512, "Espace disque : OK (>$freeMB MB libre)", "Espace disque : faible (<512 MB)", 'ok', 'fail');
        } else {
            echo "<span class='warn'>Impossible de déterminer l'espace disque</span><br>";
        }

        // 2) .htaccess présent à la racine
        $ht = $dirRoot . '/.htaccess';
        check(file_exists($ht), ".htaccess : présent", ".htaccess : absent — recommandé pour sécurité");

        // 3) Fichiers d'installation ou utilitaires exposés
        $dangerFiles = ['install.php', 'install-dev.php', 'upgrade.php', 'phpinfo.php', 'adminer.php', 'dump.sql'];
        $found = [];
        foreach ($dangerFiles as $df) {
            if (file_exists($dirRoot . '/' . $df)) $found[] = $df;
        }
        if (!empty($found)) {
            echo "<span class='fail'>Fichiers sensibles trouvés : " . implode(', ', $found) . " — supprimer après usage</span><br>";
        } else {
            echo "<span class='ok'>Aucun fichier d'installation/outil sensible détecté.</span><br>";
        }

        // 4) Vérifier présence d'un dossier admin non renommé (recherche admin*)
        $adminMatches = glob($dirRoot . '/admin*', GLOB_ONLYDIR) ?: [];
        $adminFolders = array_map(function ($p) use ($dirRoot) {
            return basename($p);
        }, $adminMatches);
        if (!empty($adminFolders)) {
            echo "<span class='warn'>Dossier back-office détecté : " . implode(', ', $adminFolders) . " (vérifier que ce n'est pas 'admin' en clair)</span><br>";
        } else {
            echo "<span class='ok'>Dossier admin : non détecté (ou renommé)</span><br>";
        }

        // 5) Vérifier overrides (/override) et customisations
        $overrideDir = $dirRoot . '/override';
        if (file_exists($overrideDir)) {
            $items = array_diff(scandir($overrideDir), ['.', '..']);
            if (!empty($items)) {
                echo "<span class='warn'>Overrides détectés dans /override (" . count($items) . " éléments) — attention aux compatibilités</span><br>";
            } else {
                echo "<span class='ok'>/override vide</span><br>";
            }
        } else {
            echo "<span class='info'>/override absent</span><br>";
        }

        // 6) Propriétaire des dossiers critiques (si posix disponible)
        $criticalPaths = [$dirRoot, $dirRoot . '/modules', $dirRoot . '/themes', $dirRoot . '/config'];
        foreach ($criticalPaths as $p) {
            if (file_exists($p)) {
                $owner = @fileowner($p);
                $ownerName = (function_exists('posix_getpwuid') && $owner !== false) ? @posix_getpwuid($owner)['name'] ?? $owner : ($owner !== false ? $owner : 'inconnu');
                echo "<span class='info'>Propriétaire " . str_replace($dirRoot, '', $p) . " : $ownerName</span><br>";
            }
        }

        // 7) Privilèges de l'utilisateur DB (SHOW GRANTS) - tentatif
        try {
            $dsn = "mysql:host=$dbHost;dbname=$dbName;charset=utf8mb4";
            $pdoTmp = new PDO($dsn, $dbUser, $dbPass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
            $grants = [];
            $stmt = $pdoTmp->query("SHOW GRANTS");
            $rows = $stmt->fetchAll(PDO::FETCH_NUM);
            foreach ($rows as $r) $grants[] = $r[0] ?? implode(' ', $r);
            if (!empty($grants)) {
                echo "<details><summary>Privilèges DB (SHOW GRANTS)</summary><pre>" . htmlspecialchars(implode("\n", $grants)) . "</pre></details>";
            } else {
                echo "<span class='warn'>Impossible de lire les privilèges DB</span><br>";
            }
        } catch (Throwable $e) {
            echo "<span class='warn'>SHOW GRANTS échoué : " . htmlspecialchars($e->getMessage()) . "</span><br>";
        }

        // Phrase explicative pour le lecteur
        echo "<p style='background:#f0f8ff;border-left:4px solid #b3dbff;padding:10px;margin-top:10px;color:#034f84;'>
            Remarque : les lignes « Propriétaire » indiquent quel utilisateur possède les dossiers sur l'hébergement, et le bloc « Privilèges DB (SHOW GRANTS) » affiche les droits SQL de l'utilisateur de la base. 
            Si votre boutique fonctionne correctement, aucune action n'est requise — sinon contactez votre hébergeur pour ajuster les droits.
        </p>";

        // 8) Taille totale des tables PrestaShop (approx)
        try {
            $pdoSize = isset($pdo) ? $pdo : (isset($pdoTmp) ? $pdoTmp : null);
            if ($pdoSize) {
                $sizeStmt = $pdoSize->query("SHOW TABLE STATUS LIKE '{$dbPrefix}%'");
                $totalBytes = 0;
                $count = 0;
                while ($row = $sizeStmt->fetch(PDO::FETCH_ASSOC)) {
                    $totalBytes += ($row['Data_length'] ?? 0) + ($row['Index_length'] ?? 0);
                    $count++;
                }
                $totalMB = round($totalBytes / 1048576, 2);
                echo "<span class='info'>Tables PS détectées : $count — taille totale ≈ {$totalMB} MB</span><br>";
            }
        } catch (Throwable $e) {
            // ignore
        }

        // B) Conflits d'override : même classe définie plusieurs fois
        echo "<h4>Conflits d'override (classes dupliquées)</h4>";
        $classMap = [];
        if (is_dir($overrideDir)) {
            $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($overrideDir));
            foreach ($it as $f) {
                if ($f->isFile() && strtolower($f->getExtension()) === 'php') {
                    $src = @file_get_contents($f->getPathname());
                    if ($src === false) continue;
                    if (preg_match_all('/class\s+([A-Za-z0-9_]+)/i', $src, $m)) {
                        foreach ($m[1] as $cls) {
                            $classMap[$cls][] = $f->getPathname();
                        }
                    }
                }
            }
            $conflicts = array_filter($classMap, function ($a) {
                return count($a) > 1;
            });
            if (!empty($conflicts)) {
                foreach ($conflicts as $cls => $files) {
                    $baseNames = array_map('basename', $files);
                    echo "<span class='fail'>Conflit override : classe $cls définie dans " . implode(', ', $baseNames) . "</span><br>";
                }
            } else {
                echo "<span class='ok'>Aucun conflit d'override détecté.</span><br>";
            }
        } else {
            echo "<span class='info'>/override absent — pas de vérification</span><br>";
        }

        // Fin des vérifications déplacées
        // trait supprimé pour éviter une ligne horizontale en sortie

        // Détection version Prestashop (améliorée)
        $psVersion = 'non détectée';

        // 1) settings.inc.php classique
        $settingsFile = $dirRoot . '/config/settings.inc.php';
        if (file_exists($settingsFile)) {
            @include($settingsFile);
            if (defined('_PS_VERSION_')) $psVersion = _PS_VERSION_;
        }

        // 2) Constants.php et PrestaShopVersion class
        if ($psVersion === 'non détectée') {
            $constantsFile = $dirRoot . '/vendor/prestashop/prestashop/core/Constants.php';
            if (file_exists($constantsFile)) {
                include_once($constantsFile);
                if (defined('VERSION')) $psVersion = VERSION;
            }
        }
        if ($psVersion === 'non détectée') {
            $versionClassFile = $dirRoot . '/vendor/prestashop/prestashop/src/Core/PrestaShopVersion.php';
            if (file_exists($versionClassFile)) {
                include_once($versionClassFile);
                if (class_exists('PrestaShop\PrestaShop\Core\PrestaShopVersion')) {
                    try {
                        $ref = new ReflectionClass('PrestaShop\PrestaShop\Core\PrestaShopVersion');
                        $props = $ref->getDefaultProperties();
                        if (!empty($props['VERSION'])) $psVersion = $props['VERSION'];
                    } catch (Throwable $e) {
                        // ignore
                    }
                }
            }
        }

        // 3) composer.lock et composer.json (recherche du package prestashop/prestashop)
        if ($psVersion === 'non détectée') {
            $composerLock = $dirRoot . '/composer.lock';
            if (file_exists($composerLock)) {
                $lock = @json_decode(@file_get_contents($composerLock), true);
                $packages = $lock['packages'] ?? $lock['packages-dev'] ?? [];
                foreach ($packages as $p) {
                    if (!empty($p['name']) && stripos($p['name'], 'prestashop/prestashop') !== false && !empty($p['version'])) {
                        $psVersion = $p['version'];
                        break;
                    }
                }
            }
        }
        if ($psVersion === 'non détectée') {
            $composerJson = $dirRoot . '/composer.json';
            if (file_exists($composerJson)) {
                $cj = @json_decode(@file_get_contents($composerJson), true);
                $require = $cj['require'] ?? [];
                foreach ($require as $name => $ver) {
                    if (stripos($name, 'prestashop/') !== false || stripos($name, 'prestashop-prestashop') !== false) {
                        $psVersion = $ver;
                        break;
                    }
                }
            }
        }

        // 4) Tentative via Symfony console si disponible (exécutable vendor/bin/console)
        if ($psVersion === 'non détectée') {
            $vendorConsole = $dirRoot . '/vendor/bin/console';
            if (file_exists($vendorConsole) && (is_executable($vendorConsole) || is_file($vendorConsole))) {
                if (function_exists('shell_exec')) {
                    $cmd = 'php ' . escapeshellarg($vendorConsole) . ' --version 2>&1';
                    $out = @shell_exec($cmd);
                    if ($out) {
                        // Exemples de sorties : "Symfony 5.4.0 (env)..." ou "PrestaShop 1.7.x"
                        if (preg_match('/PrestaShop\s*([0-9\.]+)/i', $out, $m)) $psVersion = $m[1];
                        elseif (preg_match('/(\d+\.\d+(\.\d+)?)/', $out, $m2)) $psVersion = $m2[1];
                    }
                } elseif (function_exists('proc_open')) {
                    // fallback léger si shell_exec désactivé
                    $descriptors = [1 => ['pipe', 'w'], 2 => ['pipe', 'w']];
                    $proc = @proc_open('php ' . escapeshellarg($vendorConsole) . ' --version', $descriptors, $pipes, $dirRoot);
                    if (is_resource($proc)) {
                        $out = stream_get_contents($pipes[1]) . stream_get_contents($pipes[2]);
                        fclose($pipes[1]);
                        fclose($pipes[2]);
                        @proc_close($proc);
                        if (preg_match('/PrestaShop\s*([0-9\.]+)/i', $out, $m)) $psVersion = $m[1];
                        elseif (preg_match('/(\d+\.\d+(\.\d+)?)/', $out, $m2)) $psVersion = $m2[1];
                    }
                }
            }
        }

        // 5) INSTALL.txt fallback (déjà présent dans votre script, laisse inchangé)
        if ($psVersion === 'non détectée') {
            $installFile = $dirRoot . '/INSTALL.txt';
            if (file_exists($installFile)) {
                $contents = file_get_contents($installFile);
                if (preg_match('/PrestaShop\s*([0-9\.]+)/i', $contents, $matches)) {
                    $psVersion = $matches[1];
                }
            }
        }

        check($psVersion !== 'non détectée', "Prestashop version : $psVersion", "Prestashop version : non détectée");

        // Activer debug
        $definesFile = $dirRoot . '/config/defines.inc.php';
        if (file_exists($definesFile)) {
            $content = file_get_contents($definesFile);
            if (strpos($content, "'_PS_MODE_DEV_', true") === false) {
                $content = str_replace("_PS_MODE_DEV_', false", "_PS_MODE_DEV_', true", $content);
                file_put_contents($definesFile, $content);
            }
            check(true, "Mode debug : activé", "Mode debug : activation impossible");
        }

        // Connexion base de données et modules activés
        echo "<h3>Connexion DB :</h3>";
        try {
            $dsn = "mysql:host=$dbHost;dbname=$dbName;charset=utf8mb4";
            $pdo = new PDO($dsn, $dbUser, $dbPass);
            $stmt = $pdo->query("SELECT VERSION() AS version");
            $version = $stmt->fetch(PDO::FETCH_ASSOC);
            echo "<span class='ok'>Connexion OK (MySQL {$version['version']})</span><br>";

            $stmt = $pdo->query("SELECT name, active FROM {$dbPrefix}module");
            $dbModules = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo "<h3>Modules activés :</h3>";

            $missing = false;
            foreach ($dbModules as $mod) {
                $modPath = $dirRoot . '/modules/' . $mod['name'];
                if ($mod['active'] == 1 && !file_exists($modPath)) {
                    echo "<span class='fail'>Module activé dans DB mais absent physiquement : {$mod['name']}</span><br>";
                    $missing = true;
                }
            }

            if (!$missing) {
                echo "<span class='ok'>Aucun module activé ne manque physiquement.</span><br>";
            }
        } catch (Exception $e) {
            echo "<span class='fail'>Connexion DB : ÉCHEC - " . htmlspecialchars($e->getMessage()) . "</span><br>";
        }

        // Limites PHP
        echo "<h3>Limites PHP :</h3>";
        $memory = ini_get('memory_limit');
        $time = ini_get('max_execution_time');
        echo "Memory limit : $memory (minimum recommandé 256M)<br>";
        echo "Max execution time : $time secondes (minimum recommandé 60s)<br>";
        if ((int)str_replace(['M', 'G'], '', $memory) < 256) echo "<span class='fail'>Augmenter memory_limit via .user.ini sur o2switch</span><br>";
        else echo "<span class='ok'>Memory_limit OK</span><br>";
        if ($time < 60) echo "<span class='fail'>Augmenter max_execution_time via .user.ini sur o2switch</span><br>";
        else echo "<span class='ok'>Max execution time OK</span><br>";

        // Vider cache
        $cacheDirs = ['/var/cache/prod', '/var/cache/dev'];
        echo "<h3>Cache :</h3>";
        foreach ($cacheDirs as $dir) {
            $full = $dirRoot . $dir;
            if (file_exists($full)) {
                $items = array_diff(scandir($full), ['.', '..']);
                foreach ($items as $f) {
                    $filePath = $full . '/' . $f;
                    if (is_dir($filePath)) rmdir_recursive($filePath);
                    else @unlink($filePath);
                }
                check(true, "Cache $dir vidé", "Cache $dir : impossible à vider");
            } else echo "<span class='fail'>$dir : INEXISTANT</span><br>";
        }
        function rmdir_recursive($dir)
        {
            $items = array_diff(scandir($dir), ['.', '..']);
            foreach ($items as $item) {
                $path = $dir . '/' . $item;
                if (is_dir($path)) rmdir_recursive($path);
                else @unlink($path);
            }
            @rmdir($dir);
        }

        // Extensions PHP
        $extensions = ['pdo_mysql', 'gd', 'curl', 'intl', 'zip', 'xml', 'mbstring', 'openssl', 'fileinfo'];
        echo "<h3>Extensions PHP :</h3>";
        foreach ($extensions as $ext) {
            check(extension_loaded($ext), "$ext : OK", "$ext : MANQUANTE");
        }

        // Dossiers critiques
        $paths = ['/var', '/var/cache', '/var/cache/prod', '/var/cache/dev', '/upload', '/app/config', '/modules', '/themes'];
        echo "<h3>Dossiers critiques :</h3>";
        foreach ($paths as $p) {
            $full = $dirRoot . $p;
            if (file_exists($full)) {
                $perms = substr(sprintf('%o', fileperms($full)), -4);
                check(true, "$p : $perms", "$p : problème de permission, devrait être 755");
            } else echo "<span class='fail'>$p : INEXISTANT</span><br>";
        }

        // Fichiers essentiels
        $files = ['/index.php', '/config/defines.inc.php', '/app/config/parameters.php'];
        echo "<h3>Fichiers essentiels :</h3>";
        foreach ($files as $f) {
            $full = $dirRoot . $f;
            check(file_exists($full), "$f : OK", "$f : INEXISTANT");
        }

        // Section Modules unifiée
        echo "<h3>Modules :</h3>";
        $dbModulesList = [];
        try {
            $dsn = "mysql:host=$dbHost;dbname=$dbName;charset=utf8mb4";
            $pdo = new PDO($dsn, $dbUser, $dbPass);
            $stmt = $pdo->query("SELECT name, active FROM {$dbPrefix}module");
            $dbModules = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($dbModules as $mod) {
                $dbModulesList[$mod['name']] = ($mod['active'] == 1) ? "ACTIF" : "INACTIF";
            }
        } catch (Exception $e) {
            echo "<span class='fail'>Connexion DB : ÉCHEC - " . $e->getMessage() . "</span><br>";
        }

        // Modules physiques
        $modulesDir = $dirRoot . '/modules';
        $physicalModules = [];
        if (file_exists($modulesDir)) {
            $folders = array_filter(scandir($modulesDir), function ($d) {
                return $d !== '.' && $d !== '..';
            });
            foreach ($folders as $m) {
                if (is_dir($modulesDir . '/' . $m)) {
                    $physicalModules[] = $m;
                }
            }
        }

        // Fusion modules mais affichage condensé selon ordre demandé (evite doublons)
        $preferredOrder = [
            'ps_linklist',
            'blockreassurance',
            'blockwishlist',
            'psgdpr',
            'ps_contactinfo',
            'ps_languageselector',
            'ps_currencyselector',
            'ps_customersignin',
            'ps_shoppingcart',
            'ps_mainmenu',
            'ps_searchbar',
            'ps_imageslider',
            'ps_featuredproducts',
            'ps_banner',
            'ps_customtext',
            'ps_specials',
            'ps_emailsubscription',
            'ps_socialfollow',
            'ps_customeraccountlinks',
            'productcomments',
            'ps_categorytree',
            'contactform',
            'ps_sharebuttons',
            'statspersonalinfos',
            'ps_facebook',
            'statsdata',
            'statsforecast',
            'statsbestcategories',
            'dashtrends',
            'ps_supplierlist',
            'statsbestmanufacturers',
            'ps_eventbus',
            'dashgoals',
            'ps_viewedproduct',
            'statsbestsuppliers',
            'ps_checkpayment',
            'ps_wirepayment',
            'statsstock',
            'dashactivity',
            'gsitemap',
            'statsproduct',
            'statsregistrations',
            'ps_crossselling',
            'ps_categoryproducts',
            'dashproducts',
            'statscatalog',
            'ps_mbo',
            'ps_themecusto',
            'ps_classic_edition',
            'statscarrier',
            'graphnvd3',
            'ps_faviconnotificationbo',
            'ps_dataprivacy',
            'statsbestcustomers',
            'ps_cashondelivery',
            'ps_brandlist',
            'ps_googleanalytics',
            'ps_emailalerts',
            'pagesnotfound',
            'gamification',
            'ps_apiresources',
            'statsbestvouchers',
            'statsbestproducts',
            'statssearch',
            'statssales',
            'statsnewsletter',
            'statscheckup',
            'gridhtml',
            'ps_facetedsearch',
            'ps_accounts',
            'psshipping',
            'ps_distributionapiclient',
            'ps_newproducts',
            'psxmarketingwithgoogle',
            'ps_checkout',
            'ps_bestsellers',
            'psassistant'
        ];

        // Construire la liste complète sans doublon (assure que la variable existe)
        if (!isset($allModules) || !is_array($allModules)) {
            $allModules = array_unique(array_merge(array_keys($dbModulesList ?? []), $physicalModules ?? []));
        }
        // initialisation
        $printed = [];

        // Préparer tableau
        echo '<table style="width:100%;border-collapse:collapse;margin:10px 0;">';
        echo '<thead><tr>';
        echo '<th style="text-align:left;padding:8px;border-bottom:2px solid #ddd;">Module</th>';
        echo '<th style="text-align:left;padding:8px;border-bottom:2px solid #ddd;">Statut</th>';
        echo '<th style="text-align:left;padding:8px;border-bottom:2px solid #ddd;">Présence</th>';
        echo '</tr></thead><tbody>';

        // Lister selon ordre préféré
        foreach ($preferredOrder as $m) {
            if (!in_array($m, $allModules) && in_array($m, $physicalModules)) $allModules[] = $m;
            if (!in_array($m, $allModules)) continue;
            $status = $dbModulesList[$m] ?? "NON DANS DB";
            $physical = in_array($m, $physicalModules) ? "physique OK" : "MANQUANT";
            echo '<tr>';
            echo '<td style="padding:6px 8px;border-bottom:1px solid #eee;">' . htmlspecialchars($m) . '</td>';
            echo '<td style="padding:6px 8px;border-bottom:1px solid #eee;color:' . ($status === 'ACTIF' ? 'green' : ($status === 'INACTIF' ? 'orange' : '#555')) . ';">' . htmlspecialchars($status) . '</td>';
            echo '<td style="padding:6px 8px;border-bottom:1px solid #eee;color:' . ($physical === 'physique OK' ? 'green' : 'red') . ';">' . htmlspecialchars($physical) . '</td>';
            echo '</tr>';
            $printed[] = $m;
        }

        // Rester modules non listés (sans doublons)
        foreach ($allModules as $m) {
            if (in_array($m, $printed)) continue;
            $status = $dbModulesList[$m] ?? "NON DANS DB";
            $physical = in_array($m, $physicalModules) ? "physique OK" : "MANQUANT";
            echo '<tr>';
            echo '<td style="padding:6px 8px;border-bottom:1px solid #eee;">' . htmlspecialchars($m) . '</td>';
            echo '<td style="padding:6px 8px;border-bottom:1px solid #eee;color:' . ($status === 'ACTIF' ? 'green' : ($status === 'INACTIF' ? 'orange' : '#555')) . ';">' . htmlspecialchars($status) . '</td>';
            echo '<td style="padding:6px 8px;border-bottom:1px solid #eee;color:' . ($physical === 'physique OK' ? 'green' : 'red') . ';">' . htmlspecialchars($physical) . '</td>';
            echo '</tr>';
        }

        echo '</tbody></table>';

        // Thèmes
        echo "<h3>Thèmes :</h3>";
        $themesDir = $dirRoot . '/themes';
        if (file_exists($themesDir)) {
            $themeFolders = array_filter(scandir($themesDir), function ($d) {
                return $d !== '.' && $d !== '..' && substr($d, 0, 1) !== '_';
            });
            foreach ($themeFolders as $theme) {
                $themePath = $themesDir . '/' . $theme;
                if (is_dir($themePath)) {
                    $config = $themePath . '/config/theme.yml';
                    check(file_exists($config), "Thème $theme : config OK", "Thème $theme : config manquante (theme.yml)");
                }
            }
        } else echo "<span class='fail'>Dossier themes introuvable</span><br>";

        // Core Prestashop
        echo "<h3>Core Prestashop (/vendor) :</h3>";
        $vendorDir = $dirRoot . '/vendor';
        if (file_exists($vendorDir)) {
            $criticalFiles = ['composer/autoload_real.php'];
            foreach ($criticalFiles as $file) {
                $full = $vendorDir . '/' . $file;
                check(file_exists($full), "$file : OK", "$file : manquant, upload complet nécessaire");
            }
        } else echo "<span class='fail'>/vendor introuvable, Prestashop non complet</span><br>";

        // --- Bouton téléchargement HTML ---
        echo "<h3>Diagnostic terminé.</h3>";
        echo "<form method='post'>
        <button type='submit' name='download_html'>Télécharger le rapport HTML</button>
      </form>";
        if (isset($_POST['download_html'])) {
            // Récupère le contenu complet du buffer
            $content = ob_get_contents();

            // Vide le buffer pour envoyer des headers propres
            ob_end_clean();

            // Confirmation affichée dans la console PHP (pas sur la page)
            // car après les headers, le HTML ne s’affichera plus
            error_log("✅ Rapport HTML prêt pour téléchargement.");

            // Envoi direct du rapport au navigateur
            header('Content-Type: text/html');
            header('Content-Disposition: attachment; filename="diagnostic_prestashop.html"');
            header('Content-Length: ' . strlen($content));
            echo $content;
            exit;
        }
        ?>

    </main>

    <footer style="margin-top:40px; padding:20px 0; border-top:1px solid #ccc; text-align:center; color:#555; font-size:13px;">
        <p><strong>🛠️ Diagnostic Prestashop généré automatiquement - Licence : MIT</strong></p>
        <p> 🇫🇷 &copy; 2025 Réalisé par <strong>Thierry Laval</strong> —
            <a href="https://thierrylaval.dev" target="_blank" style="color:#3366cc; text-decoration:none;">thierrylaval.dev</a>
        </p>
        <p style="padding:10px;">
            Pour soutenir mon travail :<br>
            <a href="https://revolut.me/laval96o"
                target="_blank"
                title="Un petit don, ça vous dit ? Ça m'aidera à partager mes outils gratuitement ! 😊"
                class="donate-button">
                👉🏻 Offrez-moi un café ☕️
            </a>
        </p>
        <div>
            <img src="https://www.w3.org/assets/logos/w3c/w3c-developers-dark.svg"
                alt="W3C Developers"
                width="100"
                height="35">
            <img src="https://www.php.net/images/logos/php-med-trans.png"
                alt=" PHP Logo"
                width="50"
                height="35"
                style="margin-left:10px;">
            <img src="https://experts.prestashop.com/images/logos/prestashop-logo.svg"
                alt="Prestashop Logo"
                width="100"
                height="35"
                style="margin-left:10px;">
        </div>

    </footer>




</body>

</html>