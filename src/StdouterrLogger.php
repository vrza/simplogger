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
 *  Logger that logs high priority messages (warnings and higher) to stderr
 *  while logging lower priority messages to stdout.
 *
 *  new StdouterrLogger ( bool $timestamp, bool $priority, string $ident )
 */
class StdouterrLogger extends StdstreamLogger {
  public function log(int $priority, string $message): bool {
    $fp = $priority <= LOG_WARNING ? STDERR : STDOUT;
    return $this->writemultiline($fp, $message, $priority);
  }
}
