<?php

// SPDX-License-Identifier: MPL-2.0

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

// ─── Source Format Enum ──────────────────────────────────────────────────────

enum SourceFormat: string
{
  case OneLine = 'oneline';
  case Deb822  = 'deb822';

  public function label(): string
  {
    return match ($this) {
      self::OneLine => 'one-line (.list)',
      self::Deb822  => 'deb822 (.sources)',
    };
  }
}

// ─── Mirror Class ────────────────────────────────────────────────────────────

final readonly class Mirror
{
  public function __construct(
    public string $hostname,
    public string $path,
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
  return new Mirror('deb.debian.org', '/debian/');
}

// ─── All Countries & Mirrors ─────────────────────────────────────────────────

/** @return list<Country> */
function getCountries(): array
{
  return [
    new Country('Argentina', 'ar', [
      new Mirror('debian.unnoba.edu.ar', '/debian/'),
      new Mirror('mirror.sitsa.com.ar', '/debian/'),
    ]),
    new Country('Australia', 'au', [
      new Mirror('ftp.au.debian.org', '/debian/'),
      new Mirror('debian.mirror.digitalpacific.com.au', '/debian/'),
      new Mirror('debian.mirror.serversaustralia.com.au', '/debian/'),
      new Mirror('mirror.aarnet.edu.au', '/debian/'),
      new Mirror('mirror.amaze.com.au', '/debian/'),
      new Mirror('mirror.gsl.icu', '/debian/'),
      new Mirror('mirror.linux.org.au', '/debian/'),
      new Mirror('mirror.overthewire.com.au', '/debian/'),
      new Mirror('mirror.realcompute.io', '/debian/'),
      new Mirror('mirrors.xtom.au', '/debian/'),
    ]),
    new Country('Austria', 'at', [
      new Mirror('ftp.at.debian.org', '/debian/'),
      new Mirror('debian.anexia.at', '/debian/'),
      new Mirror('debian.lagis.at', '/debian/'),
      new Mirror('ftp.tugraz.at', '/mirror/debian/'),
      new Mirror('mirror.alwyzon.net', '/debian/'),
    ]),
    new Country('Azerbaijan', 'az', [
      new Mirror('mirror.ourhost.az', '/debian/'),
    ]),
    new Country('Bangladesh', 'bd', [
      new Mirror('mirror.xeonbd.com', '/debian/'),
    ]),
    new Country('Belarus', 'by', [
      new Mirror('ftp.by.debian.org', '/debian/'),
      new Mirror('ftp.byfly.by', '/debian/'),
      new Mirror('mirror.datacenter.by', '/debian/'),
    ]),
    new Country('Belgium', 'be', [
      new Mirror('ftp.be.debian.org', '/debian/'),
      new Mirror('debian-mirror.behostings.net', '/debian/'),
      new Mirror('mirror.as35701.net', '/debian/'),
      new Mirror('mirror.unix-solutions.be', '/debian/'),
    ]),
    new Country('Brazil', 'br', [
      new Mirror('ftp.br.debian.org', '/debian/'),
      new Mirror('debian.c3sl.ufpr.br', '/debian/'),
      new Mirror('debian.pop-sc.rnp.br', '/debian/'),
      new Mirror('mirror.blue3.com.br', '/debian/'),
      new Mirror('mirrors.ic.unicamp.br', '/debian/'),
      new Mirror('mirror.uepg.br', '/debian/'),
      new Mirror('mirror.ufscar.br', '/debian/'),
    ]),
    new Country('Bulgaria', 'bg', [
      new Mirror('ftp.bg.debian.org', '/debian/'),
      new Mirror('debian.a1.bg', '/debian/'),
      new Mirror('debian.ipacct.com', '/debian/'),
      new Mirror('debian.ludost.net', '/debian/'),
      new Mirror('debian.mnet.bg', '/debian/'),
      new Mirror('debian.telecoms.bg', '/debian/'),
      new Mirror('ftp.uni-sofia.bg', '/debian/'),
      new Mirror('mirrors.netix.net', '/debian/'),
      new Mirror('mirror.telepoint.bg', '/debian/'),
    ]),
    new Country('Canada', 'ca', [
      new Mirror('ftp.ca.debian.org', '/debian/'),
      new Mirror('debian.mirror.globo.tech', '/debian/'),
      new Mirror('debian.mirror.rafal.ca', '/debian/'),
      new Mirror('mirror.cpsc.ucalgary.ca', '/debian/'),
      new Mirror('mirror.csclub.uwaterloo.ca', '/debian/'),
      new Mirror('mirror.dst.ca', '/debian/'),
      new Mirror('mirror.estone.ca', '/debian/'),
      new Mirror('mirror.it.ubc.ca', '/debian/'),
    ]),
    new Country('Chile', 'cl', [
      new Mirror('ftp.cl.debian.org', '/debian/'),
      new Mirror('debian-mirror.puq.apoapsis.cl', '/debian/'),
      new Mirror('elmirror.cl', '/debian/'),
      new Mirror('mirror.hnd.cl', '/debian/'),
      new Mirror('mirror.insacom.cl', '/debian/'),
    ]),
    new Country('China', 'cn', [
      new Mirror('ftp.cn.debian.org', '/debian/'),
      new Mirror('mirror.lzu.edu.cn', '/debian/'),
      new Mirror('mirror.nju.edu.cn', '/debian/'),
      new Mirror('mirror.nyist.edu.cn', '/debian/'),
      new Mirror('mirrors.bfsu.edu.cn', '/debian/'),
      new Mirror('mirrors.hit.edu.cn', '/debian/'),
      new Mirror('mirrors.jlu.edu.cn', '/debian/'),
      new Mirror('mirrors.qlu.edu.cn', '/debian/'),
      new Mirror('mirrors.tuna.tsinghua.edu.cn', '/debian/'),
      new Mirror('mirrors.ustc.edu.cn', '/debian/'),
      new Mirror('mirrors.zju.edu.cn', '/debian/'),
    ]),
    new Country('Costa Rica', 'cr', [
      new Mirror('debianmirror.una.ac.cr', '/debian/'),
      new Mirror('mirrors.ucr.ac.cr', '/debian/'),
    ]),
    new Country('Croatia', 'hr', [
      new Mirror('ftp.hr.debian.org', '/debian/'),
      new Mirror('debian.carnet.hr', '/debian/'),
      new Mirror('debian.iskon.hr', '/debian/'),
    ]),
    new Country('Czech Republic', 'cz', [
      new Mirror('ftp.cz.debian.org', '/debian/'),
      new Mirror('debian.mirror.web4u.cz', '/'),
      new Mirror('debian.nic.cz', '/debian/'),
      new Mirror('debian.superhosting.cz', '/debian/'),
      new Mirror('ftp.cvut.cz', '/debian/'),
      new Mirror('ftp.debian.cz', '/debian/'),
      new Mirror('ftp.sh.cvut.cz', '/debian/'),
      new Mirror('ftp.zcu.cz', '/debian/'),
      new Mirror('merlin.fit.vutbr.cz', '/debian/'),
      new Mirror('mirror.dkm.cz', '/debian/'),
      new Mirror('mirror.it4i.cz', '/debian/'),
      new Mirror('mirror-prg.webglobe.com', '/debian/'),
    ]),
    new Country('Denmark', 'dk', [
      new Mirror('ftp.dk.debian.org', '/debian/'),
      new Mirror('mirrors.dotsrc.org', '/debian/'),
      new Mirror('mirrors.rackhosting.com', '/debian/'),
    ]),
    new Country('Estonia', 'ee', [
      new Mirror('ftp.ee.debian.org', '/debian/'),
      new Mirror('ftp.eenet.ee', '/debian/'),
      new Mirror('mirrors.xtom.ee', '/debian/'),
    ]),
    new Country('Finland', 'fi', [
      new Mirror('debian.web.trex.fi', '/debian/'),
      new Mirror('mirror.5i.fi', '/debian/'),
      new Mirror('www.nic.funet.fi', '/debian/'),
    ]),
    new Country('France', 'fr', [
      new Mirror('ftp.fr.debian.org', '/debian/'),
      new Mirror('apt.tetaneutral.net', '/debian/'),
      new Mirror('debian.apt-mirror.de', '/debian/'),
      new Mirror('debian.mirrors.ovh.net', '/debian/'),
      new Mirror('debian.obspm.fr', '/debian/'),
      new Mirror('debian.polytech-lille.fr', '/debian/'),
      new Mirror('debian.proxad.net', '/debian/'),
      new Mirror('deb-mir1.naitways.net', '/debian/'),
      new Mirror('ftp.ec-m.fr', '/debian/'),
      new Mirror('ftp.lip6.fr', '/pub/linux/distributions/debian/'),
      new Mirror('ftp.rezopole.net', '/debian/'),
      new Mirror('ftp.univ-pau.fr', '/linux/mirrors/debian/'),
      new Mirror('ftp.u-picardie.fr', '/debian/'),
      new Mirror('ftp.u-strasbg.fr', '/debian/'),
      new Mirror('miroir.univ-lorraine.fr', '/debian/'),
      new Mirror('mirror.abaclouda.net', '/debian/'),
      new Mirror('mirror.debian.ikoula.com', '/debian/'),
      new Mirror('mirror.gitoyen.net', '/debian/'),
      new Mirror('mirror.johnnybegood.fr', '/debian/'),
      new Mirror('mirror.plusserver.com', '/debian/debian/'),
    ]),
    new Country('Germany', 'de', [
      new Mirror('ftp2.de.debian.org', '/debian/'),
      new Mirror('ftp.de.debian.org', '/debian/'),
      new Mirror('debian.charite.de', '/debian/'),
      new Mirror('debian.inf.tu-dresden.de', '/debian/'),
      new Mirror('debian.mirror.iphh.net', '/debian/'),
      new Mirror('debian.mirror.lrz.de', '/debian/'),
      new Mirror('debian.netcologne.de', '/debian/'),
      new Mirror('debian.tu-bs.de', '/debian/'),
      new Mirror('debian.uni-due.de', '/debian/'),
      new Mirror('de.mirrors.clouvider.net', '/debian/'),
      new Mirror('ftp.fau.de', '/debian/'),
      new Mirror('ftp.gwdg.de', '/debian/'),
      new Mirror('ftp.halifax.rwth-aachen.de', '/debian/'),
      new Mirror('ftp.hosteurope.de', '/mirror/ftp.debian.org/debian/'),
      new Mirror('ftp-stud.hs-esslingen.de', '/debian/'),
      new Mirror('ftp.stw-bonn.de', '/debian/'),
      new Mirror('ftp.tu-chemnitz.de', '/debian/'),
      new Mirror('ftp.uni-hannover.de', '/debian/debian/'),
      new Mirror('ftp.uni-kl.de', '/debian/'),
      new Mirror('ftp.uni-mainz.de', '/debian/'),
      new Mirror('ftp.uni-stuttgart.de', '/debian/'),
      new Mirror('ftp.wrz.de', '/debian/'),
      new Mirror('mirror.23m.com', '/debian/'),
      new Mirror('mirror.creoline.net', '/debian/'),
      new Mirror('mirror.de.leaseweb.net', '/debian/'),
      new Mirror('mirror.dogado.de', '/debian/'),
      new Mirror('mirror.eu.oneandone.net', '/debian/'),
      new Mirror('mirror.informatik.tu-freiberg.de', '/debian/'),
      new Mirror('mirror.ipb.de', '/debian/'),
      new Mirror('mirror.mk.de', '/debian/'),
      new Mirror('mirror.netzwerge.de', '/debian/'),
      new Mirror('mirror.plusline.net', '/debian/'),
      new Mirror('mirrors.xtom.de', '/debian/'),
      new Mirror('mirror.united-gameserver.de', '/debian/'),
      new Mirror('mirror.wtnet.de', '/debian/'),
      new Mirror('packages.hs-regensburg.de', '/debian/'),
      new Mirror('pubmirror.plutex.de', '/debian/'),
    ]),
    new Country('Greece', 'gr', [
      new Mirror('debian.otenet.gr', '/debian/'),
    ]),
    new Country('Hong Kong', 'hk', [
      new Mirror('ftp.hk.debian.org', '/debian/'),
      new Mirror('mirror.xtom.com.hk', '/debian/'),
    ]),
    new Country('Hungary', 'hu', [
      new Mirror('ftp.bme.hu', '/debian/'),
      new Mirror('repo.jztkft.hu', '/debian/'),
    ]),
    new Country('Iceland', 'is', [
      new Mirror('ftp.is.debian.org', '/debian/'),
    ]),
    new Country('Indonesia', 'id', [
      new Mirror('kartolo.sby.datautama.net.id', '/debian/'),
      new Mirror('mirror.unair.ac.id', '/debian/'),
      new Mirror('mr.heru.id', '/debian/'),
      new Mirror('werog.interkoneksimedia.co.id', '/debian/'),
    ]),
    new Country('Iran', 'ir', [
      new Mirror('archive.debian.petiak.ir', '/debian/'),
    ]),
    new Country('Israel', 'il', [
      new Mirror('debian.interhost.co.il', '/debian/'),
    ]),
    new Country('Italy', 'it', [
      new Mirror('ftp.it.debian.org', '/debian/'),
      new Mirror('debian.connesi.it', '/debian/'),
      new Mirror('debian.dynamica.it', '/debian/'),
      new Mirror('debian.mirror.garr.it', '/debian/'),
      new Mirror('ftp.linux.it', '/debian/'),
      new Mirror('giano.com.dist.unige.it', '/debian/'),
      new Mirror('mirror.units.it', '/debian/'),
    ]),
    new Country('Japan', 'jp', [
      new Mirror('ftp.maru.co.jp', '/debian/'),
      new Mirror('ftp.riken.jp', '/Linux/debian/debian/'),
      new Mirror('ftp.yz.yamagata-u.ac.jp', '/debian/'),
      new Mirror('mirrors.xtom.jp', '/debian/'),
    ]),
    new Country('Kazakhstan', 'kz', [
      new Mirror('mirror.hoster.kz', '/debian/'),
      new Mirror('mirror.ps.kz', '/debian/'),
    ]),
    new Country('Kenya', 'ke', [
      new Mirror('debian.mirror.ac.ke', '/debian/'),
      new Mirror('debian.mirror.liquidtelecom.com', '/debian/'),
    ]),
    new Country('Korea', 'kr', [
      new Mirror('ftp.kr.debian.org', '/debian/'),
      new Mirror('ftp.kaist.ac.kr', '/debian/'),
      new Mirror('ftp.lanet.kr', '/debian/'),
      new Mirror('mirror.pangkin.com', '/debian/'),
      new Mirror('mirror.siwoo.org', '/debian/'),
      new Mirror('mirror.yuki.net.uk', '/debian/'),
    ]),
    new Country('Latvia', 'lv', [
      new Mirror('debian.koyanet.lv', '/debian/'),
      new Mirror('ftp.linux.edu.lv', '/debian/'),
      new Mirror('mirror.cloudhosting.lv', '/debian/'),
      new Mirror('mirror.veesp.com', '/debian/'),
    ]),
    new Country('Lithuania', 'lt', [
      new Mirror('ftp.lt.debian.org', '/debian/'),
      new Mirror('debian.balt.net', '/debian/'),
      new Mirror('debian.mirror.vu.lt', '/debian/'),
      new Mirror('mirror.litnet.lt', '/debian/'),
      new Mirror('mirror.vpsnet.com', '/debian/'),
    ]),
    new Country('Macedonia, Republic of', 'mk', [
      new Mirror('mirror.a1.mk', '/debian/'),
    ]),
    new Country('Malaysia', 'my', [
      new Mirror('debian.tuxuri.com', '/debian/'),
    ]),
    new Country('Moldova', 'md', [
      new Mirror('ftp.md.debian.org', '/debian/'),
      new Mirror('mirror.as43289.net', '/debian/'),
    ]),
    new Country('Netherlands', 'nl', [
      new Mirror('ftp.nl.debian.org', '/debian/'),
      new Mirror('debian.snt.utwente.nl', '/debian/'),
      new Mirror('mirror.bgp.rodeo', '/debian/'),
      new Mirror('mirror.duocast.net', '/debian/'),
      new Mirror('mirror.nforce.com', '/debian/'),
      new Mirror('mirror.nl.cdn-perfprod.com', '/debian/'),
      new Mirror('mirror.nl.leaseweb.net', '/debian/'),
      new Mirror('mirror.nl.mirhosting.net', '/debian/'),
      new Mirror('mirrors.hostiserver.com', '/debian/'),
      new Mirror('mirrors.xtom.nl', '/debian/'),
      new Mirror('mirror.tngnet.com', '/debian/'),
      new Mirror('mirror.vpgrp.io', '/debian/'),
      new Mirror('nl.mirrors.clouvider.net', '/debian/'),
    ]),
    new Country('New Caledonia', 'nc', [
      new Mirror('ftp.nc.debian.org', '/debian/'),
      new Mirror('debian.nautile.nc', '/debian/'),
    ]),
    new Country('New Zealand', 'nz', [
      new Mirror('ftp.nz.debian.org', '/debian/'),
      new Mirror('linux.purple-cat.net', '/debian/'),
      new Mirror('mirror.fsmg.org.nz', '/debian/'),
    ]),
    new Country('Poland', 'pl', [
      new Mirror('ftp.pl.debian.org', '/debian/'),
      new Mirror('ftp.agh.edu.pl', '/debian/'),
      new Mirror('ftp.icm.edu.pl', '/pub/Linux/debian/'),
      new Mirror('ftp.psnc.pl', '/debian/'),
      new Mirror('ftp.task.gda.pl', '/debian/'),
    ]),
    new Country('Portugal', 'pt', [
      new Mirror('ftp.pt.debian.org', '/debian/'),
      new Mirror('debian.uevora.pt', '/debian/'),
      new Mirror('ftp.eq.uc.pt', '/software/Linux/debian/'),
      new Mirror('ftp.rnl.tecnico.ulisboa.pt', '/pub/debian/'),
      new Mirror('mirrors.ptisp.pt', '/debian/'),
      new Mirror('mirrors.up.pt', '/debian/'),
    ]),
    new Country('Reunion', 're', [
      new Mirror('debian.mithril.re', '/debian/'),
    ]),
    new Country('Romania', 'ro', [
      new Mirror('mirror.flo.c-f.ro', '/debian/'),
      new Mirror('mirror.linux.ro', '/debian/'),
      new Mirror('mirrors.datapark.ro', '/debian/'),
      new Mirror('mirrors.hosterion.ro', '/debian/'),
      new Mirror('mirrors.hostico.ro', '/debian/'),
      new Mirror('mirrors.nav.ro', '/debian/'),
      new Mirror('mirrors.nxthost.com', '/debian/'),
      new Mirror('mirrors.pidginhost.com', '/debian/'),
      new Mirror('ro.mirror.flokinet.net', '/debian/'),
    ]),
    new Country('Russia', 'ru', [
      new Mirror('ftp.ru.debian.org', '/debian/'),
      new Mirror('ftp.psn.ru', '/debian/'),
      new Mirror('mirror.corbina.net', '/debian/'),
      new Mirror('mirror.docker.ru', '/debian/'),
      new Mirror('mirror.kpfu.ru', '/debian/'),
      new Mirror('mirror.mephi.ru', '/debian/'),
      new Mirror('mirror.neftm.ru', '/debian/'),
      new Mirror('mirrors.powernet.com.ru', '/debian/'),
      new Mirror('mirror.truenetwork.ru', '/debian/'),
    ]),
    new Country('Saudi Arabia', 'sa', [
      new Mirror('mirror.maeen.sa', '/debian/'),
    ]),
    new Country('Serbia', 'rs', [
      new Mirror('mirror.pmf.kg.ac.rs', '/debian/'),
    ]),
    new Country('Singapore', 'sg', [
      new Mirror('mirror.djvg.sg', '/debian/'),
      new Mirror('mirror.sg.gs', '/debian/'),
      new Mirror('ossmirror.mycloud.services', '/debian/'),
    ]),
    new Country('Slovakia', 'sk', [
      new Mirror('ftp.sk.debian.org', '/debian/'),
      new Mirror('deb.bbxnet.sk', '/debian/'),
      new Mirror('ftp.antik.sk', '/debian/'),
      new Mirror('ftp.debian.sk', '/debian/'),
    ]),
    new Country('South Africa', 'za', [
      new Mirror('debian.as3741.net', '/debian/'),
      new Mirror('debian.envisagecloud.net.za', '/debian/'),
      new Mirror('debian.saix.net', '/'),
    ]),
    new Country('Spain', 'es', [
      new Mirror('debian.grn.cat', '/debian/'),
      new Mirror('debian.redimadrid.es', '/debian/'),
      new Mirror('ftp.cica.es', '/debian/'),
      new Mirror('mirror.raiolanetworks.com', '/debian/'),
      new Mirror('repo.ifca.es', '/debian/'),
      new Mirror('softlibre.unizar.es', '/debian/'),
    ]),
    new Country('Sweden', 'se', [
      new Mirror('debian.lth.se', '/debian/'),
      new Mirror('debian.mirror.su.se', '/debian/'),
      new Mirror('ftpmirror1.infania.net', '/debian/'),
      new Mirror('mirror.braindrainlan.nu', '/debian/'),
      new Mirror('mirrors.glesys.net', '/debian/'),
    ]),
    new Country('Switzerland', 'ch', [
      new Mirror('ftp.ch.debian.org', '/debian/'),
      new Mirror('debian.ethz.ch', '/debian/'),
      new Mirror('deb.nextgen.ch', '/debian/'),
      new Mirror('linuxsoft.cern.ch', '/debian/'),
      new Mirror('mirror1.infomaniak.com', '/debian/'),
      new Mirror('mirror2.infomaniak.com', '/debian/'),
      new Mirror('mirror.init7.net', '/debian/'),
      new Mirror('mirror.iway.ch', '/debian/'),
      new Mirror('mirror.metanet.ch', '/debian/'),
      new Mirror('mirror.sinavps.ch', '/debian/'),
      new Mirror('pkg.adfinis-on-exoscale.ch', '/debian/'),
    ]),
    new Country('Taiwan', 'tw', [
      new Mirror('ftp.tw.debian.org', '/debian/'),
      new Mirror('debian.ccns.ncku.edu.tw', '/debian/'),
      new Mirror('debian.csie.ntu.edu.tw', '/debian/'),
      new Mirror('debian.cs.nycu.edu.tw', '/debian/'),
      new Mirror('mirror.twds.com.tw', '/debian/'),
      new Mirror('opensource.nchc.org.tw', '/debian/'),
      new Mirror('tw1.mirror.blendbyte.net', '/debian/'),
    ]),
    new Country('Thailand', 'th', [
      new Mirror('ftp.debianclub.org', '/debian/'),
      new Mirror('mirror.applebred.net', '/debian/'),
      new Mirror('mirror.kku.ac.th', '/debian/'),
      new Mirror('mirrors.cloudforest.co.th', '/debian/'),
    ]),
    new Country('Turkey', 'tr', [
      new Mirror('ftp.tr.debian.org', '/debian/'),
      new Mirror('ftp.linux.org.tr', '/debian/'),
    ]),
    new Country('Ukraine', 'ua', [
      new Mirror('debian.volia.net', '/debian/'),
      new Mirror('distrohub.kyiv.ua', '/debian/'),
      new Mirror('mirror.mirohost.net', '/debian/'),
    ]),
    new Country('United Kingdom', 'uk', [
      new Mirror('ftp.uk.debian.org', '/debian/'),
      new Mirror('free.hands.com', '/debian/'),
      new Mirror('mirror.lchost.net', '/debian/'),
      new Mirror('mirror.mythic-beasts.com', '/debian/'),
      new Mirror('mirror.ox.ac.uk', '/debian/'),
      new Mirror('mirrors.coreix.net', '/debian/'),
      new Mirror('mirrorservice.org', '/sites/ftp.debian.org/debian/'),
      new Mirror('ukdebian.mirror.anlx.net', '/debian/'),
      new Mirror('uk.mirrors.clouvider.net', '/debian/'),
    ]),
    new Country('United States', 'us', [
      new Mirror('ftp.us.debian.org', '/debian/'),
      new Mirror('atl.mirrors.clouvider.net', '/debian/'),
      new Mirror('debian-archive.trafficmanager.net', '/debian/'),
      new Mirror('debian.csail.mit.edu', '/debian/'),
      new Mirror('debian.osuosl.org', '/debian/'),
      new Mirror('la.mirrors.clouvider.net', '/debian/'),
      new Mirror('lethe.chinstrap.org', '/debian/'),
      new Mirror('mirror.0x626b.com', '/debian/'),
      new Mirror('mirror.cogentco.com', '/debian/'),
      new Mirror('mirror.keystealth.org', '/debian/'),
      new Mirror('mirror.rustytel.net', '/debian/'),
      new Mirror('mirrors.accretive-networks.net', '/debian/'),
      new Mirror('mirror.siena.edu', '/debian/'),
      new Mirror('mirrors.lug.mtu.edu', '/debian/'),
      new Mirror('mirror.steadfast.net', '/debian/'),
      new Mirror('mirrors.vcea.wsu.edu', '/debian/'),
      new Mirror('mirrors.xtom.com', '/debian/'),
      new Mirror('mirror.tzulo.com', '/debian/'),
      new Mirror('mirror.us.leaseweb.net', '/debian/'),
      new Mirror('mirror.us.mirhosting.net', '/debian/'),
      new Mirror('mirror.us.oneandone.net', '/debian/'),
      new Mirror('nyc.mirrors.clouvider.net', '/debian/'),
      new Mirror('repo.ialab.dsu.edu', '/debian/'),
    ]),
    new Country('Uruguay', 'uy', [
      new Mirror('debian.repo.cure.edu.uy', '/debian/'),
      new Mirror('mirror.undernet.uy', '/debian/'),
    ]),
    new Country('Vietnam', 'vn', [
      new Mirror('debian.xtdv.net', '/debian/'),
      new Mirror('mirror.bizflycloud.vn', '/debian/'),
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
