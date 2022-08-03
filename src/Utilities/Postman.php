<?php
/*
 * MyTh Ahmed Faiz Copyright © 2016-2022 All rights reserved.
 * Email: mythpe@gmail.com
 * Mobile: +966590470092
 * Website: https://www.4myth.com
 */

namespace Myth\LaravelTools\Utilities;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Exists;
use Illuminate\Validation\Rules\Unique;

/**
 *
 */
class Postman
{
    /**
     * name on translation key
     *
     * @var string
     */
    const DESCRIPTION_KEY = 'postman.description';

    /**
     * Variables will insert to postman
     *
     * @var array
     */
    public array $collectionVariables = [];

    /**
     * Postman items
     *
     * @var array
     */
    protected array $items = [];

    /**
     * Postman variable of URL
     */
    protected string $urlVariableName;

    /**
     * Postman variable of auth token
     */
    protected string $tokenVariableName;

    /**
     * Postman variable of language
     */
    protected string $localeVariableName;

    /**
     * Postman header variable of language
     */
    protected string $localeHeaderVariableName;

    /**
     * The name of middleware used in auth-routes to create documentation
     */
    protected string $middlewareName;

    /**
     * Postman json file name
     *
     * @var string
     */
    protected string $fileName;

    /**
     * Name of postman collection
     */
    protected string $collectionName;

    /**
     * Postman domain
     */
    protected string $domain;

    /**
     * Postman default locale
     */
    protected string $locale;

    /**
     * Postman collection id
     *
     * @var null|string
     */
    protected ?string $collectionId = null;

    /**
     * Postman collection exporter id
     *
     * @var ?null|string
     */
    protected ?string $exporterId;

    /**
     * Postman constructor.
     */
    public function __construct()
    {
        $this->domain = config('app.url');
        $this->collectionId = config('4myth-tools.postman.postman_id');
        $this->exporterId = config('4myth-tools.postman.exporter_id');
        $this->locale = config('app.locale', 'ar');
        $this->collectionName = config('4myth-tools.postman.collection_name') ?: config('app.name');
        $this->fileName = Str::finish(config('4myth-tools.postman.file_name', 'postman-collection'), '.json');
        $this->middlewareName = config('4myth-tools.postman.middleware_name', 'postman');
        $this->localeHeaderVariableName = config('4myth-tools.postman.locale_header_variable_name', 'App-Locale');
        $this->localeVariableName = config('4myth-tools.postman.locale_variable_name', 'locale');
        $this->tokenVariableName = config('4myth-tools.postman.token_variable_name', 'token');
        $this->urlVariableName = config('4myth-tools.postman.url_variable_name', 'url');
    }

    /**
     * Generate postman documentation
     *
     * @return array
     */
    public function documentation(): array
    {

        $item = $this->getItems();
        $info = $this->getFileInfo();
        //$auth = $this->getAuth();
        $variable = $this->getCollectionVariables();

        $file = [
            'info'     => $info,
            'item'     => array_values($item),
            'variable' => $variable,
        ];
        $this->disk()->put(Str::finish($this->getFileName(), '.json'), json_encode($file));
        return $file;
    }

    /**
     * @param array $items
     */
    public function setItems(array $items): void
    {
        $this->items = $items;
    }

    /**
     * @return \Illuminate\Contracts\Filesystem\Filesystem|\Illuminate\Filesystem\FilesystemAdapter
     */
    public function disk()
    {
        return Storage::disk('root');
    }

    /**
     * @return string
     */
    public function getFileName(): string
    {
        return $this->fileName;
    }

    /**
     * @param string $fileName
     */
    public function setFileName(string $fileName): void
    {
        $this->fileName = Str::finish($fileName, '.json');
    }

    /**
     * @return string
     */
    public function getUrlVariableName(): string
    {
        return $this->urlVariableName;
    }

    /**
     * @param string $urlVariableName
     */
    public function setUrlVariableName(string $urlVariableName): void
    {
        $this->urlVariableName = $urlVariableName;
    }

    /**
     * @return string
     */
    public function getMiddlewareName(): string
    {
        return $this->middlewareName;
    }

    /**
     * @param string $middlewareName
     */
    public function setMiddlewareName(string $middlewareName): void
    {
        $this->middlewareName = $middlewareName;
    }

    /**
     * Postman body mode. urlencoded, formdata
     *
     * @return string
     */
    public function getBodyMode(): string
    {
        return 'formdata';
    }

