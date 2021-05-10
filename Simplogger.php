<?php
/*
   Copyright 2017-2021 Vladimir Vrzić

   Licensed under the Apache License, Version 2.0 (the "License");
   you may not use this file except in compliance with the License.
   You may obtain a copy of the License at

       http://www.apache.org/licenses/LICENSE-2.0

   Unless required by applicable law or agreed to in writing, software
   distributed under the License is distributed on an "AS IS" BASIS,
   WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
   See the License for the specific language governing permissions and
   limitations under the License.
*/

namespace Simplogger;

abstract class Logger {
  const SEVERITIES = [
    0 => 'EMERG',
    1 => 'ALERT',
    2 => 'CRIT',
    3 => 'ERR',
    4 => 'WARNING',
    5 => 'NOTICE',
    6 => 'INFO',
    7 => 'DEBUG'
  ];

  protected $stream = STDOUT;
  protected $includeTimestamp = FALSE;
  protected $ident = '';
  protected $includeSeverity = FALSE;


 /**
  *  Concrete loggers must implement
  *  log ( $severity, $message )
  *
  *  @param int    $severity  Message severity
  *  @param string $message   Message string
  */
  abstract public function log(int $severity, string $message): bool;

  public function emerg(string $message): bool {
    return $this->log(LOG_EMERG, $message);
  }

  public function alert(string $message): bool {
    return $this->log(LOG_ALERT, $message);
  }

  public function crit(string $message): bool {
    return $this->log(LOG_CRIT, $message);
  }

  public function err(string $message): bool {
    return $this->log(LOG_ERR, $message);
  }

  public function warning(string $message): bool {
    return $this->log(LOG_WARNING, $message);
  }

  public function notice(string $message): bool {
    return $this->log(LOG_NOTICE, $message);
  }

  public function info(string $message): bool {
    return $this->log(LOG_INFO, $message);
  }

  public function debug(string $message): bool {
    return $this->log(LOG_DEBUG, $message);
  }

  // aliases

  public function emergency(string $message): bool {
    return $this->emerg($message);
  }

  public function error(string $message): bool {
    return $this->err($message);
  }

  public function warn(string $message): bool {
    return $this->warning($message);
  }


  protected function write($fp, string $message, int $severity): int {
    // prepend any optional prefixes
    $prefix = ''; $sep = '';
    if ($this->includeTimestamp) {
      $prefix .= $sep . date('Y-m-d H:i:s', time());
      $sep = ' ';
    }
    if (!empty($this->ident)) {
      $prefix .= $sep . gethostname();
      $sep = ' ';
      $prefix .= $sep . '[' . $this->ident . ']';
    }
    if ($this->includeSeverity) {
      $prefix .= $sep . self::SEVERITIES[$severity];
      $sep = ' ';
    }
    $message = "$prefix$sep$message";
    // ensure line terminator
    if (strlen($message) < strlen(PHP_EOL) or
        substr_compare($message, PHP_EOL, -strlen(PHP_EOL)) !== 0) {
      $message .= PHP_EOL;
    }
    return fwrite($fp, $message);
  }

  protected function writemultiline($fp, string $message, int $severity): int {
    $r = FALSE;
    foreach (explode(PHP_EOL, $message) as $line) {
      $r = $this->write($fp, $line, $severity);
    }
    return $r;
  }
}

/**
 *  Logger that writes all messages to stdout
 *
 *  new StdoutLogger ( bool $includeTimestamp, bool $includeSeverity, string $ident )
 */
class StdoutLogger extends Logger {
  /**
   *  @param bool   $includeTimestamp  If TRUE, a timestamp string is added to each message.
   *  @param bool   $includeSeverity   If TRUE, a severity string is added to each message.
   *  @param string $ident      If not empty, "hostname [ident]" is added to each message.
   */
  function __construct(bool $includeTimestamp = FALSE, bool $includeSeverity = FALSE, string $ident = '') {
    $this->includeTimestamp = $includeTimestamp;
    $this->includeSeverity = $includeSeverity;
    $this->ident = $ident;
  }

  public function log(int $severity, string $message): bool {
    return $this->writemultiline(STDOUT, $message, $severity);
  }
}

/**
 *  Logger that writes all messages to stderr
 *
 *  new StderrLogger ( bool $includeTimestamp, bool $includeSeverity, string $ident )
 */
class StderrLogger extends Logger {
  /**
   *  @param bool   $includeTimestamp  If TRUE, a timestamp string is added to each message.
   *  @param bool   $includeSeverity   If TRUE, a severity string is added to each message.
   *  @param string $ident      If not empty, "hostname [ident]" is added to each message.
   */
  function __construct(bool $includeTimestamp = FALSE, bool $includeSeverity = FALSE, string $ident = '') {
    $this->includeTimestamp = $includeTimestamp;
    $this->includeSeverity = $includeSeverity;
    $this->ident = $ident;
  }

