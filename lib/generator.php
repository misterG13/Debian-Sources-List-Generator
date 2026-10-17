<?php

// SPDX-License-Identifier: MPL-2.0

declare(strict_types=1);

require_once __DIR__ . '/data.php';

final readonly class SourcesListGenerator
{
  private const SECURITY_URL = 'http://deb.debian.org/debian-security';

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
  ): string {
    $components = $this->buildComponents($contrib, $nonFree, $nonFreeFirmware);

    if ($format === SourceFormat::Deb822) {
      return $this->generateDeb822($mirror, $release, $arch, $includeSources, $components, $security, $signedBy);
    }

    $lines = [];
    $archQual = $this->buildArchQualifier($arch, $signedBy);

    $this->appendSources($lines, 'deb', $archQual, $mirror, $release->value, $components);
    if ($includeSources) {
      $this->appendSources($lines, 'deb-src', $archQual, $mirror, $release->value, $components);
    }

    if ($release->hasUpdates()) {
      $lines[] = '';
      $this->appendSources($lines, 'deb', $archQual, $mirror, $release->value . '-updates', $components);
      if ($includeSources) {
        $this->appendSources($lines, 'deb-src', $archQual, $mirror, $release->value . '-updates', $components);
      }
    }

    if ($security) {
      $lines[] = '';
      $this->appendSources($lines, 'deb', $archQual, self::SECURITY_URL, $release->value . '-security', $components);
      if ($includeSources) {
        $this->appendSources($lines, 'deb-src', $archQual, self::SECURITY_URL, $release->value . '-security', $components);
      }
    }

    return implode("\n", $lines);
  }

  private function generateDeb822(
    string $mirror,
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
        self::SECURITY_URL,
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
