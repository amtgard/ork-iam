<?php

$formats = json_decode(file_get_contents('orn_definitions.json'), true);

foreach ($formats as $defClass => $format) {
    ob_start();
    $formatClass = $defClass . "Format";
    $formatServices = $format['format'];
    $formatResources = $format['resourceMap'];
    include 'Format.tpl';
    $formatClassDef = ob_get_clean();

    ob_start();
    $requirementClass = $defClass . "Requirement";
    include 'Requirement.tpl';
    $requirementClassDef = ob_get_clean();

    ob_start();
    $claimClass = $defClass . "Claim";
    include 'Claim.tpl';
    $claimClassDef = ob_get_clean();

    file_put_contents(dirname(__DIR__) . DIRECTORY_SEPARATOR . "src/ORN/Definitions/$formatClass.php", "<?php\n\n$formatClassDef");
    file_put_contents(dirname(__DIR__) . DIRECTORY_SEPARATOR . "src/ORN/Definitions/$requirementClass.php", "<?php\n\n$requirementClassDef");
    file_put_contents(dirname(__DIR__) . DIRECTORY_SEPARATOR . "src/ORN/Definitions/$claimClass.php", "<?php\n\n$claimClassDef");
}