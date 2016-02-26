#!/usr/bin/php
<?php
/**
 * Aktualisiert anhand von Dukascopy-Daten die M1-History der angegebenen FX-Indizes und speichert sie im MyFX-Historyverzeichnis.
 *
 * Unterst�tzte Instrumente:
 *  � LFX-Indizes: LiteForex (gestauchte FX6-Indizes, au�er NZDLFX=NZDFX7)
 *  � FX6-Indizes: AUDFX6, CADFX6, CHFFX6, EURFX6, GBPFX6, JPYFX6, USDFX6
 *  � FX7-Indizes: AUDFX7, CADFX7, CHFFX7, EURFX7, GBPFX7, JPYFX7, USDFX7, NOKFX7, NZDFX7, SEKFX7, SGDFX7, ZARFX7
 *  � ICE-Indizes: EURX, USDX
 *
 *  TODO: AUDFX5, CADFX5, CHFFX5, EURFX5, GBPFX5, USDFX5 (ohne JPY)
 *        NOKFX6, SEKFX6, SGDFX6, ZARFX6                 (ohne JPY)
 *
 * @see  MetaTrader::indicators\LFX-Monitor.mq4
 */
require(dirName(realPath(__FILE__)).'/../../config.php');
date_default_timezone_set('GMT');


// -- Konfiguration --------------------------------------------------------------------------------------------------------------------------------


$verbose         = 0;                                       // output verbosity
$saveRawMyFXData = true;                                    // ob unkomprimierte MyFX-Historydaten gespeichert werden sollen


// Indizes und die zu ihrer Berechnung ben�tigten Instrumente
$indexes['AUDLFX'] = array('AUDUSD', 'EURUSD', 'GBPUSD', 'USDCAD', 'USDCHF', 'USDJPY');
$indexes['CADLFX'] = array('AUDUSD', 'EURUSD', 'GBPUSD', 'USDCAD', 'USDCHF', 'USDJPY');
$indexes['CHFLFX'] = array('AUDUSD', 'EURUSD', 'GBPUSD', 'USDCAD', 'USDCHF', 'USDJPY');
$indexes['EURLFX'] = array('AUDUSD', 'EURUSD', 'GBPUSD', 'USDCAD', 'USDCHF', 'USDJPY');
$indexes['GBPLFX'] = array('AUDUSD', 'EURUSD', 'GBPUSD', 'USDCAD', 'USDCHF', 'USDJPY');
$indexes['JPYLFX'] = array('AUDUSD', 'EURUSD', 'GBPUSD', 'USDCAD', 'USDCHF', 'USDJPY');
$indexes['NZDLFX'] = array('AUDUSD', 'EURUSD', 'GBPUSD', 'USDCAD', 'USDCHF', 'USDJPY', 'NZDUSD');
$indexes['USDLFX'] = array('AUDUSD', 'EURUSD', 'GBPUSD', 'USDCAD', 'USDCHF', 'USDJPY');

$indexes['AUDFX6'] = array('AUDUSD', 'EURUSD', 'GBPUSD', 'USDCAD', 'USDCHF', 'USDJPY');
$indexes['CADFX6'] = array('AUDUSD', 'EURUSD', 'GBPUSD', 'USDCAD', 'USDCHF', 'USDJPY');
$indexes['CHFFX6'] = array('AUDUSD', 'EURUSD', 'GBPUSD', 'USDCAD', 'USDCHF', 'USDJPY');
$indexes['EURFX6'] = array('AUDUSD', 'EURUSD', 'GBPUSD', 'USDCAD', 'USDCHF', 'USDJPY');
$indexes['GBPFX6'] = array('AUDUSD', 'EURUSD', 'GBPUSD', 'USDCAD', 'USDCHF', 'USDJPY');
$indexes['JPYFX6'] = array('AUDUSD', 'EURUSD', 'GBPUSD', 'USDCAD', 'USDCHF', 'USDJPY');
$indexes['USDFX6'] = array('AUDUSD', 'EURUSD', 'GBPUSD', 'USDCAD', 'USDCHF', 'USDJPY');

$indexes['AUDFX7'] = array('AUDUSD', 'EURUSD', 'GBPUSD', 'USDCAD', 'USDCHF', 'USDJPY', 'NZDUSD');
$indexes['CADFX7'] = array('AUDUSD', 'EURUSD', 'GBPUSD', 'USDCAD', 'USDCHF', 'USDJPY', 'NZDUSD');
$indexes['CHFFX7'] = array('AUDUSD', 'EURUSD', 'GBPUSD', 'USDCAD', 'USDCHF', 'USDJPY', 'NZDUSD');
$indexes['EURFX7'] = array('AUDUSD', 'EURUSD', 'GBPUSD', 'USDCAD', 'USDCHF', 'USDJPY', 'NZDUSD');
$indexes['GBPFX7'] = array('AUDUSD', 'EURUSD', 'GBPUSD', 'USDCAD', 'USDCHF', 'USDJPY', 'NZDUSD');
$indexes['JPYFX7'] = array('AUDUSD', 'EURUSD', 'GBPUSD', 'USDCAD', 'USDCHF', 'USDJPY', 'NZDUSD');
$indexes['USDFX7'] = array('AUDUSD', 'EURUSD', 'GBPUSD', 'USDCAD', 'USDCHF', 'USDJPY', 'NZDUSD');
$indexes['NOKFX7'] = array('AUDUSD', 'EURUSD', 'GBPUSD', 'USDCAD', 'USDCHF', 'USDJPY', 'USDNOK');
$indexes['NZDFX7'] = array('AUDUSD', 'EURUSD', 'GBPUSD', 'USDCAD', 'USDCHF', 'USDJPY', 'NZDUSD');
$indexes['SEKFX7'] = array('AUDUSD', 'EURUSD', 'GBPUSD', 'USDCAD', 'USDCHF', 'USDJPY', 'USDSEK');
$indexes['SGDFX7'] = array('AUDUSD', 'EURUSD', 'GBPUSD', 'USDCAD', 'USDCHF', 'USDJPY', 'USDSGD');
$indexes['ZARFX7'] = array('AUDUSD', 'EURUSD', 'GBPUSD', 'USDCAD', 'USDCHF', 'USDJPY', 'USDZAR');

$indexes['EURX'  ] = array('EURUSD', 'GBPUSD', 'USDCHF', 'USDJPY', 'USDSEK');
$indexes['USDX'  ] = array('EURUSD', 'GBPUSD', 'USDCAD', 'USDCHF', 'USDJPY', 'USDSEK');


// -- Start ----------------------------------------------------------------------------------------------------------------------------------------


// (1) Befehlszeilenargumente einlesen und validieren
$args = array_slice($_SERVER['argv'], 1);

// Optionen parsen
foreach ($args as $i => $arg) {
   if ($arg == '-h'  )   help() & exit(1);                                          // Hilfe
   if ($arg == '-v'  ) { $verbose = max($verbose, 1); unset($args[$i]); continue; } // verbose output
   if ($arg == '-vv' ) { $verbose = max($verbose, 2); unset($args[$i]); continue; } // more verbose output
   if ($arg == '-vvv') { $verbose = max($verbose, 3); unset($args[$i]); continue; } // very verbose output
}

// Symbole parsen
foreach ($args as $i => $arg) {
   $arg = strToUpper($arg);
   if (!array_key_exists($arg, $indexes)) help('error: unknown or unsupported index "'.$args[$i].'"') & exit(1);
   $args[$i] = $arg;
}
$args = $args ? array_unique($args) : array_keys($indexes);                         // ohne Angabe werden alle Indizes aktualisiert


// (2) Indizes berechnen
foreach ($args as $index) {
   if (!updateIndex($index))
      exit(1);
}
exit(0);


// --- Funktionen ----------------------------------------------------------------------------------------------------------------------------------


/**
 * Aktualisiert die M1-History eines Indexes (MyFX-Format).
 *
 * @param  string $index - Indexsymbol
 *
 * @return bool - Erfolgsstatus
 */
function updateIndex($index) {
   if (!is_string($index)) throw new IllegalTypeException('Illegal type of parameter $index: '.getType($index));
   if (!strLen($index))    throw new plInvalidArgumentException('Invalid parameter $index: ""');

   global $verbose, $indexes, $saveRawMyFXData;

   // (1) Starttag der ben�tigten Daten ermitteln
   $startTime = 0;
   $pairs = array_flip($indexes[$index]);                                     // array('AUDUSD', ...) => array('AUDUSD'=>null, ...)
   foreach($pairs as $pair => &$data) {
      $data      = array();                                                   // $data initialisieren: array('AUDUSD'=>[], ...)
      $startTime = max($startTime, Dukascopy::$historyStart_M1[$pair]);       // GMT-Timestamp
   }
   $startTime = fxtTime($startTime);                                          // FXT-Timestamp
   $startDay  = $startTime - $startTime%DAY;                                  // 00:00 Starttag FXT
   $today     = ($today=fxtTime()) - $today%DAY;                              // 00:00 aktueller Tag FXT


   // (2) Gesamte Zeitspanne tageweise durchlaufen
   for ($day=$startDay, $lastMonth=-1; $day < $today; $day+=1*DAY) {

      if (!MyFX::isForexWeekend($day, 'FXT')) {                               // au�er an Wochenenden
         $shortDate = gmDate('D, d-M-Y', $day);

         // Pr�fen, ob die History bereits existiert
         if (is_file($file=getVar('myfxTarget.compressed', $index, $day))) {
            if ($verbose > 1) echoPre('[Ok]    '.$shortDate.'   '.$index.' compressed history file: '.baseName($file));
         }
         else if (is_file($file=getVar('myfxTarget.raw', $index, $day))) {
            if ($verbose > 1) echoPre('[Ok]    '.$shortDate.'   '.$index.' raw history file: '.baseName($file));
         }
         else {
            $month = (int) gmDate('m', $day);
            if ($month != $lastMonth) {
               echoPre('[Info]    '.$index.' '.gmDate('M-Y', $day));
               $lastMonth = $month;
            }

            // History aktualisieren: M1-Bars der ben�tigten Instrumente dieses Tages einlesen
            foreach($pairs as $pair => $data) {
               if      (is_file($file=getVar('myfxSource.compressed', $pair, $day))) {}   // komprimierte oder
               else if (is_file($file=getVar('myfxSource.raw'       , $pair, $day))) {}   // unkomprimierte MyFX-Datei
               else {
                  echoPre('[Error]   '.$pair.' history for '.$shortDate.' not found');
                  return false;
               }
               // M1-Bars zwischenspeichern
               $pairs[$pair]['bars'] = MyFX::readBarFile($file);                          // array('AUDUSD'=>array('bars'=>[]), ...)
            }

            // Indexdaten f�r diesen Tag berechnen
            $function = 'calculate'.$index;
            $ixBars   = $function($day, $pairs); if (!$ixBars) return false;

            // Indexdaten speichern
            if (!saveBars($index, $day, $ixBars)) return false;
         }
      }
   }
   echoPre('[Ok]      '.$index);
   return true;
}


/**
 * Berechnet f�r die �bergebenen M1-Daten den AUDFX6-Index.
 *
 * @param  int   $day  - FXT-Timestamp des Tages der zu berechnenden Daten
 * @param  array $data - M1-Bars dieses Tages aller f�r den Index ben�tigten Instrumente
 *
 * @return MYFX_BAR[] - Array mit den resultierenden M1-Indexdaten
 *
 * Formel: AUDFX6 = ((AUDCAD * AUDCHF * AUDJPY * AUDUSD) / (EURAUD * GBPAUD)) ^ 1/6
 */
