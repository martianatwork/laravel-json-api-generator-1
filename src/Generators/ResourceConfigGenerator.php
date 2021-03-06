<?php

namespace PeteNys\Generator\Generators;

use Illuminate\Support\Str;
use PeteNys\Generator\Common\CommandData;

class ResourceConfigGenerator extends BaseGenerator
{
    /** @var CommandData */
    private $commandData;

    /** @var string */
    private $path;

    /** @var string */
    private $configContents;

    /** @var string */
    private $configTemplate;

    public function __construct(CommandData $commandData)
    {
        $this->commandData = $commandData;
        $current_version = $this->commandData->dynamicVars['$JSON_API_VERSION$'];
        $this->path = str_replace("version", $current_version, $commandData->config->pathJsonApiResourceConfig);

        $this->configContents = file_get_contents($this->path);

        $configTemplate = get_template_stub('json_api_resource_config', 'laravel-json-api-generator');

        $this->configTemplate = fill_template_stub($this->commandData->dynamicVars, $configTemplate);
    }

    public function generate()
    {
        if(Str::contains($this->configContents, "'".$this->commandData->config->mDashedPlural."' =>")) {
            $this->commandData->commandComment("\n".$this->commandData->config->mName.' json api resource already present.');
        } else {
            if(Str::contains($this->configContents, "/* End Generated Content */")) {
                $this->commandData->commandComment("Has generated content tag");
                $this->configContents = Str::replaceLast("/* End Generated Content */", $this->configTemplate, $this->configContents);
            } elseif(Str::contains($this->configContents, "'resources' => [")) {
                $this->commandData->commandComment("Has resources array");
                $this->configContents = Str::replaceLast("'resources' => [", "'resources' => [\n\t\t/* Start Generated Content */".$this->configTemplate, $this->configContents);
            }

            file_put_contents($this->path, $this->configContents);

            $this->commandData->commandComment("\n".$this->commandData->config->mName.' json api resource added.');
        }
    }

    public function rollback()
    {
        if (Str::contains($this->configContents, $this->configTemplate)) {
            $this->configContents = str_replace($this->configTemplate, '', $this->configContents);
            file_put_contents($this->path, $this->configContents);
            $this->commandData->commandComment('api resource deleted');
        }
    }
}
