<?php

/**
 * Debian sources.list API endpoint
 *
 * Usage:
 *   GET/POST https://your-server/api.php?mirror=Germany&release=bookworm
 *
 * Parameters:
 *   mirror             Country name or full URL (default: CDN)
 *   release            Release: stable, testing, bookworm, trixie, etc. (default: stable)
 *   arch               Architecture: amd64, arm64, etc. (default: none)
 *   src                Set to 1 to include deb-src lines
 *   https              Set to 1 to use HTTPS instead of HTTP
 *   contrib            Set to 0 to exclude contrib
 *   non-free           Set to 0 to exclude non-free
 *   non-free-firmware  Set to 0 to exclude non-free-firmware
 *   security           Set to 0 to exclude security repo
 *   signed-by          Path to signing key
 *
 * Examples:
 *   curl -s "http://your-server/api.php?mirror=Germany&release=bookworm"
 *   curl -s "http://your-server/api.php?mirror=United+States&release=bookworm&https=1" | sudo tee /etc/apt/sources.list
 *   curl -s "http://your-server/api.php?mirror=Germany&release=bookworm&src=1&https=1"
 */

declare(strict_types=1);

// Disable output buffering
while (ob_get_level()) {
  ob_end_clean();
}
ini_set('output_buffering', 'Off');
ini_set('zlib.output_compression', 'Off');

set_time_limit(5);

header_remove('X-Powered-By');
header_remove('Server');

try {
  require_once __DIR__ . '/lib/generator.php';
} catch (\Throwable $e) {
  sendError("Failed to load generator: {$e->getMessage()}");
}

// --- Error output ---
function sendError(string $message): never
{
  http_response_code(400);
  header('Content-Type: text/plain; charset=utf-8');
  header('X-Accel-Buffering: no');
  echo "Error: {$message}\n";
  flush();
  exit(1);
}

// --- Defaults ---
$mirror          = getCdnMirror()->url();
$release         = Release::Stable;
$arch            = '';
$includeSource   = false;
$contrib         = true;
$nonFree         = true;
$nonFreeFirmware = true;
$security        = true;
$signedBy        = null;

// --- Read params (GET or POST) ---
function param(string $key, mixed $default): mixed
{
  if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    return $_POST[$key] ?? $default;
  }
  return $_GET[$key] ?? $default;
}

// --- Resolve mirror ---
$mirrorInput = (string) param('mirror', '');
if ($mirrorInput !== '') {
  if (strlen($mirrorInput) > 200) {
    sendError("Mirror value too long (max 200 characters)");
  }
  if (str_starts_with($mirrorInput, 'http')) {
    if (!filter_var($mirrorInput, FILTER_VALIDATE_URL)) {
      sendError("Invalid mirror URL: {$mirrorInput}");
    }
    $mirror = $mirrorInput;
  } else {
    $resolved = resolveMirror($mirrorInput);
    if ($resolved === null) {
      sendError("Unknown country: {$mirrorInput}");
    }
    $mirror = $resolved;
  }
}

$releaseRaw = (string) param('release', $release->value);
$release    = Release::tryFrom($releaseRaw);
if ($release === null) {
  sendError("Invalid release: {$releaseRaw}");
}

$arch          = (string) param('arch', $arch);
if (strlen($arch) > 20) {
  sendError("Arch value too long (max 20 characters)");
}
$includeSource = param('src', '0') === '1';
$contrib       = param('contrib', '1') !== '0';
$nonFree       = param('non-free', '1') !== '0';
$nonFreeFirmware = param('non-free-firmware', '1') !== '0';
$security      = param('security', '1') !== '0';
$signedByRaw   = (string) param('signed-by', '');
if (strlen($signedByRaw) > 200) {
  sendError("Signed-by value too long (max 200 characters)");
}
$signedBy      = $signedByRaw !== '' ? $signedByRaw : null;
$useHttps       = param('https', '0') === '1';

// --- Generate ---
try {
  $generator = new SourcesListGenerator();
  $output    = $generator->generate(
    mirror: $mirror,
    release: $release,
    arch: $arch,
    includeSource: $includeSource,
    contrib: $contrib,
    nonFree: $nonFree,
    nonFreeFirmware: $nonFreeFirmware,
    security: $security,
    signedBy: $signedBy,
  );
} catch (\Throwable $e) {
  sendError("Generation failed: {$e->getMessage()}");
}

if ($useHttps) {
  $output = str_replace('http://', 'https://', $output);
}

// --- Output ---
header('Content-Type: text/plain; charset=utf-8');
header('X-Accel-Buffering: no');
header('Cache-Control: no-cache');
header('Connection: close');
header('Content-Length: ' . strlen($output . "\n"));
echo $output . "\n";
flush();
if (function_exists('fastcgi_finish_request')) {
  fastcgi_finish_request();
}
exit(0);

// --- Resolve mirror from country name or URL ---
function resolveMirror(string $input): ?string
{
  $input = trim($input);

  $countries = getCountries();
  $lower = strtolower($input);

  foreach ($countries as $country) {
    if (strtolower($country->name) === $lower) {
      $firstMirror = $country->mirrors[0] ?? null;
      if ($firstMirror !== null) {
        return $firstMirror->url();
      }
    }
  }

  return null;
}
