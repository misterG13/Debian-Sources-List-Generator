<?php

declare(strict_types=1);

// ─── Release Enum ────────────────────────────────────────────────────────────

enum Release: string
{
  case Stable    = 'stable';
  case Testing   = 'testing';
  case Trixie    = 'trixie';
  case Bookworm  = 'bookworm';
  case Bullseye  = 'bullseye';
  case Buster    = 'buster';
  case Stretch   = 'stretch';
  case Jessie    = 'jessie';
  case Wheezy    = 'wheezy';
  case SqueezeLts = 'squeeze-lts';

  public function label(): string
  {
    return match ($this) {
      self::Stable     => 'Stable',
      self::Testing    => 'Testing',
      self::Bookworm   => 'Debian 12 (bookworm)',
      self::Trixie     => 'Debian 13 (trixie)',
      self::Bullseye   => 'Debian 11 (bullseye)',
      self::Buster     => 'Debian 10 (buster)',
      self::Stretch    => 'Debian 9 (stretch)',
      self::Jessie     => 'Debian 8 (jessie)',
      self::Wheezy     => 'Debian 7 (wheezy)',
      self::SqueezeLts => 'Debian 6.0 (squeeze)',
    };
  }

  public function hasUpdates(): bool
  {
    return $this !== self::SqueezeLts;
  }

  /** non-free-firmware was split from non-free starting with Debian 12 (bookworm) */
  public function hasNonFreeFirmware(): bool
  {
    return in_array($this, [
      self::Stable,
      self::Testing,
      self::Bookworm,
      self::Trixie,
    ], true);
  }
}

// ─── Mirror Class ────────────────────────────────────────────────────────────

final readonly class Mirror
{
  /** @param list<string> $architectures */
  public function __construct(
    public string $hostname,
    public string $path,
    public array  $architectures = [],
  ) {
  }

  public function url(): string
  {
    return "http://{$this->hostname}{$this->path}";
  }

  public function label(): string
  {
    return "{$this->hostname}  {$this->path}";
  }
}

// ─── Country Class ───────────────────────────────────────────────────────────

final readonly class Country
{
  /** @param list<Mirror> $mirrors */
  public function __construct(
    public string $name,
    public string $code,
    public array  $mirrors,
  ) {
  }
}

// ─── CDN (special entry) ─────────────────────────────────────────────────────

function getCdnMirror(): Mirror
{
  return new Mirror('deb.debian.org', '/debian/', [
    'amd64', 'arm64', 'armel', 'armhf', 'i386', 'mips64el', 'mipsel',
    'ppc64el', 'riscv64', 's390x',
  ]);
}

// ─── All Countries & Mirrors ─────────────────────────────────────────────────

