<?php

namespace PeteNys\Generator\Generators;

use Illuminate\Support\Str;
use PeteNys\Generator\Common\CommandData;

class RouteGenerator extends BaseGenerator
{
    /** @var CommandData */
    private $commandData;

    /** @var string */
    private $path;

    /** @var string */
    private $routeContents;

    /** @var string */
    private $routeTemplate;

    public function __construct(CommandData $commandData)
    {
        $this->commandData = $commandData;
        $this->path = $commandData->config->pathJsonApiRoute;

        $this->routeContents = file_get_contents($this->path);

        $routeTemplate = get_template_stub('json_api_route', 'laravel-json-api-generator');

        $hasOneStr = $this->commandData->hasOneRouteRelations ?
            implode(', ', array_map(function($val){return sprintf("'%s'", $val);},
                $this->commandData->hasOneRouteRelations)) :
            "";
        $routeTemplate = str_replace('$MODEL_HAS_ONE_RELATION$', $hasOneStr, $routeTemplate);
        $hasManyStr = $this->commandData->hasManyRouteRelations ?
            implode(', ', array_map(function($val){return sprintf("'%s'", $val);},
                $this->commandData->hasManyRouteRelations)) :
            "";
        $routeTemplate = str_replace('$MODEL_HAS_MANY_RELATION$', $hasManyStr, $routeTemplate);

        $this->routeTemplate = fill_template_stub($this->commandData->dynamicVars, $routeTemplate);
    }

    public function generate()
    {
        if(Str::contains($this->routeContents, "api->resource('".$this->commandData->config->mDashedPlural."'")) {
            $this->commandData->commandComment("\n".$this->commandData->config->mName.' json api route already present.');
        } else {
            if(Str::contains($this->routeContents, "/* End Generated Content */")) {
                $this->routeContents = Str::replaceLast("/* End Generated Content */", $this->routeTemplate, $this->routeContents);
            } else {
                $this->routeContents .= "\n\t/* Start Generated Content */".$this->routeTemplate;
            }

            file_put_contents($this->path, $this->routeContents);

            $this->commandData->commandComment("\n".$this->commandData->config->mName.' json api route added.');
        }
    }

    public function rollback()
    {
        if (Str::contains($this->routeContents, "/* Start ".$this->commandData->config->mName." Route */")) {

            $this->routeContents = Str::before($this->routeContents, "/* Start ".$this->commandData->config->mName." Route */") .
                Str::after($this->routeContents, "/* End ".$this->commandData->config->mName." Route */")
            ;
            file_put_contents($this->path, $this->routeContents);
            $this->commandData->commandComment('json api route deleted');
        }
    }
}
