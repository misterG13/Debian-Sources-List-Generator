<?php

declare(strict_types=1);

require_once __DIR__ . '/data.php';

final readonly class SourcesListGenerator
{
    private const SECURITY_URL = 'http://deb.debian.org/debian-security';

    public function generate(
        string $mirror,
        Release $release,
        string $arch,
        bool $includeSource,
        bool $contrib,
        bool $nonFree,
        bool $nonFreeFirmware,
        bool $security,
        ?string $signedBy = null,
    ): string {
        $lines = [];
        $archQual = $this->buildArchQualifier($arch, $signedBy);
        $components = $this->buildComponents($contrib, $nonFree, $nonFreeFirmware);

        $this->appendSource($lines, 'deb', $archQual, $mirror, $release->value, $components);
        if ($includeSource) {
            $this->appendSource($lines, 'deb-src', $archQual, $mirror, $release->value, $components);
        }

        if ($release->hasUpdates()) {
            $lines[] = '';
            $this->appendSource($lines, 'deb', $archQual, $mirror, $release->value . '-updates', $components);
            if ($includeSource) {
                $this->appendSource($lines, 'deb-src', $archQual, $mirror, $release->value . '-updates', $components);
            }
        }

        if ($security) {
            $lines[] = '';
            $this->appendSource($lines, 'deb', $archQual, self::SECURITY_URL, $release->value . '-security', $components);
            if ($includeSource) {
                $this->appendSource($lines, 'deb-src', $archQual, self::SECURITY_URL, $release->value . '-security', $components);
            }
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
    private function appendSource(array &$lines, string $type, string $arch, string $url, string $release, string $components): void
    {
        $parts = array_filter([$type, $arch, $url, $release, $components], static fn(string $v) => $v !== '');
        $lines[] = implode(' ', $parts);
    }
}
