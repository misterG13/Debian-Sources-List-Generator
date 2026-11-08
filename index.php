<?php

// SPDX-License-Identifier: MPL-2.0

declare(strict_types=1);

require_once __DIR__ . '/lib/generator.php';

// Normalize /index.php (and subdirectory variants) to the directory URL for GET requests,
// so content lives on a single URL. POST is left alone so the form keeps working.
if ($_SERVER['REQUEST_METHOD'] === 'GET'
  && str_ends_with(parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?: '', '/index.php')) {
  $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
  $location = rtrim(dirname($path), '/') . '/';
  if (($_SERVER['QUERY_STRING'] ?? '') !== '') {
    $location .= '?' . $_SERVER['QUERY_STRING'];
  }
  http_response_code(301);
  header('Location: ' . $location);
  exit;
}

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

// --- FAQ content: visible markup and FAQPage schema share this single list ---
$faq = [
  [
    'q' => 'What is the deb822 .sources format?',
    'a' => 'It is the modern, more readable replacement for the classic one-line sources.list: repositories are written as key-value stanzas with fields such as Signed-By, Suites, and Components. Both formats are readable by current versions of APT; the deb822 file lives in /etc/apt/sources.list.d/debian.sources.',
  ],
  [
    'q' => 'How do I switch apt to a regional or local Debian mirror?',
    'a' => 'Pick your country and a mirror above, generate the list, replace your current /etc/apt/sources.list with the output, and run `sudo apt update`. The default deb.debian.org is a geographically distributed CDN, so regional mirrors mainly help with policy and traffic-shaping reasons.',
  ],
  [
    'q' => 'What does signed-by do in an APT source entry?',
    'a' => 'It tells APT which keyring file verifies the repository signatures, e.g. /usr/share/keyrings/debian-archive-keyring.gpg. For the default Debian archive the keyring from the debian-archive-keyring package is picked automatically, so a signed-by value is optional here.',
  ],
  [
    'q' => 'Which components (main, contrib, non-free, non-free-firmware) should I enable?',
    'a' => '`main` is always enabled and holds DFSG-free software. `contrib` is free software that depends on non-free parts, `non-free` is software that is not DFSG-free, and `non-free-firmware` ships device firmware that many machines require. Enable only what you actually want installed.',
  ],
];

// --- Canonical URL for SEO ---
$scheme     = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host       = $_SERVER['HTTP_HOST'] ?? 'localhost';
$scriptPath = parse_url($_SERVER['SCRIPT_NAME'] ?? '/index.php', PHP_URL_PATH) ?: '/index.php';
$basePath   = rtrim(dirname($scriptPath), '/');
// Prefer the directory URL (e.g. "/") over "/index.php" for canonical and OG URLs.
$canonicalPath = str_ends_with($scriptPath, '/index.php') ? $basePath . '/' : $scriptPath;
$canonicalUrl  = $scheme . '://' . $host . $canonicalPath;
$baseUrl       = $scheme . '://' . $host . $basePath . '/';
$ogImage       = $baseUrl . 'assets/og-image.png';
$ogImageSize   = @getimagesize(__DIR__ . '/assets/og-image.png');
?>
<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=5" />
  <title>Debian Sources List Generator — Generate /etc/apt/sources.list</title>
  <meta name="description" content="Generate a ready-to-use Debian sources.list or deb822 .sources file. Choose country, mirror, release, architecture, components, HTTPS, and Tor onion mirrors. Free online tool." />
  <meta name="robots" content="index, follow" />
  <meta name="application-name" content="Debian Sources List Generator" />
  <meta name="theme-color" content="#005f6b" />
  <link rel="canonical" href="<?= htmlspecialchars($canonicalUrl) ?>" />
  <link rel="icon" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 32 32'%3E%3Crect width='32' height='32' rx='6' fill='%23005f6b'/%3E%3Ctext x='16' y='22' font-size='18' text-anchor='middle' fill='white' font-family='sans-serif'%3ED%3C/text%3E%3C/svg%3E" />
  <link rel="preload" href="styles.css" as="style" />
  <link rel="stylesheet" href="styles.css" />

  <!-- Open Graph -->
  <meta property="og:type" content="website" />
  <meta property="og:site_name" content="Debian Sources List Generator" />
  <meta property="og:title" content="Debian Sources List Generator — Generate /etc/apt/sources.list" />
  <meta property="og:description" content="Generate a ready-to-use Debian sources.list or deb822 .sources file. Choose country, mirror, release, architecture, components, HTTPS, and Tor onion mirrors." />
  <meta property="og:url" content="<?= htmlspecialchars($canonicalUrl) ?>" />
  <meta property="og:image" content="<?= htmlspecialchars($ogImage) ?>" />
  <meta property="og:image:type" content="image/png" />
  <meta property="og:image:alt" content="Debian Sources List Generator web interface" />
<?php if ($ogImageSize !== false) : ?>
  <meta property="og:image:width" content="<?= (int) $ogImageSize[0] ?>" />
  <meta property="og:image:height" content="<?= (int) $ogImageSize[1] ?>" />