/** @return list<Country> */
function getCountries(): array
{
  return [
    new Country('Argentina', 'ar', [
      new Mirror('debian.unnoba.edu.ar', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'loong64', 'mips64el', 'mipsel', 'riscv64']),
      new Mirror('mirror.sitsa.com.ar', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'mips64el', 'mipsel', 'ppc64el', 's390x']),
    ]),
    new Country('Australia', 'au', [
      new Mirror('ftp.au.debian.org', '/debian/', ['amd64', 'armel', 'armhf', 'hurd-i386', 'i386', 'ia64', 'kfreebsd-amd64', 'kfreebsd-i386', 'mips', 'mipsel', 'powerpc', 's390', 's390x']),
      new Mirror('debian.mirror.digitalpacific.com.au', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'loong64', 'mips64el', 'mipsel', 'ppc64el', 'riscv64', 's390x']),
      new Mirror('debian.mirror.serversaustralia.com.au', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'loong64', 'mips64el', 'mipsel', 'ppc64el', 'riscv64', 's390x']),
      new Mirror('mirror.aarnet.edu.au', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'mips64el', 'mipsel', 'ppc64el', 's390x']),
      new Mirror('mirror.amaze.com.au', '/debian/', ['amd64', 'i386']),
      new Mirror('mirror.gsl.icu', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'mips64el', 'mipsel', 'ppc64el', 's390x']),
      new Mirror('mirror.linux.org.au', '/debian/', ['amd64', 'armel', 'armhf', 'hurd-i386', 'i386', 'ia64', 'kfreebsd-amd64', 'kfreebsd-i386', 'mips', 'mipsel', 'powerpc', 's390', 's390x']),
      new Mirror('mirror.overthewire.com.au', '/debian/', ['amd64', 'arm64', 'armel', 'i386']),
      new Mirror('mirror.realcompute.io', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'loong64', 'mips64el', 'mipsel', 'ppc64el', 'riscv64', 's390x']),
      new Mirror('mirrors.xtom.au', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'loong64', 'mips64el', 'mipsel', 'ppc64el', 'riscv64', 's390x']),
    ]),
    new Country('Austria', 'at', [
      new Mirror('ftp.at.debian.org', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'loong64', 'mips64el', 'mipsel', 'ppc64el', 'riscv64', 's390x']),
      new Mirror('debian.anexia.at', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'loong64', 'mips64el', 'mipsel', 'ppc64el', 'riscv64', 's390x']),
      new Mirror('debian.lagis.at', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'hurd-i386', 'i386', 'ia64', 'kfreebsd-amd64', 'kfreebsd-i386', 'mips', 'mips64el', 'mipsel', 'powerpc', 'ppc64el', 's390', 's390x']),
      new Mirror('ftp.tugraz.at', '/mirror/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'loong64', 'mips64el', 'mipsel', 'ppc64el', 'riscv64', 's390x']),
      new Mirror('mirror.alwyzon.net', '/debian/', ['amd64', 'i386']),
    ]),
    new Country('Azerbaijan', 'az', [
      new Mirror('mirror.ourhost.az', '/debian/', ['amd64', 'i386']),
    ]),
    new Country('Bangladesh', 'bd', [
      new Mirror('mirror.xeonbd.com', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'loong64', 'mips64el', 'mipsel', 'ppc64el', 'riscv64', 's390x']),
    ]),
    new Country('Belarus', 'by', [
      new Mirror('ftp.by.debian.org', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'loong64', 'mips64el', 'mipsel', 'ppc64el', 'riscv64', 's390x']),
      new Mirror('ftp.byfly.by', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'loong64', 'mips64el', 'mipsel', 'ppc64el', 'riscv64', 's390x']),
      new Mirror('mirror.datacenter.by', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'loong64', 'mips64el', 'mipsel', 'ppc64el', 'riscv64', 's390x']),
    ]),
    new Country('Belgium', 'be', [
      new Mirror('ftp.be.debian.org', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'loong64', 'mips64el', 'mipsel', 'ppc64el', 'riscv64', 's390x']),
      new Mirror('debian-mirror.behostings.net', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'loong64', 'mips64el', 'mipsel', 'ppc64el', 'riscv64', 's390x']),
      new Mirror('mirror.as35701.net', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'loong64', 'mips64el', 'mipsel', 'ppc64el', 'riscv64', 's390x']),
      new Mirror('mirror.unix-solutions.be', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'loong64', 'mips64el', 'mipsel', 'ppc64el', 'riscv64', 's390x']),
    ]),
    new Country('Brazil', 'br', [
      new Mirror('ftp.br.debian.org', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'hurd-i386', 'i386', 'ia64', 'kfreebsd-amd64', 'kfreebsd-i386', 'mips', 'mips64el', 'mipsel', 'powerpc', 'ppc64el', 's390', 's390x', 'sparc']),
      new Mirror('debian.c3sl.ufpr.br', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'hurd-i386', 'i386', 'ia64', 'kfreebsd-amd64', 'kfreebsd-i386', 'mips', 'mips64el', 'mipsel', 'powerpc', 'ppc64el', 's390', 's390x', 'sparc']),
      new Mirror('debian.pop-sc.rnp.br', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'loong64', 'mips64el', 'mipsel', 'ppc64el', 'riscv64', 's390x']),
      new Mirror('mirror.blue3.com.br', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'loong64', 'mips64el', 'mipsel', 'ppc64el', 'riscv64', 's390x']),
      new Mirror('mirrors.ic.unicamp.br', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'loong64', 'mips64el', 'mipsel', 'ppc64el', 'riscv64', 's390x']),
      new Mirror('mirror.uepg.br', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'loong64', 'mips64el', 'mipsel', 'ppc64el', 'riscv64', 's390x']),
      new Mirror('mirror.ufscar.br', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'loong64', 'mips64el', 'mipsel', 'ppc64el', 'riscv64', 's390x']),
    ]),
    new Country('Bulgaria', 'bg', [
      new Mirror('ftp.bg.debian.org', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'loong64', 'mips64el', 'mipsel', 'ppc64el', 'riscv64', 's390x']),
      new Mirror('debian.a1.bg', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'loong64', 'mips64el', 'mipsel', 'ppc64el', 'riscv64', 's390x']),
      new Mirror('debian.ipacct.com', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'loong64', 'mips64el', 'mipsel', 'ppc64el', 'riscv64', 's390x']),
      new Mirror('debian.ludost.net', '/debian/', ['amd64']),
      new Mirror('debian.mnet.bg', '/debian/', ['amd64', 'arm64', 'i386', 'mips64el']),
      new Mirror('debian.telecoms.bg', '/debian/', ['amd64', 'arm64', 'armhf']),
      new Mirror('ftp.uni-sofia.bg', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'loong64', 'mips64el', 'mipsel', 'ppc64el', 'riscv64', 's390x']),
      new Mirror('mirrors.netix.net', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'loong64', 'mips64el', 'mipsel', 'ppc64el', 'riscv64', 's390x']),
      new Mirror('mirror.telepoint.bg', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'loong64', 'mips64el', 'mipsel', 'ppc64el', 'riscv64', 's390x']),
    ]),
    new Country('Canada', 'ca', [
      new Mirror('ftp.ca.debian.org', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'loong64', 'mips64el', 'mipsel', 'ppc64el', 'riscv64', 's390x']),
      new Mirror('debian.mirror.globo.tech', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'loong64', 'mips64el', 'mipsel', 'ppc64el', 'riscv64', 's390x']),
      new Mirror('debian.mirror.rafal.ca', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'loong64', 'mips64el', 'mipsel', 'ppc64el', 'riscv64', 's390x']),
      new Mirror('mirror.cpsc.ucalgary.ca', '/debian/', ['amd64', 'arm64', 'armhf', 'i386']),
      new Mirror('mirror.csclub.uwaterloo.ca', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'loong64', 'mips64el', 'mipsel', 'ppc64el', 'riscv64', 's390x']),
      new Mirror('mirror.dst.ca', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'loong64', 'mips64el', 'mipsel', 'ppc64el', 'riscv64', 's390x']),
      new Mirror('mirror.estone.ca', '/debian/', ['amd64', 'i386']),
      new Mirror('mirror.it.ubc.ca', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'loong64', 'mips64el', 'mipsel', 'ppc64el', 'riscv64', 's390x']),
    ]),
    new Country('Chile', 'cl', [
      new Mirror('ftp.cl.debian.org', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'loong64', 'mips64el', 'mipsel', 'ppc64el', 'riscv64', 's390x']),
      new Mirror('debian-mirror.puq.apoapsis.cl', '/debian/', ['amd64', 'arm64', 'armhf', 'i386']),
      new Mirror('elmirror.cl', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'loong64', 'mips64el', 'mipsel', 'ppc64el', 'riscv64', 's390x']),
      new Mirror('mirror.hnd.cl', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'mips64el', 'mipsel', 'ppc64el', 'riscv64', 's390x']),
      new Mirror('mirror.insacom.cl', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'loong64', 'mips64el', 'mipsel', 'ppc64el', 'riscv64', 's390x']),
    ]),
    new Country('China', 'cn', [
      new Mirror('ftp.cn.debian.org', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'loong64', 'mips64el', 'mipsel', 'ppc64el', 'riscv64', 's390x']),
      new Mirror('mirror.lzu.edu.cn', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'loong64', 'mips64el', 'mipsel', 'ppc64el', 'riscv64', 's390x']),
      new Mirror('mirror.nju.edu.cn', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'loong64', 'mips64el', 'mipsel', 'ppc64el', 'riscv64', 's390x']),
      new Mirror('mirror.nyist.edu.cn', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'loong64', 'mips64el', 'mipsel', 'ppc64el', 'riscv64', 's390x']),
      new Mirror('mirrors.bfsu.edu.cn', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'loong64', 'mips64el', 'mipsel', 'ppc64el', 'riscv64', 's390x']),
      new Mirror('mirrors.hit.edu.cn', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'loong64', 'mips64el', 'mipsel', 'ppc64el', 'riscv64', 's390x']),
      new Mirror('mirrors.jlu.edu.cn', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'loong64', 'mips64el', 'mipsel', 'ppc64el', 'riscv64', 's390x']),
      new Mirror('mirrors.qlu.edu.cn', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'loong64', 'mips64el', 'mipsel', 'ppc64el', 'riscv64', 's390x']),
      new Mirror('mirrors.tuna.tsinghua.edu.cn', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'loong64', 'mips64el', 'mipsel', 'ppc64el', 'riscv64', 's390x']),
      new Mirror('mirrors.ustc.edu.cn', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'loong64', 'mips64el', 'mipsel', 'ppc64el', 'riscv64', 's390x']),
      new Mirror('mirrors.zju.edu.cn', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'riscv64']),
    ]),
    new Country('Costa Rica', 'cr', [
      new Mirror('debianmirror.una.ac.cr', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'loong64', 'mips64el', 'mipsel', 'ppc64el', 'riscv64', 's390x']),
      new Mirror('mirrors.ucr.ac.cr', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'loong64', 'mips64el', 'mipsel', 'ppc64el', 'riscv64', 's390x']),
    ]),
    new Country('Croatia', 'hr', [
      new Mirror('ftp.hr.debian.org', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'loong64', 'mips64el', 'mipsel', 'ppc64el', 'riscv64', 's390x']),
      new Mirror('debian.carnet.hr', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'loong64', 'mips64el', 'mipsel', 'ppc64el', 'riscv64', 's390x']),
      new Mirror('debian.iskon.hr', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'loong64', 'mips64el', 'mipsel', 'ppc64el', 'riscv64', 's390x']),
    ]),
    new Country('Czech Republic', 'cz', [
      new Mirror('ftp.cz.debian.org', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'loong64', 'mips64el', 'mipsel', 'ppc64el', 'riscv64', 's390x']),
      new Mirror('debian.mirror.web4u.cz', '/', ['amd64', 'arm64', 'armel', 'armhf', 'hurd-i386', 'i386', 'ia64', 'kfreebsd-amd64', 'kfreebsd-i386', 'mips', 'mips64el', 'mipsel', 'powerpc', 'ppc64el', 's390', 's390x']),
      new Mirror('debian.nic.cz', '/debian/', ['amd64', 'arm64']),
      new Mirror('debian.superhosting.cz', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'hurd-i386', 'i386', 'ia64', 'kfreebsd-amd64', 'kfreebsd-i386', 'mips', 'mips64el', 'mipsel', 'powerpc', 'ppc64el', 's390', 's390x']),
      new Mirror('ftp.cvut.cz', '/debian/', ['amd64', 'arm64', 'armhf', 'i386', 'loong64', 'mips64el', 'ppc64el', 'riscv64']),
      new Mirror('ftp.debian.cz', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'loong64', 'mips64el', 'mipsel', 'ppc64el', 'riscv64', 's390x']),
      new Mirror('ftp.sh.cvut.cz', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'loong64', 'mips64el', 'mipsel', 'ppc64el', 'riscv64', 's390x']),
      new Mirror('ftp.zcu.cz', '/debian/', ['amd64', 'arm64', 'armhf', 'i386', 'loong64', 'mips64el', 'riscv64']),
      new Mirror('merlin.fit.vutbr.cz', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'loong64', 'mips64el', 'riscv64']),
      new Mirror('mirror.dkm.cz', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'loong64', 'mips64el', 'mipsel', 'ppc64el', 'riscv64', 's390x']),
      new Mirror('mirror.it4i.cz', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'loong64', 'mips64el', 'mipsel', 'ppc64el', 'riscv64', 's390x']),
      new Mirror('mirror-prg.webglobe.com', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'loong64', 'mips64el', 'mipsel', 'ppc64el', 'riscv64', 's390x']),
    ]),
    new Country('Denmark', 'dk', [
      new Mirror('ftp.dk.debian.org', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'loong64', 'mips64el', 'mipsel', 'ppc64el', 'riscv64', 's390x']),
      new Mirror('mirrors.dotsrc.org', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'loong64', 'mips64el', 'mipsel', 'ppc64el', 'riscv64', 's390x']),
      new Mirror('mirrors.rackhosting.com', '/debian/', ['amd64', 'armhf', 'i386', 'ia64', 'kfreebsd-amd64', 'kfreebsd-i386', 's390x']),
    ]),
    new Country('Estonia', 'ee', [
      new Mirror('ftp.ee.debian.org', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'loong64', 'mips64el', 'mipsel', 'ppc64el', 'riscv64', 's390x']),
      new Mirror('ftp.eenet.ee', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'loong64', 'mips64el', 'mipsel', 'ppc64el', 'riscv64', 's390x']),
      new Mirror('mirrors.xtom.ee', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'loong64', 'mips64el', 'mipsel', 'ppc64el', 'riscv64', 's390x']),
    ]),
    new Country('Finland', 'fi', [
      new Mirror('debian.web.trex.fi', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'loong64', 'mips64el', 'mipsel', 'ppc64el', 'riscv64', 's390x']),
      new Mirror('mirror.5i.fi', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'loong64', 'mips64el', 'mipsel', 'ppc64el', 'riscv64', 's390x']),
      new Mirror('www.nic.funet.fi', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'loong64', 'mips64el', 'mipsel', 'ppc64el', 'riscv64', 's390x']),
    ]),
    new Country('France', 'fr', [
      new Mirror('ftp.fr.debian.org', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'loong64', 'mips64el', 'mipsel', 'ppc64el', 'riscv64', 's390x']),
      new Mirror('apt.tetaneutral.net', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'loong64', 'riscv64']),
      new Mirror('debian.apt-mirror.de', '/debian/', ['amd64', 'armhf', 'i386']),
      new Mirror('debian.mirrors.ovh.net', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'loong64', 'mips64el', 'mipsel', 'ppc64el', 'riscv64', 's390x']),
      new Mirror('debian.obspm.fr', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'loong64', 'mips64el', 'mipsel', 'ppc64el', 'riscv64', 's390x']),
      new Mirror('debian.polytech-lille.fr', '/debian/', ['amd64', 'arm64', 'i386', 'loong64', 'mips64el', 'ppc64el', 'riscv64', 's390x']),
      new Mirror('debian.proxad.net', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'loong64', 'mips64el', 'mipsel', 'ppc64el', 'riscv64', 's390x']),
      new Mirror('deb-mir1.naitways.net', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'loong64', 'mips64el', 'mipsel', 'ppc64el', 'riscv64', 's390x']),
      new Mirror('ftp.ec-m.fr', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'loong64', 'mips64el', 'mipsel', 'ppc64el', 'riscv64', 's390x']),
      new Mirror('ftp.lip6.fr', '/pub/linux/distributions/debian/', ['amd64', 'armel', 'armhf', 'i386', 's390x']),
      new Mirror('ftp.rezopole.net', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'loong64', 'mips64el', 'mipsel', 'ppc64el', 'riscv64', 's390x']),
      new Mirror('ftp.univ-pau.fr', '/linux/mirrors/debian/', ['amd64', 'arm64', 'armhf', 'i386', 'loong64', 'mips64el', 'ppc64el', 'riscv64']),
      new Mirror('ftp.u-picardie.fr', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'loong64', 'mips64el', 'mipsel', 'ppc64el', 'riscv64', 's390x']),
      new Mirror('ftp.u-strasbg.fr', '/debian/', ['amd64', 'i386', 'loong64', 'riscv64']),
      new Mirror('miroir.univ-lorraine.fr', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'loong64', 'mips64el', 'mipsel', 'ppc64el', 'riscv64', 's390x']),
      new Mirror('mirror.abaclouda.net', '/debian/', ['amd64']),
      new Mirror('mirror.debian.ikoula.com', '/debian/', ['amd64', 'arm64', 'i386']),
      new Mirror('mirror.gitoyen.net', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'loong64', 'mips64el', 'mipsel', 'ppc64el', 'riscv64', 's390x']),
      new Mirror('mirror.johnnybegood.fr', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'loong64', 'mips64el', 'mipsel', 'ppc64el', 'riscv64', 's390x']),
      new Mirror('mirror.plusserver.com', '/debian/debian/', ['amd64', 'armel', 'armhf', 'i386', 'mipsel', 's390x']),
    ]),
    new Country('Germany', 'de', [
      new Mirror('ftp2.de.debian.org', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'loong64', 'mips64el', 'mipsel', 'ppc64el', 'riscv64', 's390x']),
      new Mirror('ftp.de.debian.org', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'loong64', 'mips64el', 'mipsel', 'ppc64el', 'riscv64', 's390x']),
      new Mirror('debian.charite.de', '/debian/', ['amd64', 'i386', 'loong64', 'riscv64']),
      new Mirror('debian.inf.tu-dresden.de', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'loong64', 'mips64el', 'mipsel', 'ppc64el', 'riscv64', 's390x']),
      new Mirror('debian.mirror.iphh.net', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'loong64', 'mips64el', 'mipsel', 'ppc64el', 'riscv64', 's390x']),
      new Mirror('debian.mirror.lrz.de', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'loong64', 'mips64el', 'mipsel', 'ppc64el', 'riscv64', 's390x']),
      new Mirror('debian.netcologne.de', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'loong64', 'mips64el', 'mipsel', 'ppc64el', 'riscv64', 's390x']),
      new Mirror('debian.tu-bs.de', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'loong64', 'mips64el', 'mipsel', 'ppc64el', 'riscv64', 's390x']),
      new Mirror('debian.uni-due.de', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'loong64', 'mips64el', 'mipsel', 'ppc64el', 'riscv64', 's390x']),
      new Mirror('de.mirrors.clouvider.net', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'loong64', 'mips64el', 'mipsel', 'ppc64el', 'riscv64', 's390x']),
      new Mirror('ftp.fau.de', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'loong64', 'mips64el', 'mipsel', 'ppc64el', 'riscv64', 's390x']),
      new Mirror('ftp.gwdg.de', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'loong64', 'mips64el', 'mipsel', 'ppc64el', 'riscv64', 's390x']),
      new Mirror('ftp.halifax.rwth-aachen.de', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'loong64', 'mips64el', 'mipsel', 'ppc64el', 'riscv64', 's390x']),
      new Mirror('ftp.hosteurope.de', '/mirror/ftp.debian.org/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'loong64', 'mips64el', 'mipsel', 'ppc64el', 'riscv64', 's390x']),
      new Mirror('ftp-stud.hs-esslingen.de', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'hurd-i386', 'i386', 'ia64', 'kfreebsd-amd64', 'kfreebsd-i386', 'mips', 'mipsel', 'powerpc', 'ppc64el', 's390', 's390x']),
      new Mirror('ftp.stw-bonn.de', '/debian/', ['amd64', 'armel', 'armhf', 'hurd-i386', 'i386', 'ia64', 'kfreebsd-amd64', 'kfreebsd-i386', 'mips', 'mipsel', 'powerpc', 's390', 's390x']),
      new Mirror('ftp.tu-chemnitz.de', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'loong64', 'mips64el', 'mipsel', 'ppc64el', 'riscv64', 's390x']),
      new Mirror('ftp.uni-hannover.de', '/debian/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'loong64', 'mips64el', 'mipsel', 'ppc64el', 'riscv64', 's390x']),
      new Mirror('ftp.uni-kl.de', '/debian/', ['amd64', 'arm64', 'i386']),
      new Mirror('ftp.uni-mainz.de', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'loong64', 'mips64el', 'mipsel', 'ppc64el', 'riscv64', 's390x']),
      new Mirror('ftp.uni-stuttgart.de', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'loong64', 'mips64el', 'mipsel', 'ppc64el', 'riscv64', 's390x']),
      new Mirror('ftp.wrz.de', '/debian/', ['amd64', 'arm64', 'armhf', 'i386']),
      new Mirror('mirror.23m.com', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'loong64', 'mips64el', 'mipsel', 'ppc64el', 'riscv64', 's390x']),
      new Mirror('mirror.creoline.net', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'loong64', 'mips64el', 'mipsel', 'ppc64el', 'riscv64', 's390x']),
      new Mirror('mirror.de.leaseweb.net', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'mips', 'mips64el', 'mipsel', 'powerpc', 'ppc64el', 's390x']),
      new Mirror('mirror.dogado.de', '/debian/', ['amd64', 'arm64', 'i386']),
      new Mirror('mirror.eu.oneandone.net', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'loong64', 'mips64el', 'mipsel', 'ppc64el', 'riscv64', 's390x']),
      new Mirror('mirror.informatik.tu-freiberg.de', '/debian/', ['amd64', 'arm64', 'armhf', 'i386', 'loong64', 'mips64el', 'ppc64el', 'riscv64']),
      new Mirror('mirror.ipb.de', '/debian/', ['amd64', 'i386']),
      new Mirror('mirror.mk.de', '/debian/', ['amd64', 'arm64', 'i386']),
      new Mirror('mirror.netzwerge.de', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'loong64', 'mips64el', 'mipsel', 'ppc64el', 'riscv64', 's390x']),
      new Mirror('mirror.plusline.net', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'loong64', 'mips64el', 'mipsel', 'ppc64el', 'riscv64', 's390x']),
      new Mirror('mirrors.xtom.de', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'loong64', 'mips64el', 'mipsel', 'ppc64el', 'riscv64', 's390x']),
      new Mirror('mirror.united-gameserver.de', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'loong64', 'mips64el', 'mipsel', 'ppc64el', 'riscv64', 's390x']),
      new Mirror('mirror.wtnet.de', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'loong64', 'mips64el', 'mipsel', 'ppc64el', 'riscv64', 's390x']),
      new Mirror('packages.hs-regensburg.de', '/debian/', ['amd64']),
      new Mirror('pubmirror.plutex.de', '/debian/', ['amd64', 'arm64', 'armhf', 'i386']),
    ]),
    new Country('Greece', 'gr', [
      new Mirror('debian.otenet.gr', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'loong64', 'mips64el', 'mipsel', 'ppc64el', 'riscv64', 's390x']),
    ]),
    new Country('Hong Kong', 'hk', [
      new Mirror('ftp.hk.debian.org', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'loong64', 'mips64el', 'mipsel', 'ppc64el', 'riscv64', 's390x']),
      new Mirror('mirror.xtom.com.hk', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'loong64', 'mips64el', 'mipsel', 'ppc64el', 'riscv64', 's390x']),
    ]),
    new Country('Hungary', 'hu', [
      new Mirror('ftp.bme.hu', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'loong64', 'mips64el', 'mipsel', 'ppc64el', 'riscv64', 's390x']),
      new Mirror('repo.jztkft.hu', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'loong64', 'mips64el', 'mipsel', 'ppc64el', 'riscv64', 's390x']),
    ]),
    new Country('Iceland', 'is', [
      new Mirror('ftp.is.debian.org', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'loong64', 'mips64el', 'mipsel', 'ppc64el', 'riscv64', 's390x']),
    ]),
    new Country('Indonesia', 'id', [
      new Mirror('kartolo.sby.datautama.net.id', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'mips64el', 'mipsel', 'ppc64el']),
      new Mirror('mirror.unair.ac.id', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'loong64', 'mips64el', 'mipsel', 'ppc64el', 'riscv64', 's390x']),
      new Mirror('mr.heru.id', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'loong64', 'mips64el', 'mipsel', 'ppc64el', 'riscv64', 's390x']),
      new Mirror('werog.interkoneksimedia.co.id', '/debian/', ['amd64', 'arm64']),
    ]),
    new Country('Iran', 'ir', [
      new Mirror('archive.debian.petiak.ir', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'loong64', 'mips64el', 'mipsel', 'ppc64el', 'riscv64', 's390x']),
    ]),
    new Country('Israel', 'il', [
      new Mirror('debian.interhost.co.il', '/debian/', ['amd64', 'arm64', 'i386', 'loong64', 'mips64el', 'riscv64']),
    ]),
    new Country('Italy', 'it', [
      new Mirror('ftp.it.debian.org', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'loong64', 'mips64el', 'mipsel', 'ppc64el', 'riscv64', 's390x']),
      new Mirror('debian.connesi.it', '/debian/', ['amd64', 'i386']),
      new Mirror('debian.dynamica.it', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'loong64', 'mips64el', 'ppc64el', 'riscv64', 's390x']),
      new Mirror('debian.mirror.garr.it', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'loong64', 'mips64el', 'mipsel', 'ppc64el', 'riscv64', 's390x']),
      new Mirror('ftp.linux.it', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'loong64', 'mips64el', 'mipsel', 'ppc64el', 'riscv64', 's390x']),
      new Mirror('giano.com.dist.unige.it', '/debian/', ['amd64', 'armel', 'armhf', 'i386']),
      new Mirror('mirror.units.it', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'loong64', 'mips64el', 'mipsel', 'ppc64el', 'riscv64', 's390x']),
    ]),
    new Country('Japan', 'jp', [
      new Mirror('ftp.maru.co.jp', '/debian/', ['amd64', 'arm64']),
      new Mirror('ftp.riken.jp', '/Linux/debian/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'loong64', 'mips64el', 'mipsel', 'ppc64el', 'riscv64', 's390x']),
      new Mirror('ftp.yz.yamagata-u.ac.jp', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'loong64', 'mips64el', 'mipsel', 'ppc64el', 'riscv64', 's390x']),
      new Mirror('mirrors.xtom.jp', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'loong64', 'mips64el', 'mipsel', 'ppc64el', 'riscv64', 's390x']),
    ]),
    new Country('Kazakhstan', 'kz', [
      new Mirror('mirror.hoster.kz', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'loong64', 'mips64el', 'mipsel', 'ppc64el', 'riscv64', 's390x']),
      new Mirror('mirror.ps.kz', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'loong64', 'mips64el', 'mipsel', 'ppc64el', 'riscv64', 's390x']),
    ]),
    new Country('Kenya', 'ke', [
      new Mirror('debian.mirror.ac.ke', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'loong64', 'mips64el', 'mipsel', 'ppc64el', 'riscv64', 's390x']),
      new Mirror('debian.mirror.liquidtelecom.com', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'loong64', 'mips64el', 'mipsel', 'ppc64el', 'riscv64', 's390x']),
    ]),
    new Country('Korea', 'kr', [
      new Mirror('ftp.kr.debian.org', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'loong64', 'mips64el', 'mipsel', 'ppc64el', 'riscv64', 's390x']),
      new Mirror('ftp.kaist.ac.kr', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'loong64', 'mips64el', 'mipsel', 'ppc64el', 'riscv64', 's390x']),
      new Mirror('ftp.lanet.kr', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'loong64', 'mips64el', 'mipsel', 'ppc64el', 'riscv64', 's390x']),
      new Mirror('mirror.pangkin.com', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'loong64', 'mips64el', 'mipsel', 'ppc64el', 'riscv64', 's390x']),
      new Mirror('mirror.siwoo.org', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'loong64', 'mips64el', 'mipsel', 'ppc64el', 'riscv64', 's390x']),
      new Mirror('mirror.yuki.net.uk', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'loong64', 'mips64el', 'mipsel', 'ppc64el', 'riscv64', 's390x']),
    ]),
    new Country('Latvia', 'lv', [
      new Mirror('debian.koyanet.lv', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'loong64', 'mips64el', 'mipsel', 'ppc64el', 'riscv64', 's390x']),
      new Mirror('ftp.linux.edu.lv', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'loong64', 'mips64el', 'mipsel', 'ppc64el', 'riscv64', 's390x']),
      new Mirror('mirror.cloudhosting.lv', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'mips64el', 'mipsel', 'ppc64el', 's390x']),
      new Mirror('mirror.veesp.com', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'mips64el', 'mipsel', 'ppc64el', 'riscv64', 's390x']),
    ]),
    new Country('Lithuania', 'lt', [
      new Mirror('ftp.lt.debian.org', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'loong64', 'mips64el', 'mipsel', 'ppc64el', 'riscv64', 's390x']),
      new Mirror('debian.balt.net', '/debian/', ['amd64']),
      new Mirror('debian.mirror.vu.lt', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'loong64', 'mips64el', 'mipsel', 'ppc64el', 'riscv64', 's390x']),
      new Mirror('mirror.litnet.lt', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'loong64', 'mips64el', 'mipsel', 'ppc64el', 'riscv64', 's390x']),
      new Mirror('mirror.vpsnet.com', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'loong64', 'mips64el', 'mipsel', 'ppc64el', 'riscv64', 's390x']),
    ]),
    new Country('Macedonia, Republic of', 'mk', [
      new Mirror('mirror.a1.mk', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'loong64', 'mips64el', 'mipsel', 'ppc64el', 'riscv64', 's390x']),
    ]),
    new Country('Malaysia', 'my', [
      new Mirror('debian.tuxuri.com', '/debian/', ['amd64', 'arm64', 'armhf', 'i386']),
    ]),
    new Country('Moldova', 'md', [
      new Mirror('ftp.md.debian.org', '/debian/', ['amd64', 'armel', 'armhf', 'hurd-i386', 'i386', 'ia64', 'kfreebsd-amd64', 'kfreebsd-i386', 'mips', 'mipsel', 'powerpc', 's390', 's390x']),
      new Mirror('mirror.as43289.net', '/debian/', ['amd64', 'armel', 'armhf', 'hurd-i386', 'i386', 'ia64', 'kfreebsd-amd64', 'kfreebsd-i386', 'mips', 'mipsel', 'powerpc', 's390', 's390x']),
    ]),
    new Country('Netherlands', 'nl', [
      new Mirror('ftp.nl.debian.org', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'loong64', 'mips64el', 'mipsel', 'ppc64el', 'riscv64', 's390x']),
      new Mirror('debian.snt.utwente.nl', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'loong64', 'mips64el', 'mipsel', 'ppc64el', 'riscv64', 's390x']),
      new Mirror('mirror.bgp.rodeo', '/debian/', ['amd64', 'arm64']),
      new Mirror('mirror.duocast.net', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'loong64', 'mips64el', 'mipsel', 'ppc64el', 'riscv64', 's390x']),
      new Mirror('mirror.nforce.com', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'hurd-i386', 'i386', 'ia64', 'kfreebsd-amd64', 'kfreebsd-i386', 'mips', 'mips64el', 'mipsel', 'powerpc', 'ppc64el', 's390', 's390x']),
      new Mirror('mirror.nl.cdn-perfprod.com', '/debian/', ['amd64', 'i386']),
      new Mirror('mirror.nl.leaseweb.net', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'mips', 'mips64el', 'mipsel', 'powerpc', 'ppc64el', 's390x']),
      new Mirror('mirror.nl.mirhosting.net', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'loong64', 'mips64el', 'mipsel', 'ppc64el', 'riscv64', 's390x']),
      new Mirror('mirrors.hostiserver.com', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'loong64', 'mips64el', 'mipsel', 'ppc64el', 'riscv64', 's390x']),
      new Mirror('mirrors.xtom.nl', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'loong64', 'mips64el', 'mipsel', 'ppc64el', 'riscv64', 's390x']),
      new Mirror('mirror.tngnet.com', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'loong64', 'mips64el', 'mipsel', 'ppc64el', 'riscv64', 's390x']),
      new Mirror('mirror.vpgrp.io', '/debian/', ['amd64']),
      new Mirror('nl.mirrors.clouvider.net', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'loong64', 'mips64el', 'mipsel', 'ppc64el', 'riscv64', 's390x']),
    ]),
    new Country('New Caledonia', 'nc', [
      new Mirror('ftp.nc.debian.org', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'loong64', 'mips64el', 'mipsel', 'ppc64el', 'riscv64', 's390x']),
      new Mirror('debian.nautile.nc', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'loong64', 'mips64el', 'mipsel', 'ppc64el', 'riscv64', 's390x']),
    ]),
    new Country('New Zealand', 'nz', [
      new Mirror('ftp.nz.debian.org', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'loong64', 'mips64el', 'mipsel', 'ppc64el', 'riscv64', 's390x']),
      new Mirror('linux.purple-cat.net', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'loong64', 'mips64el', 'mipsel', 'ppc64el', 'riscv64', 's390x']),
      new Mirror('mirror.fsmg.org.nz', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'loong64', 'mips64el', 'mipsel', 'ppc64el', 'riscv64', 's390x']),
    ]),
    new Country('Poland', 'pl', [
      new Mirror('ftp.pl.debian.org', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'loong64', 'mips', 'mips64el', 'mipsel', 'ppc64el', 'riscv64', 's390x']),
      new Mirror('ftp.agh.edu.pl', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'loong64', 'riscv64']),
      new Mirror('ftp.icm.edu.pl', '/pub/Linux/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'loong64', 'mips64el', 'mipsel', 'ppc64el', 'riscv64', 's390x']),
      new Mirror('ftp.psnc.pl', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'loong64', 'mips64el', 'mipsel', 'ppc64el', 'riscv64', 's390x']),
      new Mirror('ftp.task.gda.pl', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'loong64', 'mips', 'mips64el', 'mipsel', 'ppc64el', 'riscv64', 's390x']),
    ]),
    new Country('Portugal', 'pt', [
      new Mirror('ftp.pt.debian.org', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'loong64', 'mips64el', 'mipsel', 'ppc64el', 'riscv64', 's390x']),
      new Mirror('debian.uevora.pt', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'loong64', 'mips64el', 'mipsel', 'ppc64el', 'riscv64', 's390x']),
      new Mirror('ftp.eq.uc.pt', '/software/Linux/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'loong64', 'mips64el', 'mipsel', 'ppc64el', 'riscv64', 's390x']),
      new Mirror('ftp.rnl.tecnico.ulisboa.pt', '/pub/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'loong64', 'mips64el', 'mipsel', 'ppc64el', 'riscv64', 's390x']),
      new Mirror('mirrors.ptisp.pt', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'loong64', 'mips64el', 'mipsel', 'ppc64el', 'riscv64', 's390x']),
      new Mirror('mirrors.up.pt', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'loong64', 'mips64el', 'mipsel', 'ppc64el', 'riscv64', 's390x']),
    ]),
    new Country('Reunion', 're', [
      new Mirror('debian.mithril.re', '/debian/', ['amd64']),
    ]),
    new Country('Romania', 'ro', [
      new Mirror('mirror.flo.c-f.ro', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'loong64', 'mips64el', 'mipsel', 'ppc64el', 'riscv64', 's390x']),
      new Mirror('mirror.linux.ro', '/debian/', ['amd64']),
      new Mirror('mirrors.datapark.ro', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'loong64', 'mips64el', 'mipsel', 'ppc64el', 'riscv64', 's390x']),
      new Mirror('mirrors.hosterion.ro', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'loong64', 'mips64el', 'mipsel', 'ppc64el', 'riscv64', 's390x']),
      new Mirror('mirrors.hostico.ro', '/debian/', ['amd64']),
      new Mirror('mirrors.nav.ro', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'loong64', 'mips64el', 'mipsel', 'ppc64el', 'riscv64', 's390x']),
      new Mirror('mirrors.nxthost.com', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'mips64el', 'mipsel', 'ppc64el', 's390x']),
      new Mirror('mirrors.pidginhost.com', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'loong64', 'riscv64']),
      new Mirror('ro.mirror.flokinet.net', '/debian/', ['amd64']),
    ]),
    new Country('Russia', 'ru', [
      new Mirror('ftp.ru.debian.org', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'loong64', 'mips64el', 'mipsel', 'ppc64el', 'riscv64', 's390x']),
      new Mirror('ftp.psn.ru', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'loong64', 'mips64el', 'mipsel', 'ppc64el', 'riscv64']),
      new Mirror('mirror.corbina.net', '/debian/', ['amd64']),
      new Mirror('mirror.docker.ru', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'loong64', 'mips64el', 'mipsel', 'ppc64el', 'riscv64', 's390x']),
      new Mirror('mirror.kpfu.ru', '/debian/', ['amd64']),
      new Mirror('mirror.mephi.ru', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'loong64', 'mips64el', 'mipsel', 'ppc64el', 'riscv64', 's390x']),
      new Mirror('mirror.neftm.ru', '/debian/', ['amd64']),
      new Mirror('mirrors.powernet.com.ru', '/debian/', ['amd64', 'i386']),
      new Mirror('mirror.truenetwork.ru', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'loong64', 'mips64el', 'mipsel', 'ppc64el', 'riscv64', 's390x']),
    ]),
    new Country('Saudi Arabia', 'sa', [
      new Mirror('mirror.maeen.sa', '/debian/', ['amd64', 'arm64']),
    ]),
    new Country('Serbia', 'rs', [
      new Mirror('mirror.pmf.kg.ac.rs', '/debian/', ['amd64']),
    ]),
    new Country('Singapore', 'sg', [
      new Mirror('mirror.djvg.sg', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'loong64', 'mips64el', 'mipsel', 'ppc64el', 'riscv64', 's390x']),
      new Mirror('mirror.sg.gs', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'loong64', 'mips64el', 'mipsel', 'ppc64el', 'riscv64', 's390x']),
      new Mirror('ossmirror.mycloud.services', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'loong64', 'mips64el', 'mipsel', 'ppc64el', 'riscv64', 's390x']),
    ]),
    new Country('Slovakia', 'sk', [
      new Mirror('ftp.sk.debian.org', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'hurd-i386', 'i386', 'ia64', 'kfreebsd-amd64', 'kfreebsd-i386', 'mips', 'mips64el', 'mipsel', 'powerpc', 'ppc64el', 's390', 's390x']),
      new Mirror('deb.bbxnet.sk', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'loong64', 'mips64el', 'mipsel', 'ppc64el', 'riscv64', 's390x']),
      new Mirror('ftp.antik.sk', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'loong64', 'mips64el', 'mipsel', 'ppc64el', 'riscv64', 's390x']),
      new Mirror('ftp.debian.sk', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'hurd-i386', 'i386', 'ia64', 'kfreebsd-amd64', 'kfreebsd-i386', 'mips', 'mips64el', 'mipsel', 'powerpc', 'ppc64el', 's390', 's390x']),
    ]),
    new Country('South Africa', 'za', [
      new Mirror('debian.as3741.net', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'loong64', 'mips64el', 'mipsel', 'ppc64el', 'riscv64', 's390x']),
      new Mirror('debian.envisagecloud.net.za', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'loong64', 'mips64el', 'mipsel', 'ppc64el', 'riscv64', 's390x']),
      new Mirror('debian.saix.net', '/', ['amd64', 'armel', 'armhf', 'i386', 'mipsel', 's390x']),
    ]),
    new Country('Spain', 'es', [
      new Mirror('debian.grn.cat', '/debian/', ['amd64', 'arm64', 'armhf', 'i386', 'loong64', 'riscv64']),
      new Mirror('debian.redimadrid.es', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'loong64', 'mips64el', 'mipsel', 'ppc64el', 'riscv64', 's390x']),
      new Mirror('ftp.cica.es', '/debian/', ['amd64', 'i386']),
      new Mirror('mirror.raiolanetworks.com', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'loong64', 'mips64el', 'mipsel', 'ppc64el', 'riscv64', 's390x']),
      new Mirror('repo.ifca.es', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'loong64', 'mips64el', 'mipsel', 'ppc64el', 'riscv64', 's390x']),
      new Mirror('softlibre.unizar.es', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'loong64', 'mips64el', 'mipsel', 'ppc64el', 'riscv64', 's390x']),
    ]),
    new Country('Sweden', 'se', [
      new Mirror('debian.lth.se', '/debian/', ['amd64', 'armel', 'armhf', 'hurd-i386', 'i386', 'ia64', 'kfreebsd-amd64', 'kfreebsd-i386', 'mips', 'mipsel', 'powerpc', 's390', 's390x']),
      new Mirror('debian.mirror.su.se', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'loong64', 'mips64el', 'mipsel', 'ppc64el', 'riscv64', 's390x']),
      new Mirror('ftpmirror1.infania.net', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'loong64', 'mips64el', 'mipsel', 'ppc64el', 'riscv64', 's390x']),
      new Mirror('mirror.braindrainlan.nu', '/debian/', ['amd64', 'i386']),
      new Mirror('mirrors.glesys.net', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'loong64', 'mips64el', 'mipsel', 'ppc64el', 'riscv64', 's390x']),
    ]),
    new Country('Switzerland', 'ch', [
      new Mirror('ftp.ch.debian.org', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'loong64', 'mips64el', 'mipsel', 'ppc64el', 'riscv64', 's390x']),
      new Mirror('debian.ethz.ch', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'loong64', 'mips64el', 'mipsel', 'ppc64el', 'riscv64', 's390x']),
      new Mirror('deb.nextgen.ch', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'mips64el', 'mipsel', 'ppc64el', 'riscv64', 's390x']),
      new Mirror('linuxsoft.cern.ch', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'loong64', 'mips64el', 'mipsel', 'ppc64el', 'riscv64', 's390x']),
      new Mirror('mirror1.infomaniak.com', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'loong64', 'mips64el', 'mipsel', 'ppc64el', 'riscv64', 's390x']),
      new Mirror('mirror2.infomaniak.com', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'loong64', 'mips64el', 'mipsel', 'ppc64el', 'riscv64', 's390x']),
      new Mirror('mirror.init7.net', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'loong64', 'mips64el', 'mipsel', 'ppc64el', 'riscv64', 's390x']),
      new Mirror('mirror.iway.ch', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'loong64', 'mips64el', 'mipsel', 'ppc64el', 'riscv64', 's390x']),
      new Mirror('mirror.metanet.ch', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'loong64', 'mips64el', 'mipsel', 'ppc64el', 'riscv64', 's390x']),
      new Mirror('mirror.sinavps.ch', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'loong64', 'mips64el', 'mipsel', 'ppc64el', 'riscv64', 's390x']),
      new Mirror('pkg.adfinis-on-exoscale.ch', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'loong64', 'mips64el', 'mipsel', 'ppc64el', 'riscv64', 's390x']),
    ]),
    new Country('Taiwan', 'tw', [
      new Mirror('ftp.tw.debian.org', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'loong64', 'mips64el', 'mipsel', 'ppc64el', 'riscv64', 's390x']),
      new Mirror('debian.ccns.ncku.edu.tw', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'loong64', 'mips64el', 'mipsel', 'ppc64el', 'riscv64', 's390x']),
      new Mirror('debian.csie.ntu.edu.tw', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'loong64', 'mips64el', 'mipsel', 'ppc64el', 'riscv64', 's390x']),
      new Mirror('debian.cs.nycu.edu.tw', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'loong64', 'mips64el', 'mipsel', 'ppc64el', 'riscv64', 's390x']),
      new Mirror('mirror.twds.com.tw', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'loong64', 'mips64el', 'mipsel', 'ppc64el', 'riscv64', 's390x']),
      new Mirror('opensource.nchc.org.tw', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'loong64', 'mips64el', 'mipsel', 'ppc64el', 'riscv64', 's390x']),
      new Mirror('tw1.mirror.blendbyte.net', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'loong64', 'mips64el', 'mipsel', 'ppc64el', 'riscv64', 's390x']),
    ]),
    new Country('Thailand', 'th', [
      new Mirror('ftp.debianclub.org', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'loong64', 'mips64el', 'mipsel', 'ppc64el', 'riscv64', 's390x']),
      new Mirror('mirror.applebred.net', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'loong64', 'mips64el', 'mipsel', 'ppc64el', 'riscv64', 's390x']),
      new Mirror('mirror.kku.ac.th', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'loong64', 'mips64el', 'mipsel', 'ppc64el', 'riscv64', 's390x']),
      new Mirror('mirrors.cloudforest.co.th', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'loong64', 'mips64el', 'mipsel', 'ppc64el', 'riscv64', 's390x']),
    ]),
    new Country('Turkey', 'tr', [
      new Mirror('ftp.tr.debian.org', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'loong64', 'mips64el', 'mipsel', 'ppc64el', 'riscv64', 's390x']),
      new Mirror('ftp.linux.org.tr', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'loong64', 'mips64el', 'mipsel', 'ppc64el', 'riscv64', 's390x']),
    ]),
    new Country('Ukraine', 'ua', [
      new Mirror('debian.volia.net', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'loong64', 'mips64el', 'mipsel', 'ppc64el', 'riscv64', 's390x']),
      new Mirror('distrohub.kyiv.ua', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'loong64', 'mips64el', 'mipsel', 'ppc64el', 'riscv64', 's390x']),
      new Mirror('mirror.mirohost.net', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'loong64', 'mips64el', 'mipsel', 'ppc64el', 'riscv64', 's390x']),
    ]),
    new Country('United Kingdom', 'uk', [
      new Mirror('ftp.uk.debian.org', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'loong64', 'mips64el', 'mipsel', 'ppc64el', 'riscv64', 's390x']),
      new Mirror('free.hands.com', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'loong64', 'mips64el', 'mipsel', 'ppc64el', 'riscv64', 's390x']),
      new Mirror('mirror.lchost.net', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'loong64', 'mips64el', 'mipsel', 'ppc64el', 'riscv64', 's390x']),
      new Mirror('mirror.mythic-beasts.com', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'loong64', 'mips64el', 'mipsel', 'ppc64el', 'riscv64', 's390x']),
      new Mirror('mirror.ox.ac.uk', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'loong64', 'mips64el', 'mipsel', 'ppc64el', 'riscv64', 's390x']),
      new Mirror('mirrors.coreix.net', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'loong64', 'mips64el', 'mipsel', 'ppc64el', 'riscv64', 's390x']),
      new Mirror('mirrorservice.org', '/sites/ftp.debian.org/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'loong64', 'mips64el', 'mipsel', 'ppc64el', 'riscv64', 's390x']),
      new Mirror('ukdebian.mirror.anlx.net', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'loong64', 'mips64el', 'mipsel', 'ppc64el', 'riscv64', 's390x']),
      new Mirror('uk.mirrors.clouvider.net', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'loong64', 'mips64el', 'mipsel', 'ppc64el', 'riscv64', 's390x']),
    ]),
    new Country('United States', 'us', [
      new Mirror('ftp.us.debian.org', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'loong64', 'mips64el', 'mipsel', 'ppc64el', 'riscv64', 's390x']),
      new Mirror('atl.mirrors.clouvider.net', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'loong64', 'mips64el', 'mipsel', 'ppc64el', 'riscv64', 's390x']),
      new Mirror('debian-archive.trafficmanager.net', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'loong64', 'mips64el', 'mipsel', 'ppc64el', 'riscv64', 's390x']),
      new Mirror('debian.csail.mit.edu', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'loong64', 'mips64el', 'mipsel', 'ppc64el', 'riscv64', 's390x']),
      new Mirror('debian.osuosl.org', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'loong64', 'mips64el', 'mipsel', 'ppc64el', 'riscv64', 's390x']),
      new Mirror('la.mirrors.clouvider.net', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'loong64', 'mips64el', 'mipsel', 'ppc64el', 'riscv64', 's390x']),
      new Mirror('lethe.chinstrap.org', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'loong64', 'mips64el', 'mipsel', 'ppc64el', 'riscv64', 's390x']),
      new Mirror('mirror.0x626b.com', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'loong64', 'mips64el', 'mipsel', 'ppc64el', 'riscv64', 's390x']),
      new Mirror('mirror.cogentco.com', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'loong64', 'mips64el', 'mipsel', 'ppc64el', 'riscv64', 's390x']),
      new Mirror('mirror.keystealth.org', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'loong64', 'mips64el', 'mipsel', 'ppc64el', 'riscv64', 's390x']),
      new Mirror('mirror.rustytel.net', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'loong64', 'mips64el', 'mipsel', 'ppc64el', 'riscv64', 's390x']),
      new Mirror('mirrors.accretive-networks.net', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'loong64', 'mips64el', 'mipsel', 'ppc64el', 'riscv64', 's390x']),
      new Mirror('mirror.siena.edu', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'loong64', 'mips64el', 'mipsel', 'ppc64el', 'riscv64', 's390x']),
      new Mirror('mirrors.lug.mtu.edu', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'loong64', 'mips64el', 'mipsel', 'ppc64el', 'riscv64', 's390x']),
      new Mirror('mirror.steadfast.net', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'loong64', 'mips64el', 'mipsel', 'ppc64el', 'riscv64', 's390x']),
      new Mirror('mirrors.vcea.wsu.edu', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'loong64', 'mips64el', 'mipsel', 'ppc64el', 'riscv64', 's390x']),
      new Mirror('mirrors.xtom.com', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'loong64', 'mips64el', 'mipsel', 'ppc64el', 'riscv64', 's390x']),
      new Mirror('mirror.tzulo.com', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'loong64', 'mips64el', 'mipsel', 'ppc64el', 'riscv64', 's390x']),
      new Mirror('mirror.us.leaseweb.net', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'mips', 'mips64el', 'mipsel', 'powerpc', 'ppc64el', 's390x']),
      new Mirror('mirror.us.mirhosting.net', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'loong64', 'mips64el', 'mipsel', 'ppc64el', 'riscv64', 's390x']),
      new Mirror('mirror.us.oneandone.net', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'loong64', 'mips64el', 'mipsel', 'ppc64el', 'riscv64', 's390x']),
      new Mirror('nyc.mirrors.clouvider.net', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'loong64', 'mips64el', 'mipsel', 'ppc64el', 'riscv64', 's390x']),
      new Mirror('repo.ialab.dsu.edu', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'loong64', 'mips64el', 'mipsel', 'ppc64el', 'riscv64', 's390x']),
    ]),
    new Country('Uruguay', 'uy', [
      new Mirror('debian.repo.cure.edu.uy', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'loong64', 'mips64el', 'mipsel', 'ppc64el', 'riscv64', 's390x']),
      new Mirror('mirror.undernet.uy', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'loong64', 'mips64el', 'mipsel', 'ppc64el', 'riscv64', 's390x']),
    ]),
    new Country('Vietnam', 'vn', [
      new Mirror('debian.xtdv.net', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'loong64', 'mips64el', 'mipsel', 'ppc64el', 'riscv64', 's390x']),
      new Mirror('mirror.bizflycloud.vn', '/debian/', ['amd64', 'arm64', 'armel', 'armhf', 'i386', 'loong64', 'mips64el', 'mipsel', 'ppc64el', 'riscv64', 's390x']),
    ]),
  ];
}

