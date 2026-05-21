<?php

namespace App\Traits;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Validation\Rule;

trait ValidationTrait
{
    private function validatePackage($folder)
    {

        if (!Storage::exists('tmp/' . $folder . '/config.json')) {
            return [false, 'Not found config.json'];
        }

        $config = Storage::get('tmp/' . $folder . '/config.json');

        $jsonData = json_decode($config, true);

        $templates = $this->collectTemplates($jsonData);

        $rules = [
            'name' => 'required|string',
            'version' => 'required|string',
            'description' => 'required|string',
            'homepage' => 'nullable|string',
            'repository' => 'nullable|string',
            'type' => 'required|in:pipeline,infrastructure',
            'license' => 'required|string',
            'author' => 'required|string',
            'template' => 'required|string',
            'file' => 'required|string',
            'language' => 'required|string',
            'blocks' => 'required|array',
            'blocks.*.template' => 'required|string',
            'blocks.*.name' => 'required|string',
            'blocks.*.description' => 'nullable|string',
            'blocks.*.fields' => 'required|array',
            'blocks.*.dependencies' => 'nullable|array',
            'blocks.*.dependencies.*' => Rule::in($templates),
            'blocks.*.extra' => 'nullable|array',
            'blocks.*.extra.*.language' => 'required|string',
            'blocks.*.extra.*.file' => 'required|string',
            'blocks.*.extra.*.route' => 'nullable|string',
            'blocks.*.extra.*.template' => 'required|string',
            'blocks.*.extra.*.dependencies' => 'nullable|array',
            'blocks.*.extra.*.dependencies.*' => Rule::in($templates),
            'blocks.*.fields.*.type' => 'required|in:text,switch,select',
            'blocks.*.fields.*.name' => 'required|string',
            'blocks.*.fields.*.label' => 'required|string',
            'blocks.*.fields.*.rules' => 'nullable|string',
            'blocks.*.fields.*.default' => 'nullable',
            'blocks.*.fields.*.dependencies' => 'nullable|array',
            'blocks.*.fields.*.dependencies.*' => Rule::in($templates),
            'blocks.*.fields.*.options' => 'required_if:blocks.*.fields.*.type,select|array',
            'blocks.*.fields.*.options.*.id' => 'required_with:blocks.*.fields.*.options|numeric',
            'blocks.*.fields.*.options.*.label' => 'required_with:blocks.*.fields.*.options|string',
            'blocks.*.fields.*.options.*.value' => 'required_with:blocks.*.fields.*.options|string',
            'blocks.*.fields.*.options.*.dependencies' => 'nullable|array',
            'blocks.*.fields.*.options.*.dependencies.*' => Rule::in($templates),
        ];

        $validator = Validator::make($jsonData, $rules);

        if ($validator->fails()) {
            $error = $validator->errors()->all();
            return [false, $error[0]];
        }

        foreach ($templates as $key => $template) {
            if (!Storage::exists('tmp/' . $folder . '/templates/' . $template . '.twig')) {
                return [false, 'Not found template ' . $template . '.twig'];
            }
        }

        return [true , 'Validation successful'];
    }

    private function collectTemplates($data)
    {
        $templates = [];

        foreach ($data as $key => $value) {
            // Si la clave es 'template', la añade a la lista de templates
            if ($key === 'template') {
                $templates[] = $value;
            }

            // Si el valor es un array, busca recursivamente en él
            if (is_array($value)) {
                $templates = array_merge($templates, $this->collectTemplates($value));
            }
        }

        return $templates;
    }
}
