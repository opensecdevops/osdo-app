<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Package;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Support\Facades\Storage;

class GenerateRequests extends FormRequest
{

    private String $packageRoute;
    private $blocksGenerate;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {

        $vendor = $this->route('vendor');
        $packageName = $this->route('package');
        $id = $this->route('id');

        $package = Package::where('name', $vendor . '/' . $packageName)->first();

        if (!$package) {
            throw new NotFoundHttpException('Package not found');
        }


        $version = $package->versions()->where('id', $id)->first();

        if (!$version) {
            throw new NotFoundHttpException('Version not found');
        }

        $service = $package->service()->first();

        $this->packageRoute = sprintf('packages/%s/%s/%s/%s/', $service->service, $vendor, $packageName, $version->commit);

        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {

        $form = Storage::get($this->packageRoute.'/config.json');

        $form = json_decode($form, true);

        $rules = [];

        $status = $this->get('status');


        foreach($status as $key => $value){
            if($value['view'] === true){
                $this->blocksGenerate[] = $key;
            }
        }

        foreach ($form['blocks'] as $block) {
            if (in_array($block['template'], $this->blocksGenerate)) {
                foreach($block['fields'] as $field){
                    if(isset($field['rules'])){
                        $rules['data.'.$block['template'].'.'.$field['name']] = $field['rules'];
                    }
                }
            }
        }

        return $rules;
    }

    public function attributes()
    {

        $form = Storage::get($this->packageRoute.'/config.json');

        $form = json_decode($form, true);
        
        $attributes = [];
    
        foreach ($form['blocks'] as $section) {
            foreach ($section['fields'] as $formField) {
                $attributes['data.'.$section['template'] . '.' . $formField['name']] = $section['name'].' '.$formField['label'];
            }
        }
    
        return $attributes;
    }


    public function getPackageRoute(): string
    {
        return $this->packageRoute;
    }

    public function getBlocksGenerate(): array
    {
        return $this->blocksGenerate;
    }
}
