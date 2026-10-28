<?php

// SPDX-License-Identifier: MPL-2.0

declare(strict_types=1);

require_once __DIR__ . '/data.php';

final readonly class SourcesListGenerator
{
  private const SECURITY_URL       = 'http://deb.debian.org/debian-security';
  private const TOR_ARCHIVE_URL    = 'tor+http://2s4yqjx5ul6okpp3f2gaunr2syex5jgbfpfvhxxbbjwnrsvbk5v3qbid.onion/debian';
  private const TOR_SECURITY_URL   = 'tor+http://5ajw6aqf3ep7sijnscdzw77t7xq4xjpsy335yb2wiwgouo7yfxtjlmid.onion/debian-security';
  private const TOR_COMMENT        = "# Tor source URLs require the tor and apt-transport-tor packages.\n"
    . "# Install them before using these entries:\n"
    . '#   sudo apt install tor apt-transport-tor';

  public function generate(
    string $mirror,
    Release $release,
    string $arch,
    bool $includeSources,
    bool $contrib,
    bool $nonFree,
    bool $nonFreeFirmware,
    bool $security,
    ?string $signedBy = null,
    SourceFormat $format = SourceFormat::OneLine,
    bool $tor = false,
  ): string {
    $components  = $this->buildComponents($contrib, $nonFree, $nonFreeFirmware);
    $archiveUrl  = $tor ? self::TOR_ARCHIVE_URL : $mirror;
    $securityUrl = $tor ? self::TOR_SECURITY_URL : self::SECURITY_URL;

    if ($format === SourceFormat::Deb822) {
      $output = $this->generateDeb822($archiveUrl, $securityUrl, $release, $arch, $includeSources, $components, $security, $signedBy);
    } else {
      $lines = [];
      $archQual = $this->buildArchQualifier($arch, $signedBy);

      $this->appendSources($lines, 'deb', $archQual, $archiveUrl, $release->value, $components);
      if ($includeSources) {
        $this->appendSources($lines, 'deb-src', $archQual, $archiveUrl, $release->value, $components);
      }

      if ($release->hasUpdates()) {
        $lines[] = '';
        $this->appendSources($lines, 'deb', $archQual, $archiveUrl, $release->value . '-updates', $components);
        if ($includeSources) {
          $this->appendSources($lines, 'deb-src', $archQual, $archiveUrl, $release->value . '-updates', $components);
        }
      }

      if ($security) {
        $lines[] = '';
        $this->appendSources($lines, 'deb', $archQual, $securityUrl, $release->value . '-security', $components);
        if ($includeSources) {
          $this->appendSources($lines, 'deb-src', $archQual, $securityUrl, $release->value . '-security', $components);
        }
      }

      $output = implode("\n", $lines);
    }

    return $tor ? self::TOR_COMMENT . "\n\n" . $output : $output;
  }

  private function generateDeb822(
    string $mirror,
    string $securityUrl,
    Release $release,
    string $arch,
    bool $includeSources,
    string $components,
    bool $security,
    ?string $signedBy,
  ): string {
    $types = $includeSources ? 'deb deb-src' : 'deb';

    $suites = [$release->value];
    if ($release->hasUpdates()) {
      $suites[] = $release->value . '-updates';
    }

    $stanzas = [$this->buildStanza($types, $mirror, $suites, $components, $arch, $signedBy)];

    if ($security) {
      $stanzas[] = $this->buildStanza(
        $types,
        $securityUrl,
        [$release->value . '-security'],
        $components,
        $arch,
        $signedBy,
      );
    }

    return implode("\n\n", $stanzas);
  }

  /**
   * @param list<string> $suites
   */
  private function buildStanza(string $types, string $uri, array $suites, string $components, string $arch, ?string $signedBy): string
  {
    $lines = [
      "Types: {$types}",
      "URIs: {$uri}",
      'Suites: ' . implode(' ', $suites),
      "Components: {$components}",
    ];

    if ($arch !== '') {
      $lines[] = "Architectures: {$arch}";
    }
    if ($signedBy !== null && $signedBy !== '') {
      $lines[] = "Signed-By: {$signedBy}";
    }

    return implode("\n", $lines);
  }

  private function buildArchQualifier(string $arch, ?string $signedBy): string
  {
    $parts = [];

    if ($arch !== '') {
      $parts[] = "arch={$arch}";
    }
    if ($signedBy !== null && $signedBy !== '') {
      $parts[] = "signed-by={$signedBy}";
    }

    if ($parts === []) {
      return '';
    }

    return '[' . implode(' ', $parts) . ']';
  }

  private function buildComponents(bool $contrib, bool $nonFree, bool $nonFreeFirmware): string
  {
    $components = ['main'];

    if ($contrib) {
      $components[] = 'contrib';
    }
    if ($nonFree) {
      $components[] = 'non-free';
    }
    if ($nonFreeFirmware) {
      $components[] = 'non-free-firmware';
    }

    return implode(' ', $components);
  }

  /** @param list<string> $lines */
  private function appendSources(array &$lines, string $type, string $arch, string $url, string $release, string $components): void
  {
    $parts = array_filter([$type, $arch, $url, $release, $components], static fn (string $v) => $v !== '');
    $lines[] = implode(' ', $parts);
  }
}