    /**
     * Get the methods name to add exec
     *
     * @return array<int,string>
     */
    public function getScriptActions(): array
    {
        return ['login', 'loginMobile', 'register', 'resetPassword'];
    }

    /**
     * @return string
     */
    public function getTokenVariableName(): string
    {
        return $this->tokenVariableName;
    }

    /**
     * @param string $tokenVariableName
     */
    public function setTokenVariableName(string $tokenVariableName): void
    {
        $this->tokenVariableName = $tokenVariableName;
    }

    /**
     * @return string
     */
    public function getCollectionName(): string
    {
        return $this->collectionName;
    }

    /**
     * @param mixed|string $collectionName
     */
    public function setCollectionName($collectionName): void
    {
        $this->collectionName = $collectionName;
    }

    /**
     * @return string
     */
    public function getLocaleVariableName(): string
    {
        return $this->localeVariableName;
    }

    /**
     * @param string $localeVariableName
     */
    public function setLocaleVariableName(string $localeVariableName): void
    {
        $this->localeVariableName = $localeVariableName;
    }

    /**
     * @return string
     */
    public function getLocale(): string
    {
        return $this->locale;
    }

    /**
     * @param string $locale
     */
    public function setLocale(string $locale): void
    {
        $this->locale = $locale;
    }

    /**
     * @return string
     */
    public function getDomain(): string
    {
        return $this->domain;
    }

    /**
     * @param mixed|string $domain
     */
    public function setDomain($domain): void
    {
        $this->domain = $domain;
    }

    /**
     * @return string
     */
    public function getLocaleHeaderVariableName(): string
    {
        return $this->localeHeaderVariableName;
    }

    /**
     * @param string $localeHeaderVariableName
     */
    public function setLocaleHeaderVariableName(string $localeHeaderVariableName): void
    {
        $this->localeHeaderVariableName = $localeHeaderVariableName;
    }

    /**
     * @return string
     */
    public function getFilePath(): string
    {
        return $this->disk()->path($this->getFileName());
    }

    /**
     * @return null|string
     */
    public function getCollectionId(): ?string
    {
        return $this->collectionId;
    }

    /**
     * @param string $collectionId
     */
    public function setCollectionId(string $collectionId): void
    {
        $this->collectionId = $collectionId;
    }

    /**
     * @return string|null
     */
    public function getExporterId(): ?string
    {
        return $this->exporterId;
    }

    /**
     * @param string|null $exporterId
     */
    public function setExporterId(?string $exporterId): void
    {
        $this->exporterId = $exporterId;
    }

