<?php
define('ICON','icon.png');

define('SOURCE_KNOWN', 0);
define('SOURCE_CONFIG', 1);

if ( !isset($query) ) $query = @$argv[1];
if ( !isset($mode) ) $mode = "SSH";

$home = getenv('HOME');
$sshRoot = "$home/.ssh/";

$config = file("$sshRoot/config");
$knownHosts = file("$sshRoot/known_hosts");

$hosts = array();
foreach ( $knownHosts as $hostInfo ) {
  if ( preg_match('/^([^\s]+)/',$hostInfo,$m) == 1 ) {
    $a = explode(',',$m[1]);
    while ( $host = array_shift($a) ) $hosts[] = array('src' => SOURCE_KNOWN, 'val' => $host);
  }
}

foreach ( $config as $configLine ) {
  if ( preg_match('/^Host\s+([^\s]+)$/',$configLine,$m) == 1 ) {
    $host = preg_replace_callback('/\*(.*)/','replace_config_wildcard',$m[1],1);
    $hosts[] = array('src' => SOURCE_CONFIG, 'val' => $host);
  }
}

echo <<<EOF
<?xml version="1.0"?>
<items>
EOF;

$exactMatch = false;

usort($hosts,'sort_hosts');

foreach ( $hosts as $host ) {
  if ( strpos($host['val'],$query) !== false ) {
    item($host['val'],$mode);
    if ( !$exactMatch && $host == $query ) $exactMatch = true;
  }
}

if ( !$exactMatch )
  item($query,$mode);

echo <<<EOF
</items>
EOF;

function item($host,$mode) {
  $icon = ICON;
  echo <<<EOF
  <item uid="$host" arg="$host" valid="yes">
    <title>$mode to '$host'</title>
    <icon>$icon</icon>
  </item>
EOF;
}

function replace_config_wildcard($matches) {
  global $query;
  $ins = $query;  
  for ( $i=strlen($query); $i > 0; $i-- ) {
    if ( substr($query,-$i) == substr($matches[1],0,$i) ) {
      $ins = substr($ins,0,-$i);
      break;
    }
  }

  return $ins.$matches[1];
}

function sort_hosts($a, $b) {
  global $query;

  $av = $a['val'];
  $bv = $b['val'];

  $ai = strpos($av,$query);
  $bi = strpos($bv,$query);

  if ( $ai == $bi ) {
    $as = $a['src'];
    $bs = $b['src'];


    if ( $as == $bs ) {
      $al = strlen($av);
      $bl = strlen($bv);

      if ( $al == $bl ) return 0;
      else return ($al < $bl) ? -1 : 1;
    }
    else return ($as < $bs) ? -1 : 1;
  } else {
    return ($ai < $bi) ? -1 : 1;
  }
}