function calculateAUDFX6($day, array $data) {
   if (!is_int($day)) throw new IllegalTypeException('Illegal type of parameter $day: '.getType($day));
   $shortDate = gmDate('D, d-M-Y', $day);

   global $verbose;
   if ($verbose > 1) echoPre('[Info]    AUDFX6  '.$shortDate);

   $AUDUSD = $data['AUDUSD']['bars'];
   $EURUSD = $data['EURUSD']['bars'];
   $GBPUSD = $data['GBPUSD']['bars'];
   $USDCAD = $data['USDCAD']['bars'];
   $USDCHF = $data['USDCHF']['bars'];
   $USDJPY = $data['USDJPY']['bars'];
   $index  = array();

   foreach ($AUDUSD as $i => $bar) {
      $audusd = $AUDUSD[$i]['open'];
      $eurusd = $EURUSD[$i]['open'];
      $gbpusd = $GBPUSD[$i]['open'];
      $usdcad = $USDCAD[$i]['open'];
      $usdchf = $USDCHF[$i]['open'];
      $usdjpy = $USDJPY[$i]['open'];
      $open   = pow(($usdcad/$eurusd) * ($usdchf/$gbpusd) * ($usdjpy/1000), 1/6) * $audusd;
      $iOpen  = round($open);

      $audusd = $AUDUSD[$i]['close'];
      $eurusd = $EURUSD[$i]['close'];
      $gbpusd = $GBPUSD[$i]['close'];
      $usdcad = $USDCAD[$i]['close'];
      $usdchf = $USDCHF[$i]['close'];
      $usdjpy = $USDJPY[$i]['close'];
      $close  = pow(($usdcad/$eurusd) * ($usdchf/$gbpusd) * ($usdjpy/1000), 1/6) * $audusd;
      $iClose = round($close);

      $index[$i]['time' ] = $bar['time'];
      $index[$i]['open' ] = $iOpen;
      $index[$i]['high' ] = max($iOpen, $iClose);
      $index[$i]['low'  ] = min($iOpen, $iClose);
      $index[$i]['close'] = $iClose;
      $index[$i]['ticks'] = abs($iOpen-$iClose) << 1;
   }
   return $index;
}


/**
 * Berechnet f�r die �bergebenen M1-Daten den AUDFX7-Index.
 *
 * @param  int   $day  - FXT-Timestamp des Tages der zu berechnenden Daten
 * @param  array $data - M1-Bars dieses Tages aller f�r den Index ben�tigten Instrumente
 *
 * @return MYFX_BAR[] - Array mit den resultierenden M1-Indexdaten
 *
 * Formel: AUDFX7 = ((AUDCAD * AUDCHF * AUDJPY * AUDNZD * AUDUSD ) / (EURAUD * GBPAUD)) ^ 1/7
 */
function calculateAUDFX7($day, array $data) {
   if (!is_int($day)) throw new IllegalTypeException('Illegal type of parameter $day: '.getType($day));
   $shortDate = gmDate('D, d-M-Y', $day);

   global $verbose;
   if ($verbose > 1) echoPre('[Info]    AUDFX7  '.$shortDate);

   $AUDUSD = $data['AUDUSD']['bars'];
   $EURUSD = $data['EURUSD']['bars'];
   $GBPUSD = $data['GBPUSD']['bars'];
   $NZDUSD = $data['NZDUSD']['bars'];
   $USDCAD = $data['USDCAD']['bars'];
   $USDCHF = $data['USDCHF']['bars'];
   $USDJPY = $data['USDJPY']['bars'];
   $index  = array();

   foreach ($AUDUSD as $i => $bar) {
      $audusd = $AUDUSD[$i]['open'];
      $eurusd = $EURUSD[$i]['open'];
      $gbpusd = $GBPUSD[$i]['open'];
      $nzdusd = $NZDUSD[$i]['open'];
      $usdcad = $USDCAD[$i]['open'];
      $usdchf = $USDCHF[$i]['open'];
      $usdjpy = $USDJPY[$i]['open'];
      $open   = pow(($usdcad/$eurusd) * ($usdchf/$gbpusd) * ($usdjpy/$nzdusd), 1/7) * $audusd;
      $iOpen  = round($open);

      $audusd = $AUDUSD[$i]['close'];
      $eurusd = $EURUSD[$i]['close'];
      $gbpusd = $GBPUSD[$i]['close'];
      $nzdusd = $NZDUSD[$i]['close'];
      $usdcad = $USDCAD[$i]['close'];
      $usdchf = $USDCHF[$i]['close'];
      $usdjpy = $USDJPY[$i]['close'];
      $close  = pow(($usdcad/$eurusd) * ($usdchf/$gbpusd) * ($usdjpy/$nzdusd), 1/7) * $audusd;
      $iClose = round($close);

      $index[$i]['time' ] = $bar['time'];
      $index[$i]['open' ] = $iOpen;
      $index[$i]['high' ] = max($iOpen, $iClose);
      $index[$i]['low'  ] = min($iOpen, $iClose);
      $index[$i]['close'] = $iClose;
      $index[$i]['ticks'] = abs($iOpen-$iClose) << 1;
   }
   return $index;
}


/**
 * Berechnet f�r die �bergebenen M1-Daten den AUDLFX-Index.
 *
 * @param  int   $day  - FXT-Timestamp des Tages der zu berechnenden Daten
 * @param  array $data - M1-Bars dieses Tages aller f�r den Index ben�tigten Instrumente
 *
 * @return MYFX_BAR[] - Array mit den resultierenden M1-Indexdaten
 *
 * Formel: AUDLFX = ((AUDCAD * AUDCHF * AUDJPY * AUDUSD) / (EURAUD * GBPAUD)) ^ 1/7
 *   oder: AUDLFX = USDLFX * AUDUSD
 */
function calculateAUDLFX($day, array $data) {
   if (!is_int($day)) throw new IllegalTypeException('Illegal type of parameter $day: '.getType($day));
   $shortDate = gmDate('D, d-M-Y', $day);

   global $verbose;
   if ($verbose > 1) echoPre('[Info]    AUDLFX  '.$shortDate);

   $AUDUSD = $data['AUDUSD']['bars'];
   $EURUSD = $data['EURUSD']['bars'];
   $GBPUSD = $data['GBPUSD']['bars'];
   $USDCAD = $data['USDCAD']['bars'];
   $USDCHF = $data['USDCHF']['bars'];
   $USDJPY = $data['USDJPY']['bars'];
   $index  = array();

   foreach ($AUDUSD as $i => $bar) {
      $audusd = $AUDUSD[$i]['open'];
      $eurusd = $EURUSD[$i]['open'];
      $gbpusd = $GBPUSD[$i]['open'];
      $usdcad = $USDCAD[$i]['open'];
      $usdchf = $USDCHF[$i]['open'];
      $usdjpy = $USDJPY[$i]['open'];
      $open   = pow(($usdcad/$audusd) * ($usdchf/$eurusd) * ($usdjpy/$gbpusd) * 100, 1/7) * $audusd;
      $iOpen  = round($open);

      $audusd = $AUDUSD[$i]['close'];
      $eurusd = $EURUSD[$i]['close'];
      $gbpusd = $GBPUSD[$i]['close'];
      $usdcad = $USDCAD[$i]['close'];
      $usdchf = $USDCHF[$i]['close'];
      $usdjpy = $USDJPY[$i]['close'];
      $close  = pow(($usdcad/$audusd) * ($usdchf/$eurusd) * ($usdjpy/$gbpusd) * 100, 1/7) * $audusd;
      $iClose = round($close);

      $index[$i]['time' ] = $bar['time'];
      $index[$i]['open' ] = $iOpen;
      $index[$i]['high' ] = max($iOpen, $iClose);
      $index[$i]['low'  ] = min($iOpen, $iClose);
      $index[$i]['close'] = $iClose;
      $index[$i]['ticks'] = abs($iOpen-$iClose) << 1;
   }
   return $index;
}


/**
 * Berechnet f�r die �bergebenen M1-Daten den CADFX6-Index.
 *
 * @param  int   $day  - FXT-Timestamp des Tages der zu berechnenden Daten
 * @param  array $data - M1-Bars dieses Tages aller f�r den Index ben�tigten Instrumente
 *
 * @return MYFX_BAR[] - Array mit den resultierenden M1-Indexdaten
 *
 * Formel: CADFX6 = ((CADCHF * CADJPY) / (AUDCAD * EURCAD * GBPCAD * USDCAD)) ^ 1/6
 */
function calculateCADFX6($day, array $data) {
   if (!is_int($day)) throw new IllegalTypeException('Illegal type of parameter $day: '.getType($day));
   $shortDate = gmDate('D, d-M-Y', $day);

   global $verbose;
   if ($verbose > 1) echoPre('[Info]    CADFX6  '.$shortDate);

   $AUDUSD = $data['AUDUSD']['bars'];
   $EURUSD = $data['EURUSD']['bars'];
   $GBPUSD = $data['GBPUSD']['bars'];
   $USDCAD = $data['USDCAD']['bars'];
   $USDCHF = $data['USDCHF']['bars'];
   $USDJPY = $data['USDJPY']['bars'];
   $index  = array();

   foreach ($AUDUSD as $i => $bar) {
      $audusd = $AUDUSD[$i]['open'];
      $eurusd = $EURUSD[$i]['open'];
      $gbpusd = $GBPUSD[$i]['open'];
      $usdcad = $USDCAD[$i]['open'];
      $usdchf = $USDCHF[$i]['open'];
      $usdjpy = $USDJPY[$i]['open'];
      $open   = pow(($usdchf/$audusd) * ($usdjpy/$eurusd) * (100000/$gbpusd) * 100, 1/6) / $usdcad * 100000;
      $iOpen  = round($open * 100000);

      $audusd = $AUDUSD[$i]['close'];
      $eurusd = $EURUSD[$i]['close'];
      $gbpusd = $GBPUSD[$i]['close'];
      $usdcad = $USDCAD[$i]['close'];
      $usdchf = $USDCHF[$i]['close'];
      $usdjpy = $USDJPY[$i]['close'];
      $close  = pow(($usdchf/$audusd) * ($usdjpy/$eurusd) * (100000/$gbpusd) * 100, 1/6) / $usdcad * 100000;
      $iClose = round($close * 100000);

      $index[$i]['time' ] = $bar['time'];
      $index[$i]['open' ] = $iOpen;
      $index[$i]['high' ] = max($iOpen, $iClose);
      $index[$i]['low'  ] = min($iOpen, $iClose);
      $index[$i]['close'] = $iClose;
      $index[$i]['ticks'] = abs($iOpen-$iClose) << 1;
   }
   return $index;
}


/**
 * Berechnet f�r die �bergebenen M1-Daten den CADFX7-Index.
 *
 * @param  int   $day  - FXT-Timestamp des Tages der zu berechnenden Daten
 * @param  array $data - M1-Bars dieses Tages aller f�r den Index ben�tigten Instrumente
 *
 * @return MYFX_BAR[] - Array mit den resultierenden M1-Indexdaten
 *
 * Formel: CADFX7 = ((CADCHF * CADJPY) / (AUDCAD * EURCAD * GBPCAD * NZDCAD * USDCAD)) ^ 1/7
 */
function calculateCADFX7($day, array $data) {
   if (!is_int($day)) throw new IllegalTypeException('Illegal type of parameter $day: '.getType($day));
   $shortDate = gmDate('D, d-M-Y', $day);

   global $verbose;
   if ($verbose > 1) echoPre('[Info]    CADFX7  '.$shortDate);

   $AUDUSD = $data['AUDUSD']['bars'];
   $EURUSD = $data['EURUSD']['bars'];
   $GBPUSD = $data['GBPUSD']['bars'];
   $NZDUSD = $data['NZDUSD']['bars'];
   $USDCAD = $data['USDCAD']['bars'];
   $USDCHF = $data['USDCHF']['bars'];
   $USDJPY = $data['USDJPY']['bars'];
   $index  = array();

   foreach ($AUDUSD as $i => $bar) {
      $audusd = $AUDUSD[$i]['open'];
      $eurusd = $EURUSD[$i]['open'];
      $gbpusd = $GBPUSD[$i]['open'];
      $nzdusd = $NZDUSD[$i]['open'];
      $usdcad = $USDCAD[$i]['open'];
      $usdchf = $USDCHF[$i]['open'];
      $usdjpy = $USDJPY[$i]['open'];
      $open   = pow(($usdchf/$audusd) * ($usdjpy/$eurusd) * (100000/$gbpusd) * (100000/$nzdusd) * 100, 1/7) / $usdcad * 100000;
      $iOpen  = round($open * 100000);

      $audusd = $AUDUSD[$i]['close'];
      $eurusd = $EURUSD[$i]['close'];
      $gbpusd = $GBPUSD[$i]['close'];
      $nzdusd = $NZDUSD[$i]['close'];
      $usdcad = $USDCAD[$i]['close'];
      $usdchf = $USDCHF[$i]['close'];
      $usdjpy = $USDJPY[$i]['close'];
      $close  = pow(($usdchf/$audusd) * ($usdjpy/$eurusd) * (100000/$gbpusd) * (100000/$nzdusd) * 100, 1/7) / $usdcad * 100000;
      $iClose = round($close * 100000);

      $index[$i]['time' ] = $bar['time'];
      $index[$i]['open' ] = $iOpen;
      $index[$i]['high' ] = max($iOpen, $iClose);
      $index[$i]['low'  ] = min($iOpen, $iClose);
      $index[$i]['close'] = $iClose;
      $index[$i]['ticks'] = abs($iOpen-$iClose) << 1;
   }
   return $index;
}