    /**
     * Generate API items
     *
     * @return array
     */
    protected function getItems(): array
    {
        // $appLocale = app()->getLocale();
        $authCollection = [];
        $gustCollection = [];
        $routes = Route::getRoutes()->getRoutes();
        $header = $this->getHeaders();
        $domain = "{{{$this->getUrlVariableName()}}}";

        foreach($routes as $route){
            $action = $route->getAction();
            $middleware = $action['middleware'] ?? [];
            if(!in_array($this->getMiddlewareName(), $middleware)){
                continue;
            }
            $auth = false;
            foreach($middleware as $value){
                if(Str::contains($value, 'auth')){
                    $auth = !0;
                    break;
                }
            }

            $controllerName = Str::kebab(class_basename($route->getController()));
            $controllerName = trim(str_ireplace('controller', '', $controllerName), '-');
            $controllerName = trim(str_ireplace('-', ' ', $controllerName));
            $controllerName = ucfirst($controllerName);

            $actionName = $route->getActionMethod();
            $isGeneralAction = in_array(strtolower($actionName), ['index', 'view', 'show', 'update', 'edit', 'store', 'create'], !0);
            $requestName = ucfirst(str_ireplace(['-', '\s+'], ' ', Str::kebab($actionName)));

            $baseUri = ltrim($route->uri, '/');
            $uri = preg_replace(['/(\{)+/', '/(\})+/'], ['{{', '}}'], $baseUri);

            $explodeVars = explode('/', $baseUri);
            foreach($explodeVars as $str){
                if(str_contains($str, '{')){
                    $key = preg_replace(['/(\{)+/', '/(\})+/'], '', $str);
                    if(!array_key_exists($key, $this->collectionVariables)){
                        $this->collectionVariables[$key] = 1;
                    }
                }
            }

            foreach($route->methods as $method){
                if(in_array($method, ['HEAD', 'PATCH'])){
                    continue;
                }
                $isGet = in_array($method, ['GET', 'HEAD']);
                $isPost = in_array($method, ['POST', 'PUT']);
                $isPut = $method == 'PUT';
                $query = [];
                $formData = [];

                /** Generate Rules */
                $controllerRuleMethods = [
                    // # Rules by function name
                    // # _{METHOD}Rules
                    "_{$actionName}Rules",
                    // # Rules by function & method name
                    // # _{METHOD}PostRules
                    "_{$actionName}".ucfirst(strtolower($method))."Rules",

                ];
                if($isGeneralAction){
                    // # General rules.
                    $controllerRuleMethods[] = "getRules";
                }

                $controller = $route->getController();
                $rules = [];
                foreach($controllerRuleMethods as $requestRule){
                    if(method_exists($controller, $requestRule)){
                        $rules = $controller->{$requestRule}();
                        //d(class_basename( $controller),$rules);
                        break;
                    }
                }

                /** Generate Examples */
                $controllerExampleMethods = [
                    // Example by function name
                    // _{METHOD}Example
                    "_{$actionName}Example",
                    // Example by function & method name
                    // _{METHOD}GetExample
                    "_{$actionName}".ucfirst(strtolower($method))."Example",
                ];
                if($isPost){
                    // Controller example
                    $controllerExampleMethods[] = "_controllerExample";
                }

                $examples = [];
                foreach($controllerExampleMethods as $example){
                    if(method_exists($controller, $example)){
                        $examples = $controller->{$example}();
                        break;
                    }
                }

                foreach($rules as $key => $rule){
                    $formRule = $this->parseFormRules($rule);
                    $isConfirmed = Str::contains($formRule, 'confirmed');
                    $isArray = Str::contains($formRule, 'array');
                    $isFile = Str::contains($formRule, ['file', 'image']);
                    $description = $this->getFullExampleDescription($examples, $key, $key, $rule);
                    //d($description);
                    $formDataKey = $key;
                    $type = 'text';
                    if($isArray){
                        if(!Str::contains($formDataKey, '.')){
                            continue;
                        }
                        $formDataKey .= "[0]";
                    }
                    if(Str::contains($formDataKey, ($s = '.*.'))){
                        $formDataKey = implode('[0][', explode($s, $formDataKey)).']';
                        //d($formDataKey);
                    }
                    $value = $this->findExample($formDataKey, $examples);
                    if($isFile){
                        $type = "file";
                        $value = "";
                    }
                    $methodData = [
                        'key'         => $formDataKey,
                        'value'       => $value,
                        'description' => $description,
                        'type'        => $type,
                        //'disabled'    => !$this->isExample($key, $examples),
                        'disabled'    => !$this->findExample($formDataKey, $examples),
                    ];
                    if($isPost){
                        $formData[] = $methodData;
                    }
                    else{
                        if(!$isGeneralAction){
                            $query[] = $methodData;
                        }
                    }

                    if(!$isArray && $isConfirmed){
                        $attr = "{$formDataKey}_confirmation";
                        $k = "attributes.{$attr}";
                        $def = ucwords($attr);
                        $ar = trans_has($k, 'ar', !1) ? __($k, [], 'ar') : $def;
                        $en = trans_has($k, 'en', !1) ? __($k, [], 'en') : $def;

                        $formData[] = [
                            'key'         => $attr,
                            'value'       => $value,
                            'description' => $en.($en != $ar ? " - {$ar}" : ''),
                            'type'        => 'text',
                            'disabled'    => $this->isExample($key, $examples),
                        ];
                    }
                }

                $bodyMode = $this->getBodyMode();

                if($isPut){
                    $method = 'POST';
                    if(!array_key_exists('_method', $formData)){
                        $formData = array_merge([
                            [
                                'key'         => '_method',
                                'value'       => 'put',
                                'description' => 'Request method is PUT if formdata is [formdata]',
                                'type'        => 'text',
                                'disabled'    => false,
                            ],
                        ], $formData);
                    }
                }
                $body = [
                    'mode'    => $bodyMode,
                    $bodyMode => $formData,
                ];
                if($isGet){
                    $queryExamples = $this->findQueryExamples($examples);
                    if(!empty($queryExamples)){
                        foreach($query as $queryKey => $v){
                            $k = $v['key'] ?? null;
                            if($k){
                                foreach($queryExamples as $_exampleKey => $_example){
                                    $exampleKey = $_example['key'] ?? null;
                                    if($exampleKey && $k == $exampleKey){
                                        $query[$queryKey]['value'] = $_example['value'] ?? $query[$queryKey]['value'];
                                        unset($queryExamples[$_exampleKey]);
                                    }
                                }
                            }
                        }
                        $queryExamples = array_values($queryExamples);
                    }
                    $query = array_merge($query, $queryExamples);
                    if(in_array($actionName, ['index', 'allIndex'])){
                        $query = array_merge($query, $this->getControllerParams($route->getController()));
                    }
                }

                $url = [
                    'raw'   => "{$domain}/{$uri}",
                    'host'  => [$domain],
                    'path'  => [$uri],
                    'query' => $query,
                ];
                $_request = [
                    'method' => $method,
                    'header' => $header,
                    'body'   => $body,
                    'url'    => $url,
                ];
                if($auth){
                    $_request['auth'] = $this->getAuth();
                }
                $itemName = $requestName;
                //$itemName = "{$requestName} - ";
                //app()->setLocale('ar');
                //$itemName .= trans_choice($requestName, 2);
                //app()->setLocale('en');
                //d($itemName);

                $itemDescriptionMethod = "_{$actionName}Description";
                $itemDescription = '';
                if(method_exists($controller, $itemDescriptionMethod)){
                    $itemDescription = $controller->{$itemDescriptionMethod}();
                }

                if(trans_has($k = "postman.descriptions.".$controller::class.".$actionName", $this->getLocale())){
                    $itemDescription = __($k, [
                        'controller' => $controllerName,
                        'method'     => $actionName,
                    ], $this->getLocale());
                }
                $item = [
                    'name'        => $itemName,
                    'description' => $itemDescription,
                    'request'     => $_request,
                    'response'    => [],
                    'event'       => [],
                ];
                if(in_array($actionName, $this->getScriptActions())){
                    $item['event'][] = [
                        'listen' => 'test',
                        'script' => [
                            "exec" => [
                                "pm.test(\"Status code is 200\", function () {
pm.response.to.have.status(200);
});
const response = pm.response.json();
pm.globals.set(\"{$this->getTokenVariableName()}\",response.token);",
                            ],
                            "type" => "text/javascript",
                        ],
                    ];
                }

                // $folderName = ucfirst(Str::plural($controllerName))." - ";
                // app()->setLocale('ar');
                $choiceName = ucfirst(Str::camel(Str::plural($controllerName)));
                $folderName = trans_choice("choice.$choiceName", 2, [], 'en').' - '.trans_choice("choice.$choiceName", 2, [], 'ar');
                //d($folderName);
                if($auth){
                    if(!array_key_exists($folderName, $authCollection)){
                        $authCollection[$folderName] = [
                            'name' => $folderName,
                            'item' => [],
                        ];
                    }
                    $authCollection[$folderName]['item'][] = $item;
                }
                else{
                    if(!array_key_exists($folderName, $gustCollection)){
                        $gustCollection[$folderName] = [
                            'name' => $folderName,
                            'item' => [],
                        ];
                    }
                    $gustCollection[$folderName]['item'][] = $item;
                }
                //$gustCollection[] = $item;

                //$items[] = [
                //    'data'  => $item,
                //    'route' => $route,
                //];
            }
        }
        // app()->setLocale($appLocale);
        //foreach ($items as $key => $value) {
        //    if (!is_numeric($key)) {
        //        $collection[] = [
        //            'name' => $key,
        //            'item' => $value,
        //        ];
        //    }
        //    else {
        //        $collection[] = $value;
        //    }
        //}
        //d($collection);
        //d($authCollection);
        ksort($authCollection);
        ksort($gustCollection);
        $collection = [
            [
                'name'        => 'Auth',
                'description' => __('postman.folder.auth_description') ?: '',
                'item'        => array_values($authCollection),
            ],
            [
                'name'        => 'Gust',
                'description' => __('postman.folder.gust_description') ?: '',
                'item'        => array_values($gustCollection),
            ],
        ];
        //d($collection);
        //$collection = $this->parseItems($items);
        return array_values($collection);
    }

    /**
     * Postman file information
     *
     * @return array
     */
    protected function getFileInfo(): array
    {
        $info = [
            //     '_postman_id' => Str::random(36),
            'name'        => $this->getCollectionName(),
            'description' => $this->getDescription(),
            'schema'      => "https://schema.getpostman.com/json/collection/v2.1.0/collection.json",
        ];
        if($this->getCollectionId()){
            $info['_postman_id'] = $this->getCollectionId();
        }

        if($this->getExporterId()){
            $info['_exporter_id'] = $this->getExporterId();
        }

        return $info;
    }

    /**
     * Postman variables
     *
     * @return array
     */
    protected function getCollectionVariables(): array
    {
        $vars = [
            [
                "key"   => $this->getLocaleVariableName(),
                "value" => $this->getLocale(),
            ],
            [
                "key"   => $this->getUrlVariableName(),
                "value" => $this->getDomain(),
            ],
            // [
            //     "key"   => $this->getTokenVariableName(),
            //     "value" => "",
            // ],
        ];

        foreach($this->collectionVariables as $key => $value){
            $vars[] = [
                'key'   => $key,
                'value' => $value,
            ];
        }

        return $vars;
    }

    /**
     * Postman request header
     *
     * @return \string[][]
     */
    protected function getHeaders(): array
    {
        $headers = [
            [
                "key"   => "Accept",
                "value" => "application/json",
                "type"  => "text",
            ],
        ];
        if($this->getLocaleHeaderVariableName()){
            $headers[] = [
                "key"   => $this->getLocaleHeaderVariableName(),
                "value" => "{{{$this->getLocaleVariableName()}}}",
                "type"  => "text",
            ];
        }
        return $headers;
        // return [
        // [
        //     "key"   => "App-Currency",
        //     "value" => config('4myth-tools.currency_code'),
        //     "type"  => "text",
        // ],
        // [
        //     "key"   => "App-Currency-Balance",
        //     "value" => config('4myth-tools.currency_balance'),
        //     "type"  => "text",
        // ],
        // [
        //     "key"   => "App-Country",
        //     "value" => config('4myth-tools.country_code'),
        //     "type"  => "text",
        // ],
        // ];
    }

    /**
     * @param $rules
     *
     * @return string
     */
    protected function parseFormRules($rules): string
    {
        if(!is_array($rules)){
            $rules = explode(',', $rules);
        }
        $rules = array_filter($rules);
        foreach($rules as $k => $rule){
            if($rule instanceof Unique){
                $rules[$k] = "unique";
                continue;
            }
            if($rule instanceof Exists){
                $rules[$k] = null;
            }
        }
        //d($rules);
        return implode(', ', array_filter($rules));
    }

    /**
     * @param array $examples
     * @param string $key
     * @param string|null $attribute
     * @param array $rule
     *
     * @return string
     */
    protected function getFullExampleDescription(array $examples, string $key, string $attribute = null, array $rule = []): string
    {
        $en = null;
        $ar = null;
        $rule = array_unique($rule);
        $formRule = $this->parseFormRules($rule);
        //$str = $this->findExampleDescription($key, $examples);
        $str = "";
        //d($examples);
        if(!is_null($attribute)){
            $k = "attributes.{$attribute}";
            $def = ucwords($attribute);
            $ar = trans_has($k, 'ar') ? __($k, [], 'ar') : $def;
            $en = trans_has($k, 'en') ? __($k, [], 'en') : $def;
        }

        if(!is_null($en)){
            $str = trim("{$en} - ").trim($str);
        }

        $str = trim($str)." [{$formRule}] ";
        //d($str);
        if(!is_null($ar) && $ar != $en){
            $str = trim($str)." - {$ar}";
        }
        return (string) $str;
    }

    /**
     * @param $key
     * @param array $examples
     *
     * @return string
     */
    protected function findExample($key, array $examples): string
    {
        $str = '';
        if(array_key_exists($key, $examples)){
            $v = $examples[$key];
            $str = is_array($v) ? ($v['value'] ?? '') : $v;
        }
        else{
            foreach($examples as $example){
                if($key == ($example['key'] ?? null)){
                    $str = ($example['value'] ?? '');
                    break;
                }
            }
        }
        return (string) $str;
    }

    /**
     * @param $key
     * @param array $examples
     *
     * @return bool
     */
    protected function isExample($key, array $examples): bool
    {
        if(array_key_exists($key, $examples)){
            return !0;
        }
        else{
            foreach($examples as $example){
                if($key == ($example['key'] ?? null)){
                    return !0;
                }
            }
        }
        return !1;
    }

    /**
     * @param array $examples
     *
     * @return array
     */
    protected function findQueryExamples(array $examples): array
    {
        $query = [];
        foreach($examples as $k => $example){
            $ex = [
                'key'         => is_array($example) ? $example['key'] : $k,
                'value'       => is_array($example) ? $example['value'] : $example,
                'description' => is_array($example) ? $example['description'] : __("attributes.{$k}"),
                'disabled'    => !0,
            ];
            $query[] = $ex;
        }
        return $query;
    }

    /**
     * Postman query params
     *
     * @param \App\Http\Controllers\Controller $controller
     *
     * @return array
     */
    protected function getControllerParams($controller): array
    {
        $pagination = [
            [
                'key'         => $controller->requestWithKey,
                'value'       => 'users,items',
                'description' => 'Relations to append of models',
                'disabled'    => !0,
            ],
            [
                'key'         => $controller->pageKey,
                'value'       => 1,
                'description' => 'The page of pagination',
                'disabled'    => !0,
            ],
            [
                'key'         => $controller->itemsPerPageKey,
                'value'       => 25,
                'description' => 'The page of pagination',
                'disabled'    => !0,
            ],
            [
                'key'         => $controller->sortByRequestKey,
                'value'       => json_encode(['name', 'date']),
                'description' => 'Listing sort',
                'disabled'    => !0,
            ],
            [
                'key'         => $controller->sortDescRequestKey,
                'value'       => json_encode([1, 0]),
                'description' => 'The descending sorting for each key. true or false',
                'disabled'    => !0,
            ],
            [
                'key'         => $controller->searchRequestKey,
                'value'       => 'example',
                'description' => 'The value of searching',
                'disabled'    => !0,
            ],
            [
                'key'         => $controller->filterRequestKey,
                'value'       => json_encode(['user_id' => 1]),
                'description' => 'Filter items by attribute name',
                'disabled'    => !0,
            ],
        ];
        $items = [
            [
                'id'   => 1,
                'date' => "0000-00-00",
            ],
        ];
        $headerItems = [
            [
                'text'  => 'Item ID',
                'value' => 'id',
            ],
            [
                'text'  => 'Item Date',
                'value' => 'date',
            ],
        ];
        $params = [
            [
                'key'         => 'indexType',
                'value'       => 'index',
                'description' => 'The type of index. [index,pdf,excel]',
                'disabled'    => !0,
            ],
            [
                'key'         => 'toUrl',
                'value'       => '1',
                'description' => 'Convert export to json url. only if indexType: pdf or excel',
                'disabled'    => !0,
            ],
            [
                'key'         => 'pageTitle',
                'value'       => 'Title',
                'description' => 'The title of PDF file. only if indexType is pdf',
                'disabled'    => !0,
            ],
            [
                'key'         => 'items',
                'value'       => json_encode($items),
                'description' => 'The items to exported. only if indexType: pdf or excel',
                'disabled'    => !0,
            ],
            [
                'key'         => 'headerItems',
                'value'       => json_encode($headerItems),
                'description' => 'The headers of export. only if indexType: pdf or excel',
                'disabled'    => !0,
            ],
        ];

        return array_merge($pagination, $params);
    }

    /**
     * Postman authentication
     *
     * @return array
     */
    protected function getAuth(): array
    {
        return [
            "type"   => "bearer",
            "bearer" => [
                [
                    "key"   => $this->getTokenVariableName(),
                    "value" => "{{{$this->getTokenVariableName()}}}",
                    "type"  => "string",
                ],
            ],
        ];
    }

    /**
     * @param $key
     * @param array $examples
     *
     * @return string
     */
    protected function findExampleDescription($key, array $examples): string
    {
        $str = '';
        if(array_key_exists($key, $examples)){
            $v = $examples[$key];
            $str = is_array($v) ? ($v['description'] ?? '') : $v;
        }
        else{
            foreach($examples as $example){
                if($key == ($example['key'] ?? null)){
                    $str = ($example['description'] ?? '');
                    break;
                }
            }
        }
        return (string) $str;
    }

    /**
     * Get description of postman documentation
     *
     * @return string
     */
    protected function getDescription(): string
    {
        $description = '';
        if(trans_has(static::DESCRIPTION_KEY, $this->getLocale())){
            $description .= (string) (__(static::DESCRIPTION_KEY, [
                'name' => appName($this->getLocale()),
                'year' => now()->format("Y"),
            ]) ?: '');
        }
        $description .= PHP_EOL."Powered by MyTh All rights reserved.";
        return $description;
    }
}
