<?php

$outputFile = "command.output.txt";
$errorFile = "command.error.txt";

$command = "php";

foreach ($argv as $arg) {
    if ($arg === "command") {
        $arg = "command.php";
    }

    $command .= " $arg";
}

$dspec = [
    // 0 => ["file", "command.php", "r"],
    1 => ["file", $outputFile, "a"],
    2 => ["file", $errorFile, "a"]
];

# delete any existing output files before starting process

if (file_exists($outputFile)) {
    unlink($outputFile);
}

if (file_exists($errorFile)) {
    unlink($errorFile);
}

$process = proc_open($command, $dspec, $pipes, realpath("./"));

$success = $stop = $exception = false;

$currentOutputLine = 0;
$currentErrorLine = 0;
$currentCommand = "";

while(true) {
    $lines = explode("\n", file_get_contents($outputFile));

    for ($i = $currentOutputLine; $i < count($lines); $i++) {
        $line = $lines[$i];

        if (str_starts_with($line, "Command: ")) {
            $currentCommand = $line;
        }

        if (str_contains($line, "REQ")) {
            if (str_contains($line, "/commands successful")) {
                $success = $stop = true;
                break;
            } else if (str_contains($line, "/commands failed")) {
                $stop = true;
                break;
            }
        } else if (str_contains($line, "Stack trace")) {
            $exception = true;
            $stop = true;
            break;
        }
    }
    
    $currentOutputLine = $i;

    if ($stop) {
        break;
    }
    
    $lines = explode("\n", file_get_contents($errorFile));

    for ($i = $currentErrorLine; $i < count($lines); $i++) {
        $line = $lines[$i];

        if (str_contains($line, "Stack trace")) {
            $exception = true;
            $stop = true;
            break;
        }
    }
    
    $currentErrorLine = $i;

    if ($stop) {
        break;
    }

    sleep(1);
}

proc_terminate($process);

function getCharactersBetween(string $startCharacter, string $endCharacter, string $string, bool $case_sensitive = true) {
    $start = ($case_sensitive) ? strpos($string, $startCharacter) : stripos($string, $startCharacter);
    $end = ($case_sensitive) ? strrpos($string, $endCharacter) : strripos($string, $endCharacter);
    
    return substr($string, $start, ($end-$start)+1);
}

if ($success) {
    echo "Command Operation was successful!\n";
    unlink($outputFile);
    unlink($errorFile);
} else {
    echo "Command Operator was unsuccessful see errors below\n";
    echo "$currentCommand\n";
    echo (!$exception) ? json_encode(json_decode(getCharactersBetween("{", "}", explode("Stack trace:", $line)[0])), JSON_PRETTY_PRINT)."\n" : file_get_contents($errorFile);
}