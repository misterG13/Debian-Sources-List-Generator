<?php

// SPDX-License-Identifier: MPL-2.0

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
$tor             = false;
$format          = SourceFormat::OneLine;
$signedBy        = '';
$output          = '';
$hasOutput       = false;

// --- Process POST ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (!hash_equals($csrfToken, $_POST['csrf_token'] ?? '')) {
    http_response_code(403);
    exit('Invalid CSRF token.');
  }

  $action      = ($_POST['action'] ?? 'generate') === 'update' ? 'update' : 'generate';
  $countryCode = (string) ($_POST['country'] ?? '');

  $mirrorUrl       = (string) ($_POST['mirror'] ?? $mirrorUrl);
  $arch            = (string) ($_POST['arch'] ?? $arch);
  $src             = isset($_POST['src']);
  $contrib         = isset($_POST['contrib']);
  $nonFree         = isset($_POST['non-free']);
  $nonFreeFirmware = isset($_POST['non-free-firmware']);
  $security        = isset($_POST['security']);
  $useHttps        = isset($_POST['https']);
  $tor             = ($_POST['tor'] ?? '') === '1';
  $format          = SourceFormat::tryFrom((string) ($_POST['format'] ?? '')) ?? SourceFormat::OneLine;
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

  // A specific country was chosen: its mirror list must contain the posted mirror,
  // otherwise fall back to the country's first mirror so one Generate click is enough.
  if ($countryCode !== '' && $selectedCountryMirrors !== []) {
    $mirrorUrls = array_map(static fn (Mirror $m): string => $m->url(), $selectedCountryMirrors);
    if (!in_array($mirrorUrl, $mirrorUrls, true)) {
      $mirrorUrl = $selectedCountryMirrors[0]->url();
    }
  }

  // 'update' only refreshes the country/mirror dropdowns; 'generate' also builds the sources list.
  if ($action === 'generate') {
    $generator = new SourcesListGenerator();
    $output    = $generator->generate(
      mirror: $mirrorUrl,
      release: $release,
      arch: $arch,
      includeSources: $src,
      contrib: $contrib,
      nonFree: $nonFree,
      nonFreeFirmware: $nonFreeFirmware,
      security: $security,
      signedBy: $signedBy !== '' ? $signedBy : null,
      format: $format,
      tor: $tor,
    );

    if ($useHttps && !$tor) {
      $output = str_replace('http://', 'https://', $output);
    }

    $hasOutput = true;
  }
}

// --- Mirrors for template (GET default: CDN mirror) ---
$selectedCountryMirrors = $selectedCountryMirrors ?? getMirrorsForCountry($countryCode, $countries);
?>
<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=5" />
  <title>Sources List Generator for Debian</title>
  <meta name="description" content="Sources List Generator for official Debian repositories" />
  <link rel="stylesheet" href="styles.css" />
</head>