/**
 * Berechnet f�r die �bergebenen M1-Daten den CADLFX-Index.
 *
 * @param  int   $day  - FXT-Timestamp des Tages der zu berechnenden Daten
 * @param  array $data - M1-Bars dieses Tages aller f�r den Index ben�tigten Instrumente
 *
 * @return MYFX_BAR[] - Array mit den resultierenden M1-Indexdaten
 *
 * Formel: CADLFX = ((CADCHF * CADJPY) / (AUDCAD * EURCAD * GBPCAD * USDCAD)) ^ 1/7
 *   oder: CADLFX = USDLFX / USDCAD
 */
function calculateCADLFX($day, array $data) {
   if (!is_int($day)) throw new IllegalTypeException('Illegal type of parameter $day: '.getType($day));
   $shortDate = gmDate('D, d-M-Y', $day);

   global $verbose;
   if ($verbose > 1) echoPre('[Info]    CADLFX  '.$shortDate);

   $AUDUSD = $data['AUDUSD']['bars'];
   $EURUSD = $data['EURUSD']['bars'];
   $GBPUSD = $data['GBPUSD']['bars'];
   $USDCAD = $data['USDCAD']['bars'];
   $USDCHF = $data['USDCHF']['bars'];
   $USDJPY = $data['USDJPY']['bars'];
   $index  = array();

   foreach ($AUDUSD as $i => $bar) {
      $audusd = $AUDUSD[$i]['open'];
      $eurusd = $EURUSD[$i]['open'];
      $gbpusd = $GBPUSD[$i]['open'];
      $usdcad = $USDCAD[$i]['open'];
      $usdchf = $USDCHF[$i]['open'];
      $usdjpy = $USDJPY[$i]['open'];
      $open   = pow(($usdcad/$audusd) * ($usdchf/$eurusd) * ($usdjpy/$gbpusd) * 100, 1/7) / $usdcad * 100000;
      $iOpen  = round($open * 100000);

      $audusd = $AUDUSD[$i]['close'];
      $eurusd = $EURUSD[$i]['close'];
      $gbpusd = $GBPUSD[$i]['close'];
      $usdcad = $USDCAD[$i]['close'];
      $usdchf = $USDCHF[$i]['close'];
      $usdjpy = $USDJPY[$i]['close'];
      $close  = pow(($usdcad/$audusd) * ($usdchf/$eurusd) * ($usdjpy/$gbpusd) * 100, 1/7) / $usdcad * 100000;
      $iClose = round($close * 100000);

      $index[$i]['time' ] = $bar['time'];
      $index[$i]['open' ] = $iOpen;
      $index[$i]['high' ] = max($iOpen, $iClose);
      $index[$i]['low'  ] = min($iOpen, $iClose);
      $index[$i]['close'] = $iClose;
      $index[$i]['ticks'] = abs($iOpen-$iClose) << 1;
   }
   return $index;
}


/**
 * Berechnet f�r die �bergebenen M1-Daten den CHFFX6-Index.
 *
 * @param  int   $day  - FXT-Timestamp des Tages der zu berechnenden Daten
 * @param  array $data - M1-Bars dieses Tages aller f�r den Index ben�tigten Instrumente
 *
 * @return MYFX_BAR[] - Array mit den resultierenden M1-Indexdaten
 *
 * Formel: CHFFX6 = (CHFJPY / (AUDCHF * CADCHF * EURCHF * GBPCHF * USDCHF)) ^ 1/6
 */
function calculateCHFFX6($day, array $data) {
   if (!is_int($day)) throw new IllegalTypeException('Illegal type of parameter $day: '.getType($day));
   $shortDate = gmDate('D, d-M-Y', $day);

   global $verbose;
   if ($verbose > 1) echoPre('[Info]    CHFFX6  '.$shortDate);

   $AUDUSD = $data['AUDUSD']['bars'];
   $EURUSD = $data['EURUSD']['bars'];
   $GBPUSD = $data['GBPUSD']['bars'];
   $USDCAD = $data['USDCAD']['bars'];
   $USDCHF = $data['USDCHF']['bars'];
   $USDJPY = $data['USDJPY']['bars'];
   $index  = array();

   foreach ($AUDUSD as $i => $bar) {
      $audusd = $AUDUSD[$i]['open'];
      $eurusd = $EURUSD[$i]['open'];
      $gbpusd = $GBPUSD[$i]['open'];
      $usdcad = $USDCAD[$i]['open'];
      $usdchf = $USDCHF[$i]['open'];
      $usdjpy = $USDJPY[$i]['open'];
      $open   = pow(($usdcad/$audusd) * ($usdjpy/$eurusd) * (100000/$gbpusd) * 100, 1/6) / $usdchf * 100000;
      $iOpen  = round($open * 100000);

      $audusd = $AUDUSD[$i]['close'];
      $eurusd = $EURUSD[$i]['close'];
      $gbpusd = $GBPUSD[$i]['close'];
      $usdcad = $USDCAD[$i]['close'];
      $usdchf = $USDCHF[$i]['close'];
      $usdjpy = $USDJPY[$i]['close'];
      $close  = pow(($usdcad/$audusd) * ($usdjpy/$eurusd) * (100000/$gbpusd) * 100, 1/6) / $usdchf * 100000;
      $iClose = round($close * 100000);

      $index[$i]['time' ] = $bar['time'];
      $index[$i]['open' ] = $iOpen;
      $index[$i]['high' ] = max($iOpen, $iClose);
      $index[$i]['low'  ] = min($iOpen, $iClose);
      $index[$i]['close'] = $iClose;
      $index[$i]['ticks'] = abs($iOpen-$iClose) << 1;
   }
   return $index;
}


/**
 * Berechnet f�r die �bergebenen M1-Daten den CHFFX7-Index.
 *
 * @param  int   $day  - FXT-Timestamp des Tages der zu berechnenden Daten
 * @param  array $data - M1-Bars dieses Tages aller f�r den Index ben�tigten Instrumente
 *
 * @return MYFX_BAR[] - Array mit den resultierenden M1-Indexdaten
 *
 * Formel: CHFFX7 = (CHFJPY / (AUDCHF * CADCHF * EURCHF * GBPCHF * NZDCHF * USDCHF)) ^ 1/7
 */
function calculateCHFFX7($day, array $data) {
   if (!is_int($day)) throw new IllegalTypeException('Illegal type of parameter $day: '.getType($day));
   $shortDate = gmDate('D, d-M-Y', $day);

   global $verbose;
   if ($verbose > 1) echoPre('[Info]    CHFFX7  '.$shortDate);

   $AUDUSD = $data['AUDUSD']['bars'];
   $EURUSD = $data['EURUSD']['bars'];
   $GBPUSD = $data['GBPUSD']['bars'];
   $NZDUSD = $data['NZDUSD']['bars'];
   $USDCAD = $data['USDCAD']['bars'];
   $USDCHF = $data['USDCHF']['bars'];
   $USDJPY = $data['USDJPY']['bars'];
   $index  = array();

   foreach ($AUDUSD as $i => $bar) {
      $audusd = $AUDUSD[$i]['open'];
      $eurusd = $EURUSD[$i]['open'];
      $gbpusd = $GBPUSD[$i]['open'];
      $nzdusd = $NZDUSD[$i]['open'];
      $usdcad = $USDCAD[$i]['open'];
      $usdchf = $USDCHF[$i]['open'];
      $usdjpy = $USDJPY[$i]['open'];
      $open   = pow(($usdcad/$audusd) * ($usdjpy/$eurusd) * (100000/$gbpusd) * (100000/$nzdusd) * 100, 1/7) / $usdchf * 100000;
      $iOpen  = round($open * 100000);

      $audusd = $AUDUSD[$i]['close'];
      $eurusd = $EURUSD[$i]['close'];
      $gbpusd = $GBPUSD[$i]['close'];
      $nzdusd = $NZDUSD[$i]['close'];
      $usdcad = $USDCAD[$i]['close'];
      $usdchf = $USDCHF[$i]['close'];
      $usdjpy = $USDJPY[$i]['close'];
      $close  = pow(($usdcad/$audusd) * ($usdjpy/$eurusd) * (100000/$gbpusd) * (100000/$nzdusd) * 100, 1/7) / $usdchf * 100000;
      $iClose = round($close * 100000);

      $index[$i]['time' ] = $bar['time'];
      $index[$i]['open' ] = $iOpen;
      $index[$i]['high' ] = max($iOpen, $iClose);
      $index[$i]['low'  ] = min($iOpen, $iClose);
      $index[$i]['close'] = $iClose;
      $index[$i]['ticks'] = abs($iOpen-$iClose) << 1;
   }
   return $index;
}


/**
 * Berechnet f�r die �bergebenen M1-Daten den CHFLFX-Index.
 *
 * @param  int   $day  - FXT-Timestamp des Tages der zu berechnenden Daten
 * @param  array $data - M1-Bars dieses Tages aller f�r den Index ben�tigten Instrumente
 *
 * @return MYFX_BAR[] - Array mit den resultierenden M1-Indexdaten
 *
 * Formel: CHFLFX = (CHFJPY / (AUDCHF * CADCHF * EURCHF * GBPCHF * USDCHF)) ^ 1/7
 *   oder: CHFLFX = UDLFX / USDCHF
 */
function calculateCHFLFX($day, array $data) {
   if (!is_int($day)) throw new IllegalTypeException('Illegal type of parameter $day: '.getType($day));
   $shortDate = gmDate('D, d-M-Y', $day);

   global $verbose;
   if ($verbose > 1) echoPre('[Info]    CHFLFX  '.$shortDate);

   $AUDUSD = $data['AUDUSD']['bars'];
   $EURUSD = $data['EURUSD']['bars'];
   $GBPUSD = $data['GBPUSD']['bars'];
   $USDCAD = $data['USDCAD']['bars'];
   $USDCHF = $data['USDCHF']['bars'];
   $USDJPY = $data['USDJPY']['bars'];
   $index  = array();

   foreach ($AUDUSD as $i => $bar) {
      $audusd = $AUDUSD[$i]['open'];
      $eurusd = $EURUSD[$i]['open'];
      $gbpusd = $GBPUSD[$i]['open'];
      $usdcad = $USDCAD[$i]['open'];
      $usdchf = $USDCHF[$i]['open'];
      $usdjpy = $USDJPY[$i]['open'];
      $open   = pow(($usdcad/$audusd) * ($usdchf/$eurusd) * ($usdjpy/$gbpusd) * 100, 1/7) / $usdchf * 100000;
      $iOpen  = round($open * 100000);

      $audusd = $AUDUSD[$i]['close'];
      $eurusd = $EURUSD[$i]['close'];
      $gbpusd = $GBPUSD[$i]['close'];
      $usdcad = $USDCAD[$i]['close'];
      $usdchf = $USDCHF[$i]['close'];
      $usdjpy = $USDJPY[$i]['close'];
      $close  = pow(($usdcad/$audusd) * ($usdchf/$eurusd) * ($usdjpy/$gbpusd) * 100, 1/7) / $usdchf * 100000;
      $iClose = round($close * 100000);

      $index[$i]['time' ] = $bar['time'];
      $index[$i]['open' ] = $iOpen;
      $index[$i]['high' ] = max($iOpen, $iClose);
      $index[$i]['low'  ] = min($iOpen, $iClose);
      $index[$i]['close'] = $iClose;
      $index[$i]['ticks'] = abs($iOpen-$iClose) << 1;
   }
   return $index;
}


/**
 * Berechnet f�r die �bergebenen M1-Daten den EURFX6-Index.
 *
 * @param  int   $day  - FXT-Timestamp des Tages der zu berechnenden Daten
 * @param  array $data - M1-Bars dieses Tages aller f�r den Index ben�tigten Instrumente
 *
 * @return MYFX_BAR[] - Array mit den resultierenden M1-Indexdaten
 *
 * Formel: EURFX6 = (EURAUD * EURCAD * EURCHF * EURGBP * EURJPY * EURUSD) ^ 1/6
 */