<?php endif; ?>
  <meta property="og:locale" content="en_US" />

  <!-- Twitter Card -->
  <meta name="twitter:card" content="summary_large_image" />
  <meta name="twitter:title" content="Debian Sources List Generator — Generate /etc/apt/sources.list" />
  <meta name="twitter:description" content="Generate a ready-to-use Debian sources.list or deb822 .sources file. Choose country, mirror, release, architecture, components, HTTPS, and Tor onion mirrors." />
  <meta name="twitter:image" content="<?= htmlspecialchars($ogImage) ?>" />

  <!-- Structured data -->
  <script type="application/ld+json">
  <?= json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'WebApplication',
    'name' => 'Debian Sources List Generator',
    'description' => 'Generate a ready-to-use Debian sources.list or deb822 .sources file. Choose country, mirror, release, architecture, components, HTTPS, and Tor onion mirrors.',
    'url' => $canonicalUrl,
    'applicationCategory' => 'DeveloperApplication',
    'operatingSystem' => 'Linux',
    'inLanguage' => 'en',
    'offers' => [
      '@type' => 'Offer',
      'price' => '0',
      'priceCurrency' => 'USD',
    ],
  ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) ?>
  </script>

  <script type="application/ld+json">
  <?= json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'FAQPage',
    'mainEntity' => array_map(static fn (array $f): array => [
      '@type' => 'Question',
      'name' => $f['q'],
      'acceptedAnswer' => [
        '@type' => 'Answer',
        'text' => str_replace('`', '', $f['a']),
      ],
    ], $faq),
  ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) ?>
  </script>
</head>

<body>
  <main>
    <header class="wrap--header">
      <div class="wrap">
        <h1 class="header">Debian Sources List Generator</h1>
        <p class="tagline">Generate a ready-to-use <code>sources.list</code> or deb822 <code>.sources</code> file for any Debian release, architecture, and mirror.</p>
      </div>
    </header>
    <div class="wrap">
      <section class="intro" aria-labelledby="intro-heading">
        <h2 id="intro-heading">About this tool</h2>
        <p>This free tool builds an official Debian <code>sources.list</code> (one-line) or modern deb822 <code>.sources</code> file. Choose from 65+ countries and their Debian mirrors, any release from Squeeze to Testing, 20+ architectures, and components such as contrib, non-free, non-free-firmware, and security updates. Optionally enable HTTPS, signed-by key paths, and Tor onion mirrors (official Debian onion services). The result includes a ready-to-paste CLI command for <code>sudo tee</code> and <code>apt update</code>.</p>
        <nav class="intro-toc" aria-label="FAQ topics">
          <p>Questions answered below:</p>
          <ul>
            <?php foreach ($faq as $i => $f) : ?>
              <li><a href="#faq-q<?= $i + 1 ?>"><?= htmlspecialchars($f['q']) ?></a></li>
            <?php endforeach; ?>
          </ul>
        </nav>
      </section>
      <form method="POST" action="" aria-label="Generate Debian sources list">
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
            <p class="output-heading">Sources List <code><?= $format === SourceFormat::Deb822 ? '/etc/apt/sources.list.d/debian.sources' : '/etc/apt/sources.list' ?></code></p>
            <pre class="code-block" role="region" aria-label="Sources List output"><code><?= htmlspecialchars($output) ?></code></pre>

            <?php if ($hasOutput) : ?>
              <p class="output-heading">CLI Command</p>
              <pre class="code-block" role="region" aria-label="CLI Command output"><code><?= htmlspecialchars($format === SourceFormat::Deb822
                ? "sudo tee /etc/apt/sources.list.d/debian.sources <<EOF\n# Debian {$release->label()} debian.sources\n" . $output . "\nEOF\n\nsudo apt update"
                : "sudo tee /etc/apt/sources.list.d/debian-{$release->value}-sources.list <<EOF\n# Debian {$release->label()} sources.list\n" . $output . "\nEOF\n\nsudo apt update") ?></code></pre>
            <?php endif; ?>
          </div>
        </div>
      </form>

      <section class="faq" aria-labelledby="faq-heading">
        <h2 id="faq-heading">Frequently asked questions</h2>
        <?php foreach ($faq as $i => $f) : ?>
          <h3 id="faq-q<?= $i + 1 ?>" tabindex="-1"><?= htmlspecialchars($f['q']) ?></h3>
          <p><?= preg_replace('/`([^`]+)`/', '<code>$1</code>', htmlspecialchars($f['a'])) ?></p>
        <?php endforeach; ?>
      </section>
    </div>
    <footer class="wrap--footer wrap">
      <p>Debian Sources List Generator — licensed under the Mozilla Public License 2.0. Source code and API documentation available on <a href="https://github.com/misterG13/Debian-Sources-List-Generator" target="_blank" rel="noopener noreferrer">GitHub</a>.</p>
    </footer>
  </main>
</body>

</html>