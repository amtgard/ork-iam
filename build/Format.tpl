namespace Amtgard\IAM\ORN\Definitions;

use Amtgard\IAM\OrkService;
use Amtgard\IAM\ORNFormat;

class <?=$formatClass ?> extends ORNFormat
{

    public static function serviceFormat(): array
    {
        return [
<?php foreach ($formatServices as $service): ?>
            OrkService::<?=$service ?>,
<?php endforeach; ?>
        ];
    }

    public static function getValidResourceMap($resource = null): array {
        $map = [
<?php foreach ($formatResources as $service => $resources): ?>
            "<?=$service ?>" => [ "<?=implode("\", \"", $resources) ?>" ]<?=(($service == array_key_last($formatResources))?"\n":",\n") ?>
<?php endforeach; ?>
        ];
        return $resource ? $map[$resource] : $map;
    }

}