function calculateEURFX6($day, array $data) {
   if (!is_int($day)) throw new IllegalTypeException('Illegal type of parameter $day: '.getType($day));
   $shortDate = gmDate('D, d-M-Y', $day);

   global $verbose;
   if ($verbose > 1) echoPre('[Info]    EURFX6  '.$shortDate);

   $AUDUSD = $data['AUDUSD']['bars'];
   $EURUSD = $data['EURUSD']['bars'];
   $GBPUSD = $data['GBPUSD']['bars'];
   $USDCAD = $data['USDCAD']['bars'];
   $USDCHF = $data['USDCHF']['bars'];
   $USDJPY = $data['USDJPY']['bars'];
   $index  = array();

   foreach ($AUDUSD as $i => $bar) {
      $audusd = $AUDUSD[$i]['open'];
      $eurusd = $EURUSD[$i]['open'];
      $gbpusd = $GBPUSD[$i]['open'];
      $usdcad = $USDCAD[$i]['open'];
      $usdchf = $USDCHF[$i]['open'];
      $usdjpy = $USDJPY[$i]['open'];
      $open   = pow(($usdcad/$audusd) * ($usdchf/$gbpusd) * ($usdjpy/1000), 1/6) * $eurusd;
      $iOpen  = round($open);

      $audusd = $AUDUSD[$i]['close'];
      $eurusd = $EURUSD[$i]['close'];
      $gbpusd = $GBPUSD[$i]['close'];
      $usdcad = $USDCAD[$i]['close'];
      $usdchf = $USDCHF[$i]['close'];
      $usdjpy = $USDJPY[$i]['close'];
      $close  = pow(($usdcad/$audusd) * ($usdchf/$gbpusd) * ($usdjpy/1000), 1/6) * $eurusd;
      $iClose = round($close);

      $index[$i]['time' ] = $bar['time'];
      $index[$i]['open' ] = $iOpen;
      $index[$i]['high' ] = max($iOpen, $iClose);
      $index[$i]['low'  ] = min($iOpen, $iClose);
      $index[$i]['close'] = $iClose;
      $index[$i]['ticks'] = abs($iOpen-$iClose) << 1;
   }
   return $index;
}


/**
 * Berechnet f�r die �bergebenen M1-Daten den EURFX7-Index.
 *
 * @param  int   $day  - FXT-Timestamp des Tages der zu berechnenden Daten
 * @param  array $data - M1-Bars dieses Tages aller f�r den Index ben�tigten Instrumente
 *
 * @return MYFX_BAR[] - Array mit den resultierenden M1-Indexdaten
 *
 * Formel: EURFX7 = (EURAUD * EURCAD * EURCHF * EURGBP * EURJPY * EURNZD * EURUSD) ^ 1/7
 */
function calculateEURFX7($day, array $data) {
   if (!is_int($day)) throw new IllegalTypeException('Illegal type of parameter $day: '.getType($day));
   $shortDate = gmDate('D, d-M-Y', $day);

   global $verbose;
   if ($verbose > 1) echoPre('[Info]    EURFX7  '.$shortDate);

   $AUDUSD = $data['AUDUSD']['bars'];
   $EURUSD = $data['EURUSD']['bars'];
   $GBPUSD = $data['GBPUSD']['bars'];
   $NZDUSD = $data['NZDUSD']['bars'];
   $USDCAD = $data['USDCAD']['bars'];
   $USDCHF = $data['USDCHF']['bars'];
   $USDJPY = $data['USDJPY']['bars'];
   $index  = array();

   foreach ($AUDUSD as $i => $bar) {
      $audusd = $AUDUSD[$i]['open'];
      $eurusd = $EURUSD[$i]['open'];
      $gbpusd = $GBPUSD[$i]['open'];
      $nzdusd = $NZDUSD[$i]['open'];
      $usdcad = $USDCAD[$i]['open'];
      $usdchf = $USDCHF[$i]['open'];
      $usdjpy = $USDJPY[$i]['open'];
      $open   = pow(($usdcad/$audusd) * ($usdchf/$gbpusd) * ($usdjpy/$nzdusd) * 100, 1/7) * $eurusd;
      $iOpen  = round($open);

      $audusd = $AUDUSD[$i]['close'];
      $eurusd = $EURUSD[$i]['close'];
      $gbpusd = $GBPUSD[$i]['close'];
      $nzdusd = $NZDUSD[$i]['close'];
      $usdcad = $USDCAD[$i]['close'];
      $usdchf = $USDCHF[$i]['close'];
      $usdjpy = $USDJPY[$i]['close'];
      $close  = pow(($usdcad/$audusd) * ($usdchf/$gbpusd) * ($usdjpy/$nzdusd) * 100, 1/7) * $eurusd;
      $iClose = round($close);

      $index[$i]['time' ] = $bar['time'];
      $index[$i]['open' ] = $iOpen;
      $index[$i]['high' ] = max($iOpen, $iClose);
      $index[$i]['low'  ] = min($iOpen, $iClose);
      $index[$i]['close'] = $iClose;
      $index[$i]['ticks'] = abs($iOpen-$iClose) << 1;
   }
   return $index;
}


/**
 * Berechnet f�r die �bergebenen M1-Daten den EURLFX-Index.
 *
 * @param  int   $day  - FXT-Timestamp des Tages der zu berechnenden Daten
 * @param  array $data - M1-Bars dieses Tages aller f�r den Index ben�tigten Instrumente
 *
 * @return MYFX_BAR[] - Array mit den resultierenden M1-Indexdaten
 *
 * Formel: EURLFX = (EURAUD * EURCAD * EURCHF * EURGBP * EURJPY * EURUSD) ^ 1/7
 *   oder: EURLFX = USDLFX * EURUSD
 */
function calculateEURLFX($day, array $data) {
   if (!is_int($day)) throw new IllegalTypeException('Illegal type of parameter $day: '.getType($day));
   $shortDate = gmDate('D, d-M-Y', $day);

   global $verbose;
   if ($verbose > 1) echoPre('[Info]    EURLFX  '.$shortDate);

   $AUDUSD = $data['AUDUSD']['bars'];
   $EURUSD = $data['EURUSD']['bars'];
   $GBPUSD = $data['GBPUSD']['bars'];
   $USDCAD = $data['USDCAD']['bars'];
   $USDCHF = $data['USDCHF']['bars'];
   $USDJPY = $data['USDJPY']['bars'];
   $index  = array();

   foreach ($AUDUSD as $i => $bar) {
      $audusd = $AUDUSD[$i]['open'];
      $eurusd = $EURUSD[$i]['open'];
      $gbpusd = $GBPUSD[$i]['open'];
      $usdcad = $USDCAD[$i]['open'];
      $usdchf = $USDCHF[$i]['open'];
      $usdjpy = $USDJPY[$i]['open'];
      $open   = pow(($usdcad/$audusd) * ($usdchf/$eurusd) * ($usdjpy/$gbpusd) * 100, 1/7) * $eurusd;
      $iOpen  = round($open);

      $audusd = $AUDUSD[$i]['close'];
      $eurusd = $EURUSD[$i]['close'];
      $gbpusd = $GBPUSD[$i]['close'];
      $usdcad = $USDCAD[$i]['close'];
      $usdchf = $USDCHF[$i]['close'];
      $usdjpy = $USDJPY[$i]['close'];
      $close  = pow(($usdcad/$audusd) * ($usdchf/$eurusd) * ($usdjpy/$gbpusd) * 100, 1/7) * $eurusd;
      $iClose = round($close);

      $index[$i]['time' ] = $bar['time'];
      $index[$i]['open' ] = $iOpen;
      $index[$i]['high' ] = max($iOpen, $iClose);
      $index[$i]['low'  ] = min($iOpen, $iClose);
      $index[$i]['close'] = $iClose;
      $index[$i]['ticks'] = abs($iOpen-$iClose) << 1;
   }
   return $index;
}


/**
 * Berechnet f�r die �bergebenen M1-Daten den EURX-Index (ICE).
 *
 * @param  int   $day  - FXT-Timestamp des Tages der zu berechnenden Daten
 * @param  array $data - M1-Bars dieses Tages aller f�r den Index ben�tigten Instrumente
 *
 * @return MYFX_BAR[] - Array mit den resultierenden M1-Indexdaten
 *
 * Formel: EURX = 34.38805726 * EURCHF^0.1113 * EURGBP^0.3056 * EURJPY^0.1891 * EURSEK^0.0785 * EURUSD^0.3155
 */
function calculateEURX($day, array $data) {
   if (!is_int($day)) throw new IllegalTypeException('Illegal type of parameter $day: '.getType($day));
   $shortDate = gmDate('D, d-M-Y', $day);

   global $verbose;
   if ($verbose > 1) echoPre('[Info]    EURX  '.$shortDate);

   $EURUSD = $data['EURUSD']['bars'];
   $GBPUSD = $data['GBPUSD']['bars'];
   $USDCHF = $data['USDCHF']['bars'];
   $USDJPY = $data['USDJPY']['bars'];
   $USDSEK = $data['USDSEK']['bars'];
   $index  = array();

   foreach ($EURUSD as $i => $bar) {
      $eurusd = $EURUSD[$i]['open'];
      $gbpusd = $GBPUSD[$i]['open'];
      $usdchf = $USDCHF[$i]['open'];
      $usdjpy = $USDJPY[$i]['open'];
      $usdsek = $USDSEK[$i]['open'];
      $open   = 34.38805726
              * pow($eurusd/100000 * $usdchf/100000, 0.1113)
              * pow($eurusd        / $gbpusd       , 0.3056)
              * pow($eurusd/100000 * $usdjpy/1000  , 0.1891)
              * pow($eurusd/100000 * $usdsek/100000, 0.0785)
              * pow($eurusd/100000                 , 0.3155);
      $iOpen  = round($open * 1000);

      $eurusd = $EURUSD[$i]['close'];
      $gbpusd = $GBPUSD[$i]['close'];
      $usdchf = $USDCHF[$i]['close'];
      $usdjpy = $USDJPY[$i]['close'];
      $usdsek = $USDSEK[$i]['close'];
      $close  = 34.38805726
              * pow($eurusd/100000 * $usdchf/100000, 0.1113)
              * pow($eurusd        / $gbpusd       , 0.3056)
              * pow($eurusd/100000 * $usdjpy/1000  , 0.1891)
              * pow($eurusd/100000 * $usdsek/100000, 0.0785)
              * pow($eurusd/100000                 , 0.3155);
      $iClose = round($close * 1000);

      $index[$i]['time' ] = $bar['time'];
      $index[$i]['open' ] = $iOpen;
      $index[$i]['high' ] = max($iOpen, $iClose);
      $index[$i]['low'  ] = min($iOpen, $iClose);
      $index[$i]['close'] = $iClose;
      $index[$i]['ticks'] = abs($iOpen-$iClose) << 1;
   }
   return $index;
}


/**
 * Berechnet f�r die �bergebenen M1-Daten den GBPFX6-Index.
 *
 * @param  int   $day  - FXT-Timestamp des Tages der zu berechnenden Daten
 * @param  array $data - M1-Bars dieses Tages aller f�r den Index ben�tigten Instrumente
 *
 * @return MYFX_BAR[] - Array mit den resultierenden M1-Indexdaten
 *
 * Formel: GBPFX6 = ((GBPAUD * GBPCAD * GBPCHF * GBPJPY * GBPUSD) / EURGBP) ^ 1/6
 */
function calculateGBPFX6($day, array $data) {
   if (!is_int($day)) throw new IllegalTypeException('Illegal type of parameter $day: '.getType($day));
   $shortDate = gmDate('D, d-M-Y', $day);

   global $verbose;
   if ($verbose > 1) echoPre('[Info]    GBPFX6  '.$shortDate);

   $AUDUSD = $data['AUDUSD']['bars'];
   $EURUSD = $data['EURUSD']['bars'];
   $GBPUSD = $data['GBPUSD']['bars'];
   $USDCAD = $data['USDCAD']['bars'];
   $USDCHF = $data['USDCHF']['bars'];
   $USDJPY = $data['USDJPY']['bars'];
   $index  = array();

   foreach ($AUDUSD as $i => $bar) {
      $audusd = $AUDUSD[$i]['open'];
      $eurusd = $EURUSD[$i]['open'];
      $gbpusd = $GBPUSD[$i]['open'];
      $usdcad = $USDCAD[$i]['open'];
      $usdchf = $USDCHF[$i]['open'];
      $usdjpy = $USDJPY[$i]['open'];
      $open   = pow(($usdcad/$audusd) * ($usdchf/$eurusd) * ($usdjpy/1000), 1/6) * $gbpusd;
      $iOpen  = round($open);

      $audusd = $AUDUSD[$i]['close'];
      $eurusd = $EURUSD[$i]['close'];
      $gbpusd = $GBPUSD[$i]['close'];
      $usdcad = $USDCAD[$i]['close'];
      $usdchf = $USDCHF[$i]['close'];
      $usdjpy = $USDJPY[$i]['close'];
      $close  = pow(($usdcad/$audusd) * ($usdchf/$eurusd) * ($usdjpy/1000), 1/6) * $gbpusd;
      $iClose = round($close);

      $index[$i]['time' ] = $bar['time'];
      $index[$i]['open' ] = $iOpen;
      $index[$i]['high' ] = max($iOpen, $iClose);
      $index[$i]['low'  ] = min($iOpen, $iClose);
      $index[$i]['close'] = $iClose;
      $index[$i]['ticks'] = abs($iOpen-$iClose) << 1;
   }
   return $index;
}