  public function log(int $severity, string $message): bool {
    return $this->writemultiline(STDERR, $message, $severity);
  }
}

/**
 *  Logger that logs high severity messages (warnings and higher by default)
 *  to stderr while logging lower severity messages to stdout.
 *
 *  new StdouterrLogger ( int $stderrSeverity, bool $includeTimestamp, bool $includeSeverity, string $ident )
 */
class StdouterrLogger extends Logger {
  private $stderrSeverity = LOG_WARNING;

  /**
    * @param int $stderrSeverity Messages at this and higher severity will go to stderr.
    * @param bool $includeTimestamp If TRUE, timestamp string is added to each message.
    * @param bool $includeSeverity If TRUE, severity string is added to each message.
    * @param string $ident If not empty, "hostname [ident]" is added to each message.
    */
  function __construct(
    int $stderrSeverity = LOG_WARNING,
    bool $includeTimestamp = false,
    bool $includeSeverity = false,
    string $ident = ''
  ) {
    $this->stderrSeverity = $stderrSeverity;
    $this->includeTimestamp = $includeTimestamp;
    $this->includeSeverity = $includeSeverity;
    $this->ident = $ident;
  }

  public function log(int $severity, string $message): bool {
    $fp = $severity <= $this->stderrSeverity ? STDERR : STDOUT;
    return $this->writemultiline($fp, $message, $severity);
  }
}

/**
 *  Logger that appends all messages to a file
 *
 *  new FileLogger ( string $file, bool $includeTimestamp, bool $includeSeverity, string $ident )
 */
class FileLogger extends Logger {
 /**
  *  @param string $file       Append to log file $file.
  *  @param bool   $includeTimestamp  If TRUE, a timestamp string is added to each message.
  *  @param bool   $includeSeverity   If TRUE, a severity string is added to each message.
  *  @param string $ident      If not empty, "hostname [ident]" is added to each message.
  */
  function __construct(string $file, bool $includeTimestamp = FALSE, bool $includeSeverity = FALSE, string $ident = '') {
    $this->includeTimestamp = $includeTimestamp;
    $this->includeSeverity = $includeSeverity;
    $this->ident = $ident;
    $this->stream = fopen($file, 'a');
  }

  function __destruct() {
    $this->debug('Destroying FileLogger instance');
    fclose($this->stream);
  }

  public function log(int $severity, string $message): bool {
    return $this->writemultiline($this->stream, $message, $severity);
  }
}

/**
 *  Logger that sends all messages to the syslog daemon
 *
 *  new SyslogLogger ( string $ident, int $option, int $facility )
 *
 *  For full documentation on $option and $facility, see
 *  http://php.net/manual/en/function.openlog.php
 */
class SyslogLogger extends Logger {
 /**
  *  @param string $ident     The string $ident is added to each message.
  *  @param int    $option    Logging options.
  *  @param int    $facility  What type of program is logging the message.
  */
  function __construct(string $ident, int $option, int $facility) {
    $this->ident = $ident;
    openlog($ident, $option, $facility);
  }

  function __destruct() {
    $this->debug('Destroying SyslogLogger instance');
    closelog();
  }

  public function log(int $severity, string $message): bool {
    $r = FALSE;
    foreach (explode(PHP_EOL, $message) as $line)
      $r = syslog($severity, $line);
    return $r;
  }
}

/**
 *  Logger that sends all messages to a remote syslog daemon
 *
 *  new SyslogLogger ( string $ident, int $facility, string $host, int $port )
 *
 *  For full documentation on $facility, see RFC 5424
 */
class RemoteSysLogger extends Logger {
 /**
  *  @param string $ident     The string $ident is added to each message.
  *  @param int    $facility  What type of program is logging the message.
  *  @param string $host      Syslog host (defaults to 127.0.0.1)
  *  @param string $port      Syslog UDP port (defaults to 514)
  */
  function __construct(string $ident, int $facility, string $host = '127.0.0.1', int $port = 514) {
    $this->ident = $ident;
    $this->facility = $facility;
    $this->host = $host;
    $this->port = $port;
    $this->sock = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
  }

  function __destruct() {
    $this->debug('Destroying RemoteSysLogger instance');
    socket_close($this->sock);
  }

  public function log(int $severity, string $message): bool {
    $r = FALSE;
    foreach (explode(PHP_EOL, $message) as $line) {
      $syslog_message = '<' . ($this->facility * 8 + $severity) . '>'
         . date('M d H:i:s ')
         . gethostname() . ' '
         . $this->ident . ': ' . $line;
      $r = socket_sendto($this->sock, $syslog_message, strlen($syslog_message), 0, $this->host, $this->port);
    }
    return $r;
  }
}
