<?php
require_once __DIR__ . '/../functions/engine.php';

class RocketConditional
{
    /** a conditional statement must be terminated by a \n a <, { and }, where that is an interpolation or another template */
    public static function resolveConditional(string $template, array $object)
    {
        $resolvedConditionalTemplate = $template;
        $allConditionalTemplates = RocketEngine::getTemplates($template, "{{~", "~}}");

        foreach ($allConditionalTemplates as $conditionalTemplate) {
            $conditionalParameters = RocketEngine::getConditionalLogic($conditionalTemplate);

            $propToValidate = $conditionalParameters->property;
            $logiclessTemplate = $conditionalParameters->logiclessTemplate;
            $truthyCheck = $conditionalParameters->truthy;
            $falsyCheck = !$conditionalParameters->truthy;
            $comparisonValue = '';

            if (isset($conditionalParameters->comparison)) {
                $comparisonValue = $conditionalParameters->comparison;
            }

            $replacement = '';

            $propertyValue = RocketEngine::getPropertyValue($propToValidate, $object);

            // use case insensitive now, could make it an option
            if ($propertyValue) {
                $propertyValue = is_array($propertyValue) ? $propertyValue : trim(strtolower(strval($propertyValue)));
                $comparisonValue = isset($conditionalParameters->comparison) ? strtolower($conditionalParameters->comparison) : null;
            }

            // maybe nice to expand to other comparisons > < >= <= :)

            if ($comparisonValue) {
                if ($truthyCheck) {
                    $replacement = $propertyValue === $comparisonValue ? $logiclessTemplate : '';
                }

                if ($falsyCheck) {
                    $replacement = $propertyValue !== $comparisonValue ? $logiclessTemplate : '';
                }
            } else {
                /** treat as truthy check aka it must exist and not be 0. if [property] */
                if ($propertyValue === 'false' || !$propertyValue || (is_array($propertyValue) && !count($propertyValue)))
                    $replacement = '';
                else {
                    $replacement = $logiclessTemplate;
                }
            }

            $resolvedConditionalTemplate = str_replace($conditionalTemplate, $replacement, $resolvedConditionalTemplate);
        }

        return $resolvedConditionalTemplate;
    }
}