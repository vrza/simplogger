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
 *  Logger that logs high severity messages (warnings and higher by default)
 *  to stderr while logging lower severity messages to stdout.
 *
 *  new StdouterrLogger ( int $stderrSeverity, bool $includeTimestamp, bool $includeSeverity, string $ident )
 */
class StdouterrLogger extends Logger
{
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

    public function log(int $severity, string $message): bool
    {
        $fp = $severity <= $this->stderrSeverity ? STDERR : STDOUT;
        return $this->writemultiline($fp, $message, $severity);
    }
}