/**
 * Berechnet f�r die �bergebenen M1-Daten den GBPFX7-Index.
 *
 * @param  int   $day  - FXT-Timestamp des Tages der zu berechnenden Daten
 * @param  array $data - M1-Bars dieses Tages aller f�r den Index ben�tigten Instrumente
 *
 * @return MYFX_BAR[] - Array mit den resultierenden M1-Indexdaten
 *
 * Formel: GBPFX7 = ((GBPAUD * GBPCAD * GBPCHF * GBPJPY * GBPNZD * GBPUSD) / EURGBP) ^ 1/7
 */
function calculateGBPFX7($day, array $data) {
   if (!is_int($day)) throw new IllegalTypeException('Illegal type of parameter $day: '.getType($day));
   $shortDate = gmDate('D, d-M-Y', $day);

   global $verbose;
   if ($verbose > 1) echoPre('[Info]    GBPFX7  '.$shortDate);

   $AUDUSD = $data['AUDUSD']['bars'];
   $EURUSD = $data['EURUSD']['bars'];
   $GBPUSD = $data['GBPUSD']['bars'];
   $NZDUSD = $data['NZDUSD']['bars'];
   $USDCAD = $data['USDCAD']['bars'];
   $USDCHF = $data['USDCHF']['bars'];
   $USDJPY = $data['USDJPY']['bars'];
   $index  = array();

   foreach ($AUDUSD as $i => $bar) {
      $audusd = $AUDUSD[$i]['open'];
      $eurusd = $EURUSD[$i]['open'];
      $gbpusd = $GBPUSD[$i]['open'];
      $nzdusd = $NZDUSD[$i]['open'];
      $usdcad = $USDCAD[$i]['open'];
      $usdchf = $USDCHF[$i]['open'];
      $usdjpy = $USDJPY[$i]['open'];
      $open   = pow(($usdcad/$audusd) * ($usdchf/$eurusd) * ($usdjpy/$nzdusd) * 100, 1/7) * $gbpusd;
      $iOpen  = round($open);

      $audusd = $AUDUSD[$i]['close'];
      $eurusd = $EURUSD[$i]['close'];
      $gbpusd = $GBPUSD[$i]['close'];
      $nzdusd = $NZDUSD[$i]['close'];
      $usdcad = $USDCAD[$i]['close'];
      $usdchf = $USDCHF[$i]['close'];
      $usdjpy = $USDJPY[$i]['close'];
      $close  = pow(($usdcad/$audusd) * ($usdchf/$eurusd) * ($usdjpy/$nzdusd) * 100, 1/7) * $gbpusd;
      $iClose = round($close);

      $index[$i]['time' ] = $bar['time'];
      $index[$i]['open' ] = $iOpen;
      $index[$i]['high' ] = max($iOpen, $iClose);
      $index[$i]['low'  ] = min($iOpen, $iClose);
      $index[$i]['close'] = $iClose;
      $index[$i]['ticks'] = abs($iOpen-$iClose) << 1;
   }
   return $index;
}


/**
 * Berechnet f�r die �bergebenen M1-Daten den GBPLFX-Index.
 *
 * @param  int   $day  - FXT-Timestamp des Tages der zu berechnenden Daten
 * @param  array $data - M1-Bars dieses Tages aller f�r den Index ben�tigten Instrumente
 *
 * @return MYFX_BAR[] - Array mit den resultierenden M1-Indexdaten
 *
 * Formel: GBPLFX = ((GBPAUD * GBPCAD * GBPCHF * GBPJPY * GBPUSD) / EURGBP) ^ 1/7
 *   oder: GBPLFX = USDLFX * GBPUSD
 */
function calculateGBPLFX($day, array $data) {
   if (!is_int($day)) throw new IllegalTypeException('Illegal type of parameter $day: '.getType($day));
   $shortDate = gmDate('D, d-M-Y', $day);

   global $verbose;
   if ($verbose > 1) echoPre('[Info]    GBPLFX  '.$shortDate);

   $AUDUSD = $data['AUDUSD']['bars'];
   $EURUSD = $data['EURUSD']['bars'];
   $GBPUSD = $data['GBPUSD']['bars'];
   $USDCAD = $data['USDCAD']['bars'];
   $USDCHF = $data['USDCHF']['bars'];
   $USDJPY = $data['USDJPY']['bars'];
   $index  = array();

   foreach ($AUDUSD as $i => $bar) {
      $audusd = $AUDUSD[$i]['open'];
      $eurusd = $EURUSD[$i]['open'];
      $gbpusd = $GBPUSD[$i]['open'];
      $usdcad = $USDCAD[$i]['open'];
      $usdchf = $USDCHF[$i]['open'];
      $usdjpy = $USDJPY[$i]['open'];
      $open   = pow(($usdcad/$audusd) * ($usdchf/$eurusd) * ($usdjpy/$gbpusd) * 100, 1/7) * $gbpusd;
      $iOpen  = round($open);

      $audusd = $AUDUSD[$i]['close'];
      $eurusd = $EURUSD[$i]['close'];
      $gbpusd = $GBPUSD[$i]['close'];
      $usdcad = $USDCAD[$i]['close'];
      $usdchf = $USDCHF[$i]['close'];
      $usdjpy = $USDJPY[$i]['close'];
      $close  = pow(($usdcad/$audusd) * ($usdchf/$eurusd) * ($usdjpy/$gbpusd) * 100, 1/7) * $gbpusd;
      $iClose = round($close);

      $index[$i]['time' ] = $bar['time'];
      $index[$i]['open' ] = $iOpen;
      $index[$i]['high' ] = max($iOpen, $iClose);
      $index[$i]['low'  ] = min($iOpen, $iClose);
      $index[$i]['close'] = $iClose;
      $index[$i]['ticks'] = abs($iOpen-$iClose) << 1;
   }
   return $index;
}


/**
 * Berechnet f�r die �bergebenen M1-Daten den JPYFX6-Index.
 *
 * @param  int   $day  - FXT-Timestamp des Tages der zu berechnenden Daten
 * @param  array $data - M1-Bars dieses Tages aller f�r den Index ben�tigten Instrumente
 *
 * @return MYFX_BAR[] - Array mit den resultierenden M1-Indexdaten
 *
 * Formel: JPYFX6 = 100 * (1 / (AUDJPY * CADJPY * CHFJPY * EURJPY * GBPJPY * USDJPY)) ^ 1/6
 */
function calculateJPYFX6($day, array $data) {
   if (!is_int($day)) throw new IllegalTypeException('Illegal type of parameter $day: '.getType($day));
   $shortDate = gmDate('D, d-M-Y', $day);

   global $verbose;
   if ($verbose > 1) echoPre('[Info]    JPYFX6  '.$shortDate);

   $AUDUSD = $data['AUDUSD']['bars'];
   $EURUSD = $data['EURUSD']['bars'];
   $GBPUSD = $data['GBPUSD']['bars'];
   $USDCAD = $data['USDCAD']['bars'];
   $USDCHF = $data['USDCHF']['bars'];
   $USDJPY = $data['USDJPY']['bars'];
   $index  = array();

   foreach ($AUDUSD as $i => $bar) {
      $audusd = $AUDUSD[$i]['open'];
      $eurusd = $EURUSD[$i]['open'];
      $gbpusd = $GBPUSD[$i]['open'];
      $usdcad = $USDCAD[$i]['open'];
      $usdchf = $USDCHF[$i]['open'];
      $usdjpy = $USDJPY[$i]['open'];
      $open   = pow(($usdcad/$audusd) * ($usdchf/$eurusd) * (100000/$gbpusd), 1/6) / $usdjpy * 100000;
      $iOpen  = round($open * 100000);

      $audusd = $AUDUSD[$i]['close'];
      $eurusd = $EURUSD[$i]['close'];
      $gbpusd = $GBPUSD[$i]['close'];
      $usdcad = $USDCAD[$i]['close'];
      $usdchf = $USDCHF[$i]['close'];
      $usdjpy = $USDJPY[$i]['close'];
      $close  = pow(($usdcad/$audusd) * ($usdchf/$eurusd) * (100000/$gbpusd), 1/6) / $usdjpy * 100000;
      $iClose = round($close * 100000);

      $index[$i]['time' ] = $bar['time'];
      $index[$i]['open' ] = $iOpen;
      $index[$i]['high' ] = max($iOpen, $iClose);
      $index[$i]['low'  ] = min($iOpen, $iClose);
      $index[$i]['close'] = $iClose;
      $index[$i]['ticks'] = abs($iOpen-$iClose) << 1;
   }
   return $index;
}


/**
 * Berechnet f�r die �bergebenen M1-Daten den JPYFX7-Index.
 *
 * @param  int   $day  - FXT-Timestamp des Tages der zu berechnenden Daten
 * @param  array $data - M1-Bars dieses Tages aller f�r den Index ben�tigten Instrumente
 *
 * @return MYFX_BAR[] - Array mit den resultierenden M1-Indexdaten
 *
 * Formel: JPYFX7 = 100 * (1 / (AUDJPY * CADJPY * CHFJPY * EURJPY * GBPJPY * NZDJPY * USDJPY)) ^ 1/7
 */
function calculateJPYFX7($day, array $data) {
   if (!is_int($day)) throw new IllegalTypeException('Illegal type of parameter $day: '.getType($day));
   $shortDate = gmDate('D, d-M-Y', $day);

   global $verbose;
   if ($verbose > 1) echoPre('[Info]    JPYFX7  '.$shortDate);

   $AUDUSD = $data['AUDUSD']['bars'];
   $EURUSD = $data['EURUSD']['bars'];
   $GBPUSD = $data['GBPUSD']['bars'];
   $NZDUSD = $data['NZDUSD']['bars'];
   $USDCAD = $data['USDCAD']['bars'];
   $USDCHF = $data['USDCHF']['bars'];
   $USDJPY = $data['USDJPY']['bars'];
   $index  = array();

   foreach ($AUDUSD as $i => $bar) {
      $audusd = $AUDUSD[$i]['open'];
      $eurusd = $EURUSD[$i]['open'];
      $gbpusd = $GBPUSD[$i]['open'];
      $nzdusd = $NZDUSD[$i]['open'];
      $usdcad = $USDCAD[$i]['open'];
      $usdchf = $USDCHF[$i]['open'];
      $usdjpy = $USDJPY[$i]['open'];
      $open   = pow(($usdcad/$audusd) * ($usdchf/$eurusd) * (100000/$gbpusd) * (100000/$nzdusd), 1/7) / $usdjpy * 100000;
      $iOpen  = round($open * 100000);

      $audusd = $AUDUSD[$i]['close'];
      $eurusd = $EURUSD[$i]['close'];
      $gbpusd = $GBPUSD[$i]['close'];
      $nzdusd = $NZDUSD[$i]['close'];
      $usdcad = $USDCAD[$i]['close'];
      $usdchf = $USDCHF[$i]['close'];
      $usdjpy = $USDJPY[$i]['close'];
      $close  = pow(($usdcad/$audusd) * ($usdchf/$eurusd) * (100000/$gbpusd) * (100000/$nzdusd), 1/7) / $usdjpy * 100000;
      $iClose = round($close * 100000);

      $index[$i]['time' ] = $bar['time'];
      $index[$i]['open' ] = $iOpen;
      $index[$i]['high' ] = max($iOpen, $iClose);
      $index[$i]['low'  ] = min($iOpen, $iClose);
      $index[$i]['close'] = $iClose;
      $index[$i]['ticks'] = abs($iOpen-$iClose) << 1;
   }
   return $index;
}


/**
 * Berechnet f�r die �bergebenen M1-Daten den JPYLFX-Index.
 *
 * @param  int   $day  - FXT-Timestamp des Tages der zu berechnenden Daten
 * @param  array $data - M1-Bars dieses Tages aller f�r den Index ben�tigten Instrumente
 *
 * @return MYFX_BAR[] - Array mit den resultierenden M1-Indexdaten
 *
 * Formel: JPYLFX = 100 * (1 / (AUDJPY * CADJPY * CHFJPY * EURJPY * GBPJPY * USDJPY)) ^ 1/7
 *   oder: JPYLFX = 100 * USDLFX / USDJPY
 */
