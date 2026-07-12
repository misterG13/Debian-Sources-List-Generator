<?php

declare(strict_types=1);

require_once __DIR__ . '/lib/generator.php';

session_start();

// --- CSRF Token ---
if (empty($_SESSION['csrf_token'])) {
  $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrfToken = $_SESSION['csrf_token'];

// --- Data ---
$countries = getCountries();

// --- Form state (defaults) ---
$step            = 1;
$countryCode     = '';
$mirrorUrl       = getCdnMirror()->url();
$release         = Release::Stable;
$arch            = '';
$src             = false;
$contrib         = true;
$nonFree         = true;
$nonFreeFirmware = true;
$security        = true;
$useHttps        = false;
$signedBy        = '';
$output          = '';
$hasOutput       = false;

// --- Process POST ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (!hash_equals($csrfToken, $_POST['csrf_token'] ?? '')) {
    http_response_code(403);
    exit('Invalid CSRF token.');
  }

  $step        = (int) ($_POST['step'] ?? 1);
  $countryCode = (string) ($_POST['country'] ?? '');

  // Step 1 → advance to step 2, populate mirrors for selected country
  if ($step === 1) {
    $step = 2;
  }

  // Step 2 → read form values, generate sources.list
  if ($step === 2) {
    $mirrorUrl       = (string) ($_POST['mirror'] ?? $mirrorUrl);
    $arch            = (string) ($_POST['arch'] ?? $arch);
    $src             = isset($_POST['src']);
    $contrib         = isset($_POST['contrib']);
    $nonFree         = isset($_POST['non-free']);
    $nonFreeFirmware = isset($_POST['non-free-firmware']);
    $security        = isset($_POST['security']);
    $useHttps        = isset($_POST['https']);
    $signedBy        = (string) ($_POST['signed-by'] ?? '');

    $releaseRaw = $_POST['releases'] ?? $release->value;
    $release    = Release::tryFrom($releaseRaw) ?? Release::Stable;

    // Validate mirror URL
    if (findMirrorByUrl($mirrorUrl) === null) {
      $mirrorUrl = getCdnMirror()->url();
    }

    // If no country selected, try to infer from mirror URL
    if ($countryCode === '' && $mirrorUrl !== getCdnMirror()->url()) {
      $countryCode = findCountryByMirror($mirrorUrl, $countries) ?? '';
    }

    $selectedCountryMirrors = getMirrorsForCountry($countryCode, $countries);

    $generator = new SourcesListGenerator();
    $output    = $generator->generate(
      mirror: $mirrorUrl,
      release: $release,
      arch: $arch,
      includeSource: $src,
      contrib: $contrib,
      nonFree: $nonFree,
      nonFreeFirmware: $nonFreeFirmware,
      security: $security,
      signedBy: $signedBy !== '' ? $signedBy : null,
    );

    if ($useHttps) {
      $output = str_replace('http://', 'https://', $output);
    }

    $hasOutput = true;
  }
}

// --- Mirrors for template (step 1 GET: CDN default) ---
$selectedCountryMirrors = $selectedCountryMirrors ?? getMirrorsForCountry($countryCode, $countries);
?>
<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=5" />
  <title>Source List Generator for Debian</title>
  <meta name="description" content="Sources List Generator for official Debian repositories" />
  <link rel="stylesheet" href="styles.css" />
</head>

