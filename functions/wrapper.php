<?php
require_once 'engine.php';

class RocketWrapper
{
    public static function resolveWrapper($template)
    {
        $resolvedWrapperTemplate = $template;
        $allWrapperTemplates = RocketEngine::getTemplates($template, '{{$', '$}}');
        $onlyFirst = 1;

        foreach ($allWrapperTemplates as $wrapperTemplate) {
            $wrapperParameters = RocketEngine::getWrapperProperty($wrapperTemplate);

            $allTemplates = RocketEngine::getTemplates($wrapperParameters->templateToFill, '{{', '}}');
            $toplevelTemplates = RocketEngine::getToplevelTemplates($allTemplates);

            $newTemplate = $wrapperParameters->templateToFill;

            foreach ($toplevelTemplates as $toplevelTemplate) {
                $property = RocketEngine::getProperty($toplevelTemplate);

                /** apply the wrapper property as prefix. */
                $newTemplate = str_replace($property, "$wrapperParameters->property.$property", $newTemplate, $onlyFirst);
            }
            /** put it into the final result. */
            $resolvedWrapperTemplate = str_replace($wrapperTemplate, $newTemplate, $resolvedWrapperTemplate);
        }

        return $resolvedWrapperTemplate;
    }
}
?>