function calculateJPYLFX($day, array $data) {
   if (!is_int($day)) throw new IllegalTypeException('Illegal type of parameter $day: '.getType($day));
   $shortDate = gmDate('D, d-M-Y', $day);

   global $verbose;
   if ($verbose > 1) echoPre('[Info]    JPYLFX  '.$shortDate);

   $AUDUSD = $data['AUDUSD']['bars'];
   $EURUSD = $data['EURUSD']['bars'];
   $GBPUSD = $data['GBPUSD']['bars'];
   $USDCAD = $data['USDCAD']['bars'];
   $USDCHF = $data['USDCHF']['bars'];
   $USDJPY = $data['USDJPY']['bars'];
   $index  = array();

   foreach ($AUDUSD as $i => $bar) {
      $audusd = $AUDUSD[$i]['open'];
      $eurusd = $EURUSD[$i]['open'];
      $gbpusd = $GBPUSD[$i]['open'];
      $usdcad = $USDCAD[$i]['open'];
      $usdchf = $USDCHF[$i]['open'];
      $usdjpy = $USDJPY[$i]['open'];
      $open   = 100 * pow(($usdcad/$audusd) * ($usdchf/$eurusd) * ($usdjpy/$gbpusd) * 100, 1/7) / $usdjpy * 1000;
      $iOpen  = round($open * 100000);

      $audusd = $AUDUSD[$i]['close'];
      $eurusd = $EURUSD[$i]['close'];
      $gbpusd = $GBPUSD[$i]['close'];
      $usdcad = $USDCAD[$i]['close'];
      $usdchf = $USDCHF[$i]['close'];
      $usdjpy = $USDJPY[$i]['close'];
      $close  = 100 * pow(($usdcad/$audusd) * ($usdchf/$eurusd) * ($usdjpy/$gbpusd) * 100, 1/7) / $usdjpy * 1000;
      $iClose = round($close * 100000);

      $index[$i]['time' ] = $bar['time'];
      $index[$i]['open' ] = $iOpen;
      $index[$i]['high' ] = max($iOpen, $iClose);
      $index[$i]['low'  ] = min($iOpen, $iClose);
      $index[$i]['close'] = $iClose;
      $index[$i]['ticks'] = abs($iOpen-$iClose) << 1;
   }
   return $index;
}


/**
 * Berechnet f�r die �bergebenen M1-Daten den NOKFX7-Index.
 *
 * @param  int   $day  - FXT-Timestamp des Tages der zu berechnenden Daten
 * @param  array $data - M1-Bars dieses Tages aller f�r den Index ben�tigten Instrumente
 *
 * @return MYFX_BAR[] - Array mit den resultierenden M1-Indexdaten
 *
 * Formel: NOKFX7 = 10 * (NOKJPY / (AUDNOK * CADNOK * CHFNOK * EURNOK * GBPNOK * USDNOK)) ^ 1/7
 *   oder: NOKFX7 = 10 * USDLFX / USDNOK
 */
function calculateNOKFX7($day, array $data) {
   if (!is_int($day)) throw new IllegalTypeException('Illegal type of parameter $day: '.getType($day));
   $shortDate = gmDate('D, d-M-Y', $day);

   global $verbose;
   if ($verbose > 1) echoPre('[Info]    NOKFX7  '.$shortDate);

   $AUDUSD = $data['AUDUSD']['bars'];
   $EURUSD = $data['EURUSD']['bars'];
   $GBPUSD = $data['GBPUSD']['bars'];
   $USDCAD = $data['USDCAD']['bars'];
   $USDCHF = $data['USDCHF']['bars'];
   $USDJPY = $data['USDJPY']['bars'];
   $USDNOK = $data['USDNOK']['bars'];
   $index  = array();

   foreach ($AUDUSD as $i => $bar) {
      $audusd = $AUDUSD[$i]['open'];
      $eurusd = $EURUSD[$i]['open'];
      $gbpusd = $GBPUSD[$i]['open'];
      $usdcad = $USDCAD[$i]['open'];
      $usdchf = $USDCHF[$i]['open'];
      $usdjpy = $USDJPY[$i]['open'];
      $usdnok = $USDNOK[$i]['open'];
      $open   = 10 * pow(($usdcad/$audusd) * ($usdchf/$eurusd) * ($usdjpy/$gbpusd) * 100, 1/7) / $usdnok * 100000;
      $iOpen  = round($open * 100000);

      $audusd = $AUDUSD[$i]['close'];
      $eurusd = $EURUSD[$i]['close'];
      $gbpusd = $GBPUSD[$i]['close'];
      $usdcad = $USDCAD[$i]['close'];
      $usdchf = $USDCHF[$i]['close'];
      $usdjpy = $USDJPY[$i]['close'];
      $usdnok = $USDNOK[$i]['close'];
      $close  = 10 * pow(($usdcad/$audusd) * ($usdchf/$eurusd) * ($usdjpy/$gbpusd) * 100, 1/7) / $usdnok * 100000;
      $iClose = round($close * 100000);

      $index[$i]['time' ] = $bar['time'];
      $index[$i]['open' ] = $iOpen;
      $index[$i]['high' ] = max($iOpen, $iClose);
      $index[$i]['low'  ] = min($iOpen, $iClose);
      $index[$i]['close'] = $iClose;
      $index[$i]['ticks'] = abs($iOpen-$iClose) << 1;
   }
   return $index;
}


/**
 * Berechnet f�r die �bergebenen M1-Daten den NZDFX7-Index.
 *
 * @param  int   $day  - FXT-Timestamp des Tages der zu berechnenden Daten
 * @param  array $data - M1-Bars dieses Tages aller f�r den Index ben�tigten Instrumente
 *
 * @return MYFX_BAR[] - Array mit den resultierenden M1-Indexdaten
 *
 * Formel: NZDFX7 = ((NZDCAD * NZDCHF * NZDJPY * NZDUSD) / (AUDNZD * EURNZD * GBPNZD)) ^ 1/7
 */
function calculateNZDFX7($day, array $data) {
   return calculateNZDLFX($day, $data, 'NZDFX7');
}


/**
 * Berechnet f�r die �bergebenen M1-Daten den NZDLFX-Index. Dieser Index entspricht dem NZDFX7.
 *
 * @param  int    $day  - FXT-Timestamp des Tages der zu berechnenden Daten
 * @param  array  $data - M1-Bars dieses Tages aller f�r den Index ben�tigten Instrumente
 * @param  string $name - optionaler Name (um die Funktion gleichzeitig f�r NZDLFX und NZDFX7 nutzen zu k�nnen)
 *
 * @return MYFX_BAR[] - Array mit den resultierenden M1-Indexdaten
 *
 * Formel: NZDLFX = ((NZDCAD * NZDCHF * NZDJPY * NZDUSD) / (AUDNZD * EURNZD * GBPNZD)) ^ 1/7
 *   oder: NZDLFX = USDLFX * NZDUSD
 */
function calculateNZDLFX($day, array $data, $name='NZDLFX') {
   if (!is_int($day)) throw new IllegalTypeException('Illegal type of parameter $day: '.getType($day));
   $shortDate = gmDate('D, d-M-Y', $day);

   global $verbose;
   if ($verbose > 1) echoPre('[Info]    '.$name.'  '.$shortDate);

   $AUDUSD = $data['AUDUSD']['bars'];
   $EURUSD = $data['EURUSD']['bars'];
   $GBPUSD = $data['GBPUSD']['bars'];
   $USDCAD = $data['USDCAD']['bars'];
   $USDCHF = $data['USDCHF']['bars'];
   $USDJPY = $data['USDJPY']['bars'];
   $NZDUSD = $data['NZDUSD']['bars'];
   $index  = array();

   foreach ($AUDUSD as $i => $bar) {
      $audusd = $AUDUSD[$i]['open'];
      $eurusd = $EURUSD[$i]['open'];
      $gbpusd = $GBPUSD[$i]['open'];
      $usdcad = $USDCAD[$i]['open'];
      $usdchf = $USDCHF[$i]['open'];
      $usdjpy = $USDJPY[$i]['open'];
      $nzdusd = $NZDUSD[$i]['open'];
      $open   = pow(($usdcad/$audusd) * ($usdchf/$eurusd) * ($usdjpy/$gbpusd) * 100, 1/7) * $nzdusd;
      $iOpen  = round($open);

      $audusd = $AUDUSD[$i]['close'];
      $eurusd = $EURUSD[$i]['close'];
      $gbpusd = $GBPUSD[$i]['close'];
      $usdcad = $USDCAD[$i]['close'];
      $usdchf = $USDCHF[$i]['close'];
      $usdjpy = $USDJPY[$i]['close'];
      $nzdusd = $NZDUSD[$i]['close'];
      $close  = pow(($usdcad/$audusd) * ($usdchf/$eurusd) * ($usdjpy/$gbpusd) * 100, 1/7) * $nzdusd;
      $iClose = round($close);

      $index[$i]['time' ] = $bar['time'];
      $index[$i]['open' ] = $iOpen;
      $index[$i]['high' ] = max($iOpen, $iClose);
      $index[$i]['low'  ] = min($iOpen, $iClose);
      $index[$i]['close'] = $iClose;
      $index[$i]['ticks'] = abs($iOpen-$iClose) << 1;
   }
   return $index;
}


/**
 * Berechnet f�r die �bergebenen M1-Daten den SEKFX7-Index.
 *
 * @param  int   $day  - FXT-Timestamp des Tages der zu berechnenden Daten
 * @param  array $data - M1-Bars dieses Tages aller f�r den Index ben�tigten Instrumente
 *
 * @return MYFX_BAR[] - Array mit den resultierenden M1-Indexdaten
 *
 * Formel: SEKFX7 = 10 * (SEKJPY / (AUDSEK * CADSEK * CHFSEK * EURSEK * GBPSEK * USDSEK)) ^ 1/7
 *   oder: SEKFX7 = 10 * USDLFX / USDSEK
 */
function calculateSEKFX7($day, array $data) {
   if (!is_int($day)) throw new IllegalTypeException('Illegal type of parameter $day: '.getType($day));
   $shortDate = gmDate('D, d-M-Y', $day);

   global $verbose;
   if ($verbose > 1) echoPre('[Info]    SEKFX7  '.$shortDate);

   $AUDUSD = $data['AUDUSD']['bars'];
   $EURUSD = $data['EURUSD']['bars'];
   $GBPUSD = $data['GBPUSD']['bars'];
   $USDCAD = $data['USDCAD']['bars'];
   $USDCHF = $data['USDCHF']['bars'];
   $USDJPY = $data['USDJPY']['bars'];
   $USDSEK = $data['USDSEK']['bars'];
   $index  = array();

   foreach ($AUDUSD as $i => $bar) {
      $audusd = $AUDUSD[$i]['open'];
      $eurusd = $EURUSD[$i]['open'];
      $gbpusd = $GBPUSD[$i]['open'];
      $usdcad = $USDCAD[$i]['open'];
      $usdchf = $USDCHF[$i]['open'];
      $usdjpy = $USDJPY[$i]['open'];
      $usdsek = $USDSEK[$i]['open'];
      $open   = 10 * pow(($usdcad/$audusd) * ($usdchf/$eurusd) * ($usdjpy/$gbpusd) * 100, 1/7) / $usdsek * 100000;
      $iOpen  = round($open * 100000);

      $audusd = $AUDUSD[$i]['close'];
      $eurusd = $EURUSD[$i]['close'];
      $gbpusd = $GBPUSD[$i]['close'];
      $usdcad = $USDCAD[$i]['close'];
      $usdchf = $USDCHF[$i]['close'];
      $usdjpy = $USDJPY[$i]['close'];
      $usdsek = $USDSEK[$i]['close'];
      $close  = 10 * pow(($usdcad/$audusd) * ($usdchf/$eurusd) * ($usdjpy/$gbpusd) * 100, 1/7) / $usdsek * 100000;
      $iClose = round($close * 100000);

      $index[$i]['time' ] = $bar['time'];
      $index[$i]['open' ] = $iOpen;
      $index[$i]['high' ] = max($iOpen, $iClose);
      $index[$i]['low'  ] = min($iOpen, $iClose);
      $index[$i]['close'] = $iClose;
      $index[$i]['ticks'] = abs($iOpen-$iClose) << 1;
   }
   return $index;
}