<body>
  <main>
    <div class="wrap--header">
      <div class="wrap">
        <h2 class="header">Debian Sources List Generator</h2>
      </div>
    </div>
    <div class="wrap">
      <form method="POST" action="">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>" />

        <!-- ── Country Select ──────────────────────────────────────── -->
        <div class="elemnts--inline">
          <p>
            <label>Country<br />
              <select name="country" tabindex="1" autofocus>
                <option value="" <?= $countryCode === '' ? 'selected' : '' ?>>Global (CDN - deb.debian.org)</option>
                <?php foreach ($countries as $c) : ?>
                  <option value="<?= htmlspecialchars($c->code) ?>" <?= $countryCode === $c->code ? 'selected' : '' ?>><?= htmlspecialchars($c->name) ?></option>
                <?php endforeach; ?>
              </select></label>
          </p>

          <!-- ── Mirror Select ────────────────────────────────────── -->
          <p>
            <label>Mirror<br />
              <select name="mirror" tabindex="2">
                <?php foreach ($selectedCountryMirrors as $m) : ?>
                  <option value="<?= htmlspecialchars($m->url()) ?>" <?= $mirrorUrl === $m->url() ? 'selected' : '' ?>><?= htmlspecialchars($m->label()) ?></option>
                <?php endforeach; ?>
              </select></label>
          </p>
        </div>

        <!-- ── Refresh mirrors for the selected country ────────────── -->
        <div class="actions">
          <button type="submit" name="action" value="update" class="button button--update" tabindex="3">Update mirrors</button>
        </div>

        <!-- ── Generation fields ──────────────────────────────────── -->
        <div id="generationFields">
          <div class="elemnts--inline">
            <p>
              <label>Releases<br />
                <select name="releases" tabindex="4">
                  <?php foreach (Release::cases() as $r) : ?>
                    <option value="<?= $r->value ?>" <?= $release === $r ? 'selected' : '' ?>><?= $r->label() ?></option>
                  <?php endforeach; ?>
                </select></label>
            </p>

            <p>
              <label>Arch<br />
                <select name="arch" tabindex="5">
                  <?php foreach (ARCHITECTURES as $a) : ?>
                    <option value="<?= htmlspecialchars($a) ?>" <?= $arch === $a ? 'selected' : '' ?>><?= htmlspecialchars($a) ?></option>
                  <?php endforeach; ?>
                </select></label>
            </p>

            <p>
              <label>Format<br />
                <select name="format" tabindex="6">
                  <?php foreach (SourceFormat::cases() as $f) : ?>
                    <option value="<?= $f->value ?>" <?= $format === $f ? 'selected' : '' ?>><?= $f->label() ?></option>
                  <?php endforeach; ?>
                </select></label>
            </p>
          </div>

          <div class="section-group">
            <div class="checkbox-grid">
              <label><input name="src" type="checkbox" <?= $src ? 'checked' : '' ?> tabindex="7" /> Include sources</label>
              <label><input name="contrib" type="checkbox" <?= $contrib ? 'checked' : '' ?> tabindex="8" /> Contrib</label>
              <label><input name="non-free" type="checkbox" <?= $nonFree ? 'checked' : '' ?> tabindex="9" /> Non-Free</label>
              <label<?= $release->hasNonFreeFirmware() ? '' : ' class="hidden"' ?>><input name="non-free-firmware" type="checkbox" <?= $nonFreeFirmware ? 'checked' : '' ?> tabindex="10" /> Non-Free Firmware</label>
              <label><input name="security" type="checkbox" <?= $security ? 'checked' : '' ?> tabindex="11" /> Security</label>
              <label><input name="https" type="checkbox" <?= $useHttps ? 'checked' : '' ?> tabindex="12" /> HTTPS</label>
            </div>
          </div>

          <div class="section-group">
            <p>
              <label>Signed-By (optional)<br />
                <input type="text" name="signed-by" class="input--wide" placeholder="/usr/share/keyrings/debian-archive-keyring.gpg" value="<?= htmlspecialchars($signedBy) ?>" tabindex="13" /></label>
            </p>
          </div>

          <div class="actions">
            <button type="submit" name="action" value="generate" class="button" tabindex="14">Generate</button>
            <button type="submit" name="tor" value="1" class="button" tabindex="15">Generate via Tor</button>
            <a href="?" class="button button--reset" tabindex="16">Reset</a>
          </div>
          <p class="field-help">Generate via Tor uses Debian's official onion services and ignores the selected mirror and HTTPS setting. Requires <code>tor</code> and <code>apt-transport-tor</code>.</p>

          <div class="section-group">
            <p>
              <label>Sources List <code><?= $format === SourceFormat::Deb822 ? '/etc/apt/sources.list.d/debian.sources' : '/etc/apt/sources.list' ?></code><br />
                <div class="code-block" role="region" aria-label="Sources List output"><?= htmlspecialchars($output) ?></div>
              </label>
            </p>

            <?php if ($hasOutput) : ?>
              <p>
                <label>CLI Command<br />
                  <div class="code-block" role="region" aria-label="CLI Command output"><?= htmlspecialchars($format === SourceFormat::Deb822
                    ? "sudo tee /etc/apt/sources.list.d/debian.sources <<EOF\n# Debian {$release->label()} debian.sources\n" . $output . "\nEOF\n\nsudo apt update"
                    : "sudo tee /etc/apt/sources.list.d/debian-{$release->value}-sources.list <<EOF\n# Debian {$release->label()} sources.list\n" . $output . "\nEOF\n\nsudo apt update") ?></div>
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