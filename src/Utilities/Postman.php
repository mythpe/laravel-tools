<?php
/*
 * MyTh Ahmed Faiz Copyright © 2016-2023 All rights reserved.
 * Email: mythpe@gmail.com
 * Mobile: +966590470092
 * Website: https://www.4myth.com
 * Github: https://github.com/mythpe
 */

namespace Myth\LaravelTools\Utilities;

use Exception;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Exists;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\Rules\Unique;

class Postman
{
    /**
     * Name on translation key.
     *
     * @var string
     */
    const DESCRIPTION_KEY = 'postman.description';

    /**
     * Name on translation key.
     *
     * @var string
     */
    const AUTH_DESCRIPTION_KEY = 'postman.folder.auth_description';

    /**
     * Name on translation key.
     *
     * @var string
     */
    const GUST_DESCRIPTION_KEY = 'postman.folder.gust_description';

    /**
     * Name on translation key.
     *
     * @var string
     */
    const FOLDER_KEY = 'postman.folder';

    /**
     * Name on translation key
     *
     * @var string
     */
    const ITEMS_KEY = 'postman.items';

    /**
     * Name on translation key
     *
     * @var string
     */
    const DESCRIPTIONS_KEY = 'postman.descriptions';

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
    public array $items = [];

    /**
     * Postman variable of URL
     */
    public string $urlVariableName;

    /**
     * Postman variable of auth token
     */
    public string $tokenVariableName;

    /**
     * Postman variable of language
     */
    public string $localeVariableName;

    /**
     * Postman header variable of language
     */
    public string $localeHeaderVariableName;

    /**
     * The name of middleware used in auth-routes to create documentation
     */
    public string $middlewareName;

    /**
     * Postman json file name
     *
     * @var string
     */
    public string $fileName;

    /**
     * Name of postman collection
     */
    public string $collectionName;

    /**
     * Postman domain
     */
    public string $domain;

    /**
     * Postman default locale
     */
    public string $locale;

    /**
     * Postman collection id
     *
     * @var null|string
     */
    public ?string $collectionId = null;

    /**
     * Postman collection exporter id
     *
     * @var ?null|string
     */
    public ?string $exporterId;

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
        $locale = app()->getLocale();
        app()->setLocale($this->getLocale());
        $item = $this->getItems();
        $info = $this->getFileInfo();
        $variable = $this->getCollectionVariables();

