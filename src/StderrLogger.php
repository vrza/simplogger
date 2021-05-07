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

namespace VladimirVrzic\Simplogger;

/**
 *  Logger that writes all messages to stderr
 *
 *  new StderrLogger ( bool $includeTimestamp, bool $includeSeverity, string $ident )
 */
class StderrLogger extends Logger
{
    /**
     *  @param bool   $includeTimestamp  If TRUE, timestamp string is added to each message.
     *  @param bool   $includeSeverity   If TRUE, severity string is added to each message.
     *  @param string $ident      If not empty, "hostname [ident]" is added to each message.
     */
    function __construct(bool $includeTimestamp = false, bool $includeSeverity = false, string $ident = '') {
        $this->includeTimestamp = $includeTimestamp;
        $this->includeSeverity = $includeSeverity;
        $this->ident = $ident;
    }

    public function log(int $severity, string $message): bool
    {
        return $this->writemultiline(STDERR, $message, $severity);
    }
}
