<?php
require_once 'engine.php';

class RocketInterpolation
{
    /** Fills in all the property values */
    public static function interpolateTemplate(string $template, array $object)
    {
        $interpolatedTemplate = $template;
        $allPropertyTemplates = RocketEngine::getTemplates($template, "{{", "}}");

        foreach ($allPropertyTemplates as $propertyTemplate) {
            $property = RocketEngine::getInnerTemplate($propertyTemplate, 2);
            $encode = $property[0] === '!';

            if ($encode) {
                $property = trim(substr($property, 1));
            }

            $templateItem = RocketEngine::getPropertyValue($property, $object);

            if ($templateItem) {
                $replacement = $templateItem;
            }

            $replacement = is_array($replacement) ? implode(', ', $replacement) : trim(strval($replacement));

            $templateReplacement = $encode ? self::escapeHtml($replacement) : $replacement;

            $interpolatedTemplate = str_replace($propertyTemplate, $templateReplacement, $interpolatedTemplate);
        }

        return $interpolatedTemplate;
    }

    private static function escapeHtml($value)
    {
        $value = preg_replace('/&/', '&amp;', $value);
        $value = preg_replace('/</', '&lt;', $value);
        $value = preg_replace('/>/', '&gt;', $value);
        $value = preg_replace('/"/', '&quot;', $value);
        $value = preg_replace('/\//', '&#039;', $value);

        return $value;
    }
}

?>