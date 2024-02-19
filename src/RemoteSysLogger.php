<?php
/*
   Copyright 2017-2024 Vladimir Vrzić

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

/**
 *  Logger that sends all messages to a remote syslog daemon
 *
 *  new SyslogLogger ( string $ident, int $facility, string $host, int $port )
 *
 *  For full documentation on $facility, see RFC 5424
 */
class RemoteSysLogger extends Logger
{
    private const MAX_UDP_DATA_LENGTH = 65023;
    private $facility;
    private $host;
    private $port;
    private $sock;

    /**
     * @param string $ident The string $ident is added to each message.
     * @param int $facility What type of program is logging the message.
     * @param string $host Syslog host (defaults to 127.0.0.1)
     * @param int $port Syslog UDP port (defaults to 514)
     */
    function __construct(string $ident, int $facility, string $host = '127.0.0.1', int $port = 514)
    {
        $this->ident = $ident;
        $this->facility = $facility;
        $this->host = $host;
        $this->port = $port;
        $this->sock = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
    }

    function __destruct()
    {
        $this->debug('Destroying RemoteSysLogger instance');
        socket_close($this->sock);
    }

    public function log(int $severity, string $message): bool
    {
        $r = false;
        foreach (explode(PHP_EOL, $message) as $line) {
            $syslog_message = '<' . ($this->facility * 8 + $severity) . '>'
                . date('M d H:i:s ')
                . gethostname() . ' '
                . $this->ident . ': ' . $line;
            $trimmed_message = substr($syslog_message, 0, self::MAX_UDP_DATA_LENGTH)
            $r = socket_sendto($this->sock, $syslog_message, strlen($trimmed_message), 0, $this->host, $this->port);
        }
        return (bool)$r;
    }
}
