<?php
include('lib/Host.php');

class Workflow {
  public static $mode;
}

if ( !isset($query) ) $query = @$argv[1];
if ( !isset($mode) ) $mode = "SSH";

Workflow::$mode = $mode;

$home = getenv('HOME');
$sshRoot = "$home/.ssh/";

$config = file("$sshRoot/config");
$knownHosts = file("$sshRoot/known_hosts");

$hosts = array();
foreach ( $knownHosts as $i => $hostInfo ) {
  if ( preg_match('/^([^\s]+)/',$hostInfo,$m) == 1 ) {
    $a = explode(',',$m[1]);
    while ( $host = array_shift($a) )
      $hosts[] = new Host($host, Host::SOURCE_KNOWN, "k$i", 'via ~/.ssh/known_hosts');
  }
}

foreach ( $config as $i => $configLine ) {
  if ( preg_match('/^\s*host\s+(.+)\s+$/i',$configLine,$m) == 1 ) {
    $all = preg_split('/\s+/',$m[1]);
    $neg = array();

    foreach ( $all as $subhost ) {
      if ( $subhost[0] == '!' ) {
        $neg[] = substr($subhost,1);
      } else {
        $host = new Host($subhost, Host::SOURCE_CONFIG, "c$i", 'via ~/.ssh/config');
        $host->replaceWildcard($query);
        $hosts[] = $host;
      }
    }

    if ( count($neg) > 0 ) {
      $j = count($hosts)-1;

    }

  } else if ( preg_match('/^\s*hostname\s+([^\s]+)$/i',$configLine,$m) == 1 ) {
    $j = count($hosts)-1;
    $key = $hosts[$j]->getKey();

    while ( $j > 0 && $hosts[$j]->updateHostnameIf($m[1], $query, $key) ) { $j--; }
  }

}

echo <<<EOF
<?xml version="1.0"?>
<items>
EOF;

usort($hosts,'sort_hosts');

/* This value is updated by ref in $host->match() */
$exactMatch = false;

$c = 0;
foreach ( $hosts as $host ) {
  if ( $host->matches($query, $exactMatch) ) {
    echo $host->toXml();
    if ( ++$c == 8 ) break;
  }
}

if ( !$exactMatch ) {
  $wildcard = new Host($query);
  echo $wildcard->toXml();
}

echo <<<EOF
</items>
EOF;

function sort_hosts($a, $b) {
  global $query;
  return $a->cmp($b,$query);
}
