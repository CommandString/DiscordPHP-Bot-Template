<?php

require_once "vendor/autoload.php";

use CommandString\Utils\ArrayUtils;
use CommandString\Utils\StringUtils;

$command = "php command.php {$argv[1]} {$argv[2]}";

$verbs = ["save" => "saving", "delete" => "deleting"];
$present_tense_verb = $verbs[$argv[1]];

$pipes = [];
$proc = proc_open($command, [["pipe", "r"], ["pipe", "w"], ["pipe", "w"]], $pipes);

stream_set_blocking($pipes[1], false);

$command = new stdClass;

while (proc_get_status($proc)["running"]) { // while attempting an action on a slash command
    try {
        while (!feof($pipes[1])) { // while there's still content in the pipe
            $line = fgets($pipes[1]); // store the current line in $line

            if (json_decode($line) !== null) { // if the current line is a JSON
                $command = json_decode($line); // decode it and store it in $command as an object
                echo ucfirst($present_tense_verb) . " ". strtolower($command->command)." command : "; // get the present_tense_verb of the action (e.g. save is Saving)
                continue;
            }

            if (str_contains($line, "REQ")) {
                if (str_contains($line, "/commands successful")) { // if action was successful
                    echo "Success\n"; // output Success

                    if (strtolower($command->command) === strtolower(ArrayUtils::getLastItem($argv))) { // if this is the last command that needs acted upon
                        fclose($pipes[1]); // close the STDOUT pipe so that an error is thrown
                        proc_terminate($proc, 9); // terminate the process as well
                    }
                } else if (str_contains($line, "/commands failed")) { // if the action failed
                    echo "Fail\n"; // output Fail
                    echo json_encode(json_decode(StringUtils::getBetween("{", "} in", $line, true)), JSON_PRETTY_PRINT); // output a prettified JSON of the error
                
                    if (strtolower($command->command) === strtolower(ArrayUtils::getLastItem($argv))) { // if this is the last command that needs acted upon
                        fclose($pipes[1]); // close the STDOUT pipe so that an error is thrown
                        proc_terminate($proc, 9); // terminate the process as well
                    }
                }
            }
        }
    } catch (TypeError) { // catch the error thrown on line 49 (hopefully)
        die(); // just die...
    }
    
    while (!feof($pipes[2])) { // while there's still content in the error pipe
        echo fgets($pipes[2]); // dump contents of the error pipe
    }
}