<body>
  <main>
    <div class="wrap--header">
      <div class="wrap">
        <h2 class="header">Debian Source List Generator</h2>
      </div>
    </div>
    <div class="wrap">
      <form method="POST" action="">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>" />
        <input type="hidden" name="step" id="stepField" value="<?= $step ?>" />

        <!-- ── Country Select (always visible) ─────────────────────── -->
        <div class="elemnts--inline">
          <p>
            <label>Country<br />
              <select name="country" id="countrySelect" tabindex="1" autofocus>
                <option value="" <?= $countryCode === '' ? 'selected' : '' ?>>Global (CDN - deb.debian.org)</option>
                <?php foreach ($countries as $c) : ?>
                  <option value="<?= htmlspecialchars($c->code) ?>" <?= $countryCode === $c->code ? 'selected' : '' ?>><?= htmlspecialchars($c->name) ?></option>
                <?php endforeach; ?>
              </select></label>
          </p>

          <!-- ── Mirror Select (step 2 only) ──────────────────────── -->
          <?php if ($step === 2) : ?>
            <p>
              <label>Mirror<br />
                <select name="mirror" tabindex="2">
                  <?php foreach ($selectedCountryMirrors as $m) : ?>
                    <option value="<?= htmlspecialchars($m->url()) ?>" <?= $mirrorUrl === $m->url() ? 'selected' : '' ?>><?= htmlspecialchars($m->label()) ?></option>
                  <?php endforeach; ?>
                </select></label>
            </p>
          <?php endif; ?>

          <!-- ── Step 1: Next button ──────────────────────────────── -->
          <?php if ($step === 1) : ?>
            <p><button type="submit" class="button" tabindex="2">Next &rarr;</button></p>
          <?php endif; ?>
        </div>

        <!-- ── Step 2 fields ──────────────────────────────────────── -->
        <div id="step2Fields" class="<?= $step === 2 ? '' : 'hidden' ?>">
          <div class="elemnts--inline">
            <p>
              <label>Releases<br />
                <select name="releases" id="releasesSelect" tabindex="3">
                  <?php foreach (Release::cases() as $r) : ?>
                    <option value="<?= $r->value ?>" <?= $release === $r ? 'selected' : '' ?>><?= $r->label() ?></option>
                  <?php endforeach; ?>
                </select></label>
            </p>

            <p>
              <label>Arch<br />
                <select name="arch" tabindex="4">
                  <?php foreach (ARCHITECTURES as $a) : ?>
                    <option value="<?= htmlspecialchars($a) ?>" <?= $arch === $a ? 'selected' : '' ?>><?= htmlspecialchars($a) ?></option>
                  <?php endforeach; ?>
                </select></label>
            </p>
          </div>

          <div class="section-group">
            <div class="checkbox-grid">
              <label><input name="src" type="checkbox" <?= $src ? 'checked' : '' ?> tabindex="5" /> Include source</label>
              <label><input name="contrib" type="checkbox" <?= $contrib ? 'checked' : '' ?> tabindex="6" /> Contrib</label>
              <label><input name="non-free" type="checkbox" <?= $nonFree ? 'checked' : '' ?> tabindex="7" /> Non-Free</label>
              <label id="nonFreeFirmwareLabel" <?= $release->hasNonFreeFirmware() ? '' : 'style="display:none"' ?>><input name="non-free-firmware" type="checkbox" <?= $nonFreeFirmware ? 'checked' : '' ?> tabindex="8" /> Non-Free Firmware</label>
              <label><input name="security" type="checkbox" <?= $security ? 'checked' : '' ?> tabindex="9" /> Security</label>
              <label><input name="https" type="checkbox" <?= $useHttps ? 'checked' : '' ?> tabindex="10" /> HTTPS</label>
            </div>
          </div>

          <div class="section-group">
            <p>
              <label>Signed-By (optional)<br />
                <input type="text" name="signed-by" id="signedByInput" placeholder="/usr/share/keyrings/debian-archive-keyring.gpg" value="<?= htmlspecialchars($signedBy) ?>" tabindex="11" style="width:100%;max-width:30rem;padding:.4rem;font-family:inherit;font-size:inherit" /></label>
            </p>
          </div>

          <div class="actions">
            <button type="submit" class="button" tabindex="12">Update</button>
            <button type="button" class="button button--reset" onclick="window.location.href='?'" tabindex="13">Reset</button>
          </div>

          <div class="section-group">
            <p>
              <label>Source List <code>/etc/apt/sources.list</code><br />
                <div class="code-block" role="region" aria-label="Source List output"><?= htmlspecialchars($output) ?></div>
              </label>
            </p>

            <?php if ($hasOutput) : ?>
              <p>
                <label>CLI Command<br />
                  <div class="code-block" role="region" aria-label="CLI Command output"><?= htmlspecialchars("sudo tee /etc/apt/sources.list.d/debian-{$release->value}-sources.list <<EOF\n# Debian {$release->label()} sources.list\n" . $output . "\nEOF\n\nsudo apt update") ?></div>
                </label>
              </p>
            <?php endif; ?>
          </div>
        </div>
      </form>
    </div>
  </main>
</body>

</html>