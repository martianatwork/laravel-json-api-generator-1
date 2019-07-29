<?php

namespace PeteNys\Generator\Generators\JsonApi;

use Illuminate\Support\Str;
use PeteNys\Generator\Common\CommandData;
use PeteNys\Generator\Common\GeneratorFieldRelation;
use PeteNys\Generator\Utils\FileUtil;
use PeteNys\Generator\Utils\TableFieldsGenerator;
use PeteNys\Generator\Generators\BaseGenerator;

class ValidatorGenerator extends BaseGenerator
{
    /**
     * Fields not included in the generator by default.
     *
     * @var array
     */
    protected $excluded_fields = [
        'created_at',
        'updated_at',
    ];

    /** @var CommandData */
    private $commandData;

    /** @var string */
    private $path;
    private $fileName;
    private $table;

    /**
     * ModelGenerator constructor.
     *
     * @param CommandData $commandData
     */
    public function __construct(CommandData $commandData)
    {
        $this->commandData = $commandData;
        $this->path = $commandData->config->pathJsonApi."\\".$this->commandData->modelName;
        $this->fileName = 'Validator.php';
        $this->table = $this->commandData->dynamicVars['$TABLE_NAME$'];
    }

    public function generate()
    {
        $templateData = get_template('json_api.validator', 'laravel-generator');

        $templateData = $this->fillTemplate($templateData);

        FileUtil::createFile($this->path, $this->fileName, $templateData);

        $this->commandData->commandComment("\nValidator created: ");
        $this->commandData->commandInfo($this->fileName);
    }

    private function fillTemplate($templateData)
    {
        $templateData = fill_template($this->commandData->dynamicVars, $templateData);

        $templateData = str_replace(
            '$RELATIONS$',
            fill_template($this->commandData->dynamicVars, implode(PHP_EOL.infy_nl_tab(1, 1), $this->generateRelations())),
            $templateData
        );

        $templateData = str_replace('$GENERATE_DATE$', date('F j, Y, g:i a T'), $templateData);

        return $templateData;
    }

    public function rollback()
    {
        if ($this->rollbackFile($this->path, $this->fileName)) {
            $this->commandData->commandComment('Validator file deleted: '.$this->fileName);
        }
    }
}