/**
 * Berechnet f�r die �bergebenen M1-Daten den SGDFX7-Index.
 *
 * @param  int   $day  - FXT-Timestamp des Tages der zu berechnenden Daten
 * @param  array $data - M1-Bars dieses Tages aller f�r den Index ben�tigten Instrumente
 *
 * @return MYFX_BAR[] - Array mit den resultierenden M1-Indexdaten
 *
 * Formel: SGDFX7 = (SGDJPY / (AUDSGD * CADSGD * CHFSGD * EURSGD * GBPSGD * USDSGD)) ^ 1/7
 *   oder: SGDFX7 = USDLFX / USDSGD
 */
function calculateSGDFX7($day, array $data) {
   if (!is_int($day)) throw new IllegalTypeException('Illegal type of parameter $day: '.getType($day));
   $shortDate = gmDate('D, d-M-Y', $day);

   global $verbose;
   if ($verbose > 1) echoPre('[Info]    SGDFX7  '.$shortDate);

   $AUDUSD = $data['AUDUSD']['bars'];
   $EURUSD = $data['EURUSD']['bars'];
   $GBPUSD = $data['GBPUSD']['bars'];
   $USDCAD = $data['USDCAD']['bars'];
   $USDCHF = $data['USDCHF']['bars'];
   $USDJPY = $data['USDJPY']['bars'];
   $USDSGD = $data['USDSGD']['bars'];
   $index  = array();

   foreach ($AUDUSD as $i => $bar) {
      $audusd = $AUDUSD[$i]['open'];
      $eurusd = $EURUSD[$i]['open'];
      $gbpusd = $GBPUSD[$i]['open'];
      $usdcad = $USDCAD[$i]['open'];
      $usdchf = $USDCHF[$i]['open'];
      $usdjpy = $USDJPY[$i]['open'];
      $usdsgd = $USDSGD[$i]['open'];
      $open   = pow(($usdcad/$audusd) * ($usdchf/$eurusd) * ($usdjpy/$gbpusd) * 100, 1/7) / $usdsgd * 100000;
      $iOpen  = round($open * 100000);

      $audusd = $AUDUSD[$i]['close'];
      $eurusd = $EURUSD[$i]['close'];
      $gbpusd = $GBPUSD[$i]['close'];
      $usdcad = $USDCAD[$i]['close'];
      $usdchf = $USDCHF[$i]['close'];
      $usdjpy = $USDJPY[$i]['close'];
      $usdsgd = $USDSGD[$i]['close'];
      $close  = pow(($usdcad/$audusd) * ($usdchf/$eurusd) * ($usdjpy/$gbpusd) * 100, 1/7) / $usdsgd * 100000;
      $iClose = round($close * 100000);

      $index[$i]['time' ] = $bar['time'];
      $index[$i]['open' ] = $iOpen;
      $index[$i]['high' ] = max($iOpen, $iClose);
      $index[$i]['low'  ] = min($iOpen, $iClose);
      $index[$i]['close'] = $iClose;
      $index[$i]['ticks'] = abs($iOpen-$iClose) << 1;
   }
   return $index;
}


/**
 * Berechnet f�r die �bergebenen M1-Daten den USDFX6-Index.
 *
 * @param  int   $day  - FXT-Timestamp des Tages der zu berechnenden Daten
 * @param  array $data - M1-Bars dieses Tages aller f�r den Index ben�tigten Instrumente
 *
 * @return MYFX_BAR[] - Array mit den resultierenden M1-Indexdaten
 *
 * Formel: USDFX6 = ((USDCAD * USDCHF * USDJPY) / (AUDUSD * EURUSD * GBPUSD)) ^ 1/6
 */
function calculateUSDFX6($day, array $data) {
   if (!is_int($day)) throw new IllegalTypeException('Illegal type of parameter $day: '.getType($day));
   $shortDate = gmDate('D, d-M-Y', $day);

   global $verbose;
   if ($verbose > 1) echoPre('[Info]    USDFX6  '.$shortDate);

   $AUDUSD = $data['AUDUSD']['bars'];
   $EURUSD = $data['EURUSD']['bars'];
   $GBPUSD = $data['GBPUSD']['bars'];
   $USDCAD = $data['USDCAD']['bars'];
   $USDCHF = $data['USDCHF']['bars'];
   $USDJPY = $data['USDJPY']['bars'];
   $index  = array();

   foreach ($AUDUSD as $i => $bar) {
      $audusd = $AUDUSD[$i]['open'];
      $eurusd = $EURUSD[$i]['open'];
      $gbpusd = $GBPUSD[$i]['open'];
      $usdcad = $USDCAD[$i]['open'];
      $usdchf = $USDCHF[$i]['open'];
      $usdjpy = $USDJPY[$i]['open'];
      $open   = pow(($usdcad/$audusd) * ($usdchf/$eurusd) * ($usdjpy/$gbpusd) * 100, 1/6);
      $iOpen  = round($open * 100000);

      $audusd = $AUDUSD[$i]['close'];
      $eurusd = $EURUSD[$i]['close'];
      $gbpusd = $GBPUSD[$i]['close'];
      $usdcad = $USDCAD[$i]['close'];
      $usdchf = $USDCHF[$i]['close'];
      $usdjpy = $USDJPY[$i]['close'];
      $close  = pow(($usdcad/$audusd) * ($usdchf/$eurusd) * ($usdjpy/$gbpusd) * 100, 1/6);
      $iClose = round($close * 100000);

      $index[$i]['time' ] = $bar['time'];
      $index[$i]['open' ] = $iOpen;
      $index[$i]['high' ] = max($iOpen, $iClose);
      $index[$i]['low'  ] = min($iOpen, $iClose);
      $index[$i]['close'] = $iClose;
      $index[$i]['ticks'] = abs($iOpen-$iClose) << 1;
   }
   return $index;
}


/**
 * Berechnet f�r die �bergebenen M1-Daten den USDFX7-Index.
 *
 * @param  int   $day  - FXT-Timestamp des Tages der zu berechnenden Daten
 * @param  array $data - M1-Bars dieses Tages aller f�r den Index ben�tigten Instrumente
 *
 * @return MYFX_BAR[] - Array mit den resultierenden M1-Indexdaten
 *
 * Formel: USDFX7 = ((USDCAD * USDCHF * USDJPY) / (AUDUSD * EURUSD * GBPUSD * NZDUSD)) ^ 1/7
 */
function calculateUSDFX7($day, array $data) {
   if (!is_int($day)) throw new IllegalTypeException('Illegal type of parameter $day: '.getType($day));
   $shortDate = gmDate('D, d-M-Y', $day);

   global $verbose;
   if ($verbose > 1) echoPre('[Info]    USDFX7  '.$shortDate);

   $AUDUSD = $data['AUDUSD']['bars'];
   $EURUSD = $data['EURUSD']['bars'];
   $GBPUSD = $data['GBPUSD']['bars'];
   $NZDUSD = $data['NZDUSD']['bars'];
   $USDCAD = $data['USDCAD']['bars'];
   $USDCHF = $data['USDCHF']['bars'];
   $USDJPY = $data['USDJPY']['bars'];
   $index  = array();

   foreach ($AUDUSD as $i => $bar) {
      $audusd = $AUDUSD[$i]['open'];
      $eurusd = $EURUSD[$i]['open'];
      $gbpusd = $GBPUSD[$i]['open'];
      $nzdusd = $NZDUSD[$i]['open'];
      $usdcad = $USDCAD[$i]['open'];
      $usdchf = $USDCHF[$i]['open'];
      $usdjpy = $USDJPY[$i]['open'];
      $open   = pow(($usdcad/$audusd) * ($usdchf/$eurusd) * ($usdjpy/$gbpusd) * (100000/$nzdusd) * 100, 1/7);
      $iOpen  = round($open * 100000);

      $audusd = $AUDUSD[$i]['close'];
      $eurusd = $EURUSD[$i]['close'];
      $gbpusd = $GBPUSD[$i]['close'];
      $nzdusd = $NZDUSD[$i]['close'];
      $usdcad = $USDCAD[$i]['close'];
      $usdchf = $USDCHF[$i]['close'];
      $usdjpy = $USDJPY[$i]['close'];
      $close  = pow(($usdcad/$audusd) * ($usdchf/$eurusd) * ($usdjpy/$gbpusd) * (100000/$nzdusd) * 100, 1/7);
      $iClose = round($close * 100000);

      $index[$i]['time' ] = $bar['time'];
      $index[$i]['open' ] = $iOpen;
      $index[$i]['high' ] = max($iOpen, $iClose);
      $index[$i]['low'  ] = min($iOpen, $iClose);
      $index[$i]['close'] = $iClose;
      $index[$i]['ticks'] = abs($iOpen-$iClose) << 1;
   }
   return $index;
}


/**
 * Berechnet f�r die �bergebenen M1-Daten den USDLFX-Index.
 *
 * @param  int   $day  - FXT-Timestamp des Tages der zu berechnenden Daten
 * @param  array $data - M1-Bars dieses Tages aller f�r den Index ben�tigten Instrumente
 *
 * @return MYFX_BAR[] - Array mit den resultierenden M1-Indexdaten
 *
 * Formel: USDLFX = ((USDCAD * USDCHF * USDJPY) / (AUDUSD * EURUSD * GBPUSD)) ^ 1/7
 */
function calculateUSDLFX($day, array $data) {
   if (!is_int($day)) throw new IllegalTypeException('Illegal type of parameter $day: '.getType($day));
   $shortDate = gmDate('D, d-M-Y', $day);

   global $verbose;
   if ($verbose > 1) echoPre('[Info]    USDLFX  '.$shortDate);

   $AUDUSD = $data['AUDUSD']['bars'];
   $EURUSD = $data['EURUSD']['bars'];
   $GBPUSD = $data['GBPUSD']['bars'];
   $USDCAD = $data['USDCAD']['bars'];
   $USDCHF = $data['USDCHF']['bars'];
   $USDJPY = $data['USDJPY']['bars'];
   $index  = array();

   foreach ($AUDUSD as $i => $bar) {
      $audusd = $AUDUSD[$i]['open'];
      $eurusd = $EURUSD[$i]['open'];
      $gbpusd = $GBPUSD[$i]['open'];
      $usdcad = $USDCAD[$i]['open'];
      $usdchf = $USDCHF[$i]['open'];
      $usdjpy = $USDJPY[$i]['open'];
      $open   = pow(($usdcad/$audusd) * ($usdchf/$eurusd) * ($usdjpy/$gbpusd) * 100, 1/7);
      $iOpen  = round($open * 100000);

      $audusd = $AUDUSD[$i]['close'];
      $eurusd = $EURUSD[$i]['close'];
      $gbpusd = $GBPUSD[$i]['close'];
      $usdcad = $USDCAD[$i]['close'];
      $usdchf = $USDCHF[$i]['close'];
      $usdjpy = $USDJPY[$i]['close'];
      $close  = pow(($usdcad/$audusd) * ($usdchf/$eurusd) * ($usdjpy/$gbpusd) * 100, 1/7);
      $iClose = round($close * 100000);

      $index[$i]['time' ] = $bar['time'];
      $index[$i]['open' ] = $iOpen;
      $index[$i]['high' ] = max($iOpen, $iClose);
      $index[$i]['low'  ] = min($iOpen, $iClose);
      $index[$i]['close'] = $iClose;
      $index[$i]['ticks'] = abs($iOpen-$iClose) << 1;
   }
   return $index;
}


/**
 * Berechnet f�r die �bergebenen M1-Daten den USDX-Index (ICE).
 *
 * @param  int   $day  - FXT-Timestamp des Tages der zu berechnenden Daten
 * @param  array $data - M1-Bars dieses Tages aller f�r den Index ben�tigten Instrumente
 *
 * @return MYFX_BAR[] - Array mit den resultierenden M1-Indexdaten
 *
 * Formel: USDX = 50.14348112 * (USDCAD^0.091 * USDCHF^0.036 * USDJPY^0.136 * USDSEK^0.042) / (EURUSD^0.576 * GBPUSD^0.119)
 */
