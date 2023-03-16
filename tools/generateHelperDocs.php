<?php

$helpers = file_get_contents(__DIR__."/../Common/Helpers.php");
$lines = explode("\n", $helpers);

$names = [];
ob_start();

foreach ($lines as $key => $line) {
    $line = trim($line);

    if (!str_starts_with($line, "function")) {
        continue;
    }
    
    $description = "";
    
    if ($lines[--$key] ?? "" === " */") {
        $key--;
        for ($i = $key; $i > 0; $i--) {
            if (str_starts_with($lines[$i], "/**")) {
                break;
            }

            $descriptionLine = substr($lines[$i], 3) . "\n";
            
            if (!empty($descriptionLine)) {
                $description = "$descriptionLine$description";
            }
        }
    }

    $header = $line;
    $line = str_replace(["function", " "], "", $line);
    $name = explode("(", $line)[0];
    $names[] = $name;
    $params = explode(",", explode(")", explode("(", $header)[1])[0]);
    $returnType = explode(":", $header)[1];

    ?>

# <?= $name ?>


## Header

```php
<?= $header ?>

```

<?php if (!empty($params[0])) { ?>
## Arguments

| Type | Name |
|------|------|
<?php
    foreach($params as $param) {
        $param = trim($param);

        $parts = explode(" ", $param);
        $name = $parts[1];
        $type = $parts[0];

        ?>
|<?= $type ?>|<?= $name ?>| 
<?php
    }
}
?>

## Return Type
`<?= trim($returnType) ?>`

<?php if (!empty($description)) { ?>
## Description
<?= $description ?>
<?php }
}

$md = ob_get_clean();

foreach ($names as $name) {
    $md = "[{$name}](#{$name})\n\n$md";
}

$md = "# Helper Functions

Inside `Common/Helpers.php` there's a bunch of utility commands that can make some repetitive tasks easier

{$md}";

file_put_contents(__DIR__."/HelperDocs.md", $md);