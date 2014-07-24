<?php

class Host {

  const SOURCE_UNKNOWN  = -1;
  const SOURCE_KNOWN    = 0;
  const SOURCE_CONFIG   = 1;

  const ICON = 'icon.png';

  private $_key;
  private $_host;
  private $_source;
  private $_subtitle;
  private $_overlap = false;

  public function __construct($host, $source=self::SOURCE_UNKNOWN, $key=false, $subtitle='') {

    /* Assign an incremental if none was provided */
    static $counter = 0;
    if ( $key === false ) $i = 'r'.($counter++);

    /* Set class properties */
    $this->_key      = $key;
    $this->_host     = $host;
    $this->_source   = $source;
    $this->_subtitle = $subtitle;
  }

  public function toXml() {
    $icon = self::ICON;
    $mode = Workflow::$mode;

    echo <<<EOF
<item uid="{$this->_host}" arg="{$this->_host}" valid="yes">
  <title>$mode to {$this->_host}</title>
  <icon>$icon</icon>
  <subtitle>{$this->_subtitle}</subtitle>
</item>
EOF;
  }

  public function getKey() { return $this->_key; }

  public function updateHostnameIf($hostname, $query, $key) {
    if ( $key != $this->_key ) return false;

    $host = str_replace('%h', $query, $hostname);

    if ( $host != $this->_host )
      $this->_subtitle = "'{$host}' {$this->_subtitle}";

    return true;
  }

  public function cmp($to,$query) {
    $av = $this->_host;
    $bv = $to->_host;

    $ai = strpos($av,$query);
    $bi = strpos($bv,$query);

    if ( $ai == $bi ) {

      $as = $this->_source;
      $bs = $to->_source;

      $ao = $this->_getOverlap($query);
      $bo = $to->_getOverlap($query);

      if ( $ao == $bo ) {
        if ( $as == $bs ) {
          $al = strlen($av);
          $bl = strlen($bv);

          if ( $al == $bl ) return 0;
          else return ($al < $bl) ? -1 : 1;
        }
        else return ($as < $bs) ? -1 : 1;
      } else {
        return ($ao > $bo) ? -1 : 1;
      }

    } else {
      return ($ai < $bi) ? -1 : 1;
    }
  }

  public function matches($query, &$exactMatch) {
    if ( strpos($this->_host,$query) !== false ) {
      if ( !$exactMatch && $this->_host == $query ) $exactMatch = true;

      return true;
    }
  }

  public function replaceWildcard($query) {
    $this->_tmpQuery = $query;
    return ($this->_host = preg_replace_callback('/\*(.*)/',array($this,'_replaceWildcardCallback'),$this->_host,1) );
  }

  protected function _replaceWildcardCallback($matches) {
    $query = $this->_tmpQuery;

    $this->_overlap = 0;
    $ins = $query;  
    for ( $i=strlen($query); $i > 0; $i-- ) {
      if ( substr($query,-$i) == substr($matches[1],0,$i) ) {
        $ins = substr($ins,0,-$i);
        $this->_overlap = $i;
        break;
      }
    }

    return $ins.$matches[1];
  }

  protected function _getOverlap($query) {
    if ( $this->_overlap !== false ) return $this->_overlap;
    //else if ( strpos($this->_host,$query) !== false ) return strlen($query);
    else return 0;
  }

}
