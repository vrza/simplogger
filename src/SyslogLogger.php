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
 *  Logger that sends all messages to the syslog daemon
 *
 *  new SyslogLogger ( string $ident, int $option, int $facility )
 *
 *  For full documentation on $option and $facility, see
 *  http://php.net/manual/en/function.openlog.php
 */
class SyslogLogger extends Logger
{
    /**
     * @param string $ident The string $ident is added to each message.
     * @param int $option Logging options.
     * @param int $facility What type of program is logging the message.
     */
    function __construct(string $ident, int $option, int $facility)
    {
        $this->ident = $ident;
        openlog($ident, $option, $facility);
    }

    function __destruct()
    {
        $this->debug('Destroying SyslogLogger instance');
        closelog();
    }

    public function log(int $severity, string $message): bool
    {
        $r = false;
        foreach (explode(PHP_EOL, $message) as $line) {
            $r = syslog($severity, $line);
        }
        return $r;
    }
}