// ─── Architecture List ───────────────────────────────────────────────────────

const ARCHITECTURES = [
  '',
  'all',
  'amd64',
  'arm64',
  'armel',
  'armhf',
  'hurd-i386',
  'i386',
  'ia64',
  'kfreebsd-amd64',
  'kfreebsd-i386',
  'loong64',
  'mips',
  'mips64el',
  'mipsel',
  'powerpc',
  'ppc64el',
  'riscv64',
  's390',
  's390x',
  'sparc',
];

// ─── Helper: Build JSON for client-side use ──────────────────────────────────

/** @return array{cdn: string, countries: list<array{name: string, code: string, mirrors: list<array{hostname: string, path: string, url: string}>}>} */
function getMirrorDataAsJson(): array
{
  $cdn = getCdnMirror();
  $countries = [];

  foreach (getCountries() as $country) {
    $mirrors = [];
    foreach ($country->mirrors as $m) {
      $mirrors[] = [
        'hostname' => $m->hostname,
        'path'     => $m->path,
        'url'      => $m->url(),
      ];
    }
    $countries[] = [
      'name'    => $country->name,
      'code'    => $country->code,
      'mirrors' => $mirrors,
    ];
  }

  return [
    'cdn'      => $cdn->url(),
    'countries' => $countries,
  ];
}

// ─── Helper: Find a mirror by URL ────────────────────────────────────────────

function findMirrorByUrl(string $url): ?Mirror
{
  if ($url === getCdnMirror()->url()) {
    return getCdnMirror();
  }

  foreach (getCountries() as $country) {
    foreach ($country->mirrors as $mirror) {
      if ($mirror->url() === $url) {
        return $mirror;
      }
    }
  }

  return null;
}

// ─── Helper: Get mirrors for a country code (or CDN) ─────────────────────────

/** @return list<Mirror> */
function getMirrorsForCountry(string $countryCode, array $countries): array
{
  if ($countryCode === '') {
    return [getCdnMirror()];
  }

  foreach ($countries as $c) {
    if ($c->code === $countryCode) {
      return $c->mirrors;
    }
  }

  return [];
}

// ─── Helper: Find country code by mirror URL ─────────────────────────────────

function findCountryByMirror(string $mirrorUrl, array $countries): ?string
{
  foreach ($countries as $c) {
    foreach ($c->mirrors as $m) {
      if ($m->url() === $mirrorUrl) {
        return $c->code;
      }
    }
  }

  return null;
}