function calculateUSDX($day, array $data) {
   if (!is_int($day)) throw new IllegalTypeException('Illegal type of parameter $day: '.getType($day));
   $shortDate = gmDate('D, d-M-Y', $day);

   global $verbose;
   if ($verbose > 1) echoPre('[Info]    USDX  '.$shortDate);

   $EURUSD = $data['EURUSD']['bars'];
   $GBPUSD = $data['GBPUSD']['bars'];
   $USDCAD = $data['USDCAD']['bars'];
   $USDCHF = $data['USDCHF']['bars'];
   $USDJPY = $data['USDJPY']['bars'];
   $USDSEK = $data['USDSEK']['bars'];
   $index  = array();

   foreach ($EURUSD as $i => $bar) {
      $eurusd = $EURUSD[$i]['open'];
      $gbpusd = $GBPUSD[$i]['open'];
      $usdcad = $USDCAD[$i]['open'];
      $usdchf = $USDCHF[$i]['open'];
      $usdjpy = $USDJPY[$i]['open'];
      $usdsek = $USDSEK[$i]['open'];
      $open   = 50.14348112
              * pow($usdcad/100000, 0.091) * pow($usdchf/100000, 0.036) * pow($usdjpy/1000, 0.136) * pow($usdsek/100000, 0.042)
              / pow($eurusd/100000, 0.576) / pow($gbpusd/100000, 0.119);
      $iOpen  = round($open * 1000);

      $eurusd = $EURUSD[$i]['close'];
      $gbpusd = $GBPUSD[$i]['close'];
      $usdcad = $USDCAD[$i]['close'];
      $usdchf = $USDCHF[$i]['close'];
      $usdjpy = $USDJPY[$i]['close'];
      $usdsek = $USDSEK[$i]['close'];
      $close  = 50.14348112
              * pow($usdcad/100000, 0.091) * pow($usdchf/100000, 0.036) * pow($usdjpy/1000, 0.136) * pow($usdsek/100000, 0.042)
              / pow($eurusd/100000, 0.576) / pow($gbpusd/100000, 0.119);
      $iClose = round($close * 1000);

      $index[$i]['time' ] = $bar['time'];
      $index[$i]['open' ] = $iOpen;
      $index[$i]['high' ] = max($iOpen, $iClose);
      $index[$i]['low'  ] = min($iOpen, $iClose);
      $index[$i]['close'] = $iClose;
      $index[$i]['ticks'] = abs($iOpen-$iClose) << 1;
   }
   return $index;
}


/**
 * Berechnet f�r die �bergebenen M1-Daten den ZARFX7-Index.
 *
 * @param  int   $day  - FXT-Timestamp des Tages der zu berechnenden Daten
 * @param  array $data - M1-Bars dieses Tages aller f�r den Index ben�tigten Instrumente
 *
 * @return MYFX_BAR[] - Array mit den resultierenden M1-Indexdaten
 *
 * Formel: ZARFX7 = 10 * (ZARJPY / (AUDZAR * CADZAR * CHFZAR * EURZAR * GBPZAR * USDZAR)) ^ 1/7
 *   oder: ZARFX7 = 10 * USDLFX / USDZAR
 */
function calculateZARFX7($day, array $data) {
   if (!is_int($day)) throw new IllegalTypeException('Illegal type of parameter $day: '.getType($day));
   $shortDate = gmDate('D, d-M-Y', $day);

   global $verbose;
   if ($verbose > 1) echoPre('[Info]    SEKFX7  '.$shortDate);

   $AUDUSD = $data['AUDUSD']['bars'];
   $EURUSD = $data['EURUSD']['bars'];
   $GBPUSD = $data['GBPUSD']['bars'];
   $USDCAD = $data['USDCAD']['bars'];
   $USDCHF = $data['USDCHF']['bars'];
   $USDJPY = $data['USDJPY']['bars'];
   $USDZAR = $data['USDZAR']['bars'];
   $index  = array();

   foreach ($AUDUSD as $i => $bar) {
      $audusd = $AUDUSD[$i]['open'];
      $eurusd = $EURUSD[$i]['open'];
      $gbpusd = $GBPUSD[$i]['open'];
      $usdcad = $USDCAD[$i]['open'];
      $usdchf = $USDCHF[$i]['open'];
      $usdjpy = $USDJPY[$i]['open'];
      $usdzar = $USDZAR[$i]['open'];
      $open   = 10 * pow(($usdcad/$audusd) * ($usdchf/$eurusd) * ($usdjpy/$gbpusd) * 100, 1/7) / $usdzar * 100000;
      $iOpen  = round($open * 100000);

      $audusd = $AUDUSD[$i]['close'];
      $eurusd = $EURUSD[$i]['close'];
      $gbpusd = $GBPUSD[$i]['close'];
      $usdcad = $USDCAD[$i]['close'];
      $usdchf = $USDCHF[$i]['close'];
      $usdjpy = $USDJPY[$i]['close'];
      $usdzar = $USDZAR[$i]['close'];
      $close  = 10 * pow(($usdcad/$audusd) * ($usdchf/$eurusd) * ($usdjpy/$gbpusd) * 100, 1/7) / $usdzar * 100000;
      $iClose = round($close * 100000);

      $index[$i]['time' ] = $bar['time'];
      $index[$i]['open' ] = $iOpen;
      $index[$i]['high' ] = max($iOpen, $iClose);
      $index[$i]['low'  ] = min($iOpen, $iClose);
      $index[$i]['close'] = $iClose;
      $index[$i]['ticks'] = abs($iOpen-$iClose) << 1;
   }
   return $index;
}


/**
 * Schreibt die Indexdaten eines Forex-Tages in die lokale MyFX-Historydatei.
 *
 * @param  string     $symbol - Symbol
 * @param  int        $day    - FXT-Timestamp des Tages
 * @param  MYFX_BAR[] $bars   - Indexdaten des Tages
 *
 * @return bool - Erfolgsstatus
 */
function saveBars($symbol, $day, array $bars) {
   if (!is_int($day)) throw new IllegalTypeException('Illegal type of parameter $day: '.getType($day));
   $shortDate = gmDate('D, d-M-Y', $day);

   global $saveRawMyFXData;


   // (1) Daten nochmal pr�fen
   $errorMsg = null;
   if (!$errorMsg && ($size=sizeOf($bars))!=1*DAY/MINUTES)             $errorMsg = 'Invalid number of bars for '.$shortDate.': '.$size;
   if (!$errorMsg && $bars[0]['time']%DAYS!=0)                         $errorMsg = 'No beginning bars for '.$shortDate.' found, first bar:'.NL.printFormatted($bars[0], true);
   if (!$errorMsg && $bars[$size-1]['time']%DAYS!=23*HOURS+59*MINUTES) $errorMsg = 'No ending bars for '.$shortDate.' found, last bar:'.NL.printFormatted($bars[$size-1], true);
   if ($errorMsg) {
      showBuffer($bars);
      throw new plRuntimeException($errorMsg);
   }


   // (2) Bars bin�r packen
   $data = null;
   foreach ($bars as $bar) {
      $data .= pack('VVVVVV', $bar['time' ],
                              $bar['open' ],
                              $bar['high' ],
                              $bar['low'  ],
                              $bar['close'],
                              $bar['ticks']);
   }


   // (3) bin�re Daten ggf. speichern
   if ($saveRawMyFXData) {
      if (is_file($file=getVar('myfxTarget.raw', $symbol, $day))) {
         echoPre('[Error]   '.$symbol.' history for '.gmDate('D, d-M-Y', $day).' already exists');
         return false;
      }
      mkDirWritable(dirName($file));
      $tmpFile = tempNam(dirName($file), baseName($file));
      $hFile   = fOpen($tmpFile, 'wb');
      fWrite($hFile, $data);
      fClose($hFile);
      rename($tmpFile, $file);                                       // So kann eine existierende Datei niemals korrupt sein.
   }


   // (4) TODO: bin�re Daten komprimieren und speichern

   return true;
}


/**
 *
 */
function showBuffer($bars) {
   echoPre(NL);
   $size = sizeOf($bars);
   $firstBar = $lastBar = null;
   if ($size) {
      if (isSet($bars[0]['time']) && $bars[$size-1]['time']) {
         $firstBar = 'from='.gmDate('d-M-Y H:i', $bars[0      ]['time']);
         $lastBar  = '  to='.gmDate('d-M-Y H:i', $bars[$size-1]['time']);
      }
      else {
         $firstBar = $lastBar = '  invalid';
         echoPre($bars);
      }
   }
   echoPre('bars['.$size.'] => '.$firstBar.($size>1? $lastBar:''));
   echoPre(NL);
}


/**
 * Erzeugt und verwaltet dynamisch generierte Variablen.
 *
 * Evaluiert und cacht st�ndig wiederbenutzte dynamische Variablen an einem zentralen Ort. Vereinfacht die Logik,
 * da die Variablen nicht global gespeichert oder �ber viele Funktionsaufrufe hinweg weitergereicht werden m�ssen,
 * aber trotzdem nicht bei jeder Verwendung neu ermittelt werden brauchen.
 *
 * @param  string $id     - eindeutiger Bezeichner der Variable (ID)
 * @param  string $symbol - Symbol oder NULL
 * @param  int    $time   - FXT-Timestamp oder NULL
 *
 * @return string - Variable
 */
function getVar($id, $symbol=null, $time=null) {
   //global $varCache;
   static $varCache = array();
   if (array_key_exists(($key=$id.'|'.$symbol.'|'.$time), $varCache))
      return $varCache[$key];

   if (!is_string($id))                          throw new IllegalTypeException('Illegal type of parameter $id: '.getType($id));
   if (!is_null($symbol) && !is_string($symbol)) throw new IllegalTypeException('Illegal type of parameter $symbol: '.getType($symbol));
   if (!is_null($time) && !is_int($time))        throw new IllegalTypeException('Illegal type of parameter $time: '.getType($time));

   static $dataDirectory;
   $self = __FUNCTION__;

   if ($id == 'myfxDirDate') {                  // $yyyy/$mm/$dd                                         // lokales Pfad-Datum
      if (!$time)   throw new plInvalidArgumentException('Invalid parameter $time: '.$time);
      $result = gmDate('Y/m/d', $time);
   }
   else if ($id == 'myfxSourceDir') {           // $dataDirectory/history/dukascopy/$symbol/$myfxDirDate // lokales Quell-Verzeichnis
      if (!$symbol) throw new plInvalidArgumentException('Invalid parameter $symbol: '.$symbol);
      if (!$dataDirectory)
      $dataDirectory = MyFX::getConfigPath('myfx.data_directory');
      $myfxDirDate   = $self('myfxDirDate', null, $time);
      $result        = "$dataDirectory/history/dukascopy/$symbol/$myfxDirDate";
   }
   else if ($id == 'myfxTargetDir') {           // $dataDirectory/history/myfx/$symbol/$myfxDirDate      // lokales Ziel-Verzeichnis
      if (!$symbol) throw new plInvalidArgumentException('Invalid parameter $symbol: '.$symbol);
      if (!$dataDirectory)
      $dataDirectory = MyFX::getConfigPath('myfx.data_directory');
      $myfxDirDate   = $self('myfxDirDate', null, $time);
      $result        = "$dataDirectory/history/myfx/$symbol/$myfxDirDate";
   }
   else if ($id == 'myfxSource.raw') {          // $myfxSourceDir/M1.myfx                                // lokale Quell-Datei ungepackt
      $myfxSourceDir = $self('myfxSourceDir', $symbol, $time);
      $result        = "$myfxSourceDir/M1.myfx";
   }
   else if ($id == 'myfxSource.compressed') {   // $myfxSourceDir/M1.rar                                 // lokale Quell-Datei gepackt
      $myfxSourceDir = $self('myfxSourceDir', $symbol, $time);
      $result        = "$myfxSourceDir/M1.rar";
   }
   else if ($id == 'myfxTarget.raw') {          // $myfxTargetDir/M1.myfx                                // lokale Ziel-Datei ungepackt
      $myfxTargetDir = $self('myfxTargetDir' , $symbol, $time);
      $result        = "$myfxTargetDir/M1.myfx";
   }
   else if ($id == 'myfxTarget.compressed') {   // $myfxTargetDir/M1.rar                                 // lokale Ziel-Datei gepackt
      $myfxTargetDir = $self('myfxTargetDir' , $symbol, $time);
      $result        = "$myfxTargetDir/M1.rar";
   }
   else {
     throw new plInvalidArgumentException('Unknown parameter $id: "'.$id.'"');
   }

   $varCache[$key] = $result;
   (sizeof($varCache) > ($maxSize=128)) && array_shift($varCache) /*&& echoPre('cache size limit of '.$maxSize.' hit')*/;

   return $result;
}


/**
 * Hilfefunktion: Zeigt die Syntax des Aufrufs an.
 *
 * @param  string $message - zus�tzlich zur Syntax anzuzeigende Message (default: keine)
 */
function help($message=null) {
   if (!is_null($message))
      echo($message.NL.NL);

   $self = baseName($_SERVER['PHP_SELF']);

echo <<<END

 Syntax:  $self [symbol ...]


END;
}
?>
