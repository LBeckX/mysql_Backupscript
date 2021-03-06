<?php

/**
 * Litte helper to auto-create the api-response-modules.
 *
 * @param $apiResponse
 * @return string
 */
function getModuleCode($class, $apiResponse)
{
    $return = "<?php

namespace rpf\\apiResponse\\module;
use rpf\\apiResponse\\apiResponseModule;

/**
 * Class $class
 *
 * !! DO NOT EDIT THIS FILE !!
 * (It's generated automatically)
 */
class $class extends apiResponseModule
{
";
    //foreach ($apiResponse as $name => $value)
    //{
    //    $return .= "    /**\n    * @var ".gettype($value)."\n    */\n    protected \$$name;\n\n";
    //}
    foreach ($apiResponse as $name => $value)
    {
        //CamelCase
        $camelName = 'get';
        $array = explode('_', $name);
        foreach ($array as $val)
        {
            $camelName .= ucfirst($val);
        }

        $value = is_string($value) && $value == intval($value) ? intval($value) : $value;
        $value = $value == NULL ? '' : $value;


        $return .= "    /**\n     * @return ".gettype($value)." \$this->rpcResponse[$name]\n     */\n";
        $return .= "    public function $camelName()\n    {\n        return \$this->rpcResponse['$name'];\n    }\n\n";
    }

    $return .= "}\n";
    return $return;
}


function writeModules($class, $apiResponse)
{
    $code = getModuleCode($class, $apiResponse);
    file_put_contents(__DIR__."/module/$class.php", $code);
    //file_put_contents(__DIR__."/module$class.txt", 'blubb');
}