        $file = [
            'info'     => $info,
            'item'     => array_values($item),
            'variable' => $variable,
        ];
        $this->disk()->put(Str::finish($this->getFileName(), '.json'), json_encode($file));
        app()->setLocale($locale);
        return $file;
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
     * Generate API items
     *
     * @return array
     */
    public function getItems(): array
    {
        $authCollection = [];
        $gustCollection = [];
        $routes = Route::getRoutes()->getRoutes();
        $header = $this->getHeaders();
        $domain = "{{{$this->getUrlVariableName()}}}";

        foreach ($routes as $route) {
            $action = $route->getAction();
            $middleware = $action['middleware'] ?? [];
            if (!in_array($this->getMiddlewareName(), $middleware)) {
                continue;
            }
            $auth = false;
            foreach ($middleware as $value) {
                if (Str::contains($value, 'auth')) {
                    $auth = !0;
                    break;
                }
            }
            $controller = $route->getController();
            $controllerClassName = $controller::class;
            $controllerName = Str::kebab(class_basename($controller));
            $controllerName = str_ireplace('-controller', '', $controllerName);
            $controllerName = ucwords($controllerName, '-');
            $controllerName = trim(str_ireplace('-', ' ', $controllerName));
            $actionName = $route->getActionMethod();
            $isGeneralAction = in_array(strtolower($actionName), ['index', 'view', 'show', 'update', 'edit', 'store', 'create', 'destroy'], !0);
            $requestName = ucfirst(str_ireplace(['-', '\s+'], ' ', Str::kebab($actionName)));

            $baseUri = ltrim($route->uri, '/');
            $uri = preg_replace(['/(\{)+/', '/(\})+/'], ['{{', '}}'], $baseUri);

            $explodeVars = explode('/', $baseUri);
            foreach ($explodeVars as $str) {
                if (str_contains($str, '{')) {
                    $key = preg_replace(['/(\{)+/', '/(\})+/'], '', $str);
                    if (!array_key_exists($key, $this->collectionVariables)) {
                        $this->collectionVariables[$key] = 1;
                    }
                }
            }

            foreach ($route->methods as $method) {
                if (in_array($method, ['HEAD', 'PATCH'])) {
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
                    "_$actionName".ucfirst(strtolower($method))."Rules",

                ];
                if ($isGeneralAction) {
                    // # General rules.
                    $controllerRuleMethods[] = "getRules";
                }


                $rules = [];
                foreach ($controllerRuleMethods as $requestRule) {
                    if (method_exists($controller, $requestRule)) {
                        $rules = $controller->{$requestRule}();
                        break;
                    }
                }

                /** Generate Examples */
                $controllerExampleMethods = [
                    // # Example by function name
                    // # _{METHOD}Example
                    "_{$actionName}Example",
                    // # Example by function & method name
                    // # _{METHOD}GetExample
                    "_$actionName".ucfirst(strtolower($method))."Example",
                ];
                if ($isPost) {
                    // # Controller example
                    $controllerExampleMethods[] = "_controllerExample";
                }

                $examples = [];
                foreach ($controllerExampleMethods as $example) {
                    if (method_exists($controller, $example)) {
                        $examples = $controller->{$example}();
                        if ($examples === !0) {
                            $examples = $this->getControllerPaginationParams($controller);
                        }
                        break;
                    }
                }

                foreach ($rules as $key => $rule) {
                    if ($actionName == 'index' && $isPost) {
                        break;
                    }
                    $formRule = $this->parseFormRules($rule);
                    $isRequired = Str::contains(strtolower($formRule), 'required');
                    $isMobile = Str::contains(strtolower($formRule), 'mobile');
                    $isNumeric = Str::contains(strtolower($formRule), ['int', 'integer', 'double', 'float', 'numeric']);
                    $isEmail = Str::contains(strtolower($formRule), 'email');
                    $isConfirmed = Str::contains(strtolower($formRule), 'confirmed');
                    $isArray = Str::contains(strtolower($formRule), 'array');
                    $isFile = Str::contains(strtolower($formRule), ['file', 'image', 'imagefile']);
                    $description = $this->getFullDescription($key, $rule);
                    $formDataKey = $key;
                    $type = 'text';
                    /**
                     * Array examples:
                     * -- 'model_id' => ['array','required'] [===> model_id[0]
                     * -- 'items.*.id' => ['array','required'] [===> items[0][id]
                     */
                    if ($isArray) {
                        if (!Str::contains($formDataKey, '.')) {
                            $keys = array_keys($rules);
                            $hasChild = !1;
                            foreach ($keys as $checkArray) {
                                if ($hasChild) {
                                    break;
                                }
                                $hasChild = Str::contains($checkArray, "$key.");
                            }
                            if ($hasChild) {
                                continue;
                            }
                            else {
                                $formDataKey .= "[0]";
                            }
                        }
                    }
                    if (Str::contains($formDataKey, ($s = '.*.'))) {
                        $formDataKey = implode('[0][', explode($s, $formDataKey)).']';
                        //d($formDataKey);
                    }
                    if (Str::endsWith($formDataKey, ($s = '.*'))) {
                        $formDataKey = Str::before($formDataKey, $s).'[0]';
                    }
                    $value = $this->findExample($formDataKey, $examples);
                    if ($isFile) {
                        $type = "file";
                        $value = "";
                    }
                    elseif (!$value) {
                        if ($isMobile) {
                            $numerify = config('4myth-tools.postman.fake_mobile') ?: '05########';
                            $value = fake()->numerify($numerify);
                        }
                        elseif ($isEmail) {
                            $value = fake()->companyEmail();
                        }
                        elseif (Str::contains($key, ['name', '_name'])) {
                            $value = fake()->name;
                        }
                        elseif (Str::endsWith($key, '_id') || $isNumeric) {
                            $value = "1";
                        }
                    }
                    $methodData = [
                        'key'         => $formDataKey,
                        'value'       => $value,
                        'description' => $description,
                        'type'        => $type,
                        //'disabled'    => !$this->isExample($key, $examples),
                        'disabled'    => !$this->findExample($formDataKey, $examples) && !$isRequired,
                        //'disabled'    => !1,
                    ];
                    if ($isPost) {
                        $formData[] = $methodData;
                    }
                    else {
                        if (!$isGeneralAction) {
                            $query[] = $methodData;
                        }
                    }

                    if (!$isArray && $isConfirmed) {
                        $attr = "{$formDataKey}_confirmation";
                        $k = "attributes.$attr";
                        $def = ucwords($attr);
                        $ar = trans_has($k, 'ar', !1) ? __($k, [], 'ar') : $def;
                        $en = trans_has($k, 'en', !1) ? __($k, [], 'en') : $def;

                        $formData[] = [
                            'key'         => $attr,
                            'value'       => $value,
                            'description' => $en.($en != $ar ? " - $ar" : ''),
                            'type'        => 'text',
                            'disabled'    => $this->isExample($key, $examples),
                        ];
                    }
                }

                $bodyMode = $this->getBodyMode();

                if ($isPut) {
                    $method = 'POST';
                    if (!array_key_exists('_method', $formData)) {
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
                if ($isGet || ($isPost && $actionName == 'index')) {
                    $queryExamples = $this->findQueryExamples($examples);
                    if (!empty($queryExamples)) {
                        foreach ($query as $queryKey => $v) {
                            $k = $v['key'] ?? null;
                            if ($k) {
                                foreach ($queryExamples as $_exampleKey => $_example) {
                                    $exampleKey = $_example['key'] ?? null;
                                    if ($exampleKey && $k == $exampleKey) {
                                        $query[$queryKey]['value'] = $_example['value'] ?? $query[$queryKey]['value'];
                                        unset($queryExamples[$_exampleKey]);
                                    }
                                }
                            }
                        }
                        $queryExamples = array_values($queryExamples);
                    }
                    $query = array_merge($query, $queryExamples);
                    if (in_array($actionName, ['index', 'allIndex']) && $isGet) {
                        $query = array_merge($query, $this->getControllerParams($controller));
                    }

                    if ($actionName == 'indexActiveOnly' || $isPost) {
                        $query = array_merge($query, $this->getControllerPaginationParams($controller));
                    }
                }

                $choiceName = ucfirst(Str::camel(Str::plural($controllerName)));
                $itemName = $requestName;
                $itemArName = $actionName;
                if (in_array($actionName, ['index', 'allIndex', 'indexActiveOnly'])) {
                    $itemArName = 'index';
                    $itemName = 'Index';
                }
                if (strtolower($itemName) == 'index') {
                    $itemName = 'List';
                }

                if (trans_has(($r = static::ITEMS_KEY.".$controllerClassName.$actionName"), 'ar')) {
                    $itemName .= ' - '.trim(__($r, ['name' => '', 'action' => $actionName, 'controller' => $controllerName]));
                }
                elseif (trans_has(($r = "replace.$itemArName"), 'ar')) {
                    $itemName .= ' - '.trim(__($r, ['name' => '', 'action' => $actionName, 'controller' => $controllerName]));
                }
                elseif (trans_has(($r = "global.$itemArName"), 'ar')) {
                    $itemName .= ' - '.trim(__($r, ['name' => '']));
                }

                $requestDescriptionMethod = "_{$actionName}Description";
                $requestDescription = '';
                if (method_exists($controller, $requestDescriptionMethod)) {
                    $requestDescription = $controller->{$requestDescriptionMethod}();
                }
                if (trans_has($k = static::DESCRIPTIONS_KEY.".$controllerClassName.$actionName")) {
                    $requestDescription = __($k, [
                        'controller' => $controllerName,
                        'method'     => $actionName,
                    ]);
                }
                if (!$requestDescription && $isGeneralAction) {
                    try {
                        $name = $controllerName;
                        if (trans_has($k = "choice.$choiceName")) {
                            $name = trans_choice($k, 2);
                        }
                        $requestDescription = __("replace.$actionName", ['name' => $name]);
                    }
                    catch (Exception $exception) {
                        //d($actionName, $choiceName);
                    }
                }
                $url = [
                    'raw'   => "$domain/$uri",
                    'host'  => [$domain],
                    'path'  => [$uri],
                    'query' => $query,
                ];
                $_request = [
                    'method'      => $method,
                    'header'      => $header,
                    'body'        => $body,
                    'url'         => $url,
                    'description' => $requestDescription,
                ];
                if ($auth) {
                    $_request['auth'] = $this->getAuth();
                }

                $item = [
                    'name'     => $itemName,
                    'request'  => $_request,
                    'response' => [],
                    'event'    => [],
                ];
                if (in_array($actionName, $this->getScriptActions())) {
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
                $folderName = $controllerName;
                $folderDescription = '';
                // Folder name
                if (trans_has($k = static::FOLDER_KEY.".$controllerClassName.name")) {
                    $folderName = __($k, ['controller' => $controllerName]);
                }
                else if (trans_has($k = "choice.$choiceName")) {
                    $folderName = trans_choice("choice.$choiceName", 2, [], 'en').' - '.trans_choice("choice.$choiceName", 2, [], 'ar');
                }

                if (trans_has($k = static::FOLDER_KEY.".$controllerClassName.description")) {
                    $folderDescription .= __($k, ['controller' => $controllerName]);
                }
                elseif (trans_has($k = static::FOLDER_KEY.".$controllerClassName")) {
                    $folderTrans = __($k);
                    if (!is_array($folderTrans)) {
                        $folderDescription .= $folderTrans;
                    }
                }

                if ($auth) {
                    if (!array_key_exists($folderName, $authCollection)) {
                        $authCollection[$folderName] = [
                            'name'        => $folderName,
                            'description' => $folderDescription,
                            'item'        => [],
                        ];
                    }
                    $authCollection[$folderName]['item'][] = $item;
                }
                else {
                    if (!array_key_exists($folderName, $gustCollection)) {
                        $gustCollection[$folderName] = [
                            'name'        => $folderName,
                            'description' => $folderDescription,
                            'item'        => [],
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

        ksort($authCollection);
        ksort($gustCollection);
        $collection = [
            [
                'name'        => 'Authenticated user',
                'description' => __(static::AUTH_DESCRIPTION_KEY) ?: '',
                'item'        => array_values($authCollection),
            ],
            [
                'name'        => 'Guest or not Authenticated',
                'description' => __(static::GUST_DESCRIPTION_KEY) ?: '',
                'item'        => array_values($gustCollection),
            ],
        ];

        return array_values($collection);
    }

    /**
     * @param array $items
     */
    public function setItems(array $items): void
    {
        $this->items = $items;
    }

    /**
     * Postman request header
     *
     * @return string[][]
     */
    public function getHeaders(): array
    {
        $headers = [
            [
                "key"   => "Accept",
                "value" => "application/json",
                "type"  => "text",
            ],
        ];
        if ($this->getLocaleHeaderVariableName()) {
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
     * Postman query params
     *
     * @param $controller
     *
     * @return array
     */
    public function getControllerPaginationParams($controller): array
    {
        return [
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
                'description' => 'Number of results per page',
                'disabled'    => !0,
            ],
            //[
            //    'key'         => $controller->sortByRequestKey,
            //    'value'       => json_encode(['name', 'date']),
            //    'description' => 'Listing sort',
            //    'disabled'    => !0,
            //],
            [
                'key'         => "$controller->sortByRequestKey[0]",
                'value'       => 'name',
                'description' => 'Listing sort. Array',
                'disabled'    => !0,
            ],
            [
                'key'         => "$controller->sortByRequestKey[1]",
                'value'       => 'date',
                'description' => 'Listing sort. Array',
                'disabled'    => !0,
            ],
            //[
            //    'key'         => $controller->sortDescRequestKey,
            //    'value'       => json_encode([1, 0]),
            //    'description' => 'The descending sorting for each key. true or false',
            //    'disabled'    => !0,
            //],
            [
                'key'         => "$controller->sortDescRequestKey[0]",
                'value'       => '1',
                'description' => 'The descending sorting for each key. true,false,1,0',
                'disabled'    => !0,
            ],
            [
                'key'         => "$controller->sortDescRequestKey[1]",
                'value'       => '0',
                'description' => 'The descending sorting for each key. true,false,1,0',
                'disabled'    => !0,
            ],
            [
                'key'         => $controller->searchRequestKey,
                'value'       => 'text',
                'description' => 'The value of searching',
                'disabled'    => !0,
            ],
            [
                'key'         => "$controller->filterRequestKey[user_id]",
                'value'       => 1,
                'description' => "Filter items by attribute name. $controller->filterRequestKey is Object",
                'disabled'    => !0,
            ],
        ];
    }

    /**
     * @param $rules
     * @param bool $array
     *
     * @return array|string
     */
    public function parseFormRules($rules, bool $array = !1): array | string
    {
        if (!is_array($rules)) {
            $rules = explode(',', $rules);
        }
        $rules = array_filter($rules);
        foreach ($rules as $k => $rule) {
            if ($rule instanceof Unique) {
                $rules[$k] = "unique";
            }
            elseif ($rule instanceof Exists || $rule instanceof Password) {
                $rules[$k] = null;
            }
            elseif ($rule instanceof Rule) {
                $rules[$k] = class_basename($rule);
            }
        }
        if ($array) {
            return array_filter($rules);
        }
        //d($rules);
        return implode(', ', array_filter($rules));
    }

    /**
     * @param string|null $attribute
     * @param array $rule
     * @return string
     */
    public function getFullDescription(string $attribute = null, array $rule = []): string
    {
        $en = null;
        $ar = null;
        $rule = $this->parseFormRules($rule, !0);
        $formRule = $this->parseFormRules($rule);
        $str = "";
        if (!is_null($attribute)) {
            $attr = $attribute;
            if (Str::contains($attr, '*.') && (trans_has("attributes.$attr", 'ar') || trans_has("attributes.$attr", 'en'))) {
                $attr = Str::afterLast($attribute, '.');
            }
            else {
                $attr = Str::before($attribute, '.');
            }
            $k = "attributes.$attr";
            $def = ucwords($attr);
            $ar = trans_has($k, 'ar') ? __($k, [], 'ar') : $def;
            $en = trans_has($k, 'en') ? __($k, [], 'en') : $def;
        }

        if (!is_null($en)) {
            $str = trim("$en - ").trim($str);
        }

        $str = trim($str);
        if (!is_null($ar) && $ar != $en) {
            //$str = trim($str)." - {$ar}";
            $str = trim("$ar - ".trim($str));
        }
        return trim($str)." [$formRule] ";
    }

    /**
     * @param $key
     * @param array $examples
     *
     * @return string
     */
    public function findExample($key, array $examples): string
    {
        $str = '';
        if (array_key_exists($key, $examples)) {
            $v = $examples[$key];
            $str = is_array($v) ? ($v['value'] ?? '') : $v;
        }
        else {
            foreach ($examples as $example) {
                if ($key == ($example['key'] ?? null)) {
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
    public function isExample($key, array $examples): bool
    {
        if (array_key_exists($key, $examples)) {
            return !0;
        }
        else {
            foreach ($examples as $example) {
                if ($key == ($example['key'] ?? null)) {
                    return !0;
                }
            }
        }
        return !1;
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
     * @param array $examples
     *
     * @return array
     */
    public function findQueryExamples(array $examples): array
    {
        $query = [];
        foreach ($examples as $k => $example) {
            $ex = [
                'key'         => is_array($example) ? $example['key'] : $k,
                'value'       => is_array($example) ? $example['value'] : $example,
                'description' => is_array($example) ? ($example['description'] ?? '') : __("attributes.$k"),
                'disabled'    => is_array($example) ? ($example['disabled'] ?? !0) : !0,
            ];
            $query[] = $ex;
        }
        return $query;
    }

    /**
     * Postman query params
     *
     * @param $controller
     *
     * @return array
     */
    public function getControllerParams($controller): array
    {
        $pagination = $this->getControllerPaginationParams($controller);
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
    public function getAuth(): array
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
     * Get the methods name to add exec
     *
     * @return array<int,string>
     */
    public function getScriptActions(): array
    {
        return ['login', 'loginMobile', 'register', 'resetPassword'];
    }

    /**
     * Postman file information
     *
     * @return array
     */
    public function getFileInfo(): array
    {
        $info = [
            //     '_postman_id' => Str::random(36),
            'name'        => $this->getCollectionName(),
            'description' => $this->getDescription(),
            'schema'      => "https://schema.getpostman.com/json/collection/v2.1.0/collection.json",
        ];
        if ($this->getCollectionId()) {
            $info['_postman_id'] = $this->getCollectionId();
        }

        if ($this->getExporterId()) {
            $info['_exporter_id'] = $this->getExporterId();
        }

        return $info;
    }

    /**
     * @return string
     */
    public function getCollectionName(): string
    {
        return $this->collectionName;
    }

    /**
     * @param string $collectionName
     */
    public function setCollectionName(string $collectionName): void
    {
        $this->collectionName = $collectionName;
    }

    /**
     * Get description of postman documentation
     *
     * @return string
     */
    public function getDescription(): string
    {
        $description = '';
        if (trans_has(static::DESCRIPTION_KEY)) {
            $description .= (__(static::DESCRIPTION_KEY, [
                'name' => config('.app.name'),
                'year' => now()->format("Y"),
            ]) ?: '');
        }
        $description .= PHP_EOL."Powered by MyTh All rights reserved.";
        return $description;
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
     * Postman variables
     *
     * @return array
     */
    public function getCollectionVariables(): array
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

        foreach ($this->collectionVariables as $key => $value) {
            $vars[] = [
                'key'   => $key,
                'value' => $value,
            ];
        }

        return $vars;
    }

    /**
     * @return string
     */
    public function getDomain(): string
    {
        return $this->domain;
    }

    /**
     * @param string $domain
     */
    public function setDomain(string $domain): void
    {
        $this->domain = $domain;
    }

    /**
     * @return Filesystem
     */
    public function disk(): Filesystem
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
    public function getFilePath(): string
    {
        return $this->disk()->path($this->getFileName());
    }

    /**
     * @param $key
     * @param array $examples
     *
     * @return string
     */
    public function findExampleDescription($key, array $examples): string
    {
        $str = '';
        if (array_key_exists($key, $examples)) {
            $v = $examples[$key];
            $str = is_array($v) ? ($v['description'] ?? '') : $v;
        }
        else {
            foreach ($examples as $example) {
                if ($key == ($example['key'] ?? null)) {
                    $str = ($example['description'] ?? '');
                    break;
                }
            }
        }
        return (string) $str;
    }
}
