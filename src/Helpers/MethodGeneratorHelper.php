<?php

namespace Crescat\SaloonSdkGenerator\Helpers;

namespace Crescat\SaloonSdkGenerator\Helpers;

use Crescat\SaloonSdkGenerator\Data\Generator\Parameter;
use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\Dumper;
use Nette\PhpGenerator\Literal;
use Nette\PhpGenerator\Method;

class MethodGeneratorHelper
{
    /**
     * Adds a promoted property to a method based on a given parameter.
     *
     * @param  Method  $method The method to which the promoted property is added.
     * @param  Parameter  $parameter The parameter based on which the promoted property is added.
     * @return Method The updated method with the promoted property.
     */
    public static function addParameterAsPromotedProperty(Method $method, Parameter $parameter): Method
    {
        $name = NameHelper::safeVariableName($parameter->name);

        $method
            ->addComment(
                trim(sprintf('@param %s $%s %s', $parameter->type, $name, $parameter->description))
            )
            ->addPromotedParameter($name)
            ->setType($parameter->type)
            ->setNullable($parameter->nullable)
            ->setProtected();

        return $method;
    }

    /**
     * Generates a method that returns parameters as an array.
     */
    public static function generateArrayReturnMethod(ClassType $classType, string $name, array $parameters): Method
    {
        $paramArray = self::buildParameterArray($parameters);

        return self::addMethodToClass($classType, $name, $paramArray);
    }

    /**
     * Builds an array of parameters with their corresponding values.
     */
    protected static function buildParameterArray(array $parameters): array
    {
        return collect($parameters)
            ->mapWithKeys(function (Parameter $parameter) {
                return [
                    $parameter->name => new Literal(
                        sprintf('$this->%s', NameHelper::safeVariableName($parameter->name))
                    ),
                ];
            })
            ->toArray();
    }

    /**
     * Adds a new method to the given class type with the specified name and body.
     */
    protected static function addMethodToClass(ClassType $classType, string $name, array $paramArray): Method
    {
        $body = sprintf('return %s;', (new Dumper)->dump($paramArray));

        return $classType
            ->addMethod($name)
            ->setReturnType('array')
            ->addBody($body);
    }
}
