<?php

require_once __DIR__ . '/../functions/engine.php';

class RocketPartial
{
    public static function resolvePartials($template, $object)
    {
        $resolvedTemplate = $template;
        $allPartialTemplates = RocketEngine::getTemplates($template, "{{#", "#}}");

        foreach ($allPartialTemplates as $partialTemplate) {
            $property = RocketEngine::getInnerTemplate($partialTemplate);

            $templateItem = RocketEngine::getPropertyValue($property, $object);

            $replacement = '';
            if ($templateItem) {
                $replacement = $templateItem;
            }

            $resolvedTemplate = str_replace($partialTemplate, $replacement, $resolvedTemplate);
        }

        /** O yes, nested partials now exist! */
        if (str_contains($resolvedTemplate, '{{#')) {
            $resolvedTemplate = self::resolvePartials($resolvedTemplate, $object);
        }
        return $resolvedTemplate;
    }
}

?>