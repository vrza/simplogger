<?php
/*
   Copyright 2017-2023 Vladimir Vrzić

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

namespace VladimirVrzic\Simplogger;

use DateTime;

abstract class Logger
{
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
    protected $includeTimestamp = false;
    protected $ident = '';
    protected $includeSeverity = false;

    /**
     *  Concrete loggers must implement
     *  log ( $severity, $message )
     *
     * @param int $severity Message severity
     * @param string $message Message string
     */
    abstract public function log(int $severity, string $message): bool;

    public function emerg(string $message): bool
    {
        return $this->log(LOG_EMERG, $message);
    }

    public function alert(string $message): bool
    {
        return $this->log(LOG_ALERT, $message);
    }

    public function crit(string $message): bool
    {
        return $this->log(LOG_CRIT, $message);
    }

    public function err(string $message): bool
    {
        return $this->log(LOG_ERR, $message);
    }

    public function warning(string $message): bool
    {
        return $this->log(LOG_WARNING, $message);
    }

    public function notice(string $message): bool
    {
        return $this->log(LOG_NOTICE, $message);
    }

    public function info(string $message): bool
    {
        return $this->log(LOG_INFO, $message);
    }

    public function debug(string $message): bool
    {
        return $this->log(LOG_DEBUG, $message);
    }

    // aliases

    public function emergency(string $message): bool
    {
        return $this->emerg($message);
    }

    public function error(string $message): bool
    {
        return $this->err($message);
    }

    public function warn(string $message): bool
    {
        return $this->warning($message);
    }


    protected function write($fp, string $message, int $severity)
    {
        // prepend any optional prefixes
        $prefix = '';
        $sep = '';
        if ($this->includeTimestamp) {
            $prefix .= $sep . self::currentDateTime();
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

    protected function writemultiline($fp, string $message, int $severity)
    {
        $r = false;
        foreach (explode(PHP_EOL, $message) as $line) {
            $r = $this->write($fp, $line, $severity);
        }
        return $r;
    }

    protected function currentDateTime()
    {
        $date = DateTime::createFromFormat('U.u', microtime(true));
        return $date->format("Y-m-d\\TH:i:s.vP"); // RFC 3339
    }
}
